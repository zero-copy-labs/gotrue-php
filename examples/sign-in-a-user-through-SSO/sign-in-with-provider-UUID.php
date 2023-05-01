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

$response = $client->signInWithSSO(
	[
		'providerId'=> '21648a9d-8d5a-4555-a9d1-d6375dc14e92',
		'options'              => [
			'captchaToken' => $options['captchaToken'] ?? null,
			'redirectTo'=> $options['redirectTo'] ?? null,
		],
	]);
print_r($response);
