<?php
session_start();
include_once '../../config.php';
include_once '../../includes/dbconn.php';
include_once "class-report.php";
include_once "../myClass.php";


$cpcResult = array("success" => "",
                    "result" => "",
                    "msg" => "");
$cpcTypeKey = array(1,2,3,4,5,6);
$success =  array();
$success['text'] = "";
$report = new report;
$myClass = new myClass;
$currentYear = $myClass->callYear();
$year = $currentYear['data']['table_year'];
$cpcScoreTable = $currentYear['data']['cpc_score'];

(!empty($_POST['per_cardno'])? $cpcResult =  $report->tableCPC($_POST['per_cardno'],$year,$cpcTypeKey,$currentYear['data']['per_personal'],$cpcScoreTable) : $cpcResult);
$r = $report->cal_gap_chart($cpcResult);

foreach ($r as $key => $value) {
    if ($value['result_minus'] < 0) {
        $success['text'] .= "<tr>";
        $success['text'] .= "<td>ปิดจุดอ่อน</td>";
        $success['text'] .= "<td>[".$value['question_code']."] ".$value['question_title']."</td>";
        $success['text'] .= "</tr>";
    }
   
}

