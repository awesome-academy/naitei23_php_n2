# API Contract - Workspace Booking System
## Hướng dẫn thiết kế UI "chạy chung nhà" với Laravel

---

## 📋 1) THÔNG TIN NỀN - MÔI TRƯỜNG DEV

### Base URL
```
http://127.0.0.1:8000
```

### Đặt file HTML
**Option 1 (Khuyến nghị):** Blade template
- File: `resources/views/admin.blade.php`, `resources/views/owner.blade.php`, `resources/views/app.blade.php`
- Route: `routes/web.php`
  ```php
  Route::view('/', 'app');
  Route::view('/admin', 'admin')->middleware(['auth', 'role:admin']);
  ```

**Option 2:** HTML tĩnh
- File: `public/admin/index.html`, `public/owner/index.html`
- Access: `http://127.0.0.1:8000/admin/index.html`

### Auth Pattern: **Token-based (Sanctum)**

#### Flow đăng nhập:
```javascript
// 1. Login
const response = await fetch('/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
});

const { data } = await response.json();
localStorage.setItem('token', data.token);
localStorage.setItem('user', JSON.stringify(data.user));

// 2. Gọi API authenticated
const apiResponse = await fetch('/api/bookings', {
    headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
    }
});
```

#### Logout:
```javascript
await fetch('/api/auth/logout', {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${token}` }
});
localStorage.clear();
```

---

## 🔑 2) AUTHENTICATION ENDPOINTS

### POST `/api/auth/login`
**Public** - Đăng nhập và nhận token

**Request:**
```json
{
  "email": "admin@workspace.com",
  "password": "admin123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Đăng nhập thành công!",
  "data": {
    "user": {
      "id": 1,
      "full_name": "Admin User",
      "email": "admin@workspace.com",
      "phone_number": "0123456789",
      "profile_avatar_url": null,
      "is_active": true,
      "is_verified": true,
      "roles": ["admin"],
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

**Error (401):**
```json
{
  "success": false,
  "message": "Email hoặc mật khẩu không đúng.",
  "errors": null
}
```

### GET `/api/auth/me`
**Auth required** - Lấy thông tin user hiện tại

**Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "full_name": "Admin User",
      "email": "admin@workspace.com",
      "roles": ["admin"],
      "is_active": true,
      "is_verified": true
    }
  }
}
```

---

## 📊 3) ADMIN ENDPOINTS

**Tất cả routes cần:** `Authorization: Bearer {token}` + role `admin`

### GET `/api/admin/statistics`
Lấy thống kê dashboard

**Query params:**
- `month` (optional): Format `YYYY-MM` (default: tháng hiện tại)

**Example:** `/api/admin/statistics?month=2024-12`

**Response (200):**
```json
{
  "success": true,
  "message": "Statistics retrieved successfully",
  "data": {
    "total_users": 150,
    "new_users_in_month": 12,
    "total_bookings": 450,
    "bookings_in_month": 45,
    "bookings_by_status_in_month": {
      "pending_confirmation": 5,
      "confirmed": 15,
      "paid": 18,
      "cancelled": 4,
      "completed": 3
    },
    "revenue_in_month": 125000000.00,
    "total_revenue": 1850000000.00,
    "total_venues": 28,
    "pending_venues": 3,
    "approved_venues": 23,
    "month": "2024-12"
  }
}
```

### GET `/api/admin/bookings`
Xem tất cả bookings (có filter + pagination)

**Query params:**
- `status` (optional): `pending_confirmation|awaiting_payment|confirmed|paid|cancelled|completed`
- `start_date` (optional): Format `YYYY-MM-DD`
- `end_date` (optional): Format `YYYY-MM-DD`
- `user_id` (optional): Filter by user
- `space_id` (optional): Filter by space
- `venue_id` (optional): Filter by venue
- `search` (optional): Search by user name/email/space name
- `per_page` (optional): Default 15, max 50

**Example:** `/api/admin/bookings?status=confirmed&per_page=20`

**Response (200):**
```json
{
  "success": true,
  "message": "Bookings retrieved successfully",
  "data": {
    "data": [
      {
        "id": 123,
        "status": "confirmed",
        "start_time": "2024-12-20 09:00:00",
        "end_time": "2024-12-20 17:00:00",
        "total_price": 800000.00,
        "note": "Conference meeting",
        "created_at": "2024-12-15T10:30:00.000000Z",
        "updated_at": "2024-12-16T14:20:00.000000Z",
        "user": {
          "id": 45,
          "name": "Nguyen Van A",
          "email": "nguyenvana@example.com"
        },
        "space": {
          "id": 12,
          "name": "Meeting Room A",
          "venue_id": 3
        },
        "venue": {
          "id": 3,
          "name": "Coworking Space Hanoi",
          "status": "approved"
        }
      }
    ],
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 67
  }
}
```

### GET `/api/admin/bookings/{id}`
Chi tiết booking

**Response (200):**
```json
{
  "success": true,
  "message": "Booking details retrieved successfully",
  "data": {
    "id": 123,
    "status": "confirmed",
    "start_time": "2024-12-20 09:00:00",
    "end_time": "2024-12-20 17:00:00",
    "total_price": 800000.00,
    "note": "Conference meeting",
    "created_at": "2024-12-15T10:30:00.000000Z",
    "updated_at": "2024-12-16T14:20:00.000000Z",
    "user": {
      "id": 45,
      "name": "Nguyen Van A",
      "email": "nguyenvana@example.com"
    },
    "space": {
      "id": 12,
      "name": "Meeting Room A",
      "venue_id": 3
    },
    "venue": {
      "id": 3,
      "name": "Coworking Space Hanoi",
      "status": "approved"
    }
  }
}
```

### GET `/api/admin/venues`
Xem danh sách venues (có filter)

**Query params:**
- `status` (optional): `pending|approved|rejected|blocked`
- `city` (optional): Filter by city
- `owner_id` (optional): Filter by owner
- `per_page` (optional): Default 15

**Example:** `/api/admin/venues?status=pending`

**Response (200):**
```json
{
  "success": true,
  "message": "Venues retrieved successfully",
  "data": {
    "data": [
      {
        "id": 5,
        "name": "Startup Hub HCMC",
        "description": "Modern coworking space in District 1",
        "address": "123 Nguyen Hue, District 1, HCMC",
        "city": "Ho Chi Minh",
        "street": "Nguyen Hue",
        "latitude": 10.7769,
        "longitude": 106.7009,
        "status": "pending",
        "created_at": "2024-12-10T08:00:00.000000Z",
        "updated_at": "2024-12-10T08:00:00.000000Z"
      }
    ],
    "current_page": 1,
    "last_page": 2,
    "per_page": 15,
    "total": 18
  }
}
```

### PATCH `/api/admin/venues/{id}/approve`
Duyệt venue (pending → approved)

**Response (200):**
```json
{
  "success": true,
  "message": "Venue 'Startup Hub HCMC' approved successfully",
  "data": {
    "id": 5,
    "name": "Startup Hub HCMC",
    "status": "approved"
  }
}
```

### PATCH `/api/admin/venues/{id}/reject`
Từ chối venue (pending → rejected)

**Response (200):**
```json
{
  "success": true,
  "message": "Venue 'Bad Venue' rejected",
  "data": {
    "id": 6,
    "status": "rejected"
  }
}
```

### PATCH `/api/admin/venues/{id}/block`
Khóa venue (approved → blocked)

**Response (200):**
```json
{
  "success": true,
  "message": "Venue 'Problem Venue' blocked",
  "data": {
    "id": 7,
    "status": "blocked"
  }
}
```

### PATCH `/api/admin/venues/{id}/unblock`
Mở khóa venue (blocked → approved)

**Response (200):**
```json
{
  "success": true,
  "message": "Venue 'Problem Venue' unblocked",
  "data": {
    "id": 7,
    "status": "approved"
  }
}
```

### GET `/api/admin/payments`
Xem lịch sử payments

**Query params:**
- `status` (optional): `pending|success|failed`
- `payment_method` (optional): `cash|bank_transfer|e_wallet|credit_card`
- `month` (optional): Format `YYYY-MM`
- `venue_id` (optional)
- `space_id` (optional)
- `user_id` (optional)
- `per_page` (optional): Default 15

**Example:** `/api/admin/payments?status=success&month=2024-12`

**Response (200):**
```json
{
  "success": true,
  "message": "Payments retrieved successfully",
  "data": {
    "data": [
      {
        "id": 89,
        "booking_id": 123,
        "amount": 800000.00,
        "payment_method": "bank_transfer",
        "transaction_id": "TXN20241215001",
        "transaction_status": "success",
        "paid_at": "2024-12-15T15:30:00.000000Z",
        "created_at": "2024-12-15T15:25:00.000000Z",
        "booking": {
          "id": 123,
          "user": {
            "id": 45,
            "name": "Nguyen Van A",
            "email": "nguyenvana@example.com"
          },
          "space": {
            "id": 12,
            "name": "Meeting Room A",
            "venue": {
              "id": 3,
              "name": "Coworking Space Hanoi"
            }
          }
        }
      }
    ],
    "current_page": 1,
    "last_page": 8,
    "per_page": 15,
    "total": 112
  }
}
```

---

## 🏠 4) OWNER ENDPOINTS

**Cần:** `Authorization: Bearer {token}` + role `owner` hoặc `manager`

### GET `/api/owner/bookings`
Xem bookings của venues mình quản lý

**Query params:**
- `status` (optional)
- `venue_id` (optional): Filter by venue owner
- `space_id` (optional)
- `start_date`, `end_date` (optional)
- `per_page` (optional)

**Response:** Tương tự Admin bookings

### PATCH `/api/owner/bookings/{id}/confirm`
Xác nhận booking (pending_confirmation → awaiting_payment)

**Response (200):**
```json
{
  "success": true,
  "message": "Booking confirmed successfully",
  "data": {
    "id": 123,
    "status": "awaiting_payment"
  }
}
```

### PATCH `/api/owner/bookings/{id}/reject`
Từ chối booking (pending_confirmation → cancelled)

**Response (200):**
```json
{
  "success": true,
  "message": "Booking rejected successfully",
  "data": {
    "id": 124,
    "status": "cancelled"
  }
}
```

---

## 👤 5) USER ENDPOINTS

**Cần:** `Authorization: Bearer {token}` + role `user`

### GET `/api/bookings`
Xem bookings của mình

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "start_time": "2024-12-20 09:00:00",
      "end_time": "2024-12-20 17:00:00",
      "status": "confirmed",
      "total_price": 800000.00,
      "payment": null
    },
    {
      "id": 125,
      "start_time": "2024-12-22 14:00:00",
      "end_time": "2024-12-22 18:00:00",
      "status": "paid",
      "total_price": 400000.00,
      "payment": {
        "id": 90,
        "amount": 400000.00,
        "payment_method": "e_wallet",
        "transaction_status": "success",
        "paid_at": "2024-12-18T10:00:00.000000Z"
      }
    }
  ]
}
```

