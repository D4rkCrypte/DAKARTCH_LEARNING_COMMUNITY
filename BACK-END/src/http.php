<?php

declare(strict_types=1);

function json_response(int $status, array $payload): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    // Allowed origins: any localhost / 127.0.0.1 variant (dev) + production domain if set
    $allowedPatterns = [
        '/^https?:\/\/localhost(:\d+)?$/',
        '/^https?:\/\/127\.0\.0\.1(:\d+)?$/',
        '/^https?:\/\/\[::1\](:\d+)?$/',           // IPv6 loopback
        '/^https?:\/\/.*\.local(:\d+)?$/',          // .local domains
    ];

    $isAllowed = false;
    if ($origin !== '') {
        foreach ($allowedPatterns as $pattern) {
            if (preg_match($pattern, $origin)) {
                $isAllowed = true;
                break;
            }
        }
    }

    if ($isAllowed) {
        header('Access-Control-Allow-Origin: ' . $origin);
    } elseif ($origin === '') {
        // Same-origin request (no Origin header) — always allowed
    }
    // else: cross-origin from unknown host — no CORS header sent (browser will block)

    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Vary: Origin');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * Escapes HTML to prevent XSS. 
 * Use this before outputting user-provided content in HTML.
 */
function escape_html(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function bearer_token(): ?string
{
    $headers = '';
    
    // 1. Standard PHP Method
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
    } 
    // 2. Mod-Rewrite Fallback
    elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    } 
    // 3. Apache/FastCGI Fallback
    elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_change_key_case($requestHeaders, CASE_LOWER);
        if (isset($requestHeaders['authorization'])) {
            $headers = trim($requestHeaders['authorization']);
        }
    }

    // Extraction du jeton
    if ($headers !== '' && preg_match('/^Bearer\s+(.*)$/i', $headers, $matches)) {
        $token = trim((string)$matches[1]);
        return $token !== '' ? $token : null;
    }

    return null;
}
