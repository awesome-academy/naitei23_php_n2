<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
     * Tạo venue mới (sau này dùng StoreVenueRequest).
     */
    public function store(Request $request)
    {
        return response()->json([
            'message' => 'owner venues store (dummy)',
            'input'   => $request->all(),
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
    public function update(Request $request, $id)
    {
        return response()->json([
            'message' => 'owner venues update (dummy)',
            'id'      => $id,
            'input'   => $request->all(),
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