### POST `/api/bookings`
Tạo booking mới

**Request:**
```json
{
  "space_id": 12,
  "start_time": "2024-12-25 09:00:00",
  "end_time": "2024-12-25 17:00:00",
  "note": "Year-end meeting"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Booking created successfully",
  "data": {
    "id": 126,
    "space_id": 12,
    "status": "pending_confirmation",
    "start_time": "2024-12-25 09:00:00",
    "end_time": "2024-12-25 17:00:00",
    "total_price": 800000.00
  }
}
```

### DELETE `/api/bookings/{id}`
Hủy booking (chỉ được hủy nếu chưa thanh toán)

**Response (200):**
```json
{
  "success": true,
  "message": "Booking cancelled successfully"
}
```

**Error (422) - Đã thanh toán:**
```json
{
  "success": false,
  "message": "Cannot cancel booking after payment",
  "errors": null
}
```

### POST `/api/payments`
Thanh toán booking

**Request:**
```json
{
  "booking_id": 123,
  "payment_method": "bank_transfer"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Payment created successfully",
  "data": {
    "id": 91,
    "booking_id": 123,
    "amount": 800000.00,
    "payment_method": "bank_transfer",
    "transaction_status": "pending"
  }
}
```

---

## 🌍 6) PUBLIC ENDPOINTS (không cần auth)

