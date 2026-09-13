<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.index', [
            'settings' => BusinessSetting::query()->firstOrCreate(
                ['id' => 1],
                [
                    'company_name' => 'Guns & Wildlife',
                    'phone' => '+92 300 0000000',
                    'email' => 'info@gunswildlife.pk',
                    'address' => 'Main Boulevard, Lahore, Pakistan',
                    'logo_path' => null,
                ],
            ),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = BusinessSetting::query()->firstOrCreate(['id' => 1]);
        $changePassword = $request->filled('current_password')
            || $request->filled('new_password')
            || $request->filled('new_password_confirmation');

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'current_password' => [$changePassword ? 'required' : 'nullable', 'current_password'],
            'new_password' => [
                $changePassword ? 'required' : 'nullable',
                'confirmed',
                Password::min(8),
            ],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('business-settings', 'public');
        }

        unset($data['logo'], $data['current_password'], $data['new_password']);

        $settings->update($data);

        if ($changePassword) {
            $request->user()->update([
                'password' => $request->input('new_password'),
            ]);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('status', $changePassword
                ? 'Receipt settings and password updated successfully.'
                : 'Receipt settings updated successfully.');
    }
}
