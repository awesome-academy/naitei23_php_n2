# Owner Venue Management API

**Branch:** `feature/owner-venue-amenities`  
**Status:** Training Phase - Using FakeAuth for testing

---

## 1. Database Setup

### 1.1. Database Information

- **Database name:** `workspace_booking`
- **DB user:** `workspace_user` 
- **DB password:** `workspace_pass`

### 1.2. Import Schema

**File SQL chuẩn:** `database/sql/workspace_booking.sql`

```bash
mysql -u workspace_user -pworkspace_pass workspace_booking < database/sql/workspace_booking.sql
```

### 1.3. Key Indexes

Đã được tạo trong SQL file:

- `venues`:
  - `idx_venues_city_status` (city, status) - Composite index cho filter kết hợp
- `payments`:
  - `idx_payments_status` (transaction_status) - Filter payments theo trạng thái

---

## 2. Authentication (Training Phase)

### Current Implementation: FakeAuthMiddleware

**⚠️ Đây là middleware GIẢ LẬP để test, KHÔNG dùng production.**

- **Location:** `app/Http/Middleware/FakeAuthMiddleware.php`
- **Đăng ký:** `app/Http/Kernel.php` → `'fake.auth'`
- **Routes:** `routes/api.php` → middleware group `'fake.auth'`

**Cách sử dụng:**

Gửi request với query parameter `?user_id=<id>`:

```
GET http://localhost:8000/api/owner/venues?user_id=1
```

Middleware sẽ:
1. Lấy `user_id` từ query string
2. Tìm User trong database
3. Set `Auth::setUser($user)` để giả lập user đăng nhập

### Target Production: Sanctum

```md
🎯 Khi module Auth hoàn thành:
- Thay `fake.auth` → `auth:sanctum` trong routes
- Sử dụng `Authorization: Bearer <token>` header
- Xóa/disable FakeAuthMiddleware
```

---

## 3. Authorization (VenuePolicy)

**File:** `app/Policies/VenuePolicy.php`

### Rules:

| Method | Permission |
|--------|-----------|
| `view(user, venue)` | Owner của venue (`venue.owner_id == user.id`) HOẶC Admin |
| `create(user)` | Bất kỳ user đăng nhập nào |
| `update(user, venue)` | Owner của venue HOẶC Admin |
| `delete(user, venue)` | Owner của venue HOẶC Admin |

**User::isAdmin() implementation:**
```php
public function isAdmin(): bool
{
    return $this->roles()->where('role_name', 'admin')->exists();
}
```

---

## 4. API Endpoints

Base URL: `http://localhost:8000/api`

### 4.1. List Venues

**Endpoint:** `GET /api/owner/venues`

**Auth:** FakeAuth (query `?user_id=`)

**Query Parameters:**
- `user_id` (required, temporary): ID của owner

**Logic:**
- Lọc venues theo `owner_id` của user hiện tại
- Sắp xếp theo `created_at` DESC
- Paginate 10 items/page

**Response:** Laravel pagination format

```json
{
  "data": [
    {
      "id": 1,
      "name": "Sun* Coworking Space",
      "description": "Modern workspace in city center",
      "address": "123 Tech Street",
      "city": "Hanoi",
      "street": "Tech Street",
      "latitude": "21.028511",
      "longitude": "105.804817",
      "status": "approved",
      "created_at": "2025-11-27T10:00:00.000000Z",
      "updated_at": "2025-11-27T10:00:00.000000Z"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/owner/venues?page=1",
    "last": "http://localhost:8000/api/owner/venues?page=3",
    "prev": null,
    "next": "http://localhost:8000/api/owner/venues?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "path": "http://localhost:8000/api/owner/venues",
    "per_page": 10,
    "to": 10,
    "total": 25
  }
}
```

---

### 4.2. Create Venue

**Endpoint:** `POST /api/owner/venues`

**Auth:** FakeAuth (query `?user_id=`)

**Request Body:**

```json
{
  "name": "New Coworking Space",
  "description": "Description here",
  "address": "123 Street Name",
  "city": "Hanoi",
  "street": "Street Name",
  "latitude": 21.028511,
  "longitude": 105.804817
}
```

**Validation Rules:**
- `name`: required, string, max:255
- `address`: required, string, max:500
- `city`: required, string, max:100
- `street`: nullable, string, max:255
- `latitude`: required, numeric, between:-90,90
- `longitude`: required, numeric, between:-180,180
- `description`: nullable, string

