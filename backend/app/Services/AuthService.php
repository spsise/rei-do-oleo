<?php

namespace App\Services;

use App\Domain\User\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Authenticate user and generate token
     */
    public function login(array $credentials, bool $rememberMe = false): array
    {
        if (!Auth::attempt($credentials, $rememberMe)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials']
            ])->status(401);
        }

        /** @var User $user */
        $user = Auth::user();

        // Ensure roles are loaded
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }

        // Check if user is active
        if (!$user->active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Account is inactive. Please contact administrator.']
            ])->status(401);
        }

        // Revoke all existing tokens
        $user->tokens()->delete();
        $user->refresh();

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Logout user and revoke token
     */
    public function logout(User $user): void
    {
        // Revoke all tokens for the user
        $user->tokens()->delete();
        $user->refresh();
    }

    /**
     * Refresh user token
     */
    public function refreshToken(User $user): array
    {
        // Revoke all existing tokens
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect']
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        // Revoke all tokens
        $user->tokens()->delete();
    }

    /**
     * Send password reset link
     */
    public function sendPasswordResetLink(string $email): string
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)]
            ]);
        }

        return $status;
    }

    /**
     * Reset password using token
     */
    public function resetPassword(array $data): string
    {
        $status = Password::reset(
            $data,
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)]
            ]);
        }

        return $status;
    }

    /**
     * Verify user email
     */
    public function verifyEmail(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            return true;
        }

        return false;
    }

    /**
     * Send email verification
     */
    public function sendEmailVerification(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->sendEmailVerificationNotification();
    }
}
