<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ComplaintOptionsController extends Controller
{
    /**
     * Return all available complaint statuses and severities.
     * Used by admin/engineer filter UIs to populate dropdowns dynamically.
     *
     * GET /api/v1/complaint-options
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'statuses' => [
                ['value' => 'pending',               'label' => 'Pending',               'icon' => '⏳'],
                ['value' => 'under_review',          'label' => 'Under Review',          'icon' => '🔍'],
                ['value' => 'in_progress',           'label' => 'In Progress',           'icon' => '🔧'],
                ['value' => 'awaiting_verification', 'label' => 'Awaiting Verification', 'icon' => '🕐'],
                ['value' => 'verified',              'label' => 'Verified ✓',            'icon' => '✅'],
                ['value' => 'rejected',              'label' => 'Rejected',              'icon' => '❌'],
            ],
            'severities' => [
                ['value' => 'low',       'label' => 'Low',       'icon' => '🟢'],
                ['value' => 'medium',    'label' => 'Medium',    'icon' => '🟡'],
                ['value' => 'high',      'label' => 'High',      'icon' => '🟠'],
                ['value' => 'emergency', 'label' => 'Emergency', 'icon' => '🔴'],
            ],
        ]);
    }
}