### GET `/api/search/spaces`
Tìm kiếm spaces (có filter available time)

**Query params:**
- `city` (optional): Filter by city
- `q` (optional): Search keyword (name/venue/address)
- `space_type_id` (optional): Filter by type
- `min_price`, `max_price` (optional): Price range
- `start_time`, `end_time` (optional): Available time filter (ISO 8601)
- `per_page` (optional): Default 10, max 50

**Example:** `/api/search/spaces?city=Hanoi&start_time=2024-12-20T09:00:00&end_time=2024-12-20T17:00:00`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 12,
        "venue_id": 3,
        "space_type_id": 1,
        "name": "Meeting Room A",
        "capacity": 10,
        "price_per_hour": 100000.00,
        "price_per_day": 800000.00,
        "price_per_month": 20000000.00,
        "open_hour": "08:00:00",
        "close_hour": "22:00:00",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z",
        "venue": {
          "id": 3,
          "name": "Coworking Space Hanoi",
          "address": "10 Tran Hung Dao, Hanoi",
          "city": "Hanoi",
          "latitude": 21.0285,
          "longitude": 105.8542
        }
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 3,
      "total": 28,
      "per_page": 10
    }
  }
}
```

### GET `/api/venues/{id}`
Chi tiết venue (public)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 3,
    "name": "Coworking Space Hanoi",
    "description": "Modern workspace in downtown Hanoi",
    "address": "10 Tran Hung Dao, Hoan Kiem, Hanoi",
    "city": "Hanoi",
    "street": "Tran Hung Dao",
    "latitude": 21.0285,
    "longitude": 105.8542,
    "status": "approved",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-05T14:30:00.000000Z",
    "spaces": [
      {
        "id": 12,
        "name": "Meeting Room A",
        "capacity": 10,
        "price_per_hour": 100000.00
      }
    ],
    "amenities": [
      {
        "id": 1,
        "name": "WiFi",
        "icon": "wifi"
      },
      {
        "id": 2,
        "name": "Projector",
        "icon": "projector"
      }
    ]
  }
}
```

