<?php

// Database connection
$serverName = "localhost";
$username = "root";
$password = "";
$db = "landregistration";
$connect = new mysqli($serverName, $username, $password, $db);

if ($connect->error) {
    die("Failed to connect to Database: " . $connect->error);
}

// Handle Add or Edit actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  
    if (isset($_POST['add_owner'])) {
        $ownerId = isset($_POST['ownerId']) ? intval($_POST['ownerId']) : null;
        $firstName = $connect->real_escape_string($_POST['FirstName']);
        $lastName = $connect->real_escape_string($_POST['LastName']);
        $email = $connect->real_escape_string($_POST['Email']);
        $phone = $connect->real_escape_string($_POST['Phone']);

        $addSQL = "INSERT INTO buyerDetails (FirstName, LastName, Email, Contact) 
                   VALUES ('$firstName', '$lastName', '$email', '$phone')";
        if ($connect->query($addSQL) === TRUE) {
            echo "<script>alert('Owner added successfully.'); window.location.href='landowners.php';</script>";
        } else {
            echo "<script>alert('Error adding owner: " . $connect->error . "');</script>";
        }
    } elseif (isset($_POST['update_owner'])) {
    
    $ownerId = isset($_POST['ownerId']) ? intval($_POST['ownerId']) : null;
    $firstName = $connect->real_escape_string($_POST['FirstName']);
    $lastName = $connect->real_escape_string($_POST['LastName']);
    $email = $connect->real_escape_string($_POST['Email']);
    $phone = $connect->real_escape_string($_POST['Phone']);

   
    // Debugging - Output the query
    $updateSQL = "UPDATE buyerDetails 
                  SET FirstName = '$firstName', 
                      LastName = '$lastName', 
                      Email = '$email', 
                      Contact = '$phone'
                  WHERE ownerId = $ownerId";


    if ($connect->query($updateSQL) === TRUE) {
        echo "<script>alert('Owner updated successfully.'); window.location.href='landowners.php';</script>";
    } else {
        error_log("Error executing query: $updateSQL");
        error_log("Error: " . $connect->error);
        echo "<script>alert('Error updating owner. Please check logs for details.');</script>";
    }
}

        
    }

// Handle Delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $ownerId = intval($_GET['id']);
    $deleteSQL = "DELETE FROM buyerDetails WHERE ownerId = $ownerId";
    if ($connect->query($deleteSQL) === TRUE) {
        echo "<script>alert('Owner deleted successfully.'); window.location.href='landowners.php';</script>";
    } else {
        echo "<script>alert('Error deleting owner: " . $connect->error . "');</script>";
    }
}

