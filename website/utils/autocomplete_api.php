<?php
require_once __DIR__ . '/../utils/paths.php';
require_once UTILS_DIR . '/db.php';
require_once UTILS_DIR . '/auth.php';
require_login();

header('Content-Type: application/json');

try {
    $term = isset($_GET['term']) ? trim($_GET['term']) : '';
    $type = isset($_GET['type']) ? $_GET['type'] : 'all';
    $results = [];
    
    if ($type === 'all' || $type === 'events') {
        if (!empty($term)) {
            $stmt = $pdo->prepare("
                SELECT DISTINCT title as label, title as value, 'event' as type
                FROM Event
                WHERE title LIKE :term
                ORDER BY title
                LIMIT 50
            ");
            $stmt->execute(['term' => "%$term%"]);
        } else {
            $stmt = $pdo->query("
                SELECT DISTINCT title as label, title as value, 'event' as type
                FROM Event
                ORDER BY title
                LIMIT 100
            ");
        }
        $events = $stmt->fetchAll();
        $results = array_merge($results, $events);
    }
    
    if ($type === 'all' || $type === 'categories') {
        if (!empty($term)) {
            $stmt = $pdo->prepare("
                SELECT DISTINCT category as label, category as value, 'category' as type
                FROM Event
                WHERE category IS NOT NULL 
                AND category != ''
                AND category LIKE :term
                ORDER BY category
                LIMIT 20
            ");
            $stmt->execute(['term' => "%$term%"]);
        } else {
            $stmt = $pdo->query("
                SELECT DISTINCT category as label, category as value, 'category' as type
                FROM Event
                WHERE category IS NOT NULL AND category != ''
                ORDER BY category
                LIMIT 50
            ");
        }
        $categories = $stmt->fetchAll();
        $results = array_merge($results, $categories);
    }
    
    if ($type === 'all' || $type === 'venues') {
        if (!empty($term)) {
            $stmt = $pdo->prepare("
                SELECT DISTINCT name as label, name as value, 'venue' as type
                FROM Venue
                WHERE name LIKE :term
                ORDER BY name
                LIMIT 30
            ");
            $stmt->execute(['term' => "%$term%"]);
        } else {
            $stmt = $pdo->query("
                SELECT DISTINCT name as label, name as value, 'venue' as type
                FROM Venue
                ORDER BY name
                LIMIT 50
            ");
        }
        $venues = $stmt->fetchAll();
        $results = array_merge($results, $venues);
    }
    
    echo json_encode($results);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch autocomplete data']);
}
