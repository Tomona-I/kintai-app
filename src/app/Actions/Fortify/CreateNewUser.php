<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        // RegisterRequestのバリデーションルールを使用
        $validator = validator($input, (new RegisterRequest())->rules(), (new RegisterRequest())->message());
        $validator->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'role' => 0, // 一般ユーザーとして登録
        ]);
    }
}
