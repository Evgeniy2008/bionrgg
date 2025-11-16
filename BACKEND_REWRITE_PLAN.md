# Bionrgg Backend Redesign — Implementation Blueprint

## 1. Stack & Principles
- **Stack**: PHP 8.x, MySQL 8.x (phpMyAdmin), vanilla JS fetch API on the frontend.
- **Architecture**: Layered (HTTP → Controllers → Services → Repositories → Database), dependency-injected helpers, JSON responses everywhere.
- **State**: Stateless API with session tokens (HTTP-only cookies) plus password reset tokens.
- **Security**: Passwords hashed with `password_hash` (bcrypt/argon2id), CSRF tokens for form posts, input validation/sanitation, upload limits (≤10 MB).
- **Media handling**: Store files under `/uploads` with generated names, store metadata in DB.
- **Internationalisation**: Language field per profile (`uk`/`en`) + static translations on UI.

## 2. Database Schema (initial draft)

```text
users
-----
id              INT PK AI
email           VARCHAR(191) UNIQUE
password_hash   VARCHAR(255)
full_name       VARCHAR(255)
role            ENUM('user','admin') DEFAULT 'user'
is_verified     TINYINT(1) DEFAULT 0
created_at      TIMESTAMP
updated_at      TIMESTAMP

user_profiles
-------------
id                INT PK AI
user_id           INT FK → users.id ON DELETE CASCADE
username_slug     VARCHAR(50) UNIQUE
first_name        VARCHAR(100)
last_name         VARCHAR(100)
position_title    VARCHAR(150)
bio               TEXT
phone             VARCHAR(30)
email_public      VARCHAR(191)
address           VARCHAR(255)
avatar_path       VARCHAR(255)
background_path   VARCHAR(255)
design_theme      ENUM('minimal','card') DEFAULT 'minimal'
language          ENUM('uk','en') DEFAULT 'uk'
qr_svg_path       VARCHAR(255)
pdf_path          VARCHAR(255)
created_at        TIMESTAMP
updated_at        TIMESTAMP

social_links
------------
id              INT PK AI
profile_id      INT FK → user_profiles.id ON DELETE CASCADE
platform        VARCHAR(50)
label           VARCHAR(100)
url             VARCHAR(255)
sort_order      INT DEFAULT 0
created_at      TIMESTAMP

organizations
-------------
id                INT PK AI
name              VARCHAR(255)
slug              VARCHAR(60) UNIQUE
description       TEXT
contact_email     VARCHAR(191)
contact_phone     VARCHAR(30)
address           VARCHAR(255)
logo_path         VARCHAR(255)
invite_code       CHAR(10) UNIQUE
design_theme      ENUM('minimal','card') DEFAULT 'minimal'
design_config     JSON
created_at        TIMESTAMP
updated_at        TIMESTAMP
status            ENUM('pending','approved','rejected') DEFAULT 'pending'

organization_members
--------------------
id               INT PK AI
organization_id  INT FK → organizations.id ON DELETE CASCADE
user_id          INT FK → users.id ON DELETE CASCADE
role             ENUM('owner','admin','member') DEFAULT 'member'
joined_at        TIMESTAMP

password_resets
---------------
id            INT PK AI
user_id       INT FK → users.id ON DELETE CASCADE
token         CHAR(64) UNIQUE
expires_at    DATETIME
created_at    TIMESTAMP

sessions
--------
id            INT PK AI
user_id       INT FK → users.id ON DELETE CASCADE
session_token CHAR(64) UNIQUE
user_agent    VARCHAR(255)
ip_address    VARCHAR(45)
expires_at    DATETIME
created_at    TIMESTAMP

admin_logs
----------
id            INT PK AI
admin_id      INT FK → users.id
action        VARCHAR(100)
target_type   ENUM('user','organization','system')
target_id     INT NULL
meta          JSON
created_at    TIMESTAMP
```

> All tables use utf8mb4, InnoDB, and FK constraints. `design_config` will hold per-organization colors/toggles; members can choose to adopt org design via UI, but backend no longer copies appearance automatically.

## 3. API Surface (v1)

### Auth
- `POST /api/v1/auth/register` — email, password, full_name, optional slug → creates user & empty profile.
- `POST /api/v1/auth/login` — sets session cookie, returns user summary.
- `POST /api/v1/auth/logout` — destroys session.
- `POST /api/v1/auth/password/forgot` — send reset link.
- `POST /api/v1/auth/password/reset` — apply token.

### Profiles
- `GET /api/v1/profiles/@{slug}` — public profile data (for public page).
- `GET /api/v1/me/profile` — auth; returns editable data.
- `PUT /api/v1/me/profile` — update bio/contact/design/language.
- `POST /api/v1/me/profile/avatar` — upload avatar (≤10 MB).
- `POST /api/v1/me/profile/background` — upload background (≤10 MB).
- `POST /api/v1/me/profile/social-links` — bulk upsert; or `POST/PUT/DELETE` single link.
- `GET /api/v1/me/profile/export/pdf` — generate + return PDF download link.
- `GET /api/v1/me/profile/export/qrcode` — return SVG/PNG data.

### Organizations
- `POST /api/v1/organizations` — create org, auto owner.
- `GET /api/v1/organizations/{id}` — auth; details for owner/admin.
- `PUT /api/v1/organizations/{id}` — update info/design (owner/admin).
- `POST /api/v1/organizations/{id}/invite` — generate/refresh invite code.
- `POST /api/v1/organizations/join` — join via invite code.
- `GET /api/v1/organizations/{id}/members` — list members + roles.
- `PUT /api/v1/organizations/{id}/members/{userId}` — change role (owner only).
- `DELETE /api/v1/organizations/{id}/members/{userId}` — remove member.

### Admin
- `GET /api/v1/admin/users` — list/search users.
- `GET /api/v1/admin/organizations` — list/search orgs.
- `PUT /api/v1/admin/organizations/{id}/status` — approve/reject.
- `DELETE /api/v1/admin/users/{id}` — remove user (soft delete?).
- `GET /api/v1/admin/logs` — audit trail.

## 4. Implementation Milestones

1. **Bootstrap backend skeleton**  
   - Autoloader, env config, DB connection pool, response helpers, error handling.  
   - Routing map (simple router or lightweight framework).

2. **Auth module**  
   - Registration/login/logout  
   - Sessions table + middleware  
   - Password reset flow

3. **Profile module**  
   - CRUD + social links  
   - Media uploads (avatar/background) with validation and storage helper  
   - QR / PDF generators (use Google Charts API or local libraries like `endroid/qr-code`, `dompdf/dompdf`)

4. **Organization module**  
   - CRUD, member management, invite codes, design config  
   - Enforcement of roles/permissions

5. **Admin module**  
   - Secure area (admin role)  
   - Approval flows and audit logging

6. **Localization + UI bindings**  
   - Provide translation keys in API responses  
   - Ensure fetch endpoints match existing UI calls; add JS adapters if needed

7. **Testing & hardening**  
   - Unit tests (PHPUnit) for services  
   - Integration tests for key API flows  
   - Manual run-through with existing frontend

8. **Deployment aids**  
   - SQL migrations (initial + seeds)  
   - `.env.example`, README instructions, phpMyAdmin import steps

## 5. Next Actions
1. Build SQL migration scripts (`database/schema.sql`).  
2. Scaffold new PHP backend structure under `backend/` (or reuse `src/`).  
3. Implement authentication flow first, wire to UI login/register forms.  
4. Iterate module by module per milestones.

Once this plan is approved, we proceed with schema DDL and code scaffolding.






