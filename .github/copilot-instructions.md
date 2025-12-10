# Copilot Instructions - Workspace Booking System

## Project Overview

Laravel 10 REST API for a workspace booking platform. Vietnamese-language internship project (`naitei23_php_n2`) with multi-role authentication (Admin, Owner, User) using Laravel Sanctum.

## Architecture Patterns

### API Response Convention
**Always use helper functions** for JSON responses:
```php
return api_success($data, 'Message', 200);  // Success
return api_error('Error message', 400, $errors);  // Error
```
Defined in `app/helpers.php` via `ApiResponse` class. Standard format: `{success, message, data/errors}`.

### Authentication & Authorization
- **Sanctum tokens** for all authenticated routes (`auth:sanctum` middleware)
- **Role-based access:** `role:admin`, `role:admin,moderator` middleware (see `app/Http/Middleware/CheckRole.php`)
- **Policies for ownership:** Use `$this->authorize('view', $venue)` in controllers before operations
  - Example: `VenuePolicy` checks `$user->id === $venue->owner_id` for CRUD operations
- **User model quirks:**
  - DB field: `password_hash` (not `password`)
  - DB field: `full_name` (not `name`)
  - Accessors/mutators in `User.php` provide compatibility aliases

### Controller Structure
Three controller namespaces by role:
- `App\Http\Controllers\Owner\*` - Venue management (requires auth + owner check)
- `App\Http\Controllers\Admin\*` - User administration (requires `role:admin`)
- `App\Http\Controllers\Api\*` - User booking operations (requires `auth:sanctum`)
- Public routes: `PublicVenueController`, `SearchSpaceController`, `MapController`

### Service Layer Pattern
Complex business logic lives in `app/Services/`:
- `BookingService`: Handles time validation, overlap checking, price calculation
- Use DB transactions for multi-step operations:
  ```php
  return DB::transaction(function () use ($data) {
      // Multiple database operations
  });
  ```

## Database Schema Key Points

### Status Enums (defined as constants in models)
- **Venues:** `pending`, `approved`, `blocked` (`Venue::STATUS_*`)
- **Bookings:** `pending_confirmation`, `awaiting_payment`, `confirmed`, `paid`, `cancelled`, `completed`
- **Payments:** `pending`, `success`, `failed`

### Critical Relationships
- `User` → owns `Venues` (1:n) → contain `Spaces` (1:n) → receive `Bookings` (1:n)
- `Venue` ↔ `Amenity` (n:n via `venue_amenities`)
- `Space` ↔ `Amenity` (n:n via `space_amenities`)
- `Booking` → has one `Payment` (1:1)
- `Venue` ↔ `User` (managers via `venue_managers`)

See `docs/db-schema-venues-bookings.md` for indexed queries.

## Development Workflows

### Initial Setup
```bash
composer install && npm install && npm run build
cp .env.example .env
php artisan key:generate
# Configure DB_DATABASE=workspace_booking in .env
php artisan migrate:fresh --seed
php artisan serve
```

### Demo Accounts (from seeder)
- **Admin:** admin@workspace.com / admin123
- **Owner:** owner@workspace.com / password

### Testing
```bash
vendor/bin/phpunit                    # Run all tests
vendor/bin/phpunit --filter Booking   # Filter tests
```
Tests use `RefreshDatabase` trait. See `tests/Feature/BookingCancelTest.php` for examples using Sanctum auth: `Sanctum::actingAs($user)`.

### API Testing with Postman
See `POSTMAN_GUIDE.md` for full workflow. Key points:
- Environment variables: `{{base_url}}`, `{{token}}`
- Collections in `postman/` directory with auto-save token scripts
- All owner routes require: `Authorization: Bearer {{token}}`

## Code Conventions

### Language Standards
- **Vietnamese only for:** Validation error messages in FormRequest `messages()` method
- **English for everything else:** API responses, log messages, email/notifications, code, comments
- **Examples:**
  ```php
  // ✅ GOOD: English API messages
  return api_success($data, 'Venue created successfully');
  return response()->json(['message' => "Deleted venue '{$venueName}' with ID {$venueId}"]);
  
  // ✅ GOOD: Vietnamese validation errors
  public function messages(): array
  {
      return [
          'name.required' => 'Tên địa điểm là bắt buộc.',
          'address.required' => 'Địa chỉ là bắt buộc.',
      ];
  }
  
  // ✅ GOOD: English code/comments
  public function store(StoreVenueRequest $request)
  {
      // Prevent owner_id injection
      unset($data['owner_id']);
  }
  ```

### Validation
Use FormRequest classes in `app/Http/Requests/`:
- Separate by role: `Owner\StoreVenueRequest`, `StoreBookingRequest`
- Vietnamese error messages in `messages()` method
- Never trust client input - unset protected fields:
  ```php
  unset($data['owner_id'], $data['status']); // Prevent injection
  ```

### Resource Transformation
Use API Resources (`app/Http/Resources/`) for consistent response formatting:
```php
return VenueResource::collection($venues);  // For lists
return new VenueResource($venue);           // For single items
```

### Response Messages Pattern
Success messages should be descriptive in English:
```php
// ✅ GOOD: Include entity details in English
return api_success($venue, "Venue '{$venue->name}' created successfully");
return response()->json(['message' => "Deleted venue '{$venueName}' with ID {$venueId}"]);

// ❌ AVOID: Generic messages without context
return response()->json(['message' => 'Success']);
```

### Eager Loading
Always eager load relationships to avoid N+1:
```php
$venues = Venue::with(['amenities', 'spaces'])->get();
$bookings = Booking::with('space.venue')->ownedBy($userId)->get();
```

### Route Organization (`routes/api.php`)
- Public routes first (search, map, venue details)
- Auth routes grouped by middleware
- Owner routes prefixed with `/api/owner/`
- Admin routes prefixed with `/api/admin/` with `role:admin` middleware

## Critical Files

- `app/helpers.php` - Autoloaded global functions (`api_success`, `api_error`)
- `app/Support/ApiResponse.php` - Standardized JSON response builder
- `app/Http/Middleware/CheckRole.php` - Role validation logic
- `app/Services/BookingService.php` - Complex booking validation & creation
- `docs/db-schema-venues-bookings.md` - Database structure reference
- `POSTMAN_GUIDE.md` - API testing walkthrough (Vietnamese)

## Common Pitfalls

1. **Don't use `name` or `password` fields** - User model uses `full_name` and `password_hash`
2. **Always check ownership** - Use policies, don't trust route model binding alone
3. **Status transitions** - Validate booking status before state changes (see `BookingService::cancel`)
4. **Time validation** - Bookings must respect `open_time`/`close_time` on Spaces
5. **Duplicate prevention** - Check for overlapping bookings via `ensureNoOverlap()` in `BookingService`
