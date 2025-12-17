<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminStatisticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminStatisticsController extends Controller
{
    public function __construct(
        private AdminStatisticsService $statisticsService
    ) {}

    /**
     * Get dashboard statistics.
     * 
     * GET /api/admin/statistics
     * 
     * Query params:
     * - month: YYYY-MM (optional, defaults to current month)
     */
    public function index(Request $request): JsonResponse
    {
        $month = $request->query('month');
        
        $statistics = $this->statisticsService->getStatistics($month);

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }
}
