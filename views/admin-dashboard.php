<?php
include '../views/templates/header.php';
?>

<div class="container mt-5">
    <h2>Admin Dashboard</h2>
    
    <!-- Dropdown for selecting user type -->
    <div class="mb-4">
        <label for="userType" class="form-label">Select User Type:</label>
        <select class="form-select" id="userType" onchange="fetchUsers()">
            <option value="">Select user type</option>
            <option value="artisan">Artisan</option>
            <option value="customer">Customer</option>
        </select>
    </div>
    
    <!-- Table to display users -->
    <table class="table table-striped" id="userTable">
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- User rows will be populated here by JavaScript -->
        </tbody>
    </table>
</div>

<!-- JavaScript to handle dropdown selection and AJAX request -->
<script>
function fetchUsers() {
    const userType = document.getElementById('userType').value;
    
    if (userType) {
        fetch(`fetch_users.php?role=${userType}`)
            .then(response => response.json())
            .then(data => {
                const tableBody = document.getElementById('userTable').getElementsByTagName('tbody')[0];
                tableBody.innerHTML = ''; // Clear previous rows

                data.forEach(user => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${user.first_name}</td>
                        <td>${user.last_name}</td>
                        <td>${user.phone_number}</td>
                        <td>${user.email}</td>
                        <td><button onclick="deleteUser(${user.id})" class="btn btn-danger btn-sm">Delete</button></td>
                    `;
                    tableBody.appendChild(row);
                });
            })
            .catch(error => console.error('Error fetching users:', error));
    }
}

// Function to delete user
function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch(`delete_user.php?id=${userId}`, { method: 'GET' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User deleted successfully');
                    fetchUsers(); // Refresh the user list
                } else {
                    alert('Failed to delete user');
                }
            })
            .catch(error => console.error('Error deleting user:', error));
    }
}
</script>
