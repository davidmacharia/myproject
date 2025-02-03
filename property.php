<?php
$serverName="localhost";
$username="root";
$password="";
$db="landregistration";
    $connect=new mysqli($serverName,$username,$password,$db);
    if($connect->error){
        die("Failed to connect to Database".$connect->error);
    }
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to handle redirects safely
ob_start();

// Handle DELETE action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $propertyId = intval($_GET['id']);
    $deleteSQL = "DELETE FROM propertyDetails WHERE parcelId = $propertyId";
    if ($connect->query($deleteSQL) === TRUE) {
        echo "<script>alert('Property deleted successfully.');</script>";
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    } else {
        echo "<script>alert('Error deleting property: " . $connect->error . "');</script>";
    }
}

// Handle EDIT action
if (isset($_POST['update_property'])) {
    $propertyId = intval($_POST['propertyId']);
    $propertyType = $connect->real_escape_string($_POST['PropertyType']);
    $transactionType = $connect->real_escape_string($_POST['TransactionType']);
    $price = floatval($_POST['price']);
    $size = $connect->real_escape_string($_POST['SizeOrValue']);
    $location = $connect->real_escape_string($_POST['LocationOfProperty']);
    $titleDeed = $connect->real_escape_string($_POST['TitleDeedNumber']);
    $TypeOfOwnership=$connect->real_escape_string($_POST['typeOfOwnership']);
    $updateSQL = "UPDATE propertyDetails SET 
        PropertyType = '$propertyType', 
        TransactionType = '$transactionType', 
        price = $price, 
        SizeOrValue = '$size', 
        LocationOfProperty = '$location', 
        TypeOfOwnership='$TypeOfOwnership',
        titleDeedNumber = '$titleDeed'
        WHERE parcelId= '$propertyId'";

    if ($connect->query($updateSQL) === TRUE) {
        echo "<script>alert('Property updated successfully.');</script>";
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    } else {
        echo "<script>alert('Error updating property: " . $connect->error . "');</script>";
    }
}

// Handle ADD action
if (isset($_POST['add_property'])) {
    $propertyType = $connect->real_escape_string($_POST['PropertyType']);
    $transactionType = $connect->real_escape_string($_POST['TransactionType']);
    $price = floatval($_POST['price']);
    $size = $connect->real_escape_string($_POST['SizeOrValue']);
    $location = $connect->real_escape_string($_POST['LocationOfProperty']);
    $titleDeed = $connect->real_escape_string($_POST['TitleDeedNumber']);
    $TypeOfOwnership=$connect->real_escape_string($_POST['typeOfOwnership']);
    $addSQL = "INSERT INTO propertyDetails (ownerId,PropertyType, TransactionType, price, SizeOrValue,
     LocationOfProperty, titleDeedNumber,TypeOfOwnership,parcelstatus) 
               VALUES (1,'$propertyType', '$transactionType', $price, '$size', '$location', '$titleDeed','$TypeOfOwnership')";
    if ($connect->query($addSQL) === TRUE) {
        echo "<script>alert('Property added successfully.');</script>";
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    } else {
        echo "<script>alert('Error adding property: " . $connect->error . "');</script>";
    }
}


// Handle Search
$searchTerm = '';
$searchSQL = "SELECT DISTINCT buyerDetails.* ,propertyDetails.*,transferlog.*
                           FROM propertyDetails
                           LEFT JOIN buyerDetails ON propertyDetails.ownerId=buyerDetails.ownerId
                           LEFT JOIN transferlog ON propertyDetails.ownerId=transferlog.propertyId
                           WHERE propertyDetails.ownerId=buyerDetails.ownerId
              ";

// Add search conditions if search term exists
if (isset($_POST['search']) && !empty($_POST['search'])) {
    $searchTerm = $connect->real_escape_string($_POST['search']);
    $searchSQL .= " WHERE 
                    propertyDetails.PropertyType LIKE '%$searchTerm%' OR 
                    propertyDetails.LocationOfProperty LIKE '%$searchTerm%' OR 
                    propertyDetails.titleDeedNumber LIKE '%$searchTerm%' OR 
                    buyerDetails.FirstName LIKE '%$searchTerm%' OR 
                     propertyDetails.price LIKE '%$searchTerm%' OR 
                     propertyDetails.TransactionType LIKE '%$searchTerm%' OR 
                    propertyDetails.SizeOrValue LIKE '%$searchTerm%' OR 
                    sellerDetails.sFirstName LIKE '%$searchTerm%'";
}

