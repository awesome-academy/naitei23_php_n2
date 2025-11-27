<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Owner\StoreVenueRequest;
use App\Http\Requests\Owner\UpdateVenueRequest;
// use App\Models\Venue; // sẽ dùng ở Task sau, giờ có thể comment tạm

class VenueController extends Controller
{
    /**
     * GET /api/owner/venues
     * Trả về list venue của chủ nhà (sau này).
     */
    public function index()
    {
        return response()->json([
            'message' => 'owner venues index (dummy)',
        ]);
    }

    /**
     * POST /api/owner/venues
     * Tạo venue mới.
     */
    public function store(StoreVenueRequest $request)
    {
        $data = $request->validated();

        return response()->json([
            'message' => 'valid store venue request',
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/owner/venues/{venue}
     * Xem chi tiết venue.
     */
    public function show($id)
    {
        return response()->json([
            'message' => 'owner venues show (dummy)',
            'id'      => $id,
        ]);
    }

    /**
     * PUT /api/owner/venues/{venue}
     * Update venue.
     */
    public function update(UpdateVenueRequest $request, $id)
    {
        $data = $request->validated();

        return response()->json([
            'message' => 'valid update venue request',
            'id'      => $id,
            'data'    => $data,
        ]);
    }

    /**
     * DELETE /api/owner/venues/{venue}
     * Xoá venue.
     */
    public function destroy($id)
    {
        return response()->json([
            'message' => 'owner venues destroy (dummy)',
            'id'      => $id,
        ]);
    }
}
