<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Space;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    /**
     * Tạo booking mới với validation đầy đủ.
     */
    public function create(User $user, array $data): Booking
    {
        return DB::transaction(function () use ($user, $data) {
            $space = Space::with('venue')->findOrFail($data['space_id']);

            $start = Carbon::parse($data['start_time']);
            $end   = Carbon::parse($data['end_time']);

            // Validate thời gian trong open hours
            $this->validateTimeWithinOpenHours($space, $start, $end);

            // Check không bị trùng với booking khác
            $this->ensureNoOverlap($space, $start, $end);

            // Tính tổng tiền
            $totalPrice = $this->calculatePrice($space, $start, $end);

            // Tạo booking
            $booking = Booking::create([
                'user_id'     => $user->id,
                'space_id'    => $space->id,
                'start_time'  => $start,
                'end_time'    => $end,
                'total_price' => $totalPrice,
                'status'      => Booking::STATUS_PENDING_CONFIRMATION,
                'note'        => $data['note'] ?? null,
            ]);

            return $booking->load('space.venue');
        });
    }

    /**
     * Hủy booking (cancel before making payment).
     * 
     * Rules:
     * - Cannot cancel if booking has successful payment
     * - Can only cancel if status is pending_confirmation or confirmed
     * - User must own the booking (checked in controller policy)
     */
    public function cancel(Booking $booking): void
    {
        // 1. Check if booking has successful payment
        $hasSuccessPayment = $booking->payments()
            ->where('transaction_status', \App\Models\Payment::STATUS_SUCCESS)
            ->exists();

        if ($hasSuccessPayment) {
            throw ValidationException::withMessages([
                'booking' => 'Paid booking cannot be cancelled.',
            ]);
        }

        // 2. Only allow cancel for pending or confirmed bookings
        if (!in_array($booking->status, [
            Booking::STATUS_PENDING_CONFIRMATION,
            Booking::STATUS_CONFIRMED,
        ], true)) {
            throw ValidationException::withMessages([
                'booking' => 'Only pending or confirmed bookings can be cancelled.',
            ]);
        }

        // 3. Update status to cancelled
        $booking->status = Booking::STATUS_CANCELLED;
        $booking->save();

        // 4. Emit event if exists
        if (class_exists(\App\Events\BookingCancelled::class)) {
            event(new \App\Events\BookingCancelled($booking));
        }
    }

    /**
     * Validate booking time nằm trong giờ mở cửa.
     */
    protected function validateTimeWithinOpenHours(Space $space, Carbon $start, Carbon $end): void
    {
        // Space có open_hour và close_hour dạng "08:00", "18:00"
        $open  = Carbon::parse($start->format('Y-m-d') . ' ' . $space->open_hour);
        $close = Carbon::parse($start->format('Y-m-d') . ' ' . $space->close_hour);

        if ($start->lt($open) || $end->gt($close)) {
            throw ValidationException::withMessages([
                'time' => 'Booking time must be within space opening hours (' . $space->open_hour . ' - ' . $space->close_hour . ').',
            ]);
        }
    }

    /**
     * Đảm bảo không trùng với booking confirmed/paid khác.
     */
    protected function ensureNoOverlap(Space $space, Carbon $start, Carbon $end): void
    {
        $hasOverlap = $space->bookings()
            ->whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_AWAITING_PAYMENT,
            ])
            ->where(function ($q) use ($start, $end) {
                // Case 1: booking mới bắt đầu hoặc kết thúc trong khoảng có booking
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereBetween('end_time', [$start, $end])
                  // Case 2: booking cũ bao trùm booking mới
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_time', '<=', $start)
                         ->where('end_time', '>=', $end);
                  });
            })
            ->exists();

        if ($hasOverlap) {
            throw ValidationException::withMessages([
                'time' => 'This time slot is already booked.',
            ]);
        }
    }

    /**
     * Tính tổng tiền dựa trên duration và price của space.
     * 
     * Space có 3 loại giá: price_per_hour, price_per_day, price_per_month
     * Tự động chọn loại giá phù hợp nhất.
     */
    protected function calculatePrice(Space $space, Carbon $start, Carbon $end): float
    {
        $durationInMinutes = $start->diffInMinutes($end);
        $durationInHours = $durationInMinutes / 60;
        $durationInDays = $durationInMinutes / (60 * 24);

        // Ưu tiên: nếu book >= 30 ngày → dùng price_per_month
        if ($durationInDays >= 30 && $space->price_per_month > 0) {
            $months = ceil($durationInDays / 30);
            return $months * $space->price_per_month;
        }

        // Nếu book >= 1 ngày → dùng price_per_day
        if ($durationInDays >= 1 && $space->price_per_day > 0) {
            $days = ceil($durationInDays);
            return $days * $space->price_per_day;
        }

        // Mặc định: dùng price_per_hour
        $hours = ceil($durationInHours);
        return $hours * $space->price_per_hour;
    }
}
