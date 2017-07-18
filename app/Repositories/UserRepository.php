<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends Repository
{
    public static function create(array $data)
    {
        return User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
            'photo'    => $data['photo'] ?? null,
            'role_id'  => $data['role_id'] ?? 4,
            'active'   => $data['active'] ?? 0,
        ]);
    }

    public static function updateApiToken($userId)
    {
        return User::find($userId)
            ->update(['api_token' => bcrypt(time().str_random(60))]);
    }
}