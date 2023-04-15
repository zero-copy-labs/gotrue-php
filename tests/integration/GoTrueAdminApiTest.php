<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class GoTrueAdminApiTest extends TestCase
{
    private $client;

    public function setup(): void
    {
        parent::setUp();
        $dotenv = \Dotenv\Dotenv::createUnsafeImmutable(__DIR__, '/../../.env.test');
        $dotenv->load();
        $scheme = 'https';
        $domain = 'supabase.co';
        $path = '/auth/v1';
        $api_key = getenv('API_KEY');
        $reference_id = getenv('REFERENCE_ID');
        $this->client = new  \Supabase\GoTrue\GoTrueClient($reference_id, $api_key, [
            'autoRefreshToken'   => false,
            'persistSession'     => true,
            'storageKey'         => $api_key,
        ], $domain, $scheme, $path);
    }

    public function testGetUserById(): void
    {
        $email = $this->createRandomEmail();
        $result = $this->client->admin->createUser([
            'email'                => $email,
            'password'             => 'example-password',
            'email_confirm'        => true,
        ]);

        $result = $this->client->signInWithPassword([
            'email'                => $email,
            'password'             => 'example-password',
        ]);
        $uid = $result['data']['user']['id'];
        $result = $this->client->admin->getUserById($uid);
        $this->assertEquals($uid, $result['id']);
        $this->assertIsArray($result);

        $result = $this->client->admin->deleteUser($uid);
    }

    public function testListUsers(): void
    {
        $result = $this->client->admin->listUsers([
            'page'   => 1,
            'perPage'=> 2,
        ]);
        fwrite(STDERR, print_r($result, true));
        $this->assertNull($result['error']);
        $this->assertIsArray($result['data']);
    }

    public function testCreateUser(): void
    {
        $email = $this->createRandomEmail();
        $result = $this->client->admin->createUser([
            'email'                => $email,
            'password'             => 'example-password',
            'email_confirm'        => true,
        ]);
        fwrite(STDERR, print_r($result, true));
        $uid = $result['data']['id'];
        $this->assertNull($result['error']);
        $this->assertIsArray($result['data']);
        $result = $this->client->admin->deleteUser($uid);
    }

    public function testDeleteUser(): void
    {
        $email = $this->createRandomEmail();
        $result = $this->client->admin->createUser([
            'email'                => $email,
            'password'             => 'example-password',
            'email_confirm'        => true,
        ]);
        $uid = $result['data']['id'];
        $result = $this->client->admin->deleteUser($uid);
        fwrite(STDERR, print_r($result, true));
        $this->assertNull($result['error']);
        $this->assertIsArray($result['data']);
    }

    public function testInviteUserByEmail(): void
    {
        $email = $this->createRandomEmail();
        $result = $this->client->admin->inviteUserByEmail($email);
        fwrite(STDERR, print_r($result, true));
        $this->assertNull($result['error']);
        $this->assertIsArray($result['data']);
    }

    public function testResetPasswordForEmail(): void
    {
        $email = $this->createRandomEmail();
        $result = $this->client->admin->createUser([
            'email'                => $email,
            'password'             => 'example-password',
            'email_confirm'        => true,
        ]);
        $uid = $result['data']['user']['id'];
        $result = $this->client->admin->resetPasswordForEmail(
            $email,
            ['redirectTo' => 'https://example.com/update-password']
        );
        fwrite(STDERR, print_r($result, true));
        $this->assertIsArray($result['data']);
        $this->assertNull($result['error']);
        $result = $this->client->admin->deleteUser($uid);
    }

    public function testGenerateLink(): void
    {
        $email = $this->createRandomEmail();
        $result = $this->client->admin->createUser([
            'email'                => $email,
            'password'             => 'example-password',
            'email_confirm'        => true,
        ]);
        $uid = $result['data']['user']['id'];
        $result = $this->client->admin->resetPasswordForEmail(
            $email,
            ['redirectTo' => 'https://example.com/update-password']
        );
        fwrite(STDERR, print_r($result, true));
        $this->assertIsArray($result['data']);
        $this->assertNull($result['error']);
        $result = $this->client->admin->deleteUser($uid);
    }

    private function createRandomEmail(): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $random_string = '';
        $domain = 'example.com';
        $length = 10;
        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $random_string.'@'.$domain;
    }
}