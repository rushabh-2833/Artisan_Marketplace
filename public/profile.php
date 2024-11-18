    <?php include '../views/templates/header.php'; ?>
    <?php

    include '../src/helpers/db_connect.php'; 

    // Check if the user is logged in
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        header("Location: login.php"); // Redirect to login if not logged in
        exit;
    }

    // Fetch user information from the database
    $stmt = $conn->prepare("SELECT first_name, last_name, email, phone_number, address FROM users WHERE id = ?");
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
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
        <title>Your Profile</title>
    </head>
    <body>
    <div class="container mt-5">
        <div class="card profile-card shadow">
            <div class="card-header text-center text-white">
                <h2 class="mb-0 text-white">Your Profile Information</h2>
            </div>
            <div class="card-body p-4">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <th>First Name</th>
                            <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Last Name</th>
                            <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td><?php echo htmlspecialchars($user['address']); ?></td>
                        </tr>
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-4">
                    <a href="index.php" class="btn btn-primary me-2">Back to Home</a>
                    <a href="edit_profile.php" class="btn btn-outline-primary">Edit Profile</a>
                </div>
            </div>
        </div>
    </div>

        <?php include '../views/templates/footer.php'; ?>
