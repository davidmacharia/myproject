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

// Start session
session_start();
if (!isset($_SESSION['email'])) {
    die("Unauthorized access. Please <a href='login.php'>login</a>.");
}

$email = $_SESSION['email'];

// Fetch documents and their conditions for a specific landowner
$stmt = $connection->prepare("
    SELECT 
        buyerDetails.*,
        propertyDetails.*,
        documents.*
    FROM 
        buyerDetails
    LEFT JOIN 
        propertyDetails ON buyerDetails.ownerId = propertyDetails.ownerId
    LEFT JOIN 
        documents ON propertyDetails.parcelId = documents.parcelId
    WHERE 
        buyerDetails.Email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploaded Documents</title>
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
            width: 80%;
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
        a {
            color: #4CAF50;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .no-data {
            text-align: center;
            margin: 20px 0;
            color: #666;
        }
        .upload-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
        .upload-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php 
                $parcelId = htmlspecialchars($row['parcelId']);
                $documents = [
                    "Title Deed" => $row['titleDeed'],
                    "Parent Document" => $row['parentDocumentId'] ? "Linked to ID: " . htmlspecialchars($row['parentDocumentId']) : "None",
                    "Encumbrance Certificate" => $row['encumbranceCert'],
                    "Agreement" => $row['agreement'],
                    "Payment Receipt" => $row['paymentReceipts'],
                    "Clearance Certificate" => $row['clearanceCert']
                ];
                $documentStatus = htmlspecialchars($row['documentStatus']);
            ?>
            <h1>Uploaded Documents for Parcel ID: <?= $parcelId ?></h1>
            <table>
                <thead>
                    <tr>
                        <th>Document Name</th>
                        <th>Document Status</th>
                        <th>Verification Status</th>
                        <th>View/Download</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $docName => $docValue): ?>
                        <?php 
                            $status = $docValue && file_exists($docValue) ? "Uploaded" : "Missing";
                            $viewLink = $docValue && file_exists($docValue) 
                                ? "<a href='" . htmlspecialchars($docValue) . "' target='_blank'>View/Download</a>" 
                                : "N/A";
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($docName) ?></td>
                            <td style="color: <?= $status === 'Uploaded' ? 'green' : 'red' ?>;"><?= $status ?></td>
                            <td><?= $documentStatus ?></td>
                            <td><?= $viewLink ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="no-data">No documents found for your account.</p>
    <?php endif; ?>

    <a href="uploadeddocument.php" class="upload-btn">Upload Documents</a>
</body>
</html>
<?php 
// Close the statement and database connection
$stmt->close();
$connection->close(); 
?>
