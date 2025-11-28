# Smart Home Access Control Dashboard — Setup Guide

## Quick Start

1. **Install dependencies**

   ```bash
   composer install
   npm install
   ```

2. **Configure environment**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Update database settings in `.env`**

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=smarthome
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

4. **Run migrations & seeders**

   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build frontend**

   ```bash
   npm run build
   ```

6. **Start server**

   ```bash
   php artisan serve
   ```

7. **Access application**

   - Open: <http://localhost:8000>
   - Log in with: `owner@smarthome.local` / `password`

## Default Test Accounts

| Role   | Email                   | Password | Permissions                                      |
|--------|-------------------------|----------|--------------------------------------------------|
| Owner  | owner@smarthome.local  | password | Full access, manage devices/rules/MAC/DAC.       |
| Family | family@smarthome.local | password | Control most devices, submit role requests.      |
| Guest  | guest@smarthome.local  | password | Limited, time/location-restricted access.        |

## Features Overview

### 1. Mandatory Access Control (MAC)

- Sensitivity labels (Public/Internal/Confidential) stored in `sensitivity_levels`.
- Roles include clearance; devices carry a classification selected during create/edit.
- Only owners may adjust classifications to guarantee admin-only policy changes.

### 2. Discretionary Access Control (DAC)

- Owners can grant per-user permissions (view/control/specific actions with expiration).
- Grants/revokes recorded in `device_permissions` & `permission_logs`, plus system logs.

### 3. Role-Based Access Control (RBAC)

- Owner (3) / Family (2) / Guest (1) hierarchy.
- Users submit role change requests; owners approve/deny with decision notes.
- Every change is audited and tracked in `role_change_requests`.

### 4. Rule- & Attribute-Based Access Control (RuBAC + ABAC)

- Rules support `time_window`, `day_of_week`, `location`, `device_type`, and `attribute`.
- Attribute profiles (department, location, employment status, etc.) live in `user_attributes`.
- ABAC policies defined in `config/access.php` combine attributes, roles, and context.

### 5. Device Management

- Owners manage devices and assign sensitivity labels.
- Users control devices via MAC/RBAC/ABAC-enforced actions.
- DAC management (grant/revoke permissions) sits directly on each device detail page.

### 6. Audit & System Logging

- Activity logs capture user, device, action, status, timestamp, IP, and metadata.
- Permission changes, role decisions, and system events (boot/shutdown/config) logged centrally.
- `SystemLogService` encrypts sensitive payloads before storage.

### 7. Backup & Recovery

- Manual backup: `php artisan system:backup`
- Restore: `php artisan system:restore storage/backups/backup_YYYY-MM-DD_HH-MM-SS.zip`
- Automatic daily backups at 2 AM (see `bootstrap/app.php`)

## Testing Scenarios

### Test 1: Guest Time Restriction (RuBAC)

1. Log in as `guest@smarthome.local`.
2. Attempt to unlock a door after 22:00.
3. Expect denial: "Guests cannot unlock doors between 10 PM and 6 AM."

### Test 2: DAC Override

1. As owner, grant DAC access to the guest for a device.
2. As guest, confirm you can control the device despite hierarchy limits.
3. Verify permission and system logs show the override.

### Test 3: Role Change Workflow

1. As family, submit a role change request with justification.
2. As owner, approve or deny it with reviewer notes.
3. Confirm audit/system logs capture the outcome.

### Test 4: Attribute-Based Policy

1. Update profile attributes (e.g., Department = `Payroll`).
2. Trigger an ABAC-protected action (per `config/access.php`) to verify allow/deny.

### Test 5: Family Access Baseline

1. Log in as `family@smarthome.local`.
2. Ensure devices respect role hierarchy and MAC labels.
3. Confirm device editing remains restricted to owners.

### Test 6: Owner Capabilities

1. Log in as `owner@smarthome.local`.
2. Create/edit/delete devices and rules.
3. Review/export audit logs and check system event entries.

## Artisan Commands

### Backup System

```bash
php artisan system:backup
# Creates storage/backups/backup_YYYY-MM-DD_HH-MM-SS.zip
```

### Restore System

```bash
php artisan system:restore storage/backups/backup_2025-11-22_12-00-00.zip
# Restores devices, logs, rules, MAC/DAC metadata
```

## Project Architecture

### Models

- **User / Role / SensitivityLevel** — clearance hierarchy + MAC metadata.
- **Device** — classification, status, discretionary permissions.
- **Rule** — JSON-driven condition set for RuBAC/ABAC.
- **DevicePermission & PermissionLog** — DAC state and audit trail.
- **RoleChangeRequest** — RBAC workflow management.
- **UserAttribute** — ABAC attribute bag.
- **AuditLog & SystemLog** — user and system event tracking.

### Services

- **AccessDecisionService** — orchestrates MAC, DAC, RBAC, RuBAC, ABAC.
- **AttributePolicyService** — evaluates ABAC policies from `config/access.php`.
- **RulesEngine** — processes conditional rules with context.
- **AuditLogService / SystemLogService** — structured logging with encryption support.

### Middleware & Policies

- **CheckRole** — role-based route enforcement.
- **DevicePolicy** — restricts classification and device changes to owners.

### Controllers

- **DashboardController** — overview + stats.
- **DeviceController** — CRUD, control actions, MAC/DAC management.
- **AuditLogController** — activity viewer/exporter.
- **RuleController** — owner-only rule management.
- **RoleChangeRequestController** — submit/review workflow.

## Security Considerations

1. Passwords hashed with bcrypt.
2. CSRF tokens on all forms.
3. Comprehensive MAC/DAC/RBAC/RuBAC/ABAC enforcement.
4. Full audit trail for user/system events (with encrypted payload support).
5. Strict request validation for every form.

## Troubleshooting

### Migration Fails

Ensure the target database exists and check `.env` credentials.

### Seeder Fails

Run migrations before seeding:

```bash
php artisan migrate
php artisan db:seed
```

### Assets Not Loading

```bash
npm run build
# or for development
npm run dev
```

### Permission Errors

```bash
chmod -R 775 storage bootstrap/cache
```

## Next Steps

1. Customize devices, rules, and ABAC policies (`config/access.php`).
2. Integrate notifications (email/SMS) for role requests or alerts.
3. Extend authentication hardening (CAPTCHA, MFA, lockout policies).
4. Configure alerting on critical system events.
5. Deploy behind HTTPS with reverse proxy or load balancer.

## Support

Consult the [Laravel documentation](https://laravel.com/docs) for framework-specific guidance.

