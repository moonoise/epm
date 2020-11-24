<?php

printf("\n/*********************************/
/*   อัพเดท ความคืบหน้าการประเมิน    */
/*                               */
/*********************************/ \n");

sleep(2);


include_once "../config.php";
include_once "../includes/dbconn.php";
include_once "../module/report/class-report.php";
include_once "../module/myClass.php";

$report = new report;
$myClass = new myClass;
$db = new DbConn;

$currentYear = $myClass->callYear();
$year = $currentYear['data']['table_year'];
$cpcScoreTable = $currentYear['data']['cpc_score'];
$kpiScoreTable = $currentYear['data']['kpi_score'];
$personalTable = $currentYear['data']['per_personal'];
$kpiScoreResultTable = $currentYear['data']['kpi_score_result'];
$cpcScoreResultTable = $currentYear['data']['cpc_score_result'];

$cpcTypeKey = array(1, 2, 3);
$cpcTypeKey2 = array(1, 2, 3, 4, 5, 6);

$msg_error = "";
$log = array();
$log_ = array();

$sqlPersonal = "select per_cardno from $personalTable ";
$stm = $db->conn->prepare($sqlPersonal);
$stm->execute();
$personalResult = $stm->fetchAll(PDO::FETCH_ASSOC);


foreach ($personalResult as $key => $value) {

    $per_cardno = $value['per_cardno'];
    $msg_result = array();
    $msg_result_ = $value['per_cardno'];

    $doneScore = $report->CPCscoreDone($per_cardno, $cpcScoreTable, $year);
    if ($doneScore['result'] === 0) {

        (!empty($per_cardno) ? $cpcResult =  $report->tableCPC($per_cardno, $year, $cpcTypeKey, $personalTable, $cpcScoreTable) : $cpcResult);
        $cpc = $report->reportCPC1($cpcResult);
        $updateResutl = $report->cpcResultUpdate($cpc, $currentYear['data']);

        (!empty($per_cardno) ? $cpcResult2 =  $report->tableCPC($per_cardno, $year, $cpcTypeKey2, $personalTable, $cpcScoreTable) : $cpcResult);
        $r = $report->cal_gap_chart($cpcResult2);
        $gapUpdate = array();
        foreach ($r as $keyGapUpdate => $valueGapUpdate) {

            $gapUpdate[] = $report->gapUpdateByid($valueGapUpdate['pointEqual_OverGap'], $valueGapUpdate['gap_status'], $valueGapUpdate['cpc_score_id'], $cpcScoreTable);
        }
        $log_['updateResutl'] =  $updateResutl;
        $log_['gapUpdate'] = $gapUpdate;
        $msg_result_ .= " สมรรถนะ ประเมินเสร็จแล้ว ";
    } else {
        $report->cpcUpdateScoreResult($cpcScoreResultTable, $per_cardno, NULL, NULL,  NULL,  $year,  NULL);

        $msg_result_ .= "  สมรรถนะ ยังประเมินไม่เสร็จ";
    }

    $kpiDone = $report->KPIscoreDone($per_cardno, $kpiScoreTable, $year);
    if ($kpiDone['success'] == true && $kpiDone['result'] === 0 && $kpiDone['checkaccept'] === 0) {

        (!empty($per_cardno) ? $kpiResult = $report->tableKPI($per_cardno, $year, $personalTable, $kpiScoreTable) : $kpiResult);
        $kpi = $report->reportKPI1($kpiResult);
        $kpiResultUpdate =  $report->kpiResultUpdate($kpi, $currentYear['data']);

        $msg_result_ .= " ตัวชี้วัด ยังประเมินเสร็จแล้ว";
        $log_['kpiResultUpdate'] = $kpiResultUpdate;
    } else {
        $report->KPIupdateScoreResult($kpiScoreResultTable,  $per_cardno,  NULL,  NULL,  $year,  NULL);
        $msg_result_ .= "  ตัวชี้วัด ยังประเมินไม่เสร็จ";
    }

    $log[] = $log_;
    $msg_result[] = $msg_result_;

    printf($key . " " . $msg_result_ . "\n");
}
