<?php
$serverName="localhost";
$username="root";
$password="";
$db="landregistration";
    $connect=new mysqli($serverName,$username,$password,$db);
    if($connect->error){
        die("Failed to connect to Database".$connect->error);
    }// Include your database connection
session_start();
$email = $_SESSION['email'];

// SQL query to fetch ownership details
$sale = "SELECT buyerDetails.*, propertyDetails.*
         FROM propertyDetails 
         JOIN buyerDetails ON buyerDetails.ownerId = propertyDetails.ownerId
        
         
         WHERE buyerDetails.Email='$email';";

$allOwnerships = $connect->query($sale);
if ($allOwnerships->num_rows > 0) {
    $counter = 1; // Counter to display row numbers
    while ($row = $allOwnerships->fetch_assoc()) {
        $ownerId = $row['ownerId'];
        $name = $row['FirstName'] . ' ' . $row['LastName'];
        $FirstName= $row['FirstName'] ;
        $LastName= $row['LastName'];
        $email = $row['Email'];
        $contact = $row['Contact'];
        $titleDeedNumber=$row['titleDeedNumber'];
        $ownerStatus = $row['TypeOfOwnership'];
        $LocationOfProperty=$row['LocationOfProperty'];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Land Transfer Request</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 50px;
            max-width: 800px;
            background: #fff;
            box-shadow: 0px 0px 15px rgba(0,0,0,0.2);
            border-radius: 10px;
            padding: 30px;
        }
        .form-group label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .header-title {
            font-size: 24px;
            font-weight: bold;
            color: #343a40;
            margin-bottom: 20px;
            text-align: center;
        }
        .required {
            color: red;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="header-title"><i class="fas fa-file-contract"></i> Land Transfer Request Form</h2>
    <form action="transferprocess.php" method="POST">
        <!-- Owner Details Section -->
        <div class="form-group">
            <label for="ownerName">Owner Name <span class="required">*</span></label>
            <input type="text" class="form-control" id="ownerName" name="ownerName" value="<?php echo $name;?>" required>
        </div>
        <div style="display:none">
            <input type="text" class="form-control" id="ownerId" name="Id" value="<?php echo $ownerId;?>" >
            <input type="text" class="form-control" id="ownerf1" name="ownerf1" value="<?php echo $FirstName;?>" >
            <input type="text" class="form-control" id="ownerId" name="ownerf2" value="<?php echo $LastName;?>" >
            
        </div>
        

        <div class="form-group">
            <label for="ownerEmail">Owner Email <span class="required">*</span></label>
            <input type="email" class="form-control" id="ownerEmail" name="ownerEmail" value="<?php echo $email; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="ownerContact">Contact Number <span class="required">*</span></label>
            <input type="tel" class="form-control" id="ownerContact" name="ownerContact" value="<?php echo $contact;?>" required>
        </div>

        <!-- Property Details Section -->
        <div class="form-group">
            <label for="propertyId">Property ID/Title Deed Number <span class="required">*</span></label>
            <input type="text" class="form-control" id="propertyId" name="propertyId" value="<?php echo $titleDeedNumber;?>" required>
        </div>
        <div class="form-group">
            <label for="propertyLocation">Property Location <span class="required">*</span></label>
            <input type="text" class="form-control" id="propertyLocation" name="propertyLocation" value="<?php echo $LocationOfProperty;?>" required>
        </div>

        <!-- New Owner Details Section -->
        <div class="form-group">
            <label for="newOwnerName">New Owner first Name <span class="required">*</span></label>
            <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Enter first Name" required>
        </div>
        <div class="form-group">
            <label for="lastName"> New Owner Last Name <span class="required">*</span></label>
            <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Enter last Name" required>
        </div>
        <div class="form-group">
            <label for="newOwnerEmail">New Owner Email <span class="required">*</span></label>
            <input type="email" class="form-control" id="newOwnerEmail" name="newOwnerEmail" placeholder="Enter the new owner's email" required>
        </div>
        <div class="form-group">
            <label for="newOwnerContact">New Owner Contact Number <span class="required">*</span></label>
            <input type="tel" class="form-control" id="newOwnerContact" name="newOwnerContact" placeholder="Enter the new owner's contact number" required>
        </div>

        <!-- Additional Details -->
        <div class="form-group">
            <label for="transferReason">Reason for Transfer</label>
            <textarea class="form-control" id="transferReason" name="transferReason" rows="4" placeholder="Provide a brief reason for the transfer (optional)"></textarea>
        </div>
        <div class="form-group">
            <label for="uploadDocument">Upload Supporting Documents (if any)</label>
            <input type="file" class="form-control-file" id="uploadDocument" name="uploadDocument">
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Submit Transfer Request</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php }
else echo "no property";?>