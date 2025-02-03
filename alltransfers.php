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

// Fetch pending transfer requests
$pendingQuery = "SELECT buyerDetails.FirstName, buyerDetails.LastName,
buyerDetails.Email,buyerDetails.Contact,
           sellerDetails.sContact,propertyDetails.*,
           sellerDetails.sFirstName, sellerDetails.sLastName,transferrequests.*
    FROM sellerDetails
     JOIN propertyDetails ON propertyDetails.ownerId=sellerDetails.sellerId
     JOIN  transferrequests ON transferrequests.id= sellerDetails.sellerId
      JOIN  buyerDetails ON buyerDetails.ownerId=sellerDetails.sellerId
     WHERE buyerDetails.Email = ? AND transferrequests.statecondition='Pending'";

$stmtPending = $connect->prepare($pendingQuery);
$stmtPending->bind_param("s", $email);
$stmtPending->execute();
$pendingTransfers = $stmtPending->get_result();

// Fetch approved transfer requests
$approvedQuery = "SELECT buyerDetails.FirstName, buyerDetails.LastName,
buyerDetails.Email,buyerDetails.Contact,
           sellerDetails.sContact,propertyDetails.*,
           sellerDetails.sFirstName, sellerDetails.sLastName,transferrequests.*
    FROM sellerDetails
    LEFT JOIN propertyDetails ON propertyDetails.ownerId=sellerDetails.sellerId
    LEFT JOIN  transferrequests ON transferrequests.id= sellerDetails.sellerId
     LEFT JOIN  buyerDetails ON buyerDetails.ownerId=sellerDetails.sellerId
     WHERE buyerDetails.Email = ? AND `statecondition`='approved'";
$stmtApproved = $connect->prepare($approvedQuery);
$stmtApproved->bind_param("s", $email);
$stmtApproved->execute();
$approvedTransfers = $stmtApproved->get_result();

// Fetch rejected/canceled transfer requests
$rejectedQuery = "SELECT buyerDetails.FirstName, buyerDetails.LastName,
buyerDetails.Email,buyerDetails.Contact,
           sellerDetails.sContact,propertyDetails.*,
           sellerDetails.sFirstName, sellerDetails.sLastName,transferrequests.*
    FROM buyerDetails
   LEFT  JOIN propertyDetails ON propertyDetails.ownerId=buyerDetails.ownerId
    LEFT JOIN  transferrequests ON transferrequests.id= buyerDetails.ownerId
    LEFT  JOIN  sellerDetails ON sellerDetails.sellerId=buyerDetails.ownerId
     WHERE buyerDetails.Email = ?  AND transferrequests.statecondition IN ('rejected', 'canceled')";
$stmtRejected = $connect->prepare($rejectedQuery);
$stmtRejected->bind_param("s", $email);
$stmtRejected->execute();
$rejectedTransfers = $stmtRejected->get_result();

// Fetch previous deeds
$previousDeedQuery = "SELECT buyerDetails.*, documents.* 
                      FROM buyerDetails 
                      JOIN documents ON buyerDetails.ownerId = documents.documentId
                      WHERE buyerDetails.Email = ?";
$stmtPreviousDeed = $connect->prepare($previousDeedQuery);
$stmtPreviousDeed->bind_param("s", $email);
$stmtPreviousDeed->execute();
$resultPreviousDeed = $stmtPreviousDeed->get_result();

$previousDeed = 'default_document.pdf'; // Fallback document path
if ($resultPreviousDeed->num_rows > 0) {
    while ($row = $resultPreviousDeed->fetch_assoc()) {
        $previousDeed = $row['titleDeed'];
    }
}

// Close statements
$stmtPending->close();
$stmtApproved->close();
$stmtRejected->close();
$stmtPreviousDeed->close();

// Query to count transfers by their state
$query = "SELECT statecondition, COUNT(*) AS total_transfers FROM transferrequests GROUP BY statecondition";
$result = $connect->query($query);

// Query to count total transfer
$countQuery = "SELECT COUNT(*) AS totalCount
               FROM sellerDetails
               LEFT JOIN propertyDetails ON propertyDetails.ownerId = sellerDetails.sellerId
               LEFT JOIN transferrequests ON transferrequests.id = sellerDetails.sellerId
               LEFT JOIN buyerDetails ON buyerDetails.ownerId = sellerDetails.sellerId
               WHERE buyerDetails.Email = ? AND transferrequests.statecondition = 'Pending'";

// Prepare the statement
$stmt = $connect->prepare($countQuery);
if (!$stmt) {
    die("Prepare statement failed: " . $connect->error);
}

// Bind parameters and execute
$stmt->bind_param("s", $email);
$stmt->execute();

