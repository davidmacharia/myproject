        <?php
      include("receive.php");
$b="landRegistration";
$serverName="localhost";
$username="root";
$password="";

$link= new mysqli($serverName,$username,$password,$b);
if($link->connect_error){
    die("Failed to connect".$link->connect_error);
}
else{
        
if($_SERVER['REQUEST_METHOD']=='POST'){

 function validate($data){
        $data=htmlspecialchars($data);
        $data=stripslashes($data);
        $data=trim($data);
        return $data;
        }
        
    $role=validate($_POST['account']);
    $mail=validate($_POST['email']);
    $password=md5(validate($_POST['password']));
    session_start();
    $_SESSION['email']=$mail;
    $_SESSION['role']=$role;
$members="SELECT *FROM `Users` where `Email`='$mail' AND `Account`='$role' AND `SecretPin`='$password';";
$response=$link->query($members);
if($response->num_rows>0){
$row=$response->fetch_assoc();
       $permision=$row["Account"];
       $Email=$row["Email"];
       $pin=$row["SecretPin"];
       $username=$row['Username'];
       $_SESSION['username']=$username;
       #SURVEYER BLOCK
       if($role=="surveyer"){
        $notify="SELECT COUNT(*) as count FROM `WelcomeNotification` WHERE `Email`='$Email' AND `Reaction`='unread' ;";
    $notifications=$link->query($notify);
    if($notifications->num_rows>0){
    while($row=$notifications->fetch_assoc()){
    $total=$row["count"];
     }}
        ?>
       
   
    </head>
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surveyor Dashboard</title>
    <!-- Include W3.CSS -->
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <!-- Include Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
    function control(){
    var a=document.getElementById('content');
    var b=document.getElementById('virtual').style;
    document.getElementById('sub').addEventListener('click',()=>{
    a.style.display='block';
    b.display='none';
    a.src='subdivisionAndAmalgamation.html';
    });
    document.getElementById('account').addEventListener('click',()=>{
     a.style.display='block';
         b.display='none';
    a.src='profile.php';
    });
    document.getElementById('appointment').addEventListener('click',()=>{
     a.style.display='block';
         b.display='none';
    a.src='viewappointment.php';
    });
    document.getElementById('home').addEventListener('click',()=>{
    location.href='home.html';
    });
    function entry(){
 document.getElementById('notification').addEventListener('click',()=>{
document.getElementById('not').style.color='green';
 a.style.display='block';
     b.display='none';
a.src='welcomenotification.php';
});}
entry();
    var s=document.getElementById('num');
    var l= s.textContent;
    if(l>0){
    s.style.display='none';
    document.getElementById('not').style.color='red';
}
     document.getElementById('quick').addEventListener('click',()=>{
    document.getElementById('virtual').style.display='block';
    a.style.display='none';
    })
    }
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        #top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
        }

        #top img {
            height: 50px;
            margin-right: auto;
        }

        #sidebar {
            width: 250px;
            background-color: #2e3b4e;
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
        }

        #sidebar a {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            margin: 10px 0;
            border-radius: 5px;
            transition: all 0.3s;
        }

        #sidebar a:hover {
            background-color: #4caf50;
            color: white;
        }

        #virtual {
            margin-left: 250px;
        padding: 20px;
        margin-top: 15vh;
        flex-grow: 1;
        }

        .dashboard-card {
            background: #fff;
            padding:10px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            flex: 1 1 calc(33.33% - 20px);
            text-align: center;
    }
        

        iframe {
            margin-top: 20px;
            width: 100%;
            height: 500px;
            border: none;
        }
    </style>
