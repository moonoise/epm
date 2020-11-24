<?php


include_once "../config.php";
include_once "../includes/dbconn.php";
include_once "../includes/ociConn.php";
include_once "../module/module_dpis/class-dpis.php";
include_once "../module/myClass.php";

$dpis = new dpis;
$myClass = new myClass;

$currentYear = $myClass->callYear();
$log = array();
$personalTable = $currentYear['data']['per_personal'];


$ociSql = "SELECT
                    per_personal.per_cardno
                    FROM
                        per_personal
                    WHERE
                        per_personal.per_type = 1
                    AND
                        per_personal.per_status = 1
                    ";
$stid = oci_parse($dpis->ociConn, $ociSql);
oci_execute($stid);
oci_fetch_all($stid, $res, null, null, OCI_ASSOC);

oci_free_statement($stid);

foreach ($res['PER_CARDNO'] as $key => $value) {
    $result = $dpis->queryPersonal($value);
    $r = $dpis->insertPer_Personal($result['result'], $personalTable);
    if ($r['success'] === false or $r['success'] === null) {
        $log[] = $r;
    }
    printf("%s :  %s  \n", $key + 1, $r['msg']);
    printf("%s   \n", $value);
    // print_r($value);
}

print_r($log);
