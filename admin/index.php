<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../index.php");
    exit;
}

// Include database connection
require_once '../config/database.php';

// Get all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY name");
$users = $stmt->fetchAll();

// Success message
$success = isset($_GET['success']) ? $_GET['success'] : '';
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Scout Warehouse Manager</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/header.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>User management</h1>
                <a href="add_user.php" class="btn btn-primary">Add user</a>
            </div>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    <?php 
                    if($success == 'added') echo 'Uživatel byl úspěšně přidán.';
                    elseif($success == 'updated') echo 'Uživatel byl úspěšně aktualizován.';
                    elseif($success == 'deleted') echo 'Uživatel byl úspěšně smazán.';
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Admin</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if($user['is_admin']): ?>
                                        <span class="badge badge-success">Yes</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">No</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-small btn-secondary">Edit</a>
                                    <?php if($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Do you really want to delete this user?')">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="../js/script.js"></script>
</body>
</html>