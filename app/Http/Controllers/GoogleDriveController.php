<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;

class GoogleDriveController extends Controller
{
    public function redirectToGoogleDrive(): RedirectResponse
    {
        /** @var \Laravel\Socialite\Two\AbstractProvider $provider */
        $provider = Socialite::driver('google');

        return $provider
            ->redirectUrl(route('admin.google.drive.callback'))
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->scopes([
                'openid',
                'profile',
                'email',
                'https://www.googleapis.com/auth/drive',
            ])
            ->redirect();
    }

    public function handleGoogleDriveCallback(Request $request): RedirectResponse
    {
        try {
            /** @var \Laravel\Socialite\Two\AbstractProvider $provider */
            $provider = Socialite::driver('google');

            $googleUser = $provider
                ->redirectUrl(route('admin.google.drive.callback'))
                ->stateless()
                ->user();

            $token = $googleUser->token;
            $refreshToken = $googleUser->refreshToken;
            $expiresIn = $googleUser->expiresIn;
            $email = $googleUser->getEmail();

            if (! $token || ! $refreshToken) {
                return Redirect::route('admin.settings')->with('error', 'Google Drive authorization failed. Please connect again and grant offline access.');
            }

            $tokenData = [
                'access_token' => $token,
                'refresh_token' => $refreshToken,
                'expires_in' => $expiresIn,
                'created' => time(),
            ];

            Setting::setValue('google_drive_oauth_account', $email, 'google', 'Google Drive connected account email');
            Setting::setValue('google_drive_oauth_tokens', encrypt(json_encode($tokenData)), 'google', 'Google Drive OAuth token data');

            return Redirect::route('admin.settings')->with('success', 'Google Drive connected successfully.');
        } catch (\Throwable $exception) {
            Log::error('Google Drive OAuth callback failed', ['error' => $exception->getMessage()]);

            return Redirect::route('admin.settings')->with('error', 'Google Drive authorization failed. ' . $exception->getMessage());
        }
    }
}
