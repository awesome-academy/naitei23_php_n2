# 🎯 HƯỚNG DẪN DEMO API CHO MENTOR

**Chuẩn bị:** 10 phút | **Demo:** 15-20 phút | **Độ khó:** ⭐⭐☆☆☆

---

## 📋 Checklist trước khi demo

### 1. Backend chạy OK
```bash
# Terminal 1: Start server
php artisan serve
# ✅ Đang chạy: http://127.0.0.1:8000

# Terminal 2: Check database
php artisan migrate:fresh --seed
# ✅ 23 migrations run, roles seeded
```

### 2. Thunder Client setup
- ✅ Extension đã cài (icon tia sét bên trái VSCode)
- ✅ Import environment: `thunder-client/thunder-environment_Local.json`
- ✅ Import collection: `thunder-client/thunder-collection_Owner-API-Demo.json`
- ✅ Active environment: **Local**

### 3. Tạo user owner để test
```bash
php artisan tinker
```
```php
$owner = App\Models\User::firstOrCreate(
    ['email' => 'owner@workspace.com'],
    ['full_name' => 'Owner Demo', 'password_hash' => Hash::make('password')]
);
// ✅ Created owner user

// Tạo thêm user để test manager
App\Models\User::firstOrCreate(
    ['email' => 'manager@test.com'],
    ['full_name' => 'Manager Test', 'password_hash' => Hash::make('password')]
);
// ✅ Created manager user

exit;
```

---

## 🎬 SCRIPT DEMO (đọc từng bước)

### 🔐 Bước 0: Login & Lấy Token

**Nói:**
> Đầu tiên em sẽ login để lấy Sanctum token. API này em đã merge từ nhánh auth của team.

**Làm:**
1. Click request **"Auth / Login as Owner"**
2. Check body:
   ```json
   {
     "email": "owner@workspace.com",
     "password": "password"
   }
   ```
3. Click **Send** ⚡
4. Response hiện `"success": true` và có `token`
5. **Quan trọng:** Token tự động lưu vào environment (nhờ Tests script)

**Nói thêm:**
> Response trả về token Sanctum. Em đã config Thunder Client tự động lưu token vào environment, nên các request sau sẽ dùng chung token này.

---

### 📍 Bước 1: Venue CRUD

#### 1.1. List Venues (rỗng)

**Nói:**
> Giờ em test API quản lý venue của owner. Đầu tiên là list venues.

**Làm:**
- Click **"1.1 List My Venues"**
- Send ⚡
- Response: `"data": []` (mảng rỗng)

**Nói:**
> API trả về mảng rỗng vì owner này chưa có venue nào. Backend tự filter `WHERE owner_id = user->id` nên chỉ thấy venue của mình.

---

#### 1.2. Create Venue

**Nói:**
> Bây giờ em tạo venue mới.

**Làm:**
- Click **"1.2 Create Venue"**
- Show body (đã điền sẵn):
  ```json
  {
    "name": "Cozy Co-working Space",
    "address": "123 Phố Huế, Hà Nội",
    "city": "Hanoi",
    ...
  }
  ```
- Send ⚡
- Response: Status **201 Created**, có `"id": 1`

**Nói thêm:**
> API validate input, tự gán `owner_id` = user đang login, lưu vào database. `venueId` cũng tự động lưu vào environment để dùng cho các request sau.

---

#### 1.3. List lại (có data)

**Làm:**
- Click lại **"1.1 List My Venues"**
- Send ⚡
- Response: Có 1 venue trong `data`

**Nói:**
> Sau khi tạo xong, gọi lại list thì venue vừa tạo xuất hiện.

---

#### 1.4. Update Venue

**Nói:**
> Test API update venue.

**Làm:**
- Click **"1.4 Update Venue"**
- Chỉ vào URL: `{{venueId}}` đã tự động thay = 1
- Show body có thay đổi name, phone
- Send ⚡
- Response: Status **200**, name đã update

**Nói thêm:**
> API này có SpacePolicy check quyền: chỉ owner của venue đó hoặc admin mới update được. Nếu user khác gọi sẽ bị 403 Forbidden.

---

### 🎨 Bước 2: Venue Amenities

