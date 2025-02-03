<?php
include "serverValidation.php";
$serverName="localhost";
$username="root";
$password="";
$db="landregistration";
$connec=new mysqli($serverName,$username,$password);
$selectedDb=mysqli_select_db($connec,$db);
if(!$selectedDb){
    echo  "<html>
    <head>
    <title>Result for land Search</title>
    <script>
    function search(){
    setTimeout(()=>{
    
        document.getElementById('container').style.display='block';
        document.getElementById('loading').style.display='none';
    },1000);
     setTimeout(()=>{
    location.href='myform.html';
    },4000)
}
    </script>
 <link rel='stylesheet' href='https://www.w3schools.com/w3css/4/w3.css' />
    <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css'>
       <link rel='stylesheet' href='general.css'>
       <style>
        #container {
            width: 50vw;
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
    <h1 style='color:green'>Sorry!</h1></br>
    No land/Property have being Registered </br>
    Database not Found
    </br><img style='width:10vh;height:10vh;border-radius:50% ;margin-top:2%' src='fail.png'>
    </div> </body></html>";
}
else{
    $connect=new mysqli($serverName,$username,$password,$db);

if($connect->error){
    die("connection failed".$connect->error);

}
else{
#searching statments; 
 $sql ="SELECT propertyDetails.* ,buyerDetails.*
         FROM propertyDetails 
         LEFT JOIN  buyerDetails ON buyerDetails.ownerId = propertyDetails.ownerId
         WHERE propertyDetails.titleDeedNumber='$DeedNumber';";


 $result=$connect->query($sql);
if($result->num_rows>0){
$row=$result->fetch_assoc();
$OwnerFirstName=validate($row['FirstName']);
$OwnerLastName=validate($row['LastName']);
$buyerContact=$row['Contact'];
$address=$row['Email'];
$title=$row['titleDeedNumber'];
$location=$row['LocationOfProperty'];
$size=$row['SizeOrValue'];
$TransactionType=$row['TransactionType'];
$restriction=$row['PropertyType'];


    $year=date("Y");
    $day=date("d");

    echo
    "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Resulst</title>
    <style>
      button {
            width:15vw;
            padding: 10px;
            background-color: green;
            color: #fff;
            border: none;
            position:absolute;
            border-radius: 4px;
            top:5vh;
            right:0;
            cursor: pointer;
        }
        button:hover {
            background-color: #15de37;
        }
            h6{margin:5px;}
            h5{margin:12px;}
            p{margin:10px;}
body {
    font-family: Arial, sans-serif;
    background-color: #f3f3f3;
    justfy-content:center;
    align-items:center;
    width:700px;
    overflow-x:hidden
    !important
}
    *{font-size:21px;;}
        #container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding:30px;
            width:100%;
            overflow:hidden;
        }
            @media screen and (max-width: 768px) {
            span1{
            margin-right:-20vw;
             }
            h6{margin: 0 !important;}
            *{font-size:small;}
            #container {
            margin-left:3px;
            width:80vw
            !important
        }
            body {
    justify-content:left;
    align-items:left;
}
    }
        #heading{
            text-align: center;
        }
        #container h2 {
            margin-bottom: 20px;
            text-align:left;
        }
                       #label{
 position:absolute;
width:55vw;
top:1%;
padding:0.5%;
padding-top: 0;
z-index: 2;
height:7%;
        }
        span1{
        color:red;
            position:absolute;
            right:1vw;
            top:7%;
             }
        @media print{
        span1{margin-right:-25vw;}
        #pr{
        display:none;
    }
    }
    </style>
    
   
</head>
<body>
    <div id='container'>

    <div id='label'><span><h6>LRA-85</h6></span><span1><h6>[f-85]</h6></span1></div>
           <div id='heading'>
            <h6>REPUBLIC OF KENYA</h6>
            <h6>THE LAND REGISTRATION ACT</h6>
            <h6>THE LAND REGISTRATION (GENERAL) REGULATION,2017</h6>
            <h6>CERTIFICATE OF OFFICIAL SEARCH</h6>
            </br>
            <b>TITLE NO :</b><span>....$title.....</span></br>
            <b>SEARCH NO :</b><span>....$title.....</span>
           </div> 
           </br>
            <p>On the.....$day......day of....";

$month = date('m'); // Current month as a number (01-12)
switch ($month) {
    case '01':
        echo "January";
        break;
    case '02':
        echo "February";
        break;
    case '03':
        echo "March";
        break;
    case '04':
        echo "April";
        break;
    case '05':
        echo "May";
        break;
    case '06':
        echo "June";
        break;
    case '07':
        echo "July";
        break;
    case '08':
        echo "August";
        break;
    case '09':
        echo "September";
        break;
    case '10':
        echo "October";
        break;
    case '11':
        echo "November";
        break;
    case '12':
        echo "December";
        break;
    default:
        echo "Unknown Month";
        break;
}
echo"............$year...............the following were 
                the entries of the above recommended title.</p>
                <h5>Part A -Property Section [approximate etc.]</h5>
                <p>Nature of title.............................$TransactionType........................................</p>
                <p>Approximate area.............$size Acres..................................</p>
                <h5>Part B -Proprietorship Section</h5>
                <p>Name  of Proprietor........$OwnerFirstName ...  $OwnerLastName.........................</p>
                <p>Address...........$address.........................
                <p>Prohibitions,cautions and restrictions...$restriction.........................................................</p>
                <h5>Part C -Encumburances Section (leases,charges,etc,)</h5>
                <p>..........................................................................................</p>
                <p>The following applications are pending:</p>
                <h6>(a)................................................................</h6>
                <h6>(b)................................................................</h6>
                <h6>(c)................................................................</h6>
                <h6>(d)...............................................................</h6>
                <p>The following certified copies are attached as requested:</p>
                <h6>(a)................................................................</h6>
                <h6>(b)................................................................</h6>
                <h6>(c)................................................................</h6>
                <h6>(d)...............................................................</h6>
                <h6>Date..........................................day............................20.....................</h6>
                <p><b>Signed by the Registral</b></p>
                <h6>Name........................................Seal......................</h6>
                <h6>Signature.........................</h6>  
                 
                     <button  id='pr' onclick='window.print()'>Print</button>
        </div>

</body>
</html>
";
    }
   else{
    echo 
    "<html>
    <head>
    <title>Result for land Search</title>
    <script>
    function search(){
    setTimeout(()=>{
    
        document.getElementById('container').style.display='block';
        document.getElementById('loading').style.display='none';
    },1000);
     setTimeout(()=>{
    location.href='search.php';
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
    <h1 style='color:green'>Sorry!</h1>
    $connect->error
    </br>
    No Result found for Title Deed : $DeedNumber </br>
    
    </br><img style='width:10vh;height:10vh;border-radius:50% ;margin-top:2%' src='fail.png'>
    </div> </body></html>";
   } 

}


} 



 ?>