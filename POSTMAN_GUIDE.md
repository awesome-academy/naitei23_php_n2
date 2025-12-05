                                                                                                                                # 🚀 HƯỚNG DẪN DEMO API BẰNG POSTMAN

**Thời gian:** 15-20 phút | **Độ khó:** ⭐⭐☆☆☆

---

## 📋 0. Chuẩn bị trước khi mở Postman

### Backend chạy:
```bash
php artisan serve
# Mặc định: http://127.0.0.1:8000
```

### Database đã seed:
```bash
php artisan migrate:fresh --seed

# Tạo thêm user owner để test
php artisan tinker
$owner = App\Models\User::firstOrCreate(
    ['email' => 'owner@workspace.com'],
    ['full_name' => 'Owner Demo', 'password_hash' => Hash::make('password')]
);
exit;
```

### Postman đã cài:
- Download: https://www.postman.com/downloads/
- Hoặc: `sudo snap install postman`

---

## 🌍 1. Tạo Environment trong Postman

**Bước 1:** Góc trên phải Postman → Click icon **con mắt** (Environment) → **Add**

**Bước 2:** Đặt tên: `Workspace Local`

**Bước 3:** Thêm các biến:

| Variable | Initial Value | Current Value |
|----------|--------------|---------------|
| `base_url` | `http://127.0.0.1:8000/api` | `http://127.0.0.1:8000/api` |
| `token` | _(để trống)_ | _(để trống)_ |
| `venue_id` | _(để trống)_ | _(để trống)_ |
| `space_id` | _(để trống)_ | _(để trống)_ |

**Bước 4:** Save và chọn `Workspace Local` ở dropdown góc trên phải

---

## 📁 2. Tạo Collection

**Bước 1:** Tab **Collections** bên trái → **New Collection**

**Bước 2:** Đặt tên: `Workspace Booking - Owner API`

**Bước 3:** Tạo các folder trong Collection:
- `01 - Auth`
- `02 - Owner / Venues`
- `03 - Owner / Amenities`
- `04 - Owner / Spaces`
- `05 - Owner / Managers`
- `06 - Public APIs`

**Bước 4:** Set Authorization cho toàn Collection:
- Click vào Collection name → Tab **Authorization**
- Type: `Bearer Token`
- Token: `{{token}}`
- Save

---

## 🔐 3. BƯỚC 1: Login lấy Token

### Request: Login as Owner

**Folder:** `01 - Auth`

**Setup:**
- Method: `POST`
- URL: `{{base_url}}/auth/login`
- Headers:
  ```
  Content-Type: application/json
  Accept: application/json
  ```
- Body (raw JSON):
  ```json
  {
    "email": "owner@workspace.com",
    "password": "password"
  }
  ```

**Tab Tests (auto-save token):**
```javascript
let res = pm.response.json();
if (res.success && res.data && res.data.token) {
    pm.environment.set("token", res.data.token);
    console.log("✅ Token saved:", res.data.token.substring(0, 20) + "...");
}
```

