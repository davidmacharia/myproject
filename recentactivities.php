<?php
$serverName = "localhost";
$username = "root";
$password = "";
$db = "landRegistration";

// Create a connection to MySQL database
$connecting = new mysqli($serverName, $username, $password, $db);

// Check connection
if ($connecting->connect_error) {
    die("Failed to connect: " . $connecting->connect_error);
}

// Array of tables to display data from
$tables = [
    'buyerDetails', 'sellerDetails', 'propertyDetails', 'Documents', 
    'WelcomeNotification', 'appointments', 'recentproperties', 
    'feedback', 'Users', 'UsersProfile', 'transferLog', 'transferRequests'
];

// Function to export data to CSV
function exportData($table, $connection) {
    $filename = $table . '_data.csv';
    $query = "SELECT * FROM $table";
    $result = $connection->query($query);

    if ($result->num_rows > 0) {
        $output = fopen('php://output', 'w');
        $fields = $result->fetch_fields();
        $headers = [];

        // Write the column headers
        foreach ($fields as $field) {
            $headers[] = $field->name;
        }
        fputcsv($output, $headers);

        // Write data rows
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }

        fclose($output);
    }
    
    // Log the activity
    logActivity('Exported data', $table, $connection);
}

// Function to delete all data from a table
function deleteAllData($table, $connection) {
    $query = "DELETE FROM $table";
    if ($connection->query($query)) {
        logActivity('Deleted all data', $table, $connection);
        return "All data from $table has been deleted.";
    } else {
        return "Error deleting data: " . $connection->error;
    }
}

// Function to log activity
function logActivity($action, $table, $connection) {
    $admin_id = 1; // Example admin ID, replace with dynamic value
    $stmt = $connection->prepare("INSERT INTO activity_log (action, table_name, admin_id) VALUES (?, ?, ?)");
    $stmt->bind_param('ssi', $action, $table, $admin_id);
    $stmt->execute();
}

// Handle POST actions for export and delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['export_table'])) {
        $table = $_POST['export_table'];
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $table . '_data.csv"');
        exportData($table, $connecting);
        exit;
    }

    if (isset($_POST['delete_table'])) {
        $table = $_POST['delete_table'];
        $message = deleteAllData($table, $connecting);
        echo "<script>alert('$message'); window.location.href = ''; </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recent Activities - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>

    <div class="container mt-4">
        <h1>Recent Activities</h1>
        <p>Below are the recent activities performed by the admin.</p>

        <!-- Table to display recent activities -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Table</th>
                    <th>Timestamp</th>
                    <th>Admin ID</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query to fetch recent activities
                $query = "SELECT * FROM activity_log ORDER BY timestamp DESC LIMIT 10"; // Fetch last 10 activities
                $result = $connecting->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['action']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['table_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['timestamp']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['admin_id']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>No recent activities found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </div>

    <!-- Include Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
