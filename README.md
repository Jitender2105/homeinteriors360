# Interior Design 360 - Database-Driven Lead Generation Website

A fully database-driven Next.js application for interior design lead generation in Gurgaon. **Zero hardcoded content** - everything from dropdown options to SEO meta tags is fetched from MySQL at runtime.

## Features

- ✅ **100% Database-Driven**: All content, form options, and SEO metadata stored in MySQL
- ✅ **Dynamic Lead Form**: Work types and budgets fetched from `form_options` table
- ✅ **Gurgaon Localities**: Dynamic list of societies and areas from `localities` table
- ✅ **Admin Panel**: Secure authentication with bcryptjs and JWT
- ✅ **Content Management**: Update site text, form options, and content without code changes
- ✅ **SEO Optimized**: Meta tags fetched from database per page
- ✅ **Portfolio & Blog**: Dynamic articles and design portfolio pages

## Tech Stack

- **Frontend**: Next.js 14 (App Router), React, TypeScript, Tailwind CSS
- **Backend**: Next.js API Routes
- **Database**: MySQL (mysql2/promise)
- **Authentication**: bcryptjs, JWT
- **Security**: Prepared statements for all SQL queries

## Setup Instructions

### 1. Database Setup

```bash
# Create database and tables
mysql -u root -p < database/schema.sql
```

The schema includes:
- `users` - Admin authentication
- `leads` - Customer inquiries
- `form_options` - Dynamic dropdown options
- `localities` - Gurgaon areas and societies
- `site_content` - All page text and SEO metadata
- `articles` - Blog content
- `designs` - Portfolio projects

### 2. Environment Variables

**⚠️ IMPORTANT:** You must create a `.env.local` file before running the application!

Create a file named `.env.local` in the project root with:

```env
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=your_mysql_password_here
DB_NAME=interiordesign360
JWT_SECRET=your_super_secret_jwt_key_change_this_in_production
NEXT_PUBLIC_SITE_URL=http://localhost:3000
```

**Replace `your_mysql_password_here` with your actual MySQL root password.**

**Important:** After creating or modifying `.env.local`, you **must restart** the Next.js dev server for changes to take effect.

If you see "Access denied" errors, see [SETUP.md](./SETUP.md) for troubleshooting.

### 3. Install Dependencies

```bash
npm install
```

### 4. Run Development Server

```bash
npm run dev
```

Visit `http://localhost:3000`

### 5. Setup Admin User

After running the database schema, set up your admin user:

```bash
npm run setup-admin [username] [password]
```

Example:
```bash
npm run setup-admin admin mySecurePassword123
```

If no arguments are provided, it defaults to:
- Username: `admin`
- Password: `admin123` (⚠️ **CHANGE THIS IN PRODUCTION!**)

Alternatively, you can generate a password hash manually:
```bash
node scripts/generate-password.js your_password
```

Then update the database:
```sql
UPDATE users SET password_hash = '<generated_hash>' WHERE username = 'admin';
```

## Project Structure

```
├── app/
│   ├── api/              # API routes
│   │   ├── auth/        # Authentication endpoints
│   │   ├── admin/      # Admin CRUD operations
│   │   ├── leads/       # Lead submission
│   │   ├── form-options/# Dynamic form options
│   │   ├── localities/  # Gurgaon areas
│   │   └── site-content/# Page content
│   ├── admin/           # Admin panel pages
│   ├── articles/        # Blog pages
│   ├── designs/         # Portfolio pages
│   └── page.tsx         # Home page (dynamic)
├── components/
│   └── LeadForm.tsx     # Dynamic lead form
├── lib/
│   ├── db.js           # Database connection & utilities
│   └── auth.js         # Authentication utilities
└── database/
    └── schema.sql      # Database schema
```

## Key Features Explained

### Dynamic Content System

All page content is stored in the `site_content` table:

```sql
SELECT content_value FROM site_content 
WHERE page_name = 'home' AND section_key = 'hero_title'
```

The home page fetches all sections at once and renders them dynamically.

### Dynamic Form Options

Form dropdowns are populated from the `form_options` table:

```sql
SELECT option_value FROM form_options 
WHERE field_type = 'work_type' AND is_active = TRUE
```

Add new work types or budget ranges from the admin panel without code changes.

### SEO Metadata

Each page fetches its meta tags from the database:

```typescript
const content = await getPageContent('home');
// Returns: { meta_title: '...', meta_description: '...' }
```

### Admin Panel

Access at `/admin` (redirects to `/admin/login` if not authenticated)

Features:
- View and manage leads
- Edit site content (all page text)
- Manage form options (work types, budgets)
- Manage localities
- Manage articles and designs

## Security Features

- ✅ All SQL queries use prepared statements
- ✅ Password hashing with bcryptjs
- ✅ JWT authentication with HTTP-only cookies
- ✅ Admin routes protected with `requireAuth` middleware
- ✅ Environment variables for sensitive data

## Database-Driven Architecture Benefits

1. **No Code Deployment for Content Changes**: Update text, options, and SEO tags from admin panel
2. **Market Responsive**: Add new Gurgaon localities as projects are delivered
3. **Budget Flexibility**: Adjust budget ranges when material costs change
4. **SEO Optimization**: Update meta tags per page without redeploying
5. **Scalable**: Easy to add new pages and content types

## Next Steps

1. **Install dependencies**: `npm install`
2. **Set up database**: Run the schema SQL file
3. **Configure environment**: Copy `.env.local.example` to `.env.local` and update values
4. **Change default admin password**: Use the password generator script
5. **Add your content**: Update `site_content` table with your actual text
6. **Customize form options**: Add/update work types and budgets in `form_options`
7. **Add localities**: Update `localities` table with actual Gurgaon areas
8. **Add portfolio & blog**: Create designs and articles through admin panel
9. **Test the application**: Run `npm run dev` and test all features
10. **Deploy**: Configure production environment variables and deploy

## Note on TypeScript Errors

If you see TypeScript/linter errors before running `npm install`, this is expected. The errors will resolve once dependencies are installed. The application uses TypeScript but some type definitions are provided by the installed packages.

## License

MIT

