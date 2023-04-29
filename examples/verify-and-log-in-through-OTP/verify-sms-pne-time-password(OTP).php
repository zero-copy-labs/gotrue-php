<?php

include __DIR__.'../../header.php';
use Supabase\GoTrue\GoTrueClient;

$scheme = 'https';
$domain = 'supabase.co';
$path = '/auth/v1';

$client = new GoTrueClient($reference_id, $api_key, [
	'autoRefreshToken'   => false,
	'persistSession'     => true,
	'storageKey'         => $api_key,
], $domain, $scheme, $path);

$userData = [
	'email'         => $ramdom_email,
	'password'      => 'some-password',
	'email_confirm' => true,
	'phone'         => '1234567897',
	'phone_confirm' => true,
];

$response = $client->admin->createUser($userData);
$uid = $response['data']['id'];

$response = $client->signInWithPassword([
	'email'                => $ramdom_email,
	'password'             => 'some-password',
	'gotrue_meta_security' => ['captcha_token' => $options['captchaToken'] ?? null],
]);
$accesToken = $response['data']['access_token'];
$response = $client->verifyOtp('1234567897', $accesToken, 'sms', ['captcha_token' => $options['captchaToken'] ?? null]);
print_r($response);
$response = $client->admin->deleteUser($uid);
