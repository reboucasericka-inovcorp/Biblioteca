<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    /**
     * Lista utilizadores com nome, email e role (apenas Admin).
     */
    public function index(Request $request)
    {
        $query = User::query()->with('roles');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $sort = $request->get('sort', 'name');
        $dir = $request->get('dir', 'asc');
        $query->orderBy($sort, $dir);

        $paginator = $query->paginate(15);

        $paginator->getCollection()->transform(function (User $user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()?->name ?? 'Cidadao',
            ];
        });

        return ApiResponse::success($paginator);
    }

    /**
     * Atualiza a role do utilizador (apenas Admin).
     */
    public function updateRole(UpdateUserRoleRequest $request, User $user)
    {
        $newRole = $request->validated('role');

        $user->syncRoles([$newRole]);

        return ApiResponse::success([
            'id' => $user->id,
            'role' => $newRole,
        ], 'Role updated successfully.');
    }
}
