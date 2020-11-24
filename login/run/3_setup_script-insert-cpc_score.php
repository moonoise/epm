<?php
include_once "../config.php";
include_once "../includes/dbconn.php";
include_once "../module/cpc/class-cpc.php";
include_once "../module/myClass.php";

$cpc = new cpc;
$db = new DbConn;
$myClass = new myClass;
$currentYear = $myClass->callYear();
$per_personal = $currentYear['data']['per_personal'];
$cpcScoreTable  = $currentYear['data']['cpc_score'];
$year = $currentYear['data']['table_year'];

printf("\n/*********************************/
/*   อัพเดท นำเข้า สมรรถนะ    */
/*                               */
/*********************************/ \n");
printf("\n ใส่ปี และ รอบ การประเมินรอบที่แล้ว \n");
$readYear = readline('Insert Year Old : ');
$readTerm = readline('Insert Term Old etc.(1-2) : ');
$tableYear = $readYear . "-" . $readTerm;

$yearById = $myClass->callYearByTableYear($tableYear);
print_r($tableYear);
$yearOld = $yearById['data']['table_year'];
$cpcScoreTableOld = $yearById['data']['cpc_score'];

$sqlPer = "SELECT * FROM $per_personal ";
$stmPer = $db->conn->prepare($sqlPer);
$stmPer->execute();
$resultPer = $stmPer->fetchAll();
$ok = array();
$log = array();
$log2 = array();

foreach ($resultPer as $key => $v) {

    $err = '';
    $success = array();

    $d = date("Y-m-d H:i:s");
    $setDataSoftDelete = array(
        "soft_delete" => 1,
        "id_admin" => 'moonoise',
        "date_key_score" => $d,
        "per_cardno" => $v['per_cardno'],
        "years" => $year
    );

    $softDelete = $cpc->cpcScoreSoftDeleteByPer_cardno($setDataSoftDelete, $cpcScoreTable);

    if ($softDelete['success'] == true) {

        $r = $cpc->cpcScoreGetDefault($v['per_cardno'], $v['pl_code'], $v['level_no']);

        if (!empty($r['result'])) {
            foreach ($r['result'] as $keyR => $value) {
                $setData = array(
                    "question_no" => $value['question_no'],
                    "per_cardno" => $value['per_cardno'],
                    "id_admin" => 'moonoise',
                    "years" => $year,
                    "cpc_divisor" => $value['cpc_divisor'],
                    "date_key_score" => $d,
                    "soft_delete" => 0
                );

                $result =  $cpc->cpcScoreSet($setData, $cpcScoreTable);
            }
            array_push($ok, $v['per_cardno']);
        } else {
            array_push($log, $v['per_cardno']);
        }
    }

    printf("%s : %s \r ", $key + 1, $v['per_cardno']);
}

foreach ($log as $key => $value) {

    try {
        $sql = "SELECT `cpc_score_id`,
                        `question_no`,
                        `per_cardno`,
                        `id_admin` ,
                        `cpc_divisor` ,
                         CONCAT('$yearOld') as `years` ,
                         CONCAT('moonoise') as `id_admin` ,
                         CONCAT(NOW()) as `date_key_score`    
                         FROM $cpcScoreTableOld 
                         WHERE `per_cardno` = :per_cardno";
        $stm = $db->conn->prepare($sql);
        $stm->bindParam(":per_cardno", $value);
        $stm->execute();
        $result = $stm->fetchAll(PDO::FETCH_ASSOC);
        $count = $stm->rowCount();
    } catch (\Exception $e) {
        $err = $e->getMessage();
    }
    if ($count > 0) {
        $myClass->pdoMultiInsert($cpcScoreTable, $result);
    } else {
        array_push($log2, $value);
    }
}

print_r($log);
print_r($log2);