### GET `/api/spaces/{id}`
Chi tiết space (public)

**Response:** Tương tự space object trong search

### GET `/api/map/config`
Cấu hình map (center, zoom, bounds)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "center": {
      "lat": 21.0285,
      "lng": 105.8542
    },
    "zoom": 12,
    "bounds": {
      "north": 21.1,
      "south": 20.9,
      "east": 106.0,
      "west": 105.7
    }
  }
}
```

### GET `/api/map/venues`
Tất cả venue markers cho map

**Query params:**
- `city` (optional): Filter by city
- `status` (optional): Default `approved`, set `all` for all statuses

**Response (200):**
```json
{
  "success": true,
  "data": {
    "markers": [
      {
        "id": 3,
        "name": "Coworking Space Hanoi",
        "address": "10 Tran Hung Dao, Hanoi",
        "city": "Hanoi",
        "position": {
          "lat": 21.0285,
          "lng": 105.8542
        },
        "coordinates": {
          "latitude": 21.0285,
          "longitude": 105.8542
        }
      }
    ],
    "total": 23,
    "center": {
      "lat": 21.0285,
      "lng": 105.8542
    }
  }
}
```

### GET `/api/map/venues/bounds`
Venues trong viewport (bounding box query)

**Query params (required):**
- `north`: North latitude
- `south`: South latitude
- `east`: East longitude
- `west`: West longitude

**Example:** `/api/map/venues/bounds?north=21.1&south=20.9&east=106.0&west=105.7`

**Response:** Tương tự `/api/map/venues`

### GET `/api/map/venues/{id}`
Venue detail cho info window popup

**Response:** Tương tự `/api/venues/{id}` nhưng format ngắn gọn hơn

### GET `/api/map/search`
Search venues trên map

**Query params:**
- `q` (required): Search keyword
- `city` (optional): Filter by city

**Example:** `/api/map/search?q=coworking&city=Hanoi`

**Response:** Tương tự `/api/map/venues`

---

## 📐 7) RESPONSE FORMAT CHUẨN

### Success Response
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": { /* payload */ }
}
```

### Pagination Response
```json
{
  "success": true,
  "data": {
    "data": [/* items */],
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 67
  }
}
```

### Error Response (4xx, 5xx)
```json
{
  "success": false,
  "message": "Error message in Vietnamese or English",
  "errors": {
    "field_name": ["Error detail 1", "Error detail 2"]
  }
}
```

### Validation Error (422)
```json
{
  "success": false,
  "message": "Dữ liệu không hợp lệ.",
  "errors": {
    "email": ["Email đã được sử dụng."],
    "start_time": ["Thời gian bắt đầu phải sau thời điểm hiện tại."]
  }
}
```