**Send** → Response:
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "email": "owner@workspace.com",
      "full_name": "Owner Demo"
    },
    "token": "1|abc123..."
  }
}
```

**Khi demo nói:**
> "Đầu tiên em login để lấy Sanctum token. Token này sẽ tự động lưu vào environment và dùng cho tất cả request sau."

---

## 🏢 4. VENUE CRUD

**Folder:** `02 - Owner / Venues`

### 4.1 List My Venues

**Setup:**
- Method: `GET`
- URL: `{{base_url}}/owner/venues`
- Auth: Inherit from parent (Bearer token tự động)

**Send** → Response: `{"data": []}`

**Khi demo:**
> "API này trả về venues của owner đang login. Backend tự filter WHERE owner_id = user->id nên chỉ thấy venue của mình."

---

### 4.2 Create Venue

**Setup:**
- Method: `POST`
- URL: `{{base_url}}/owner/venues`
- Body (raw JSON):
  ```json
  {
    "name": "Coworking HUST",
    "description": "Không gian làm việc cho sinh viên",
    "address": "1 Đại Cồ Việt, Hai Bà Trưng",
    "city": "Hanoi",
    "latitude": 21.004,
    "longitude": 105.843,
    "phone": "0987654321"
  }
  ```

**Tab Tests:**
```javascript
let res = pm.response.json();
if (res.success && res.data && res.data.id) {
    pm.environment.set("venue_id", res.data.id);
    console.log("✅ Venue ID saved:", res.data.id);
}
```

**Send** → Response: Status **201 Created**

**Khi demo:**
> "API validate input, tự gán owner_id = user đang login, lưu vào DB. venue_id được lưu vào biến để dùng cho các request sau."

---

### 4.3 Show Venue Detail

**Setup:**
- Method: `GET`
- URL: `{{base_url}}/owner/venues/{{venue_id}}`

**Send** → Response: Chi tiết venue vừa tạo

---

### 4.4 Update Venue

**Setup:**
- Method: `PUT`
- URL: `{{base_url}}/owner/venues/{{venue_id}}`
- Body:
  ```json
  {
    "name": "Coworking HUST - Updated",
    "description": "Update mô tả venue",
    "phone": "0123456789"
  }
  ```

**Khi demo:**
> "API này có VenuePolicy check: chỉ owner của venue đó hoặc admin mới update được. Nếu dùng token user khác sẽ bị 403 Forbidden."

---

### 4.5 Delete Venue

**Setup:**
- Method: `DELETE`
- URL: `{{base_url}}/owner/venues/{{venue_id}}`

**Send** → Status **200**

**Lưu ý:** Nếu muốn test tiếp, tạo lại venue trước khi xóa!

---

## 🎨 5. VENUE AMENITIES

**Folder:** `03 - Owner / Amenities`

### 5.1 Get Venue Amenities

**Setup:**
- Method: `GET`
- URL: `{{base_url}}/owner/venues/{{venue_id}}/amenities`

**Send** → Response: List amenities

**Khi demo:**
> "API này trả list tiện ích của venue, dùng cho màn edit venue: checkbox WiFi, parking, projector..."

---

### 5.2 Update Venue Amenities

**Setup:**
- Method: `PUT`
- URL: `{{base_url}}/owner/venues/{{venue_id}}/amenities`
- Body:
  ```json
  {
    "amenity_ids": [1, 2, 3]
  }
  ```

**Khi demo:**
> "Backend sync vào bảng pivot venue_amenities. FE gọi khi user tick/untick tiện ích."

---

## 🏢 6. SPACE CRUD

**Folder:** `04 - Owner / Spaces`

### 6.1 List Spaces in Venue

**Setup:**
- Method: `GET`
- URL: `{{base_url}}/owner/venues/{{venue_id}}/spaces`

**Send** → Response: `{"data": []}`

---

### 6.2 Create Space

**Setup:**
- Method: `POST`
- URL: `{{base_url}}/owner/venues/{{venue_id}}/spaces`
- Body:
  ```json
  {
    "name": "Phòng họp 201",
    "description": "Phòng họp 8 người, có TV",
    "space_type_id": 1,
    "capacity": 8,
    "price_per_hour": 100000,
    "open_time": "08:00",
    "close_time": "22:00"
  }
  ```

**Tab Tests:**
```javascript
let res = pm.response.json();
if (res.success && res.data && res.data.id) {
    pm.environment.set("space_id", res.data.id);
    console.log("✅ Space ID saved:", res.data.id);
}
```

**Khi demo:**
> "API validate: capacity > 0, price > 0, open_time < close_time. SpacePolicy check owner của venue."

---

### 6.3 Show Space Detail

**Setup:**
- Method: `GET`
- URL: `{{base_url}}/owner/spaces/{{space_id}}`

---

### 6.4 Update Space

**Setup:**
- Method: `PUT`
- URL: `{{base_url}}/owner/spaces/{{space_id}}`
- Body:
  ```json
  {
    "name": "Phòng họp 201 - Updated",
    "capacity": 10,
    "price_per_hour": 150000
  }
  ```

---

### 6.5 Update Space Amenities

**Setup:**
- Method: `PUT`
- URL: `{{base_url}}/owner/spaces/{{space_id}}/amenities`
- Body:
  ```json
  {
    "amenity_ids": [1, 2, 4]
  }
  ```

---

## 👥 7. MANAGERS

**Folder:** `05 - Owner / Managers`

### 7.1 List Managers

**Setup:**
- Method: `GET`
- URL: `{{base_url}}/owner/venues/{{venue_id}}/managers`

---

### 7.2 Add Manager

**Trước tiên tạo user manager:**
```bash
php artisan tinker
App\Models\User::firstOrCreate(
    ['email' => 'manager@test.com'],
    ['full_name' => 'Manager Test', 'password_hash' => Hash::make('password')]
);
exit;
```

**Setup:**
- Method: `POST`
- URL: `{{base_url}}/owner/venues/{{venue_id}}/managers`
- Body:
  ```json
  {
    "email": "manager@test.com"
  }
  ```

**Khi demo:**
> "Backend tìm user theo email, gán role 'manager' nếu chưa có (dùng assignRole), rồi thêm vào venue_managers."

---

### 7.3 Remove Manager

**Setup:**
- Method: `DELETE`
- URL: `{{base_url}}/owner/venues/{{venue_id}}/managers/{manager_id}`

**Lưu ý:** Thay `{manager_id}` bằng ID user manager (xem trong response Add Manager)

---

## 🌐 8. PUBLIC APIs

**Folder:** `06 - Public APIs`

**Lưu ý:** Các API này không cần token!

### 8.1 Public Venue Detail

**Setup:**
- Method: `GET`
- URL: `{{base_url}}/venues/{{venue_id}}`
- Authorization: `No Auth` (override từ parent)

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Coworking HUST",
    "spaces": [...],
    "amenities": [...]
  }
}
```

