<?php
// Include database connection
$serverName = "localhost";
$username = "root";
$password = "";
$db = "landregistration";

$connect = new mysqli($serverName, $username, $password, $db);

// Check connection
if ($connect->connect_error) {
    die("Failed to connect to Database: " . $connect->connect_error);
}

// Start session to check admin login
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    echo "<h2>You must be an admin to view this page.</h2>";
    exit();
}

// Admin authentication is done via role stored in session variable (example: $_SESSION['role'])

// Fetch all transactions for the admin
$sql = "SELECT t.transaction_id, t.amount, t.payment_method, t.transaction_date, t.status, p.titleDeedNumber, b.FirstName, b.LastName 
        FROM transactions t
        LEFT JOIN propertyDetails p ON t.parcel_id = p.parcelId
        LEFT JOIN buyerDetails b ON p.ownerId = b.ownerId
        ORDER BY t.transaction_date DESC";
$result = $connect->query($sql);

// Handle transaction status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $transaction_id = $_POST['transaction_id'];
    $new_status = $_POST['status'];

    $updateSql = "UPDATE transactions SET status = ? WHERE transaction_id = ?";
    $stmt = $connect->prepare($updateSql);
    $stmt->bind_param("si", $new_status, $transaction_id);
    if ($stmt->execute()) {
        echo "<p>Status updated successfully.</p>";
    } else {
        echo "<p>Error updating status: " . $stmt->error . "</p>";
    }
}

// Handle transaction deletion
if (isset($_GET['delete_id'])) {
    $transaction_id = $_GET['delete_id'];
    $deleteSql = "DELETE FROM transactions WHERE transaction_id = ?";
    $stmt = $connect->prepare($deleteSql);
    $stmt->bind_param("i", $transaction_id);
    if ($stmt->execute()) {
        echo "<p>Transaction deleted successfully.</p>";
    } else {
        echo "<p>Error deleting transaction: " . $stmt->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Transaction Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f8f9fa; }
        header { background-color: #2e8b57; color: white; padding: 20px; text-align: center; }
        .container { max-width: 1000px; margin: 20px auto; padding: 20px; background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        h2 { text-align: center; color: #333333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #cccccc; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 14px; color: #555555; margin-bottom: 5px; }
        .form-group select { width: 100%; padding: 10px; font-size: 14px; border: 1px solid #cccccc; border-radius: 5px; }
        .form-group button { padding: 5px 10px; font-size: 14px; color: white; background-color: #4caf50; border: none; border-radius: 5px; cursor: pointer; }
      
    </style>
    <script>
        function filterProperties() {
            const filter = document.getElementById('search').value.toLowerCase();
            const rows = document.querySelectorAll(' tbody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
</head>
<body>

    <div class="container">
        <h2>Transaction Management</h2>
<input type="text" oninput="filterProperties()" placeholder="search transaction" id="search">
        <table>
            <tr>
                <th>Transaction ID</th>
                <th>Title Number</th>
                <th>Payer Name</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Transaction Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            
            <?php
            if ($result->num_rows > 0) {
            $counter=0;
                while ($row = $result->fetch_assoc()) {
                    $counter++;
                    echo "<tbody><tr>";
                    echo "<td>" .$counter . "</td>";
                    echo "<td>" . htmlspecialchars($row['titleDeedNumber']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['FirstName']) . " " . htmlspecialchars($row['LastName']) . "</td>";
                    echo "<td>KES " . number_format($row['amount'], 2) . "</td>";
                    echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['transaction_date']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td>
                            <form method='POST' style='display:inline-block;'>
                                <select name='status' required>
                                    <option value='Pending' " . ($row['status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                                    <option value='Completed' " . ($row['status'] == 'Completed' ? 'selected' : '') . ">Completed</option>
                                    <option value='Failed' " . ($row['status'] == 'Failed' ? 'selected' : '') . ">Failed</option>
                                </select>
                                <button type='submit' name='update_status' value='Update'>Update Status</button>
                                <input type='hidden' name='transaction_id' value='" . $row['transaction_id'] . "'>
                            </form>
                            <a href='?delete_id=" . $row['transaction_id'] . "' onclick='return confirm(\"Are you sure you want to delete this transaction?\");'>Delete</a>
                        </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No transactions found.</td></tr>";
            }
            ?>
            </tbody>
        </table>

    </div>

   
</body>
</html>