### Unauthorized (401)
```json
{
  "success": false,
  "message": "Unauthenticated.",
  "errors": null
}
```

### Forbidden (403)
```json
{
  "success": false,
  "message": "You are not authorized to access this resource.",
  "errors": null
}
```

---

## 🎨 8) ENUMS & STATUS LISTS

### Booking Statuses
```javascript
const BOOKING_STATUSES = {
  PENDING_CONFIRMATION: 'pending_confirmation',  // Chờ Owner xác nhận
  AWAITING_PAYMENT: 'awaiting_payment',          // Đã xác nhận, chờ thanh toán
  CONFIRMED: 'confirmed',                        // Đã xác nhận (legacy)
  PAID: 'paid',                                  // Đã thanh toán
  CANCELLED: 'cancelled',                        // Đã hủy
  COMPLETED: 'completed'                         // Đã hoàn thành
};

// Badge colors
const BOOKING_STATUS_COLORS = {
  'pending_confirmation': 'warning',   // Yellow
  'awaiting_payment': 'info',          // Blue
  'confirmed': 'success',              // Green
  'paid': 'success',                   // Green
  'cancelled': 'danger',               // Red
  'completed': 'secondary'             // Gray
};
```

### Venue Statuses
```javascript
const VENUE_STATUSES = {
  PENDING: 'pending',       // Chờ duyệt
  APPROVED: 'approved',     // Đã duyệt
  REJECTED: 'rejected',     // Bị từ chối
  BLOCKED: 'blocked'        // Bị khóa
};

// Badge colors
const VENUE_STATUS_COLORS = {
  'pending': 'warning',     // Yellow
  'approved': 'success',    // Green
  'rejected': 'danger',     // Red
  'blocked': 'dark'         // Black/Dark
};
```

### Payment Statuses
```javascript
const PAYMENT_STATUSES = {
  PENDING: 'pending',
  SUCCESS: 'success',
  FAILED: 'failed'
};
```

### Payment Methods
```javascript
const PAYMENT_METHODS = {
  CASH: 'cash',
  BANK_TRANSFER: 'bank_transfer',
  E_WALLET: 'e_wallet',
  CREDIT_CARD: 'credit_card'
};
```

---

## 🗂️ 9) FIELD REQUIREMENTS CHO UI SCREENS

### 3.1 Public Search/List Spaces
**Required fields:**
- `space.id`, `space.name`, `space.capacity`
- `space.price_per_hour`, `space.price_per_day`, `space.price_per_month`
- `venue.id`, `venue.name`, `venue.address`, `venue.city`
- `venue.latitude`, `venue.longitude`

**Optional for UX:**
- `space.open_hour`, `space.close_hour`
- `venue.amenities` (badges)

### 3.2 Space/Venue Detail
**Required:**
- All fields from search +
- `venue.description`
- `amenities[]` (list with icons)
- `spaces[]` (list of rooms available)

### 3.3 User Booking UI
**Required:**
- `booking.id`, `booking.status`, `booking.total_price`
- `booking.start_time`, `booking.end_time`, `booking.created_at`
- `space.name`, `venue.name`, `venue.address`
- Action buttons based on status:
  - `pending_confirmation`: Hiển thị "Chờ xác nhận" (không có nút)
  - `awaiting_payment`: Nút "Thanh toán" + "Hủy"
  - `confirmed`: Nút "Thanh toán" + "Hủy"
  - `paid`: Hiển thị "Đã thanh toán" (không hủy được)
  - `cancelled`: Hiển thị "Đã hủy"

### 3.4 Owner Booking Management
**Required:**
- All booking fields +
- `user.name`, `user.email`, `user.phone_number`
- Action buttons:
  - `pending_confirmation`: Nút "Xác nhận" + "Từ chối"
  - Other statuses: View only

### 3.5 Admin Dashboard
**Statistics widget:**
- `total_users`, `new_users_in_month`
- `total_bookings`, `bookings_in_month`
- `revenue_in_month`, `total_revenue`
- `bookings_by_status_in_month` (chart data)
- `pending_venues` (badge alert)

