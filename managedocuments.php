<?php
$serverName = "localhost";
$username = "root";
$password = "";
$db = "landRegistration";

// Create a connection to MySQL database
$connection = new mysqli($serverName, $username, $password, $db);

// Check connection
if ($connection->connect_error) {
    die("Failed to connect: " . $connection->connect_error);
}
session_start();

// Check if email is set in session, else redirect to login
if (!isset($_SESSION['email'])) { 
    die("Unauthorized access. Please <a href='login.php'>login</a>.");
}

$mail = $_SESSION['email'];
$members = "SELECT * FROM `Users` WHERE `Email`='$mail' AND `Account`='admin';";
$response = $connection->query($members);

if ($response->num_rows > 0) {
    $row = $response->fetch_assoc();
    $adminId = $row['userId'];
} else {
    die("Unauthorized access. You are not an admin.");
}

// Fetch all properties, documents, and their statuses
$sql = "SELECT 
            buyerDetails.*, 
            propertyDetails.*, 
            documents.*,
                        buyerDetails.Email AS buyerEmail

        FROM 
            buyerDetails
        LEFT JOIN 
            propertyDetails ON buyerDetails.ownerId = propertyDetails.ownerId
        LEFT JOIN 
            documents ON propertyDetails.parcelId = documents.parcelId
            LEFT JOIN 
            Users ON buyerDetails.Email=Users.Email
            WHERE propertyDetails.parcelId = documents.parcelId ";

$result = $connection->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Document Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        h1 {
            text-align: center;
            color: #4CAF50;
        }
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 15px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .btn {
            padding: 8px 12px;
            color: white;
            background-color: #4CAF50;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Admin Panel - Document Verification</h1>
    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Owner Name</th>
                    <th>Parcel ID</th>
                    <th>Document Name</th>
                    <th>Status</th>
                    <th>Verification Status</th>
                    <th>View</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php 
                        $documents = [
                            "Deed File" => $row['titleDeed'],
                            "Encumbrance Certificate" => $row['encumbranceCert'],
                            "Agreement" => $row['agreement'],
                            "Payment Receipt" => $row['paymentReceipts'],
                            "Clearance Certificate" => $row['clearanceCert']
                        ];
                        $ownerName = htmlspecialchars($row['FirstName'] . " " . $row['LastName']);
                        $newemail = $row['buyerEmail'];
                        $parcelId = htmlspecialchars($row['parcelId']);
                        $newtitle=$row['titleDeedNumber'];
                        $documentStatus = htmlspecialchars($row['documentStatus']);
                    ?>
                    <?php foreach ($documents as $docName => $docPath): ?>
                        <tr>
                            <td><?= $ownerName ?></td>
                            <td><?= $parcelId ?></td>
                            <td><?= htmlspecialchars($docName) ?></td>
                            <td style="color: <?= file_exists($docPath) ? 'green' : 'red' ?>;">
                                <?= file_exists($docPath) ? "Uploaded" : "Missing" ?>
                            </td>
                            <td><?= htmlspecialchars($documentStatus) ?></td>
                            <td>
                                <?= file_exists($docPath) ? "<a href='" . htmlspecialchars($docPath) . "' target='_blank'>View</a>" : "N/A" ?>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="document_id" value="<?= $row['documentId'] ?>">
                                    <select name="verification_status" required>
                                        <option value="">Select</option>
                                        <option value="approved">Approve</option>
                                        <option value="rejected">Reject</option>
                                    </select>
                                    <button type="submit" name="verify_document" class="btn">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center;">No records found.</p>
    <?php endif; ?>
</body>
</html>

<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_document'])) {
    $documentId = $_POST['document_id'];
    $newStatus = $_POST['verification_status'];
    
    // Prepare the update query
    $updateSql = "UPDATE documents SET documentStatus = ?, lastUpdatedBy = ?, lastUpdatedAt = NOW() WHERE documentId = ?";
    $stmt = $connection->prepare($updateSql);
    
    // Bind parameters and execute
    $stmt->bind_param("sii", $newStatus, $adminId, $documentId);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>Document verification status updated successfully.</p>";
        // Send Notification
$time = date("H:i:s");
$date = date("Y-m-d");
$notificationMessage = "Your documents for land title deed '$newtitle' have been $newStatus.";

// Insert notification into WelcomeNotification table
$notificationQuery = "INSERT INTO WelcomeNotification (Email, NotificationType, Messages,
 TimeRegistered, DateRegistered, Reaction) 
                       VALUES (?, 'update', ?, ?, ?, 'unread')";

$stmtNotification = $connection->prepare($notificationQuery);
$stmtNotification->bind_param("ssss", $newemail, $notificationMessage, $time, $date);

if ($stmtNotification->execute()) {
    echo "<p>Notification inserted successfully.</p>";
} else {
    echo "<p>Error inserting notification: " . $stmtNotification->error . "</p>";
}
        
        
    } else {
        echo "<p style='color: red;'>Error updating status: " . $stmt->error . "</p>";
    }
}

// Close the database connection

$connection->close(); 
?>
