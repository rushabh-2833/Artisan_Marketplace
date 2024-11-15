<?php
session_start();
include '../src/helpers/db_connect.php'; // Include your database connection file

// Check if the user is logged in
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit;
}

// Fetch user information from the database
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone_number, address, password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit;
}

$stmt->close();

// Initialize variables for validation
$errors = [];
$first_name = $user['first_name'];
$last_name = $user['last_name'];
$email = $user['email'];
$phone_number = $user['phone_number'];
$address = $user['address'];
$password = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);

    // Manual Validation
    if (empty($first_name)) {
        $errors['first_name'] = "First name is required.";
    } elseif (!preg_match("/^[a-zA-Z ]*$/", $first_name)) {
        $errors['first_name'] = "First name must only contain letters.";
    }

    if (empty($last_name)) {
        $errors['last_name'] = "Last name is required.";
    } elseif (!preg_match("/^[a-zA-Z ]*$/", $last_name)) {
        $errors['last_name'] = "Last name must only contain letters.";
    }

    if (empty($email)) {
        $errors['email'] = "A valid email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    }

    if (empty($phone_number)) {
        $errors['phone_number'] = "Phone number is required.";
    } elseif (!preg_match("/^[0-9]{10,15}$/", $phone_number)) {
        $errors['phone_number'] = "Phone number must be between 10 to 15 digits.";
    }

    if (empty($address)) {
        $errors['address'] = "Address is required.";
    }

    if (!empty($password) && strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters long.";
    }

    // If no errors, proceed to update the information
    if (empty($errors)) {
        $hashed_password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : $user['password'];

        $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone_number=?, address=?, password=? WHERE id=?");
        $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $phone_number, $address, $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            header("Location: profile.php"); // Redirect back to profile page after update
            exit;
        } else {
            echo "Error updating information.";
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
    <link rel="stylesheet" href="style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit Profile</title>
</head>
<body>
<div class="container mt-5">
        <div class="card edit-pro shadow-sm">
            <div class="card-header text-center text-white">
                <h2>Edit Your Profile Information</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="edit_profile.php">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>">
                        <?php if (isset($errors['first_name'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['first_name']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>">
                        <?php if (isset($errors['last_name'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['last_name']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="text" class="form-control <?php echo isset($errors['phone_number']) ? 'is-invalid' : ''; ?>" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($phone_number); ?>">
                        <?php if (isset($errors['phone_number'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['phone_number']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" id="address" name="address" rows="3"><?php echo htmlspecialchars($address); ?></textarea>
                        <?php if (isset($errors['address'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['address']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password (Leave blank to keep current)</label>
                        <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password">
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <a href="profile.php" class="btn btn-secondary btn-custom">Back to Profile</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../views/templates/footer.php'; ?>
