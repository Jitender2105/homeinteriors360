import { query, queryOne } from '@/lib/db';

async function tableExists(tableName: string) {
  const row = (await queryOne(
    `SELECT 1 as ok
     FROM information_schema.tables
     WHERE table_schema = DATABASE() AND table_name = ?`,
    [tableName]
  )) as { ok?: number } | null;
  return !!row?.ok;
}

async function columnExists(tableName: string, columnName: string) {
  const row = (await queryOne(
    `SELECT 1 as ok
     FROM information_schema.columns
     WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?`,
    [tableName, columnName]
  )) as { ok?: number } | null;
  return !!row?.ok;
}

async function ensureIndex(tableName: string, indexName: string, ddl: string) {
  const row = (await queryOne(
    `SELECT 1 as ok
     FROM information_schema.statistics
     WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?`,
    [tableName, indexName]
  )) as { ok?: number } | null;
  if (!row?.ok) {
    await query(ddl, []);
  }
}

export async function ensureDesignerSchema() {
  await query(
    `CREATE TABLE IF NOT EXISTS interior_designers (
      id INT AUTO_INCREMENT PRIMARY KEY,
      full_name VARCHAR(255) NOT NULL,
      slug VARCHAR(255) UNIQUE NOT NULL,
      profile_title VARCHAR(255),
      bio TEXT,
      profile_image VARCHAR(500),
      years_experience INT DEFAULT 0,
      total_projects INT DEFAULT 0,
      is_active BOOLEAN DEFAULT TRUE,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_interior_designer_slug (slug),
      INDEX idx_interior_designer_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`,
    []
  );

  await query(
    `CREATE TABLE IF NOT EXISTS interior_designer_projects (
      id INT AUTO_INCREMENT PRIMARY KEY,
      interior_designer_id INT NOT NULL,
      image_url VARCHAR(500) NOT NULL,
      location VARCHAR(255),
      cost_range VARCHAR(100),
      work_type VARCHAR(100),
      project_title VARCHAR(255),
      display_order INT DEFAULT 0,
      is_active BOOLEAN DEFAULT TRUE,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_designer_projects_designer (interior_designer_id),
      INDEX idx_designer_projects_active (is_active),
      FOREIGN KEY (interior_designer_id) REFERENCES interior_designers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`,
    []
  );

  await query(
    `CREATE TABLE IF NOT EXISTS interior_designer_testimonials (
      id INT AUTO_INCREMENT PRIMARY KEY,
      interior_designer_id INT NOT NULL,
      customer_name VARCHAR(255) NOT NULL,
      customer_location VARCHAR(255),
      testimonial_text TEXT NOT NULL,
      rating TINYINT DEFAULT 5,
      display_order INT DEFAULT 0,
      is_active BOOLEAN DEFAULT TRUE,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_designer_testimonials_designer (interior_designer_id),
      INDEX idx_designer_testimonials_active (is_active),
      FOREIGN KEY (interior_designer_id) REFERENCES interior_designers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`,
    []
  );

  await query(
    `CREATE TABLE IF NOT EXISTS interior_designer_trust_points (
      id INT AUTO_INCREMENT PRIMARY KEY,
      interior_designer_id INT NOT NULL,
      title VARCHAR(255) NOT NULL,
      description TEXT,
      display_order INT DEFAULT 0,
      is_active BOOLEAN DEFAULT TRUE,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_designer_trust_designer (interior_designer_id),
      INDEX idx_designer_trust_active (is_active),
      FOREIGN KEY (interior_designer_id) REFERENCES interior_designers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`,
    []
  );

  await query(
    `CREATE TABLE IF NOT EXISTS interior_designer_usps (
      id INT AUTO_INCREMENT PRIMARY KEY,
      interior_designer_id INT NOT NULL,
      title VARCHAR(255) NOT NULL,
      description TEXT,
      display_order INT DEFAULT 0,
      is_active BOOLEAN DEFAULT TRUE,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_designer_usps_designer (interior_designer_id),
      INDEX idx_designer_usps_active (is_active),
      FOREIGN KEY (interior_designer_id) REFERENCES interior_designers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`,
    []
  );

  if (await tableExists('users')) {
    if (!(await columnExists('users', 'role'))) {
      await query(
        `ALTER TABLE users ADD COLUMN role ENUM('admin', 'super_admin') NOT NULL DEFAULT 'admin'`,
        []
      );
    }
  }

  if (await tableExists('leads')) {
    if (!(await columnExists('leads', 'interior_designer_id'))) {
      await query(`ALTER TABLE leads ADD COLUMN interior_designer_id INT NULL`, []);
    }
    await ensureIndex(
      'leads',
      'idx_lead_interior_designer',
      'ALTER TABLE leads ADD INDEX idx_lead_interior_designer (interior_designer_id)'
    );
  }
}

export function getFriendlyDesignerSchemaError(error: any) {
  const code = error?.code || 'UNKNOWN';

  if (
    code === 'ER_DBACCESS_DENIED_ERROR' ||
    code === 'ER_ACCESS_DENIED_ERROR' ||
    code === 'ER_TABLEACCESS_DENIED_ERROR' ||
    code === 'ER_SPECIFIC_ACCESS_DENIED_ERROR'
  ) {
    return {
      status: 500,
      error:
        'Database user lacks CREATE/ALTER privileges for designer onboarding tables. Please run migration with a privileged DB user.',
      code,
    };
  }

  if (code === 'ER_DUP_ENTRY') {
    return {
      status: 409,
      error: 'A designer with similar unique data already exists (likely slug).',
      code,
    };
  }

  if (code === 'ER_NO_SUCH_TABLE' || code === 'ER_BAD_FIELD_ERROR') {
    return {
      status: 500,
      error: 'Database schema is not up-to-date for designer microsites. Run the migration SQL from database/schema.sql.',
      code,
    };
  }

  return {
    status: 500,
    error: 'Failed to process interior designer request due to a database error.',
    code,
  };
}
