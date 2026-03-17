<?php
/**
 * Admin - Manage Users
 * View, edit, and manage all users
 */

require_once __DIR__ . '/../includes/session.php';

// Require admin access
requireAdmin();

$username = getCurrentUsername();
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);
    
    try {
        $pdo = getPDOConnection();
        
        if ($action === 'toggle_status' && $userId) {
            $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ? AND id != ?");
            $stmt->execute([$userId, getCurrentUserId()]);
            $message = 'User status updated successfully.';
            $messageType = 'success';
        } elseif ($action === 'change_role' && $userId) {
            $newRole = $_POST['role'] ?? 'user';
            if (in_array($newRole, ['admin', 'user']) && $userId != getCurrentUserId()) {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$newRole, $userId]);
                $message = 'User role updated successfully.';
                $messageType = 'success';
            }
        } elseif ($action === 'delete' && $userId) {
            if ($userId != getCurrentUserId()) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $message = 'User deleted successfully.';
                $messageType = 'success';
            }
        }
    } catch (PDOException $e) {
        $message = 'An error occurred.';
        $messageType = 'danger';
    }
}

// Get all users
$users = [];
$search = sanitize($_GET['search'] ?? '');

try {
    $pdo = getPDOConnection();
    
    $sql = "SELECT * FROM users";
    $params = [];
    
    if ($search) {
        $sql .= " WHERE username LIKE ? OR email LIKE ?";
        $params = ['%' . $search . '%', '%' . $search . '%'];
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serenique | Manage Users</title>
    <link rel="icon" type="image/x-icon" href="../frontend/images/logo2.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-color: #d27b5a; --secondary-color: #f8f5f2; }
        body { font-family: 'Poppins', sans-serif; background: #f8f5f2; }
        .admin-header { background: linear-gradient(135deg, #d27b5a 0%, #b86a4d 100%); color: white; padding: 1.5rem 0; }
        .admin-header h1 { font-family: 'Playfair Display', serif; }
        .sidebar { background: white; min-height: calc(100vh - 100px); padding: 1.5rem; box-shadow: 2px 0 10px rgba(0,0,0,0.05); }
        .sidebar a { display: block; padding: 0.75rem 1rem; color: #333; text-decoration: none; border-radius: 8px; margin-bottom: 0.5rem; }
        .sidebar a:hover, .sidebar a.active { background: var(--secondary-color); color: var(--primary-color); }
        .content-card { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .content-card h5 { font-family: 'Playfair Display', serif; border-bottom: 2px solid var(--secondary-color); padding-bottom: 0.5rem; }
        .table th { background: var(--secondary-color); }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="m-0">Manage Users</h1>
                <div class="d-flex align-items-center gap-3">
                    <span>Welcome, <?php echo htmlspecialchars($username); ?></span>
                    <a href="../homepage.php" class="btn btn-outline-light btn-sm">View Site</a>
                    <a href="../logout.php" class="btn btn-light btn-sm">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <nav>
                    <a href="dashboard.php">📊 Dashboard</a>
                    <a href="manage_users.php" class="active">👥 Manage Users</a>
                    <a href="manage_products.php">📦 Manage Products</a>
                    <hr>
                    <a href="../homepage.php">🏠 Back to Site</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 py-4">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="m-0 border-0 pb-0">All Users (<?php echo count($users); ?>)</h5>
                        <form class="d-flex gap-2" method="GET">
                            <input type="text" name="search" class="form-control form-control-sm" 
                                   placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-sm btn-primary">Search</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                        <?php if ($user['id'] == getCurrentUserId()): ?>
                                            <span class="badge bg-info">You</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="change_role">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="role" class="form-select form-select-sm" style="width: auto;" 
                                                    onchange="this.form.submit()" <?php echo $user['id'] == getCurrentUserId() ? 'disabled' : ''; ?>>
                                                <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td><?php echo $user['last_login_at'] ? date('M j, Y H:i', strtotime($user['last_login_at'])) : 'Never'; ?></td>
                                    <td>
                                        <?php if ($user['id'] != getCurrentUserId()): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Toggle Status">
                                                    <?php echo $user['is_active'] ? '🚫' : '✅'; ?>
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">🗑️</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

