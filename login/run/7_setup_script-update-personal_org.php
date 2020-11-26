<?php


include_once "../config.php";
include_once "../includes/dbconn.php";
include_once "../module/myClass.php";

$myClass = new myClass;
$db = new DbConn;

$currentYear = $myClass->callYear();
$log = array();
$i = 0;
$personalTable = $currentYear['data']['per_personal'];

printf("\n/*********************************/
/*   อัพเดท หัวหน้า    */
/*                               */
/*********************************/ \n");
printf("\n ใส่ปี และ รอบ การประเมินรอบที่แล้ว \n");
$readYear = readline('Insert Year : ');
$readTerm = readline('Insert Term etc.(1-2) : ');
$tableYear = $readYear . "-" . $readTerm;
$yearById = $myClass->callYearByTableYear($tableYear);
$yearOld = $yearById['data']['table_year'];
$personalTableOld = $yearById['data']['per_personal'];

try {
    $sql = "SELECT `per_cardno`,`pos_no`, `org_id`,`org_name`,`org_id_1`,`org_name1`,`org_id_2`,`org_name2` FROM $personalTableOld  ";
    $stm = $db->conn->prepare($sql);
    $stm->execute();
    $arrPer_cardno = $stm->fetchAll(PDO::FETCH_ASSOC);
} catch (\Exception $e) {
    $err = $e->getMessage();
}

foreach ($arrPer_cardno as $key => $value) {
    try {
        $sqlCheck = "SELECT `pos_no` FROM $personalTable WHERE per_cardno = :per_cardno ";
        $stmCheck = $db->conn->prepare($sqlCheck);
        $stmCheck->bindParam(":per_cardno", $value['per_cardno']);
        $stmCheck->execute();

        $result = $stmCheck->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Exception $e) {
        $err = $e->getMessage();
    }
    if ($stmCheck->rowCount() == 1 && $value['pos_no'] == $result[0]['pos_no']) {
        try {
            $sqlUpdate = "UPDATE $personalTable SET 
                                                     `org_id` = :org_id ,
                                                     `org_name` = :org_name ,
                                                     `org_id_1` = :org_id_1,
                                                     `org_name1` = :org_name1,
                                                     `org_id_2` = :org_id_2,
                                                     `org_name2` = :org_name2
                                                       WHERE `per_cardno` = :per_cardno ";
            $stmUpdate = $db->conn->prepare($sqlUpdate);
            $stmUpdate->bindParam(":org_id", $value['org_id']);
            $stmUpdate->bindParam(":org_name", $value['org_name']);
            $stmUpdate->bindParam(":org_id_1", $value['org_id_1']);
            $stmUpdate->bindParam(":org_name1", $value['org_name1']);
            $stmUpdate->bindParam(":org_id_2", $value['org_id_2']);
            $stmUpdate->bindParam(":org_name2", $value['org_name2']);
            $stmUpdate->bindParam(":per_cardno", $value['per_cardno']);
            $stmUpdate->execute();
            $c = $stmUpdate->rowCount();
            if ($c == 1) {
                array_push($log, $value['per_cardno']);
            }
        } catch (\Exception $e) {
            $err = $e->getMessage();
        }
        $i++;
        printf("%s : %s \n", $i, $value['per_cardno']);
    }
}

print_r($log);
