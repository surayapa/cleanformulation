<?php
header('Content-Type: application/json');

// STEP 2.1 — Load JSON safely
$jsonFile = $_SERVER['DOCUMENT_ROOT'] . '/data/ingredients.json';

if (!file_exists($jsonFile)) {
    echo json_encode(["error" => "ingredients.json not found"]);
    exit;
}

$data = json_decode(file_get_contents($jsonFile), true);

if (!is_array($data)) {
    echo json_encode(["error" => "Invalid ingredients.json"]);
    exit;
}

// STEP 2.2 — DEBUG: confirm GET is visible
if (!empty($_GET)) {
    // TEMP DEBUG — remove later
    // echo json_encode(["debug_get" => $_GET]);
    // exit;
}

// STEP 2.3 — QUERY search (THIS is your broken case)
if (isset($_GET['query']) && $_GET['query'] !== '') {
    $q = strtolower(trim($_GET['query']));
    $results = [];

    foreach ($data as $item) {
        if (
            isset($item['name']) &&
            strpos(strtolower($item['name']), $q) !== false
        ) {
            $results[] = $item;
        }
    }

    if (!empty($results)) {
        echo json_encode($results);
    } else {
        echo json_encode(["error" => "Ingredient not found"]);
    }
    exit;
}

// STEP 2.4 — CAS lookup
if (isset($_GET['cas']) && $_GET['cas'] !== '') {
    $cas = trim($_GET['cas']);

    foreach ($data as $item) {
        if (isset($item['cas']) && $item['cas'] === $cas) {
            echo json_encode($item);
            exit;
        }
    }

    echo json_encode(["error" => "Ingredient not found"]);
    exit;
}

// STEP 2.5 — Slug lookup (path based)
$slug = $_GET['slug'] ?? '';

if ($slug !== '') {
    foreach ($data as $item) {
        if (isset($item['slug']) && $item['slug'] === $slug) {
            echo json_encode($item);
            exit;
        }
    }
}

// STEP 2.6 — Final fallback
echo json_encode(["error" => "Ingredient not found"]);
exit;
