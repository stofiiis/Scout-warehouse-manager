<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../index.php");
    exit;
}

// Include database connection
require_once '../config/database.php';

$error = '';
$success = '';

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    if(empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif(strlen($password) < 6) {
        $error = "The password must be at least 6 characters long.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if($stmt->rowCount() > 0) {
            $error = "Email is already registered.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, ?)");
            
            try {
                $stmt->execute([$name, $email, $hashed_password, $is_admin]);
                
                // Redirect to user list
                header("Location: index.php?success=added");
                exit;
            } catch(PDOException $e) {
                $error = "User creation error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přidat uživatele - Správa skautských skladů</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Přidat uživatele</h1>
                <a href="index.php" class="btn btn-secondary">Zpět na seznam</a>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" action="add_user.php">
                    <div class="form-group">
                        <label for="name">Jméno:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Heslo:</label>
                        <input type="password" id="password" name="password" required minlength="6">
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="is_admin" name="is_admin">
                        <label for="is_admin">Administrátor</label>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Uložit uživatele</button>
                        <a href="index.php" class="btn btn-secondary">Zrušit</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../js/script.js"></script>
</body>
</html>