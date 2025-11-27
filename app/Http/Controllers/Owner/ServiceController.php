<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreServiceRequest;
use App\Http\Requests\Owner\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Models\Venue;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * GET /api/owner/venues/{venue}/services
     * List services của 1 venue thuộc owner.
     */
    public function indexByVenue(Request $request, Venue $venue)
    {
        $this->authorize('view', $venue);

        $services = $venue->services()
            ->orderBy('created_at', 'desc')
            ->get();

        return ServiceResource::collection($services);
    }

    /**
     * POST /api/owner/venues/{venue}/services
     * Tạo service mới cho 1 venue.
     */
    public function store(StoreServiceRequest $request, Venue $venue)
    {
        $this->authorize('update', $venue);

        $data = $request->validated();
        $data['venue_id'] = $venue->id;

        $service = Service::create($data);

        return (new ServiceResource($service))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * PUT /api/owner/services/{service}
     * Cập nhật service.
     */
    public function update(UpdateServiceRequest $request, Service $service)
    {
        // Check quyền thông qua venue
        $this->authorize('update', $service->venue);

        $service->update($request->validated());

        return new ServiceResource($service);
    }

    /**
     * DELETE /api/owner/services/{service}
     * Xoá service.
     */
    public function destroy(Service $service)
    {
        $this->authorize('delete', $service->venue);

        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully',
        ]);
    }
}
