# HomeInteriors360 PHP App

This folder contains the PHP frontend + backend for HomeInteriors360.

## Included

- Public pages: `/`, `/professionals`, `/professionals/{slug}`, `/properties`, `/property/{slug}`, `/cost-calculator`
- Lead capture with homepage/profile/calculator sources
- Admin auth and dashboard
- Database-managed real estate projects for sale and rent, including inventory, media, floor plans, pricing, amenities, SEO, and enquiries
- Admin pages: `/admin`, `/admin/content`, `/admin/leads`, `/admin/pros`
- APIs:
  - `/api/auth/login`, `/api/auth/logout`, `/api/auth/me`
  - `/api/homepage`, `/api/pros`, `/api/pros/{slug}`, `/api/site-content`
  - `/api/leads`, `/api/calculator/estimate`
  - `/api/property-projects`, `/api/property-projects/{slug}`, `/api/property-enquiries`
  - `/api/admin/content`, `/api/admin/leads`, `/api/admin/leads/status`
- `/api/admin/pros`, `/api/admin/pros/verify`

## Home Services Expansion

The platform now supports a configurable multi-category home-services layer alongside the existing interior journeys. It includes public service pages, city-specific pages, service-aware enquiry forms, consent capture, provider service/coverage relationships, commercial policy placeholders and an admin catalogue/form manager.

Public routes:

- `/home-services`
- `/services/{category}/{service}`
- `/{city}/{service}` for enabled Delhi NCR cities, for example `/gurgaon/electricians`

Admin route:

- `/admin/home-services`

APIs:

- `GET /api/home-services`
- `GET /api/home-services/{service}/form`
- `POST /api/home-services/enquiries`
- `POST /api/admin/home-services/nodes`
- `POST /api/admin/home-services/questions`

Run this additive migration before publishing any Home Services page:

- `database/migrations/2026_10_28_home_services.sql`

The migration seeds the initial Delhi NCR catalogue for design and construction, home improvement, repair and maintenance, cleaning and protection, and moving and household support. New services remain configurable from the admin catalogue. Existing interior leads, buyer carts, coupons, Razorpay verification and BOQ subscriptions are unchanged.

## Single Lead Sale

Admins can publish one selected lead as an individual sale bucket from:

- `/admin/single-lead-sales`
- Quick action in `/admin/lead-tracker`

Public buyers can browse or buy individual leads from:

- `/single-lead`
- `/single-lead/{slug}`

Single lead checkout is always flat `₹100`, does not use coupons, and does not apply the first-time free lead offer. Successful payments are delivered through the existing buyer dashboard and `lead_purchase_leads` mapping table.

Run this migration before using the module:

- `database/migrations/2026_09_09_single_lead_sales.sql`

## BOQ Studio upgrade

The Quotation Builder now supports BOQ-specific project context, item specifications, internal cost and optional markup, studio branding, per-designer rate cards, client-safe proposal links, and client acceptance or revision requests.

Run this migration after the earlier quotation migrations:

- `database/migrations/2026_10_13_boq_studio_upgrade.sql`

Internal cost, markup, margin, commission and internal notes are deliberately removed from public proposal and print payloads. Designer rate cards are scoped to the signed-in designer; administrators can continue to manage global and individual designer rates.

## Delhi-NCR Founding Partner + Lead Alerts

New public and dashboard routes:

- `/delhi-ncr-founding-partners`
- `/designer/alert-preferences`
- `/admin/lead-alerts`
- `/lead-alert-sw.js`

New APIs:

- `/api/designer/alert-preferences`
- `/api/designer/push-subscriptions`
- `/api/lead-engagement`
- `/api/admin/founding-partners/status`
- `/api/admin/lead-alert-settings`
- `/api/admin/lead-alerts/recalculate`
- `/api/admin/lead-alerts/start`
- `/api/admin/lead-alerts/process`

Run these migrations for lead assignment and alerting:

- `database/migrations/2026_08_18_lead_purchase_assignments.sql`
- `database/migrations/2026_08_18_founding_partner_lead_alerts.sql`

Feature flags:

- `FOUNDING_PARTNER_PROGRAM_ENABLED`
- `REAL_TIME_LEAD_MATCHING_ENABLED`
- `WHATSAPP_LEAD_ALERTS_ENABLED`
- `WEB_PUSH_LEAD_ALERTS_ENABLED`
- `ENGAGED_REMINDERS_ENABLED`
- `LEAD_ALERT_WAVE_2_ENABLED`
- `LEAD_ALERT_WAVE_3_ENABLED`

Optional wave settings:

- `LEAD_ALERT_WAVE_1_SIZE`
- `LEAD_ALERT_WAVE_2_SIZE`
- `LEAD_ALERT_WAVE_3_SIZE`
- `LEAD_ALERT_WAVE_2_DELAY_MINUTES`
- `LEAD_ALERT_WAVE_3_DELAY_MINUTES`

