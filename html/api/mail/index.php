<?php
require '../../vendor/autoload.php';

use Application\Mail;
use Application\Page;

// Connect to production database
$dsn = "pgsql:host=" . getenv('DB_PROD_HOST') . ";dbname=" . getenv('DB_PROD_NAME');

try {
    $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$mail = new Mail($pdo);
$page = new Page();

// GET: list all
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $page->list($mail->getAllMail());
    exit;
}

// POST: create new mail
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (!isset($data['subject'], $data['body'])) {
        $page->badRequest();
        exit;
    }

    $id = $mail->createMail($data['subject'], $data['body']);
    http_response_code(201);
    echo json_encode(["id" => $id]);
    exit;
}

// All other methods
$page->badRequest();
