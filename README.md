
# Workspace Booking System

## Setup
```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
```

Cấu hình `.env`:
```bash
DB_DATABASE=workspace_booking
DB_USERNAME=root
DB_PASSWORD=
```

Chạy migration & seeder:
```bash
php artisan migrate
php artisan db:seed
php artisan serve
```

## Tài khoản demo
**Admin:** admin@workspace.com / admin123  
**Owner:** owner@workspace.com / password


