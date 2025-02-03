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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Land Search Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            margin-left:2vw;
            display: flex;
            justify-content:center;
            align-items:center;
            height: 100vh;
        }
        @media screen and (max-width: 700px){
        .search-form {
            width:100%
            !important
        }
        }
        .search-form {
            background: #fff;
            padding: 20px;
            width:50vw;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .search-form h2 {
            margin-bottom:15px;
            text-align: center;
        }
        .search-form input {
            width:90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .search-form button {
            width: 60%;
            padding: 10px;
            margin-top: 5%;
            margin-left: 20%;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-form button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <form action="landsearch.php" method="post" class="search-form">
        <h4>Land Search</h4>
        <p style="color:red">*Fill the required details properly</p>
        <input type="text" name="titleDeedNumber" placeholder="title Deed Number"  value="<?php echo $titleDeedNumber;?>" id="title" required>
        <input type="number" id="idnum" name="IDno" placeholder=" Enter Owners National ID" >
        <input type="text" name="kraPin" placeholder="KRA PIN">
        <button type="submit">Search</button>
    </form>
</body>
</html>

