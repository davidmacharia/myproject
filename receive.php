
<?php
$serverName="localhost";
$username="root";
$password="";
$db="landregistration";
$connect=new mysqli($serverName,$username,$password);
if($connect->error){
    die("connection failed".$connect->error);

}
$selectedDb=mysqli_select_db($connect,$db);
if(!$selectedDb){
$sql="CREATE DATABASE `landRegistration`;";
if($connect->query($sql)==TRUE){
    include "recordingscript.php";    
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
     failed to create database <br> $connect->error
    </br><img style='width:10vh;height:10vh;border-radius:50% ;margin-top:2%' src='fail.png'>
    </div> </body></html>"; 
}
}
$connect->close();
?>