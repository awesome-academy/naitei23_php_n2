# DB Schema - Venues / Spaces / Bookings / Payments

## 1. Bảng & quan hệ chính

- users
  - 1 - n venues (users.id = venues.owner_id)
  - 1 - n bookings (users.id = bookings.user_id)
- venues
  - 1 - n spaces
  - n - n amenities (venue_amenities)
  - n - n managers (venue_managers → users)
- spaces
  - 1 - n bookings
  - thuộc 1 venue, 1 space_type
- bookings
  - 1 - 1 payment
  - n - n services (booking_services)

## 2. Trạng thái

### bookings.status

- pending_confirmation
- awaiting_payment
- confirmed
- cancelled
- completed

### payments.transaction_status

- pending
- success
- failed

## 3. Index chính

- venues:
  - idx_venues_owner_id (owner_id)
  - idx_venues_city_status (city, status)
- spaces:
  - idx_spaces_venue (venue_id)
  - idx_spaces_type (space_type_id)
- bookings:
  - idx_bookings_user (user_id)
  - idx_bookings_space (space_id)
  - idx_bookings_status (status)
  - idx_bookings_space_time (space_id, start_time, end_time)
- payments:
  - uniq_payments_booking (booking_id)
  - idx_payments_status (transaction_status)