</head>
<body onload="control()">

    <!-- Top Bar -->
    <div id="top" class="w3-green">
        <span><?php echo "Hello $role $username"; ?></span>
        <img src="nyandarualogo.jpg" class="w3-display-topmiddle w3-responsive" alt="Nyandarua Logo">
    </div>

    <!-- Sidebar -->
    <div id="sidebar">
        <a id="quick"><span class="fas fa-eye"></span> Overview</a>
        <a id="properties"><span class="fas fa-map-marker-alt"></span> Plot Details</a>
        <a id="appointment"><span class="fas fa-calendar-alt"></span> Appointment</a>
        <a id="report"><span class="fas fa-file-alt"></span> Survey Reports</a>
        <a id="sub"><span class="fas fa-code-branch"></span> Subdivisions</a>
        <a id="documents"><span class="fas fa-upload"></span> Upload Documents</a>
        <a id="schedule"><span class="fas fa-clock"></span> Meeting Scheduling</a>
        <a id="tools"><span class="fas fa-tools"></span> Survey Tools</a>
        <a id="notifications">
            <span class="fas fa-bell"></span> Notifications 
            <span id="num" class="w3-badge w3-red"><?php echo $total; ?></span>
        </a>
        <a id="account"><span class="fas fa-user"></span> Account</a>
        <a id="home"><span class="fas fa-sign-out-alt"></span> Logout</a>
    </div>

    <!-- Main Content -->
    <div id="virtual">
        <!-- Recent Activities -->
        <div id="content1" class="dashboard-card w3-padding">
            <h5>Recent Activities</h5>
            <hr>
            <p><strong>Reports Submitted:</strong> 15</p>
            <p><strong>Meetings Scheduled:</strong> 3</p>
        </div>

        <!-- Projects -->
        <div id="content2" class="dashboard-card w3-padding">
            <h5>Projects</h5>
            <hr>
            <p><strong>Completed:</strong> 10</p>
            <p><strong>Ongoing:</strong> 5</p>
        </div>

        <!-- Documents -->
        <div id="content3" class="dashboard-card w3-padding">
            <h5>Documents</h5>
            <hr>
            <p><strong>Uploaded:</strong> 20</p>
            <p><strong>Approved:</strong> 18</p>
            <p><strong>Pending:</strong> 2</p>
        </div>

        <!-- Survey Tools -->
        <div id="content4" class="dashboard-card w3-padding">
            <h5>Survey Tools</h5>
            <hr>
            <p><strong>GPS Devices:</strong> 4 Available</p>
            <p><strong>Total Stations:</strong> 2 In Use</p>
            <p><strong>Drones:</strong> 1 Ready for Deployment</p>
        </div>

        <!-- Notifications -->
        <div id="content5" class="dashboard-card w3-padding">
            <h5>Notifications</h5>
            <hr>
            <ul>
                <li>Plot Survey Approved for Client A</li>
                <li>Meeting Scheduled with Client B</li>
                <li>New Survey Report Submitted by Team C</li>
            </ul>
        </div>

        <!-- Key Metrics -->
        <div id="content6" class="dashboard-card w3-padding">
            <h5>Key Metrics</h5>
            <hr>
            <p><strong>Total Surveys Conducted:</strong> 50</p>
            <p><strong>Successful Registrations:</strong> 45</p>
            <p><strong>Feedback Ratings:</strong> 4.8/5</p>
        </div>
    </div>

    <!-- Content Frame -->
    <iframe src="" id="content" class="w3-stretch"></iframe>

</body>
</html>

