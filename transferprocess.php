<?php
// Database connection
$serverName = "localhost";
$username = "root";
$password = "";
$db = "landregistration";

// Create database connection
$connect = new mysqli($serverName, $username, $password, $db);

// Check connection
if ($connect->connect_error) {
    die("Failed to connect to Database: " . $connect->connect_error);
}
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $id = $_POST['Id'];
    $ownerFirstName = $_POST['ownerf1'];
    $ownerLastName = $_POST['ownerf2'];
    $ownerEmail = $_POST['ownerEmail'];
    $ownerContact = $_POST['ownerContact'];
    $propertyId = $_POST['propertyId'];
    $propertyLocation = $_POST['propertyLocation'];
    $newOwnerFirstName = $_POST['firstName'];
    $newOwnerLastName = $_POST['lastName'];
    $newOwnerEmail = $_POST['newOwnerEmail'];
    $newOwnerContact = $_POST['newOwnerContact'];
    $transferReason = isset($_POST['transferReason']) ? $_POST['transferReason'] : '';
    $newOwnerName = $newOwnerFirstName . ' ' . $newOwnerLastName;

    // Handle file upload
    $uploadedFile = '';
    $uploadDir = 'uploads/';
    if (isset($_FILES['uploadDocument']) && $_FILES['uploadDocument']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['uploadDocument']['tmp_name'];
        $fileName = $_FILES['uploadDocument']['name'];
        $uploadedFile = $uploadDir . basename($fileName);

        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move file to upload directory
        if (!move_uploaded_file($fileTmpPath, $uploadedFile)) {
            die("Error: Failed to upload file.");
        }
    }

    // Insert transfer request
    $status = "pending";
    $query = "INSERT INTO transferRequests 
              (id, newOwnerFirstName, newOwnerLastName, newOwnerEmail, newOwnerContact, transferReason, uploadedDocument, statecondition) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connect->prepare($query);
    if (!$stmt) {
        die("Error preparing query: " . $connect->error);
    }
    $stmt->bind_param("ssssssss", $id, $newOwnerFirstName, $newOwnerLastName, $newOwnerEmail, $newOwnerContact, $transferReason, $uploadedFile, $status);

    $time = date("H:i:s");
    $date = date("Y-m-d");
    $notificationQuery = " INSERT INTO `WelcomeNotification` 
    ( `Email`,`NotificationType`,`Messages`, `TimeRegistered`, `DateRegistered`, `Reaction`) 
                          VALUES ( ?, ?, ?, ?,?, 'unread')";
    $notificationStmt = $connect->prepare($notificationQuery);
    $type='update';
    $notificationMessage = "Your request for land transfer<br>title deed $propertyId<br>to $newOwnerName is in progress";
    $notificationStmt->bind_param("sssss", $ownerEmail, $type,$notificationMessage, $time, $date);

    if ($stmt->execute() && $notificationStmt->execute()) {
        // Insert seller details
        $insertSeller = "INSERT INTO `sellerDetails` (sellerId, sFirstName, sLastName, sEmail, sContact)
                         VALUES (?, ?, ?, ?, ?)";
        $sellerStmt = $connect->prepare($insertSeller);
        if (!$sellerStmt) {
            die("Error preparing seller query: " . $connect->error);
        }
        $sellerStmt->bind_param("sssss", $id, $ownerFirstName, $ownerLastName, $ownerEmail, $ownerContact);
        if ($sellerStmt->execute()) {
            echo "Data inserted in sellerDetails.";
        } else {
            echo "Error inserting seller details: " . $sellerStmt->error;
        }
        $sellerStmt->close();

        // Success message and redirection
        echo "<script>alert('Transfer request submitted successfully!');</script>";
        echo "<script>window.location.href = 'assets.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statements
    $stmt->close();
    $notificationStmt->close();
    $connect->close();
} else {
    echo "Invalid request method.";
}
?>
