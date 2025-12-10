# Payment API Documentation

## Module 3: Pay After Confirmation

Sau khi booking được owner xác nhận, user có thể thanh toán.

---

## Endpoints

### 1. Create Payment (Pay for Booking)

**POST** `/api/payments`

**Auth:** Required (Sanctum)

**Description:** User thanh toán cho booking đã được xác nhận.

**Request Body:**
```json
{
  "booking_id": 1,
  "payment_method": "credit_card",
  "meta": {
    "card_last4": "4242",
    "card_type": "visa"
  }
}
```

**Validation Rules:**
- `booking_id`: required, integer, exists in bookings table
- `payment_method`: required, string, one of: `credit_card`, `debit_card`, `bank_transfer`, `e_wallet`
- `meta`: optional, object/array (any additional payment metadata)

**Success Response (201):**
```json
{
  "message": "Payment successful",
  "payment": {
    "id": 1,
    "booking_id": 1,
    "amount": "100.00",
    "payment_method": "credit_card",
    "transaction_id": "TXN_1765348470_1",
    "transaction_status": "success",
    "paid_at": "2025-12-10T06:34:30.000000Z",
    "meta": {
      "card_last4": "4242",
      "card_type": "visa"
    },
    "created_at": "2025-12-10T06:34:30.000000Z",
    "updated_at": "2025-12-10T06:34:30.000000Z",
    "booking": {
      "id": 1,
      "user_id": 1,
      "space_id": 1,
      "start_time": "2025-12-11T10:00:00.000000Z",
      "end_time": "2025-12-11T12:00:00.000000Z",
      "total_price": "100.00",
      "status": "paid",
      "paid_at": "2025-12-10T06:34:30.000000Z",
      "space": {
        "id": 1,
        "venue_id": 1,
        "name": "Conference Room A",
        "venue": {
          "id": 1,
          "name": "Coworking Space Downtown"
        }
      }
    }
  }
}
```

**Error Responses:**

**400 - Booking Not Confirmed:**
```json
{
  "message": "Payment failed",
  "error": "Booking must be confirmed before payment"
}
```

**400 - Already Paid:**
```json
{
  "message": "Payment failed",
  "error": "Booking already paid"
}
```

**400 - Unauthorized:**
```json
{
  "message": "Payment failed",
  "error": "Unauthorized to pay for this booking"
}
```

**422 - Validation Error:**
```json
{
  "message": "The booking id field is required. (and 1 more error)",
  "errors": {
    "booking_id": ["Booking not found"],
    "payment_method": ["Invalid payment method"]
  }
}
```

---

### 2. List User Payments

**GET** `/api/payments`

**Auth:** Required (Sanctum)

**Description:** Lấy danh sách tất cả payments của user đã đăng nhập.

**Query Parameters:** None

**Success Response (200):**
```json
{
  "payments": [
    {
      "id": 2,
      "booking_id": 2,
      "amount": "200.00",
      "payment_method": "bank_transfer",
      "transaction_id": "TXN_1765348480_2",
      "transaction_status": "success",
      "paid_at": "2025-12-10T06:35:00.000000Z",
      "meta": null,
      "created_at": "2025-12-10T06:35:00.000000Z",
      "updated_at": "2025-12-10T06:35:00.000000Z",
      "booking": {
        "id": 2,
        "user_id": 1,
        "space_id": 2,
        "status": "paid",
        "space": {
          "id": 2,
          "name": "Private Office",
          "venue": {
            "id": 1,
            "name": "Coworking Space Downtown"
          }
        }
      }
    },
    {
      "id": 1,
      "booking_id": 1,
      "amount": "100.00",
      "payment_method": "credit_card",
      "transaction_id": "TXN_1765348470_1",
      "transaction_status": "success",
      "paid_at": "2025-12-10T06:34:30.000000Z",
      "meta": {
        "card_last4": "4242"
      },
      "created_at": "2025-12-10T06:34:30.000000Z",
      "updated_at": "2025-12-10T06:34:30.000000Z",
      "booking": {
        "id": 1,
        "user_id": 1,
        "space_id": 1,
        "status": "paid",
        "space": {
          "id": 1,
          "name": "Conference Room A",
          "venue": {
            "id": 1,
            "name": "Coworking Space Downtown"
          }
        }
      }
    }
  ]
}
```