**Khi demo:**
> "API này dùng cho FE trang Venue Detail và Map: click marker → load detail. Dùng VenueResource để format response."

---

### 8.2 Public Space Detail

**Setup:**
- Method: `GET`
- URL: `{{base_url}}/spaces/{{space_id}}`
- Authorization: `No Auth`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Phòng họp 201",
    "venue": {...},
    "upcoming_bookings": [...]
  }
}
```

**Khi demo:**
> "Public space API có nested venue info (dùng VenueShortResource để tránh circular reference) và upcoming bookings trong 30 ngày tới."

---

## 💡 9. Tips khi demo

### ✅ Nên làm:

1. **Trước khi Send:**
   - "Đây là API để {mục đích}..."
   - Chỉ vào URL, Headers, Body

2. **Sau khi Send:**
   - "Status code 200/201 nghĩa là thành công"
   - Highlight data quan trọng trong response
   - "Format response chuẩn: success, message, data"

3. **Giải thích logic:**
   - "Backend check auth qua Sanctum token"
   - "Policy check owner_id hoặc admin role"
   - "Validation: capacity > 0, open_time < close_time"

### ❌ Không nên:

- Demo quá nhanh (mentor không kịp xem)
- Nói quá kỹ thuật (tên class, method...)
- Bỏ qua lỗi (phải giải thích nguyên nhân)
- Quên check token còn hạn

---

## 🚨 10. Xử lý lỗi thường gặp

### **401 Unauthorized**
```json
{
  "message": "Unauthenticated."
}
```
**Nguyên nhân:** Token hết hạn hoặc sai
**Fix:** Login lại → token mới tự động lưu vào environment

---

### **403 Forbidden**
```json
{
  "success": false,
  "message": "This action is unauthorized."
}
```
**Nguyên nhân:** User không phải owner venue đó
**Khi demo nói:** 
> "Đúng rồi, đây là Policy đang hoạt động. User này không có quyền với venue này."

---

### **422 Unprocessable Entity**
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "capacity": ["The capacity must be greater than 0."]
  }
}
```
**Nguyên nhân:** Validation fail
**Khi demo:** 
> "Backend validate input. Ví dụ capacity phải > 0, giá phải > 0..."

---

### **500 Internal Server Error**
**Nguyên nhân:** Bug trong code hoặc DB issue
**Fix:** Check terminal chạy `php artisan serve` để xem error log

---

## 📊 11. Checklist hoàn chỉnh

Sau khi setup xong, bạn sẽ có **19 requests** trong Postman:

```
Workspace Booking - Owner API/
├── 01 - Auth/
│   └── Login as Owner ✅
│
├── 02 - Owner / Venues/
│   ├── 1. List My Venues ✅
│   ├── 2. Create Venue ✅
│   ├── 3. Show Venue ✅
│   ├── 4. Update Venue ✅
│   └── 5. Delete Venue ✅
│
├── 03 - Owner / Amenities/
│   ├── 1. Get Venue Amenities ✅
│   └── 2. Update Venue Amenities ✅
│
├── 04 - Owner / Spaces/
│   ├── 1. List Spaces ✅
│   ├── 2. Create Space ✅
│   ├── 3. Show Space ✅
│   ├── 4. Update Space ✅
│   └── 5. Update Space Amenities ✅
│
├── 05 - Owner / Managers/
│   ├── 1. List Managers ✅
│   ├── 2. Add Manager ✅
│   └── 3. Remove Manager ✅
│
└── 06 - Public APIs/
    ├── 1. Public Venue Detail ✅
    └── 2. Public Space Detail ✅
```

