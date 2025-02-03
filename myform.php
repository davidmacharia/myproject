<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Land Registration Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f3f3;
            margin: 0;
            background-image: url("background.jpg");
            background-size: cover;
            background-repeat: no-repeat;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        #form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 80%;
            overflow-y: auto;
        }
        h2 {
            text-align: center;
            color: #4CAF50;
        }
        fieldset {
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
            padding: 20px;
        }
        legend {
            font-weight: bold;
            color: #4CAF50;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="email"], input[type="number"], input[type="tel"], input[type="file"], select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .dynamic-price {
            display: none; /* Hide price by default */
        }
    </style>
    
</script>

</head>
<body>
    <div id="form-container">
        <form action="enter.php" method="post" enctype="multipart/form-data">
            <h2>Land Registration Form</h2>

            <!-- Buyer's Details -->
            <fieldset>
                <legend>Buyer's Details</legend>
                <label for="buyerFirstName">First Name:</label>
                <input type="text" id="buyerFirstName" name="buyerFirstName" required>

                <label for="buyerLastName">Last Name:</label>
                <input type="text" id="buyerLastName" name="buyerLastName" required>

                <label for="buyerContact">Contact:</label>
                <input type="tel" id="buyerContact" name="buyerContact" required>

                <label for="buyerEmail">Email:</label>
                <input type="email" id="buyerEmail" name="buyerEmail" required>

                <label for="buyerIDno">Buyer ID No.:</label>
                <input type="number" id="buyerIDno" name="buyerIDno" required>

                <label for="buyerKraPIN">KRA PIN:</label>
                <input type="text" id="buyerKraPIN" name="buyerKraPIN" required>

                <label for="buyerColouredPassport">Buyer Coloured Passport:</label>
                <input type="file" id="buyerColouredPassport" name="buyerColouredPassport" required>
            </fieldset>

            <!-- Seller's Details -->
            <fieldset>
                <legend>Seller's Details</legend>
                <label for="sellerFirstName">First Name:</label>
                <input type="text" id="sellerFirstName" name="sellerFirstName" required>

                <label for="sellerLastName">Last Name:</label>
                <input type="text" id="sellerLastName" name="sellerLastName" required>

                <label for="sellerContact">Contact:</label>
                <input type="tel" id="sellerContact" name="sellerContact" required>

                <label for="sellerEmail">Email:</label>
                <input type="email" id="sellerEmail" name="sellerEmail" required>

                <label for="sellerIDno">Seller ID No.:</label>
                <input type="number" id="sellerIDno" name="sellerIDno" required>

                <label for="sellerKraPIN">KRA PIN:</label>
                <input type="text" id="sellerKraPIN" name="sellerKraPIN" required>

                <label for="sellerColouredPassport">Seller Coloured Passport:</label>
                <input type="file" id="sellerColouredPassport" name="sellerColouredPassport" required>
            </fieldset>

            <!-- Property Details -->
            <fieldset>
                <legend>Property Details</legend>
               
                <label for="landType">Choose a type of land:</label>
    <select id="landType" name="propertyType"  onchange="updateOwnershipOptions()">
    <option value="">--Select Type of Land--</option>
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

                <label for="transactionType">Choose a type of transaction:</label>
    <select id="transactionType" name="typeOfTransaction" onchange="togglePriceField()">
        <option value="">--Select Transaction Type--</option>
    </select>
    <div id="priceField" style="display:none;">
        <label for="price">Enter Price (KES):</label>
        <input type="number" id="price" name="price" placeholder="Price in KES">
    </div>

    <label for="ownershipType">Choose a Type of Ownership:</label>
    <select id="ownershipType" name="typeOfOwnership">
        <option value="">--Select Ownership Type--</option>
    </select>

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
            ownershipSelect.innerHTML = '<option value="">--Select Ownership Type--</option>';

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

                <label for="valueOfPropety">Size/Value of Property (in Acres):</label>
                <input type="text" id="valueOfPropety" name="valueOfPropety" required>

                <label for="locationOfPropety">Location of Property:</label>
                <input type="text" id="locationOfPropety" name="locationOfPropety" required>


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


                <label for="titleDeedNumber">Title Deed Number:</label>
                <input type="text" id="titleDeedNumber" name="titleDeedNumber" required>
            </fieldset>

            <!-- Document Uploads -->
            <fieldset>
                <legend>Proof of Ownership</legend>
                <label for="titleDeed">Title Deed:</label>
                <input type="file" id="titleDeed" name="titleDeed" required>

                <label for="agreement">Sale Agreement:</label>
                <input type="file" id="agreement" name="agreement" required>

                <label for="originaTitleDeed">Previous Deed/Transfer Documents:</label>
                <input type="file" id="originaTitleDeed" name="originaTitleDeed" required>

                <label for="PaymentReceipt">Payment Receipt:</label>
                <input type="file" id="PaymentReceipt" name="PaymentReceipt" required>

                <label for="encumbranceCertificate">Encumbrance Certificate:</label>
                <input type="file" id="encumbranceCertificate" name="encumbranceCertificate" required>

                <label for="ClearanceCert">Clearance Cert:</label>
                <input type="file" id="ClearanceCert" name="ClearanceCert" required>

            </fieldset>

            <button type="submit">Submit</button>
        </form>
    </div>

    <script>
        function togglePriceField() {
            var transactionType = document.getElementById("typeOfTransaction").value;
            var priceField = document.getElementById("priceField");
            if (transactionType === "Sale") {
                priceField.style.display = "block"; // Show price input if Sale is selected
            } else {
                priceField.style.display = "none"; // Hide price input for other transaction types
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
</body>
</html>
