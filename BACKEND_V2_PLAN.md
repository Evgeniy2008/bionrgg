# Backend v2 Architecture (Draft)

## Goals
- Consolidate all profile and company logic behind a consistent REST/JSON API.
- Eliminate ad-hoc procedural scripts; move to layered services with clear responsibilities.
- Preserve existing database schema (see `create-database-complete.sql`) while tightening constraints and validation.
- Support company creation, membership, unified design propagation, and profile layouts with predictable behaviour.
- Provide a foundation for future features (invitations, audit logs, role management).

## High-Level Structure
- `public/api/index.php` – single entry point, handles routing and JSON responses.
- `src/Bootstrap.php` – initialises autoloading, environment, error handling.
- `src/Config/Env.php` – reads configuration (DB creds, CORS, uploads) from `.env`/constants.
- `src/Database/ConnectionFactory.php` – PDO-based connection management with transactions.
- `src/Http/Router.php` – minimal router matching method + path to controller actions.
- `src/Http/JsonResponse.php` – standardised success/error payloads.
- `src/Domain` – entities/value objects (`User`, `Company`, `CompanyDesign`, `CompanyMember`, etc.).
- `src/Repositories` – DB access via prepared statements, no business logic.
- `src/Services` – business rules (registration, authentication, company lifecycle, unified design synchronisation).
- `src/Validation` – centralised request validation with reusable rules.
- `src/Controllers` – thin HTTP layer, parse request, invoke services, return response DTOs.
- `src/Exceptions` – typed exceptions for validation/auth/forbidden/not-found with automatic HTTP mapping.

## Key API Endpoints
| Method | Path | Description |
| --- | --- | --- |
| POST | `/auth/register` | Create personal/company profile, optional join by key. |
| POST | `/auth/login` | Authenticate and return session token (JWT or signed session id). |
| POST | `/companies` | Create company (owner only). |
| GET | `/companies/{id}` | Fetch company details including members/design (auth required). |
| POST | `/companies/join` | Join company by key (auth required). |
| PATCH | `/companies/{id}/settings` | Toggle unified design, rename company (owner). |
| PATCH | `/companies/{id}/design` | Update design and propagate if unified. |
| DELETE | `/companies/{id}` | Delete company (owner). |
| DELETE | `/companies/{id}/members/{userId}` | Remove member (owner). |
| GET | `/profiles/{username}` | Fetch public profile. |
| PATCH | `/profiles/{username}` | Update profile (respect unified design lock). |

> **Auth Strategy:** initially reuse username/password request auth, but isolate in `AuthService` to swap for JWT later. Responses will include short-lived token if front-end adapts; fallback basic auth supported for backwards compatibility.

## Data Flow Notes
- All controllers resolve the authenticated user via `AuthService::requireUser(Request $req)`.
- Company operations verify membership/role via `CompanyService`.
- Unified design flag triggers `DesignSyncService::applyToMembers($companyId)` using a repo-backed queue to update members in a transaction.
- File/media inputs are handled by `MediaService`, storing either base64 blobs or migrating to `uploads/company/` files referencing relative paths.

## Backwards Compatibility
- Legacy scripts remain temporarily under `api/legacy/` to avoid breaking current front-end; new router served at `/api/v2/`.
- Gradual migration: front-end switches endpoint by endpoint.
- Shared helpers (e.g. `sendJSONResponse`) replaced by `JsonResponse`.

## Next Steps
1. Scaffold `src/` structure with PSR-4 autoloading (composer or custom autoloader).
2. Implement bootstrap, router, and standard response handling.
3. Port registration/login to services + controllers.
4. Implement company CRUD + unified design sync.
5. Expose new endpoints via `public/api/index.php` and add minimal integration tests (PHPUnit/Lumen).
6. Document migration & API usage in `docs/`.

---

## Implemented Components (Status Update)
- ✅ `src/Bootstrap.php` + PSR-4 autoloader.
- ✅ HTTP layer (`Request`, `Router`, `JsonResponse`) with global error handling.
- ✅ Repositories for `users`, `users_info`, `companies`, `company_members`, `company_designs`.
- ✅ Services: `RegistrationService`, `AuthService`, `CompanyService`, `DesignService`.
- ✅ Controllers: `AuthController`, `CompanyController`.
- ✅ Single entry point `api/v2/index.php` with clean REST routing.

## API Usage
### Authentication
- **POST `/api/v2/auth/register`**
  ```json
  {
    "username": "owner1",
    "password": "SuperSecret1",
    "profile_type": "company",
    "company_name": "Acme Corp",
    "description": "Optional bio",
    "profile_color": "#c27eef",
    "text_color": "#ffffff",
    "media": {
      "avatar": "base64…",
      "bg": null,
      "blockImage": null
    }
  }
  ```
- **POST `/api/v2/auth/login`**
  ```json
  { "username": "owner1", "password": "SuperSecret1" }
  ```

### Company Management
- **POST `/api/v2/companies`** – body must include authenticated `username`/`password` + `company_name`.
- **POST `/api/v2/companies/join`** – body: `username`, `password`, `company_key`.
- **GET `/api/v2/companies/{id}?username=...&password=...`** – returns company info, members, design (if allowed).
- **PATCH `/api/v2/companies/{id}/settings`** – body: `username`, `password`, optional `unified_design_enabled`, `company_name`.
- **PATCH `/api/v2/companies/{id}/design`** – body: `username`, `password`, any subset of design fields (`profileColor`, `profileOpacity`, etc.). Values are sanitised server-side.
- **DELETE `/api/v2/companies/{id}`** – body: `username`, `password`.
- **DELETE `/api/v2/companies/{id}/members/{username}`** – body: `username`, `password` of owner.

> All write operations expect credentials in the JSON body (temporary scheme until token auth is added).

## Migration / Integration Checklist
1. Deploy SQL schema from `create-database-complete.sql` (already compatible).
2. Point front-end to new endpoints (e.g. `fetch('/api/v2/auth/register', …)`).
3. Update client forms to send JSON payloads and include credentials where required.
4. Keep legacy PHP scripts under `api/` during transition; they can coexist.
5. Once front-end fully migrates, decommission old endpoints and remove redundant helpers.

## Legacy Compatibility
- Legacy scripts untouched; new API lives under `/api/v2/`.
- Shared DB schema ensures existing data remains valid.
- Unified design propagation now centralised in `DesignService`, eliminating duplicated SQL logic.

## Future Enhancements
- Replace credential-in-body auth with JWT/session tokens.
- Add PHPUnit request/feature tests.
- Extend validation rules (e.g., custom rule objects, more social fields).
- Implement invitation flows and audit logs.


