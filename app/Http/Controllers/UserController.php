<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles')->get();
        $users = UserResource::collection($users);
        $users = $users->toArray($request);


        return Inertia::render('Users/Index', ['users' => $users, 'roles' => $roles]);
    }

    public function create()
    {
        $roles = Role::query()->pluck('name', 'id')->toArray();
        return Inertia::render('Users/Create', ['roles' => $roles]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required', 'email' => 'required|unique:users']);
        $data['password'] = '';
        User::create($data);
        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
    }

    public function edit(User $user)
    {
    }

    public function update(Request $request, User $user)
    {
    }

    public function destroy(User $user)
    {
    }
}
