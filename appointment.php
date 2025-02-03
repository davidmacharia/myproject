<?php
$serverName="localhost";
$username="root";
$password="";
$db="landregistration";
    $connect=new mysqli($serverName,$username,$password,$db);
    if($connect->error){
        die("Failed to connect to Database".$connect->error);
    }
// Assuming connection to the database is already made
$surveyors = $connect->query("SELECT * FROM `Users` WHERE `Account`='surveyer';");
$admin=$connect->query("SELECT *FROM `Users` WHERE `Account`='admin';");
// SQL query to fetch ownership details 
session_start();
$email = $_SESSION['email'];
$sale = "SELECT DISTINCT buyerDetails.*, propertyDetails.*, transferlog.* 
        FROM propertyDetails 
        LEFT JOIN buyerDetails ON propertyDetails.ownerId = buyerDetails.ownerId 
        LEFT JOIN transferlog ON propertyDetails.ownerId = transferlog.propertyId 
        WHERE buyerDetails.Email = '$email';";

$allOwnerships = $connect->query($sale);
// Handling form submission (appointment booking)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $survey_id = $_POST['survey_id'] ? : $_POST['admin_id'];
    $appointment_time = $_POST['appointment_time'];
    $user_name = $_POST['user_name'];
    $user_contact = $_POST['user_contact'];
    $message = $_POST['message'];

    // Insert into appointments table (assuming an 'appointments' table exists)
    $sql = "INSERT INTO `appointments` (`appointment_id`,`survey_id`, `user_name`, `user_contact`, `appointment_time`, `messages`,`statecondation`)
            VALUES ('','$survey_id', '$user_name', '$user_contact', '$appointment_time', '$message','pending')";

    if ($connect->query($sql) === TRUE) {
        $success_message = "Appointment booked successfully!";
    } else {
        $error_message = "Error booking appointment: " . $connect->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Booking | Land Management System</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }
        .container {
            margin-top: 30px;
        }
        .surveyor-card {
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #007BFF;
            color: white;
        }
        .form-control {
            margin-bottom: 15px;
        }
        .btn-primary {
            width: 100%;
        }
        .appointment-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .message-success, .message-error {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
        }
        .message-success {
            background-color: #28a745;
            color: white;
        }
        .message-error {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>

<body>

<div class="container">
    <h2 class="text-center">Book an Appointment</h2>

    <!-- Success or Error Message -->
    <?php if (isset($success_message)) { ?>
        <div class="message-success"><?php echo $success_message; ?></div>
    <?php } elseif (isset($error_message)) { ?>
        <div class="message-error"><?php echo $error_message; ?></div>
    <?php } ?>

    <!-- Available Surveyors -->
    <h4>Available Surveyors</h4>
    <div class="row">
        <?php while ($row = $surveyors->fetch_assoc()) { ?>
            <div class="col-md-4">
                <div class="card surveyor-card">
                    <div class="card-header">
                        <h5><?php 
                        echo $row['Username']; ?></h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Email:</strong> <?php echo $row['Email']; ?></p>
                        <p><strong>Contact:</strong> <?php echo $row['Contact']; ?></p>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <h4>Available Admins</h4>
    <div class="row">
        <?php while ($row = $admin->fetch_assoc()) { ?>
            <div class="col-md-4">
                <div class="card surveyor-card">
                    <div class="card-header">
                        <h5><?php 
                        echo $row['Username']; ?></h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Email:</strong> <?php echo $row['Email']; ?></p>
                        <p><strong>Contact:</strong> <?php echo $row['Contact']; ?></p>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    <!-- Appointment Form -->
    <div class="appointment-form">
        <h4>Schedule an Appointment</h4>
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <!-- Surveyor Selection -->
            <div class="form-group">
                <label for="survey_id">Select Surveyor</label>
                <select class="form-control" id="survey_id" name="survey_id" optional>
                    <option value="" disabled selected>Select Surveyor</option>
                    <?php
                    // Reset the surveyor query pointer to the beginning
                    $surveyors->data_seek(0);
                    while ($row = $surveyors->fetch_assoc()) {
                        echo "<option value='" . $row['userId'] . "'>" . $row['Username'] . "</option>";
                    }
                    ?>
                </select>
                <label for="survey_id">Select Admin</label>
                <select class="form-control" id="admin_id" name="survey_id" optional>
                    <option value="" disabled selected>Select Admin</option>
                    <?php
                    // Reset the surveyor query pointer to the beginning
                    $admin->data_seek(0);
                    while ($row = $admin->fetch_assoc()) {
                        echo "<option value='" . $row['userId'] . "'>" . $row['Username'] . "</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Appointment Date & Time -->
            <div class="form-group">
                <label for="appointment_time">Appointment Date & Time</label>
                <input type="datetime-local" class="form-control" id="appointment_time" name="appointment_time" required>
            </div>

            <!-- User Details -->
            <div class="form-group">
                <label for="user_name">Your Name</label>
                <?php  if ($allOwnerships->num_rows > 0) {
                    $counter = 1; // Counter to display row numbers
                    while ($row = $allOwnerships->fetch_assoc()) {
                        $ownerId = $row['ownerId'];
                        $name = $row['FirstName'] . ' ' . $row['LastName'];
                        $email = $row['Email'];
                        $contact = $row['Contact'];
                    }}
                    else{
                       $option= "SELECT * FROM `Users` WHERE `Email`='$email'";
                       $out=$connect->query($option);
                       if($out->num_rows>0){
                        while($out->fetch_assoc()){
                            $name = $row['Username'];
                            $email = $row['Email'];
                            $contact = $row['Contact'];
                        }
                       }
                    }
                    ?>
                <input type="text" class="form-control" value= "<?php echo $name;?>" id="user_name" name="user_name" required>
            </div>
            <div class="form-group">
                <label for="user_contact">Your Contact</label>
                <input type="text" class="form-control" id="user_contact" value= "<?php echo $contact;?>" name="user_contact" required>
            </div>

            <!-- Additional Message -->
            <div class="form-group">
                <label for="message">Additional Message (Optional)</label>
                <textarea class="form-control" id="message" name="message" rows="4"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Book Appointment</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
