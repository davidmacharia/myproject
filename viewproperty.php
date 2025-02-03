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

// Get the email from the session
session_start();
$email = $_SESSION['email'];

// Check if email is provided
if (!$email) {
    echo "<h2>Please provide an email address to view the properties owned.</h2>";
    exit();
}

// SQL query to fetch properties for the given email
$sql = "SELECT DISTINCT buyerDetails.*, propertyDetails.*, transferlog.* 
        FROM propertyDetails 
        LEFT JOIN buyerDetails ON propertyDetails.ownerId = buyerDetails.ownerId 
        LEFT JOIN transferlog ON propertyDetails.ownerId = transferlog.propertyId 
        WHERE buyerDetails.Email = '$email'";

$result = $connect->query($sql);

// Define constants for each land type and their corresponding transactions
$landTransactions = [
    "publicLand" => ["Allocation", "Leasing", "Licensing", "Surrendering/Redistribution"],
    "privateLand" => ["Sale/Purchase", "Lease", "Gift/Transfer", "Mortgage", "Sub-division"],
    "communityLand" => ["Registration", "Lease/Sharing", "Transfer to Individual Ownership", "Sale or Leasing to External Parties"],
    "agriculturalLand" => ["Sale/Purchase", "Lease", "Sharecropping", "Sub-division", "Development Projects"],
    "forestLand" => ["Leasing", "Concessions", "Conservation Agreements", "Restoration/Excision"],
    "urbanLand" => ["Sale/Purchase", "Lease", "Zoning Changes", "Development Agreements"],
    "industrialLand" => ["Sale/Purchase", "Leasing", "Zoning/Rezoning", "Partnerships"],
    "recreationalLand" => ["Leasing", "Partnerships", "Sale/Purchase"],
    "conservationLand" => ["Conservation Easements", "Leasing/Management Agreements", "Sale/Purchase"],
    "reservedLand" => ["Leasing", "Excision/Redistribution", "Licensing"],
    "vacantLand" => ["Sale/Purchase", "Leasing", "Development Agreements"],
    "waterfrontLand" => ["Sale/Purchase", "Leasing", "Conservation Agreements"],
    "mineralLand" => ["Leasing/Concessions", "Sale/Purchase", "Exploration Licenses"],
    "specialUseLand" => ["Leasing", "Sale/Purchase", "Development Agreements"],
    "landHeldInTrust" => ["Transfer of Land Rights", "Leasing", "Redistribution"]
];

// Tax calculation function
function calculateTax($landType, $transactionType, $price) {
    $taxRate = 0.02; // Default tax rate
    
    // Adjust tax rate based on land type and transaction type
    if ($landType == 'publicLand') {
        if ($transactionType == 'Leasing' || $transactionType == 'Licensing') {
            $taxRate = 0.015;
        }
    } elseif ($landType == 'privateLand') {
        if ($transactionType == 'Sale/Purchase') {
            $taxRate = 0.05;
        }
    } elseif ($landType == 'agriculturalLand') {
        $taxRate = 0.03;
    }

    return $price * $taxRate;
}

// Generate Bill
if (isset($_POST['generateBill'])) {
    $billDetails = [];
    while ($row = $result->fetch_assoc()) {
        // Calculate tax for each property
        $landType = strtolower(str_replace(" ", "", $row['PropertyType'])); // Assuming PropertyType corresponds to the land type
        $transactionType = $row['TransactionType'];
        $price = $row['price']; // Property price from the database
        
        // Calculate the tax amount
        $taxAmount = calculateTax($landType, $transactionType, $price);

        // Store the bill details
        $billDetails[] = [
            'Buyer' => $row['FirstName'] . ' ' . $row['LastName'],
            'title'=>$row['titleDeedNumber'],
            'Seller' => 'Revenue Authority of Kenya',
            'Transaction' => $row['TransactionType'],
            'Price' => number_format($price, 2),
            'Tax' => number_format($taxAmount, 2)
        ];
    }

    // Format the bill as plain text or HTML for download
    $billContent = "Bill for Properties :\n\n";
    foreach ($billDetails as $bill) {
        $billContent .= "Title Deed Number: " . $bill['title'] . "\n";
        $billContent .= "Owner: " . $bill['Buyer'] . "\n";
        $billContent .= "Email: " . $email . "\n";
        $billContent .= "Pay To: " . $bill['Seller'] . "\n";
        $billContent .= "Transaction Type: " . $bill['Transaction'] . "\n";
        $billContent .= "Price (KES): " . $bill['Price'] . "\n";
        $billContent .= "Tax (KES): " . $bill['Tax'] . "\n\n";
    }

    // Set the download header and send the bill content as a .txt file
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="land_transaction_bill.txt"');
    echo $billContent;
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Properties and Tax Calculation</title>
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
        .bill-button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 20px;
        }
        .bill-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <header>
        <h1>TAX STATUS</h1>
    </header>

    <div class="container">
        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Buyer's Name</th>
                        <th>Land Type</th>
                        <th>Transaction Type</th>
                        <th>Price (KES)</th>
                        <th>Property Size</th>
                        <th>Location</th>
                        <th>Title Deed Number</th>
                        <th>Tax Calculation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;
                    while ($row = $result->fetch_assoc()):
                        // Calculate tax for each property
                        $landType = strtolower(str_replace(" ", "", $row['PropertyType'])); // Assuming PropertyType corresponds to the land type
                        $transactionType = $row['TransactionType'];
                        $price = $row['price']; // Property price from the database
                        
                        // Calculate the tax amount
                        $taxAmount = calculateTax($landType, $transactionType, $price);
                    ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><?php echo $row['FirstName'] . ' ' . $row['LastName']; ?></td>
                            <td><?php echo $row['PropertyType']; ?></td>
                            <td><?php echo $row['TransactionType']; ?></td>
                            <td><?php echo number_format($row['price'], 2); ?></td>
                            <td><?php echo $row['SizeOrValue']; ?></td>
                            <td><?php echo $row['LocationOfProperty']; ?></td>
                            <td><?php echo $row['titleDeedNumber']; ?></td>
                            <td><?php echo number_format($taxAmount, 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <form method="POST">
                <button type="submit" name="generateBill" class="bill-button">Generate Bill</button>
            </form>
            
               <!-- Button -->
<button type="button" class="bill-button" onclick="redirectToPayment()">Pay bills</button>

<script>
    // Define the function to redirect
    function redirectToPayment() {
        // Replace this URL with the destination page where you want to redirect
        window.location.href = 'payment.php';
    }
</script>

            
        <?php else: ?>
            <p>No properties found for the provided email address.</p>
        <?php endif; ?>
    </div>
</body>
</html>
