# Database Setup

## 1. Database info

- Database name: `workspace_booking`
- DB user: `workspace_user`
- DB password: `workspace_pass`

## 2. Import schema từ file SQL

Chạy lệnh sau trong thư mục project:

```bash
mysql -u workspace_user -pworkspace_pass workspace_booking < database/sql/workspace_booking.sql
