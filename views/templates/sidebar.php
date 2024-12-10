<div class="col-md-3">
    <style>
        /* Sidebar Styling */
        .sidebar {
            border: none;
            background-color: #f8f9fa; /* Light gray for the card background */
            border-radius: 8px; /* Rounded corners */
            overflow: hidden;
        }

        .sidebar .card-header {
            font-size: 1.2rem;
            font-weight: bold;
            text-align: center; /* Center the title text */
            background-color: #007bff; /* Blue header background */
            color: white; /* White header text */
        }

        .sidebar .list-group-item {
            border: none; /* Removes borders */
            background-color: transparent; /* Transparent background for items */
            font-size: 1rem;
            padding: 12px 20px; /* Adjust spacing for better visuals */
            color: #495057; /* Slightly darker text color */
            transition: background-color 0.2s, color 0.2s; /* Smooth hover transition */
        }

        .sidebar .list-group-item i {
            margin-right: 8px; /* Space between icon and text */
            color: #6c757d; /* Muted color for icons */
        }

        .sidebar .list-group-item.active {
            background-color: #007bff; /* Blue background for active link */
            color: white; /* White text for active link */
            font-weight: bold; /* Bold text for emphasis */
        }

        .sidebar .list-group-item:hover {
            background-color: #e9ecef; /* Light hover effect */
            color: #007bff; /* Blue text on hover */
        }

        .sidebar .badge {
            font-size: 0.75rem;
            font-weight: bold;
            padding: 5px 8px;
            border-radius: 12px; /* Rounded badges */
            position: relative;
            top: -2px; /* Align vertically with text */
            background-color: #dc3545; /* Red for unread count */
            color: white;
        }
    </style>
    <div class="sidebar card shadow-sm">
        <div class="card-header">Navigation</div>
        <div class="list-group">
            <a href="profile.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user me-2"></i> Personal Information
            </a>
            <a href="order_history.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) === 'order_history.php' ? 'active' : ''; ?>">
                <i class="fas fa-history me-2"></i> Order History
            </a>
            <a href="notifications.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) === 'notifications.php' ? 'active' : ''; ?>">
                <i class="fas fa-bell me-2"></i> Notifications
                <?php if (!empty($unread_count) && $unread_count > 0): ?>
                    <span class="badge float-end"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="payment_methods.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) === 'payment_methods.php' ? 'active' : ''; ?>">
                <i class="fas fa-credit-card me-2"></i> Payment Methods
            </a>
            <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                <i class="fas fa-sign-out-alt me-2"></i> Sign Out
            </a>
        </div>
    </div>
</div>
