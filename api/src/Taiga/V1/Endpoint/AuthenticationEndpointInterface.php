<?php

declare(strict_types=1);

namespace App\Taiga\V1\Endpoint;

use App\Taiga\V1\Model\UserModel;

interface AuthenticationEndpointInterface
{
    public function authenticate(string $username, string $password): UserModel;
}
