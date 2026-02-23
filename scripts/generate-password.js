/**
 * Utility script to generate bcrypt password hash
 * Usage: node scripts/generate-password.js <password>
 */

const bcrypt = require('bcryptjs');

const password = process.argv[2];

if (!password) {
  console.error('Usage: node scripts/generate-password.js <password>');
  process.exit(1);
}

bcrypt.hash(password, 10).then((hash) => {
  console.log('\nPassword hash generated:');
  console.log(hash);
  console.log('\nUse this hash in your database:\n');
  console.log(`UPDATE users SET password_hash = '${hash}' WHERE username = 'admin';`);
  console.log('\n');
});

