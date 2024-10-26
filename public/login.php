<?php
session_start();
include '../src/helpers/db_connect.php';

$login_error = '';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate email and password
    if (empty($email)) {
        $login_error = "Email is required.";
    } elseif (empty($password)) {
        $login_error = "Password is required.";
    } else {
        // Check if email exists in the database
        $stmt = $conn->prepare("SELECT id, first_name, last_name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // If email exists, check the password
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Set session variables for logged-in user
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_role'] = $user['role']; // Store the role in session

                // Redirect based on user role
                if ($user['role'] === 'admin') {
                    header("Location: dashboard.php");
                } elseif ($user['role'] === 'customer') {
                    header("Location: index.php");
                } elseif ($user['role'] === 'artisan') {
                    header("Location: product_management.php");
                } else {
                    header("Location: home.php"); // Default redirection for any other roles
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Login</title>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4">Login to Your Account</h2>

                <!-- Display error message -->
                <?php if (!empty($login_error)) : ?>
                    <div class="alert alert-danger">
                        <?php echo $login_error; ?>
                    </div>
                <?php endif; ?>

                <!-- Login form -->
                <form action="login.php" method="POST" novalidate>
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
                <p class="text-center mt-3">Don't have an account? <a href="register.php">Register here</a>.</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
