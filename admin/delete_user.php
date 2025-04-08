<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../index.php");
    exit;
}

// Include database connection
require_once '../config/database.php';

// Check if user ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_GET['id'];

// Prevent deleting yourself
if($user_id == $_SESSION['user_id']) {
    header("Location: index.php?error=self_delete");
    exit;
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// If user doesn't exist, redirect
if(!$user) {
    header("Location: index.php");
    exit;
}

// Check if user has borrowed items
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE borrowed_by = ? AND is_borrowed = 1");
$stmt->execute([$user_id]);
$borrowed_count = $stmt->fetch()['count'];

if($borrowed_count > 0) {
    header("Location: index.php?error=has_borrowed_items");
    exit;
}

// Delete user
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
try {
    $stmt->execute([$user_id]);
    header("Location: index.php?success=deleted");
    exit;
} catch(PDOException $e) {
    header("Location: index.php?error=delete_failed");
    exit;
}
?>