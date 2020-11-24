<?php
include_once "../../config.php";
include_once "../../includes/ociConn.php";
include_once "../../includes/dbconn.php";
include_once "class-dpis.php";
if ($_POST['org_id'] != "") {
    $dpis = new dpis;

    $arrPer_cardno = $dpis->queryPer_cardno($_POST['org_id']);

    $dpis->ociClose();

    echo json_encode($arrPer_cardno['result'][0]);
}


// $sqlOrg =  'select * from per_org where org_id_ref = :org_id';
//     $stm = $dpis->conn->prepare($sqlOrg);
//     $result = $stm->execute();