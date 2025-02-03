<?php
// Database connection
$serverName = "localhost";
$username = "root";
$password = "";
$db = "landregistration";

// Create database connection
$connect = new mysqli($serverName, $username, $password, $db);

// Check connection
if ($connect->connect_error) {
    die("Failed to connect to Database: " . $connect->connect_error);
}

// Add Lease
if ($_SERVER['REQUEST_METHOD'] == 'POST' ) {
    $property_id = intval($_POST['property_id']);
    $tenant_id = intval($_POST['ownerId']);
    $start_date = $connect->real_escape_string($_POST['start_date']);
    $end_date = $connect->real_escape_string($_POST['end_date']);
    $amount = floatval($_POST['amount']);
    $leaseTerms = $connect->real_escape_string($_POST['leaseTerms']);

    // Validate dates
    if (strtotime($start_date) >= strtotime($end_date)) {
        echo "<script>alert('Start date must be before end date.');</script>";
    } 
        $addLeaseSQL = "INSERT INTO leases (property_id, tenant_id, start_date, end_date, amount, status) 
                        VALUES ('$property_id', '$tenant_id', '$start_date', '$end_date', '$amount', '$leaseTerms')";

        if ($connect->query($addLeaseSQL) === TRUE) {
            echo "<script>alert('Lease added successfully.'); window.location.href='';</script>";
        } else {
            echo "<script>alert('Error adding lease: " . $connect->error . "');</script>";
        }
    
}

// Update Lease
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_lease'])) {
    $lease_id = intval($_POST['lease_id']);
    $property_id = intval($_POST['property_id']);
    $tenant_id = intval($_POST['tenant_id']);
    $start_date = $connect->real_escape_string($_POST['start_date']);
    $end_date = $connect->real_escape_string($_POST['end_date']);
    $amount = floatval($_POST['amount']);
    $leaseTerms = $connect->real_escape_string($_POST['leaseTerms']);

    // Validate dates
    if (strtotime($start_date) >= strtotime($end_date)) {
        echo "<script>alert('Start date must be before end date.');</script>";
    } else {
        $updateLeaseSQL = "UPDATE leases SET 
                            property_id = '$property_id', 
                            tenant_id = '$tenant_id', 
                            start_date = '$start_date', 
                            end_date = '$end_date', 
                            amount = '$amount', 
                            leaseTerms = '$leaseTerms' 
                            WHERE lease_id = $lease_id";

        if ($connect->query($updateLeaseSQL) === TRUE) {
            echo "<script>alert('Lease updated successfully.'); window.location.href='';</script>";
        } else {
            echo "<script>alert('Error updating lease: " . $connect->error . "');</script>";
        }
    }
}

// Delete Lease
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['lease_id'])) {
    $lease_id = intval($_GET['lease_id']);
    $deleteLeaseSQL = "DELETE FROM leases WHERE lease_id = $lease_id";
    if ($connect->query($deleteLeaseSQL) === TRUE) {
        echo "<script>alert('Lease deleted successfully.');</script>";
    } else {
        echo "<script>alert('Error deleting lease: " . $connect->error . "');</script>";
    }
}

// Fetch properties for selection
function getProperties($connect) {
    $propertiesQuery = "SELECT parcelId, LocationOfProperty, titleDeedNumber FROM propertyDetails";
    return $connect->query($propertiesQuery);
}

// Fetch lessees for selection
function getLessees($connect) {
    $ownersQuery = "SELECT ownerId, FirstName, LastName FROM buyerDetails";
    return $connect->query($ownersQuery);
}

// Display leases
function displayLeases($connect) {
    $leaseQuery = "SELECT leases.*, propertyDetails.LocationOfProperty, buyerDetails.FirstName, buyerDetails.LastName 
                   FROM leases 
                   INNER JOIN propertyDetails ON leases.property_id = propertyDetails.parcelId
                   LEFT JOIN buyerDetails ON leases.tenant_id = buyerDetails.ownerId";
    return $connect->query($leaseQuery);
}

