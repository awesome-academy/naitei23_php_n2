
# Workspace Booking System

## Setup Instructions
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
php artisan migrate:fresh --seed // nếu muốn reset db
php artisan serve
```

## Tài khoản demo
**Admin:** admin@workspace.com / admin123  
- Quản lý toàn hệ thống (approve venues, manage users)

**Owner:** owner@workspace.com / password  
- Sở hữu venues (ID: 1,2,3)
- Quản lý spaces, confirm/reject bookings
- Ủy quyền managers

**Manager 1:** manager1@workspace.com / password  
- Được ủy quyền quản lý Venue #1, #3

**Manager 2:** manager2@workspace.com / password  
- Được ủy quyền quản lý Venue #1, #2

**User:** user@workspace.com / password  
- Tạo booking, thanh toán
- Xem venues/spaces công khai


