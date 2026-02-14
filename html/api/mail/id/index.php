<?php
require '../../../vendor/autoload.php';

use Application\Mail;
use Application\Page;

// Connect to production database
$dsn = "pgsql:host=" . getenv('DB_PROD_HOST') . ";dbname=" . getenv('DB_PROD_NAME');
$pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$mail = new Mail($pdo);
$page = new Page();

// Extract ID from URL
$uri = $_SERVER['REQUEST_URI'];
$parts = explode('/', trim($uri, '/'));
$id = (int)end($parts);

if (!$id) {
    $page->badRequest();
    exit;
}

// GET: retrieve
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $item = $mail->getMail($id);
    if (!$item) $page->notFound();
    else $page->item($item);
    exit;
}

// PUT: update
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (!isset($data['subject'], $data['body'])) {
        $page->badRequest();
        exit;
    }

    if (!$mail->updateMail($id, $data['subject'], $data['body'])) {
        $page->notFound();
        exit;
    }

    $page->item(["updated" => true]);
    exit;
}

// DELETE: remove
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!$mail->deleteMail($id)) {
        $page->notFound();
        exit;
    }

    $page->item(["deleted" => true]);
    exit;
}

// All other methods
$page->badRequest();
