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

// Handling actions on appointments
if (isset($_POST['action'])) {
    $appointment_id = $_POST['appointment_id'];
    $action = $_POST['action'];

    if ($action == 'complete') {
        $query = "UPDATE appointments SET statecondation='completed' WHERE appointment_id='$appointment_id'";
        $connect->query($query);
    } elseif ($action == 'cancel') {
        $query = "UPDATE appointments SET statecondation='cancelled' WHERE appointment_id='$appointment_id'";
        $connect->query($query);
    } elseif ($action == 'edit') {
        // Redirect to edit page (Assuming you have an edit page for appointments)
        header("Location: edit_appointment.php?id=$appointment_id");
        exit();
    }
}

// SQL query to fetch all appointments with surveyor details
$query = "SELECT appointments.*, Users.Username AS SurveyorName 
          FROM appointments 
          JOIN Users ON appointments.survey_id = Users.userId 
          ORDER BY appointments.appointment_time ASC";

$result = $connect->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Appointments | Land Management System</title>
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
        button{
            margin:5px
            !important
        }
        .table thead th {
            background-color: #007BFF;
            color: white;
        }
        .action-btn {
            margin-right: 10px;
        }
    </style>
</head>
<script>
  
        function filterProperties() {
            const filter = document.getElementById('search').value.toLowerCase();
            const rows = document.querySelectorAll('table  tr ');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
<body>

<div class="container">
    <h2 class="text-center">All Appointments</h2>
<input type="text" placeholder="search" id="search" oninput="filterProperties()">
    <?php if ($result->num_rows > 0) { ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Date & Time</th>
                    <th>Contact</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $counter=0; while ($row = $result->fetch_assoc()) {
                    $counter++; ?>
                    <tr>
                    
                       <td><?php echo $counter;?></td>
                        <td><?php echo $row['user_name']; ?></td>
                        <td><?php echo $row['SurveyorName']; ?></td>
                        <td><?php echo date("F j, Y, g:i a", strtotime($row['appointment_time'])); ?></td>
                        <td><?php echo $row['user_contact']; ?></td>
                        <td><?php echo $row['messages']; ?></td>
                        <td>
                            <span class="status 
                                <?php 
                                    if ($row['statecondation'] == 'pending') echo 'status-pending'; 
                                    elseif ($row['statecondation'] == 'completed') echo 'status-completed'; 
                                    else echo 'status-cancelled'; 
                                ?>">
                                <?php echo ucfirst($row['statecondation']); ?>
                            </span>
                        </td>
                        <td>
                            <!-- Action Buttons -->
                            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
                                <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                                <button type="submit" name="action" value="complete" class="btn btn-success btn-sm action-btn">Complete</button>
                            </form>

                            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
                                <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                                <button type="submit" name="action" value="cancel" class="btn btn-danger btn-sm action-btn">Cancel</button>
                            </form>

                            <form style="display:none" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
                                <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                                <button type="submit" name="action" value="edit" class="btn btn-info btn-sm action-btn">Edit</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <div class="alert alert-info text-center">
            No appointments found.
        </div>
    <?php } ?>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
