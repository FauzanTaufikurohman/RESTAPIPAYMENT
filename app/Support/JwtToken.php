<?php

namespace App\Support;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

class JwtToken
{
    private static function secret(): string
    {
        return env(
            'JWT_SECRET',
            'restapi-jwt-secret-key-2026-very-long'
        );
    }

    /**
     * Generate access token.
     */
    public static function encode(User $user): string
    {
        $now = time();

        $payload = [
            'sub' => $user->user_id,
            'email' => $user->email,
            'type' => 'access',
            'iat' => $now,
            'exp' => $now + (60 * 15), // 15 menit
        ];

        return JWT::encode(
            $payload,
            self::secret(),
            'HS256'
        );
    }

    /**
     * Generate refresh token.
     */
    public static function refresh(User $user): string
    {
        $now = time();

        $payload = [
            'sub' => $user->user_id,
            'type' => 'refresh',
            'iat' => $now,
            'exp' => $now + (60 * 60 * 24 * 7), // 7 hari
        ];

        return JWT::encode(
            $payload,
            self::secret(),
            'HS256'
        );
    }

    /**
     * Decode JWT.
     */
    public static function decode(string $token): object
    {
        return JWT::decode(
            $token,
            new Key(self::secret(), 'HS256')
        );
    }

    /**
     * Get user from access token.
     */
    public static function userFromToken(?string $token): ?User
    {
        if (!$token) {
            return null;
        }

        try {
            $payload = self::decode($token);

            if (($payload->type ?? null) !== 'access') {
                return null;
            }

            return User::query()
                ->where(
                    'user_id',
                    $payload->sub ?? null
                )
                ->first();

        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Get user from refresh token.
     */
    public static function userFromRefreshToken(?string $token): ?User
    {
        if (!$token) {
            return null;
        }

        try {
            $payload = self::decode($token);

            if (($payload->type ?? null) !== 'refresh') {
                return null;
            }

            return User::query()
                ->where(
                    'user_id',
                    $payload->sub ?? null
                )
                ->first();

        } catch (Throwable) {
            return null;
        }
    }
}