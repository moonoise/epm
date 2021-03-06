<?php
include_once "../../config.php";
include_once "../../includes/dbconn.php";
include_once "../kpi/class-kpi.php";
include_once "../myClass.php";

if (!empty($_GET['per_cardno'])) 
{
    $kpi = new kpi;
    $myClass = new myClass;
    $currentYear = $myClass->callYear();

    $kpiScoreTable = $currentYear['data']['kpi_score'];
    $kpiComment = $currentYear['data']['kpi_comment'];
    $per_personalTable = $currentYear['data']['per_personal'];
    $year = $currentYear['data']['table_year'];

    $result = $kpi->KpiScoreSelect($_GET['per_cardno'],$kpiScoreTable,$year);

    if ($result['success'] == true) {
    $t = "";
    $n = 1;
    foreach ($result['result'] as $row) {
    $s = $kpi->kpiBtnStatus1($row['kpi_score_id'],$kpiScoreTable);
    if ($s['success'] === true) {
        $msg = 'ยืนยัน';
        $color = 'btn-info';
        $ss = $kpi->kpiBtnStatus2($row['kpi_score_id'],$kpiScoreTable);
        if ($ss['success'] === true) {
            $msg = 'ยืนยันแล้ว';
            $color = 'btn-success';
            
        }
    }elseif ($s['success'] === false) {
            $msg = 'กำลังประเมิน';
            $color = 'btn-default';
    }
    echo  "<tr>";
    echo  "<td class='text-center text-info'>".$row['kpi_code_org']."</td>";
    echo  "<td class='text-success'>".$row['kpi_title']."</td>";
    echo  "<td class='text-danger text-center'>".$row['weight']."</td>";
    echo  "<td class='text-center text-danger'>".$kpiType[$row['kpi_type']]."</td>";
    echo "<td>
    <a href='#' class='btn ".$color." btn-xs confirm-add' data-toggle='confirmation'  id='CK-".$row['kpi_score_id']."'
    onclick='kpiEva(`".$_GET['per_cardno']."`,`".$row['kpi_code']."`,`".$row['kpi_score_id']."`,`".$row['kpi_type2']."`)' >
   ".$msg." </a>
    </td>";
    echo "</tr>";
        }
    }

}else {
    echo "Error  per_cardno not found.";
}
