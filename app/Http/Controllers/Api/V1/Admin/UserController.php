<?php
namespace App\Http\Controllers\Api\V1\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::select('id','name','email','role','phone','is_active','created_at')
            ->latest();

        // Optional role filter — used by the assign-engineer dropdown
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Use a high limit (no pagination) so engineers list is always complete
        $perPage = min((int) ($request->per_page ?? 200), 200);
        $users = $query->paginate($perPage);

        return response()->json($users);
    }

    public function updateRole(Request $request, User $user): JsonResponse
    {
        $data = $request->validate(['role' => ['required', 'in:citizen,engineer,admin']]);
        $user->update(['role' => $data['role']]);
        return response()->json(['message' => "User role updated to {$data['role']}."]);
    }
}
