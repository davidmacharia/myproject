<?php
$serverName = "localhost";
$username = "root";
$password = "";
$db = "landRegistration";

// Create a connection to MySQL database
$connect = new mysqli($serverName, $username, $password, $db);

// Check connection
if ($connect->connect_error) {
    die("Failed to connect: " . $connecting->connect_error);
}
// Initialize success and error messages
$success_message = "";
$error_message = "";

// Handle the feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $feedback = $_POST['feedback'];

    // Simple validation
    if (empty($name) || empty($email) || empty($feedback)) {
        $error_message = "All fields are required!";
    } else {
        // Insert the feedback into the feedback table
        $query = "INSERT INTO `feedback` (`name`, `email`, `feedback`) 
                  VALUES ('$name', '$email', '$feedback')";

        if ($connect->query($query) === TRUE) {
            $success_message = "Feedback submitted successfully!";
        } else {
            $error_message = "Error: " . $connect->error;
        }
    }
}

// Handle deleting feedback
if (isset($_GET['delete'])) {
    $feedback_id = $_GET['delete'];
    $query = "DELETE FROM feedback WHERE id = $feedback_id";

    if ($connect->query($query) === TRUE) {
        $success_message = "Feedback deleted successfully!";
    } else {
        $error_message = "Error: " . $connect->error;
    }
}

// Handle marking feedback as reviewed
if (isset($_GET['reviewed'])) {
    $feedback_id = $_GET['reviewed'];
    $query = "UPDATE feedback SET reviewed = 1 WHERE id = $feedback_id";

    if ($connect->query($query) === TRUE) {
        $success_message = "Feedback marked as reviewed!";
    } else {
        $error_message = "Error: " . $connect->error;
    }
}

// Fetch all feedback from the database
$query = "SELECT * FROM feedback ORDER BY id DESC"; 
$result = $connect->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - Land Registration System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 1000px;
            padding: 30px;
        }

        .feedback-header {
            text-align: center;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 20px;
        }

        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 15px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        td {
            background-color: #f9f9f9;
        }

        .feedback-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }

        .feedback-container input,
        .feedback-container textarea,
        .feedback-container button {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }

        .feedback-container textarea {
            resize: vertical;
            min-height: 120px;
        }

        .feedback-container button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .feedback-container button:hover {
            background-color: #0056b3;
        }

        .error-message, .success-message {
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .error-message {
            color: red;
        }

        .success-message {
            color: green;
        }

        .no-feedback {
            text-align: center;
            font-size: 1.2rem;
            color: #777;
        }
    </style>
</head>
<body>

    <div class="container">

        <!-- Display success or error messages -->
        <?php if ($error_message) { ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php } ?>

        <?php if ($success_message) { ?>
            <div class="success-message">
                <?php echo $success_message; ?>
            </div>
        <?php } ?>

        <!-- Display feedback table -->
        <div class="table-container">
            <h2>All Feedback</h2>

            <?php if ($result->num_rows > 0) { ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Feedback</th>
                            <th>Date Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['feedback']); ?></td>
                                <td><?php echo date("F j, Y, g:i a", strtotime($row['created_at'])); ?></td>
                                <td>
                                    <!-- Action Buttons -->
                                    <a href="feedback.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this feedback?');">Delete</a> | 
                                    <?php if (!$row['reviewed']) { ?>
                                        <a href="feedback.php?reviewed=<?php echo $row['id']; ?>">Mark as Reviewed</a>
                                    <?php } else { ?>
                                        Reviewed
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class="no-feedback">
                    No feedback available.
                </div>
            <?php } ?>
        </div>

        <!-- Feedback submission form -->
        <div class="feedback-container">
            <h2>We Value Your Feedback</h2>
            <p>Please let us know how we can improve our services.</p>

            <form action="feedback.php" method="POST">
                <label for="name">Your Name:</label>
                <input type="text" name="name" id="name" placeholder="Your Name" required>

                <label for="email">Your Email:</label>
                <input type="email" name="email" id="email" placeholder="Your Email" required>

                <label for="feedback">Your Feedback:</label>
                <textarea name="feedback" id="feedback" placeholder="Your feedback" required></textarea>

                <button type="submit">Submit Feedback</button>
            </form>
        </div>
    </div>

</body>
</html>