<?php }
    if($role=="admin"){
        $notify="SELECT COUNT(*) as count FROM `WelcomeNotification` WHERE `Email`='$Email' AND `Reaction`='unread' ;";
        $notifications=$link->query($notify);
        if($notifications->num_rows>0){
        while($row=$notifications->fetch_assoc()){
           $totalnotification=$row["count"];
        
        }}
        $total="SELECT COUNT(*) as count FROM `propertyDetails`;";
$number=$link->query($total);
if($number->num_rows>0){
while($row=$number->fetch_assoc()){
   $totalProperties=$row["count"];

}
}
$total="SELECT COUNT(*) as count FROM `buyerDetails`;";
$number=$link->query($total);
if($number->num_rows>0){
while($row=$number->fetch_assoc()){
   $totalCurrentOwners=$row["count"];

}
}
$members="SELECT COUNT(*) as count FROM `Users`;";
$users=$link->query($members);
if($users->num_rows>0){
while($row=$users->fetch_assoc()){
   $totalUsers=$row["count"];

}
}
$members="SELECT COUNT(*) as count FROM `appointments`;";
$users=$link->query($members);
if($users->num_rows>0){
while($row=$users->fetch_assoc()){
   $appointments=$row["count"];

}
}// Query to count total feedback entries
$query = "SELECT COUNT(*) AS total_feedback FROM `feedback`";
$result = $link->query($query);

if ($result) {
    $row = $result->fetch_assoc();
    $feed= $row['total_feedback'];
} else {
    $feed=$connect->error;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Admin Dashboard</title>


<style>
    body {
        display: flex;
        min-height: 100vh;
        font-family: Arial, sans-serif;
        margin: 0;
        overflow: auto;
    }
    footer { text-align: center; padding: 10px; background-color: #2e8b57; color: white; font-size: 12px; position: fixed; bottom: 0; width: 100%; }

    #hamburger {
        display: none;
        cursor: pointer;
        font-size: 24px;
        position: absolute;
        top: 20px;
        left: 20px;
    }

    #sidebar {
        width: 250px;
        background-color: #333;
        color: white;
        padding-top: 70px;
        display:block;
        position: fixed;
        height: 100%;
        transition: all 0.3s ease;
        overflow-y: auto;
    }

    #sidebar a {
        padding: 15px;
        text-decoration: none;
        color: #ddd;
        display: block;
        font-size: 16px;
        transition: 0.3s;
    }

    #sidebar a:hover {
        background-color: #575757;
        color: white;
    }

    #top {
        background-color: #4CAF50;
        color: white;
        padding: 1px;
        text-align: center;
        width: 100%;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
    }

    #main-content {
        margin-left: 250px;
        padding: 20px;
        margin-top: 10vh;
        flex-grow: 1;
    }

    .stat-card {
        background: #fff;
            padding:5px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            flex: 1 1 calc(19% - 5px);
            text-align: center;
        
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-card h5 {
        color: #333;
        font-size: 1.2rem;
    }

    .stat-card h2 {
        color: #4CAF50;
        font-size: 2.5rem;
    }

    #virtual {
        display: flex;
            flex-wrap: wrap;
            background-color: red;
            gap: 10px;
        padding:15px;
       position: fixed;
        margin-top:-5vh;
        flex-grow: 1;
    }

    iframe {
        width: 100%;
        height: 600px;
        border: none;
        margin-top: 20px;
        display: none;
    }

    @media screen and (max-width:768px) {
        #sidebar {
            display: none;
        }

        #hamburger {
            display: block;
        }

        #main-content {
            margin-left: 0;
            padding: 0;
        }
        #virtual{
            position: absolute;
            margin-left: 0;
        }
        .stat-card {
            min-width: 100%;
            flex: 1;
        }
    }
</style>

<script>
function control() {
    const hamburger = document.getElementById('hamburger');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('virtual');
    const iframe = document.getElementById('content');
    const statCards = document.querySelectorAll('.stat-card');
    
    const links = {
        search: 'search.php',
        account: 'profile.php',
        property: 'property.php',
        content4: 'property.php',
        content1: 'members.php',
        appointment: 'viewappointment.php',
        content2:'viewappointment.php',
        content5:'landowners.php',
        analytics: 'analysis.php',
        feedback: 'feedback.php',
        content6: 'feedback.php',
        transaction: 'transaction.php',
        recentActivities: 'recentactivities.php',
        content7: 'recentactivities.php',
        transfer: 'viewtransfer.php',
        content3: 'viewtransfer.php',
        content16: 'managedocuments.php',
        content17: 'leases.php',
        notification: 'welcomenotification.php'
    };
//back to home page;
document.getElementById('home').addEventListener('click',()=>{
        location.href='home.html';
    });
    let isSidebarOpen = false;
    
    hamburger.addEventListener('click', () => {
        if (isSidebarOpen) {
            
            sidebar.style.width = '0';
            content.style.marginLeft = '0';
            isSidebarOpen = false;
        } else {
            document.getElementById('quick').addEventListener('click',()=>{
        sidebar.style.width = '0';
        content.style.marginLeft='0';
    });
            sidebar.style.display = 'block';
            sidebar.style.width = '250px';
            content.style.marginLeft = '250px';
            isSidebarOpen = true;
        }
    });

    Object.keys(links).forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('click', () => {
                iframe.src = links[id];
                iframe.style.display = 'block';
                let isSidebarOpen = false;

function checkScreenWidth() {
    // Check if the screen width is <= 768px
    if (window.innerWidth <= 768) {
        
        // For mobile: Always display sidebar, hide hamburger button
        sidebar.style.display = 'none';
        hamburger.style.display = 'block';
       
    } else {
        // For larger screens: Set sidebar to be visible initially
        sidebar.style.display = 'block';
        hamburger.style.display = 'none';
    }
}

// Call checkScreenWidth initially
checkScreenWidth();
                content.style.display = 'none';
            });
        }
    });

    document.getElementById('quick').addEventListener('click', () => {
        content.style.display = 'flex';
        iframe.style.display = 'none';
    });

    const notificationElement = document.getElementById('not');
    const notificationCount = parseInt(document.getElementById('num').textContent) || 0;
    if (notificationCount > 0) {
        notificationElement.style.color = 'red';
    } else if(notificationCount == 0) {
        document.getElementById('num').style.display = "none";
    }
}

