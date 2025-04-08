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

// Get item details
$stmt = $pdo->prepare("
    SELECT i.*, w.name as warehouse_name 
    FROM items i 
    JOIN warehouses w ON i.warehouse_id = w.id 
    WHERE i.id = ?
");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

// If item doesn't exist or is already borrowed, redirect
if(!$item || $item['is_borrowed']) {
    header("Location: warehouse.php?id=" . $item['warehouse_id']);
    exit;
}

// Process borrow action
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update item to borrowed status
    $stmt = $pdo->prepare("
        UPDATE items 
        SET is_borrowed = 1, borrowed_by = ?, borrowed_at = NOW() 
        WHERE id = ?
    ");
    
    try {
        $stmt->execute([$user_id, $item_id]);
        
        // Redirect back to warehouse
        header("Location: warehouse.php?id=" . $item['warehouse_id'] . "&success=borrowed");
        exit;
    } catch(PDOException $e) {
        $error = "Chyba při vypůjčení položky: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vypůjčit položku - Správa skautských skladů</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/header.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Vypůjčit položku</h1>
                <a href="warehouse.php?id=<?php echo $item['warehouse_id']; ?>" class="btn btn-secondary">Zpět na sklad</a>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="borrow-confirmation">
                <h2>Potvrzení vypůjčení</h2>
                <p>Chystáte se vypůjčit následující položku:</p>
                
                <div class="item-details">
                    <p><strong>Název:</strong> <?php echo htmlspecialchars($item['name']); ?></p>
                    <p><strong>Popis:</strong> <?php echo htmlspecialchars($item['description']); ?></p>
                    <p><strong>Sklad:</strong> <?php echo htmlspecialchars($item['warehouse_name']); ?></p>
                    <p><strong>Množství:</strong> <?php echo $item['quantity']; ?></p>
                </div>
                
                <form method="POST" action="borrow_item.php?id=<?php echo $item_id; ?>">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Potvrdit vypůjčení</button>
                        <a href="warehouse.php?id=<?php echo $item['warehouse_id']; ?>" class="btn btn-secondary">Zrušit</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>