WhatsApp alerts currently use the provider abstraction with a fake provider unless production credentials/templates are configured and a real provider class is added. Browser push has subscription storage and service-worker click handling; production Web Push sending requires VAPID keys/provider wiring.

## Setup

1. Create DB and import schema:
   - Use `database/schema.sql`
2. Copy env file:
   - `cp .env.example .env.local`
3. Update DB credentials in `.env.local`
4. Serve `public/` as document root.
5. Set `APP_KEY` in `.env.local` (long random secret).

## Source-Verified Professional Import

The research import contains 30 public business profiles each for Delhi, Gurgaon, and Noida. It stores public business facts and source URLs only. It does not copy reviews, testimonials, or portfolio images. Every imported profile is inactive and unverified, every imported project is a draft, and admin ownership/accuracy review is required before publication.

Files:

- `scripts/research_professionals.php` refreshes the auditable JSON/CSV dataset.
- `database/seeds/researched-professionals-2026-09-25.json` is the import dataset.
- `database/migrations/2026_09_25_researched_professionals.sql` adds source tracking and removes only the known demo seed slugs.
- `scripts/import_researched_professionals.php` performs an idempotent import without overwriting existing registrations.

Review and import:

```bash
php tests/researched_professionals.php
php scripts/import_researched_professionals.php
php scripts/import_researched_professionals.php --apply --cleanup-dummy
```

The first importer command is a dry run. Use `--apply` only after reviewing the JSON/CSV source audit. The `--cleanup-dummy` option removes the exact dummy profiles from the old Gurgaon seed and relies on existing foreign keys to remove their demo portfolios and reviews.

## Hostinger Git Sync

Use one of these deployment layouts:

1. Recommended:
   - Repository path/docroot points to `homeinteriors/public`
   - Keep `homeinteriors/src` and `homeinteriors/database` in deployment
2. Flat layout (`public_html` contains `index.php`, `src`, `assets`):
   - The bootstrap loader already supports this layout too.

## Quotation Builder Subscription Reconciliation

The designer dashboard keeps quotation creation locked until a designer authorises the trial subscription. The initial subscription Checkout creates a ₹2 authorisation/upfront payment and schedules ₹999 monthly billing after 15 days. A Razorpay `payment.failed`, halted, or cancelled subscription webhook locks new quotation creation immediately.

Also configure this Hostinger Cron Job in hPanel so payment status is reconciled every night at 12:00 AM India time. The production server uses UTC, so set the schedule to `30 18 * * *`:

```bash
/usr/bin/php /home/u927315402/domains/homeinteriors360.com/public_html/scripts/reconcile_designer_subscriptions.php >> /home/u927315402/domains/homeinteriors360.com/subscription-reconcile.log 2>&1
```

The script is not web-accessible. It checks subscriptions with a due billing cycle against Razorpay. A payment that did not become active is cancelled and the designer retains dashboard access but cannot create another quotation.

Configure the Razorpay webhook URL as `https://homeinteriors360.com/api/razorpay/quotation-subscription-webhook` and enable subscription and payment-failure events.

## Deploying a targeted fix

Use `scripts/deploy-file.sh` to upload only the files changed by a fix to the matching location in Hostinger's `public_html` folder. Files in local `public/` are uploaded directly into `public_html/`; application files such as `src/...` retain their full path. The script automatically reads the ignored project `.env`; never commit credentials.

```bash
cd /Users/jitenderkumar/interiordesign360/homeinteriors
./scripts/deploy-file.sh --dry-run public/assets/feature-overrides.css
./scripts/deploy-file.sh public/assets/feature-overrides.css src/Views/partials/header.php
```

The script refuses `.env` files and Git metadata, and creates required remote folders automatically.

## Automatic production deployment

`.github/workflows/deploy-homeinteriors360.yml` deploys changed application files whenever a commit is pushed to `main`. Configure these repository secrets once in GitHub: `FTP_HOST`, `FTP_USER`, `FTP_PASSWORD`, and optionally `FTP_REMOTE_ROOT` (default: `/` for the HomeInteriors360 FTP account). The workflow uses the same guarded deployment script as local deploys and never writes credentials to the repository or build log.

Database migrations are deliberately **not** run automatically by the workflow. Apply each migration through the approved production migration process after reviewing it.

### Designer memberships

Apply `database/migrations/2026_11_21_designer_memberships.sql` before releasing the designer membership screen. It seeds the Free Listing, Pro Member, and Pro + Featured plan records. Paid plan checkout uses Razorpay Subscriptions; configure the four `RAZORPAY_DESIGNER_*_PLAN_ID` values in the production environment with matching monthly and yearly Razorpay Plan IDs. The existing BOQ / Quotation Builder subscription remains separate and continues to use `RAZORPAY_QUOTATION_PLAN_ID`.

## Default admin

- username: `admin`
- password: `admin123`

Change this immediately in production.

## Notes

- Pretty URL routing uses `public/.htaccess`.
- Auth is session-based with signed cookie fallback (for shared-hosting session edge cases).
- Super admin-only routes require user role = `super_admin`.
