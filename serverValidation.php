<?php
if($_SERVER['REQUEST_METHOD']=='POST'){
function validate($data){
    $data=htmlspecialchars($data);
    $data=stripslashes($data);
    $data=trim($data);
    return $data;
    }

if(empty($_POST['titleDeedNumber'])){
    $DeedNumberError="Title Deed Number cannot be empty";
}
else{
    $DeedNumber=validate($_POST['titleDeedNumber']);
    }
 
}
?>