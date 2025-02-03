<?php
$serverName = "localhost";
$username = "root";
$password = "";
$db = "landRegistration";
$table = "buyerDetails";

// Create a connection to MySQL database
$connecting = new mysqli($serverName, $username, $password, $db);

// Check connection
if ($connecting->connect_error) {
    die("Failed to connect: " . $connecting->connect_error);
}

// Check if the table exists
$check = "SHOW TABLES LIKE '$table'";
$result = $connecting->query($check);

if ($result && $result->num_rows == 1) {
    echo "Table '$table' exists.";
} else {
    // SQL queries to create necessary tables
    $sql = "
        CREATE TABLE IF NOT EXISTS `buyerDetails` (
            ownerId INT AUTO_INCREMENT PRIMARY KEY,
            FirstName VARCHAR(100) NOT NULL,
            LastName VARCHAR(100) NOT NULL,
            Email VARCHAR(100) NOT NULL,
            Contact VARCHAR(15) NOT NULL
        );
        CREATE TABLE IF NOT EXISTS `transactions` (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    parcel_id INT NOT NULL,
    owner_id INT NOT NULL,
    payee_id INT NOT NULL,
    transaction_type VARCHAR(50) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'Pending',
    payment_method VARCHAR(50) NOT NULL,
    FOREIGN KEY (parcel_id) REFERENCES propertyDetails(parcelId),
    FOREIGN KEY (owner_id) REFERENCES buyerDetails(ownerId),
    FOREIGN KEY (payee_id) REFERENCES payeeDetails(payeeId)
          
);


        CREATE TABLE IF NOT EXISTS `sellerDetails` (
            sellerId INT AUTO_INCREMENT PRIMARY KEY,
            sFirstName VARCHAR(100) NOT NULL,
            sLastName VARCHAR(100) NOT NULL,
            sEmail VARCHAR(100) NOT NULL,
            sContact VARCHAR(15) NOT NULL
        );

        CREATE TABLE IF NOT EXISTS `propertyDetails` (
            parcelId INT AUTO_INCREMENT PRIMARY KEY,
            ownerId INT  NOT NULL,
            titleDeedNumber VARCHAR(50) NOT NULL UNIQUE,
            PropertyType VARCHAR(100) NOT NULL,
            SizeOrValue VARCHAR(100) NOT NULL,
            price DECIMAL(15, 2) NOT NULL,
            TypeOfOwnership VARCHAR(100) NOT NULL,
            LocationOfProperty VARCHAR(100) NOT NULL,
            TransactionType VARCHAR(20) NOT NULL,
            longitude DECIMAL,
            latitude DECIMAL,
            parcelstatus VARCHAR(255) NOT NULL
        );

        CREATE TABLE IF NOT EXISTS `Documents` (
    documentId INT AUTO_INCREMENT PRIMARY KEY,
    parcelId VARCHAR(50) NOT NULL,
    titleDeed VARCHAR(255) NOT NULL,
    parentDocumentId INT NULL,
    encumbranceCert VARCHAR(255) NOT NULL,
    agreement VARCHAR(255) NOT NULL,
    paymentReceipts VARCHAR(255) NOT NULL,
    clearanceCert VARCHAR(255) NOT NULL,
    issueDate DATETIME,
    expiryDate DATETIME,
    documentStatus ENUM('draft', 'pending', 'approved', 'rejected', 'archived') DEFAULT 'draft',
    version INT NOT NULL DEFAULT 1,
    createdBy INT NOT NULL,
    lastUpdatedBy INT NOT NULL,
    lastUpdatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (createdBy) REFERENCES Users(userId),
    FOREIGN KEY (lastUpdatedBy) REFERENCES Users(userId),
    FOREIGN KEY (parentDocumentId) REFERENCES Documents(documentId)
);
CREATE TABLE IF NOT EXISTS `leases` (
    lease_id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    tenant_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('Active', 'Expired', 'Renewed', 'Terminated') DEFAULT 'Active',
    FOREIGN KEY (property_id) REFERENCES propertyDetails(parcelId),
    FOREIGN KEY (tenant_id) REFERENCES buyerDetails(ownerId)
);



        CREATE TABLE IF NOT EXISTS `WelcomeNotification` (
            NotificationId INT AUTO_INCREMENT PRIMARY KEY,
            Email VARCHAR(100) NOT NULL,
            NotificationType VARCHAR(50) NOT NULL,
            Messages VARCHAR(255) NOT NULL,
            TimeRegistered TIME NOT NULL,
            DateRegistered DATE NOT NULL,
            Reaction VARCHAR(25) DEFAULT 'unread'
        );

        CREATE TABLE IF NOT EXISTS `appointments` (
            appointment_id INT AUTO_INCREMENT PRIMARY KEY,
            survey_id INT NOT NULL,
            user_name VARCHAR(255) NOT NULL,
            user_contact VARCHAR(15) NOT NULL,
            appointment_time TIME NOT NULL,
            messages VARCHAR(255) NOT NULL,
            statecondation VARCHAR(255) NOT NULL
        );

        CREATE TABLE IF NOT EXISTS `recentproperties` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            userFirstName VARCHAR(50),
            userLastName VARCHAR(50),
            userContact VARCHAR(15),
            userEmail VARCHAR(100),
            userID BIGINT,
            propertyType VARCHAR(50),
            propertySize VARCHAR(50),
            propertyLocation VARCHAR(100),
            ownershipType VARCHAR(50),
            transactionType VARCHAR(50),
            titleDeedNumber VARCHAR(100),
            titleDeedPath VARCHAR(255),
            agreementPath VARCHAR(255),
            paymentReceiptPath VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS `feedback` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            feedback TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            reviewed TINYINT(1) DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS `Users` (
            userId INT AUTO_INCREMENT PRIMARY KEY,
            Account VARCHAR(50) NOT NULL,
            Username VARCHAR(100) NOT NULL,
            Email VARCHAR(100) NOT NULL UNIQUE,
            Contact VARCHAR(15) NOT NULL,
            SecretPin VARCHAR(255) NOT NULL
        );

        CREATE TABLE IF NOT EXISTS `UsersProfile` (
            userId INT AUTO_INCREMENT PRIMARY KEY,
            Email VARCHAR(100) NOT NULL UNIQUE,
            img VARCHAR(255) NOT NULL,
            bio VARCHAR(255) NOT NULL
        );

        CREATE TABLE IF NOT EXISTS `transferLog` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            propertyId INT,
            previousOwnerId INT,
            previousOwnerName VARCHAR(255),
            newOwnerId INT,
            transferDate DATETIME
        );