// Handle Transfer Property
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transfer_property'])) {
    $propertyId = isset($_POST['propertyId']) ? intval($_POST['propertyId']) : null;
    $newOwnerId = isset($_POST['newOwnerId']) ? intval($_POST['newOwnerId']) : null;

    if ($propertyId && $newOwnerId) {
        // 1. Retrieve Previous Owner Information
        $prevOwnerQuery = "SELECT buyerDetails.* 
                           FROM buyerDetails 
                           INNER JOIN propertyDetails ON buyerDetails.ownerId = propertyDetails.ownerId
                           WHERE  buyerDetails.ownerId = propertyDetails.ownerId";
        $stmtPrevOwner = $connect->prepare($prevOwnerQuery);
        $stmtPrevOwner->execute();
        $prevOwnerResult = $stmtPrevOwner->get_result();

        $prevOwnerExists = $prevOwnerResult->num_rows > 0;
        if ($prevOwnerExists) {
            $prevOwner = $prevOwnerResult->fetch_assoc();
            $prevOwnerId = $prevOwner['ownerId'];
            $prevFirstName = $prevOwner['FirstName'];
            $prevLastName = $prevOwner['LastName'];
            $prevEmail = $prevOwner['Email'];
            $prevContact = $prevOwner['Contact'];
            $percelId=$prevOwner['parcelId'];
    
        } else {
            // No previous owner found; default values
            $prevOwnerId = null;
            $prevFirstName = 'N/A';
            $prevLastName = 'N/A';
            $prevEmail = 'N/A';
            $prevContact = 'N/A';
            $prevTitleDeed = 'N/A';
        }

        // 2. Update Property Owner
        $transferSQL = "UPDATE propertyDetails SET ownerId = ?, parcelstatus='approved' WHERE parcelId = ?";
        $stmtTransfer = $connect->prepare($transferSQL);
        $stmtTransfer->bind_param("ii", $newOwnerId, $propertyId);

        if ($stmtTransfer->execute()) {
            // 3. Log Transfer Details
            $transferLogSQL = "INSERT INTO transferLog (propertyId, previousOwnerId, previousOwnerName, newOwnerId, transferDate) 
                               VALUES (?, ?, ?, ?, NOW())";
            $prevOwnerName = $prevFirstName . ' ' . $prevLastName;
            $stmtLog = $connect->prepare($transferLogSQL);
            $stmtLog->bind_param("iisi", $newOwnerId, $prevOwnerId, $prevOwnerName, $newOwnerId);
            $stmtLog->execute();

            // 4. Insert Seller Details
            $insertSellerSQL = "INSERT INTO sellerDetails (sellerId, sFirstName, sLastName, sEmail, sContact) 
                                VALUES (?, ?, ?, ?, ?)";
            $stmtSeller = $connect->prepare($insertSellerSQL);
            $stmtSeller->bind_param("issss", $prevOwnerId, $prevFirstName, $prevLastName, $prevEmail, $prevContact);
            $stmtSeller->execute();

            // 5. Retrieve New Owner Information
            $newOwnerQuery ="SELECT buyerDetails.* ,propertyDetails.*
                           FROM buyerDetails 
                           INNER JOIN propertyDetails ON buyerDetails.ownerId = propertyDetails.ownerId
                           WHERE  propertyDetails.ownerId = ?";
            $stmtNewOwner = $connect->prepare($newOwnerQuery);
            $stmtNewOwner->bind_param("i", $newOwnerId);
            $stmtNewOwner->execute();
            $newOwnerResult = $stmtNewOwner->get_result();

            if ($newOwnerResult->num_rows > 0) {
                $newOwner = $newOwnerResult->fetch_assoc();
                $newFirstName = $newOwner['FirstName'];
                $newLastName = $newOwner['LastName'];
                $newEmail = $newOwner['Email'];
                $newContact = $newOwner['Contact'];
                $newtitle=$newOwner['titleDeedNumber'];
                // 6. Insert into Transfer Requests
                $status = "approved";
                $transferReason = "done by admin";
                $insertTransferRequest = "INSERT INTO transferRequests 
                                          (id, newOwnerFirstName, newOwnerLastName, newOwnerEmail, newOwnerContact, transferReason, uploadedDocument, statecondition) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmtRequest = $connect->prepare($insertTransferRequest);
                $stmtRequest->bind_param("isssssss", $newOwnerId, $newFirstName, $newLastName, $newEmail, $newContact, $transferReason, $prevTitleDeed, $status);
                $stmtRequest->execute();

                $time = date("H:i:s");
                $date = date("Y-m-d");
                $notificationMessage = "Your request for land transfer<br>title deed $newtitle <br>to $newFirstName $newLastName has been completed";
                $notificationType = "Land Transfer"; // Assuming a static notification type. You can modify this as needed
                
                $notificationQuery = "INSERT INTO WelcomeNotification (Email, NotificationType, Messages, TimeRegistered, DateRegistered, Reaction) 
                                       VALUES (?, ?, ?, ?, ?, 'unread')";
                
                $stmtNotification = $connect->prepare($notificationQuery);
                
                if ($stmtNotification === false) {
                    // Check if there was an issue preparing the statement
                    error_log("Error preparing statement: " . $connect->error);
                } else {
                    // Binding the parameters for the query
                    $stmtNotification->bind_param("sssss", $newEmail, $notificationType, $notificationMessage, $time, $date);
                
                    if ($stmtNotification->execute()) {
                        // Success message or further actions
                        echo "Notification sent successfully!";
                    } else {
                        // Debugging error if the execution fails
                        error_log("Error executing query: " . $stmtNotification->error);
                        echo "Error sending notification.";
                    }
                }
                
                
                // Success Message
                echo "<script>alert('Property transferred successfully to $newFirstName $newLastName.'); window.location.href='landowners.php';</script>";
            } else {
                echo "<script>alert('Error retrieving new owner details.');</script>";
            }
        } else {
            echo "<script>alert('Error updating property ownership.');</script>";
        }
    } else {
        echo "<script>alert('Please provide valid Property ID and New Owner ID.');</script>";
    }
}

// Fetch properties for the dropdown
$propertiesQuery = "SELECT parcelId, LocationOfProperty, titleDeedNumber FROM propertyDetails";
$properties = $connect->query($propertiesQuery);

// Fetch owners for the dropdown
$ownersQuery = "SELECT ownerId, FirstName, LastName FROM buyerDetails";
$owners = $connect->query($ownersQuery);

