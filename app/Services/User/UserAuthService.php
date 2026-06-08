<?php

namespace App\Services\User;

use App\Repositories\User\UserInterface;
use App\Repositories\RefreshToken\RefreshTokenInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class UserAuthService
{
    protected $userRepository;
    protected $refreshTokenRepository;

    // Token expiration times
    const ACCESS_TOKEN_EXPIRATION = 15; // minutes
    const REFRESH_TOKEN_EXPIRATION = 7; // days

    public function __construct(
        UserInterface $userRepository,
        RefreshTokenInterface $refreshTokenRepository
    ) {
        $this->userRepository = $userRepository;
        $this->refreshTokenRepository = $refreshTokenRepository;
    }

    public function login(array $credentials, array $deviceInfo = [])
    {
        $email = $credentials['email'];
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        // Clean up expired tokens for this user
        $this->refreshTokenRepository->deleteUserExpiredTokens($user->id);

        // Create access token with expiration
        $accessToken = $user->createToken(
            'access_token',
            ['*'],
            now()->addMinutes(self::ACCESS_TOKEN_EXPIRATION)
        )->plainTextToken;

        // Create refresh token
        $refreshToken = $this->createRefreshToken($user, $deviceInfo);

        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken->token,
            'token_type' => 'Bearer',
            'access_token_expires_in' => self::ACCESS_TOKEN_EXPIRATION * 60, // seconds
            'refresh_token_expires_in' => self::REFRESH_TOKEN_EXPIRATION * 24 * 60 * 60, // seconds
        ];
    }

    public function refresh(string $refreshTokenString)
    {
        $hashedToken = hash('sha256', $refreshTokenString);
        $refreshToken = $this->refreshTokenRepository->findByToken($hashedToken);

        if (!$refreshToken || !$refreshToken->isValid()) {
            throw ValidationException::withMessages([
                'refresh_token' => ['Invalid or expired refresh token'],
            ]);
        }

        $user = $refreshToken->user;

        // Update last used time
        $refreshToken->last_used_at = now();
        $refreshToken->save();

        // Create new access token
        $accessToken = $user->createToken(
            'access_token',
            ['*'],
            now()->addMinutes(self::ACCESS_TOKEN_EXPIRATION)
        )->plainTextToken;

        return [
            'user' => $user,
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'access_token_expires_in' => self::ACCESS_TOKEN_EXPIRATION * 60,
        ];
    }

    public function logout($user, $refreshTokenString = null)
    {
        // Revoke current access token
        $user->currentAccessToken()->delete();
        
        // Revoke all refresh tokens của user
        $this->refreshTokenRepository->revokeUserTokens($user->id);
        
        return true;
    }

    public function logoutFromDevice(string $refreshTokenString)
    {
        $hashedToken = hash('sha256', $refreshTokenString);
        $refreshToken = $this->refreshTokenRepository->findByToken($hashedToken);

        if ($refreshToken) {
            $refreshToken->revoke();
        }

        return true;
    }

    public function logoutFromAllDevices($user)
    {
        // Revoke all access tokens
        $user->tokens()->delete();
        
        // Revoke all refresh tokens
        $this->refreshTokenRepository->revokeUserTokens($user->id);
        
        return true;
    }

    public function update($user, $data)
    {
        unset($data['password_confirmation']);

        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $this->userRepository->edit($user, $data);
    }

    protected function createRefreshToken($user, array $deviceInfo = [])
    {
        $token = Str::random(128);
        $hashedToken = hash('sha256', $token);

        $refreshToken = $this->refreshTokenRepository->create([
            'user_id' => $user->id,
            'token' => $hashedToken,
            'expires_at' => Carbon::now()->addDays(self::REFRESH_TOKEN_EXPIRATION),
            'device_name' => $deviceInfo['device_name'] ?? null,
            'ip_address' => $deviceInfo['ip_address'] ?? null,
            'user_agent' => $deviceInfo['user_agent'] ?? null,
        ]);

        // Return plain token to client, but store hashed version
        $refreshToken->token = $token;
        return $refreshToken;
    }
}
