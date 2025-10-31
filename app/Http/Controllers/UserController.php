<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::with('role')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'User crée', 'user' => $user]);
    }

    public function show(User $user)
    {
        return $user->load('role');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role_id' => 'sometimes|required|exists:roles,id',
        ]);
        $user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'role_id' => $request->role_id ?? $user->role_id,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);
        return response()->json(['message' => 'User modifié', 'user' => $user]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User Supprimé']);
    }

    public function uploadSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        $path = $request->file('signature')->store('signatures', 'public');
        $user = $request->user();
        $user->signature_url = $path;
        $user->save();
        return response()->json(['message' => 'Signature uploaded', 'url' => $path]);
    }
}