$properties = $connect->query($searchSQL);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDOpG5bdUdujeOZdBiuji1s_Fqf6bsxhkM"></script>
<style>
    /* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    color: #333;
    margin: 0;
    padding: 0;
    line-height: 1.6;
}

h2 {
    color: #333;
    border-bottom: 2px solid #4CAF50;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

/* Header */
header {
    background-color: #4CAF50;
    color: white;
    padding: 20px 10px;
    text-align: center;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
}
header h1 {
    margin: 0;
    font-size: 24px;
}

/* Container */
.container, #dashboard-container {
    width: 95vw;
    max-width: 100%;
    z-index: -1;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
}

/* Buttons */
button, .btn, form button, .search-box button {
    display: inline-block;
    padding: 10px 20px;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
    font-size: 14px;
    text-transform: uppercase;
}
button:hover, .btn:hover, form button:hover, .search-box button:hover {
    background-color: #45a049;
}
button, .btn {
    background-color: #4CAF50;
}
.search-box button, .btn.edit {
    background-color: #3498db;
}
.search-box button:hover, .btn.edit:hover {
    background-color: #2980b9;
}
.btn.delete {
    background-color: #e74c3c;
}
.btn.delete:hover {
    background-color: #c0392b;
}

/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    font-size: 14px;
}
table th, table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
table th {
    background-color: #f4f4f4;
    color: #333;
    text-transform: uppercase;
}
table tr:nth-child(even) {
    background-color: #f9f9f9;
}
table tr:hover {
    background-color: #f1f1f1;
}
table .actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

