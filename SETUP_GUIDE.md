# Smart Home Access Control Dashboard - Setup Guide

## Quick Start

1. **Install Dependencies**:
```bash
composer install
npm install
```

2. **Configure Environment**:
```bash
cp .env.example .env
php artisan key:generate
```

3. **Update Database Settings in `.env`**:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smarthome
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

4. **Run Migrations & Seeders**:
```bash
php artisan migrate:fresh --seed
```

5. **Build Frontend**:
```bash
npm run build
```

6. **Start Server**:
```bash
php artisan serve
```

7. **Access Application**:
   - Open: http://localhost:8000
   - Login with: `owner@smarthome.local` / `password`

## Default Test Accounts

| Role | Email | Password | Permissions |
|------|-------|----------|-------------|
| Owner | owner@smarthome.local | password | Full access, can manage devices and rules |
| Family | family@smarthome.local | password | Can control most devices, view logs |
| Guest | guest@smarthome.local | password | Limited access, time-restricted |

## Features Overview

### 1. Role-Based Access Control (RBAC)
- **Owner** (Hierarchy 3): Full system control
- **Family** (Hierarchy 2): Partial device access
- **Guest** (Hierarchy 1): Limited, time-restricted access

### 2. Rule-Based Access Control (RuBAC)
- Time-based restrictions (e.g., guests cannot unlock doors after 10 PM)
- Device-specific rules
- Action-specific rules
- Role-specific rules

### 3. Device Management
- Create, edit, delete devices (Owner only)
- Control devices (based on role permissions)
- Device types: lights, locks, thermostats, cameras, doors, sensors, control panels

### 4. Audit Logging
- All actions are logged with:
  - User who performed the action
  - Device affected
  - Action type
  - Status (allowed/denied)
  - Timestamp
  - IP address and user agent
  - Additional metadata

### 5. Backup & Recovery
- Manual backup: `php artisan system:backup`
- Restore: `php artisan system:restore storage/backups/backup_YYYY-MM-DD_HH-MM-SS.zip`
- Automatic daily backups at 2 AM

## Testing Scenarios

### Test 1: Guest Time Restriction
1. Log in as `guest@smarthome.local`
2. Try to unlock a door after 10 PM
3. Should be denied with message: "Guests cannot unlock doors between 10 PM and 6 AM"

### Test 2: Family Access
1. Log in as `family@smarthome.local`
2. Should see most devices (except owner-only devices)
3. Can control lights, locks, etc.
4. Cannot create/edit devices or rules

### Test 3: Owner Full Access
1. Log in as `owner@smarthome.local`
2. Can create/edit/delete devices
3. Can create/edit/delete rules
4. Can view all audit logs
5. Can export logs

## Artisan Commands

### Backup System
```bash
php artisan system:backup
# Creates backup in storage/backups/backup_YYYY-MM-DD_HH-MM-SS.zip
```

### Restore System
```bash
php artisan system:restore storage/backups/backup_2025-11-22_12-00-00.zip
# Restores devices, logs, and rules from backup
```

## Project Architecture

### Models
- **User**: Extends Laravel's Authenticatable, has role relationship
- **Role**: Defines user roles with hierarchy
- **Device**: Smart home devices with status and settings
- **Rule**: Access control rules with conditions
- **AuditLog**: Action logs with full context

### Services
- **RulesEngine**: Evaluates rules and permissions
- **AuditLogService**: Handles logging of all actions

### Middleware
- **CheckRole**: Validates user roles for route access

### Controllers
- **DashboardController**: Main dashboard with statistics
- **DeviceController**: Device CRUD and control
- **AuditLogController**: View and export logs
- **RuleController**: Rule management (Owner only)

## Security Considerations

1. **Password Security**: All passwords are hashed using bcrypt
2. **CSRF Protection**: All forms include CSRF tokens
3. **Authorization**: Policies and middleware enforce access control
4. **Audit Trail**: All actions are logged for security monitoring
5. **Input Validation**: All inputs are validated using Laravel's validation

## Troubleshooting

### Issue: Migration fails
**Solution**: Ensure database exists and credentials in `.env` are correct

### Issue: Seeder fails
**Solution**: Run migrations first, then seeders separately:
```bash
php artisan migrate
php artisan db:seed
```

### Issue: Assets not loading
**Solution**: Build assets:
```bash
npm run build
# Or for development:
npm run dev
```

### Issue: Permission denied errors
**Solution**: Check file permissions:
```bash
chmod -R 775 storage bootstrap/cache
```

## Next Steps

1. Customize device types and locations
2. Add more rules based on your needs
3. Configure scheduled backups
4. Set up email notifications (optional)
5. Deploy to production server

## Support

For Laravel-specific issues, refer to [Laravel Documentation](https://laravel.com/docs)