// Fetch lease details for editing
function getLeaseDetails($connect, $lease_id) {
    $leaseDetailsQuery = "SELECT * FROM leases WHERE lease_id = $lease_id";
    return $connect->query($leaseDetailsQuery);
}

// Edit Lease
$leaseDetails = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['lease_id'])) {
    $lease_id = intval($_GET['lease_id']);
    $leaseDetails = getLeaseDetails($connect, $lease_id)->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Land Lease Management</title>
    <style>
        /* Styles here... */
    </style>
</head>
<body>
    <h1>Land Lease Management</h1>

    <!-- Add or Edit Lease Form -->
    
    <form method="POST" action="">
        <h2><?php echo $leaseDetails ? 'Edit Lease' : 'Add Lease'; ?></h2>
        <input type="hidden" name="lease_id" value="<?php echo $leaseDetails['lease_id'] ?? ''; ?>">

        <label for="property_id">Select Property:</label>
        <select name="property_id" required>
            <?php
            $properties = getProperties($connect);
            while ($property = $properties->fetch_assoc()):
                $selected = ($leaseDetails && $leaseDetails['property_id'] == $property['parcelId']) ? 'selected' : '';
            ?>
                <option value="<?php echo $property['parcelId']; ?>" <?php echo $selected; ?>>
                    <?php echo htmlspecialchars($property['LocationOfProperty']); ?> 
                    (<?php echo htmlspecialchars($property['titleDeedNumber']); ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label for="ownerId">Select Lessee:</label>
        <select name="ownerId" required>
            <?php
            $lessees = getLessees($connect);
            while ($lessee = $lessees->fetch_assoc()):
                $selected = ($leaseDetails && $leaseDetails['tenant_id'] == $lessee['ownerId']) ? 'selected' : '';
            ?>
                <option value="<?php echo $lessee['ownerId']; ?>" <?php echo $selected; ?>>
                    <?php echo htmlspecialchars($lessee['FirstName']) . ' ' . htmlspecialchars($lessee['LastName']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="start_date">Lease Start Date:</label>
        <input type="date" name="start_date" required value="<?php echo $leaseDetails['start_date'] ?? ''; ?>">

        <label for="end_date">Lease End Date:</label>
        <input type="date" name="end_date" required value="<?php echo $leaseDetails['end_date'] ?? ''; ?>">

        <label for="amount">Lease Amount:</label>
        <input type="number" name="amount" required step="0.01" value="<?php echo $leaseDetails['amount'] ?? ''; ?>">

        <label for="leaseTerms">Lease Terms:</label>
        <textarea name="leaseTerms" required><?php echo $leaseDetails['leaseTerms'] ?? ''; ?></textarea>

        <button type="submit" name="<?php echo $leaseDetails ? 'update_lease' : 'add_lease'; ?>">
            <?php echo $leaseDetails ? 'Update Lease' : 'AddLease'; ?>
        </button>
    </form>

    <hr>

    <!-- Display Leases Table -->
    <h2>Existing Leases</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Property</th>
                <th>Lessee</th>
                <th>Lease Start Date</th>
                <th>Lease End Date</th>
                <th>Lease Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $leases = displayLeases($connect);
            $count = 1;
            while ($lease = $leases->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo $count++; ?></td>
                    <td><?php echo htmlspecialchars($lease['LocationOfProperty']); ?></td>
                    <td><?php echo htmlspecialchars($lease['FirstName']) . ' ' . htmlspecialchars($lease['LastName']); ?></td>
                    <td><?php echo htmlspecialchars($lease['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($lease['end_date']); ?></td>
                    <td><?php echo number_format($lease['amount'], 2); ?></td>
                    <td class="actions">
                        <a href="?action=edit&lease_id=<?php echo $lease['lease_id']; ?>">Edit</a> | 
                        <a href="?action=delete&lease_id=<?php echo $lease['lease_id']; ?>" onclick="return confirm('Are you sure you want to delete this lease?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
