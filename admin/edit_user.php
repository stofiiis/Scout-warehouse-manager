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

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// If user doesn't exist, redirect
if(!$user) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    if(empty($name) || empty($email)) {
        $error = "Name and email are required fields.";
    } else {
        // Check if email already exists for other users
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        
        if($stmt->rowCount() > 0) {
            $error = "The email is already in use by another user.";
        } else {
            // Update user
            if(empty($password)) {
                // Update without changing password
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, is_admin = ? WHERE id = ?");
                $params = [$name, $email, $is_admin, $user_id];
            } else {
                // Update with new password
                if(strlen($password) < 6) {
                    $error = "The password must be at least 6 characters long.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, is_admin = ? WHERE id = ?");
                    $params = [$name, $email, $hashed_password, $is_admin, $user_id];
                }
            }
            
            if(empty($error)) {
                try {
                    $stmt->execute($params);
                    
                    // If updating current user, update session
                    if($user_id == $_SESSION['user_id']) {
                        $_SESSION['user_name'] = $name;
                        $_SESSION['user_email'] = $email;
                        $_SESSION['is_admin'] = $is_admin;
                    }
                    
                    // Redirect to user list
                    header("Location: index.php?success=updated");
                    exit;
                } catch(PDOException $e) {
                    $error = "User update error: " . $e->getMessage();
                }
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
    <title>Edit user - Scout Warehouse Manager</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Edit user</h1>
                <a href="index.php" class="btn btn-secondary">Back to list</a>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" action="edit_user.php?id=<?php echo $user_id; ?>">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password">
                        <small>Leave blank if you do not want to change the password.</small>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="is_admin" name="is_admin" <?php echo $user['is_admin'] ? 'checked' : ''; ?>>
                        <label for="is_admin">Administrator</label>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../js/script.js"></script>
</body>
</html>