window.onload = control;

</script>
</head>
<body>

    <div id="top">
        <div id="hamburger">☰</div>
        <?php echo "Hello, $role $username"; ?>
        <img src='nyandarualogo.jpg' class='w3-right' alt='Logo' width='50' height='50'>
    </div>

    <div id='sidebar'>
        <a id='quick'><i class='fas fa-eye'></i> Overview</a>
        <a id='property'><i class='bi bi-gear'></i> Properties</a>
        <a id='appointment'><i class='fa fa-calendar'></i> Appointment</a>
        <a id='analytics'><i class='fas fa-chart-line'></i> Analytics</a>
        <a id='transaction'><i class='fas fa-file-alt'></i> Transactions</a>
        <a id='feedback'><i class='fas fa-comments'></i> Feedback</a>
        <a id='recentActivities'><i class='fa fa-history'></i> Recent Activities</a>
        <a id='transfer'><i class='fa fa-exchange'></i> Transfers</a>
        <a id='notification'><i id='not' class='fas fa-bell'></i> Notification <span id='num'><?php echo $totalnotification; ?></span></a>
        <a id='account'><i class='bi bi-person'></i> Account</a>
        <a id='home'><i class='glyphicon glyphicon-log-out'></i> Logout</a>
    </div>

    <div id='main-content'>
        <div id='virtual'>
            <div id='content1' class='stat-card'>
                <h5>Registered Members</h5>
                <i class='fas fa-user-tie fa-2x'></i>
                <h2><?php echo $totalUsers; ?></h2>
            </div>
            <div id='content2' class='stat-card'>
                <h5>Appointments</h5>
                <i class='fa fa-calendar fa-2x'></i>
                <h2><?php echo $appointments; ?></h2>
            </div>
            <div id='content3' class='stat-card'>
                <h5>Transfers</h5>
                <i class='fa fa-exchange fa-2x'></i>
                <h2><?php // Query to count total transfers
$totalQuery = "SELECT COUNT(*) AS total_transfers FROM transferrequests;";
$totalResult = $link->query($totalQuery);
$totalTransfers = $totalResult->fetch_assoc()['total_transfers'];
echo htmlspecialchars($totalTransfers);
?></h2>
            </div>
            <div id='content4' class='stat-card'>
                <h5>Registered Properties</h5>
                <i class='fas fa-building fa-2x'></i>
                <h2><?php echo $totalProperties; ?></h2>
            </div>
            
            <div id='content5' class='stat-card'>
                <h5>Land owners</h5>
                <i class='fas fa-user-tie fa-2x'></i>
                <h2><?php 
                $co="SELECT COUNT(*) as count FROM `buyerDetails`;";
                $commer=$link->query($co);
                if($commer->num_rows>0){
                while($row=$commer->fetch_assoc()){
                   echo $row["count"]; }}
                ?></h2>
            </div>
            <div id='content16' class='stat-card'>
                <h5>Manage Documents</h5>
                <i class='fas fa-comments fa-2x'></i>
                <h2><?php echo $feed; ?></h2>
            </div>
            <div id='content17' class='stat-card'>
                <h5>Land Leases</h5>
                <i class='fas fa-comments fa-2x'></i>
                <h2><?php echo $feed; ?></h2>
            </div>
            <div id='content6' class='stat-card'>
                <h5>Feedback</h5>
                <i class='fas fa-comments fa-2x'></i>
                <h2><?php echo $feed; ?></h2>
            </div>
            <div id='content7' class='stat-card'>
                <h5>Recent Activities</h5>
                <i class='fa fa-history fa-2x'></i>
                <h2><?php echo "not updated"; ?></h2>
            </div>
        </div>
        <iframe id='content'></iframe>
    </div>
    <footer>&copy; 2024 Land Registration System. All Rights Reserved.</footer>
