<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route($this->redirectRouteForUser(Auth::user()));
        }

        return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Invalid email or password.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()
            ->route($this->redirectRouteForUser($request->user()))
            ->with('status', 'Login successful.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'You have been logged out.');
    }

    private function redirectRouteForUser($user): string
    {
        return $user->isAdmin() ? 'admin.dashboard' : $user->dashboardRouteName();
    }
}
