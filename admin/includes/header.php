<header class="main-header">
    <div class="header-content">
        <div class="logo">
            <a href="https://linharteum.cz/skaut/index.php">Správa skautských skladů</a>
        </div>
        
        <nav class="main-nav">
            <ul>
                <li><a href="https://linharteum.cz/skaut/">Sklady</a></li>
                <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <li><a href="https://linharteum.cz/skaut/admin/index.php">Správa uživatelů</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="user-menu">
                <div class="user-dropdown">
                    <a href="#" class="user-menu-toggle">
                        <?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_email']); ?> 
                        <span class="dropdown-arrow">▼</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="https://linharteum.cz/skaut/profile.php">Můj profil</a></li>
                        <li><a href="https://linharteum.cz/skaut/logout.php">Odhlásit se</a></li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>