**Auto-set Fields:**
- `owner_id`: Tự động set = user ID hiện tại
- `status`: Tự động set = `"pending"` (Venue::STATUS_PENDING)

**Response:** HTTP 201 Created

```json
{
  "id": 10,
  "name": "New Coworking Space",
  "description": "Description here",
  "address": "123 Street Name",
  "city": "Hanoi",
  "street": "Street Name",
  "latitude": "21.028511",
  "longitude": "105.804817",
  "status": "pending",
  "created_at": "2025-11-27T12:00:00.000000Z",
  "updated_at": "2025-11-27T12:00:00.000000Z"
}
```

---

### 4.3. Get Venue Details

**Endpoint:** `GET /api/owner/venues/{venue}`

**Auth:** FakeAuth (query `?user_id=`)

**Authorization:** 
- Chỉ owner của venue hoặc admin mới xem được
- Nếu không có quyền → HTTP 403 Forbidden

**Response:** HTTP 200 OK

```json
{
  "id": 1,
  "name": "Sun* Coworking Space",
  "description": "Modern workspace",
  "address": "123 Tech Street",
  "city": "Hanoi",
  "street": "Tech Street",
  "latitude": "21.028511",
  "longitude": "105.804817",
  "status": "approved",
  "created_at": "2025-11-27T10:00:00.000000Z",
  "updated_at": "2025-11-27T10:00:00.000000Z",
  "amenities": [
    {
      "id": 1,
      "amenity_name": "WiFi",
      "icon_url": "wifi.svg"
    },
    {
      "id": 2,
      "amenity_name": "Parking",
      "icon_url": "parking.svg"
    }
  ],
  "spaces": [
    {
      "id": 5,
      "venue_id": 1,
      "space_type_id": 1,
      "name": "Meeting Room A",
      "capacity": 10,
      "price_per_hour": "100000.00",
      "price_per_day": "500000.00",
      "price_per_month": "10000000.00",
      "open_hour": "08:00:00",
      "close_hour": "22:00:00",
      "created_at": "2025-11-27T10:00:00.000000Z",
      "updated_at": "2025-11-27T10:00:00.000000Z"
    }
  ]
}
```

**Note:** 
- `amenities` và `spaces` chỉ xuất hiện khi được eager load
- Sử dụng `whenLoaded()` trong VenueResource

---

### 4.4. Update Venue

**Endpoint:** `PUT /api/owner/venues/{venue}`

**Auth:** FakeAuth (query `?user_id=`)

**Authorization:** Chỉ owner hoặc admin

**Request Body:** (Tất cả fields đều optional)

```json
{
  "name": "Updated Name",
  "description": "Updated description",
  "city": "Danang"
}
```

**Protected Fields (không được sửa):**
- `owner_id`: Không cho phép thay đổi chủ sở hữu
- `status`: Không cho owner tự approve, chỉ admin mới đổi được

**Response:** HTTP 200 OK (VenueResource)

---

### 4.5. Delete Venue

**Endpoint:** `DELETE /api/owner/venues/{venue}`

**Auth:** FakeAuth (query `?user_id=`)

**Authorization:** Chỉ owner hoặc admin

**Response:** HTTP 200 OK

```json
{
  "message": "Venue deleted successfully"
}
```

**Note:** Hard delete, không phải soft delete

---

## 5. Additional APIs (Venue Related)

### 5.1. Amenities Management

**List Amenities:**
```
GET /api/amenities
```
Public endpoint, trả tất cả amenities.

**Get Venue's Amenities:**
```
GET /api/owner/venues/{venue}/amenities?user_id=1
```

**Sync Venue's Amenities:**
```
PUT /api/owner/venues/{venue}/amenities?user_id=1
Content-Type: application/json

{
  "amenity_ids": [1, 2, 3]
}
```

### 5.2. Services Management

**List Venue's Services:**
```
GET /api/owner/venues/{venue}/services?user_id=1
```

**Create Service:**
```
POST /api/owner/venues/{venue}/services?user_id=1
Content-Type: application/json

{
  "name": "Coffee Service",
  "description": "Free coffee all day",
  "price": 50000
}
```

**Update Service:**
```
PUT /api/owner/services/{service}?user_id=1
Content-Type: application/json

{
  "price": 80000
}
```

**Delete Service:**
```
DELETE /api/owner/services/{service}?user_id=1
```

### 5.3. Space Amenities Management

**Get Space's Amenities:**
```
GET /api/owner/spaces/{space}/amenities?user_id=1
```

