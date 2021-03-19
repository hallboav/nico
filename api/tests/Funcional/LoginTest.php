<?php

declare(strict_types=1);

namespace App\Tests\Funcional;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class LoginTest extends ApiTestCase
{
    /**
     * @group login
     */
    public function testLogin(): string
    {
        $response = static::createClient()->request('POST', '/login', [
            'json' => [
                'username' => 'admin',
                'password' => 'password',
            ],
        ]);

        $this->assertResponseIsSuccessful();

        $responseData = $response->toArray();
        $this->assertArrayHasKey('token', $responseData);

        return $responseData['token'];
    }
}
