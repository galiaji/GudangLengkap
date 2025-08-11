<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Vinkla\Hashids\Facades\Hashids;

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
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users',
                'email' => 'required|email|max:255|unique:users',
                'role' => 'required|in:admin,staff,user',
                'password' => 'required|string|min:6|confirmed',
            ], [
                'password.required' => 'Password wajib diisi.',
                'password.min' => 'Password minimal 6 karakter.',
                'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            ]);

            $validated['password'] = bcrypt($validated['password']);

            User::create($validated);

            return redirect()->route('account.index')->with('success', 'Akun berhasil ditambahkan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }
    }

    public function show($hashid)
    {
        $id = Hashids::decode($hashid)[0] ?? null;
        $account = \App\Models\User::findOrFail($id);

        return view('account.show', compact('account'));
    }

    public function edit($hashid)
    {
        $id = Hashids::decode($hashid)[0] ?? null;
        $account = \App\Models\User::findOrFail($id);

        return view('account.edit', compact('account'));
    }

    public function update(Request $request, $hashid)
    {
        $id = Hashids::decode($hashid)[0] ?? null;
        $account = \App\Models\User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$account->id,
            'email' => 'required|email|max:255|unique:users,email,'.$account->id,
            'role' => 'required|in:admin,staff,user',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $account->update($validated);

        return redirect()->route('account.index')->with('success', 'Akun berhasil diupdate!');
    }

    public function destroy($hashid)
    {
        $id = Hashids::decode($hashid)[0] ?? null;
        $account = \App\Models\User::findOrFail($id);

        $account->delete();
        return redirect()->route('account.index')->with('success', 'Akun berhasil dihapus.');
    }
}
