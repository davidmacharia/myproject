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

// SQL query to fetch ownership details
$sale = "SELECT *
         FROM `recentproperties`
         WHERE `userEmail`='$email';";

$allOwnerships = $connect->query($sale);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Recent Properties</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<style>
    body {
        background-color: #f8f9fa;
        font-family: Arial, sans-serif;
    }
    .container {
        margin-top: 50px;
    }
    .table-container {
        box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        padding: 20px;
        background-color: #fff;
        border-radius: 10px;
    }
    .action-btn {
        margin: 5px;
        padding: 5px 10px;
        border-radius: 5px;
        border: none;
        color: #fff;
    }
    .edit-btn { background-color: #007bff; }
    .transfer-btn { background-color: #28a745; }
    .delete-btn { background-color: #dc3545; }
    .hideform {
        text-align: center;
        width: 120px;
    }
    #toggle-actions {
        cursor: pointer;
        background: transparent;
        border: none;
        font-size: 16px;
        color: red;
    }
    .no-properties {
        text-align: center;
        color: #6c757d;
    }
</style>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4">Recent Properties</h2>
    <div class="table-container">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>NO.</th>
                    <th>Title No.</th>
                    <th>Owner Status</th>
                    <th>Location</th>
                    <th>Name</th>
                    <th>Size</th>
                    <th class="hideform">Action <button id="toggle-actions">X</button></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($allOwnerships->num_rows > 0) {
                    $counter = 1; // Counter to display row numbers
                    while ($row = $allOwnerships->fetch_assoc()) {
                        $ownerId = $row['id'];
                        $name = $row['userFirstName'] . ' ' . $row['userLastName'];
                        $location = $row['propertyLocation'];
                        $size = $row['propertySize'];
                        $ownerStatus = $row['ownershipType'];
                        $titledeed = $row['titleDeedNumber'];

                        echo "<tr>
                                <td>$counter</td>
                                <td>$titledeed</td>
                                <td>$ownerStatus</td>
                                <td>$location</td>
                                <td>$name</td>
                                <td>$size</td>
                                <td class='hideform'>
                                    <button class='action-btn edit-btn' onclick='redirectToSearch($ownerId)'>Search</button>
                                    <button class='action-btn transfer-btn' onclick='redirectToTransfer($ownerId)'>Transfer</button>
                                </td>
                              </tr>";
                        $counter++;
                    }
                } else {
                    echo "<tr><td colspan='7' class='no-properties'>No properties found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Redirect to search page
    function redirectToSearch(ownerId) {
        window.location.href = `search.php?ownerId=${ownerId}`;
    }

    // Redirect to transfer page
    function redirectToTransfer(ownerId) {
        window.location.href = `requesttransfer.php?ownerId=${ownerId}`;
    }

    // Toggle visibility of the action buttons
    document.getElementById('toggle-actions').addEventListener('click', function() {
        const actions = document.querySelectorAll('.hideform');
        actions.forEach(action => {
            action.style.display = (action.style.display === 'none') ? 'table-cell' : 'none';
        });
    });
</script>

</body>
</html>