// Fetch all owners
$search = isset($_GET['search']) ? $connect->real_escape_string($_GET['search']) : "";
$searchQuery = $search ? "WHERE buyerDetails.FirstName LIKE '%$search%' OR buyerDetails.LastName LIKE '%$search%' OR buyerDetails.Email LIKE '%$search%'" : "";

// Combined SQL Query: Join buyerDetails with propertyDetails, incorporating search query
$titl = "SELECT buyerDetails.*, propertyDetails.* 
         FROM propertyDetails 
         JOIN buyerDetails ON buyerDetails.ownerId = propertyDetails.ownerId
         $searchQuery
         ORDER BY buyerDetails.ownerId DESC";  // Sort by ownerId in descending order

$allO = $connect->query($titl);
//fetch others with no titledeed
// Fetch all owners
$search = isset($_GET['search']) ? $connect->real_escape_string($_GET['search']) : "";
$searchQuery = $search ? "WHERE FirstName LIKE '%$search%' OR LastName LIKE '%$search%' OR Email LIKE '%$search%'" : "";
$ownersQuery = "SELECT * FROM buyerDetails $searchQuery ORDER BY ownerId DESC";
$owners = $connect->query($ownersQuery);



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Land Owner Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        #dashboard-container {
            width: 90%;
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .search-container {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .search-container input[type="text"] {
            padding: 10px;
            width: 70%;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .search-container button {
            padding: 10px 20px;
            background-color: #2e8b57;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-container button:hover {
            background-color: #276d43;
        }
        .owner-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .owner-table th, .owner-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .owner-table th {
            background-color: #f2f2f2;
        }
        .actions a {
            text-decoration: none;
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border-radius: 3px;
            margin-right: 10px;
        }
        .actions a:hover {
            background-color: #0056b3;
        }
        .actions .delete {
            background-color: #dc3545;
        }
        .actions .delete:hover {
            background-color: #c82333;
        }
        form input[type="text"], form input[type="email"], form textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        form button:hover {
            background-color: #0056b3;
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div id="dashboard-container">
    <h1>Land Owner Management</h1>

    <!-- Search bar -->
    <div class="search-container">
        <form action="" method="GET">
            <input type="text" name="search" placeholder="Search by Name, Email, etc." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>
        <a href="?action=add" class="search-container button">Add Owner</a>
    </div>

    <!-- Owner Table -->
    <table class="owner-table">
        <thead>
            <tr>
                <th>#</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Title Deed Number</th>
                <th>Actions</th>
            </tr>
        </thead> 
        <tbody>
        <?php if (($allO && $allO->num_rows > 0) || ($owners && $owners->num_rows > 0)): ?>
    <?php $count = 1; ?>
    
    <?php
// Initialize an array to keep track of displayed owner IDs
$displayedOwnerIds = [];
?>

<?php // Process allO results
if ($allO && $allO->num_rows > 0): 
    while ($row = $allO->fetch_assoc()):
        // Check if the ownerId is already displayed
        if (!in_array($row['ownerId'], $displayedOwnerIds)): 
            // Add the ownerId to the displayedOwnerIds array
            $displayedOwnerIds[] = $row['ownerId'];
            ?>
            <tr>
                <td><?php echo $count++; ?></td>
                <td><?php echo htmlspecialchars($row['FirstName']); ?></td>
                <td><?php echo htmlspecialchars($row['LastName']); ?></td>
                <td><?php echo htmlspecialchars($row['Email']); ?></td>
                <td><?php echo htmlspecialchars($row['Contact']); ?></td>
                <td><?php echo htmlspecialchars($row['titleDeedNumber']); ?></td>
                <td class="actions">
                    <a href="?action=edit&id=<?php echo $row['ownerId']; ?>">Edit</a>
                    <a href="?action=delete&id=<?php echo $row['ownerId']; ?>" class="delete" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
        <?php endif; ?>
    <?php endwhile; ?>
<?php endif; ?>

<?php // Process owners results
if ($owners && $owners->num_rows > 0): 
    while ($row = $owners->fetch_assoc()):
        // Check if the ownerId is already displayed
        if (!in_array($row['ownerId'], $displayedOwnerIds)): 
            // Add the ownerId to the displayedOwnerIds array
            $displayedOwnerIds[] = $row['ownerId'];
            $firstName=htmlspecialchars($row['FirstName']);
            $lastName=htmlspecialchars($row['LastName']);
            $contact=htmlspecialchars($row['Contact']);
            $email=htmlspecialchars($row['Email']);
            ?>
            <tr>
                <td><?php echo $count++; ?></td>
                <td><?php echo $firstName; ?></td>
                <td><?php echo $lastName; ?></td>
                <td><?php echo $email; ?></td>
                <td><?php echo $contact; ?></td>
                <td></td> <!-- Empty cell for properties -->
                <td class="actions">
                    <a href="?action=edit&id=<?php echo $row['ownerId']; ?>">Edit</a>
                    <a href="?action=delete&id=<?php echo $row['ownerId']; ?>" class="delete" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
        <?php endif; ?>
    <?php endwhile; ?>
<?php endif; ?>

<?php else: ?>
    <tr><td colspan="7">No owners found.</td></tr>
<?php endif; ?>


        </tbody>
    </table>

    <!-- Add/Edit Owner Form -->
    <?php
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
        $ownerId = intval($_GET['id']);
        $ownerQuery = "SELECT * FROM buyerDetails WHERE ownerId = $ownerId";
        $ownerResult = $connect->query($ownerQuery);
        if ($ownerResult->num_rows > 0) {
            $ownerData = $ownerResult->fetch_assoc();
    ?>
    <h2>Edit Owner</h2>
    <form method="POST" action="">
        <input type="hidden" name="ownerId" value="<?php echo $ownerData['ownerId']; ?>">
        <input type="text" name="FirstName" placeholder="First Name" value="<?php echo htmlspecialchars($ownerData['FirstName']); ?>" required>
        <input type="text" name="LastName" placeholder="Last Name" value="<?php echo htmlspecialchars($ownerData['LastName']); ?>" required>
        <input type="email" name="Email" placeholder="Email" value="<?php echo htmlspecialchars($ownerData['Email']); ?>" required>
        <input type="text" name="Phone" placeholder="Phone" value="<?php echo htmlspecialchars($ownerData['Contact']); ?>" required>
        <button type="submit" name="update_owner">Update Owner</button>
    </form>
    <?php 
        }
    } elseif (isset($_GET['action']) && $_GET['action'] == 'add') {
        $sql = "SELECT * FROM `Users`;";
        $res = $connect->query($sql);
        if($res && $res->num_rows>0){
           
           
    ?>
    <h2>Add Owner</h2>
    <form method="POST" action="">
        <input type="text" name="FirstName" placeholder="First Name" required>
        <input type="text" name="LastName" placeholder="Last Name" required>
        <select type="email" name="Email" placeholder="Email" required onchange="changephone(this.value)">
            <option value="" disabled selected> select owner</option>
            <?php
             $res->data_seek(0);
             while($out=$res->fetch_assoc()){
                echo "<option>"."<article style='display:hidden'>".$out['userId']."</article>".$out['Email']."</option>";
            }

        }
        ?>
            </select>
            <input type="text" id = "phone" name="Phone" placeholder="Phone" required>
            <script>
                function changephone(id){
                var http= new XMLHttpRequest();
                http.open('POST', '<?php echo $_SERVER['PHP_SELF'];?>,true');
                http.onload = function(){
                    if(http.status===200){
                var contact=document.getElementById("phone").value;
                const response=http.responseText;
                contact=response;
                    }
                    else{
                        alert("error" + );
                    }
                }
                http.send(id);
                }
            </script>
        
        <button type="submit" name="add_owner">Add Owner</button>
    </form>
    <?php } ?>

<!-- Transfer Property Section -->
<h2>Transfer Property</h2>
<form method="POST" action="">
    <label for="propertyId">Select Property:</label>
    <select name="propertyId" required>
        <?php while ($property = $properties->fetch_assoc()): ?>
            <option value="<?php echo $property['parcelId']; ?>">
                <?php echo htmlspecialchars($property['LocationOfProperty']); ?> 
                (<?php echo htmlspecialchars($property['titleDeedNumber']); 
                $title=$property['titleDeedNumber'];
                ?>)</option>
        <?php endwhile; ?>
    </select>

    <label for="newOwnerId">Select New Owner:</label>
    <select name="newOwnerId" required>
        <?php 
        // Fetch all owners to populate the new owner dropdown
        $ownerQuery = "SELECT *FROM buyerDetails";
        $ownerResult = $connect->query($ownerQuery);
        while ($owner = $ownerResult->fetch_assoc()):
        ?>
            <option value="<?php echo $owner['ownerId']; ?>">
                <?php echo htmlspecialchars($owner['FirstName']) . ' ' . htmlspecialchars($owner['LastName']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit" name="transfer_property">Transfer Property</button>
</form>

</div>
</body>
</html>