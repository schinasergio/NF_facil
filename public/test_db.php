<?php
try {
    $pdo = new PDO('mysql:host=db;dbname=nfe_dev', 'nfe', 'nfe');
    $stmt = $pdo->query("SELECT count(*) FROM users");
    echo "Users count: " . $stmt->fetchColumn() . "<br>";
    echo "Database Connected Successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
phpinfo();
