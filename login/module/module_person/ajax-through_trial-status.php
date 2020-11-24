<?php
include_once "../../config.php";
include_once "../../includes/dbconn.php";
include_once "class-person.php";
include_once "../myClass.php";

$db = new DbConn;
$myClass = new myClass;
$currentYear = $myClass->callYear();

$err = "";
$success = array();

try{
    $sql =  "UPDATE ".$currentYear['data']['per_personal']." SET through_trial = IF(through_trial = 1,2,1) WHERE `per_cardno` = :per_cardno";
    $stm = $db->conn->prepare($sql);
    $stm->bindParam(':per_cardno',$_POST['per_cardno']);
    $stm->execute();

    $sql2 = "SELECT `through_trial` FROM  ".$currentYear['data']['per_personal']." WHERE `per_cardno` = :per_cardno " ;
    $stm2 = $db->conn->prepare($sql2);
    $stm2->bindParam(':per_cardno',$_POST['per_cardno']);
    $stm2->execute();
    $result = $stm2->fetchAll();
    $count = $stm2->rowCount();

    if ($count == 1) {
        $success['success'] = true;   
        $success['result'] = $result[0]['through_trial']; 
    }else{
        $success['success'] = false;
        $success['msg'] = "ไม่พบข้อมูล";
    }    
}catch(Exception $e){
    $err = $e->getMessage();
}
if($err != ""){
    $success['success'] = null;
    $success['msg'] = $err;
}

echo json_encode($success);
