<?php

namespace App\Services\User;

use App\Repositories\RefreshToken\RefreshTokenInterface;
use App\Repositories\User\UserInterface;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserAuthService
{
    protected $userRepository;
    protected $refreshTokenRepository;
    protected $userAvatarService;

    const ACCESS_TOKEN_EXPIRATION = 15; // minutes
    const REFRESH_TOKEN_EXPIRATION = 7; // days

    public function __construct(
        UserInterface $userRepository,
        RefreshTokenInterface $refreshTokenRepository,
        UserAvatarService $userAvatarService
    ) {
        $this->userRepository = $userRepository;
        $this->refreshTokenRepository = $refreshTokenRepository;
        $this->userAvatarService = $userAvatarService;
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

        $this->refreshTokenRepository->deleteUserExpiredTokens($user->id);

        $accessToken = $user->createToken(
            'access_token',
            ['*'],
            now()->addMinutes(self::ACCESS_TOKEN_EXPIRATION)
        )->plainTextToken;

        $refreshToken = $this->createRefreshToken($user, $deviceInfo);

        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken->token,
            'token_type' => 'Bearer',
            'access_token_expires_in' => self::ACCESS_TOKEN_EXPIRATION * 60,
            'refresh_token_expires_in' => self::REFRESH_TOKEN_EXPIRATION * 24 * 60 * 60,
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

        $refreshToken->last_used_at = now();
        $refreshToken->save();

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
        $user->currentAccessToken()?->delete();

        if ($refreshTokenString) {
            $hashedToken = hash('sha256', $refreshTokenString);
            $this->refreshTokenRepository->revokeTokenForUser($user->id, $hashedToken);
        }

        return true;
    }

    public function logoutFromDevice($user, string $refreshTokenString)
    {
        $hashedToken = hash('sha256', $refreshTokenString);
        $this->refreshTokenRepository->revokeTokenForUser($user->id, $hashedToken);

        return true;
    }

    public function logoutFromAllDevices($user)
    {
        $user->tokens()->delete();
        $this->refreshTokenRepository->revokeUserTokens($user->id);

        return true;
    }

    public function update($user, $data)
    {
        $avatar = $data['avatar'] ?? null;
        unset($data['avatar'], $data['password_confirmation']);

        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($avatar instanceof UploadedFile) {
            $data['avatar'] = $this->userAvatarService->store($user, $avatar);
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

        $refreshToken->token = $token;
        return $refreshToken;
    }
}
