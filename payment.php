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

// Start session to get email
session_start();
if (!isset($_SESSION['email'])) {
    echo "<h2>Please log in to view the properties owned.</h2>";
    exit();
}

$email = $_SESSION['email'];

// SQL query to fetch properties for the given email using prepared statements
$sql = "SELECT DISTINCT buyerDetails.*, propertyDetails.*, transferlog.* 
        FROM propertyDetails 
        LEFT JOIN buyerDetails ON propertyDetails.ownerId = buyerDetails.ownerId 
        LEFT JOIN transferlog ON propertyDetails.ownerId = transferlog.propertyId 
        WHERE buyerDetails.Email = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if property exists for the given email
if ($result->num_rows === 0) {
    echo "<h2>No properties found for the provided email address.</h2>";
    exit();
}

$property = $result->fetch_assoc(); // Assuming you are working with one property

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form data
    $parcel_id = $property['parcelId'];
    $owner_id = $property['ownerId'];
    $payee_id = 1; // Assuming payee ID is 1 (e.g., government)
    $amount = (float) $_POST['amount'];
    $payment_method = $_POST['payment_method'];

    // Insert data into the transactions table
    $insertQuery = "INSERT INTO transactions (parcel_id, owner_id, payee_id, transaction_type, 
                        amount, transaction_date, `status`, payment_method) 
                    VALUES (?, ?, ?, 'Payment', ?, NOW(), 'Pending', ?)";

    $stmt = $connect->prepare($insertQuery);
    $stmt->bind_param("iiiss", $parcel_id, $owner_id, $payee_id, $amount, $payment_method);

    if ($stmt->execute()) {
        echo "<p>Payment Successful! Transaction ID: " . $stmt->insert_id . "</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }
}

// SQL query to fetch transaction history for the given email
$historySql = "SELECT * FROM transactions 
               WHERE owner_id = (SELECT ownerId FROM buyerDetails WHERE Email = ?) 
               ORDER BY transaction_date DESC";
$historyStmt = $connect->prepare($historySql);
$historyStmt->bind_param("s", $email);
$historyStmt->execute();
$historyResult = $historyStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Land Registration Payment</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f8f9fa; }
        header { background-color: #2e8b57; color: white; padding: 20px; text-align: center; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        h2 { text-align: center; color: #333333; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 14px; color: #555555; margin-bottom: 5px; }
        .form-group input, .form-group select { width: 100%; padding: 10px; font-size: 14px; border: 1px solid #cccccc; border-radius: 5px; }
        button { width: 100%; padding: 10px; font-size: 16px; color: white; background-color: #2e8b57; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #4caf50; }
        footer { text-align: center; padding: 10px; background-color: #2e8b57; color: white; font-size: 12px; position: fixed; bottom: 0; width: 100%; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #cccccc; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Make a Payment</h2>
        <form action="" method="post">
            <div class="form-group">
                <label for="payer-name">Payer Name:</label>
                <input type="text" id="payer-name" name="payer_name" required placeholder="Enter your full name" value="<?php echo htmlspecialchars($property['FirstName'] . ' ' . $property['LastName']); ?>">
            </div>
            <div class="form-group">
                <label for="title-number">Title Number:</label>
                <input type="text" id="title-number" name="title_number" required placeholder="Enter the title number" value="<?php echo htmlspecialchars($property['titleDeedNumber']); ?>">
            </div>
            <div class="form-group">
                <label for="payment-method">Payment Method:</label>
                <select id="payment-method" name="payment_method" required>
                    <option value="" disabled selected>Select a payment method</option>
                    <option value="credit-card">Credit Card</option>
                    <option value="debit-card">Debit Card</option>
                    <option value="mobile-money">Mobile Money</option>
                    <option value="bank-transfer">Bank Transfer</option>
                </select>
            </div>
            <div class="form-group">
                <label for="amount">Amount (KES):</label>
                <input type="number" id="amount" name="amount" required placeholder="Enter the payment amount" value="<?php echo htmlspecialchars($property['price']); ?>">
            </div>
            <button type="submit">Pay Now</button>
        </form>

        <h2>Transaction History</h2>
        <table>
            <tr>
                <th>Transaction ID</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
            <?php
            if ($historyResult->num_rows > 0) {
                while ($row = $historyResult->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['transaction_id']) . "</td>";
                    echo "<td>KES " . number_format($row['amount'], 2) . "</td>";
                    echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['transaction_date']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No transaction history found.</td></tr>";
            }
            ?>
        </table>
    </div>

    <footer>&copy; 2024 Land Registration System. All Rights Reserved.</footer>
</body>
</html>
