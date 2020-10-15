<?php
session_start();
include_once "../../config.php";
include_once "../../includes/dbconn.php";
include_once "../../includes/ociConn.php";
include_once "../report/class-report.php";
include_once "../myClass.php";
$err = "";
$dbConn = new DbConn;
$ociDB = new ociConn;
$ociConn = $ociDB->ociConnect();
$myClass = new myClass;

$currentYear = $myClass->callYear();

$per_personalTable = $currentYear['data']['per_personal'];
$year = $currentYear['data']['table_year'];

$id = array();
// $sqlOracle = array();
$moveCode = array();
$sqlID = array();
$i = 0;
try {
    $sql = "SELECT per_cardno FROM " . $per_personalTable;

    $stm = $dbConn->conn->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll(PDO::FETCH_NUM);
} catch (Exception $e) {
    $err = $e->getMessage();
}
foreach ($result as $key => $value) {
    if ($i < 999) {
        array_push($id, $value[0]);
        $i++;
    } else {
        $sqlID[] = "'" . implode("','", $id) . "'";
        $id = array();
        $i = 0;
    }
}
if (count($id) > 0) {
    $sqlID[] = "'" . implode("','", $id) . "'";
}
foreach ($sqlID as $key => $value) {
    $sqlOracle = "SELECT PER_CARDNO FROM PER_ACTINGHIS WHERE MOV_CODE = 10211 AND PER_CARDNO IN (" . $value . ")";
    $stid = oci_parse($ociConn, $sqlOracle);
    oci_execute($stid);
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        array_push($moveCode, $row);
    }
}
$part = $year;
$rr = updateMovCode($moveCode, $per_personalTable);
$rrr = updatethroughTrial($result, '2019-10-01', $per_personalTable);
$rrrr = array_merge($rr['msg'], $rrr['msg']);
echo json_encode($rrrr);

function updateMovCode($moveCode, $per_personalTable)
{
    $err = "";
    $success = array();
    $dbConn = new DbConn;
    $success['msg'] = array();
    $i = 0;
    try {
        $sqlUpdate = "UPDATE " . $per_personalTable . " 
                        SET `mov_code` = :mov_code
                         WHERE per_cardno = :per_cardno ";

        $stm = $dbConn->conn->prepare($sqlUpdate);
        $stm->bindValue(":mov_code", 10211);
        // echo count($moveCode)."<br>";
        foreach ($moveCode as $key => $value) {
            $v = intval($value['PER_CARDNO']);
            $stm->bindParam(":per_cardno", $v);
            $stm->execute();
            $count = $stm->rowCount();

            if ($count == 1) {
                $i = '<span class="text-info" >Update mov_code:: ' . $v . '</span>';
                array_push($success['msg'], $i);
            } else if ($count == 0) {
                $i = '<span class="text-success" >Update mov_code Skip :: ' . $v . '</span>';
                array_push($success['msg'], $i);
            }
            // echo $i."<br>";
        }

        $success['success'] = true;


        // echo $i."<br>";

    } catch (Exception $e) {
        $err = $e->getMessage();
    }
    if ($err != '') {
        $success['success'] = null;
        array_push($success['msg'], $err);
    }
    return $success;
}

function updatethroughTrial($arrPer_cardno, $e, $per_personalTable)
{
    $report = new report;
    $err = "";
    $success = array();
    $dbConn = new DbConn;
    $success['msg'] = array();
    $i = 0;
    try {
        $sqlUpdate = "UPDATE " . $per_personalTable . " 
                        SET `through_trial` = :through_trial WHERE per_cardno = :per_cardno ";

        $stm = $dbConn->conn->prepare($sqlUpdate);
        // echo count($moveCode)."<br>";
        foreach ($arrPer_cardno as $key => $value) {
            $t = $report->throughTrial($value[0], $e, $per_personalTable);
            // $tt = ($t["result"] === true ? 1 : 2 );
            if ($t["result"] === true) {
                $tt = 1;
            } elseif ($t["result"] === false) {
                $tt = 2;
            } else {
                $tt = 1;
            }

            $stm->bindParam(":through_trial", $tt);
            $stm->bindParam(":per_cardno", $value[0]);
            $stm->execute();
            $count = $stm->rowCount();

            if ($count == 1) {
                $i = '<span class="text-info" >Update through Trial :: ' . $value[0] . '</span>';
                array_push($success['msg'], $i);
            } else if ($count == 0) {
                $i = '<span class="text-success" >Update through Trial Skip :: ' . $value[0] . '</span>';
                array_push($success['msg'], $i);
            }
            // echo $i."<br>";
        }

        $success['success'] = true;


        // echo $i."<br>";

    } catch (Exception $e) {
        $err = $e->getMessage();
    }
    if ($err != '') {
        $success['success'] = null;
        array_push($success['msg'], $err);
    }
    return $success;
}



 // echo $sqlOracle ."<br>";
// echo $sqlOracle;
// echo "<pre>";
// print_r($moveCode);
// echo "</pre>";
//  echo var_dump($moveCode);

// echo "<pre>";
// print_r($rrr);
// echo "</pre>";
