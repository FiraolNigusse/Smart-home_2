# Smart Home Access Control Dashboard

A comprehensive Laravel-based smart home control system with role-based and rule-based access control, audit logging, and backup/recovery capabilities.

## Features

- **Mandatory Access Control (MAC)**: Data classifications (Public, Internal, Confidential) with clearance checks and admin-only label management.
- **Discretionary Access Control (DAC)**: Device owners can grant fine-grained permissions (view/control/actions) with automatic permission logging.
- **Role-Based Access Control (RBAC)**: Owner/Family/Guest roles with hierarchy, plus self-service role change requests and admin approval workflow.
- **Rule-Based Access Control (RuBAC)**: Time, location, device-type, and attribute-aware rules that can allow or deny actions dynamically.
- **Attribute-Based Access Control (ABAC)**: Attribute profiles (department, location, employment status, custom attributes) evaluated through a policy decision service (`config/access.php`).
- **Device Management**: Control lights, locks, thermostats, cameras, and more through an audited dashboard.
- **Audit & System Logging**: Action logs, permission logs, role-change audits, and encrypted system event logs with centralized storage + alerting.
- **Authentication Hardening**: Email verification, CAPTCHA, MFA (email OTP), password complexity policies, account lockout, HTTPS enforcement, API tokens, and biometric hooks.
- **Backup & Recovery**: Automated daily backups plus manual backup/restore commands.
- **Modern UI**: Clean, responsive dashboard built with Laravel Breeze and Tailwind CSS.

## Requirements

- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL/PostgreSQL/SQLite

## Installation

1. **Clone the repository** (or navigate to the project directory)

2. **Install dependencies**:

   ```bash
   composer install
   npm install
   ```

3. **Configure environment**:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Update `.env` file** with your database credentials and security flags (optional):

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=smarthome
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   FORCE_HTTPS=true
   ALERT_MIN_SEVERITY=warning
   LOGIN_MAX_ATTEMPTS=5
   LOGIN_DECAY_SECONDS=300
   CAPTCHA_ENABLED=true
   MFA_REQUIRED=true
   ```

5. **Run migrations and seeders**:

   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build frontend assets**:

   ```bash
   npm run build
   ```

7. **Start the development server**:

   ```bash
   php artisan serve
   ```

## Default Users

After seeding, you can log in with:

- **Owner**: `owner@smarthome.local` / `password`
- **Family**: `family@smarthome.local` / `password`
- **Guest**: `guest@smarthome.local` / `password`

## Usage

### Access Control Overview

- **MAC**: Each device carries a sensitivity label. Users inherit clearance from their role (owners can edit classifications).
- **DAC**: Owners can grant or revoke device permissions per user, including allowed actions and expiration dates.
- **RBAC**: Roles dictate baseline permissions. Users can submit role change requests; owners review/approve them.
- **RuBAC**: Rules can reference time windows, days, device types, locations, or user attributes for deny/allow decisions.
- **ABAC**: Update your profile with department, location, and employment status; policies combine these attributes at runtime.

### Roles & Permissions

- **Owner**: Full system access, manages devices, rules, MAC/DAC settings, and role requests.
- **Family**: Can control most devices, view activity logs, and request elevated roles.
- **Guest**: Limited, time-restricted access; may receive DAC overrides for specific devices.

### Device Control

1. Navigate to **Devices** from the dashboard
2. Click on a device to view details
3. Use control buttons to turn on/off, lock/unlock, etc.
4. All actions are logged and checked against rules

### Rules Management (Owner Only)

1. Navigate to **Rules** from the dashboard.
2. Choose a condition type:
   - `time_window`, `day_of_week`, `location`, `device_type`, `attribute`, or `always`.
3. Provide JSON parameters (see inline helpers in the form).
4. Set the rule effect to **Allow** or **Deny** and specify optional denial messaging.

### Audit & System Logs

- **Activity Logs**: Filter by user, device, action, status, and date range. Export as JSON (Owner only).
- **Permission Logs**: Every DAC grant/revoke is logged with actor, target, and change detail.
- **System Logs**: Critical system events (startup, shutdown, device changes, role approvals) are centrally stored with optional encrypted context and e-mail alerts for warning/critical events.

### Authentication & Security Hardening

- **Email Verification**: New accounts must confirm their email before accessing protected areas.
- **CAPTCHA**: Login and registration forms include a built-in challenge to reduce automated attacks.
- **Password Policies**: Minimum 12 characters, mixed case, numeric, symbol, and breached password checks.
- **Account Lockout**: Configurable rate-limited login attempts with informative feedback.
- **Multi-Factor Authentication (MFA)**: Email-based one-time passcode required after password authentication.
- **API Tokens**: Use Laravel Sanctum to generate scoped tokens from the **API Tokens** page; sample API endpoints live under `/api`.
- **Biometric Hooks**: Store public key payloads for WebAuthn/FIDO-style authenticators under **Biometrics**.
- **HTTPS Enforcement**: Optional global HTTPS redirect via `FORCE_HTTPS=true`.

### Backup & Recovery

**Create Backup**:

```bash
php artisan system:backup
```

**Restore from Backup**:

```bash
php artisan system:restore storage/backups/backup_2025-11-22_12-00-00.zip
```

**Scheduled Backups**: Daily backups run automatically at 2 AM (configured in `bootstrap/app.php`)

## Project Structure

```text
app/
├── Console/Commands/         # Backup & restore commands
├── Http/
│   ├── Controllers/          # Dashboard, Devices, Rules, Audit logs, Role requests
│   └── Middleware/           # Role enforcement
├── Models/                   # User, Role, Device, Rule, SensitivityLevel, DevicePermission, etc.
├── Policies/                 # DevicePolicy and future policies
└── Services/
    ├── AccessDecisionService # MAC + DAC + RBAC + RuBAC + ABAC orchestration
    ├── AttributePolicyService# ABAC policy evaluation
    ├── AuditLogService       # Action logging helpers
    └── SystemLogService      # Encrypted system event logging
config/
└── access.php                # Sensitivity levels & ABAC policy definitions
database/
├── migrations/               # Tables for MAC/DAC/RBAC/ABAC, logging, backups
└── seeders/                  # Sensitivity, role, device, rule, user seeds
resources/views/
├── devices/                  # MAC/DAC-aware UI
├── rules/                    # Rule builder/editor
└── roles/requests            # Role change workflow
routes/web.php                # Routes for all modules
```

## Security Features

- Mandatory, Discretionary, Role-based, Rule-based, and Attribute-based access controls.
- Device-level sensitivity labels and user clearance checks.
- Discretionary permission grants with full audit trail.
- Role change request workflow with approvals and logging.
- Complete audit logging of all user actions plus encrypted system event logs + alerting.
- Email verification, CAPTCHA, MFA, strong password policies, account lockout, and HTTPS enforcement.
- API tokens (Sanctum) and biometric credential hooks for future WebAuthn integrations.
- CSRF protection, hashed passwords, and authorization policies.

## Testing

Test the system with different user roles:

1. Log in as **Guest** and try to unlock a door after 10 PM (should be denied)
2. Log in as **Family** and control devices (should work for most)
3. Log in as **Owner** and create new devices/rules

## Troubleshooting

**Migration errors**: Make sure your database is created and credentials are correct in `.env`

**Permission errors**: Check that storage directories are writable:

```bash
chmod -R 775 storage bootstrap/cache
```

**Asset build errors**: Make sure Node.js is installed and run:

```bash
npm install
npm run build
```

## License

This project is open-sourced software licensed under the MIT license.

## Support

For issues or questions, please check the Laravel documentation or create an issue in the repository.
