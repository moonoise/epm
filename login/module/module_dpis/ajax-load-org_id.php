<?php
include_once "../../config.php";
include_once "../../includes/dbconn.php";

$db = new dbconn;

$sqlOrg =  'select org_id,org_name from per_org where org_id_ref = 77';
$stm = $db->conn->prepare($sqlOrg);
$stm->execute();
$result = $stm->fetchAll(PDO::FETCH_ASSOC);
//     echo "<pre>";
//     print_r($result);
//  echo "</pre>";

echo json_encode($result);
