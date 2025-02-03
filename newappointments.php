<?php
session_start();
$serverName = "localhost";
$username = "root";
$password = "";
$db = "landregistration";

$connect = new mysqli($serverName, $username, $password, $db);

if ($connect->connect_error) {
    die("Failed to connect to Database: " . $connect->connect_error);
}

$email = $_SESSION['email'];

// Query to count appointments
$countQuery = "SELECT COUNT(*) AS appointmentCount
               FROM appointments 
               JOIN Users ON appointments.survey_id = Users.userId 
               WHERE appointments.user_contact = (SELECT Contact FROM Users WHERE Email = '$email')";

$countResult = $connect->query($countQuery);

if ($countResult && $row = $countResult->fetch_assoc()) {
    $appoint= $row['appointmentCount'];
} else {
    echo "Error: " . $connect->error;
}


// Fetch upcoming appointments for the logged-in landowner
$query = "SELECT appointments.*, Users.Username AS SurveyorName 
          FROM appointments 
          JOIN Users ON appointments.survey_id = Users.userId 
          WHERE appointments.user_contact = (SELECT Contact FROM Users WHERE Email = '$email')
          ORDER BY appointments.appointment_time ASC";

$result = $connect->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Appointments | Land Management System</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }
        .container {
            margin-top: 30px;
        }
        .card {
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #007BFF;
            color: white;
        }
        .appointment-time {
            font-weight: bold;
            color: #007BFF;
        }
        .status {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .status-pending {
            background-color: #ffc107;
            color: #fff;
        }
        .status-completed {
            background-color: #28a745;
            color: #fff;
        }
        .status-cancelled {
            background-color: #dc3545;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center">Upcoming Appointments<?php echo $appoint;?></h2>

    <?php if ($result->num_rows > 0) { ?>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Surveyor: <?php echo $row['SurveyorName']; ?></h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Date & Time:</strong> <span class="appointment-time">
                                <?php echo date("F j, Y, g:i a", strtotime($row['appointment_time'])); ?>
                            </span></p>
                            <p><strong>Contact:</strong> <?php echo $row['user_contact']; ?></p>
                            <p><strong>Message:</strong> <?php echo $row['messages']; ?></p>
                            <p><strong>Status:</strong> 
                                <span class="status 
                                    <?php 
                                        if ($row['statecondation'] == 'pending') echo 'status-pending'; 
                                        elseif ($row['statecondation'] == 'completed') echo 'status-completed'; 
                                        else echo 'status-cancelled'; 
                                    ?>">
                                    <?php echo ucfirst($row['statecondation']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="alert alert-info text-center">
            You have no upcoming appointments.<?php echo $email;?>
        </div>
    <?php } ?>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