</body>
</html>

<?php
    }
       if($role=="landOwner"){

if (!isset($_SESSION['email'])) {
    die("Error: User is not logged in.");
}
$email = $_SESSION['email'];

        $notify="SELECT COUNT(*) as count FROM `WelcomeNotification` WHERE `Email`='$Email' AND `Reaction`='unread' ;";
$notifications=$link->query($notify);
if($notifications->num_rows>0){
while($row=$notifications->fetch_assoc()){
    $totalNotifications=$row["count"];

}}
// Query to count appointments
$countQuery = "SELECT COUNT(*) AS appointmentCount
               FROM appointments 
               JOIN Users ON appointments.survey_id = Users.userId 
               WHERE appointments.user_contact = (SELECT Contact FROM Users WHERE Email = '$email')";

$countResult = $link->query($countQuery);

if ($countResult && $row = $countResult->fetch_assoc()) {
    $appoint= $row['appointmentCount'];
} else {
    echo "Error: " ;
}
// Query 1: Count of buyer properties
$wealth = "SELECT COUNT(*) as count FROM `buyerDetails` WHERE `Email` = '$Email';";
$number = $link->query($wealth);

if ($number) { // Check if query execution is successful
    $row = $number->fetch_assoc(); // Fetch the single row returned by COUNT
    $Properties = $row['count'] ?? 0; // Assign the count or 0 if not set
} else {
    $Properties = 0; // Default to 0 if the query failed
    error_log("Query failed: " . $link->error); // Log the error for debugging
}

// Query 2: Pending transfer requests
$transfer = "SELECT COUNT(*) AS count
FROM sellerDetails
LEFT JOIN propertyDetails ON propertyDetails.propertyId = sellerDetails.sellerId
LEFT JOIN transferrequests ON transferrequests.id = sellerDetails.sellerId
LEFT JOIN buyerDetails ON buyerDetails.ownerId = sellerDetails.sellerId
WHERE buyerDetails.Email ='$email' AND transferrequests.statecondition = 'Pending';";
$result = $link->query($transfer);

if ($result) {
    $row = $result->fetch_assoc();
    $pendingTransfers = $row['count'] ?? 0;
} else {
    $pendingTransfers = 0;
    error_log("Query failed: " . $link->error);
}

// Query 3: Recent properties
$tra = "SELECT COUNT(*) as count FROM `recentproperties` WHERE `userEmail` = '$email';";
$res = $link->query($tra);

if ($res) {
    $row = $res->fetch_assoc();
    $recentProperties = $row['count'] ?? 0;
} else {
    $recentProperties = 0;
    error_log("Query failed: " . $link->error);
}

// Query 4: Approved transfer requests
$transfer1 =  "SELECT COUNT(*) AS count
FROM sellerDetails
LEFT JOIN propertyDetails ON propertyDetails.propertyId = sellerDetails.sellerId
LEFT JOIN transferrequests ON transferrequests.id = sellerDetails.sellerId
LEFT JOIN buyerDetails ON buyerDetails.ownerId = sellerDetails.sellerId
WHERE buyerDetails.Email ='$email' AND transferrequests.statecondition` = 'approved';";
$result1 = $link->query($transfer1);

if ($result1) {
    $row = $result1->fetch_assoc();
    $approvedTransfers = $row['count'] ?? 0;
} else {
    $approvedTransfers = 0;
    error_log("Query failed: " . $link->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Land Registration System</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            height: 100vh;
            display:flex;
            margin:0;
        }
        #top {
            padding: 5px;
            background-color: #28a745;
            color: #fff;
            width:100%;
            position:absolute;
            top:0;
            z-index: 1;
            justify-content: space-between;
            align-items: center;
        }
        #name{
            text-align: center
            !important
        }
        #top img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
        }
        #sidebar {
        width: 250px;
        background-color: #333;
        color: white;
        padding-top: 15vh;
        position: absolute;
        height: 100%;
        overflow-y: auto;
        }
        #sidebar a {
            display: block;
            padding: 15px 20px;
            color: #ddd;
            text-decoration: none;
            font-size: 16px;
        }
        #not{
            color: red;
        }
        #sidebar a:hover {
            background-color: #007bff;
            color: #fff;
        }
        #sidebar a .fas, #sidebar a .bi {
            margin-right: 10px;
        }
        #content {
            margin-left: 250px;
        padding: 20px;
        margin-top: 15vh;
        flex-grow: 1;
        }
        #overview-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        .card {
            background: #fff;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            flex: 1 1 calc(20% - 10px);
            text-align: center;
        }
        .card h5 {
            margin-bottom: 10px;
        }
        @media screen and (max-width:768px) {
            #sidebar {
            display:none;
            }
            button{
                display: flex;
                flex-wrap: wrap;
                gap:20px;
                margin: 10px;
            }
            .card {
            background: #fff;
            padding:10px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            flex: 1 1 calc(25% - 10px);
            text-align: center;
        }
            #overview-cards{
                display:flex;
                margin-left: 0;
                flex-wrap:wrap;
            gap:15px;
            
            }
            #hamburger {
        display: block
        !important
    }

           #content{
            margin-left:1px;
            padding:10px;
            margin-right: 5px;
           } 
           #top {
            padding: 2px;
            background-color: #28a745;
            color: #fff;
            width:100%;
            height:10vh;
            position:absolute;
            top:0;
            z-index: 1;
        }
        #top img {
            width: 50px;
            height: 50px;
            position: absolute;
            margin-left: 85vw;
            top: 1vh;
            border-radius: 50%;
        }
        }
       
        .card h2 {
            font-size: 36px;
            color: #28a745;
        }
        .notification-badge {
            background-color: red;
            color: white;
            padding: 5px 10px;
            border-radius: 50%;
            font-size: 12px;
            right: 20px;
        }
        iframe {
            width: 100%;
            height: calc(100vh - 150px);
            border: none;
        }
        #hamburger{
            display:none;
            font-size: 24px;
            cursor: pointer
        }
    </style>
<script>
function control() {
    const hamburger = document.getElementById('hamburger');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const overviewCards = document.getElementById('overview-cards');

    // Initial state
    let isSidebarOpen = false;

    // Toggle sidebar on hamburger click
    hamburger.addEventListener('click', () => {
        if (isSidebarOpen) {
            // Close sidebar
            sidebar.style.display = "none";
            content.style.marginRight = "1px";
            overviewCards.style.marginRight ="0";
            hamburger.textContent = "☰";
            hamburger.style.color="";
            hamburger.style.backgroundColor="";
            isSidebarOpen = false;
        } else {
            // Open sidebar
            sidebar.style.display = "block";
            content.style.marginRight = "";
            overviewCards.style.marginRight ="0";
            hamburger.textContent = "X";
            hamburger.style.color="red";
            hamburger.style.backgroundColor="white";
            isSidebarOpen = true;
        }
    });

   
    // References to elements
    const overviewCardsStyle = document.getElementById('overview-cards').style;
    const quickActionsStyle = document.getElementById('quick-actions').style;
    const contentFrame = document.getElementById('content-frame');

    // Helper function to load content into the iframe
    function loadContent(page) {
        contentFrame.src = page;
        contentFrame.style.display = 'block';
        overviewCardsStyle.display = "none";
        quickActionsStyle.display = "none"; // Hide quick actions when loading content
    }

    // Event Listeners for Sidebar Links
    document.getElementById('account').addEventListener('click', () => {
        loadContent('profile.php');
    });

    document.getElementById('properties').addEventListener('click', () => {
        loadContent('assets.php');
    });
    document.getElementById('ptransfer').addEventListener('click', () => {
        loadContent('transfer.php');
    });
    document.getElementById('atransfer').addEventListener('click', () => {
        loadContent('approvedtransfer.php');
    });
    document.getElementById('property').addEventListener('click', () => {
        loadContent('assets.php');
    });

    document.getElementById('appointment').addEventListener('click', () => {
        loadContent('appointment.php');
    });

    document.getElementById('search').addEventListener('click', () => {
        loadContent('search.php');
    });
    document.getElementById('documents').addEventListener('click', () => {
        loadContent('documents.php');
    });
    document.getElementById('notification').addEventListener('click', () => {
        loadContent('welcomenotification.php');
    });
    document.getElementById('newappointments').addEventListener('click', () => {
        loadContent('newappointments.php');
    });
    document.getElementById('notifications').addEventListener('click', () => {
        loadContent('welcomenotification.php');
    });
    document.getElementById('transfers').addEventListener('click', () => {
        loadContent('alltransfers.php');
    });
    document.getElementById('recent').addEventListener('click', () => {
        loadContent('recentproperties.php');
    });

    document.getElementById('home').addEventListener('click', () => {
        window.location.href = 'home.html';
    });

    // Restore layout when "Dashboard Overview" is clicked
    document.getElementById('quick').addEventListener('click', () => {
        overviewCardsStyle.display = "flex";
        quickActionsStyle.display = "block";
        contentFrame.style.display = "none";
    });

    // Add click listeners for quick actions buttons
    document.querySelectorAll('#quick-actions button').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.stopPropagation(); // Prevent interference from other listeners
            const page = button.getAttribute('data-page');
            if (page) {
                loadContent(page);
            }
        });
    });
    var s=document.getElementById('num');
    var l= s.textContent;
    if(l==0){
    document.getElementById('not').style.display='none';
}
}

// Ensure everything is initialized properly after the window loads
window.onload = control;

// Ensure no overlapping elements block quick actions
document.getElementById('quick-actions').style.zIndex = '10';
document.getElementById('content-frame').style.zIndex = '1';
</script>

</head>
<body>
    <div id="top">
    <div id="hamburger">☰</div>
          <div id="name" ><?php echo "Hello, $role $username"; ?></div> 
           <img src="nyandarualogo.jpg" alt="Logo">
         
           
    </div>
      <div id="sidebar">
        <a id="quick"><i class="fas fa-tachometer-alt"></i> Dashboard Overview</a>
        <a id="account"><i class="bi bi-person"></i> My Account</a>
        <a id="properties"><i class="bi bi-gear"></i> My Properties</a>
        <a id="appointment"><i class="fa fa-calendar"></i> Appointments</a>
        <a id="search"><i class="fa fa-search"></i> Search Records</a>
        <a id="notification"><i  class="fas fa-bell"></i> Notifications 
        <span id="not" class="notification-badge"><span id="num" ><?php echo $totalNotifications; ?></span></span></a>
        <a id="home"><i class="glyphicon glyphicon-log-out"></i> Logout</a>
       </div>
    <div id="content">
       <div id="overview-cards">
        <!-- Registered Properties Card -->
        <div id="property" class="card">
            <h5>Registered Properties</h5>
            <h2><?php echo $Properties; ?></h2>
        </div>

        <!-- Pending Transfers Card -->
        <div id="ptransfer" class="card">
            <h5>Pending Transfers</h5>
            <h2><?php echo $pendingTransfers;?></h2> <!-- Example count -->
        </div>

        <!-- Approved Transfers Card -->

        <div class="card" id="atransfer">
            <h5>Approved Transfers</h5>
            <h2><?php echo  $approvedTransfers;?></h2> <!-- Example count -->
        </div>

        <!-- Pending Documents Verification Card -->
        <div class="card" id="documents">
            <h5>Pending Document Verifications</h5>
            <h2>3</h2> <!-- Example count -->
        </div>

        <!-- New Notifications Card -->
        <div id="notifications" class="card">
            <h5>New Notifications</h5>
            <h2><?php echo $totalNotifications; ?></h2> <!-- Display number of notifications -->
        </div>

        <!-- Upcoming Appointments Card -->
        <div class="card" id="newappointments">
            <h5>Upcoming Appointments</h5>
            <h2><?php echo $appoint;?></h2> <!-- Example count -->
        </div>

        <!-- Transfer Requests Card -->
        <div class="card" id="transfers">
            <h5>Transfer Requests</h5>
            <h2><?php
// Query to count total transfers for a specific email with 'Pending' statecondition
$countQuery = "
    SELECT COUNT(*) AS totalCount
    FROM buyerDetails
    LEFT JOIN propertyDetails ON buyerDetails.ownerId = propertyDetails.ownerId
    LEFT JOIN transferrequests ON propertyDetails.parcelId = transferrequests.id 
    LEFT JOIN sellerDetails ON propertyDetails.ownerId = sellerDetails.sellerId
    WHERE buyerDetails.Email = ? AND transferrequests.statecondition = 'Pending';
";

// Prepare the statement
$stmt = $link->prepare($countQuery);
if (!$stmt) {
    die("Prepare statement failed: " . $link->error);
}

// Bind parameters
$stmt->bind_param("s", $email);

// Execute the statement
$stmt->execute();

// Fetch the result
$result = $stmt->get_result();
if ($result) {
    $row = $result->fetch_assoc();
    echo $row['totalCount'];
} else {
    echo "0";
}

// Close the statement and connection if necessary
$stmt->close();
?>
</h2> <!-- Example count -->
        </div>

        <!-- Payment Status Card -->
        <div class="card">
            <h5>Pending Payments</h5>
            <h2>1</h2> <!-- Example count -->
        </div>

        <!-- Recently Added Properties Card -->
        <div class="card" id="recent">
            <h5>Recently Added Properties</h5>
            <h2><?php echo  $recentProperties;?></h2> <!-- Example count -->
        </div>
    </div>

    <!-- Actions and Quick Links Section -->
    <div id="quick-actions">
    <h4>Quick Actions</h4>
    <div class="action-buttons">
        <button data-page="initiateregistration.php" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Register New Property
        </button>
        <button data-page="requesttransfer.php" class="btn btn-primary">
            <i class="fas fa-exchange-alt"></i> Request Property Transfer
        </button>
        <button data-page="appointment.php" class="btn btn-info">
            <i class="fas fa-calendar-check"></i> Schedule Appointment
        </button>
        <button data-page="payment.php" class="btn btn-warning">
            <i class="fas fa-money-bill-alt"></i> Make Payment
        </button>
    </div>
</div>

    <!-- Iframe for dynamic content -->
    <iframe id="content-frame"></iframe>
</div>

</body>
</html>

        <?php
    }
   

}


else{
    ?><html>
    <head>
    <title>Result for land Search</title>
    <script>
    function search(){
    setTimeout(()=>{
    
        document.getElementById('container').style.display='block';
        document.getElementById('loading').style.display='none';
    },1000);
     setTimeout(()=>{
    location.href='register.php';
    },4000)
}
    </script>
 <link rel='stylesheet' href='https://www.w3schools.com/w3css/4/w3.css' />
    <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css'>
      <link rel='stylesheet' href='general.css'>
    <style>
        #container {
            width: 30vw;
            text-align:center;
            padding:10px;
        }
            </style>
    </head>
    <body onload='search()'>
        <div id='loading'>
        <div id='loader'></div>
    <p>Please wait....</p>
    </div>
    <div id='container'>
    <h2 style='color:green'>Sorry!</h2>
    Email/password is incorect</br>
    Create new account
    </br><img style='width:10vh;height:10vh;border-radius:50% ;margin-top:2%' src='fail.png'>
    </div> </body></html>";
 <?php   
}
}

}



?>