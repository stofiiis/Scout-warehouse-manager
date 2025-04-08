<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'config/database.php';

$user_id = $_SESSION['user_id'];

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get borrowed items
$stmt = $pdo->prepare("
    SELECT i.*, w.name as warehouse_name 
    FROM items i 
    JOIN warehouses w ON i.warehouse_id = w.id 
    WHERE i.borrowed_by = ? AND i.is_borrowed = 1
    ORDER BY i.borrowed_at DESC
");
$stmt->execute([$user_id]);
$borrowed_items = $stmt->fetchAll();

$error = '';
$success = '';

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Update name only
    if(!empty($name) && empty($current_password)) {
        $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        
        try {
            $stmt->execute([$name, $user_id]);
            $_SESSION['user_name'] = $name;
            $success = "Name has been successfully updated.";
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } catch(PDOException $e) {
            $error = "Error when updating the name: " . $e->getMessage();
        }
    }
    
    // Update password
    if(!empty($current_password)) {
        // Verify current password
        if(password_verify($current_password, $user['password'])) {
            // Check if new passwords match
            if($new_password === $confirm_password) {
                if(strlen($new_password) >= 6) {
                    // Hash new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // Update password
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    
                    try {
                        $stmt->execute([$hashed_password, $user_id]);
                        $success = "The password has been successfully changed.";
                    } catch(PDOException $e) {
                        $error = "Error changing password: " . $e->getMessage();
                    }
                } else {
                    $error = "The new password must be at least 6 characters long.";
                }
            } else {
                $error = "The new passwords do not match.";   
            }
        } else {
            $error = "The current password is incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Scout Warehouse Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/header.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>My Profile</h1>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="profile-container">
                <div class="profile-section">
                    <h2>Personal data</h2>
                    <form method="POST" action="profile.php" class="profile-form">
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <small>Email cannot be changed.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
                        </div>
                        
                        <h3>Change password</h3>
                        <div class="form-group">
                            <label for="current_password">Current password:</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New password:</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm a new password:</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
                
                <div class="profile-section">
                    <h2>My borrowed items</h2>
                    
                    <?php if(empty($borrowed_items)): ?>
                        <p>You have no borrowed items.</p>
                    <?php else: ?>
                        <div class="items-table-container">
                            <table class="items-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Warehouse</th>
                                        <th>Borrowed on</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($borrowed_items as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td><?php echo htmlspecialchars($item['warehouse_name']); ?></td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($item['borrowed_at'])); ?></td>
                                            <td>
                                                <a href="return_item.php?id=<?php echo $item['id']; ?>" class="btn btn-small btn-success">Return</a>
                                                <a href="warehouse.php?id=<?php echo $item['warehouse_id']; ?>" class="btn btn-small btn-secondary">Back to warehouse</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>