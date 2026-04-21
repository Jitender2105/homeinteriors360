# HomeInteriors360 PHP App

This folder contains the PHP frontend + backend for HomeInteriors360.

## Included

- Public pages: `/`, `/professionals`, `/professionals/{slug}`, `/cost-calculator`
- Lead capture with homepage/profile/calculator sources
- Admin auth and dashboard
- Admin pages: `/admin`, `/admin/content`, `/admin/leads`, `/admin/pros`
- APIs:
  - `/api/auth/login`, `/api/auth/logout`, `/api/auth/me`
  - `/api/homepage`, `/api/pros`, `/api/pros/{slug}`, `/api/site-content`
  - `/api/leads`, `/api/calculator/estimate`
  - `/api/admin/content`, `/api/admin/leads`, `/api/admin/leads/status`
  - `/api/admin/pros`, `/api/admin/pros/verify`

## Setup

1. Create DB and import schema:
   - Use `database/schema.sql`
2. Copy env file:
   - `cp .env.example .env.local`
3. Update DB credentials in `.env.local`
4. Serve `public/` as document root.
5. Set `APP_KEY` in `.env.local` (long random secret).

## Hostinger Git Sync

Use one of these deployment layouts:

1. Recommended:
   - Repository path/docroot points to `homeinteriors/public`
   - Keep `homeinteriors/src` and `homeinteriors/database` in deployment
2. Flat layout (`public_html` contains `index.php`, `src`, `assets`):
   - The bootstrap loader already supports this layout too.

## Default admin

- username: `admin`
- password: `admin123`

Change this immediately in production.

## Notes

- Pretty URL routing uses `public/.htaccess`.
- Auth is session-based with signed cookie fallback (for shared-hosting session edge cases).
- Super admin-only routes require user role = `super_admin`.
