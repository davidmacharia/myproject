<?php
// Database connection
$serverName = "localhost";
$username = "root";
$password = "";
$db = "landregistration";

$connect = new mysqli($serverName, $username, $password, $db);
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session to retrieve user information
session_start();
$email = $_SESSION['email']; // Assume userId is stored in the session
if (!isset($_SESSION['email'])) {
    die("Unauthorized access. Please <a href='login.php'>login</a>.");
}

$sql = "SELECT DISTINCT buyerDetails.*, propertyDetails.*, transferlog.* 
        FROM propertyDetails 
        LEFT JOIN buyerDetails ON propertyDetails.ownerId = buyerDetails.ownerId 
        LEFT JOIN transferlog ON propertyDetails.ownerId = transferlog.propertyId 
        WHERE buyerDetails.Email = '$email'";
        $result = $connect->query($sql);
        if ($result && $result->num_rows > 0){
            while ($row = $result->fetch_assoc()){
            $userId=$row['ownerId'];
            }
        }


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parcelId = $connect->real_escape_string($_POST['parcelId']);
    $uploadDir = 'uploads/';

    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $requiredFiles = [
        'titleDeed', 'agreement', 'originaTitleDeed',
        'PaymentReceipt', 'encumbranceCertificate', 'ClearanceCert'
    ];
    $uploadedFiles = [];

    // Process file uploads
    foreach ($requiredFiles as $fileKey) {
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES[$fileKey]['name']);
            $filePath = $uploadDir . uniqid() . '_' . $fileName;
            $fileType = mime_content_type($_FILES[$fileKey]['tmp_name']);

            // Validate file type (accepting common document/image formats)
            if (in_array($fileType, ['application/pdf', 'image/jpeg', 'image/png'])) {
                if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $filePath)) {
                    $uploadedFiles[$fileKey] = $filePath;
                } else {
                    echo "<p>Error uploading $fileName</p>";
                }
            } else {
                echo "<p>Invalid file type for $fileName</p>";
            }
        } else {
            echo "<p>Error uploading file: $fileKey</p>";
        }
    }

    if (count($uploadedFiles) === count($requiredFiles)) {
        $expiryDate = date('Y-m-d H:i:s', strtotime('+5 years'));



        $stmt = $connect->prepare("INSERT INTO Documents (
                parcelId, titleDeed, agreement, parentDocumentId, 
                paymentReceipts, encumbranceCert, clearanceCert, 
                issueDate,expiryDate, documentStatus, `version`, createdBy, lastUpdatedBy
            ) VALUES (?, ?, ?, NULL, ?, ?, ?, NOW(),'$expiryDate','draft', 1, ?, ?)
        ");
    
        if (!$stmt) {
            die("Prepare failed: " . $connect->error);
        }
    
        $stmt->bind_param(
            "ssssssss",
            $parcelId,
            $uploadedFiles['titleDeed'],
            $uploadedFiles['agreement'],
            $uploadedFiles['PaymentReceipt'],
            $uploadedFiles['encumbranceCertificate'],
            $uploadedFiles['ClearanceCert'],
            $userId,
            $userId
        );
    
        if ($stmt->execute()) {
            echo "<p>Documents uploaded successfully!</p>";
        } else {
            die("Database error: " . $stmt->error);
        }
    
        $stmt->close();    
    } else {
        echo "<p>All files are required to proceed.</p>";
    }
}

$connect->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Upload</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        fieldset {
            border: 2px solid #4CAF50;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        legend {
            font-weight: bold;
            color: #4CAF50;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="file"] {
            margin-bottom: 20px;
        }
        button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .alert {
            color: red;
            font-size: 14px;
            margin-top: -15px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Documents</h1>
        <form method="POST" enctype="multipart/form-data">
            <fieldset>
                <legend>Document Uploads</legend>
                <label for="parcelId">Parcel ID:</label>
                <input type="text" id="parcelId" name="parcelId" placeholder="Enter Parcel ID" required>

                <legend>Proof of Ownership</legend>
                <label for="titleDeed">Title Deed:</label>
                <input type="file" id="titleDeed" name="titleDeed" required>

                <label for="agreement">Sale Agreement:</label>
                <input type="file" id="agreement" name="agreement" required>

                <label for="originaTitleDeed">Previous Deed/Transfer Documents:</label>
                <input type="file" id="originaTitleDeed" name="originaTitleDeed" required>

                <label for="PaymentReceipt">Payment Receipt:</label>
                <input type="file" id="PaymentReceipt" name="PaymentReceipt" required>

                <label for="encumbranceCertificate">Encumbrance Certificate:</label>
                <input type="file" id="encumbranceCertificate" name="encumbranceCertificate" required>

                <label for="ClearanceCert">Clearance Cert:</label>
                <input type="file" id="ClearanceCert" name="ClearanceCert" required>
            </fieldset>
            <button type="submit">Submit Documents</button>
        </form>
    </div>
</body>
</html>
