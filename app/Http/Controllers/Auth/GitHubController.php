<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Laravel\Socialite\Facades\Socialite;

class GitHubController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('github')
            ->scopes(['repo', 'read:user', 'read:org'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $githubUser = Socialite::driver('github')->user();

            $user = User::where('email', $githubUser->email)->first();

            if ($user) {
                // Update existing user
                $user->update([
                    'github_id' => $githubUser->id,
                    'github_username' => $githubUser->nickname,
                    'github_token' => Crypt::encryptString($githubUser->token),
                    'avatar' => $githubUser->avatar,
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $githubUser->name ?? $githubUser->nickname,
                    'email' => $githubUser->email,
                    'github_id' => $githubUser->id,
                    'github_username' => $githubUser->nickname,
                    'github_token' => Crypt::encryptString($githubUser->token),
                    'avatar' => $githubUser->avatar,
                    'email_verified_at' => now(),
                    'password' => bcrypt(str()->random(32)),
                ]);
            }

            Auth::login($user);

            return redirect()->intended(route('projects.index', absolute: false));
        } catch (\Exception $e) {
            \Log::error('GitHub OAuth callback error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect('/')->with('error', 'GitHubログインに失敗しました: ' . $e->getMessage());
        }
    }
}
