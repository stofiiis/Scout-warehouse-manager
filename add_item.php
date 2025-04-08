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
if(!isset($_GET['warehouse_id']) || !is_numeric($_GET['warehouse_id'])) {
    header("Location: index.php");
    exit;
}

$warehouse_id = $_GET['warehouse_id'];

// Get warehouse details
$stmt = $pdo->prepare("SELECT * FROM warehouses WHERE id = ?");
$stmt->execute([$warehouse_id]);
$warehouse = $stmt->fetch();

// If warehouse doesn't exist, redirect to home
if(!$warehouse) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $quantity = (int)$_POST['quantity'];
    
    if(empty($name) || $quantity < 1) {
        $error = "Název je povinný a množství musí být alespoň 1.";
    } else {
        // Insert new item
        $stmt = $pdo->prepare("INSERT INTO items (warehouse_id, name, description, quantity) VALUES (?, ?, ?, ?)");
        
        try {
            $stmt->execute([$warehouse_id, $name, $description, $quantity]);
            $success = "Položka byla úspěšně přidána.";
            
            // Redirect to warehouse page after short delay
            header("refresh:2;url=warehouse.php?id=" . $warehouse_id);
        } catch(PDOException $e) {
            $error = "Chyba při přidávání položky: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přidat položku - <?php echo htmlspecialchars($warehouse['name']); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/header.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Přidat položku do skladu: <?php echo htmlspecialchars($warehouse['name']); ?></h1>
                <a href="warehouse.php?id=<?php echo $warehouse_id; ?>" class="btn btn-secondary">Zpět na sklad</a>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" action="add_item.php?warehouse_id=<?php echo $warehouse_id; ?>">
                    <div class="form-group">
                        <label for="name">Název položky:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Popis:</label>
                        <textarea id="description" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Množství:</label>
                        <input type="number" id="quantity" name="quantity" min="1" value="1" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Uložit položku</button>
                        <a href="warehouse.php?id=<?php echo $warehouse_id; ?>" class="btn btn-secondary">Zrušit</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>