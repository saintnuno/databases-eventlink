<?php
header('Content-Type: application/json');

require_once __DIR__ . '/paths.php';
require_once UTILS_DIR . '/auth.php';

if (!is_user_logged_in()) {
    echo json_encode([
        'success' => false,
        'error' => 'Authentication required',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
    ]);
    exit;
}

function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

try {
    $clientIP = getClientIP();
    
    $isPrivateIP = false;
    if ($clientIP === '127.0.0.1' || $clientIP === '::1' || 
        preg_match('/^10\./', $clientIP) || 
        preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $clientIP) || 
        preg_match('/^192\.168\./', $clientIP)) {
        $isPrivateIP = true;
    }
    
    if ($isPrivateIP) {
        $url = "https://ipinfo.io/json";
    } else {
        $url = "https://ipinfo.io/" . $clientIP . "/json";
    }
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        throw new Exception('Failed to connect to ipinfo.io service');
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response from ipinfo.io');
    }
    
    if (isset($data['loc'])) {
        list($lat, $lon) = explode(',', $data['loc']);
        
        echo json_encode([
            'success' => true,
            'ip' => $data['ip'] ?? $clientIP,
            'city' => $data['city'] ?? 'Unknown',
            'region' => $data['region'] ?? 'Unknown',
            'country' => $data['country'] ?? 'Unknown',
            'lat' => (float)$lat,
            'lon' => (float)$lon,
            'org' => $data['org'] ?? 'Unknown'
        ]);
    } else {
        throw new Exception('Location data not available in response');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'ip' => getClientIP()
    ]);
}