#### 2.1. Get Amenities

**Nói:**
> Owner có thể set amenities cho venue, như WiFi, máy lạnh, chỗ đỗ xe...

**Làm:**
- Click **"2.1 Get Venue Amenities"**
- Send ⚡
- Response: `"data": []` (chưa set)

---

#### 2.2. Update Amenities

**Làm:**
- Click **"2.2 Update Venue Amenities"**
- Show body:
  ```json
  {
    "amenity_ids": [1, 2, 3]
  }
  ```
- Send ⚡
- Response: Status **200**, có list amenities

**Nói:**
> API này sync vào bảng pivot `venue_amenities`. Backend dùng `syncWithoutDetaching` nên không bị duplicate.

---

#### 2.3. Verify qua Public API

**Nói:**
> Giờ em check xem FE có thấy amenities không qua public API.

**Làm:**
- Scroll xuống folder **"6. Public APIs"**
- Click **"6.1 Public Venue Detail"**
- Send ⚡
- Response có:
  ```json
  {
    "id": 1,
    "name": "...",
    "amenities": [
      {"id": 1, "amenity_name": "WiFi"},
      {"id": 2, "amenity_name": "Projector"}
    ],
    "spaces": []
  }
  ```

**Nói:**
> Public API này không cần token, dùng cho FE/Map hiển thị. Response dùng VenueResource nên format chuẩn, có nested amenities và spaces.

---

### 🏢 Bước 3: Space CRUD

#### 3.1. List Spaces (rỗng)

**Nói:**
> Mỗi venue có nhiều space. Em test API quản lý space.

**Làm:**
- Click **"3.1 List Spaces in Venue"**
- Send ⚡
- Response: `"data": []`

---

#### 3.2. Create Space

**Làm:**
- Click **"3.2 Create Space in Venue"**
- Show body:
  ```json
  {
    "name": "Phòng họp 01",
    "space_type_id": 1,
    "capacity": 6,
    "price_per_hour": 100000,
    "open_time": "08:00",
    "close_time": "21:00"
  }
  ```
- Send ⚡
- Response: Status **201**, có `"id": 1`

**Nói:**
> API validate: capacity > 0, price > 0, open_time < close_time. SpacePolicy check owner của venue mới tạo được space.

---

#### 3.3. Show Space Detail

**Làm:**
- Click **"3.3 Show Space Detail"**
- Send ⚡
- Response: Chi tiết space vừa tạo

---

#### 3.4. Update Space

**Làm:**
- Click **"3.4 Update Space"**
- Show body thay đổi capacity, price
- Send ⚡
- Response: Status **200**, data updated

---

#### 3.5. Public Space Detail

**Nói:**
> Giờ em check public API của space này.

**Làm:**
- Click **"6.2 Public Space Detail"**
- Send ⚡
- Response:
  ```json
  {
    "id": 1,
    "name": "Phòng họp 01",
    "venue": {
      "id": 1,
      "name": "Cozy Co-working Space"
    },
    "capacity": 6,
    "upcoming_bookings": []
  }
  ```

**Nói:**
> Public space API dùng SpaceResource, có nested venue info (dùng VenueShortResource để tránh circular reference) và upcoming bookings trong 30 ngày tới.

---

### 🎨 Bước 4: Space Amenities

**Nói:**
> Space cũng có amenities riêng, như projector, bảng trắng...

**Làm:**
- Click **"4.2 Update Space Amenities"**
- Body: `{"amenity_ids": [1, 2, 4]}`
- Send ⚡
- Response: Status **200**

**Nói:**
> Tương tự venue amenities, sync vào bảng pivot `space_amenities`.

---

### 👥 Bước 5: Manager Assignment

#### 5.1. List Managers

**Nói:**
> Owner có thể gán thêm manager để cùng quản lý venue.

**Làm:**
- Click **"5.1 List Venue Managers"**
- Send ⚡
- Response: `"data": []`

---

#### 5.2. Add Manager

**Làm:**
- Click **"5.2 Add Manager to Venue"**
- Body:
  ```json
  {
    "email": "manager@test.com"
  }
  ```
- Send ⚡
- Response: Status **200**, có manager info

