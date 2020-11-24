<?php
include_once "../../config.php";
include_once "../../includes/ociConn.php";
include_once "../../includes/dbconn.php";
include_once "class-dpis.php";
include_once "../myClass.php";
include_once "../report/class-report.php";

$dpis = new dpis;
$myClass = new myClass;
$db = new DbConn;
$ociDB = new ociConn;
$report = new report;
$ociConn = $ociDB->ociConnect();

$currentYear = $myClass->callYear();
$log = array();
$personalTable = $currentYear['data']['per_personal'];

// $yearById = $myClass->callYearByID(9);
$id = array();
$moveCode = array();
$throughTrial = array();
$sqlID = array();
$i = 0;
$tt = null;
try {
    $sql = "SELECT `per_cardno` FROM $personalTable ";

    $stm = $db->conn->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $err = $e->getMessage();
}



foreach ($result as $key => $value) {
    $sqlOracle = "SELECT PER_CARDNO FROM PER_ACTINGHIS WHERE MOV_CODE = 10211 AND PER_CARDNO = :per_cardno ";
    $stid = oci_parse($ociConn, $sqlOracle);
    oci_bind_by_name($stid, ":per_cardno", $value['per_cardno']);
    oci_execute($stid);
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        array_push($moveCode, $row);
        try {
            $sqlUpdate = "UPDATE $personalTable
                        SET `mov_code` = :mov_code 
                         WHERE per_cardno = :per_cardno ";

            $stm = $db->conn->prepare($sqlUpdate);
            $stm->bindValue(":mov_code", 10211);
            $stm->bindParam(":per_cardno", $value['per_cardno']);
            $stm->execute();
        } catch (\Exception $e) {
            $err = $e->getMessage();
        }
    }


    $t = $report->throughTrial($value['per_cardno'], '2019-10-01', $personalTable);
    // $tt = ($t["result"] === true ? 1 : 2 );
    if ($t["result"] === true) {
        $tt = 1;
    } elseif ($t["result"] === false) {
        $tt = 2;
        array_push($throughTrial, $value['per_cardno']);
    } else {
        $tt = 1;
    }

    try {
        $sqlTrial = "UPDATE $personalTable
                     SET `through_trial` = :through_trial
                      WHERE per_cardno = :per_cardno ";

        $stmT = $db->conn->prepare($sqlTrial);
        $stmT->bindParam(":through_trial", $tt);
        $stmT->bindParam(":per_cardno", $value['per_cardno']);
        $stmT->execute();
    } catch (\Exception $e) {
        $err = $e->getMessage();
    }

    $i++;
    printf(" %s : %s  -> %s \r", $i, $value['per_cardno'], $tt);
}
print_r($throughTrial);
