<?php
include "receive.php";
$serverName = "localhost";
$username = "root";
$password = "";
$db = "landregistration";

// Establish connection to the database
$connect = new mysqli($serverName, $username, $password, $db);
if ($connect->error) {
    die("Failed to connect to Database: " . $connect->error);
} else {
    // Count queries for dashboard stats
    $counts = [
        'totalUsers' => "SELECT COUNT(*) AS count FROM `Users`;",
        'admins' => "SELECT COUNT(*) AS count FROM `Users` WHERE `Account` = 'admin';",
        'landOwners' => "SELECT COUNT(*) AS count FROM `Users` WHERE `Account` = 'landOwner';",
        'surveyors' => "SELECT COUNT(*) AS count FROM `Users` WHERE `Account` = 'surveyer';"
    ];

    $totals = [];
    foreach ($counts as $key => $query) {
        $result = $connect->query($query);
        $totals[$key] = $result->fetch_assoc()['count'] ?? 0;
    }

    // Default query for all users
    $query = "SELECT * FROM `Users`";
    $searchQuery = '';

    // Check if search is performed
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $searchQuery = $connect->real_escape_string($_GET['search']);
        $query .= " WHERE `Username` LIKE '%$searchQuery%' 
                    OR `Email` LIKE '%$searchQuery%' 
                    OR `Account` LIKE '%$searchQuery%'";
    }

    // Get filtered or all users
    $allUsers = $connect->query($query);
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['addUser'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $contact = $_POST['contact'];
        $account = $_POST['account'];
        $pass = md5($_POST['secretekey']);
        $time=date("H:i:s");
        $date=date("Y-m-d");
        $insertion .="INSERT INTO `WelcomeNotification` VALUES('$account','$email',
        ' Welcome to County Department Ministry of Lands if you need anything let us know',
        '$time','$date','unread');";
        $task=$connect->query($insertion);
        if($task==false){
            echo "error";
        }
        $connect->query("INSERT INTO `Users` (Username, Email, Contact, Account,SecretPin) VALUES ('$username', '$email', '$contact', '$account','$pass')");
    } elseif (isset($_POST['editUser'])) {
        $userId = $_POST['userId'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $contact = $_POST['contact'];
        $account = $_POST['account'];
        $connect->query("UPDATE `Users` SET Username='$username', Email='$email', Contact='$contact', Account='$account' WHERE userId='$userId'");
    } elseif (isset($_POST['deleteUser'])) {
        $userId = $_POST['userId'];
        $connect->query("DELETE FROM `Users` WHERE userId='$userId'");
    }
    // Refresh to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .card-header {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .stats-card {
            margin-bottom: 20px;
        }
        .user-table {
            margin-top: 20px;
        }
        .section-header {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4">Admin Panel - User Management</h1>

        <!-- Dashboard Cards -->
        <div class="row text-center mb-4">
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <h3 class="text-primary"><?php echo $totals['totalUsers']; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body">
                        <h3 class="text-success"><?php echo $totals['admins']; ?></h3>
                        <p>Admins</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body">
                        <h3 class="text-warning"><?php echo $totals['landOwners']; ?></h3>
                        <p>Land Owners</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body">
                        <h3 class="text-info"><?php echo $totals['surveyors']; ?></h3>
                        <p>Surveyors</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="mb-4">
            <form method="GET" class="row g-3">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="search" placeholder="Search by name, email, or role..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </form>
        </div>

        <!-- User Management Table -->
        <div class="mb-4">
            <h3>User Management</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $counter=0;
                    if ($allUsers->num_rows > 0): ?>
                        <?php while ($row = $allUsers->fetch_assoc()): $counter++;?>
                            <tr>
                                <td><?php echo $counter; ?></td>
                                <td><?php echo htmlspecialchars($row['Username']); ?></td>
                                <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                <td><?php echo htmlspecialchars($row['Contact']); ?></td>
                                <td><?php echo htmlspecialchars($row['Account']); ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal" onclick="editUser(<?php echo htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="userId" value="<?php echo $row['userId']; ?>">
                                        <button class="btn btn-danger btn-sm" name="deleteUser">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Add User Form -->
        <div class="mb-4">
            <h3>Add User</h3>
            <form method="POST" class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="username" placeholder="Name" required>
                </div>
                <div class="col-md-3">
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" name="contact" placeholder="Contact" required>
                </div>
                <div class="col-md-2">
                    <input type="password" class="form-control" name="secretekey" placeholder="password" required>
                </div>
                <div class="col-md-2">
                    <select class="form-control" name="account" required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="landOwner">Land Owner</option>
                        <option value="surveyer">Surveyor</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary" name="addUser">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="userId" id="editUserId">
                        <div class="mb-3">
                            <input type="text" class="form-control" name="username" id="editUsername" required>
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" name="email" id="editEmail" required>
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" name="contact" id="editContact" required>
                        </div>
                        <div class="mb-3">
                            <select class="form-control" name="account" id="editAccount" required>
                                <option value="admin">Admin</option>
                                <option value="landOwner">Land Owner</option>
                                <option value="surveyer">Surveyor</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-warning" name="editUser">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(user) {
            document.getElementById('editUserId').value = user.userId;
            document.getElementById('editUsername').value = user.Username;
            document.getElementById('editEmail').value = user.Email;
            document.getElementById('editContact').value = user.Contact;
            document.getElementById('editAccount').value = user.Account;
        }
    </script>
</body>
</html>
