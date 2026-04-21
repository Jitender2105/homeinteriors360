# HomeInteriors PHP Migration

This folder contains a PHP (frontend + backend) migration of the Interior360 Node.js app.

## Included

- Public pages: `/`, `/articles`, `/designs`, `/designer/{slug}`
- Lead form and lead APIs
- Admin auth and dashboard
- Admin pages: leads, content, form-options, interior-designers
- APIs equivalent to the Node version:
  - `/api/auth/login`, `/api/auth/logout`, `/api/auth/me`
  - `/api/form-options`, `/api/localities`, `/api/site-content`, `/api/leads`
  - `/api/admin/content`, `/api/admin/form-options`
  - `/api/admin/interior-designers`, `/api/admin/interior-designers/{id}`
  - `/api/admin/interior-designers/{id}/sections`

## Setup

1. Create DB and import schema:
   - Use `database/schema.sql`
2. Copy env file:
   - `cp .env.example .env.local`
3. Update DB credentials in `.env.local`
4. Serve `public/` as document root.

## Default admin

- username: `admin`
- password: `admin123`

Change this immediately in production.

## Notes

- Pretty URL routing uses `public/.htaccess`.
- Auth is session-based in PHP.
- Super admin-only routes require user role = `super_admin`.
