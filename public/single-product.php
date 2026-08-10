<?php
// Legacy entry point. Product detail pages now live at their own dedicated
// URLs (neofilera.php, hyabell.php, variofill.php, meteora.php). This file
// only exists so any old ?id= link still lands somewhere correct.
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/partials/product-theme-functions.php';

$id = (int) ($_GET['id'] ?? 0);
$target = '/product.php';

if ($pdo instanceof PDO && $id > 0) {
    try {
        $stmt = $pdo->prepare('SELECT name FROM products WHERE id = ? AND is_active = 1');
        $stmt->execute([$id]);
        $name = (string) ($stmt->fetchColumn() ?: '');
        if ($name !== '') {
            $target = product_detail_url($name);
        }
    } catch (Exception $e) {}
}

header('Location: ' . $target, true, 301);
exit;
