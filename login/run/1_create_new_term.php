<?php

include_once "../config.php";
include_once "../includes/dbconn.php";
include_once "../module/report/class-report.php";
include_once "../module/myClass.php";

$report = new report;
$myClass = new myClass;
$db = new DbConn;

printf("\n/*********************************/
/*   อัพเดท ความคืบหน้าการประเมิน    */
/*                               */
/*********************************/ \n");
$readYear = readline('Insert Year : ');
$readTerm = readline('Insert Term etc.(1-2) : ');
$t = $readYear . "_" . $readTerm;

$perPersonalName = "per_personal_" . $t;
$cpcScoreName = "cpc_score_" . $t;
$cpcScoreResultName = "cpc_score_result_" . $t;
$kpiScoreName = "kpi_score_" . $t;
$kpiScoreResultName = "kpi_score_result_" . $t;
$kpiCommentName = "kpi_comment_" . $t;
$idpScoreName = "idp_score_" . $t;

$detail = "รอบการประเมิน ประจำปีงบประมาณ " . ((int)$readYear + 543) . " รอบที่ " . $readTerm;
$detailShort = "ปีงบประมาณ " . ((int)$readYear + 543) . " รอบที่ " . $readTerm;

$startEvaluation = $readYear . "-10-01";
$endEvaluation = $readYear . "-03-31";
$startEvaluation2 = $readYear . "-04-01";
$endEvaluation2 = $readYear . "-09-30";

$tableYear = $readYear . "-" . $readTerm;
$detailStatus = "1";
$useStatus = "1";


include_once "variable_create_table.php";
$viewTable = array();
try {
    $db->conn->beginTransaction();
    $stm1 = $db->conn->prepare($perPersonal);
    $stm1->execute();

    $stm2 = $db->conn->prepare($cpcScore);
    $stm2->execute();

    $stm3 = $db->conn->prepare($cpcScoreResult);
    $stm3->execute();

    $stm4 = $db->conn->prepare($kpiScore);
    $stm4->execute();

    $stm5 = $db->conn->prepare($kpiScoreResult);
    $stm5->execute();

    $stm6 = $db->conn->prepare($kpiComment);
    $stm6->execute();

    $stm7 = $db->conn->prepare($idpScore);
    $stm7->execute();

    $stm8 = $db->conn->prepare("UPDATE table_year SET default_status = NULL ");
    $stm8->execute();

    $stm9 = $db->conn->prepare("INSERT INTO table_year (detail,
                                detail_short,
                                per_personal,
                                cpc_score,
                                cpc_score_result,
                                kpi_score,
                                kpi_score_result,
                                kpi_comment,
                                idp_score,
                                start_evaluation,
                                end_evaluation,
                                start_evaluation_2,
                                end_evaluation_2,
                                table_year,
                                default_status,
                                use_status
                                ) 
                                VALUES 
                                (
                                :detail,
                                :detail_short,
                                :per_personal,
                                :cpc_score,
                                :cpc_score_result,
                                :kpi_score,
                                :kpi_score_result,
                                :kpi_comment,
                                :idp_score,
                                :start_evaluation,
                                :end_evaluation,
                                :start_evaluation_2,
                                :end_evaluation_2,
                                :table_year,
                                :default_status,
                                :use_status
                                )");

    $stm9->bindParam(":detail", $detail);
    $stm9->bindParam(":detail_short", $detailShort);
    $stm9->bindParam(":per_personal", $perPersonalName);
    $stm9->bindParam(":cpc_score", $cpcScoreName);
    $stm9->bindParam(":cpc_score_result", $cpcScoreResultName);
    $stm9->bindParam(":kpi_score", $kpiScoreName);
    $stm9->bindParam(":kpi_score_result", $kpiScoreResultName);
    $stm9->bindParam(":kpi_comment", $kpiCommentName);
    $stm9->bindParam(":idp_score", $idpScoreName);
    $stm9->bindParam(":start_evaluation", $startEvaluation);
    $stm9->bindParam(":end_evaluation", $endEvaluation);
    $stm9->bindParam(":start_evaluation_2", $startEvaluation2);
    $stm9->bindParam(":end_evaluation_2", $endEvaluation2);
    $stm9->bindParam(":table_year", $tableYear);
    $stm9->bindParam(":default_status", $detailStatus);
    $stm9->bindParam(":use_status", $useStatus);
    $stm9->execute();

    $db->conn->commit();

    $viewTable['success'] = "Query OK.";
} catch (\Exception $e) {
    $db->conn->rollBack();
    $viewTable['success'] = $e->getMessage();
}
print_r($viewTable);
