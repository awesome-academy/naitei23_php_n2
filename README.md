
# Workspace Booking System (Project Naitei23 PHP N2)

Đây là source code cho dự án đặt phòng làm việc (Workspace Booking)
Hướng dẫn để cài đặt môi trường sau khi Clone code về.

---

## Quy trình

Sau khi `git clone` dự án về, hãy mở Terminal tại thư mục dự án và chạy lần lượt các bước sau:

Anh em tu fix loi lien quan den composer install (Neu co)
```bash
composer install
npm install
npm run build
```
Sau do sua file .env

```bash
DB_DATABASE=workspace_booking
DB_USERNAME=root                  
DB_PASSWORD=        
```
Sau do tao app key: 
```bash
php artisan key:generate
```

Migrate and run:
```bash
php artisan migrate
php artisan serve
```




