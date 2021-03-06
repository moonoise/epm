<?php
include_once "../../config.php";
include_once "../../includes/dbconn.php";
include_once "../myClass.php";

if(!empty($_GET['per_cardno']))
{   $err = '';
    $success = array();
    try
    {
        $db = new DbConn;
        $myClass = new myClass;
        $currentYear = $myClass->callYear();
        $personalTable = $currentYear['data']['per_personal'];

        $sql = "UPDATE $personalTable SET head = null WHERE per_cardno = :per_cardno";

        $stm = $db->conn->prepare($sql);
        $stm->bindParam(":per_cardno",$_GET['per_cardno']);
        $stm->execute();
        $c = $stm->rowCount();
        
        if ($c == 1) {
            $success['success'] = true;
            $success['msg']  = $c . " record";
        }elseif ($c == 0) {
            $success['success'] = false;
            $success['msg']  = "Not found record.";
        }elseif ($c > 1) {
            $success['success'] = false;
            $success['msg']  = $c . " record";
        }
    }catch(Exception $e)
    {
        $err = $e->getMessage();
    }
    
    if ($err != '') {
        $success['success'] = null;
        $success['msg']  = $err;
    }
}

echo json_encode($success);