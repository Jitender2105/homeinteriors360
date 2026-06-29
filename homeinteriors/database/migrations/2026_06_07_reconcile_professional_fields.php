<?php

declare(strict_types=1);

$bootstrap = dirname(__DIR__, 2) . '/src/bootstrap.php';
if (!is_file($bootstrap)) {
    $bootstrap = __DIR__ . '/../../src/bootstrap.php';
}
require $bootstrap;

$pdo = Database::pdo();
$databaseName = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();

$columns = [
    'full_name' => 'VARCHAR(255) NOT NULL',
    'slug' => 'VARCHAR(255) NOT NULL',
    'profile_pic' => 'VARCHAR(500) DEFAULT NULL',
    'cover_photo' => 'VARCHAR(500) DEFAULT NULL',
    'role' => "ENUM('Architect','Designer','Contractor') NOT NULL DEFAULT 'Designer'",
    'profile_description' => 'TEXT DEFAULT NULL',
    'specialization' => 'VARCHAR(255) DEFAULT NULL',
    'primary_work_type' => 'VARCHAR(120) DEFAULT NULL',
    'primary_work_area' => 'VARCHAR(120) DEFAULT NULL',
    'verification_status' => 'TINYINT(1) NOT NULL DEFAULT 0',
    'is_premium' => 'TINYINT(1) NOT NULL DEFAULT 0',
    'rating' => 'DECIMAL(3,2) NOT NULL DEFAULT 0',
    'years_experience' => 'INT NOT NULL DEFAULT 0',
    'projects_delivered' => 'INT NOT NULL DEFAULT 0',
    'starting_price' => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
    'min_project_value' => 'DECIMAL(12,2) DEFAULT NULL',
    'max_project_value' => 'DECIMAL(12,2) DEFAULT NULL',
    'consultation_fee' => 'DECIMAL(12,2) DEFAULT NULL',
    'city' => 'VARCHAR(120) DEFAULT NULL',
    'office_address' => 'VARCHAR(255) DEFAULT NULL',
    'phone' => 'VARCHAR(30) DEFAULT NULL',
    'email' => 'VARCHAR(255) DEFAULT NULL',
    'website_url' => 'VARCHAR(500) DEFAULT NULL',
    'founded_year' => 'YEAR DEFAULT NULL',
    'team_size' => 'INT DEFAULT NULL',
    'office_hours' => 'VARCHAR(120) DEFAULT NULL',
    'client_count' => 'INT DEFAULT NULL',
    'service_summary' => 'TEXT DEFAULT NULL',
    'service_areas' => 'JSON DEFAULT NULL',
    'materials_json' => 'JSON DEFAULT NULL',
    'design_styles_json' => 'JSON DEFAULT NULL',
    'languages_json' => 'JSON DEFAULT NULL',
    'certifications_json' => 'JSON DEFAULT NULL',
    'process_steps_json' => 'JSON DEFAULT NULL',
    'awards_json' => 'JSON DEFAULT NULL',
    'faq_json' => 'JSON DEFAULT NULL',
    'response_time_hours' => 'INT DEFAULT NULL',
    'bio' => 'TEXT DEFAULT NULL',
    'why_work_with_me' => 'TEXT DEFAULT NULL',
    'offerings_json' => 'JSON DEFAULT NULL',
    'google_business_url' => 'VARCHAR(500) DEFAULT NULL',
    'is_active' => 'TINYINT(1) NOT NULL DEFAULT 1',
    'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
];

$indexes = [
    'idx_pro_role' => ['role'],
    'idx_pro_city' => ['city'],
    'idx_pro_primary_work_type' => ['primary_work_type'],
    'idx_pro_primary_work_area' => ['primary_work_area'],
    'idx_pro_verified' => ['verification_status'],
    'idx_pro_active' => ['is_active'],
];

$columnStmt = $pdo->prepare(
    'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?'
);
$columnStmt->execute([$databaseName, 'pros']);
$existingColumns = array_fill_keys(array_map('strval', $columnStmt->fetchAll(PDO::FETCH_COLUMN)), true);

$addedColumns = [];
foreach ($columns as $name => $definition) {
    if (isset($existingColumns[$name])) {
        continue;
    }
    $pdo->exec(sprintf('ALTER TABLE pros ADD COLUMN `%s` %s', $name, $definition));
    $addedColumns[] = $name;
}

$indexStmt = $pdo->prepare(
    'SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?'
);
$indexStmt->execute([$databaseName, 'pros']);
$existingIndexes = array_fill_keys(array_map('strval', $indexStmt->fetchAll(PDO::FETCH_COLUMN)), true);

$addedIndexes = [];
foreach ($indexes as $name => $indexColumns) {
    if (isset($existingIndexes[$name])) {
        continue;
    }
    $quotedColumns = implode(', ', array_map(static fn(string $column): string => '`' . $column . '`', $indexColumns));
    $pdo->exec(sprintf('ALTER TABLE pros ADD INDEX `%s` (%s)', $name, $quotedColumns));
    $addedIndexes[] = $name;
}

$verification = $pdo->query('SHOW COLUMNS FROM pros')->fetchAll();
$missingAfterMigration = array_values(array_diff(array_keys($columns), array_column($verification, 'Field')));

$testAssignments = implode(', ', array_map(static fn(string $name): string => sprintf('`%s` = `%s`', $name, $name), array_keys($columns)));
$pdo->exec('UPDATE pros SET ' . $testAssignments . ' WHERE id = (SELECT id FROM (SELECT id FROM pros ORDER BY id LIMIT 1) AS professional)');

return [
    'database' => $databaseName,
    'added_columns' => $addedColumns,
    'added_indexes' => $addedIndexes,
    'missing_after_migration' => $missingAfterMigration,
    'total_columns_checked' => count($columns),
    'update_statement_verified' => true,
];
