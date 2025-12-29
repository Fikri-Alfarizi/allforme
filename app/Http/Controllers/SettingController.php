<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display settings page.
     */
    public function index()
    {
        $user = auth()->user();
        $settings = $user->settings;

        // Create default settings if not exists
        if (!$settings) {
            $settings = Setting::create([
                'user_id' => $user->id,
            ]);
        }

        return view('settings.index', compact('settings'));
    }

    /**
     * Update general settings.
     */
    /**
     * Update general settings via AJAX.
     */
    public function updateGeneralAjax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currency' => 'nullable|string|max:10',
            'language' => 'nullable|string|max:5',
            'timezone' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 400);
        }

        $user = auth()->user();
        $settings = $user->settings;

        // Create if not exists (defensive)
        if (!$settings) {
            $settings = Setting::create(['user_id' => $user->id]);
        }

        $updateData = [];
        if ($request->has('currency')) $updateData['currency'] = $request->currency;
        if ($request->has('language')) $updateData['language'] = $request->language;
        if ($request->has('timezone')) $updateData['timezone'] = $request->timezone;

        $settings->update($updateData);

        return response()->json([
            'success' => true, 
            'message' => 'Pengaturan berhasil disimpan.',
            'settings' => $settings
        ]);
    }

    /**
     * Update general settings (Legacy/Fallback).
     */
    public function updateGeneral(Request $request)
    {
        // ... (Keep existing if needed or remove if fully replaced)
        // Ideally we keep it compatible or redirect to new method logic
        return $this->updateGeneralAjax($request); 
    }

    /**
     * Update notification settings.
     */
    public function updateNotifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_enabled' => 'boolean',
            'ai_enabled' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();
        $settings = $user->settings;

        $settings->update([
            'notification_enabled' => $request->notification_enabled ?? false,
            'ai_enabled' => $request->ai_enabled ?? false,
        ]);

        return back()->with('success', 'Pengaturan notifikasi berhasil diupdate!');
    }

    /**
     * Update security settings.
     */
    public function updateSecurity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vault_timeout_minutes' => 'required|integer|min:1|max:120',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();
        $settings = $user->settings;

        $settings->update([
            'vault_timeout_minutes' => $request->vault_timeout_minutes,
        ]);

        return back()->with('success', 'Pengaturan keamanan berhasil diupdate!');
    }

    /**
     * Update profile.
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();
        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && str_contains($user->avatar, 'storage/avatars')) {
                $oldPath = str_replace(asset('storage/'), '', $user->avatar);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = asset('storage/' . $path);
        }

        $user->update($data);

        return back()->with('success', 'Profil berhasil diupdate!');
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();

        // Verify current password
        if (!\Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Password saat ini salah!');
        }

        $user->update([
            'password' => \Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password berhasil diubah!');
    }

    /**
     * Set custom preference.
     */
    public function setPreference(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:100',
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = auth()->user();
        $settings = $user->settings;

        $settings->setPreference($request->key, $request->value);

        return response()->json([
            'success' => true,
            'message' => 'Preference saved!',
        ]);
    }

    /**
     * Get custom preference.
     */
    public function getPreference(Request $request)
    {
        $key = $request->get('key');

        if (!$key) {
            return response()->json(['error' => 'Key required'], 400);
        }

        $user = auth()->user();
        $settings = $user->settings;

        $value = $settings->getPreference($key);

        return response()->json([
            'success' => true,
            'key' => $key,
            'value' => $value,
        ]);
    }
}
