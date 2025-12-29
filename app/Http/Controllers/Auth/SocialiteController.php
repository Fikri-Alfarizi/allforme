<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialiteController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            return $this->loginOrCreateUser($googleUser->id, $googleUser->email, $googleUser->name, $googleUser->avatar);
        } catch (Exception $e) {
            return redirect('login')->with('error', 'Login dengan Google gagal, silakan coba lagi. ' . $e->getMessage());
        }
    }

    public function handleOneTapCallback(Request $request)
    {
        try {
            $credential = $request->input('credential');
            if (!$credential) {
                return redirect('login')->with('error', 'Token Google tidak ditemukan.');
            }

            // Decode JWT standard (Header.Payload.Signature)
            $parts = explode('.', $credential);
            if (count($parts) != 3) {
                return redirect('login')->with('error', 'Token Google tidak valid.');
            }

            $payload = $parts[1]; 
            // Base64URL decode
            $decoded = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);

            if (!$decoded) {
                return redirect('login')->with('error', 'Gagal membaca data Google.');
            }

            // Optional: Verify Audience (Client ID)
            // if ($decoded['aud'] !== config('services.google.client_id')) { ... }

            $googleId = $decoded['sub'];
            $email = $decoded['email'];
            $name = $decoded['name'];
            $avatar = $decoded['picture'] ?? null;

            return $this->loginOrCreateUser($googleId, $email, $name, $avatar);

        } catch (Exception $e) {
            return redirect('login')->with('error', 'Login dengan Google One Tap gagal. ' . $e->getMessage());
        }
    }

    private function loginOrCreateUser($googleId, $email, $name, $avatar)
    {
        $findUser = User::where('google_id', $googleId)->first();

        if ($findUser) {
            Auth::login($findUser);
            return redirect()->intended('dashboard');
        } else {
            $checkEmail = User::where('email', $email)->first();

            if ($checkEmail) {
                $checkEmail->update([
                    'google_id' => $googleId,
                    'avatar' => $avatar
                ]);
                Auth::login($checkEmail);
                return redirect()->intended('dashboard');
            }

            $newUser = User::create([
                'name' => $name,
                'email' => $email,
                'google_id' => $googleId,
                'avatar' => $avatar,
                'password' => bcrypt('password'), // Dummy password
            ]);

            Auth::login($newUser);
            return redirect()->intended('dashboard');
        }
    }
}
