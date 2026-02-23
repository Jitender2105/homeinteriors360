/**
 * Setup script to create/update admin user with password
 * Usage: node scripts/setup-admin.js <username> <password>
 */

const bcrypt = require('bcryptjs');
const mysql = require('mysql2/promise');
require('dotenv').config({ path: '.env.local' });

const username = process.argv[2] || 'admin';
const password = process.argv[3] || 'admin123';

if (!password) {
  console.error('Usage: node scripts/setup-admin.js [username] [password]');
  process.exit(1);
}

async function setupAdmin() {
  let connection;
  
  try {
    // Connect to database
    connection = await mysql.createConnection({
      host: process.env.DB_HOST || 'localhost',
      port: parseInt(process.env.DB_PORT || '3306'),
      user: process.env.DB_USER || 'root',
      password: process.env.DB_PASSWORD || '',
      database: process.env.DB_NAME || 'interiordesign360',
    });

    console.log('Connected to database...');

    // Generate password hash
    console.log('Generating password hash...');
    const passwordHash = await bcrypt.hash(password, 10);

    // Check if user exists
    const [users] = await connection.execute(
      'SELECT id FROM users WHERE username = ?',
      [username]
    );

    if (users.length > 0) {
      // Update existing user
      await connection.execute(
        'UPDATE users SET password_hash = ? WHERE username = ?',
        [passwordHash, username]
      );
      console.log(`✓ Updated password for user: ${username}`);
    } else {
      // Create new user
      await connection.execute(
        'INSERT INTO users (username, password_hash, email) VALUES (?, ?, ?)',
        [username, passwordHash, `${username}@interiordesign360.com`]
      );
      console.log(`✓ Created admin user: ${username}`);
    }

    console.log('\n✓ Admin user setup complete!');
    console.log(`  Username: ${username}`);
    console.log(`  Password: ${password}`);
    console.log('\n⚠️  Remember to change this password in production!\n');

  } catch (error) {
    console.error('Error setting up admin:', error.message);
    process.exit(1);
  } finally {
    if (connection) {
      await connection.end();
    }
  }
}

setupAdmin();

