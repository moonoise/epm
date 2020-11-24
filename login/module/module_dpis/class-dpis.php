<?php
// @include_once "config.php";
// @include_once "includes/ociConn.php";
class dpis extends ociConn
{
    public $ociConn;
    public $db_epm;
    public function __construct()
    {
        $this->ociConn = $this->ociConnect();
        $this->db_epm = new Dbconn;
    }

    function queryPersonal($per_cardno)
    {   //ใช้กับไฟล์ ajax-modal-move_person-show.php view-load-person2.php  
        $success = array();
        $ociSql = "SELECT
                    per_personal.per_id,
                    per_personal.per_cardno,
                    per_prename.pn_name,
                    per_personal.per_name,
                    per_personal.per_surname,
                    per_personal.per_eng_name,
                    per_personal.per_eng_surname,
                    per_personal.per_gender,
                    per_personal.per_type,
                    per_personal.pos_id,
                    per_position.pos_no,
                    per_personal.per_birthdate,
                    per_personal.per_startdate,
                    per_personal.per_retiredate,
                    per_personal.per_mgtsalary,
                    per_personal.per_spsalary,
                    per_line.pl_name,
                    per_line.pl_code,
                    per_mgt.pm_code,
                    per_mgt.pm_name,
                    per_type.pt_name,
                    per_personal.level_no,
                    per_level.level_name,
                    (per_personalpic.per_cardno || '-' || LPAD(per_personalpic.per_picseq,3,'0')  || '.jpg') as per_picture,
                    per_org.org_id,
                    per_org.org_name,
                    per_org1.org_id AS org_id_1,
                    per_org1.org_name AS org_name1,
                    per_org2.org_id AS org_id_2,
                    per_personal.per_status,
                    per_personal.per_type AS per_type1,
                    per_org2.org_name AS org_name2
                FROM
                    per_personal
                LEFT JOIN
                    per_prename
                ON per_prename.pn_code = per_personal.pn_code 
                LEFT JOIN per_position
                ON per_position.pos_id = per_personal.pos_id 
                LEFT JOIN per_level
                ON per_level.level_no = per_personal.level_no 
                LEFT JOIN per_org
                ON per_position.org_id = per_org.org_id 
                LEFT JOIN per_org per_org1
                ON per_position.org_id_1 = per_org1.org_id 
                LEFT JOIN per_line
                ON per_line.pl_code = per_position.pl_code 
                LEFT JOIN per_mgt
                ON per_position.pm_code = per_mgt.pm_code 
                LEFT JOIN per_org per_org2
                ON per_position.org_id_2 = per_org2.org_id 
                LEFT JOIN per_type
                ON per_type.pt_code = per_position.pt_code 
                LEFT JOIN per_personalpic
                ON per_personal.per_id = per_personalpic.per_id 
                AND per_personalpic.pic_show = 1
                WHERE
                    per_personal.per_type = 1
                
                AND per_personal.per_cardno = :per_cardno ";

        $stid = oci_parse($this->ociConn, $ociSql);
        oci_bind_by_name($stid, ":per_cardno", $per_cardno);
        oci_execute($stid);
        oci_fetch_all($stid, $res, null, null, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
        $success['result'] = $res;
        oci_free_statement($stid);
        return $success;

        // AND
        //             per_personal.per_status = 1

    }

    function queryPer_cardno($org_id)
    {
        $success = array();
        $ociSql = "SELECT
        per_personal.per_cardno
        FROM
            per_personal
        RIGHT JOIN per_position
        ON per_position.pos_id = per_personal.pos_id 
        AND (per_position.org_id = :org_id  OR per_position.org_id_1 = :org_id or per_position.org_id_2 = :org_id)
        LEFT JOIN per_org
        ON per_position.org_id = per_org.org_id 
        LEFT JOIN per_org per_org1
        ON per_position.org_id_1 = per_org1.org_id 
        LEFT JOIN per_org per_org2
        ON per_position.org_id_2 = per_org2.org_id 
        WHERE
            per_personal.per_type = 1
        AND
            per_personal.per_status = 1
        
        ORDER BY per_position.org_id ASC ,per_position.org_id_1 ASC ,per_position.org_id_2 ASC
                ";
        // AND per_org.org_id = 19184
        $stid = oci_parse($this->ociConn, $ociSql);
        oci_bind_by_name($stid, ":org_id", $org_id);
        oci_execute($stid);
        oci_fetch_all($stid, $res, null, null, OCI_NUM);
        $success['result'] = $res;
        oci_free_statement($stid);
        return $success;
    }

    function searchPer_cardno($per_cardno)
    {
        $success = array();
        $ociSql = "SELECT
        per_personal.per_cardno,
        per_personal.per_name,
        per_personal.per_surname,
        per_org.org_name,
        per_org1.org_name AS org_name1,
        per_org2.org_name AS org_name2
        FROM
            per_personal
        RIGHT JOIN per_position
        ON per_position.pos_id = per_personal.pos_id 
        LEFT JOIN per_org
        ON per_position.org_id = per_org.org_id 
        LEFT JOIN per_org per_org1
        ON per_position.org_id_1 = per_org1.org_id 
        LEFT JOIN per_org per_org2
        ON per_position.org_id_2 = per_org2.org_id 
        WHERE
            per_personal.per_type = 1
        AND per_personal.per_cardno like  :per_cardno
        AND
            per_personal.per_status = 1
                ";
        // AND per_org.org_id = 19184
        $stid = oci_parse($this->ociConn, $ociSql);
        $id = "%" . $per_cardno . "%";
        oci_bind_by_name($stid, ":per_cardno", $id);
        oci_execute($stid);
        oci_fetch_all($stid, $res, null, null, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
        $success['result'] = $res;
        oci_free_statement($stid);

        return $success;
    }

    function ociClose()
    {
        oci_close($this->ociConn);
    }

    function updatePer_Personal($data, $personalTable)
    {
        $success = array();
        $err = '';
        try {
            $sql = "UPDATE  " . $personalTable . "  SET 
                                                    `per_id` = :per_id ,  
                                                    `pn_name` = :pn_name , 
                                                    `per_name` = :per_name , 
                                                    `per_surname` = :per_surname , 
                                                    `per_eng_name` = :per_eng_name , 
                                                    `per_eng_surname` = :per_eng_surname , 
                                                    `per_gender` = :per_gender , 
                                                    `per_type` = :per_type , 
                                                    `pos_id` = :pos_id , 
                                                    `pos_no` = :pos_no , 
                                                    `per_birthdate` = :per_birthdate , 
                                                    `per_startdate` = :per_startdate , 
                                                    `per_retiredate` = :per_retiredate , 
                                                    `per_mgtsalary` = :per_mgtsalary , 
                                                    `per_spsalary` = :per_spsalary , 
                                                    `pl_name` = :pl_name , 
                                                    `pl_code` = :pl_code , 
                                                    `pm_code` = :pm_code , 
                                                    `pm_name` = :pm_name , 
                                                    `pt_name` = :pt_name , 
                                                    `level_no` = :level_no , 
                                                    `level_name` = :level_name ,  
                                                    `per_picture` = :per_picture , 
                                                    `org_id` = :org_id , 
                                                    `org_name` = :org_name , 
                                                    `org_id_1` = :org_id_1 , 
                                                    `org_name1` = :org_name1 , 
                                                    `org_id_2` = :org_id_2 , 
                                                    `org_name2` = :org_name2 , 
                                                    `login_status` = :login_status  
                WHERE `per_cardno` = :per_cardno 
                                                ";
            $stm = $this->db_epm->conn->prepare($sql);
            $stm->bindParam(":per_id", $data[0]['PER_ID']);
            $stm->bindParam(":per_cardno", $data[0]['PER_CARDNO']);
            $stm->bindParam(":pn_name", $data[0]['PN_NAME']);
            $stm->bindParam(":per_name", $data[0]['PER_NAME']);
            $stm->bindParam(":per_surname", $data[0]['PER_SURNAME']);
            $stm->bindParam(":per_eng_name", $data[0]['PER_ENG_NAME']);
            $stm->bindParam(":per_eng_surname", $data[0]['PER_ENG_SURNAME']);
            $stm->bindParam(":per_gender", $data[0]['PER_GENDER']);
            $stm->bindParam(":per_type", $data[0]['PER_TYPE']);
            $stm->bindParam(":pos_id", $data[0]['POS_ID']);
            $stm->bindParam(":pos_no", $data[0]['POS_NO']);
            $stm->bindParam(":per_birthdate", $data[0]['PER_BIRTHDATE']);
            $stm->bindParam(":per_startdate", $data[0]['PER_STARTDATE']);
            $stm->bindParam(":per_retiredate", $data[0]['PER_RETIREDATE']);
            $stm->bindParam(":per_mgtsalary", $data[0]['PER_MGTSALARY']);
            $stm->bindParam(":per_spsalary", $data[0]['PER_SPSALARY']);
            $stm->bindParam(":pl_name", $data[0]['PL_NAME']);
            $stm->bindParam(":pl_code", $data[0]['PL_CODE']);
            $stm->bindParam(":pm_code", $data[0]['PM_CODE']);
            $stm->bindParam(":pm_name", $data[0]['PM_NAME']);
            $stm->bindParam(":pt_name", $data[0]['PT_NAME']);
            $stm->bindParam(":level_no", $data[0]['LEVEL_NO']);
            $stm->bindParam(":level_name", $data[0]['LEVEL_NAME']);
            $stm->bindParam(":per_picture", $data[0]['PER_PICTURE']);
            $stm->bindParam(":org_id", $data[0]['ORG_ID']);
            $stm->bindParam(":org_name", $data[0]['ORG_NAME']);
            $stm->bindParam(":org_id_1", $data[0]['ORG_ID_1']);
            $stm->bindParam(":org_name1", $data[0]['ORG_NAME1']);
            $stm->bindParam(":org_id_2", $data[0]['ORG_ID_2']);
            $stm->bindParam(":org_name2", $data[0]['ORG_NAME2']);
            $stm->bindValue(":login_status", 1);

            $stm->bindParam(":per_cardno", $data[0]['PER_CARDNO']);
            $stm->execute();
            $c = $stm->rowCount();
            if ($c == 1) {
                $success['success'] = true;
                $success['msg'] = "Updated :: " . $data[0]['PER_CARDNO'] . ' -> ' . $data[0]['PER_NAME'] . ' ' . $data[0]['PER_SURNAME'];
            } else if ($c > 1) {
                $success['success'] = false;
                $success['msg'] = 'Updates :: ' . $c . 'Row' . $data[0]['PER_CARDNO'] . ' -> ' . $data[0]['PER_NAME'] . ' ' . $data[0]['PER_SURNAME'];
            } else {
                $success['success'] = false;
                $success['msg'] = "Skip :: " . $data[0]['PER_CARDNO'] . ' -> ' . $data[0]['PER_NAME'] . ' ' . $data[0]['PER_SURNAME'];
            }
        } catch (Exception $e) {
            $err = $e->getMessage();
        }
        if ($err != '') {
            $success['success'] = null;
            $success['msg'] = $err . ' :: ' . $data[0]['PER_CARDNO'] . ' -> ' . $data[0]['PER_NAME'] . ' ' . $data[0]['PER_SURNAME'];;
        }
        return $success;
    }

    function insertPer_Personal($data, $personalTable)
    {
        $success = array();
        $err = '';
        //  echo "<pre>";
        // print_r($data[0]['PER_ID']);
        // echo "</pre>";
        try {
            $sql = "INSERT INTO " . $personalTable . " (
                                                    `per_id`, 
                                                    `per_cardno`, 
                                                    `pn_name`, 
                                                    `per_name`, 
                                                    `per_surname`, 
                                                    `per_eng_name`, 
                                                    `per_eng_surname`, 
                                                    `per_gender`, 
                                                    `per_type`, 
                                                    `pos_id`, 
                                                    `pos_no`, 
                                                    `per_birthdate`, 
                                                    `per_startdate`, 
                                                    `per_retiredate`, 
                                                    `per_mgtsalary`, 
                                                    `per_spsalary`, 
                                                    `pl_name`, 
                                                    `pl_code`, 
                                                    `pm_code`, 
                                                    `pm_name`, 
                                                    `pt_name`, 
                                                    `level_no`, 
                                                    `level_name`,  
                                                    `per_picture`, 
                                                    `org_id`, 
                                                    `org_name`, 
                                                    `org_id_1`, 
                                                    `org_name1`, 
                                                    `org_id_2`, 
                                                    `org_name2`, 
                                                    `login_status`) 
                                                VALUES (
                                                    :per_id,
                                                    :per_cardno,
                                                    :pn_name,
                                                    :per_name,
                                                    :per_surname,
                                                    :per_eng_name,
                                                    :per_eng_surname,
                                                    :per_gender,
                                                    :per_type,
                                                    :pos_id,
                                                    :pos_no,
                                                    :per_birthdate,
                                                    :per_startdate,
                                                    :per_retiredate,
                                                    :per_mgtsalary,
                                                    :per_spsalary,
                                                    :pl_name,
                                                    :pl_code,
                                                    :pm_code,
                                                    :pm_name,
                                                    :pt_name,
                                                    :level_no,
                                                    :level_name,
                                                    :per_picture,
                                                    :org_id,
                                                    :org_name,
                                                    :org_id_1,
                                                    :org_name1,
                                                    :org_id_2,
                                                    :org_name2,
                                                    :login_status
                                                )";
            $stm = $this->db_epm->conn->prepare($sql);
            $stm->bindParam(":per_id", $data[0]['PER_ID']);
            $stm->bindParam(":per_cardno", $data[0]['PER_CARDNO']);
            $stm->bindParam(":pn_name", $data[0]['PN_NAME']);
            $stm->bindParam(":per_name", $data[0]['PER_NAME']);
            $stm->bindParam(":per_surname", $data[0]['PER_SURNAME']);
            $stm->bindParam(":per_eng_name", $data[0]['PER_ENG_NAME']);
            $stm->bindParam(":per_eng_surname", $data[0]['PER_ENG_SURNAME']);
            $stm->bindParam(":per_gender", $data[0]['PER_GENDER']);
            $stm->bindParam(":per_type", $data[0]['PER_TYPE']);
            $stm->bindParam(":pos_id", $data[0]['POS_ID']);
            $stm->bindParam(":pos_no", $data[0]['POS_NO']);
            $stm->bindParam(":per_birthdate", $data[0]['PER_BIRTHDATE']);
            $stm->bindParam(":per_startdate", $data[0]['PER_STARTDATE']);
            $stm->bindParam(":per_retiredate", $data[0]['PER_RETIREDATE']);
            $stm->bindParam(":per_mgtsalary", $data[0]['PER_MGTSALARY']);
            $stm->bindParam(":per_spsalary", $data[0]['PER_SPSALARY']);
            $stm->bindParam(":pl_name", $data[0]['PL_NAME']);
            $stm->bindParam(":pl_code", $data[0]['PL_CODE']);
            $stm->bindParam(":pm_code", $data[0]['PM_CODE']);
            $stm->bindParam(":pm_name", $data[0]['PM_NAME']);
            $stm->bindParam(":pt_name", $data[0]['PT_NAME']);
            $stm->bindParam(":level_no", $data[0]['LEVEL_NO']);
            $stm->bindParam(":level_name", $data[0]['LEVEL_NAME']);
            $stm->bindParam(":per_picture", $data[0]['PER_PICTURE']);
            $stm->bindParam(":org_id", $data[0]['ORG_ID']);
            $stm->bindParam(":org_name", $data[0]['ORG_NAME']);
            $stm->bindParam(":org_id_1", $data[0]['ORG_ID_1']);
            $stm->bindParam(":org_name1", $data[0]['ORG_NAME1']);
            $stm->bindParam(":org_id_2", $data[0]['ORG_ID_2']);
            $stm->bindParam(":org_name2", $data[0]['ORG_NAME2']);
            $stm->bindValue(":login_status", 1);
            $stm->execute();

            if ($stm->rowCount() == 1) {
                $success['success'] = true;
                $success['msg'] = "Insert :: " . $data[0]['PER_CARDNO'] . ' -> ' . $data[0]['PN_NAME'] . ' ' . $data[0]['PER_SURNAME'];
            } else {
                $success['success'] = false;
                $success['msg'] = "Insert error :: " . $data[0]['PER_CARDNO'] . ' -> ' . $data[0]['PN_NAME'] . ' ' . $data[0]['PER_SURNAME'];
            }
        } catch (Exception $e) {
            $err = $e->getMessage();
        }
        if ($err != '') {
            $success['success'] = null;
            $success['msg'] = $err . ' :: INSERT ' . $data[0]['PER_CARDNO'] . ' -> ' . $data[0]['PN_NAME'] . ' ' . $data[0]['PER_SURNAME'];;
        }
        return $success;
    }
}
