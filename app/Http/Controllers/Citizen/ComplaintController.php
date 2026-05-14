<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Web controller — only renders Blade page shells.
 * All actual data is fetched by the frontend via /api/v1/... endpoints.
 */
class ComplaintController extends Controller
{
    /** GET /citizen/complaints */
    public function index(): View
    {
        return view('citizen.complaints.index');
    }

    /** GET /citizen/complaints/create */
    public function create(): View
    {
        return view('citizen.complaints.create');
    }

    /** POST /citizen/complaints → handled by API — this route is no longer needed */
    public function store(): RedirectResponse
    {
        // Submissions go directly to POST /api/v1/citizen/complaints via axios.
        // This fallback redirects in case someone hits the web route directly.
        return redirect()->route('citizen.complaints.index');
    }

    /** GET /citizen/complaints/{complaint} */
    public function show(string $complaint): View
    {
        // Pass only the ID to the view — actual complaint data fetched from API.
        return view('citizen.complaints.show', ['complaintId' => $complaint]);
    }
}
