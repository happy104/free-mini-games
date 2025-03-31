<div class="sidebar">
    <div class="logo">
        <a href="../index.html" target="_blank">
            <h2>Free Mini Games</h2>
        </a>
    </div>
    
    <div class="sidebar-menu">
        <ul>
            <li>
                <a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-home"></i> <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="add-game.php" <?php echo basename($_SERVER['PHP_SELF']) == 'add-game.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-plus"></i> <span>Add Game</span>
                </a>
            </li>
            <li>
                <a href="manage-games.php" <?php echo basename($_SERVER['PHP_SELF']) == 'manage-games.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-gamepad"></i> <span>Manage Games</span>
                </a>
            </li>
            <li>
                <a href="update-games-data.php" <?php echo basename($_SERVER['PHP_SELF']) == 'update-games-data.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-database"></i> <span>Update Game Data</span>
                </a>
            </li>
            <li>
                <a href="updateHomepageFeaturedGames.php" <?php echo basename($_SERVER['PHP_SELF']) == 'updateHomepageFeaturedGames.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-sync"></i> <span>Update Homepage</span>
                </a>
            </li>
            <li>
                <a href="update-all-games.php" <?php echo basename($_SERVER['PHP_SELF']) == 'update-all-games.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-list"></i> <span>Update All Games</span>
                </a>
            </li>
            <li>
                <a href="settings.php" <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-cog"></i> <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="sidebar-footer">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
    </div>
</div>