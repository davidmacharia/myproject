<?php
// Include database connection
$serverName = "localhost";
$username = "root";
$password = "";
$db = "landregistration";
$connect = new mysqli($serverName, $username, $password, $db);
if ($connect->error) {
    die("Failed to connect to Database: " . $connect->error);
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get the email from the session
session_start();
$email = $_SESSION['email'];

// Check if email is provided
if (!$email) {
    echo "<h2>Please provide an email address to view the properties owned.</h2>";
    exit();
}

// Fetch properties owned by the user with the given email
$sql = "SELECT DISTINCT buyerDetails.*, propertyDetails.*, transferlog.* 
        FROM propertyDetails 
        LEFT JOIN buyerDetails ON propertyDetails.ownerId = buyerDetails.ownerId 
        LEFT JOIN transferlog ON propertyDetails.ownerId = transferlog.propertyId 
        WHERE buyerDetails.Email = '$email'";

$result = $connect->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Properties</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-KyZXEAg3QhqLMpG8r+Knujsl5/5hb7ur3hxMl0uTT+0+Az7rPjpE6pEjTWHuZMlz" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            width: 90%;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .search-bar {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .search-bar input {
            padding: 10px;
            width: 80%;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .search-bar button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .search-bar button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table th {
            background-color: #f4f4f4;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .actions button {
            padding: 6px 12px;
            font-size: 14px;
            cursor: pointer;
        }
        .view {
            background-color: #2e8b57;
            color: white;
        }
        .view:hover {
            background-color: #4caf50;
        }
        .transfer {
            background-color: #ff9800;
            color: white;
        }
        .transfer:hover {
            background-color: #ff5722;
        }
        .delete {
            background-color: #f44336;
            color: white;
        }
        .delete:hover {
            background-color: #d32f2f;
        }
        @media (max-width: 768px) {
            table th, table td {
                font-size: 12px;
            }
        }
    </style>
    <script>
        function filterProperties() {
            const filter = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
</head>
<body>
    <header>
        <h1>Properties Owned by <?php echo htmlspecialchars($email); ?></h1>
    </header>
    <div class="container">
        <div class="search-bar">
            <input type="text" id="searchInput" onkeyup="filterProperties()" placeholder="Search properties...">
            <button onclick="filterProperties()">Search</button>
        </div>
        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Buyer's Name</th>
                        <th>Seller's Name</th>
                        <th>Land Type</th>
                        <th>Transaction Type</th>
                        <th>Price (KES)</th>
                        <th>Property Size</th>
                        <th>Location</th>
                        <th>Title Deed Number</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;
                    while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><?php echo $row['FirstName'] . ' ' . $row['LastName']; ?></td>
                            <td><?php echo $row['previousOwnerName']; ?></td>
                            <td><?php echo $row['PropertyType']; ?></td>
                            <td><?php echo $row['TransactionType']; ?></td>
                            <td><?php echo $row['price']; ?></td>
                            <td><?php echo $row['SizeOrValue']; ?></td>
                            <td><?php echo $row['LocationOfProperty']; ?></td>
                            <td><?php echo $row['titleDeedNumber']; ?></td>
                            <td class="actions">
                                                                <!-- View Property Details -->
                                                                <form method="GET" action="viewproperty.php" style="display:inline;">
                                    <input type="hidden" name="propertyId" value="<?php echo $row['parcelId']; ?>">
                                    <button type="submit" class="view">View</button>
                                </form>
                                
                                <!-- Request Transfer -->
                                <form method="GET" action="requesttransfer.php" style="display:inline;">
                                    <input type="hidden" name="propertyId" value="<?php echo $row['parcelId']; ?>">
                                    <button type="submit" class="transfer">Request Transfer</button>
                                </form>
                                
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No properties found for the provided email address.</p>
        <?php endif; ?>
    </div>
</body>
</html>
