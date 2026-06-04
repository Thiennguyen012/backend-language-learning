<?php

namespace App\Repositories\RefreshToken;

use App\Models\RefreshToken\RefreshToken;
use App\Repositories\Base\BaseRepository;
use Carbon\Carbon;

class RefreshTokenRepository extends BaseRepository implements RefreshTokenInterface
{
    public function model()
    {
        return RefreshToken::class;
    }

    public function findByToken(string $token)
    {
        return $this->model
            ->where('token', $token)
            ->where('is_revoked', false)
            ->first();
    }

    public function revokeUserTokens(int $userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('is_revoked', false)
            ->update(['is_revoked' => true]);
    }

    public function deleteExpiredTokens()
    {
        return $this->model
            ->where('expires_at', '<', Carbon::now())
            ->delete();
    }

    public function deleteUserExpiredTokens(int $userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('expires_at', '<', Carbon::now())
            ->delete();
    }
}
