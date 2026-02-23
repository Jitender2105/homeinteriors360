// lib/db.js
import mysql from "mysql2/promise";

/**
 * Read and validate DB config from env
 */
function getDbConfig() {
  const host = process.env.DB_HOST || "localhost";
  const port = Number(process.env.DB_PORT || 3306);
  const user = process.env.DB_USER || "root";
  const password = process.env.DB_PASSWORD; // ✅ NO hardcoded fallback password
  const database = process.env.DB_NAME || "interiordesign360";

  if (!Number.isFinite(port)) {
    throw new Error(`Invalid DB_PORT: ${process.env.DB_PORT}`);
  }

  // Helpful dev logs (never print password)
  if (process.env.NODE_ENV === "development") {
    console.log("Database Config:", {
      host,
      port,
      user,
      database,
      hasPassword: !!password,
    });
  }

  // In production, fail fast if password missing (recommended)
  if (!password && process.env.NODE_ENV === "production") {
    throw new Error("DB_PASSWORD is missing in environment variables.");
  }

  return {
    host,
    port,
    user,
    password: password || "", // allow blank only if your DB actually has blank password (rare)
    database,
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0,
    enableKeepAlive: true,
    keepAliveInitialDelay: 0,
  };
}

/**
 * Pool caching (prevents multiple pools during Next.js hot reload)
 */
const globalForMySQL = globalThis;

function getPool() {
  if (!globalForMySQL.__mysqlPool) {
    const config = getDbConfig();
    globalForMySQL.__mysqlPool = mysql.createPool(config);
  }
  return globalForMySQL.__mysqlPool;
}

/**
 * Execute a prepared SQL query
 * @param {string} sql - SQL query with placeholders (?)
 * @param {Array} params - Parameters for the query
 */
export async function query(sql, params = []) {
  try {
    const pool = getPool();
    const [results] = await pool.execute(sql, params);
    return results;
  } catch (error) {
    console.error("Database query error:", error);

    if (error?.code === "ER_ACCESS_DENIED_ERROR") {
      console.error("\n❌ Database Access Denied");
      console.error("Check:");
      console.error("1) .env.local exists in project root");
      console.error("2) DB_USER / DB_PASSWORD are correct");
      console.error("3) You restarted the dev server after editing env\n");
    }

    if (error?.code === "ECONNREFUSED") {
      console.error("\n❌ Connection refused");
      console.error("MySQL is likely not running or wrong host/port.\n");
    }

    throw error;
  }
}

export async function queryOne(sql, params = []) {
  const results = await query(sql, params);
  return Array.isArray(results) && results.length > 0 ? results[0] : null;
}

export async function getSiteContent(pageName, sectionKey) {
  const result = await queryOne(
    "SELECT content_value FROM site_content WHERE page_name = ? AND section_key = ? AND is_active = TRUE",
    [pageName, sectionKey]
  );
  return result ? result.content_value : null;
}

export async function getPageContent(pageName) {
  const results = await query(
    "SELECT section_key, content_value, content_type FROM site_content WHERE page_name = ? AND is_active = TRUE ORDER BY display_order, id",
    [pageName]
  );

  const content = {};
  for (const row of results) {
    content[row.section_key] = row.content_value;
  }
  return content;
}

export async function getFormOptions(fieldType) {
  const results = await query(
    "SELECT option_value, display_order FROM form_options WHERE field_type = ? AND is_active = TRUE ORDER BY display_order, id",
    [fieldType]
  );
  return results.map((row) => row.option_value);
}

export async function getLocalities() {
  return await query(
    "SELECT name, area_type FROM localities WHERE is_active = TRUE ORDER BY display_order, name",
    []
  );
}

export async function testConnection() {
  try {
    await query("SELECT 1");
    return true;
  } catch {
    return false;
  }
}

// ✅ Export the getter (not the pool instance)
export default getPool;
