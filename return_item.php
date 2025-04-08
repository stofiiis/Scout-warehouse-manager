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

// Get item details with proper SQL syntax
try {
    $stmt = $pdo->prepare("
        SELECT i.*, w.name AS warehouse_name, u.name AS borrower_name, u.id AS borrower_id
        FROM items i 
        JOIN warehouses w ON i.warehouse_id = w.id
        LEFT JOIN users u ON i.borrowed_by = u.id
        WHERE i.id = ?
    ");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();
} catch(PDOException $e) {
    die("Chyba při načítání položky: " . $e->getMessage());
}

// If item doesn't exist or is not borrowed, redirect
if(!$item || !$item['is_borrowed']) {
    header("Location: warehouse.php?id=" . $item['warehouse_id']);
    exit;
}

// Check if the current user is the one who borrowed the item or is an admin
if($item['borrower_id'] != $user_id && !$is_admin) {
    // Redirect with error message
    header("Location: warehouse.php?id=" . $item['warehouse_id'] . "&error=unauthorized_return");
    exit;
}

// Process return action
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update item to available status
    $stmt = $pdo->prepare("UPDATE items SET is_borrowed = 0, borrowed_by = NULL, borrowed_at = NULL WHERE id = ?");
    
    try {
        $stmt->execute([$item_id]);
        
        // Redirect back to warehouse
        header("Location: warehouse.php?id=" . $item['warehouse_id'] . "&success=returned");
        exit;
    } catch(PDOException $e) {
        $error = "Chyba při vracení položky: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vrátit položku - Správa skautských skladů</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/header.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Vrátit položku</h1>
                <a href="warehouse.php?id=<?php echo $item['warehouse_id']; ?>" class="btn btn-secondary">Zpět na sklad</a>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="return-confirmation">
                <h2>Potvrzení vrácení</h2>
                <p>Chystáte se vrátit následující položku:</p>
                
                <div class="item-details">
                    <p><strong>Název:</strong> <?php echo htmlspecialchars($item['name']); ?></p>
                    <p><strong>Sklad:</strong> <?php echo htmlspecialchars($item['warehouse_name']); ?></p>
                    <p><strong>Vypůjčeno:</strong> <?php echo htmlspecialchars($item['borrower_name']); ?></p>
                    <p><strong>Datum vypůjčení:</strong> <?php echo date('d.m.Y H:i', strtotime($item['borrowed_at'])); ?></p>
                </div>
                
                <form method="POST" action="return_item.php?id=<?php echo $item_id; ?>">
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Potvrdit vrácení</button>
                        <a href="warehouse.php?id=<?php echo $item['warehouse_id']; ?>" class="btn btn-secondary">Zrušit</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>