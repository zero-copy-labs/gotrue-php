<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class GoTrueClientTest extends TestCase
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

    /**
     * Test Creates a new user.
     *
     * @return void
     */
    public function testCreateUser(): void
    {
        $email = $this->createRandomEmail();
        $result = $this->client->signUp([
            'email'                => $email,
            'password'             => 'example-password',
            'gotrue_meta_security' => ['captcha_token' => $options['captchaToken'] ?? null],
        ]);
        $this->assertNull($result['error']);
        $this->assertArrayHasKey('data', $result);
        $email = $result['data']['user']['email'];
        $uid = $result['data']['user']['id'];
        $this->assertEquals($email, $email);
        $result = $this->client->admin->deleteUser($uid);
    }

    public function testSignInUser(): void
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

        $this->assertNull($result['error']);
        $this->assertArrayHasKey('data', $result);
        $uid = $result['data']['user']['id'];
        $access_token = $result['data']['access_token'];
        $this->assertIsString($access_token);
        $result = $this->client->admin->deleteUser($uid);
    }

    public function testSignInWithOtp(): void
    {
        $email = $this->createRandomEmail();
        $user = $this->client->admin->createUser([
            'email'                => $email,
            'password'             => 'example-password',
            'email_confirm'        => true,
        ]);

        $result = $this->client->signInWithOtp([
            'email'                => $email,
            'gotrue_meta_security' => ['captcha_token' => $options['captchaToken'] ?? null],
            'options'              => [
                'emailRedirectTo'=> 'https://example.com/welcome',
            ],
        ]);

        $this->assertNull($result['error']);
        $this->assertArrayHasKey('data', $result);
        $uid = $user['data']['id'];
        $result = $this->client->admin->deleteUser($uid);
    }

    public function testSignOut(): void
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
        $access_token = $result['data']['access_token'];
        $result = $this->client->signOut($access_token);
        $this->assertNull($result['error']);
        $uid = $result['data']['user']['id'];
        $this->assertIsString($access_token);

        $result = $this->client->admin->deleteUser($uid);
    }

    public function testGetSession(): void
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
        $access_token = $result['data']['access_token'];
        $result = $this->client->getSession($access_token);
        $this->assertIsString($access_token);

        $result = $this->client->admin->deleteUser($uid);
    }

    public function testGetUser(): void
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
        $access_token = $result['data']['access_token'];
        $result = $this->client->getUser($access_token);
        $this->assertEquals($email, $result['email']);
        $result = $this->client->admin->deleteUser($uid);
    }

    public function testUpdateUser(): void
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
        $access_token = $result['data']['access_token'];
        $result = $this->client->updateUser(['email'=>"new-{$email}"], $access_token);
        fwrite(STDERR, print_r($result, true));
        $this->assertArrayHasKey('new_email', $result['data']);
        $this->assertEquals("new-{$email}", $result['data']['new_email']);
        $result = $this->client->admin->deleteUser($uid);
    }

    public function testSetSession(): void
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
        $access_token = $result['data']['access_token'];
        $refresh_token = $result['data']['refresh_token'];
        $result = $this->client->setSession(['access_token'=>$access_token, 'refresh_token' =>$refresh_token]);
        fwrite(STDERR, print_r($result, true));
        $this->assertArrayHasKey('session', $result['data']);
        $this->assertNull($result['error']);
        $result = $this->client->admin->deleteUser($uid);
    }

    public function testEnroll(): void
    {//Pending
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
        $access_token = $result['data']['access_token'];
        $result = $this->client->mfa->enroll(['factor_type'=> 'totp'], $access_token);
        $factor_id = $result['data']['id'];
        $data_challenge = $this->client->mfa->challenge($factor_id, $access_token);
        $challenge_id = $data_challenge['data']['id'];
        $data_verify = $this->client->mfa->verify(
            $factor_id,
            $access_token,
            ['challenge_id'=> $challenge_id, 'code'=> '849822']
        );
        unset($result['data']['totp']['qr_code']);
        fwrite(STDERR, print_r($result, true));
        $this->assertArrayHasKey('data', $result);
        $this->assertNull($result['error']);
        $result = $this->client->admin->deleteUser($uid);
    }

    public function testGetAuthenticatorAssuranceLevel(): void
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
        $access_token = $result['data']['access_token'];
        $result = $this->client->_getAuthenticatorAssuranceLevel($access_token);
        fwrite(STDERR, print_r($result, true));
        $this->assertArrayHasKey('data', $result);
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