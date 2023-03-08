<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Validation\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Validation\Rules\Password;

final readonly class UserRepository
{
    public function __construct(private User $authUser,
                                private Hasher $encoder,
                                private Factory $validation)
    {
    }

    public function register(array $data): void
    {
        $this->validation->make($data, $this->validationRules())->validated();

        $authUser = $this->authUser->newInstance();

        $authUser['id'] = $data['id'];
        $authUser['name'] = $data['name'];
        $authUser['email'] = $data['email'];
        $authUser['password'] = $this->encoder->make($data['password']);

        $authUser->saveOrFail();
    }

    public function delete(string $userId): void
    {
        $this->authUser->newQuery()->where('id', $userId)->delete();
    }

    public function findByEmail(string $email): null|Model
    {
        return $this->authUser->newQuery()->where('email', $email)->first();
    }

    /**
     * @return array{'id': string, 'name': string, 'email': string, 'password': array}
     */
    public function validationRules(): array
    {
        return[
            'id' => 'required|uuid',
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => ['required', Password::min(8)],
        ];
    }
}
