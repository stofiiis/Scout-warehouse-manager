<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'config/database.php';

// Check if item ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$item_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] ? true : false;

// Get item details
$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

// If item doesn't exist, redirect
if(!$item) {
    header("Location: index.php");
    exit;
}

// Check if user has permission to delete (admin or creator)
if(!$is_admin && (!isset($item['created_by']) || $item['created_by'] != $user_id)) {
    header("Location: warehouse.php?id=" . $item['warehouse_id'] . "&error=unauthorized");
    exit;
}

// Check if item is borrowed
if($item['is_borrowed']) {
    header("Location: warehouse.php?id=" . $item['warehouse_id'] . "&error=borrowed_item");
    exit;
}

// Delete item
$stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
try {
    $stmt->execute([$item_id]);
    header("Location: warehouse.php?id=" . $item['warehouse_id'] . "&success=deleted");
    exit;
} catch(PDOException $e) {
    header("Location: warehouse.php?id=" . $item['warehouse_id'] . "&error=delete_failed");
    exit;
}
?>