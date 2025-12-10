<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Process payment for a booking
     * 
     * @param StorePaymentRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        $payment = $this->paymentService->pay(
            $request->booking_id,
            $request->user()->id,
            $request->payment_method,
            $request->meta
        );

        return response()->json([
            'message' => 'Payment successful',
            'data' => $payment->load('booking.space.venue'),
        ], 201);
    }

    /**
     * List all payments for authenticated user
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $payments = $this->paymentService->listForUser(auth()->id());

        return response()->json([
            'payments' => $payments,
        ]);
    }
}
