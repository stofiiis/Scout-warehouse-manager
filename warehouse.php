<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'config/database.php';

// Check if warehouse ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$warehouse_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] ? true : false;

// Get warehouse details
$stmt = $pdo->prepare("SELECT * FROM warehouses WHERE id = ?");
$stmt->execute([$warehouse_id]);
$warehouse = $stmt->fetch();

// If warehouse doesn't exist, redirect
if(!$warehouse) {
    header("Location: index.php");
    exit;
}

// Get items in the warehouse
$stmt = $pdo->prepare("
    SELECT i.*, u.name as borrower_name, u.id as borrower_id
    FROM items i
    LEFT JOIN users u ON i.borrowed_by = u.id
    WHERE i.warehouse_id = ?
    ORDER BY i.name
");
$stmt->execute([$warehouse_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($warehouse['name']); ?> - Správa skautských skladů</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/header.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1><?php echo htmlspecialchars($warehouse['name']); ?></h1>
                <a href="index.php" class="btn btn-secondary">← Zpět na seznam skladů</a>
            </div>
            
            <p><?php echo htmlspecialchars($warehouse['description']); ?></p>
            
            <div class="action-buttons">
                <a href="add_item.php?warehouse_id=<?php echo $warehouse_id; ?>" class="btn btn-primary">Přidat položku</a>
            </div>
            
            <?php if(count($items) > 0): ?>
                <div class="items-table-container">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Název</th>
                                <th>Popis</th>
                                <th>Množství</th>
                                <th>Stav</th>
                                <th>Vypůjčeno</th>
                                <th>Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>
                                        <?php if($item['is_borrowed']): ?>
                                            <span class="status-borrowed">Vypůjčeno</span>
                                        <?php else: ?>
                                            <span class="status-available">Dostupné</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($item['is_borrowed']): ?>
                                            <?php echo htmlspecialchars($item['borrower_name']); ?><br>
                                            <small><?php echo date('d.m.Y H:i', strtotime($item['borrowed_at'])); ?></small>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($item['is_borrowed']): ?>
                                            <?php if($item['borrower_id'] == $user_id || $is_admin): ?>
                                                <a href="return_item.php?id=<?php echo $item['id']; ?>" class="btn btn-small btn-success">Vrátit</a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="borrow_item.php?id=<?php echo $item['id']; ?>" class="btn btn-small btn-primary">Vypůjčit</a>
                                        <?php endif; ?>
                                        <a href="edit_item.php?id=<?php echo $item['id']; ?>" class="btn btn-small btn-secondary">Upravit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>V tomto skladu zatím nejsou žádné položky.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>