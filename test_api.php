<?php
// Test using Brevo V3 API
$ch = curl_init();
$data = [
    'sender' => ['name' => 'Ariq Ikbar Hakim', 'email' => 'ariqikbar730@gmail.com'],
    'to' => [
        ['email' => 'ariqikbar730@gmail.com', 'name' => 'Ariq Ikbar Hakim']
    ],
    'subject' => 'Test Email',
    'htmlContent' => '<html><body><h1>This is a test email</h1></body></html>'
];

curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.brevo.com/v3/smtp/email',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'accept: application/json',
        'api-key: ZV2hRn89GySJbPpL',
        'content-type: application/json'
    ]
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpcode\n";
echo "Response: $response\n";