**Sync Space's Amenities:**
```
PUT /api/owner/spaces/{space}/amenities?user_id=1
Content-Type: application/json

{
  "amenity_ids": [1, 3, 5]
}
```

---

## 6. Testing Guide

### 6.1. Setup Test Data

Tạo test user:
```sql
INSERT INTO users (full_name, email, password_hash, is_active, is_verified) 
VALUES ('Test Owner', 'owner@test.com', '$2y$10$...', 1, 1);
```

Lấy user ID (ví dụ: 1)

### 6.2. Test với wget

**List venues:**
```bash
wget -O - 'http://localhost:8000/api/owner/venues?user_id=1'
```

**Create venue:**
```bash
wget --header='Content-Type: application/json' \
     --post-data='{"name":"Test Venue","address":"123 St","city":"Hanoi","latitude":21.0,"longitude":105.8}' \
     'http://localhost:8000/api/owner/venues?user_id=1'
```

**Get venue detail:**
```bash
wget -O - 'http://localhost:8000/api/owner/venues/1?user_id=1'
```

**Update venue:**
```bash
wget --method=PUT \
     --header='Content-Type: application/json' \
     --body-data='{"name":"Updated Name"}' \
     'http://localhost:8000/api/owner/venues/1?user_id=1'
```

**Delete venue:**
```bash
wget --method=DELETE 'http://localhost:8000/api/owner/venues/1?user_id=1'
```

### 6.3. Test Authorization (403)

Với user khác không phải owner:
```bash
wget -O - 'http://localhost:8000/api/owner/venues/1?user_id=999'
# Expected: HTTP 403 Forbidden
```

### 6.4. Test Validation (422)

Missing required fields:
```bash
wget --header='Content-Type: application/json' \
     --post-data='{"name":"Test"}' \
     'http://localhost:8000/api/owner/venues?user_id=1'
# Expected: HTTP 422 Unprocessable Content
```

---

## 7. Response Status Codes

| Code | Meaning | When |
|------|---------|------|
| 200 | OK | Successful GET, PUT, DELETE |
| 201 | Created | Successful POST |
| 403 | Forbidden | User không có quyền (VenuePolicy) |
| 404 | Not Found | Venue không tồn tại |
| 422 | Unprocessable Content | Validation failed |
| 500 | Server Error | Lỗi server |

---

## 8. Models & Relationships

### Venue Model

**Constants:**
```php
const STATUS_PENDING = 'pending';
const STATUS_APPROVED = 'approved';
const STATUS_BLOCKED = 'blocked';
```

**Relationships:**
- `owner()` / `user()`: BelongsTo User
- `spaces()`: HasMany Space
- `amenities()`: BelongsToMany Amenity (pivot: venue_amenities)
- `services()`: HasMany Service
- `managers()`: BelongsToMany User (pivot: venue_managers)

**Fillable:**
```php
'owner_id', 'name', 'description', 'address', 
'city', 'street', 'latitude', 'longitude', 'status'
```

---

## 9. Next Steps / Migration to Production

### When Auth module is ready:

1. **Update routes/api.php:**
   ```php
   Route::middleware('auth:sanctum')  // Thay fake.auth
       ->prefix('owner')
       ->group(function () {
           // ... routes
       });
   ```

2. **Remove FakeAuthMiddleware:**
   - Delete `app/Http/Middleware/FakeAuthMiddleware.php`
   - Remove from `app/Http/Kernel.php` middlewareAliases

3. **Update documentation:**
   - Replace query `?user_id=` examples
   - Add `Authorization: Bearer <token>` header examples

4. **Test with real Sanctum tokens:**
   ```bash
   curl -H "Authorization: Bearer <token>" \
        http://localhost:8000/api/owner/venues
   ```

---

## 10. Checklist Self-Review

- [x] Database file: `workspace_booking.sql` tồn tại
- [x] Indexes: `idx_venues_city_status`, `idx_payments_status` có trong SQL
- [x] Auth: FakeAuthMiddleware documented rõ ràng
- [x] JSON structure: Match với VenueResource->toArray()
- [x] Authorization: VenuePolicy logic documented
- [x] Pagination: Laravel pagination format (data + links + meta)
- [x] Test examples: Đúng với code hiện tại (dùng `?user_id=`)
- [x] Migration plan: Rõ ràng cách chuyển sang Sanctum

---

**Last updated:** December 2, 2025  
**Author:** AI Agent following checklist guidelines
