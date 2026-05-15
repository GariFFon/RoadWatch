<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * GET /api/v1/me
     * Returns the authenticated user's profile data.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id'                 => $user->id,
                'name'               => $user->name,
                'email'              => $user->email,
                'phone'              => $user->phone,
                'role'               => $user->role,
                'gender'             => $user->gender,
                'profile_photo_url'  => $user->profile_photo_url,
                'profile_banner_url' => $user->profile_banner_url,
                'auth_provider'      => $user->auth_provider,
                'is_active'          => $user->is_active,
                'created_at'         => $user->created_at->toIso8601String(),
            ]
        ]);
    }
}
