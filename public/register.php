<?php include '../views/templates/header.php'; ?>
<?php

include '../src/helpers/db_connect.php'; 

// Variables for error messages and success feedback
$register_success = '';
$first_name_error = $last_name_error = $email_error = $phone_error = $address_error = $password_error = $confirm_password_error = $role_error = '';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Validate form inputs
   // Validate form inputs
if (empty($first_name) || !preg_match("/^[a-zA-Z\s]+$/", $first_name)) {
    $first_name_error = "Valid first name is required.";
}
if (empty($last_name) || !preg_match("/^[a-zA-Z\s]+$/", $last_name)) {
    $last_name_error = "Valid last name is required.";
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $email_error = "A valid email is required.";
}
if (empty($phone_number) || !preg_match("/^[0-9]{10}$/", $phone_number)) {
    $phone_error = "A valid phone number is required (10 digits).";
}
if (empty($address) || !preg_match("/^[a-zA-Z0-9\s,.-]+$/", $address)) {
    $address_error = "A valid address is required.";
}
if (empty($password)) {
    $password_error = "Password is required.";
} elseif (strlen($password) < 8) {
    $password_error = "Password must be at least 8 characters long.";
}
if ($password !== $confirm_password) {
    $confirm_password_error = "Passwords do not match.";
}
if (empty($role)) {
    $role_error = "Role is required.";
}


    // Proceed if there are no validation errors
    if (empty($first_name_error) && empty($last_name_error) && empty($email_error) && empty($phone_error) && empty($address_error) && empty($password_error) && empty($confirm_password_error) && empty($role_error)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, phone_number, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $first_name, $last_name, $email, $hashed_password, $phone_number, $address, $role);

        // Execute the statement
        if ($stmt->execute()) {
            $register_success = "Registration successful! <a href='login.php'>Log in here</a>.";
        } else {
            $register_success = "Error: Could not register.";
        }

        // Close the statement
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
    <title>Register</title>
</head>
<style>
    #password-strength {
        width: 100%;
        margin-top: 10px;
    }
    #strength-bar {
        height: 10px;
        background-color: lightgray;
        border-radius: 5px;
    }
    #strength-fill {
        height: 100%;
        width: 0;
        border-radius: 5px;
        transition: width 0.3s;
    }
</style>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4">Create Your Account</h2>

                <!-- Display success message -->
                <?php if (!empty($register_success)) : ?>
                    <div class="alert alert-success">
                        <?php echo $register_success; ?>
                    </div>
                <?php endif; ?>

                <!-- Registration form -->
                <form action="register.php" method="POST" novalidate>
                    <!-- First Name -->
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control <?php echo (!empty($first_name_error)) ? 'is-invalid' : ''; ?>" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name ?? ''); ?>" required>
                        <div class="invalid-feedback">
                            <?php echo $first_name_error; ?>
                        </div>
                    </div>

                    <!-- Last Name -->
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control <?php echo (!empty($last_name_error)) ? 'is-invalid' : ''; ?>" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name ?? ''); ?>" required>
                        <div class="invalid-feedback">
                            <?php echo $last_name_error; ?>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control <?php echo (!empty($email_error)) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        <div class="invalid-feedback">
                            <?php echo $email_error; ?>
                        </div>
                    </div>

                    <!-- Phone Number -->
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="text" class="form-control <?php echo (!empty($phone_error)) ? 'is-invalid' : ''; ?>" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($phone_number ?? ''); ?>" required>
                        <div class="invalid-feedback">
                            <?php echo $phone_error; ?>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control <?php echo (!empty($address_error)) ? 'is-invalid' : ''; ?>" id="address" name="address" required><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                        <div class="invalid-feedback">
                            <?php echo $address_error; ?>
                        </div>
                    </div>

                    <!-- Role Selection -->
                    <div class="mb-3">
                        <label for="role" class="form-label">Select Role</label>
                        <select class="form-select <?php echo (!empty($role_error)) ? 'is-invalid' : ''; ?>" id="role" name="role" required>
                            <option value="">Choose...</option>
                            <option value="artisan" <?php if (isset($role) && $role === 'artisan') echo 'selected'; ?>>Artisan</option>
                            <option value="customer" <?php if (isset($role) && $role === 'customer') echo 'selected'; ?>>Customer</option>

                        </select>
                        <div class="invalid-feedback">
                            <?php echo $role_error; ?>
                        </div>
                    </div>

                    <div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <input type="password" class="form-control <?php echo (!empty($password_error)) ? 'is-invalid' : ''; ?>" id="password" name="password" required>
    <div class="invalid-feedback">
        <?php echo $password_error; ?>
    </div>

    <!-- Password strength indicator -->
    <div id="password-strength" class="mt-2">
        <div id="strength-bar" style="height: 10px; width: 100%; background-color: lightgray; border-radius: 5px;">
            <div id="strength-fill" style="height: 100%; width: 0; background-color: red; border-radius: 5px;"></div>
        </div>
        <div id="strength-text" class="mt-1">Weak</div> <!-- Strength text -->
    </div>
</div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control <?php echo (!empty($confirm_password_error)) ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password" required>
                        <div class="invalid-feedback">
                            <?php echo $confirm_password_error; ?>
                        </div>
                    </div>

                    <!-- Register Button -->
                    <button type="submit" class="btn btn-primary w-100">Register</button>
                    <!-- Already have an account? Link -->
                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="login.php">Log in now</a>.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const strengthFill = document.getElementById('strength-fill');
    const strengthText = document.getElementById('strength-text');

    passwordInput.addEventListener('input', function() {
        const password = passwordInput.value;
        let strength = '';
        let strengthLevel = 0; // 0 for Weak, 1 for Normal, 2 for Strong

        if (password.length < 6) {
            strength = 'Weak';
            strengthLevel = 0;
            strengthFill.style.backgroundColor = 'red';
        } else if (password.length < 10) {
            strength = 'Normal';
            strengthLevel = 1;
            strengthFill.style.backgroundColor = 'orange';
        } else {
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            const hasSpecialChars = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            const criteriaMet = [hasUpperCase, hasLowerCase, hasNumbers, hasSpecialChars].filter(Boolean).length;

            if (criteriaMet === 4) {
                strength = 'Strong';
                strengthLevel = 2;
                strengthFill.style.backgroundColor = 'green';
            } else if (criteriaMet >= 2) {
                strength = 'Normal';
                strengthLevel = 1;
                strengthFill.style.backgroundColor = 'orange';
            } else {
                strength = 'Weak';
                strengthLevel = 0;
                strengthFill.style.backgroundColor = 'red';
            }
        }

        // Update the fill width and strength text
        strengthFill.style.width = `${(strengthLevel + 1) * 33.33}%`; // 0% for Weak, 66.66% for Normal, 100% for Strong
        strengthText.textContent = strength;
    });
});
</script>



<?php include '../views/templates/footer.php'; ?>
