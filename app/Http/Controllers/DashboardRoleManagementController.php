<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardRoleManagementController extends Controller
{
    public function index(Request $request){
        $search = $request->query('search');
        $perPage = $request->query('per_page', 9);

        $query = User::query()->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate($perPage)->appends($request->only(['search', 'per_page']));

        return Inertia::render('Dashboard/UserRoles/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ]
        ]);
    }

    public function assign(Request $request){
        $data = $request->validate([
            'users' => 'required|array',
            'users.*.user_id' => 'required|exists:users,user_id',
            'users.*.is_admin' => 'required|boolean',
        ]);

        foreach($data['users'] as $userData){
            if(auth()->user()->user_id === $userData['user_id']){
                continue; // skip self
            }
            $user = User::find($userData['user_id']);
            if($user->is_admin != $userData['is_admin']){
                ActivityLog::create([
                    'user_id' => Auth::user()->user_id,
                    'action' => 'user.role_changed',
                    'meta' => json_encode([
                        'username' => $user->username,
                        'is_admin' => $userData['is_admin'],
                    ]),
                ]);
            }
            $user->is_admin = $userData['is_admin'];
            $user->save();
        }

        return redirect()->back()->with('flash', [
            'type' => 'success',
            'message' => 'User roles updated successfully.',
        ]);
    }
}