---

## 🎯 12. Script demo cho mentor (15 phút)

### **Phút 1-2: Giới thiệu**
> "Em đã implement 17 API endpoints cho phần Owner quản lý venue và space. Em sẽ demo bằng Postman."

### **Phút 3-4: Authentication**
- Chạy Login → Show token
> "Đây là Sanctum token, tự động lưu vào environment để dùng cho các request sau."

### **Phút 5-8: Venue CRUD**
- List (rỗng) → Create → List (có data) → Update
> "Owner chỉ thấy venues của mình. Backend check ownership qua Policy."

### **Phút 9-10: Amenities**
- Get amenities → Update amenities
> "Sync vào bảng pivot venue_amenities. FE dùng để hiển thị checkbox tiện ích."

### **Phút 11-13: Space CRUD**
- Create space → Update space → Update space amenities
> "Policy check: chỉ owner venue mới tạo/sửa được space. Validation: capacity > 0, open < close..."

### **Phút 14: Managers**
- Add manager → Remove manager
> "Gán role manager cho user, thêm vào venue_managers để cùng quản lý."

### **Phút 15: Public APIs**
- Public venue detail → Public space detail
> "Không cần token. Dùng Resource để format response chuẩn, có nested relationships."

**Kết:** 
> "Tất cả 17 endpoints đều có auth, authorization, validation, và standard response format. Em có viết automated test, chạy `php artisan test:api-checklist` thì 100% pass."

---

## 📚 13. Export/Import Collection (bonus)

### Export để backup:
1. Click vào Collection → **...** (3 chấm) → **Export**
2. Format: Collection v2.1 (recommended)
3. Save file: `Workspace_Booking_Owner_API.postman_collection.json`

### Import vào máy khác:
1. Postman → **Import** → Chọn file JSON
2. Import environment tương tự

---

**Chúc bạn demo thành công!** 🚀

_Nếu gặp lỗi, check API_CHECKLIST_RESULTS.md để xem chi tiết implementation._
# Booking API Testing Guide

## 🎯 Module 1: Booking Core APIs

Tổng cộng **4 endpoints** cho user booking core:

### 1. Chuẩn bị
1. Login để lấy token:
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "owner@workspace.com",
  "password": "password"
}
```

Response:
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "token": "1|xxxxxxxxxxxxx"
  }
}
```

2. Set header cho tất cả requests sau:
```
Authorization: Bearer 1|xxxxxxxxxxxxx
Accept: application/json
```

---

### 2. Tạo Booking Mới

**Endpoint:** `POST /api/bookings`

**Body:**
```json
{
  "space_id": 1,
  "start_time": "2025-12-07 09:00:00",
  "end_time": "2025-12-07 11:00:00",
  "note": "Team meeting"
}
```

**Expected Response (201):**
```json
{
  "success": true,
  "message": "Booking created successfully",
  "data": {
    "id": 1,
    "user_id": 2,
    "space_id": 1,
    "start_time": "2025-12-07 09:00:00",
    "end_time": "2025-12-07 11:00:00",
    "total_price": "200000.00",
    "status": "pending_confirmation",
    "note": "Team meeting",
    "created_at": "2025-12-06T10:30:00.000000Z",
    "updated_at": "2025-12-06T10:30:00.000000Z",
    "space": {
      "id": 1,
      "name": "Meeting Room A",
      "venue": {
        "id": 1,
        "name": "Downtown Workspace"
      }
    }
  }
}
```

**Validation Tests:**

1. **Missing fields:**
```json
{
  "space_id": 1
}
```
Expected: 422 với error messages về start_time và end_time required.

2. **Past time:**
```json
{
  "space_id": 1,
  "start_time": "2020-01-01 09:00:00",
  "end_time": "2020-01-01 11:00:00"
}
```
Expected: 422 - "Start time must be in the future."

3. **End before start:**
```json
{
  "space_id": 1,
  "start_time": "2025-12-07 11:00:00",
  "end_time": "2025-12-07 09:00:00"
}
```
Expected: 422 - "End time must be after start time."

