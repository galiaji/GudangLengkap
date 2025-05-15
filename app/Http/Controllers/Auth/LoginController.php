<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Show the login form.
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Process the login form submission.
    public function login(Request $request)
    {
        // Validate the input data.
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Attempt to authenticate with the provided credentials.
        if (Auth::attempt($credentials)) {
            // Regenerate the session to prevent session fixation.
            $request->session()->regenerate();

            // Redirect the user to their dashboard or intended URL.
            return redirect()->intended('/dashboard');
        }

        // If authentication fails, return back with error messages.
        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->withInput();
    }
}