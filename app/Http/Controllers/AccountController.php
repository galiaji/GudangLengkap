<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function index()
    {
        $users = User::all(); // atau bisa juga ->orderBy('created_at', 'desc')
        return view('account.index', compact('users'));
    }

    public function create()
    {
        return view('account.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        \App\Models\User::create($validated);

        return redirect()->route('account.index')->with('success', 'Akun berhasil ditambahkan!');
    }

    public function edit(User $account)
    {
        return view('account.edit', compact('account'));
    }

    public function update(Request $request, User $account)
    {
        $validated = $request->validate([
            'name' => 'required',
            'username' => 'required|unique:users,username,'.$account->id,
            'email' => 'required|email|unique:users,email,'.$account->id,
            'password' => 'nullable|min:6',
        ]);
        if ($request->password) {
            $validated['password'] = bcrypt($request->password);
        } else {
            unset($validated['password']);
        }
        $account->update($validated);
        return redirect()->route('account.index')->with('success', 'Akun berhasil diupdate!');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('account.manage')->with('success', 'Akun berhasil dihapus.');
    }
}
