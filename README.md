# Smart Home Access Control Dashboard

A comprehensive Laravel-based smart home control system with role-based and rule-based access control, audit logging, and backup/recovery capabilities.

## Features

- **Role-Based Access Control (RBAC)**: Three roles (Owner, Family, Guest) with hierarchical permissions
- **Rule-Based Access Control (RuBAC)**: Time-based and context-based access restrictions
- **Device Management**: Control lights, locks, thermostats, cameras, and more
- **Audit Logging**: Complete audit trail of all actions with user, device, action, and timestamp
- **Backup & Recovery**: Automated daily backups and manual restore functionality
- **Modern UI**: Clean, responsive dashboard built with Laravel Breeze and Tailwind CSS

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

4. **Update `.env` file** with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smarthome
DB_USERNAME=your_username
DB_PASSWORD=your_password
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

### Roles & Permissions

- **Owner**: Full system access, can create/edit/delete devices and rules
- **Family**: Can control most devices, view activity logs
- **Guest**: Limited access with time-based restrictions (e.g., cannot unlock doors after 10 PM)

### Device Control

1. Navigate to **Devices** from the dashboard
2. Click on a device to view details
3. Use control buttons to turn on/off, lock/unlock, etc.
4. All actions are logged and checked against rules

### Rules Management (Owner Only)

1. Navigate to **Rules** from the dashboard
2. Create rules with conditions:
   - **Time Window**: Restrict actions during specific hours
   - **Day of Week**: Restrict actions on specific days
   - **Always**: Apply rule at all times
3. Set effect to **Allow** or **Deny**

### Audit Logs

- View all activity logs in the **Activity Logs** section
- Filter by user, device, action, status, and date range
- Export logs as JSON (Owner only)

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

```
app/
├── Console/Commands/      # Backup & Restore commands
├── Http/
│   ├── Controllers/       # Device, Dashboard, AuditLog, Rule controllers
│   └── Middleware/        # CheckRole middleware
├── Models/                # User, Role, Device, Rule, AuditLog models
├── Policies/              # DevicePolicy for authorization
└── Services/              # RulesEngine, AuditLogService
database/
├── migrations/            # Database migrations
└── seeders/              # Role, Device, Rule seeders
resources/
└── views/                # Blade templates
routes/
└── web.php               # Application routes
```

## Security Features

- Role-based access control with hierarchy
- Rule-based restrictions (time windows, device-specific)
- Complete audit logging of all actions
- CSRF protection on all forms
- Password hashing
- Authorization policies

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
