<?php

namespace App\Http\Controllers;

use App\Http\Resources\AmenityResource;
use App\Models\Amenity;

class AmenityController extends Controller
{
    /**
     * GET /api/amenities
     * Trả về danh sách toàn bộ amenities (public/shared).
     */
    public function index()
    {
        $amenities = Amenity::all();

        return AmenityResource::collection($amenities);
    }
}