// Fetch the count result
$result = $stmt->get_result();
if ($result) {
    $row = $result->fetch_assoc();
    $totalTransfers=$row['totalCount'];
} else {
    echo "No results found.";
}

// Close the statement
$stmt->close();
$connect->close();
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
        .alert-warning {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Land Transfers Summary -->
    <div class="card">
        <div class="card-header">
            Land Transfers Summary
        </div>
        <div class="card-body">
            <h4>Total Transfers: <?php echo htmlspecialchars($totalTransfers); ?></h4>
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>State</th>
                            <th>Total Transfers</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['statecondition']); ?></td>
                                <td><?php echo htmlspecialchars($row['total_transfers']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="alert alert-warning">No transfer records found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pending Transfers Section -->
    <div class="card">
        <div class="card-header">Pending Land Registration Transfers</div>
        <div class="card-body">
            <?php if ($pendingTransfers->num_rows > 0): ?>
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
                            <?php while ($row = $pendingTransfers->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['FirstName']."  ".$row['LastName'] ); ?></td>
                                    <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Contact']); ?></td>
                                    <td><?php echo htmlspecialchars($row['titleDeedNumber']); ?></td>
                                    <td><?php echo htmlspecialchars($row['LocationOfProperty']); ?></td>
                                    <td><?php echo htmlspecialchars($row['newOwnerFirstName']."    ".$row['newOwnerLastName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['newOwnerEmail']); ?></td>
                                    <td><?php echo htmlspecialchars($row['newOwnerContact']); ?></td>
                                    <td><?php echo htmlspecialchars($row['requestDate']); ?></td>
                                    <td>
                                        <a href="path/to/documents/<?php echo htmlspecialchars($row['uploadedDocument']); ?>" class="btn btn-primary" target="_blank">View Title Deed</a>
                                        <a href="path/to/documents/<?php echo htmlspecialchars($previousDeed); ?>" class="btn btn-secondary" target="_blank">Previous Documents</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="alert alert-warning">No pending land transfer records found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Approved Transfers Section -->
    <div class="card">
        <div class="card-header">Approved Land Transfers</div>
        <div class="card-body">
            <?php if ($approvedTransfers->num_rows > 0): ?>
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
                            <?php while ($row = $approvedTransfers->fetch_assoc()): ?>
                                <tr>
                                <td><?php echo htmlspecialchars($row['FirstName']."  ".$row['LastName'] ); ?></td>
                                    <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Contact']); ?></td>
                                    <td><?php echo htmlspecialchars($row['titleDeedNumber']); ?></td>
                                    <td><?php echo htmlspecialchars($row['LocationOfProperty']); ?></td>
                                    <td><?php echo htmlspecialchars($row['newOwnerFirstName']."    ".$row['newOwnerLastName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['newOwnerEmail']); ?></td>
                                    <td><?php echo htmlspecialchars($row['newOwnerContact']); ?></td>
                                    <td><?php echo htmlspecialchars($row['requestDate']); ?></td>
                                    <td>
                                        <a href="path/to/documents/<?php echo htmlspecialchars($row['uploadedDocument']); ?>" class="btn btn-primary" target="_blank">View Title Deed</a>
                                        <a href="path/to/documents/<?php echo htmlspecialchars($previousDeed); ?>" class="btn btn-secondary" target="_blank">Previous Documents</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="alert alert-warning">No approved land transfer records found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Rejected/Canceled Transfers Section -->
    <div class="card">
        <div class="card-header">Rejected/Canceled Land Transfers</div>
        <div class="card-body">
            <?php if ($rejectedTransfers->num_rows > 0): ?>
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
                            <?php while ($row = $rejectedTransfers->fetch_assoc()): ?>
                                <tr>
                                <td><?php echo htmlspecialchars($row['FirstName']."  ".$row['LastName'] ); ?></td>
                                    <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Contact']); ?></td>
                                    <td><?php echo htmlspecialchars($row['titleDeedNumber']); ?></td>
                                    <td><?php echo htmlspecialchars($row['LocationOfProperty']); ?></td>
                                    <td><?php echo htmlspecialchars($row['newOwnerFirstName']."    ".$row['newOwnerLastName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['newOwnerEmail']); ?></td>
                                    <td><?php echo htmlspecialchars($row['newOwnerContact']); ?></td>
                                    <td><?php echo htmlspecialchars($row['requestDate']); ?></td>
                                    <td>
                                        <a href="path/to/documents/<?php echo htmlspecialchars($row['uploadedDocument']); ?>" class="btn btn-primary" target="_blank">View Title Deed</a>
                                        <a href="path/to/documents/<?php echo htmlspecialchars($previousDeed); ?>" class="btn btn-secondary" target="_blank">Previous Documents</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="alert alert-warning">No rejected or canceled land transfer records found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
