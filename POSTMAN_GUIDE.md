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
