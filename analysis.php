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

    // Log the activity of exporting data
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
    <title>Admin Database Report</title>
    <!-- Include Bootstrap for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h1 {
            color: #007bff;
        }
        h2 {
            color: #28a745;
        }
        .table-container {
            margin-bottom: 40px;
        }
        .table th, .table td {
            text-align: center;
        }
        .table-container table {
            width: 100%;
        }
        .card {
            margin-bottom: 20px;
        }
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .action-buttons button {
            margin-left: 10px;
        }
        .btn-action {
            background-color: #007bff;
            color: white;
            border: none;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
        }
        .btn-success {
            background-color: #28a745;
            color: white;
            border: none;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Admin Database Report</h1>
        <p>Welcome to the database analysis dashboard. Below, you can view and generate reports for each table. You can also export data to CSV or delete records as needed.</p>

        <?php
        foreach ($tables as $table) {
            echo "<div class='table-container card shadow'>";
            echo "<div class='card-body'>";
            echo "<h2>Data from Table: $table</h2>";

            // Query to get all data from the current table
            $query = "SELECT * FROM $table";
            $result = $connecting->query($query);

            // Action buttons (e.g., for exporting data, deleting rows)
            echo "<div class='action-buttons'>";
            echo "<form method='POST' style='display: inline;'>";
            echo "<input type='hidden' name='export_table' value='$table'>";
            echo "<button type='submit' class='btn btn-success btn-action'><i class='fas fa-file-export'></i> Export Data</button>";
            echo "</form>";
            echo "<form method='POST' style='display: inline;'>";
            echo "<input type='hidden' name='delete_table' value='$table'>";
            echo "<button type='submit' class='btn btn-danger btn-action'><i class='fas fa-trash'></i> Delete All Data</button>";
            echo "</form>";
            echo "</div>";

            if ($result->num_rows > 0) {
                // Start the table to display data
                echo "<table class='table table-bordered table-striped'>";
                echo "<thead><tr>";

                // Fetch and display column names as headers
                $fields = $result->fetch_fields();
                foreach ($fields as $field) {
                    echo "<th>" . ucfirst($field->name) . "</th>";
                }

                echo "</tr></thead><tbody>";

                // Fetch and display rows
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                    echo "</tr>";
                }

                echo "</tbody></table>";
            } else {
                echo "<p>No data available in this table.</p>";
            }

            echo "</div></div>";
        }

        // Close the connection
        $connecting->close();
        ?>

        <hr>

        <footer>
            <p class="text-center">&copy; <?php echo date('Y'); ?> Land Registration Dashboard</p>
        </footer>

    </div>

    <!-- Include Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <!-- Font Awesome for icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
</body>
</html>
