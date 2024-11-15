<?php include '../views/templates/header.php'; ?>
<?php

include '../src/helpers/db_connect.php';

$login_error = '';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email)) {
        $login_error = "Email is required.";
    } elseif (empty($password)) {
        $login_error = "Password is required.";
    } else {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_role'] = $user['role'];

                $stmt = $conn->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
                $stmt->bind_param("i", $user['id']);
                $stmt->execute();
                $cartItems = $stmt->get_result();

                $_SESSION['cart'] = [];
                while ($item = $cartItems->fetch_assoc()) {
                    $_SESSION['cart'][$item['product_id']] = ['quantity' => $item['quantity']];
                }

                if ($user['role'] === 'admin') {
                    header("Location: ../views/admin-dashboard.php");
                } elseif ($user['role'] === 'customer') {
                    header("Location: index.php");
                } elseif ($user['role'] === 'artisan') {
                    header("Location: product_management.php");
                } else {
                    header("Location: home.php");
                }
                exit;
            } else {
                $login_error = "Incorrect password. Please try again.";
            }
        } else {
            $login_error = "No account found with that email.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/login.css">
    <title>Login</title>
</head>
<body>
    <div class="container mt-5">
        <div class="login-container">
            <h2 class="text-center mb-4">Login In</h2>

            <?php if (!empty($login_error)) : ?>
                <div class="alert alert-danger">
                    <?php echo $login_error; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="login-form">
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control <?php echo (!empty($login_error)) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control <?php echo (!empty($login_error)) ? 'is-invalid' : ''; ?>" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <p class="text-center mt-3">Don't have an account? <a href="register.php" class="register-link">Register here</a>.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../views/templates/footer.php'; ?>