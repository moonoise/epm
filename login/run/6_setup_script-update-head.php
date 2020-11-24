<?php
printf("\n/*********************************/
/*   อัพเดท ผู้บังคับบัญชา    */
/*                               */
/*********************************/ \n");
include_once "../config.php";
include_once "../includes/dbconn.php";
include_once "../module/myClass.php";

$myClass = new myClass;
$db = new DbConn;

$currentYear = $myClass->callYear();
$log = array();
$i = 0;
$personalTable = $currentYear['data']['per_personal'];

$yearById = $myClass->callYearByID(9);
$personalTableOld = $yearById['data']['per_personal'];

try {
    $sql = "SELECT `per_cardno`, `head` FROM $personalTableOld  ";
    $stm = $db->conn->prepare($sql);
    $stm->execute();
    $arrPer_cardno = $stm->fetchAll(PDO::FETCH_ASSOC);
} catch (\Exception $e) {
    $err = $e->getMessage();
}

foreach ($arrPer_cardno as $key => $value) {
    try {
        $sqlUpdate = "UPDATE $personalTable SET `head` = :head,
                                                      WHERE `per_cardno` = :per_cardno ";
        $stmUpdate = $db->conn->prepare($sqlUpdate);
        $stmUpdate->bindParam(":head", $value['head']);
        $stmUpdate->bindParam(":per_cardno", $value['per_cardno']);
        $stmUpdate->execute();
        $c = $stmUpdate->rowCount();
        if ($c == 0) {
            array_push($log, $value['per_cardno']);
        }
    } catch (\Exception $e) {
        $err = $e->getMessage();
    }
    $i++;
    printf("%s : %s -> %s \n", $i, $value['per_cardno'], $value['head']);
}

print_r($log);
