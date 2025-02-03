<?php
// Database credentials
$serverName = "localhost";
$username = "root";
$password = "";
$db = "landregistration";

// Start session and validate login
session_start();
if (!isset($_SESSION['email'])) {
    die("Error: User is not logged in.");
}
$email = $_SESSION['email'];

// Create database connection
$connect = new mysqli($serverName, $username, $password, $db);
if ($connect->connect_error) {
    die("Database connection failed: " . $connect->connect_error);
}

// Fetch transfer requests
$sale = " SELECT buyerDetails.FirstName, buyerDetails.LastName,
sellerDetails.sEmail,
           sellerDetails.sContact,propertyDetails.*,
           sellerDetails.sFirstName, sellerDetails.sLastName,transferrequests.*
    FROM sellerDetails
    LEFT JOIN propertyDetails ON propertyDetails.ownerId=sellerDetails.sellerId
    LEFT JOIN  transferrequests ON transferrequests.id= sellerDetails.sellerId
     LEFT JOIN  buyerDetails ON buyerDetails.ownerId=sellerDetails.sellerId
     WHERE sellerDetails.sEmail = ?  AND transferrequests.statecondition='approved'";
$stmt_sale = $connect->prepare($sale);
if (!$stmt_sale) {
    die("Prepare statement failed: " . $connect->error);
}
$stmt_sale->bind_param("s", $email);
$stmt_sale->execute();
$allOwnerships = $stmt_sale->get_result();

// Initialize previous deed variable
$previousdeed = 'default_document.pdf'; // Fallback document path


$stmt_sale->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Land Registration Transfers</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }
        .container {
            margin-top: 30px;
        }
        .card {
            margin-bottom: 30px;
        }
        .table {
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
        }
        .table th {
            background-color: #007bff;
            color: white;
        }
        .table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .table tr:hover {
            background-color: #f1f1f1;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            padding: 10px;
            font-size: 1.5em;
        }
        .btn {
            margin: 5px 0;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            Approved Land  Transfers
        </div>
        <div class="card-body">
            <?php if ($allOwnerships->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Owner Name</th>
                                <th>Owner Email</th>
                                <th>Owner Contact</th>
                                <th>Title Deed</th>
                                <th>Location of Property</th>
                                <th>New Owner Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Date</th>
                                <th>Documents</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $allOwnerships->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['sFirstName'])."    ".$row['LastName']; ?></td>
                                    <td><?php echo htmlspecialchars($row['sEmail']); ?></td>
                                    <td><?php echo htmlspecialchars($row['sContact']); ?></td>
                                    <td><?php echo htmlspecialchars($row['titleDeedNumber']); ?></td>
                                    <td><?php echo htmlspecialchars($row['LocationOfProperty']); ?></td>
                                    <td><?php echo htmlspecialchars($row['FirstName']."    ".$row['LastName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['newOwnerEmail']); ?></td>
                                    <td><?php echo htmlspecialchars($row['newOwnerContact']); ?></td>
                                    <td><?php echo htmlspecialchars($row['requestDate']); ?></td>
                                    <td>
                                        <a href="<?php echo 'path/to/documents/' . htmlspecialchars($row['uploadedDocument']); ?>" class="btn btn-primary" target="_blank">View Title Deed</a><br>
                                        <a href="<?php echo 'path/to/documents/' . htmlspecialchars($previousdeed); ?>" class="btn btn-secondary" target="_blank">Previous Documents</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="alert alert-warning">No land transfer records found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
<?php
$connect->close();
?>