**Notes:**
- Payments được sắp xếp theo `created_at` DESC (mới nhất trước)
- Chỉ hiển thị payments của bookings thuộc về user đã đăng nhập
- Includes related `booking.space.venue` data

---

## Business Rules

1. **Booking Status Flow:**
   - `pending_confirmation` → Owner confirms → `confirmed`
   - `confirmed` → User pays → `paid`
   - `paid` → Complete → `completed`

2. **Payment Constraints:**
   - ❌ Cannot pay if booking status is NOT `confirmed`
   - ❌ Cannot pay twice for same booking (check `paid_at` field)
   - ❌ Can only pay for own bookings (authorization check)
   - ✅ Payment creates transaction with fake gateway (TXN_timestamp_bookingId)

3. **Database Changes:**
   - `payments` table: added `paid_at` (timestamp), `meta` (json)
   - `bookings` table: added `paid_at` (timestamp), `confirmed_at` (timestamp), status enum includes `'paid'`

4. **Transaction Safety:**
   - Uses DB::transaction() for atomicity
   - lockForUpdate() on booking to prevent race conditions
   - All validations run before payment creation

---

## Postman Examples

### Example 1: Pay for Confirmed Booking

**Request:**
```
POST http://localhost:8000/api/payments
Authorization: Bearer {your_token}
Content-Type: application/json

{
  "booking_id": 5,
  "payment_method": "credit_card",
  "meta": {
    "card_last4": "1234",
    "card_type": "mastercard",
    "cardholder_name": "John Doe"
  }
}
```

**Response:** 201 Created (see Success Response above)

---

### Example 2: Try to Pay for Pending Booking (Should Fail)

**Request:**
```
POST http://localhost:8000/api/payments
Authorization: Bearer {your_token}
Content-Type: application/json

{
  "booking_id": 3,
  "payment_method": "e_wallet"
}
```

**Response:** 400 Bad Request
```json
{
  "message": "Payment failed",
  "error": "Booking must be confirmed before payment"
}
```

---

### Example 3: Get Payment History

**Request:**
```
GET http://localhost:8000/api/payments
Authorization: Bearer {your_token}
```

**Response:** 200 OK (see List Response above)

---

## Testing

Run payment tests:
```bash
php artisan test --filter PaymentTest
```

**Test Coverage:**
- ✅ Cannot pay for pending booking
- ✅ Can pay for confirmed booking
- ✅ Cannot pay twice for same booking
- ✅ Cannot pay for other user's booking
- ✅ Can list own payments
- ✅ Validation for booking_id
- ✅ Validation for payment_method
- ✅ Authentication required

All 8 tests passing (58 assertions).

---

## Models & Database

### Payment Model Constants
```php
Payment::STATUS_PENDING = 'pending';
Payment::STATUS_SUCCESS = 'success';
Payment::STATUS_FAILED = 'failed';
```

### Booking Model Constants (Updated)
```php
Booking::STATUS_PENDING_CONFIRMATION = 'pending_confirmation';
Booking::STATUS_CONFIRMED = 'confirmed';
Booking::STATUS_PAID = 'paid';  // NEW
Booking::STATUS_COMPLETED = 'completed';
Booking::STATUS_CANCELLED = 'cancelled';
Booking::STATUS_REJECTED = 'rejected';
```

### Relationships
```php
// Payment model
$payment->booking()  // belongsTo Booking

// Booking model
$booking->payment()   // hasOne Payment (first/main payment)
$booking->payments()  // hasMany Payment (all payment attempts)
```

---

## Migration Files

1. **2025_12_10_061659_add_paid_at_and_meta_to_payments_table.php**
   - Added `paid_at` (timestamp, nullable)
   - Added `meta` (json, nullable)
   - Added index on `transaction_status`

2. **2025_12_10_062305_add_paid_status_and_timestamps_to_bookings.php**
   - Added `confirmed_at` (timestamp, nullable)
   - Added `paid_at` (timestamp, nullable)
   - Altered status enum to include `'paid'`

---

## Service Layer

**PaymentService Methods:**

1. `pay(int $bookingId, int $userId, string $paymentMethod, ?array $meta): Payment`
   - Validates booking ownership
   - Checks booking not already paid
   - Requires booking status = confirmed
   - Creates payment record with fake transaction
   - Updates booking to 'paid' status
   - Uses DB transaction for atomicity

2. `listForUser(int $userId): Collection`
   - Returns all payments for user's bookings
   - Eager loads booking.space.venue
   - Ordered by created_at DESC
