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

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJson($client->getResponse()->getContent());

        return json_decode($client->getResponse()->getContent(), true);
    }

    public function testCreateUser(): void
    {
        $client = static::createClient();
        $this->jwtToken = self::userLogIn();

        $client->request('POST', '/api/users', [
            'headers' => ['Accept' => 'application/json'], 
            'auth_bearer' => $this->jwtToken,
            'json' => [
                'email' => 'newuser@example.com',
                'username' => 'newuser',
                'password' => 'password',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJson($client->getResponse()->getContent());

        // Verify the created user
        $createdUser = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('newuser@example.com', $createdUser['email']);
        $this->assertEquals('newuser', $createdUser['username']);
    }

    public function testCreateUserInvalidEmail(): void
    {
        $client = static::createClient();
        $this->jwtToken = self::userLogIn();

        $client->request('POST', '/api/users', [
            'headers' => ['Accept' => 'application/json'], 
            'auth_bearer' => $this->jwtToken,
            'json' => [
                'username' => 'newuser',
                'password' => 'newpassword',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($client->getResponse()->getContent());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    public function testCreateUserDuplicateEmail(): void
    {
        $client = static::createClient();
        $this->jwtToken= self::userLogIn();

        // Create a user first
        $this->createUser($client, $this->jwtToken, 'newuser@example.com', 'newuser');

        // Try creating another user with the same email
        $client->request('POST', '/api/users', [
            'headers' => ['Accept' => 'application/json'], 
            'auth_bearer' => $this->jwtToken,
            'json' => [
                'email' => 'newuser@example.com',
                'username' => 'anotheruser',
                'password' => 'newpassword',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        $this->assertJson($client->getResponse()->getContent());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('An user with that username or email already exists.', $data['error']);
    }

    public function testGetUsers(): void
    {
        // Test GET /api/users without auth
        $response = static::createClient()->request('GET', '/api/users', ['headers' => ['Accept' => 'application/json']]);
        $this->assertResponseStatusCodeSame(401);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains(['code' => 401, 'message' => 'JWT Token not found']);

        // Test GET /api/users with auth
        $this->jwtToken = self::userLogIn();

        $response = static::createClient()->request('GET', '/api/users', ['headers' => ['Accept' => 'application/json'], 'auth_bearer' => $this->jwtToken]);
        $this->assertResponseIsSuccessful();
        //$this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');

        // Save users for later use
        $this->users = $response->toArray();
    }
}