**Venue management table:**
- `venue.id`, `venue.name`, `venue.city`, `venue.status`, `venue.created_at`
- Action buttons by status:
  - `pending`: "Duyệt" + "Từ chối"
  - `approved`: "Khóa"
  - `blocked`: "Mở khóa"

**Booking list:**
- Same as Owner + filter by `venue` and `user`

**Payment history:**
- `payment.transaction_id`, `payment.amount`, `payment.payment_method`
- `payment.transaction_status`, `payment.paid_at`
- `booking.id` (link to booking detail)
- `user.name`, `venue.name`

---

## ⚙️ 10) DATETIME FORMAT

**Backend trả về:** ISO 8601 format
```
2024-12-20T09:00:00.000000Z  // UTC timezone
```

**UI hiển thị:** Format theo locale VN
```javascript
// Option 1: Native JS
const date = new Date('2024-12-20T09:00:00.000000Z');
const formatted = date.toLocaleString('vi-VN', {
  year: 'numeric',
  month: '2-digit',
  day: '2-digit',
  hour: '2-digit',
  minute: '2-digit'
});
// Output: "20/12/2024, 16:00" (UTC+7)

// Option 2: Day.js (khuyến nghị)
dayjs('2024-12-20T09:00:00.000000Z')
  .tz('Asia/Bangkok')
  .format('DD/MM/YYYY HH:mm');
```

**Gửi lên backend:** ISO 8601 hoặc `YYYY-MM-DD HH:mm:ss`
```javascript
// Từ datetime-local input
const startTime = document.getElementById('start_time').value;
// "2024-12-20T09:00" → Backend nhận được
```

---

## 🚀 11) SAMPLE CODE - API CLIENT WRAPPER

```javascript
// config.js
const CONFIG = {
  BASE_URL: 'http://127.0.0.1:8000',
  TOKEN_KEY: 'workspace_token',
  USER_KEY: 'workspace_user'
};

// apiClient.js
class ApiClient {
  constructor() {
    this.baseUrl = CONFIG.BASE_URL;
  }

  getToken() {
    return localStorage.getItem(CONFIG.TOKEN_KEY);
  }

  async request(endpoint, options = {}) {
    const url = `${this.baseUrl}${endpoint}`;
    const headers = {
      'Content-Type': 'application/json',
      ...options.headers
    };

    const token = this.getToken();
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    try {
      const response = await fetch(url, {
        ...options,
        headers
      });

      const data = await response.json();

      if (!response.ok) {
        // Handle errors
        if (response.status === 401) {
          this.handleUnauthorized();
        }
        throw new Error(data.message || 'Request failed');
      }

      return data;
    } catch (error) {
      console.error('API Error:', error);
      throw error;
    }
  }

  handleUnauthorized() {
    localStorage.removeItem(CONFIG.TOKEN_KEY);
    localStorage.removeItem(CONFIG.USER_KEY);
    window.location.href = '/login';
  }

  // Auth methods
  async login(email, password) {
    const data = await this.request('/api/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password })
    });
    
    localStorage.setItem(CONFIG.TOKEN_KEY, data.data.token);
    localStorage.setItem(CONFIG.USER_KEY, JSON.stringify(data.data.user));
    
    return data.data;
  }

  async logout() {
    await this.request('/api/auth/logout', { method: 'POST' });
    localStorage.clear();
  }

  async getMe() {
    return this.request('/api/auth/me');
  }

  // Admin methods
  async getAdminStatistics(month = null) {
    const params = month ? `?month=${month}` : '';
    return this.request(`/api/admin/statistics${params}`);
  }

  async getAdminBookings(filters = {}) {
    const params = new URLSearchParams(filters).toString();
    return this.request(`/api/admin/bookings?${params}`);
  }

  async getAdminVenues(filters = {}) {
    const params = new URLSearchParams(filters).toString();
    return this.request(`/api/admin/venues?${params}`);
  }

  async approveVenue(id) {
    return this.request(`/api/admin/venues/${id}/approve`, { method: 'PATCH' });
  }

  async rejectVenue(id) {
    return this.request(`/api/admin/venues/${id}/reject`, { method: 'PATCH' });
  }

  async blockVenue(id) {
    return this.request(`/api/admin/venues/${id}/block`, { method: 'PATCH' });
  }

  // Public methods
  async searchSpaces(filters = {}) {
    const params = new URLSearchParams(filters).toString();
    return this.request(`/api/search/spaces?${params}`);
  }

  async getVenueDetail(id) {
    return this.request(`/api/venues/${id}`);
  }

  async getMapVenues(filters = {}) {
    const params = new URLSearchParams(filters).toString();
    return this.request(`/api/map/venues?${params}`);
  }

  // User methods
  async getMyBookings() {
    return this.request('/api/bookings');
  }

  async createBooking(data) {
    return this.request('/api/bookings', {
      method: 'POST',
      body: JSON.stringify(data)
    });
  }

  async cancelBooking(id) {
    return this.request(`/api/bookings/${id}`, { method: 'DELETE' });
  }
}

// Export singleton
const api = new ApiClient();
```

