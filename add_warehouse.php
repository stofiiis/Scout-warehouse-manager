<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'config/database.php';

$error = '';
$success = '';

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    
    if(empty($name) || empty($location)) {
        $error = "Název a umístění jsou povinné položky.";
    } else {
        // Insert new warehouse
        $stmt = $pdo->prepare("INSERT INTO warehouses (name, location, description) VALUES (?, ?, ?)");
        
        try {
            $stmt->execute([$name, $location, $description]);
            $success = "Sklad byl úspěšně vytvořen.";
            
            // Redirect to warehouse page after short delay
            header("refresh:2;url=index.php");
        } catch(PDOException $e) {
            $error = "Chyba při vytváření skladu: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přidat sklad - Správa skautských skladů</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/header.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Přidat nový sklad</h1>
                <a href="index.php" class="btn btn-secondary">Zpět na seznam</a>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" action="add_warehouse.php">
                    <div class="form-group">
                        <label for="name">Název skladu:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Umístění:</label>
                        <input type="text" id="location" name="location" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Popis:</label>
                        <textarea id="description" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Uložit sklad</button>
                        <a href="index.php" class="btn btn-secondary">Zrušit</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>