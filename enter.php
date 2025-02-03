<?php
$serverName = "localhost";
$username = "root";
$password = "";
$db = "landRegistration";
$table = "buyerDetails";

// Create a connection to MySQL database
$connecting = new mysqli($serverName, $username, $password, $db);

// Check connection
if ($connecting->connect_error) {
    die("Failed to connect: " . $connecting->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Collect buyer details
    $buyerFirstName = $_POST['buyerFirstName'];
    $buyerLastName = $_POST['buyerLastName'];
    $buyerContact = $_POST['buyerContact'];
    $buyerEmail = $_POST['buyerEmail'];
    $buyerIDno = $_POST['buyerIDno'];
    $buyerKraPIN = $_POST['buyerKraPIN'];
    
    // Collect seller details
    $sellerFirstName = $_POST['sellerFirstName'];
    $sellerLastName = $_POST['sellerLastName'];
    $sellerContact = $_POST['sellerContact'];
    $sellerEmail = $_POST['sellerEmail'];
    $sellerIDno = $_POST['sellerIDno'];
    $sellerKraPIN = $_POST['sellerKraPIN'];
    
    // Collect property details
    $propertyType = $_POST['propertyType'];
    $valueOfPropety = $_POST['valueOfPropety'];
    $locationOfPropety = $_POST['locationOfPropety'];
    $typeOfOwnership = $_POST['typeOfOwnership'];
    $titleDeedNumber = $_POST['titleDeedNumber'];
    $typeOftransaction = $_POST['typeOfTransaction'];
    $price = $_POST['price'];  // Collecting the price from the form
    
    // Define function to handle file uploads
    function uploadFile($file, $fieldName) {
        $uploadDirectory = "documents/";
        if (!file_exists('documents/')) {
            mkdir('documents/', 0777, true);
        }

        $targetFile = $uploadDirectory . basename($file["name"]);
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ["jpg", "jpeg", "png", "pdf", "docx"];

        // Check if file is a valid type
        if (!in_array($fileType, $allowedTypes)) {
            return "Error: Invalid file type for $fieldName.";
        }

        // Check if file already exists
        if (file_exists($targetFile)) {
            return "Error: $fieldName file already exists.";
        }

        // Check file size (max 10MB)
        if ($file["size"] > 10485760) {
            return "Error: $fieldName file is too large.";
        }

        // Attempt to upload the file
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            return $targetFile;
        } else {
            return "Error: Failed to upload $fieldName.";
        }
    }

    // File uploads for buyer and seller documents
    $buyerColouredPassport = uploadFile($_FILES['buyerColouredPassport'], "Buyer Coloured Passport");
    $sellerColouredPassport = uploadFile($_FILES['sellerColouredPassport'], "Seller Coloured Passport");
    $agreement = uploadFile($_FILES['agreement'], "Sale Agreement");
    $originaTitleDeed = uploadFile($_FILES['originaTitleDeed'], "Previous Deed/Transfer Documents");
    $encumbranceCertificate = uploadFile($_FILES['encumbranceCertificate'], "Encumbrance Certificate");

    // Additional files (You may need to adjust these for actual file inputs)
    $Deed_file = uploadFile($_FILES['titleDeed'], "Deed File");
    $TransferDocuments = uploadFile($_FILES['originaTitleDeed'], "Transfer Documents");
    $PaymentReceipt = uploadFile($_FILES['PaymentReceipt'], "Payment Receipt");
    $ClearanceCert = uploadFile($_FILES['ClearanceCert'], "Clearance Certificate");

    // Insert Land Registration Form data
    $message = "Land registration for land number $titleDeedNumber completed successfully.";
    $time = date("H:i:s");
    $date = date("Y-m-d");
    $details = "
    INSERT INTO `buyerdetails` VALUES ('', '$buyerFirstName', '$buyerLastName', '$buyerEmail', '$buyerContact', '$buyerIDno', '$buyerKraPIN', '$buyerColouredPassport');
    INSERT INTO `sellerdetails` VALUES ('', '$sellerFirstName', '$sellerLastName', '$sellerEmail', '$sellerContact', '$sellerIDno', '$sellerKraPIN', '$sellerColouredPassport');
    INSERT INTO `propertydetails` VALUES ('', '$titleDeedNumber', '$propertyType', '$valueOfPropety', '$price', '$typeOfOwnership', '$locationOfPropety', '$typeOftransaction');
    INSERT INTO `documents` VALUES ('', '$titleDeedNumber', '$Deed_file', '$TransferDocuments', '$encumbranceCertificate', '$agreement', '$PaymentReceipt', '$ClearanceCert');
    INSERT INTO `WelcomeNotification` VALUES ('', '$buyerEmail', '$message', '$time', '$date', 'unread');
";

    // Execute SQL queries
    $in = $connecting->multi_query($details);
    if ($in == false) {
        echo "An error occurred while inserting data.";
    }

    // If any file upload failed, display the error and stop processing
    if (strpos($buyerColouredPassport, 'Error') !== false || strpos($sellerColouredPassport, 'Error') !== false || 
        strpos($Deed_file, 'Error') !== false || strpos($agreement, 'Error') !== false || 
        strpos($originaTitleDeed, 'Error') !== false || strpos($encumbranceCertificate, 'Error') !== false ||
        strpos($Deed_file, 'Error') !== false || strpos($TransferDocuments, 'Error') !== false ||
        strpos($PaymentReceipt, 'Error') !== false || strpos($ClearanceCert, 'Error') !== false) {
        echo "File upload error(s) occurred.";
        exit;
    }

    // Save the form data to a database or process further (this example just displays the data)
    echo "<h2>Registration Successful!</h2>";
    echo "<p>Buyer Name: $buyerFirstName $buyerLastName</p>";
    echo "<p>Seller Name: $sellerFirstName $sellerLastName</p>";
    echo "<p>Property Type: $propertyType</p>";
    echo "<p>Property Location: $locationOfPropety</p>";
    echo "<p>Size/Value of Property: $valueOfPropety acres</p>";
    echo "<p>Price: KES $price</p>";  // Displaying price

    // Display uploaded file paths (For debugging or confirmation)
    echo "<h3>Uploaded Files:</h3>";
    echo "<p>Buyer Coloured Passport: $buyerColouredPassport</p>";
    echo "<p>Seller Coloured Passport: $sellerColouredPassport</p>";
    echo "<p>Title Deed: $titleDeed</p>";
    echo "<p>Sale Agreement: $agreement</p>";
    echo "<p>Previous Title Deed: $originaTitleDeed</p>";
    echo "<p>Encumbrance Certificate: $encumbranceCertificate</p>";
} else {
    echo "Invalid request method.";
}
?>