---

## 🎯 12) VALIDATION RULES UI CẦN BIẾT

### Booking creation
- `start_time` phải sau thời điểm hiện tại
- `end_time` phải sau `start_time`
- Thời gian phải nằm trong `open_hour` - `close_hour` của space
- Không được trùng với booking khác (check overlap backend)

### Cancel booking rules (quan trọng!)
- ❌ **KHÔNG thể hủy** nếu `status = 'paid'`
- ❌ **KHÔNG thể hủy** nếu có `payment` với `transaction_status = 'success'`
- ✅ **Được hủy** nếu `status = 'pending_confirmation'`
- ✅ **Được hủy** nếu `status = 'awaiting_payment'` hoặc `confirmed` (chưa thanh toán)

### Payment rules
- Chỉ được thanh toán khi `status = 'awaiting_payment'` hoặc `confirmed`
- Sau khi payment success → `status` tự động chuyển thành `paid`

---

## 🗺️ 13) MAP INTEGRATION NOTES

### Google Maps setup (nếu dùng Google Maps)
```javascript
// Load Google Maps API
// <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY&callback=initMap"></script>

async function initMap() {
  const configData = await api.request('/api/map/config');
  const config = configData.data;

  const map = new google.maps.Map(document.getElementById('map'), {
    center: config.center,
    zoom: config.zoom
  });

  // Load venues
  const venuesData = await api.getMapVenues();
  const markers = venuesData.data.markers;

  markers.forEach(venue => {
    const marker = new google.maps.Marker({
      position: venue.position,
      map: map,
      title: venue.name
    });

    // Info window
    const infoWindow = new google.maps.InfoWindow({
      content: `
        <div>
          <h3>${venue.name}</h3>
          <p>${venue.address}</p>
          <a href="/venues/${venue.id}">Xem chi tiết</a>
        </div>
      `
    });

    marker.addListener('click', () => {
      infoWindow.open(map, marker);
    });
  });
}
```

---

## 📝 14) CHECKLIST THÔNG TIN ĐỦ CHƯA?

✅ **Base URL:** `http://127.0.0.1:8000`  
✅ **Auth pattern:** Token-based Sanctum  
✅ **Login endpoint:** `POST /api/auth/login` returns `token` + `user` with `roles[]`  
✅ **Response format:** Chuẩn `{success, message, data}`  
✅ **Pagination format:** Laravel paginate với `data/current_page/last_page/total/per_page`  
✅ **Error format:** `422` với `errors` map  
✅ **Datetime format:** ISO 8601 UTC  
✅ **Enums:** Booking/Venue/Payment statuses đã define  
✅ **Admin endpoints:** 9 routes (statistics/bookings/venues/payments)  
✅ **Owner endpoints:** Có (xem/confirm/reject bookings)  
✅ **User endpoints:** Có (bookings CRUD + payments)  
✅ **Public endpoints:** Có (search/map/venue detail)  
✅ **Field requirements:** List đầy đủ cho từng màn hình  
✅ **Validation rules:** Cancel/payment rules rõ ràng  
✅ **Map integration:** Config/venues/bounds endpoints có  

---

## 📞 Liên hệ & hỗ trợ

Nếu còn thiếu thông tin gì, hãy test thử các endpoint này bằng Postman (có sẵn collections trong `postman/`) và báo lại để bổ sung vào document này.

**Demo accounts:**
- Admin: `admin@workspace.com` / `admin123`
- Owner: `owner@workspace.com` / `password`
- User: `user@workspace.com` / `password`

---

**Document version:** 1.0  
**Last updated:** 2024-12-17
