<?php
session_start();
include_once "../../config.php";
include_once "../../includes/dbconn.php";
include_once "../report/class-report.php";

$db = new DbConn;
$report =  new report;
$errYears = "";
$errScore = "";
$grade = array(
    '0' => 0,
    '1' => 0,
    '2' => 0,
    '3' => 0,
    '4' => 0,
    'org_name' => "",
    'login_status_0' => 0,
    'Error' => 0
);

$years_id = $_POST['selectYears'];


try {
    $sqlYear  = "SELECT  * FROM `table_year` WHERE table_id = :selectYears " ;
    $stmYear = $db->conn->prepare($sqlYear);
    $stmYear->bindParam(":selectYears" , $years_id);
    $stmYear->execute();
    $resultYear = $stmYear->fetchAll(PDO::FETCH_ASSOC);
    
} catch (\Exception $e) {
    $errYears = $e->getMessage();
    // echo $errYears;
}

if ($errYears == "") {

    $per_personal = $resultYear[0]['per_personal'];
    $cpc_score = $resultYear[0]['cpc_score'];
    $kpi_score = $resultYear[0]['kpi_score'];

    try {
        $sqlScore = "
        select  * 
        FROM $per_personal 
        LEFT JOIN $cpc_score on $cpc_score.per_cardno = $per_personal.per_cardno 
        LEFT JOIN $kpi_score on $kpi_score.per_cardno = $per_personal.per_cardno
        LEFT JOIN per_org on per_org.org_id = $per_personal.org_id
        WHERE $per_personal.login_status = 1  
        " ;
        if ($orgSelect != '77' ) {
            $sqlScore .= " AND ( $per_personal.org_id = :org_id OR $per_personal.org_id_1 = :org_id OR $per_personal.org_id_2 = :org_id )";
        }

        $stmScore = $db->conn->prepare($sqlScore);
        if ($orgSelect != '77' ) {
            $stmScore->bindParam(":org_id",$orgSelect);
        }
        $stmScore->execute();

        $stmResult = $stmScore->fetchAll(PDO::FETCH_ASSOC);

    } catch (\Exception $e) {
        $errScore = $e->getMessage();
    }

    try {
        if ($orgSelect != '77' ) {
            $sqlPersonalStatusOff = "SELECT count(*) as loginOff FROM $per_personal WHERE login_status = 0  AND ( $per_personal.org_id = :org_id OR $per_personal.org_id_1 = :org_id OR $per_personal.org_id_2 = :org_id ) ";
        }elseif ($orgSelect == '77') {
            $sqlPersonalStatusOff = "SELECT count(*) as loginOff FROM $per_personal WHERE login_status = 0 ";
        }
        
        $stmStatus = $db->conn->prepare($sqlPersonalStatusOff);
        if ($orgSelect != '77' ) {
            $stmStatus->bindParam(":org_id",$orgSelect);

        }
        $stmStatus->execute();
        $resultStatus = $stmStatus->fetchAll(PDO::FETCH_NUM); 
        $grade['login_status_0'] = $resultStatus[0][0];
         
    } catch (\Exception $e) {
        $errResultScore = $e->getMessage();
    }

}

if ($errResultScore == "" && count($stmResult) > 0) {
    if ($orgSelect != '77' ) {
        $grade['org_name'] = $stmResult[0]['org_name'];
    }else {
        $grade['org_name'] = "ทั้งกรม";
    }
    
    $grade['total'] = count($stmResult);

    foreach ($stmResult as $key => $value) {
        $g = $report->cutGrade($value['totalScore']);
        if ($g == "A") {
            $grade[0]++;
        }elseif ($g == "B") {
            $grade[1]++;
        }elseif ($g == "C") {
            $grade[2]++;
        }elseif ($g == "D") {
            $grade[3]++;
        }elseif ($g == "F") {
            $grade[4]++;
        }else {
            $grade["Error"]++;
        }
       
    }
    
}
 
echo json_encode($grade);
// echo var_dump($sqlResultScore) ;
