<?php

include_once "../config.php";
include_once "../includes/dbconn.php";
include_once "../module/myClass.php";

$db = new DbConn;
$myClass = new myClass;
$currentYear = $myClass->callYear();
$per_personal = $currentYear['data']['per_personal'];
$kpiScoreTable = $currentYear['data']['kpi_score'];
$year = $currentYear['data']['table_year'];

printf("\n/*********************************/
/*   อัพเดท นำเข้า KPI    */
/*                               */
/*********************************/ \n");
printf("\n ใส่ปี และ รอบ การประเมินรอบที่แล้ว \n");
$readYear = readline('Insert Year : ');
$readTerm = readline('Insert Term etc.(1-2) : ');
$tableYear = $readYear . "-" . $readTerm;

$yearById = $myClass->callYearByTableYear($tableYear);
$yearOld = $yearById['data']['table_year'];
$kpiScoreTableOld = $yearById['data']['kpi_score'];
$countKpi = 0;

$logError = array();
$logOk = array();
$log = array();

try {
    $sql = "SELECT per_cardno FROM $per_personal ";
    $stm = $db->conn->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll(PDO::FETCH_ASSOC);
} catch (\Exception $e) {
    $err = $e->getMessage();
}

foreach ($result as $key => $value) {
    try {
        $sqlKpi = "SELECT `kpi_code` ,`per_cardno` ,`id_admin` , `weight` , 
                            CONCAT('$year') AS years , 
                            CONCAT(NOW()) AS date_key_score  , 
                            `soft_delete`
                            FROM  $kpiScoreTableOld
                            WHERE per_cardno = :per_cardno AND soft_delete = 0 ";
        $stmKpi = $db->conn->prepare($sqlKpi);
        $stmKpi->bindParam(":per_cardno", $value['per_cardno']);
        $stmKpi->execute();
        $resultKpi =  $stmKpi->fetchAll(PDO::FETCH_ASSOC);
        $countKpi = $stmKpi->rowCount();
    } catch (\Exception $e) {
        $err = $e->getMessage();
    }

    if ($countKpi > 0) {

        // print_r($valueKpi);
        $r = $myClass->pdoMultiInsert($kpiScoreTable, $resultKpi);
        if ($r['success'] == true) {
            array_push($logOk, $value['per_cardno']);
        } else {
            array_push($logError, $value['per_cardno']);
        }
    } else {
        array_push($log, $value['per_cardno']);
    }

    printf("%s : %s  \r", $key + 1, $value['per_cardno']);
}

print_r($logError);
print_r($log);