CREATE TABLE IF NOT EXISTS `activity_log` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(255) NOT NULL,      -- The action performed (e.g., 'Exported data', 'Deleted data')
    table_name VARCHAR(255) NOT NULL,   -- The table affected by the action (e.g., 'buyerDetails', 'propertyDetails')
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- The timestamp when the action occurred
    admin_id INT NOT NULL,              -- Admin ID who performed the action
    FOREIGN KEY (admin_id) REFERENCES Users(id) -- Assuming you have a Users table with admin IDs (you can modify based on your system)
);

        CREATE TABLE IF NOT EXISTS `transferRequests` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            newOwnerFirstName VARCHAR(255) NOT NULL,
            newOwnerLastName VARCHAR(255) NOT NULL,
            newOwnerEmail VARCHAR(255) NOT NULL,
            newOwnerContact VARCHAR(15) NOT NULL,
            transferReason TEXT,
            uploadedDocument VARCHAR(255),
            statecondition VARCHAR(255) NOT NULL,
            requestDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";

    if ($connecting->multi_query($sql)) {
        do {
            if ($result = $connecting->store_result()) {
                $result->free();
            }
        } while ($connecting->more_results() && $connecting->next_result());
        echo "Tables created successfully.";
    } else {
        echo "Error: " . $connecting->error;
    }
}

// Close the connection
$connecting->close();
?>