/* Form Styles */
form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    margin: 20px auto;
}
form input, form select, .search-box input[type="text"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    margin-bottom: 15px;
    box-sizing: border-box;
}
form input:focus, form select:focus, .search-box input[type="text"]:focus {
    border-color: #4CAF50;
    outline: none;
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
}
form label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
    font-size: 14px;
    color: #333;
}
#priceField {
    margin-top: 15px;
}
#landDescription {
    background-color: #f9f9f9;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 15px;
    font-size: 14px;
}
.search-box {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

/* Responsive Design */
@media screen and (max-width: 768px) {
    form input, form select, .search-box input[type="text"] {
        width: 100%;
    }
    .search-box {
        flex-direction: column;
    }
    header h1 {
        font-size: 18px;
    }
    .container {
        padding: 15px;
    }
    table th, table td {
        padding: 8px 5px;
    }
    table .actions {
        flex-direction: column;
        align-items: stretch;
    }
}

@media screen and (max-width: 480px) {
    header {
        padding: 15px 5px;
    }
    header h1 {
        font-size: 16px;
    }
    button, .btn {
        padding: 8px 12px;
        font-size: 12px;
    }
    form input, form select {
        font-size: 12px;
    }
    table th, table td {
        padding: 6px 3px;
    }
}

</style>
<script>
        window.addEventListener('load', () => {
            document.getElementById('searchbutton').style.display = "none";

          

            // Search functionality for filtering properties
            document.getElementById('search').addEventListener('input', filterProperties);

            function filterProperties() {
                const filter = document.getElementById('search').value.toLowerCase();
                const rows = document.querySelectorAll('table tbody tr');
                rows.forEach(row => {
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            }
        });
    </script>

</head>
<body>
<div id="dashboard-container">
<div id="map" style="width: 100%; height: 500px; margin-top: 20px;"></div>
<?php

if (isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'add')) {
    $property = [
        "parcelId" => "",
        "PropertyType" => "",
        "TransactionType" => "",
        "price" => "",
        "SizeOrValue" => "",
        "LocationOfProperty" => "",
        "titleDeedNumber" => ""
    ];

    if ($_GET['action'] == 'edit' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $result = $connect->query("SELECT * FROM propertyDetails WHERE parcelId = '$id';");
        if ($result && $result->num_rows > 0) {
            $property = $result->fetch_assoc();
        }
    }
?>
    <h2><?php echo ($_GET['action'] == 'add') ? 'Add New Property' : 'Edit Property'; ?></h2>
    <form method="POST" action="">
        <input type="hidden" name="propertyId" value="<?php echo $property['parcelId']; ?>">
        
        <label>Property Type:</label>
        <select id="landType" name="PropertyType" onchange="updateOwnershipOptions()">
            <option value="<?php echo $property['PropertyType']; ?>"><?php echo $property['PropertyType'];?></option>
            <option value="publicLand">Public Land</option>
            <option value="privateLand">Private Land</option>
            <option value="communityLand">Community Land</option>
            <option value="agriculturalLand">Agricultural Land</option>
            <option value="forestLand">Forest Land</option>
            <option value="urbanLand">Urban Land</option>
            <option value="industrialLand">Industrial Land</option>
            <option value="recreationalLand">Recreational Land</option>
            <option value="conservationLand">Conservation Land</option>
            <option value="reservedLand">Reserved Land</option>
            <option value="vacantLand">Vacant Land</option>
            <option value="waterfrontLand">Waterfront Land</option>
            <option value="mineralLand">Mineral Land</option>
            <option value="specialUseLand">Special Use Land</option>
            <option value="landHeldInTrust">Land Held in Trust</option>
        </select>

        <div id="landDescription">
            <p>Select a land type to see its description.</p>
        </div>

        <label for="ownershipType">Choose a Type of Ownership:</label>
        <select id="ownershipType" name="typeOfOwnership">
            <option value="<?php echo $property['TypeOfOwnership']; ?>"><?php echo $property['TypeOfOwnership'];?></option>
        </select>

        <label for="transactionType">Choose a type of transaction:</label>
        <select id="transactionType" name="TransactionType" onchange="togglePriceField()">
            <option value="<?php echo $property['TransactionType']; ?>"><?php echo $property['TransactionType']; ?></option>
        </select>

        <div id="priceField" style="display:none;">
            <label for="price">Enter Price (KES):</label>
            <input type="number" id="price" name="price" value="<?php echo $property['price']; ?>" placeholder="Price in KES">
        </div>

        <label>Size or Value:</label>
        <input type="text" name="SizeOrValue" value="<?php echo $property['SizeOrValue']; ?>" required>

        <label>Location:</label>
        <input type="text" name="LocationOfProperty" value="<?php echo $property['LocationOfProperty']; ?>" required>

        <label>Title Deed Number:</label>
        <input type="text" name="TitleDeedNumber" value="<?php echo $property['titleDeedNumber']; ?>" required>

        <button type="submit" name="<?php echo ($_GET['action'] == 'add') ? 'add_property' : 'update_property'; ?>">Save</button>
    </form>

<?php
} else {
?>
    <h2>Property Listings</h2>
    <form id="searchform" method="POST" action="">
        <input type="text" name="search" oninput="filterProperties()" id="search" value="<?php echo $searchTerm; ?>" placeholder="Search by Property Type, Location, Title Deed, etc.">
        <button type="submit" id="searchbutton">Search</button>
    </form>



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
        if ($properties && $properties->num_rows > 0) {
            $count = 1;
            while ($row = $properties->fetch_assoc()) {
                echo "<tr>
                    <td>{$count}</td>
                    <td>{$row['FirstName']} {$row['LastName']}</td>
                    <td>{$row['previousOwnerName']}</td>
                    <td>{$row['PropertyType']}</td>
                    <td>{$row['TransactionType']}</td>
                    <td>{$row['price']}</td>
                    <td>{$row['SizeOrValue']}</td>
                    <td>{$row['LocationOfProperty']}</td>
                    <td>{$row['titleDeedNumber']}</td>
                    
                    <td>
                        <a href='{$_SERVER['PHP_SELF']}?action=edit&id={$row['parcelId']}' class='edit'>Edit</a>
                        
                        <a href='{$_SERVER['PHP_SELF']}?action=delete&id={$row['parcelId']}' class='delete' onclick='return confirm(\"Are you sure?\");'>Delete</a>
                    </td>
                </tr>";
                ?>
                <script>
      // Initialize the map
      let map;

function initMap() {
    // Initialize the map centered at a default location (e.g., Nairobi, Kenya)
    const defaultLocation = { lat: -1.286389, lng: 36.817223 }; // Nairobi
    map = new google.maps.Map(document.getElementById('map'), {
        center: defaultLocation,
        zoom: 8
    });

    // Fetch property data and add markers
    fetchPropertiesAndAddMarkers();
}

function fetchPropertiesAndAddMarkers() {
    // Example property data (replace this with your actual data)
    const properties = [
        {
            id: 1,
            name: "<?php echo $row['FirstName'];?>",
            location: "<?php echo $row['LocationOfProperty'];?>",
            lat: -1.457673,
            lng: <?php echo $row['latitude'];?>
        },
        {
            id: 2,
            name: "Property 2",
            location: "Mombasa",
            lat: -4.043477,
            lng: 39.668206
        }
    ];

    properties.forEach(property => {
        const marker = new google.maps.Marker({
            position: { lat: property.lat, lng: property.lng },
            map,
            title: property.name
        });

        // Add an info window for each marker
        const infoWindow = new google.maps.InfoWindow({
            content: `<h4>${property.name}</h4><p>${property.location}</p>`
        });

        marker.addListener('click', () => {
            infoWindow.open(map, marker);
        });
    });
}

// Initialize the map when the page loads
initMap();
</script>
                <?php
                $count++;
            }?>

            <?php
        } else {
            echo "<tr><td colspan='10'>No properties found.</td></tr>";
        }
        ?>
        
        </tbody>
        <button id="add"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=add" class="add">+ Add Property</a></button>
    </table>
<?php
}
?>
</div>
</body>
</html>

<script>
        const ownershipOptions = {
            residential: ["Freehold", "Leasehold"],
            commercial: ["Freehold", "Leasehold"],
            agricultural: ["Freehold", "Leasehold", "Customary"],
            privateLand: ["Freehold", "Leasehold"],
            communityLand: ["Customary", "Communal", "Leasehold"],
            forestLand: ["Government", "Leasehold"],
            publicLand: ["Government", "Leasehold"]
        };

        // Function to update the ownership options based on selected land type
        function updateOwnershipOptions() {
            const landType = document.getElementById("landType").value;
            const ownershipSelect = document.getElementById("ownershipType");

            // Clear the previous options
            ownershipSelect.innerHTML = +'<option value="">--Select Ownership Type--</option>';

            // Add new options based on the selected land type
            if (landType && ownershipOptions[landType]) {
                const options = ownershipOptions[landType];
                options.forEach(option => {
                    const opt = document.createElement("option");
                    opt.value = option;
                    opt.textContent = option;
                    ownershipSelect.appendChild(opt);
                });
            }
        }
    </script>



<script>
       const landTransactions = {
        publicLand: ["Allocation", "Leasing", "Licensing", "Surrendering/Redistribution"],
        privateLand: ["Sale/Purchase", "Lease", "Gift/Transfer", "Mortgage", "Sub-division"],
        communityLand: ["Registration", "Lease/Sharing", "Transfer to Individual Ownership", "Sale or Leasing to External Parties"],
        agriculturalLand: ["Sale/Purchase", "Lease", "Sharecropping", "Sub-division", "Development Projects"],
        forestLand: ["Leasing", "Concessions", "Conservation Agreements", "Restoration/Excision"],
        urbanLand: ["Sale/Purchase", "Lease", "Zoning Changes", "Development Agreements"],
        industrialLand: ["Sale/Purchase", "Leasing", "Zoning/Rezoning", "Partnerships"],
        recreationalLand: ["Leasing", "Partnerships", "Sale/Purchase"],
        conservationLand: ["Conservation Easements", "Leasing/Management Agreements", "Sale/Purchase"],
        reservedLand: ["Leasing", "Excision/Redistribution", "Licensing"],
        vacantLand: ["Sale/Purchase", "Leasing", "Development Agreements"],
        waterfrontLand: ["Sale/Purchase", "Leasing", "Conservation Agreements"],
        mineralLand: ["Leasing/Concessions", "Sale/Purchase", "Exploration Licenses"],
        specialUseLand: ["Leasing", "Sale/Purchase", "Development Agreements"],
        landHeldInTrust: ["Transfer of Land Rights", "Leasing", "Redistribution"]
    };

    const monetaryTransactions = [
        "Sale/Purchase", "Lease", "Mortgage", "Sub-division", "Sale or Leasing to External Parties", 
        "Development Projects", "Concessions", "Partnerships", "Exploration Licenses"
    ];

    // Event listener to update transaction options based on selected land type
    document.getElementById("landType").addEventListener("change", function() {
        const landType = this.value;
        const transactionSelect = document.getElementById("transactionType");
        
        // Clear previous options
        transactionSelect.innerHTML = '<option value="">--Select Transaction Type--</option>';
        
        if (landType && landTransactions[landType]) {
            const transactions = landTransactions[landType];
            transactions.forEach(transaction => {
                const option = document.createElement("option");
                option.value = transaction;
                option.textContent = transaction;
                transactionSelect.appendChild(option);
            });
        }
        
        // Hide the price input by default
        document.getElementById("priceField").style.display = "none";
    });

    // Event listener to show price input if the transaction type involves money
    document.getElementById("transactionType").addEventListener("change", function() {
        const transactionType = this.value;
        
        // Check if the selected transaction requires a price input
        if (monetaryTransactions.includes(transactionType)) {
            document.getElementById("priceField").style.display = "block";
        } else {
            document.getElementById("priceField").style.display = "none";
        }
    });

</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
    // Call these functions to ensure the options are correct on page load
    updateOwnershipOptions();
    togglePriceField();
});

        function togglePriceField() {
    var transactionType = document.getElementById("transactionType").value;
    var priceField = document.getElementById("priceField");

    if (transactionType && ["Sale/Purchase", "Lease", "Mortgage"].includes(transactionType)) {
        priceField.style.display = "block";  // Show price input if the transaction type involves money
    } else {
        priceField.style.display = "none";  // Hide price field for other transaction types
    }
}

    const landDescriptions = {
        publicLand: "Land owned by the government (national or county) and held in trust for the people of Kenya. Includes National Public Land and County Public Land.",
        privateLand: "Land owned by individuals, groups, or corporate entities. Includes Freehold Land and Leasehold Land.",
        communityLand: "Land held by a specific community under customary law or communal arrangements. Includes Trust Land and Customary Land.",
        agriculturalLand: "Land used for farming, including crop cultivation (Arable Land) and livestock grazing (Pastoral Land).",
        forestLand: "Land covered by forests, either naturally occurring or planted. Managed by the Kenya Forest Service (KFS).",
        urbanLand: "Land in cities and towns, used for residential, commercial, or industrial purposes. Includes Residential, Commercial, and Industrial Land.",
        industrialLand: "Land designated for industrial use such as factories, warehouses, and manufacturing facilities.",
        recreationalLand: "Land used for recreational purposes like parks, sports fields, and leisure centers.",
        conservationLand: "Land set aside for the preservation of wildlife, ecosystems, and biodiversity. Includes Game Reserves and Wetlands.",
        reservedLand: "Land reserved for specific uses, such as military purposes, infrastructure, or cultural heritage sites.",
        vacantLand: "Unoccupied or unused land that may be available for development or agriculture.",
        waterfrontLand: "Land located along rivers, lakes, or oceans, typically designated for tourism, development, or conservation.",
        mineralLand: "Land containing valuable minerals or natural resources, regulated for mining activities.",
        specialUseLand: "Land used for specific purposes like hospitals, schools, and government buildings.",
        landHeldInTrust: "Land held in trust by the government or organizations for individuals or communities, typically for settlement schemes."
    };

    document.getElementById("landType").addEventListener("change", function() {
        const selectedType = this.value;
        const description = landDescriptions[selectedType] || "Please select a valid land type.";
        document.getElementById("landDescription").innerHTML = `<p>${description}</p>`;
    });

    </script>
