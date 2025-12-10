<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Process payment for a booking
     * 
     * @param int $bookingId
     * @param int $userId
     * @param string $paymentMethod
     * @param array|null $meta
     * @return Payment
     * @throws \Exception
     */
    public function pay(int $bookingId, int $userId, string $paymentMethod, ?array $meta = null): Payment
    {
        return DB::transaction(function () use ($bookingId, $userId, $paymentMethod, $meta) {
            // Lock booking to prevent race conditions
            $booking = Booking::where('id', $bookingId)
                ->lockForUpdate()
                ->first();

            if (!$booking) {
                throw new \Exception('Booking not found');
            }

            // Check authorization
            if ($booking->user_id !== $userId) {
                throw new \Exception('Unauthorized to pay for this booking');
            }

            // Check if already paid (must check first)
            if ($booking->status === Booking::STATUS_PAID || $booking->paid_at !== null) {
                throw new \Exception('Booking already paid');
            }

            // Check booking status
            if ($booking->status !== Booking::STATUS_CONFIRMED) {
                throw new \Exception('Booking must be confirmed before payment');
            }

            // Simulate payment gateway (fake for now)
            $transactionId = 'TXN_' . time() . '_' . $bookingId;
            $transactionStatus = Payment::STATUS_SUCCESS;

            // Create payment record
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->total_price,
                'payment_method' => $paymentMethod,
                'transaction_id' => $transactionId,
                'transaction_status' => $transactionStatus,
                'paid_at' => now(),
                'meta' => $meta,
            ]);

            // Update booking status
            $booking->update([
                'status' => Booking::STATUS_PAID,
                'paid_at' => now(),
            ]);

            return $payment;
        });
    }

    /**
     * List all payments for a user
     * 
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listForUser(int $userId)
    {
        return Payment::whereHas('booking', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with(['booking.space.venue'])
        ->orderBy('created_at', 'desc')
        ->get();
    }
}
