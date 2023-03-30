<?php

include __DIR__.'../../../header.php';
use Supabase\GoTrue\GoTrueClient;

$scheme = 'https';
$domain = 'supabase.co';
$path = '/auth/v1';

$client = new GoTrueClient($reference_id, $api_key, [
    'autoRefreshToken'   => false,
    'persistSession'     => true,
    'storageKey'         => $api_key,
], $domain, $scheme, $path);

$response = $client->admin->inviteUserByEmail('email@example.com');
print_r($response);
