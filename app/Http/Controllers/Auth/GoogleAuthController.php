<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Inertia\Inertia;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        $url = Socialite::driver('google')->redirect()->getTargetUrl();
        return Inertia::location($url);
    }

    public function callback()
    {
        // if nanti kena "Invalid state" ubah ke:
        // $googleUser = Socialite::driver('google')->stateless()->user();
        $googleUser = Socialite::driver('google')->user();

        // find user by google_id OR by email (to link accounts)
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        $isNewUser = false;

        if (! $user) {
            $user = User::create([
                'full_name' => $googleUser->getName() ?? 'User',
                'username' => $this->generateUniqueUsername($googleUser->getEmail()),
                'email' => $googleUser->getEmail(),
                'password' => str()->random(32),
                'google_id' => $googleUser->getId(),
                'profile_image' => $googleUser->getAvatar(),
            ]);

            $isNewUser = true;
        } else {
            $user->update([
                'google_id' => $user->google_id ?? $googleUser->getId(),
                'profile_image' => $user->profile_image ?? $googleUser->getAvatar(),
            ]);
        }

        Auth::login($user, true);

        ActivityLog::create([
            'user_id' => Auth::user()->user_id,
            'action' => 'user.login',
            'meta' => json_encode([
                'username' => $user->username,
            ]),
        ]);

        if ($isNewUser) return redirect('/onboarding'); 
        else return redirect()->intended('/');
    }

    private function generateUniqueUsername(string $email): string
    {
        $local = strtolower(explode('@', $email)[0]);

        // keep only a-z 0-9 _ .
        $base = preg_replace('/[^a-z0-9_.]/', '_', $local);

        // hilangkan multiple underscores
        $base = preg_replace('/_+/', '_', $base);

        // hapus _ atau . di akhir string
        $base = trim($base, '_.');

        if ($base === '') $base = 'user';

        // limit length 20 karakter
        $base = substr($base, 0, 20);

        $username = $base;
        $i = 1;

        while (User::where('username', $username)->exists()) {
            $suffix = (string) $i++;
            $username = substr($base, 0, 20 - strlen($suffix)) . $suffix;
        }

        return $username;
    }
}
