<?php

declare(strict_types=1);

namespace App\Taiga\V1\Model;

class UserModel
{
    public function __construct(
        private string $authToken,
    ) {
    }

    /**
     * @param array{
     *     'auth_token': string,
     * } $array
     */
    public static function fromArray(array $array): self
    {
        return new self(
            $array['auth_token'],
        );
    }

    public function getAuthToken(): string
    {
        return $this->authToken;
    }
}