4. **Outside open hours:**
```json
{
  "space_id": 1,
  "start_time": "2025-12-07 06:00:00",
  "end_time": "2025-12-07 08:00:00"
}
```
Expected: 422 - "Booking time must be within space opening hours."

5. **Overlapping booking:**
- Tạo booking đầu tiên (09:00-11:00)
- Tạo booking thứ 2 (10:00-12:00)

Expected: 422 - "This time slot is already booked."

---

### 3. List Bookings của User

**Endpoint:** `GET /api/bookings`

**Query params (optional):**
- `page=1` - Pagination

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user_id": 2,
        "space_id": 1,
        "start_time": "2025-12-07 09:00:00",
        "end_time": "2025-12-07 11:00:00",
        "total_price": "200000.00",
        "status": "pending_confirmation",
        "note": "Team meeting",
        "space": {
          "id": 1,
          "name": "Meeting Room A",
          "venue": {
            "id": 1,
            "name": "Downtown Workspace"
          }
        }
      }
    ],
    "per_page": 10,
    "total": 1
  }
}
```

**Test:**
- Chỉ hiển thị bookings của current user
- Sắp xếp theo start_time desc (mới nhất lên đầu)
- Pagination 10 items/page

---

### 4. Xem Chi Tiết Booking

**Endpoint:** `GET /api/bookings/{id}`

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 2,
    "space_id": 1,
    "start_time": "2025-12-07 09:00:00",
    "end_time": "2025-12-07 11:00:00",
    "total_price": "200000.00",
    "status": "pending_confirmation",
    "note": "Team meeting",
    "space": {
      "id": 1,
      "name": "Meeting Room A",
      "capacity": 10,
      "price_per_hour": "100000.00",
      "venue": {
        "id": 1,
        "name": "Downtown Workspace",
        "address": "123 Main St"
      }
    }
  }
}
```

**Authorization Test:**
- Login với user khác (manager1@workspace.com / password)
- GET /api/bookings/1 (booking của owner)

Expected: 403 Forbidden

---

### 5. Hủy Booking

**Endpoint:** `DELETE /api/bookings/{id}`

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Booking cancelled successfully"
}
```

**Test Cases:**

1. **Cancel pending booking:** ✅ Success
2. **Cancel confirmed booking:**
```
Expected: 422 - "Only pending bookings can be cancelled."
```
3. **Cancel booking của user khác:**
```
Expected: 403 Forbidden
```

---

## 📊 Business Logic Summary

### Price Calculation
- **Duration < 24h:** Dùng `price_per_hour` × số giờ (làm tròn lên)
- **Duration >= 24h:** Dùng `price_per_day` × số ngày (làm tròn lên)
- **Duration >= 30 days:** Dùng `price_per_month` × số tháng (làm tròn lên)

### Status Flow
```
pending_confirmation → awaiting_payment → confirmed → completed
                    ↓
                 cancelled
```

### Validation Rules
1. ✅ Start time phải sau hiện tại
2. ✅ End time phải sau start time
3. ✅ Booking time phải trong open_hour - close_hour
4. ✅ Không được trùng với booking confirmed/awaiting_payment
5. ✅ Space phải tồn tại (exists:spaces,id)

---

## 🎯 Test Checklist

- [ ] Tạo booking thành công với data hợp lệ
- [ ] Validate các field required
- [ ] Validate time phải trong tương lai
- [ ] Validate open hours
- [ ] Validate overlap bookings
- [ ] Tính giá đúng (hour/day/month)
- [ ] List bookings chỉ của current user
- [ ] Pagination hoạt động
- [ ] View booking detail
- [ ] 403 khi view booking của người khác
- [ ] Cancel booking thành công
- [ ] 422 khi cancel booking không phải pending
- [ ] 403 khi cancel booking của người khác

---

## 🔥 Quick Test Script

```bash
# 1. Login
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"owner@workspace.com","password":"password"}' \
  | jq -r '.data.token')

# 2. Create booking
curl -X POST http://127.0.0.1:8000/api/bookings \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "space_id": 1,
    "start_time": "2025-12-07 09:00:00",
    "end_time": "2025-12-07 11:00:00",
    "note": "Test booking"
  }'

# 3. List bookings
curl -X GET http://127.0.0.1:8000/api/bookings \
  -H "Authorization: Bearer $TOKEN"

# 4. Cancel booking
curl -X DELETE http://127.0.0.1:8000/api/bookings/1 \
  -H "Authorization: Bearer $TOKEN"
```
