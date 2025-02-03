<?php
// Database configuration
session_start();
$email=$_SESSION['email'];
$servername = "localhost";
$username = "root"; // Update with your database username
$password = ""; // Update with your database password
$dbname = "landregistration"; // Update with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $userFirstName = $_POST['userFirstName'];
    $userLastName = $_POST['userLastName'];
    $userContact = $_POST['userContact'];
    $userEmail = $_POST['userEmail'];
    $userID = $_POST['userID'];
    $propertyType = $_POST['propertyType'];
    $propertySize = $_POST['propertySize'];
    $propertyLocation = $_POST['propertyLocation'];
    $ownershipType = $_POST['ownershipType'];
    $transactionType = $_POST['transactionType'];
    $titleDeedNumber = $_POST['titleDeedNumber'];

    // File upload paths
    $uploadsDir = "uploads/";
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0777, true);
    }

    $titleDeedPath = $uploadsDir . basename($_FILES['titleDeed']['name']);
    $agreementPath = $uploadsDir . basename($_FILES['agreement']['name']);
    $paymentReceiptPath = $uploadsDir . basename($_FILES['paymentReceipt']['name']);

    // Move uploaded files
    move_uploaded_file($_FILES['titleDeed']['tmp_name'], $titleDeedPath);
    move_uploaded_file($_FILES['agreement']['tmp_name'], $agreementPath);
    move_uploaded_file($_FILES['paymentReceipt']['tmp_name'], $paymentReceiptPath);

    // SQL query to insert data into the database
    $sql = "INSERT INTO recentproperties
            (userFirstName, userLastName, userContact, userEmail, userID, 
             propertyType, propertySize, propertyLocation, ownershipType, transactionType, 
             titleDeedNumber, titleDeedPath, agreementPath, paymentReceiptPath) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssss",
        $userFirstName,
        $userLastName,
        $userContact,
        $userEmail,
        $userID,
        $propertyType,
        $propertySize,
        $propertyLocation,
        $ownershipType,
        $transactionType,
        $titleDeedNumber,
        $titleDeedPath,
        $agreementPath,
        $paymentReceiptPath
    );

    // Execute the query and check the result
    if ($stmt->execute()) {
        echo "Land registration recorded successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Initiate Land Registration</title>
    <style>
        body {
            
        }
        #form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 900px;
            overflow-y: auto;
        }
        h2 {
            text-align: center;
            color: #4CAF50;
        }
        fieldset {
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
            padding: 20px;
        }
        legend {
            font-weight: bold;
            color: #4CAF50;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        input[type="text"], 
        input[type="email"], 
        input[type="number"], 
        input[type="tel"], 
        input[type="file"], 
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div id="form-container">
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post"
    enctype="multipart/form-data">
            <h2>Initiate Land Registration</h2>
<?php // Fetch user information from the database
$sale = "SELECT * FROM `Users` WHERE `Email`='$email';";
$allOwnerships = $conn->query($sale);
if ($allOwnerships->num_rows > 0) {
    while ($row = $allOwnerships->fetch_assoc()) {
        $name = $row['Username'];
        $semail = $row['Email'];
        $contact = $row['Contact'];
    }
}
// Close the connection
$conn->close();
?>
            <!-- User Information -->
            <fieldset>
                <legend>User Information</legend>
                <label for="userFirstName">First Name:</label>
                <input type="text" id="userFirstName" name="userFirstName" required>

                <label for="userLastName">Last Name:</label>
                <input type="text" id="userLastName" name="userLastName" required>

                <label for="userContact">Contact Number:</label>
                <input type="tel" id="userContact" value="<?php echo $contact;?>"name="userContact" required>

                <label for="userEmail">Email:</label>
                <input type="email" id="userEmail"  value="<?php echo $semail;?>" name="userEmail" required>

                <label for="userID">National ID Number:</label>
                <input type="number" id="userID" name="userID" required>
            </fieldset>

            <!-- Land Details -->
            <fieldset>
                <legend>Land Details</legend>
                <label for="propertyType">Property Type:</label>
                <select id="propertyType" name="propertyType" required>
                    <option value="residential">Residential</option>
                    <option value="commercial">Commercial</option>
                    <option value="agricultural">Agricultural</option>
                </select>

                <label for="propertySize">Property Size (Acres):</label>
                <input type="text" id="propertySize" name="propertySize" required>

                <label for="propertyLocation">Property Location:</label>
                <input type="text" id="propertyLocation" name="propertyLocation" required>

                <label for="ownershipType">Ownership Type:</label>
                <select id="ownershipType" name="ownershipType" required>
                    <option value="private">Private</option>
                    <option value="joint">Joint Ownership</option>
                    <option value="community">Community</option>
                </select>

                <label for="transactionType">Transaction Type:</label>
                <select id="transactionType" name="transactionType" required>
                    <option value="sale">Sale</option>
                    <option value="leasehold">Leasehold</option>
                    <option value="transfer">Transfer</option>
                </select>

                <label for="titleDeedNumber">Title Deed Number:</label>
                <input type="text" id="titleDeedNumber" name="titleDeedNumber" required>
            </fieldset>

            <!-- Document Uploads -->
            <fieldset>
                <legend>Document Uploads</legend>
                <label for="titleDeed">Upload Title Deed:</label>
                <input type="file" id="titleDeed" name="titleDeed" accept=".pdf,.jpg,.jpeg,.png" required>

                <label for="agreement">Upload Sale Agreement:</label>
                <input type="file" id="agreement" name="agreement" accept=".pdf,.jpg,.jpeg,.png" required>

                <label for="paymentReceipt">Upload Payment Receipt:</label>
                <input type="file" id="paymentReceipt" name="paymentReceipt" accept=".pdf,.jpg,.jpeg,.png" required>
            </fieldset>

            <button type="submit">Submit Land Registration</button>
        </form>
    </div>
</body>
</html>
