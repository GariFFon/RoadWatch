<?php
namespace App\Http\Controllers\Api\V1\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::select('id','name','email','role','phone','is_active','created_at')
            ->latest()->paginate(25);
        return response()->json($users);
    }

    public function updateRole(Request $request, User $user): JsonResponse
    {
        $data = $request->validate(['role' => ['required', 'in:citizen,engineer,admin']]);
        $user->update(['role' => $data['role']]);
        return response()->json(['message' => "User role updated to {$data['role']}."]);
    }
}
