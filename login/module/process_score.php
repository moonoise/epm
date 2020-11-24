<?php
session_start();
include_once '../config.php';
include_once '../includes/dbconn.php';
include_once "report/class-report.php";
include_once "myClass.php";

$kpiResult = array(
    "success" => "",
    "result" => "",
    "msg" => ""
);
$cpcResult = array(
    "success" => "",
    "result" => "",
    "msg" => ""
);

$cpcTypeKey = array(1, 2, 3);
$cpcTypeKey2 = array(1, 2, 3, 4, 5, 6);
$per_cardno = "1100200878309";

$report = new report;
$myClass = new myClass;


$currentYear = $myClass->callYear();
$idpScoreTable = $currentYear['data']['idp_score'];
$year = $currentYear['data']['table_year'];

(!empty($per_cardno) ? $kpiResult = $report->tableKPI($per_cardno, $year, $currentYear['data']['per_personal'], $currentYear['data']['kpi_score']) : $kpiResult);

$kpi = $report->reportKPI1($kpiResult);
$kpiUpdateResutl =  $report->kpiResultUpdate($kpi, $currentYear['data']);

(!empty($per_cardno) ? $cpcResult =  $report->tableCPC($per_cardno, $year, $cpcTypeKey, $currentYear['data']['per_personal'], $currentYear['data']['cpc_score']) : $cpcResult);
$cpc = $report->reportCPC1($cpcResult);
$updateResutl = $report->cpcResultUpdate($cpc, $currentYear['data']);


(!empty($per_cardno) ? $cpcResult2 =  $report->tableCPC($per_cardno, $year, $cpcTypeKey2, $currentYear['data']['per_personal'], $currentYear['data']['cpc_score']) : $cpcResult);  // cpcTypeKey2 1,2,3,4,5,6
$r = $report->cal_gap_chart($cpcResult2);
$gap = $report->cal_gap($r, $idpScoreTable);
$idp = $report->cal_idp($per_cardno, $year, $idpScoreTable);
$gapUpdate = array();

foreach ($r as $keyGapUpdate => $valueGapUpdate) {

    $gapUpdate[] = $report->gapUpdateByid($valueGapUpdate['pointEqual_OverGap'], $valueGapUpdate['gap_status'], $valueGapUpdate['cpc_score_id'], $currentYear['data']['cpc_score']);
}

echo "<pre>";
print_r($kpiUpdateResutl);
echo "</pre>";

echo "<pre>";
print_r($updateResutl);
echo "</pre>";

echo "<pre>";
print_r($gapUpdate);
echo "</pre>";
