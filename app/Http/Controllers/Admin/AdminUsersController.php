<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminBootstrapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUsersController extends Controller
{
    public function store(Request $request, AdminBootstrapService $adminBootstrapService): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:10'],
        ]);

        $admin = $adminBootstrapService->createAdmin(
            $data['name'],
            $data['email'],
            $data['password']
        );

        return response()->json([
            'user_id' => $admin->id,
            'email' => $admin->email,
            'role' => $admin->role,
        ]);
    }
}
