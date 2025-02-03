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
$pendingQuery = "SELECT buyerDetails.*,
                       transferlog.*,transferrequests.*,
                     propertyDetails.*,sellerDetails.*
              FROM transferrequests
               LEFT JOIN buyerDetails ON transferrequests.id=buyerDetails.ownerId
              LEFT JOIN transferlog ON transferrequests.id=transferlog.newOwnerId
              LEFT JOIN sellerDetails ON transferrequests.id=sellerDetails.sellerId
    LEFT JOIN  propertyDetails ON transferrequests.id=propertyDetails.ownerId
     WHERE  transferrequests.statecondition='pending';";
$pendingTransfers = $connect->query($pendingQuery);

$approvedQuery = "SELECT DISTINCT buyerDetails.*,
                       transferlog.*,transferrequests.*,
                     propertyDetails.*,sellerDetails.*
              FROM transferlog
        RIGHT JOIN buyerDetails ON transferlog.newOwnerId=buyerDetails.ownerId
              RIGHT JOIN transferrequests ON transferlog.newOwnerId=transferrequests.id
              RIGHT JOIN sellerDetails ON transferlog.newOwnerId=sellerDetails.sellerId
    INNER JOIN  propertyDetails ON transferlog.propertyId=propertyDetails.ownerId
     WHERE  transferrequests.statecondition='approved'";
$approvedTransfers = $connect->query($approvedQuery);

$rejectedQuery = "SELECT buyerDetails.*,
                       transferlog.*,transferrequests.*,
                     propertyDetails.*,sellerDetails.*
              FROM transferrequests
               LEFT JOIN buyerDetails ON transferrequests.id=buyerDetails.ownerId
              LEFT JOIN transferlog ON transferrequests.id=transferlog.newOwnerId
              LEFT JOIN sellerDetails ON transferrequests.id=sellerDetails.sellerId
    LEFT JOIN  propertyDetails ON transferrequests.id=propertyDetails.ownerId
     WHERE  transferrequests.statecondition IN ('rejected', 'canceled')";
$rejectedTransfers = $connect->query($rejectedQuery);





?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Land Transfer Management</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
        }
        .container {
            margin-top: 30px;
        }
        .card-header {
            font-size: 1.25rem;
            font-weight: bold;
        }
        .btn-action {
            margin: 5px;
        }
        .section-header {
            color: #007bff;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center section-header">Admin Panel - Land Transfer Management</h2>

    <!-- Display Message -->
    <?php if (isset($message)): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Pending Transfers -->
    <div class="card">
        <div class="card-header bg-warning text-white">Pending Transfers</div>
        <div class="card-body">
            <?php if ($pendingTransfers->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Owner Name</th>
                                                                <th>Title Deed</th>
                                <th>New Owner Name</th>
                                <th>Request Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $pendingTransfers->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['previousOwnerName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['titleDeedNumber']); ?></td>
                                    <td><?php echo htmlspecialchars($row['FirstName']."    ".
                                $row['LastName']
                                ); ?></td>
                                    <td><?php echo htmlspecialchars($row['requestDate']); ?></td>
                                    <td>
                                        <form action="" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button class="btn btn-success btn-sm btn-action" type="submit">Approve</button>
                                        </form>
                                        <form action="" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button class="btn btn-danger btn-sm btn-action" type="submit">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="alert alert-info">No pending transfers available.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Approved Transfers -->
    <div class="card">
        <div class="card-header bg-success text-white">Approved Transfers</div>
        <div class="card-body">
            <?php if ($approvedTransfers->num_rows > 0):             
                ?>
                     <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Owner Name</th>
                                                                <th>Title Deed</th>
                                <th>New Owner Name</th>
                                <th>Request Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $approvedTransfers->fetch_assoc()): 
                            //previous
                            $ownerFirstName=htmlspecialchars($row['sFirstName']);
                            $ownerLastName=htmlspecialchars($row['sLastName']);
                                $contact=htmlspecialchars($row['sContact']);
                                $email=htmlspecialchars($row['sEmail']);
                              
                                 //new owner
                                 $newOwnerFirstName=htmlspecialchars($row['newOwnerFirstName']);
                                 $newOwnerLastName=htmlspecialchars($row['newOwnerLastName']);
                                $newcontact=htmlspecialchars($row['newOwnerContact']);
                                $newemail=htmlspecialchars($row['newOwnerEmail']);
                                
                                ?>
                                
                                <tr>
                                    <td><?php echo $row['previousOwnerName']; ?></td>
                                    <td><?php echo  htmlspecialchars( $row['titleDeedNumber']); ?></td>
                                    <td><?php echo htmlspecialchars($row['FirstName']."    ".
                                $row['LastName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['requestDate']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="alert alert-info">No approved transfers available.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Rejected/Canceled Transfers -->
    <div class="card">
        <div class="card-header bg-danger text-white">Rejected/Canceled Transfers</div>
        <div class="card-body">
            <?php if ($rejectedTransfers->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Owner Name</th>
                                                                <th>Title Deed</th>
                                <th>previous Owner Name</th>
                                <th>Request Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $rejectedTransfers->fetch_assoc()): ?>
                                <tr>
                                <td><?php echo htmlspecialchars($row['previousOwnerName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['titleDeedNumber']); ?></td>
                                    <td><?php echo htmlspecialchars($row['newOwnerFirstName']."  ".$row['newOwnerLastName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['requestDate']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="alert alert-info">No rejected/canceled transfers available.</p>
            <?php endif; 



// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transferId = $_POST['id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $updateQuery = "UPDATE transferrequests SET statecondition='approved' WHERE id=?";
        
        $insert="INSERT INTO `sellerDetails`(sellerId,sFirstName,sLastName,sEmail,sContact)
        VALUES('','$ownerFirstName','$ownerLastName','$email','$contact');";
        if($connect->query($insert)==TRUE){
       echo "data inserted in sellerDetails";
        }
        else{
            echo "data not inserted";
        }

    } elseif ($action === 'reject') {
        $updateQuery = "UPDATE transferrequests SET statecondition='rejected' WHERE id=?";
    } else {
        die("Invalid action.");
    }

    $stmt = $connect->prepare($updateQuery);
    $stmt->bind_param("i", $transferId);

    if ($stmt->execute()) {
        $message = "Transfer request has been " . ($action === 'approve' ? "approved" : "rejected") . " successfully.";
    } else {
        $message = "Error updating record: " . $connect->error;
    }
    $stmt->close();
}
            // Close database connection
$connect->close();?>
        </div>
    </div>
</div>
</body>
</html>
