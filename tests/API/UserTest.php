<?php

namespace App\Tests\API;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserTest extends ApiTestCase
{
    private string $jwtToken;

    public function userLogIn(): string
    {
        $response = static::createClient()->request('POST', '/api/login', [
            'json' => [
                'username' => 'admin',
                'password' => 'admin',
            ],
        ]);

        return $response->toArray()['token'];
    }

    protected function createUser($client, $token, $email, $username): array
    {
        $client->request('POST', '/api/users', [
            'headers' => ['Authorization' => "Bearer $token"],
            'json' => [
                'email' => $email,
                'username' => $username,
                'password' => 'password',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());

        return json_decode($client->getResponse()->getContent(), true);
    }

    public function testCreateUser(): void
    {
        $client = static::createClient();
        $this->jwtToken = self::userLogIn();

        $response = $client->request('POST', '/api/users', [
            'headers' => ['Accept' => 'application/json'], 
            'auth_bearer' => $this->jwtToken,
            'json' => [
                'email' => 'newuser@example.com',
                'username' => 'newuser',
                'password' => 'password',
            ],
        ]);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertJson($response->getContent());

        // Verify the created user
        $createdUser = json_decode($response->getContent(), true);
        $this->assertEquals('newuser@example.com', $createdUser['email']);
        $this->assertEquals('newuser', $createdUser['username']);
    }

    public function testCreateUserInvalidEmail(): void
    {
        $client = static::createClient();
        $this->jwtToken = self::userLogIn();

        $response = $client->request('POST', '/api/users', [
            'headers' => ['Accept' => 'application/json'], 
            'auth_bearer' => $this->jwtToken,
            'json' => [
                'username' => 'newuser',
                'password' => 'newpassword',
            ],
        ]);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testCreateUserDuplicatedEmail(): void
    {
        $client = static::createClient();
        $this->jwtToken= self::userLogIn();

        // Try creating another user with the same email
        $response = $client->request('POST', '/api/users', [
            'headers' => ['Accept' => 'application/json'], 
            'auth_bearer' => $this->jwtToken,
            'json' => [
                'email' => 'newuser@example.com',
                'username' => 'anotheruser',
                'password' => 'newpassword',
            ],
        ]);

        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());
    }

    public function testGetUsers(): void
    {
        // Test GET /api/users without auth
        $response = static::createClient()->request('GET', '/api/users', ['headers' => ['Accept' => 'application/json']]);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains(['code' => 401, 'message' => 'JWT Token not found']);

        // Test GET /api/users with auth
        $this->jwtToken = self::userLogIn();

        $response = static::createClient()->request('GET', '/api/users', ['headers' => ['Accept' => 'application/json'], 'auth_bearer' => $this->jwtToken]);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}
