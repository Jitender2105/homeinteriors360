# Database Setup Guide

## Quick Fix for "Access denied" Error

If you're seeing the error: `Access denied for user 'root'@'localhost' (using password: NO)`, follow these steps:

### Step 1: Create `.env.local` file

Create a file named `.env.local` in the project root (same directory as `package.json`) with the following content:

```env
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=your_mysql_password_here
DB_NAME=interiordesign360

JWT_SECRET=your_super_secret_jwt_key_change_this_in_production
NEXT_PUBLIC_SITE_URL=http://localhost:3000
```

**Important:** Replace `your_mysql_password_here` with your actual MySQL root password.

### Step 2: Restart the Development Server

After creating `.env.local`, you **must** restart your Next.js development server:

1. Stop the current server (Ctrl+C or Cmd+C)
2. Start it again: `npm run dev`

Next.js only loads environment variables when the server starts, so changes to `.env.local` require a restart.

### Step 3: Verify Database Connection

Make sure:
- MySQL is running on your system
- The database `interiordesign360` exists (created by running `database/schema.sql`)
- Your MySQL root password matches what's in `.env.local`

### Common Issues

#### Issue: "Access denied" even with correct password
- **Solution:** Make sure `.env.local` is in the project root, not in a subdirectory
- **Solution:** Restart the dev server after creating/updating `.env.local`
- **Solution:** Check for typos in variable names (should be `DB_PASSWORD`, not `DB_PASS`)

#### Issue: "Unknown database 'interiordesign360'"
- **Solution:** Run the schema file: `mysql -u root -p < database/schema.sql`

#### Issue: Environment variables not loading
- **Solution:** Make sure the file is named exactly `.env.local` (not `.env`, `.env.local.txt`, etc.)
- **Solution:** Restart the dev server
- **Solution:** Check that the file is in the project root directory

### Testing the Connection

You can test if your database connection works by running:

```bash
node -e "require('dotenv').config({path:'.env.local'}); const mysql=require('mysql2/promise'); mysql.createConnection({host:process.env.DB_HOST,port:process.env.DB_PORT,user:process.env.DB_USER,password:process.env.DB_PASSWORD,database:process.env.DB_NAME}).then(c=>{console.log('✅ Connection successful!');c.end();}).catch(e=>console.error('❌ Connection failed:',e.message));"
```

### Security Note

Never commit `.env.local` to git. It's already in `.gitignore`, but double-check that your sensitive credentials are not exposed.

