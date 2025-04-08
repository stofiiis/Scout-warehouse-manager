<?php
// Database connection parameters
$host = 'localhost'; // Zde zadejte adresu serveru databáze (např. localhost)
$dbname = 'nazev_databaze'; // Zde zadejte název vaší databáze
$username = 'uzivatel'; // Zde zadejte uživatelské jméno pro připojení k databázi
$password = 'heslo_k_databazi';

try {
    // Create PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>