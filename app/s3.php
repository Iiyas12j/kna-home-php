<?php

function s3_configured(): bool
{
    return defined('S3_BUCKET') && S3_BUCKET !== ''
        && defined('S3_KEY') && S3_KEY !== ''
        && defined('S3_SECRET') && S3_SECRET !== '';
}

function s3_request_target(string $key): array
{
    $key = ltrim($key, '/');
    $endpoint = defined('S3_ENDPOINT') ? rtrim((string) S3_ENDPOINT, '/') : '';
    $pathStyle = defined('S3_PATH_STYLE') && S3_PATH_STYLE;

    if ($endpoint !== '') {
        $parts = parse_url($endpoint);
        $scheme = $parts['scheme'] ?? 'https';
        $baseHost = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $basePath = trim((string) ($parts['path'] ?? ''), '/');
        $host = ($pathStyle ? $baseHost : S3_BUCKET . '.' . $baseHost) . $port;
        $pathParts = array_filter([$basePath, $pathStyle ? S3_BUCKET : '', $key], static fn($part) => $part !== '');
        $canonicalUri = '/' . implode('/', array_map('rawurlencode', explode('/', implode('/', $pathParts))));
        return [$scheme . '://' . $host . $canonicalUri, $host, $canonicalUri];
    }

    $host = S3_BUCKET . '.s3.' . S3_REGION . '.amazonaws.com';
    $canonicalUri = '/' . implode('/', array_map('rawurlencode', explode('/', $key)));
    return ['https://' . $host . $canonicalUri, $host, $canonicalUri];
}

function s3_signing_key(string $dateStamp): string
{
    return hash_hmac('sha256', 'aws4_request',
        hash_hmac('sha256', 's3',
            hash_hmac('sha256', S3_REGION,
                hash_hmac('sha256', $dateStamp, 'AWS4' . S3_SECRET, true),
            true),
        true),
    true);
}

function s3_request(string $method, string $key, string $body = '', string $mime = 'application/octet-stream'): void
{
    [$url, $host, $canonicalUri] = s3_request_target($key);
    $payloadHash = hash('sha256', $body);
    $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $amzDate = $now->format('Ymd\THis\Z');
    $dateStamp = $now->format('Ymd');
    $canonicalHeaders = "content-type:$mime\nhost:$host\nx-amz-content-sha256:$payloadHash\nx-amz-date:$amzDate\n";
    $signedHeaders = 'content-type;host;x-amz-content-sha256;x-amz-date';
    $canonicalRequest = implode("\n", [
        $method,
        $canonicalUri,
        '',
        $canonicalHeaders,
        $signedHeaders,
        $payloadHash,
    ]);
    $credentialScope = "$dateStamp/" . S3_REGION . '/s3/aws4_request';
    $stringToSign = implode("\n", [
        'AWS4-HMAC-SHA256',
        $amzDate,
        $credentialScope,
        hash('sha256', $canonicalRequest),
    ]);
    $signature = hash_hmac('sha256', $stringToSign, s3_signing_key($dateStamp));
    $authorization = 'AWS4-HMAC-SHA256 Credential=' . S3_KEY . "/$credentialScope, "
        . "SignedHeaders=$signedHeaders, Signature=$signature";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $method === 'PUT' ? $body : null,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => [
            "Authorization: $authorization",
            "Content-Type: $mime",
            "x-amz-content-sha256: $payloadHash",
            "x-amz-date: $amzDate",
            'Content-Length: ' . strlen($body),
        ],
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $curlError !== '') {
        throw new RuntimeException("S3 $method failed: $curlError");
    }
    if ($status < 200 || $status >= 300) {
        throw new RuntimeException("S3 $method failed (HTTP $status)");
    }
}

function s3_upload(string $tmpFile, string $s3Key, ?string $mime = null): string
{
    $body = file_get_contents($tmpFile);
    if ($body === false) {
        throw new RuntimeException('ไม่สามารถอ่านไฟล์สำหรับอัปโหลดได้');
    }
    $mime = $mime ?: ((new finfo(FILEINFO_MIME_TYPE))->file($tmpFile) ?: 'application/octet-stream');
    s3_request('PUT', $s3Key, $body, $mime);

    if (defined('S3_URL') && S3_URL !== '') {
        return rtrim(S3_URL, '/') . '/' . ltrim($s3Key, '/');
    }
    [$url] = s3_request_target($s3Key);
    return $url;
}

function s3_delete(string $s3Key): void
{
    s3_request('DELETE', $s3Key, '', 'application/octet-stream');
}