**Nói:**
> Backend tìm user theo email, gán role `manager` nếu chưa có (dùng helper `assignRole` từ nhánh auth), rồi insert vào `venue_managers`.

---

#### 5.3. Remove Manager

**Làm:**
- Click **"5.3 Remove Manager from Venue"**
- Send ⚡
- Response: Status **200**

**Nói:**
> API này xóa liên kết trong `venue_managers`. Role manager của user vẫn giữ nguyên (có thể manage venue khác).

---

### 🎬 Bước 6: Tổng kết

**Nói:**
> Vậy là em đã demo xong 17 API endpoints:
> 
> - **Venue CRUD**: List, Create, Update, Delete ✅
> - **Venue Amenities**: Get, Update ✅
> - **Space CRUD**: List, Create, Show, Update, Delete ✅
> - **Space Amenities**: Get, Update ✅
> - **Manager Assignment**: List, Add, Remove ✅
> - **Public APIs**: Venue Detail, Space Detail ✅
>
> Tất cả đều có:
> - ✅ Authentication (Sanctum token)
> - ✅ Authorization (Policy check owner_id hoặc admin)
> - ✅ Validation (Request validation)
> - ✅ Standard response format (api_success/error helpers)
> - ✅ Resource transformers (VenueResource, SpaceResource...)
>
> Em có viết test tự động, chạy `php artisan test:api-checklist` thì 17/17 tests pass 100%.

---

## 💡 Tips khi demo

### ✅ Nên làm:
- Nói chậm, rõ ràng
- Chỉ vào URL/body trước khi Send
- Highlight phần quan trọng trong response
- Giải thích ngắn gọn logic backend (1-2 câu)

### ❌ Không nên:
- Nói quá kỹ thuật (không cần nhắc tên class, method)
- Demo quá nhanh (mentor không kịp xem response)
- Bỏ qua lỗi nếu có (phải giải thích nguyên nhân)
- Quên check token còn hạn không

### 🚨 Xử lý lỗi

**Nếu 401 Unauthorized:**
> À, token hết hạn. Em login lại nhanh.
→ Chạy lại request "Auth / Login as Owner"

**Nếu 403 Forbidden:**
> Đúng rồi, đây là demo policy hoạt động. User này không phải owner venue nên bị chặn.

**Nếu 500 Internal Server Error:**
> Em check log backend nhanh... (mở terminal xem `storage/logs/laravel.log`)

---

## 📊 Kết quả mong đợi

Sau khi demo xong, mentor sẽ thấy:

✅ **Backend API hoạt động đầy đủ**
- Authentication/Authorization OK
- CRUD operations OK
- Relationships OK (venue-space, venue-manager, amenities)
- Validation OK

✅ **Code quality**
- Policy-based authorization
- Resource transformers
- Standard response format
- Clean separation of concerns

✅ **Test coverage**
- 17/17 automated tests pass
- Checklist document đầy đủ

---

## 🎯 Câu hỏi thường gặp từ mentor

**Q: "Policy check như thế nào?"**
> A: Em dùng `SpacePolicy` với method `view`, `update`, `delete`. Trong đó check `$user->id === $space->venue->owner_id` hoặc `$user->isAdmin()`. Controller gọi `$this->authorize('update', $space)`.

**Q: "Token lưu ở đâu?"**
> A: Token Sanctum lưu trong bảng `personal_access_tokens`. Khi login, em gọi `$user->createToken('name')->plainTextToken`. FE lưu token này và gửi qua header `Authorization: Bearer {token}`.

**Q: "Response format có chuẩn không?"**
> A: Em tạo helper `api_success()` và `api_error()` trong `app/helpers.php`, autoload qua composer. Format: `{ "success": true/false, "message": "...", "data": {...} }`.

**Q: "Có test không?"**
> A: Có ạ, em viết artisan command `php artisan test:api-checklist` test tất cả 17 endpoints. Kết quả 100% pass. Em cũng có PHPUnit test suite nếu cần.

---

**Chúc bạn demo thành công!** 🚀

_Nếu gặp vấn đề, check API_CHECKLIST_RESULTS.md để xem chi tiết implementation._
