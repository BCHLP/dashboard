<?php

namespace App\Http\Controllers;

use App\Actions\CreateUser;
use App\Actions\DeleteUser;
use App\Actions\UpdateUser;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles')->get();
        $users = UserResource::collection($users);
        $users = $users->toArray($request);

        return Inertia::render('Users/Index', ['users' => $users]);
    }

    public function create()
    {
        $roles = Role::all();

        return Inertia::render('Users/Create', ['roles' => $roles]);
    }

    public function store(Request $request, CreateUser $createUser)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users',
            'role' => 'string|nullable',
        ]);

        $createUser($data['name'], $data['email'], $data['role']);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user) {}

    public function edit(User $user)
    {
        $user->load('roles');
        $userResource = new UserResource($user);
        $roles = Role::all();

        return Inertia::render('Users/Edit', [
            'user' => $userResource->toArray(request()),
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user, UpdateUser $updateUser)
    {
        $data = $request->validate([
            'name' => 'required',
            'role' => 'string|nullable',
        ]);

        $updateUser($user, $data['name'], $data['role']);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user, DeleteUser $deleteUser)
    {
        $deleteUser($user);
    }
}
