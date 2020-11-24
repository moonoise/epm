<?php
include_once "../../config.php";
include_once "../../includes/ociConn.php";
include_once "../../includes/dbconn.php";
include_once "class-dpis.php";
if ($_POST['per_cardno'] != "") {
    $dpis = new dpis;
    
    $arrPer_cardno = $dpis->searchPer_cardno($_POST['per_cardno']);

    $dpis->ociClose();

    echo json_encode($arrPer_cardno['result']);
}
