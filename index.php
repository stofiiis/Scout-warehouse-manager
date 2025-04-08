<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'config/database.php';

// Get all warehouses
$stmt = $pdo->query("SELECT w.*, 
                     (SELECT COUNT(*) FROM items WHERE warehouse_id = w.id) as item_count 
                     FROM warehouses w ORDER BY w.created_at DESC");
$warehouses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Správa skautských skladů</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/header.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Sklady</h1>
                <a href="add_warehouse.php" class="btn btn-primary">Přidat sklad</a>
            </div>
            
            <?php if(empty($warehouses)): ?>
                <div class="empty-state">
                    <p>Zatím nejsou žádné sklady.</p>
                    <a href="add_warehouse.php" class="btn btn-primary">Přidat první sklad</a>
                </div>
            <?php else: ?>
                <div class="warehouse-grid">
                    <?php foreach($warehouses as $warehouse): ?>
                        <div class="warehouse-card">
                            <h2><?php echo htmlspecialchars($warehouse['name']); ?></h2>
                            <p class="location"><?php echo htmlspecialchars($warehouse['location']); ?></p>
                            <?php if(!empty($warehouse['description'])): ?>
                                <p class="description"><?php echo htmlspecialchars($warehouse['description']); ?></p>
                            <?php endif; ?>
                            <div class="warehouse-footer">
                                <span>Počet položek: <?php echo $warehouse['item_count']; ?></span>
                                <a href="warehouse.php?id=<?php echo $warehouse['id']; ?>" class="btn btn-secondary">Zobrazit</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>