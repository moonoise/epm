<?php
// @include_once "../../config.php";
// @include_once "../../includes/dbconn.php";
// @include_once "../../ociConn.php";

class report extends DbConn
{
    function cutGrade($score)
    {
        if (is_numeric($score)) {
            $score = round($score);
            if ($score >= 90) {
                $grade = "A";
            } elseif (($score >= 80) && ($score <= 89)) {
                $grade = "B";
            } elseif (($score >= 70) && ($score <= 79)) {
                $grade = "C";
            } elseif (($score >= 60) && ($score <= 69)) {
                $grade = "D";
            } elseif (($score >= 0) && ($score <= 59)) {
                $grade = "F";
            }
        } else {
            $grade = "Error";
        }
        return $grade;
    }

    function diffDate($date1, $date2)
    {
        // $diff = array();
        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);
        $interval = $datetime1->diff($datetime2);
        $diff = array(
            "y" => $interval->format('%y'),
            "m" => $interval->format('%m'),
            "d" => $interval->format('%d')
        );
        return $diff;
    }

    function  throughTrial($per_cardno, $dateEvaluation, $personalTable)
    {   //เช็คว่า ผ่านงานแล้วหรือยัง  เช็คจากอายุงาน -> แล้วก็เช็คจาก mov_code  true=ผ่านงาน false=ไม่ผ่านงาน
        $err = '';
        $success = array();
        // $d = date("Y-m-d");
        $db = new DbConn;
        try {
            $sql = "SELECT per_startdate,mov_code From $personalTable WHERE per_cardno = :per_cardno ";
            $stm = $db->conn->prepare($sql);
            $stm->bindParam(":per_cardno", $per_cardno);
            $stm->execute();
            $result = $stm->fetchAll();
            $count = $stm->rowCount();

            if ($count == 1) {

                $diff = $this->diffDate($dateEvaluation, $result[0]['per_startdate']);
                //echo ($diff["y"]) ;
                // echo ($diff["y"] * 12) + $diff["m"];
                if (($diff["y"] * 12) + $diff["m"]  >= 12) {
                    $success['success'] = true;
                    $success['result'] = true;
                } elseif (($diff["y"] * 12) + $diff["m"]  < 12) {
                    if ($result[0]['mov_code'] == 10211) {
                        $success['success'] = true;
                        $success['result'] = true;
                    } else {
                        $success['success'] = true;
                        $success['result'] = false;
                    }
                }
            } else {
                $success['success'] = false;
                $success['msg'] = "ไม่บพข้อมูล";
            }
        } catch (Exception $e) {
            $err = $e->getMessage();
        }

        if ($err != '') {
            $success['success'] = null;
            $success['msg'] = $err;
        }
        return $success;
    }

    function personalThroughTrial($per_cardno, $personal_table)
    {
        $err = '';
        $success  = array(
            'success' => null,
            'result' => null,
            'msg' => null
        );
        // $d = date("Y-m-d");
        $db = new DbConn;
        try {
            $sql = "SELECT `through_trial` FROM $personal_table WHERE per_cardno = :per_cardno ";
            $stm = $db->conn->prepare($sql);
            $stm->bindParam(":per_cardno", $per_cardno);
            $stm->execute();
            $result = $stm->fetchAll(PDO::FETCH_COLUMN);

            // if ($result['through_trial'] == 2) {

            // }

        } catch (\Exception $e) {
            $err = $e->getMessage();
        }

        if ($err != "") {
            $success  = array(
                'success' => null,
                'result' => null,
                'msg' => $err
            );
        } else {
            $success  = array(
                'success' => true,
                'result' => $result[0],
                'msg' => null
            );
        }

        return $success;
    }


    function tableKPI($per_cardno, $years, $tablePersonal, $kpiScoreTable)
    {
        // $dbConn = new DbConn;
        $err = "";
        $success = array();

        try {

            $sqlKPI = "SELECT " . $kpiScoreTable . ".`kpi_score_id`,
            " . $kpiScoreTable . ".`kpi_code`,
            " . $kpiScoreTable . ".`per_cardno`,
            " . $kpiScoreTable . ".`id_admin`,
            " . $kpiScoreTable . ".`kpi_score`,
            " . $kpiScoreTable . ".`kpi_score_raw`,
            " . $kpiScoreTable . ".`weight`,
            " . $kpiScoreTable . ".`kpi_accept`,
            `kpi_question`.`kpi_code_org`,
            `kpi_question`.`kpi_title`,
            " . $tablePersonal . ".`through_trial`
                FROM " . $kpiScoreTable . " 
                LEFT JOIN `kpi_question` 
                ON " . $kpiScoreTable . ".`kpi_code` = `kpi_question`.`kpi_code`
                LEFT JOIN `$tablePersonal` 
                ON " . $tablePersonal . ".`per_cardno` = " . $kpiScoreTable . ".`per_cardno`
                WHERE " . $kpiScoreTable . ".`per_cardno` = :per_cardno AND " . $kpiScoreTable . ".`years` = :years AND " . $kpiScoreTable . ".`soft_delete` = 0";
            $stmKPI = $this->conn->prepare($sqlKPI);
            $stmKPI->bindParam(":per_cardno", $per_cardno);
            $stmKPI->bindParam(":years", $years);
            $stmKPI->execute();
            $result = $stmKPI->fetchAll();

            $success['success'] = true;
            $success['result'] = $result;
        } catch (Exception $e) {
            $err = $e->getMessage();
        }
        // KPIs
        if ($err != '') {

            $success['success'] = null;
            $success['msg'] = $err;
        }
        return $success;
    }

    function reportKPIresult($per_cardno, $years, $kpiScoreResult)
    {  //ไม่ได้ใช้ กะว่าจะลบออก มันรกเกินไป
        $err = "";
        $success = array();
        try {
            $sql = "SELECT * FROM $kpiScoreResult 
                    WHERE `per_cardno` = :per_cardno 
                    AND `years` = :years 
                    AND soft_delete = 0 ";
            $stm = $this->conn->prepare($sql);
            $stm->bindParam(":per_cardno", $per_cardno);
            $stm->bindParam(":years", $years);
            $stm->execute();
            $result = $stm->fetchAll(PDO::FETCH_ASSOC);
            if (count($result == 0)) {
                $success['success'] = true;
                $success['result'] = $result;
            } elseif (count($result > 1)) {
                $success['success'] = false;
                $success['msg'] = "found one more record.";
            } else {
                $success['success'] = false;
                $success['msg'] = "not found record.";
            }
        } catch (Exception $e) {
            $err = $e->getMessage();
            $success['success'] = null;
            $success['msg'] = $err;
        }
        return $success;
    }

    function reportKPI($per_cardno, $years, $kpi_type, $kpi_table)
    {  // ใช้กับไฟล์ export-kpi135-1.php

        $err = "";
        $success = array();

        try {

            $sqlKPI = "SELECT " . $kpi_table . ".`kpi_score_id`,
            " . $kpi_table . ".`kpi_code`,
            " . $kpi_table . ".`per_cardno`,
            " . $kpi_table . ".`id_admin`,
            " . $kpi_table . ".`kpi_score`,
            " . $kpi_table . ".`kpi_score_raw`,
            " . $kpi_table . ".`works_name`,
            " . $kpi_table . ".`weight`,
            " . $kpi_table . ".`kpi_accept`,
                        `kpi_question`.`kpi_code_org`,
                        `kpi_question`.`kpi_title`,
                        `kpi_question`.`kpi_type`,
                        `kpi_question`.`kpi_level1`,
                        `kpi_question`.`kpi_level2`,
                        `kpi_question`.`kpi_level3`,
                        `kpi_question`.`kpi_level4`,
                        `kpi_question`.`kpi_level5`
                FROM " . $kpi_table . "
                INNER JOIN `kpi_question` 
                ON " . $kpi_table . ".`kpi_code` = `kpi_question`.`kpi_code`
                AND `kpi_question`.`kpi_type` = :kpi_type
                WHERE per_cardno = :per_cardno 
                AND years = :years 
                AND soft_delete = 0 ";
            $stmKPI = $this->conn->prepare($sqlKPI);
            $stmKPI->bindParam(":per_cardno", $per_cardno);
            $stmKPI->bindParam(":kpi_type", $kpi_type);
            $stmKPI->bindParam(":years", $years);
            $stmKPI->execute();
            $result = $stmKPI->fetchAll(PDO::FETCH_ASSOC);

            $success['success'] = true;
            $success['result'] = $result;
        } catch (Exception $e) {
            $err = $e->getMessage();
        }
        // KPIs
        if ($err != '') {

            $success['success'] = null;
            $success['msg'] = $err;
        }
        return $success;
    }


    function tableCPC($per_cardno, $years, $cpcTypeKey, $tablePersonal, $tableCPCscore)
    {  //ต้องแก้ return คือ  -> จำนวนข้อที่ผ่านตามความคาดหวัง  -> คะแนนที่ส่งออกต้องเช็คเงื่อนไขตามค่าคาดหวังก่อน
        // $dbConn = new DbConn;
        $err = "";
        $cpcType = implode(",", $cpcTypeKey);

        try {

            $sqlCPC = "
            SELECT " . $tableCPCscore . ".`cpc_score_id`,
            `" . $tableCPCscore . "`.`question_no`,
            " . $tableCPCscore . ".`per_cardno`,
            " . $tableCPCscore . ".`cpc_divisor`,
            " . $tableCPCscore . ".`cpc_accept1`,
            " . $tableCPCscore . ".`cpc_accept2`,
            " . $tableCPCscore . ".`cpc_accept3`,
            " . $tableCPCscore . ".`cpc_accept4`,
            " . $tableCPCscore . ".`cpc_accept5`,
            " . $tableCPCscore . ".`cpc_score1`,
            " . $tableCPCscore . ".`cpc_score2`,
            " . $tableCPCscore . ".`cpc_score3`,
            " . $tableCPCscore . ".`cpc_score4`,
            " . $tableCPCscore . ".`cpc_score5`,
            " . $tableCPCscore . ".`years`,
                `cpc_question`.`question_code`,
                `cpc_question`.`question_title`,
                `cpc_question`.`question_type`,
                `" . $tablePersonal . "`.`level_no`,
            IF (`" . $tablePersonal . "`.`level_no` = 'D1' OR `" . $tablePersonal . "`.`level_no` = 'D2' ,  
                ( CASE `cpc_question`.`question_type`
                    WHEN 1 THEN 10
                    WHEN 2 THEN 3
                    WHEN 3 THEN 4
                END
                )
                    , 10) AS cpc_weight,
                    " . $tablePersonal . ".`through_trial`   
            FROM " . $tableCPCscore . " 
            INNER JOIN `cpc_question` ON (`cpc_question`.`question_no` = " . $tableCPCscore . ".`question_no` )
                AND (`cpc_question`.`question_type` IN (" . $cpcType . ") )
            LEFT JOIN `" . $tablePersonal . "` ON `" . $tablePersonal . "`.`per_cardno` = " . $tableCPCscore . ".`per_cardno`
            WHERE " . $tableCPCscore . ".`per_cardno` = :per_cardno
            AND  " . $tableCPCscore . ".`years` = :years
            AND " . $tableCPCscore . ".`soft_delete` = 0
            ORDER BY `cpc_question`.`question_code` ASC
                         ";
            // echo $sqlCPC;
            $stmCPC = $this->conn->prepare($sqlCPC);
            $stmCPC->bindParam(":per_cardno", $per_cardno);
            $stmCPC->bindParam(":years", $years);
            $stmCPC->execute();
            $result = $stmCPC->fetchAll();

            // foreach ($result as $key => $value) {
            // $a =  array($value['cpc_accept1'],$value['cpc_accept2'],$value['cpc_accept3'],$value['cpc_accept4'],$value['cpc_accept5']);
            // $p = $this->getPoint($a);
            // $t = $this->getTotal($value['cpc_divisor'],$a);
            // $r['get_point'] = $p ;
            // $r['total'] = $t;
            // $rr[] = array_merge($result[$key],$r);

            // }

            $success['success'] = true;

            $success['result'] = $result;
        } catch (Exception $e) {
            $err = $e->getMessage();
        }

        if ($err != '') {
            $success['success'] = null;
            $success['msg'] = $err;
        }
        return $success;
    }
    // ใช้กับไฟล์ view-evaluation-result-cpc.php 
    function getPoint($cpc_accept)
    { // $cpc_accept  ฟังก์ชั่น ไว้หาว่าผ่านกี่ข้อ  โดยต้องผ่าน 1 ไปหา 2 , 3 ,4 ตามลำดับ  
        $i = 0;
        $point = null;
        while (@$cpc_accept[$i] > 2 && $i < count($cpc_accept)) {
            $point++;
            $i++;
        }

        $checkCountArr = count(array_filter($cpc_accept, function ($item_values) {
            if ($item_values === null) {
                return false;
            }
            return true;
        }));
        if ($checkCountArr > 0 && $point === null) {
            $point = 0;
        }
        return $point;
    }
    // ใช้กับไฟล์ view-evaluation-result-cpc.php 
    function getPointForType456($cpc_accept)
    { // $cpc_accept  ฟังก์ชั่น ไว้หาว่าผ่านมีข้อ  โดยต้องผ่าน 1 ไปหา 2 , 3 ,4 ตามลำดับ  ใช้กับกลุ่ม 4 ,5,6  เวลาที่ติด IDP.
        $i = 0;
        $point = null;
        while (@$cpc_accept[$i] > 0 && $i < count($cpc_accept)) {
            $point++;
            $i++;
        }
        $checkCountArr = count(array_filter($cpc_accept, function ($item_values) {
            if ($item_values === null) {
                return false;
            }
            return true;
        }));
        if ($checkCountArr > 0 && $point === null) {
            $point = 0;
        }
        return $point;
    }


    function getTotal($divisor, $cpc_accept)
    {   //สำหรับหาคะแนน
        if (!strlen($divisor)) {
            return null;
        }
        $s = 0;
        $i = 0;
        $divisor = intval($divisor);
        $checkCountArr = count(array_filter($cpc_accept, function ($item_values) {
            if ($item_values === null) {
                return false;
            }
            return true;
        }));
        if ($checkCountArr == 5) {
            while ($i < $divisor) {
                @$s = $s + $cpc_accept[$i];
                $i++;
            }
            @$s = $s / $divisor;
            // echo "<pre>";
            // print_r($cpc_accept);
            // echo "</pre>";
            // echo $divisor;
            // return round($s,2) ;  //ห้ามตัดเศษที่ฟังก์ชัน  เพราะต้องเอาไปคำนวณ gap ด้วย  วิธีการคำนวณ gap ปัดเศษลงอย่างเดียว
            return $s;
        } else {
            return null;
        }
    }

    function getTotal_seriesType($divisor, $cpc_accept)
    {  // คำนวณคะแนน แบบอนุกรม  สำหรับ type 1 กับ 2  ในข้อย่อยที่เกินจากค่าคาดหวังแล้ว 
        $i = $divisor;
        $point = null;
        while (@$cpc_accept[$i] > 2 && $i < 5) {
            $point++;
            $i++;
        }
        $checkCountArr = count(array_filter($cpc_accept, function ($item_values) {
            if ($item_values === null) {
                return false;
            }
            return true;
        }));
        if ($checkCountArr > 0 && $point === null) {
            $point = 0;
        }
        return $point;
    }



    function cpcQueryScore($per_cardno, $years, $cpcScoreResult)
    {
        $err = "";
        $success = array();
        try {
            $sql = "SELECT * FROM $cpcScoreResult WHERE  per_cardno = :per_cardno AND years = :years ";
            $stm = $this->conn->prepare($sql);
            $stm->bindParam(":per_cardno", $per_cardno);
            $stm->bindParam(":years", $years);
            $stm->execute();

            $result = $stm->fetchAll();
            $success['success'] = true;
            $success['result'] = $result;
        } catch (Exception $e) {
            $err = $e->getMessage();
        }

        if ($err != '' || count($result) == 0) {
            $success['success'] = null;
            $success['msg'] = $err;
        }
        return $success;
    }

    function kpiQueryScore($per_cardno, $years, $kpiScoreResultTable)
    {
        $err = "";
        $success = array();
        try {
            $sql = "SELECT * FROM $kpiScoreResultTable WHERE  per_cardno = :per_cardno AND years = :years ";
            $stm = $this->conn->prepare($sql);
            $stm->bindParam(":per_cardno", $per_cardno);
            $stm->bindParam(":years", $years);
            $stm->execute();

            $result = $stm->fetchAll();
            $success['success'] = true;
            $success['result'] = $result;
        } catch (Exception $e) {
            $err = $e->getMessage();
        }

        if ($err != '' or count($result) == 0) {
            $success['success'] = null;
            $success['msg'] = $err;
        }
        return $success;
    }

    function  cpcCalculate($per_cardno, $years, $cpcTypeKey)
    {   //คำนวณในส่วน  report 
        // $cpcTypeKey = array(1,2,3);
        $cpcResult = $this->tableCPC($per_cardno, $years, $cpcTypeKey);
        $success = array();
        $r = array();
        $rr = array();


        if ($cpcResult['success'] === true) {
            $cpcSum2 = 0;
            $CPCSumWeight = 0;
            $success['sucess'] = true;


            foreach ($cpcResult['result'] as $key => $value) {
                $a = array($value['cpc_accept1'], $value['cpc_accept2'], $value['cpc_accept3'], $value['cpc_accept4'], $value['cpc_accept5']);
                $total = $this->getTotal($value['cpc_divisor'], $a);
                $sum1 = (($total * $value['cpc_weight'] * 20) / 100);
                $cpcSum2 = $cpcSum2 + $sum1;
                $CPCSumWeight = $CPCSumWeight + $value['cpc_weight'];


                $r['question_code'] = $value['question_code'];
                $r['question_title'] = $value['question_title'];
                $r['cpc_divisor'] = $value['cpc_divisor'];
                $r['total'] = $total;
                $r['cpc_weight'] = $value['cpc_weight'];
                $r['sum1']     = $sum1;

                array_push($rr, $r);
            }
            $success['cpc'] = $rr;
            $success['CPCsumWeight'] = $CPCSumWeight;
            $success['cpcSum2'] = $cpcSum2;
        }

        return $success;
    }

    function reportKPI1($kpiResult)
    {  // ใช้กับไฟล์ ajax-evaluation-result-year.php
        $kpiCheckSumAll = 0;
        $kpi = array();
        $arr = array();
        if ($kpiResult['success'] === true) {
            $kpiSum2 = null;
            $kpiSum2_user = null;
            $kpiSum = 0;
            $kpiSum_user = 0;
            $kpiWeightSum = null;
            foreach ($kpiResult['result'] as $key => $value) {

                $kpiSum_user = ($value['kpi_score'] * $value['weight'] * 20) / 100;
                $kpiSum2_user = $kpiSum2_user + $kpiSum_user;
                if ($value['kpi_accept'] == 1) {
                    $kpiSum = ($value['kpi_score'] * $value['weight'] * 20) / 100;

                    $kpiSum2 = $kpiSum2 + $kpiSum;
                } else {
                    $kpiCheckSumAll++;
                }
                $kpiWeightSum = $kpiWeightSum + $value['weight'];

                $arr = array(
                    'kpi_score_id' => $value['kpi_score_id'],
                    'kpi_code' => $value['kpi_code'],
                    'kpi_code_org' => $value['kpi_code_org'],
                    'kpi_title' =>  $value['kpi_title'],
                    'kpi_score' => ($value['kpi_accept'] == 1 ? $value['kpi_score'] : "-"),
                    'weight' => $value['weight'],
                    'kpiSum' => ($value['kpi_accept'] == 1 ? round($kpiSum, 2) : "-"),
                    'kpiSum_' => ($value['kpi_accept'] == 1 ? round($kpiSum, 2) : NULL),
                    'kpiSum_user' => ($value['kpi_score'] != null ? round($kpiSum_user, 2) : "-"),
                );
                // kpiSum_  กรณียังไม่ยืนยันผลให้ส่ง Null ไป
                $kpi['text'][] = $arr;
            }
            $kpi['per_cardno'] = $kpiResult['result'][0]['per_cardno'];
            $kpi['kpiWeightSum'] = ($kpiWeightSum == "" ? "-" : $kpiWeightSum);
            $kpi['kpiWeightSum_'] = ($kpiWeightSum == "" ? NULL : $kpiWeightSum);
            $kpi['kpiSum2'] = ($kpiCheckSumAll == 0 && $kpiSum2 != "" ? round($kpiSum2, 2) : "-"); //ถ้ายืนยันยังไม่ครบส่ง - ใช้กับ report
            $kpi['kpiSum2_'] = ($kpiCheckSumAll == 0 && $kpiSum2 != "" ? round($kpiSum2, 2) : null); //ถ้ายืนยันยังไม่ครบส่ง null
            $kpi['kpiSum2_user'] =  $kpiSum2_user;
            $kpi['kpiSum2_user_'] =  ($kpiSum2_user == 0 ? "-" : $kpiSum2_user); // ไม่มีคะแนนให้ใส่ - 
            $kpi['kpiCheckSumAll'] = ($kpiCheckSumAll == 0 ? $kpi['kpiSum2'] : "-");
            $kpi['scoring'] = ($kpiResult['result'][0]['through_trial'] == 2 ? 50 : 70);
            $kpi['through_trial'] = $kpiResult['result'][0]['through_trial'];
        }

        return $kpi;
    }

    function reportCPC1($cpcResult)
    {  // ใช้กับไฟล์ ajax-evaluation-result-year.php
        if ($cpcResult['success'] === true) {
            $cpcSum2 = null;
            $cpcSum2_user = null;
            $cpcSumWeight = null;
            $cpcCheckSumAll = 0;
            $cpcCheckSumAll_user = 0;
            $cpcCheckSum_user = 0;
            $arr = array();
            $cpc = array();

            foreach ($cpcResult['result'] as $key => $value) {
                $a =  array($value['cpc_accept1'], $value['cpc_accept2'], $value['cpc_accept3'], $value['cpc_accept4'], $value['cpc_accept5']);
                $b =  array($value['cpc_score1'], $value['cpc_score2'], $value['cpc_score3'], $value['cpc_score4'], $value['cpc_score5']);
                $total = $this->getTotal($value['cpc_divisor'], $a);
                // echo $value['cpc_divisor']."<br>";
                $total_user = $this->getTotal($value['cpc_divisor'], $b);
                if ($value['cpc_accept1'] === null or $value['cpc_accept2'] === null or $value['cpc_accept3'] === null or $value['cpc_accept4'] === null or $value['cpc_accept5'] === null) {
                    $totalShow = "";
                    $cpcCheckSumAll++;
                } else {
                    $totalShow = $total;
                }
                if ($value['cpc_score1'] === null or $value['cpc_score2'] === null or $value['cpc_score3'] === null or $value['cpc_score4'] === null or $value['cpc_score5'] === null) {
                    $cpcCheckSumAll_user++;
                    $cpcCheckSum_user = 1;
                }
                $sum1 = (($total * $value['cpc_weight'] * 20) / 100);
                $cpcSum2 = $cpcSum2 + $sum1;
                //-----------
                $sum1_user = (($total_user * $value['cpc_weight'] * 20) / 100);
                $cpcSum2_user = $cpcSum2_user + $sum1_user;

                if ($value['question_type'] == 1 or  $value['question_type'] == 2 or $value['question_type'] == 3) {
                    $cpcSumWeight = $cpcSumWeight + $value['cpc_weight'];
                }

                $arr = array(
                    "cpc_score_id" => $value['cpc_score_id'],
                    "question_no" => $value['question_no'],
                    "question_code" => $value['question_code'],
                    "question_title" => $value['question_title'],
                    "cpc_weight" => $value['cpc_weight'],
                    "total_user" => round($total_user, 2),
                    "total" => ($cpcCheckSumAll > 0 ? "-" : round($total, 2)),
                    "total_" => ($cpcCheckSumAll > 0 ? NULL : round($total, 2)),
                    "sum1" => ($cpcCheckSumAll > 0 ? "-" : round($sum1, 2)),
                    "sum1_" => ($cpcCheckSumAll > 0 ? NULL : round($sum1, 2)),
                    "sum1_user" => ($cpcCheckSum_user == 1 ? "-" : round($sum1_user, 2))
                );
                $cpc['text'][] = $arr;
                $cpcCheckSum_user = 0;
            }
            $cpc['per_cardno'] = $value['per_cardno'];
            $cpc['cpcSumWeight'] = ($cpcSumWeight != "" ? $cpcSumWeight : "-");
            $cpc['cpcSumWeight_'] = ($cpcSumWeight != "" ? $cpcSumWeight : NULL);
            $cpc['cpcSum2'] = ($cpcCheckSumAll > 0 && $cpcSum2 == 0 ? "-" : round($cpcSum2, 2));  //ถ้ายังยืนยันผลไม่เสร็จให้ส่งค่าเป็น -  ใช้กับ report
            $cpc['cpcSum2_'] = ($cpcCheckSumAll > 0 && $cpcSum2 == 0 ? null : round($cpcSum2, 2));  //ถ้ายังยืนยันผลไม่เสร็จให้ส่งค่าเป็น NULL 
            //----user -----
            $cpc['cpcSum2_user'] = ($cpcCheckSumAll_user > 0 && $cpcSum2_user == 0 ? "-" : round($cpcSum2_user, 2));
            $cpc['scoring'] = ($value['through_trial'] == 2 ? 50 : 30);
            $cpc['through_trial'] = $value['through_trial'];
            // $cpc['cpcSum2_user'] = $cpcCheckSumAll_user ;
        }

        return $cpc;
    }

    function cpcResultUpdate($cpc, $resultYear)
    {
        $success = array(
            'success' => NULL,
            'error' => NULL
        );

        $cpc_score = $resultYear['cpc_score'];
        $years = $resultYear['table_year'];
        $cpc_score_result = $resultYear['cpc_score_result'];
        $log_ = array();

        if (array_key_exists('text', $cpc)) {
            if ($cpc['through_trial'] == 1) {
                $cpcScoring = 30;
            } elseif ($cpc['through_trial'] == 2) {
                $cpcScoring = 50;
            } else {
                $cpcScoring = 30;
            }
            foreach ($cpc['text'] as $cpc_key => $cpc_value) {
                // echo var_dump($cpc_value['sum1_']);
                try {
                    $sqlUpdate = "UPDATE $cpc_score SET `total_head` = :total , 
                                                    `sum_head` = :sum_head 
                                                WHERE `cpc_score_id` = :cpc_score_id ";
                    $stmUpdateCPC = $this->conn->prepare($sqlUpdate);

                    $stmUpdateCPC->bindParam(":total", $cpc_value['total_']);
                    $stmUpdateCPC->bindParam(":sum_head", $cpc_value['sum1_']);

                    $stmUpdateCPC->bindParam(":cpc_score_id", $cpc_value['cpc_score_id']);

                    $stmUpdateCPC->execute();
                } catch (\Exception $e) {
                    $errUpdate = $e->getMessage();

                    $log_['per_cardno'] = $cpc_value['per_cardno'];
                    $log_['id'] = $cpc_value['cpc_score_id'];
                    $log_['error'] = $cpc_score . " " . $errUpdate;
                    $log[] = $log_;
                    $log_ = [];
                }
            } // end foreach


            try {

                $sqlCheckCpcResult = "SELECT COUNT(*) FROM $cpc_score_result WHERE `per_cardno` = :per_cardno";
                $stmCheckCpcResult = $this->conn->prepare($sqlCheckCpcResult);
                $stmCheckCpcResult->bindParam(":per_cardno", $cpc['per_cardno']);
                $stmCheckCpcResult->execute();

                if ($stmCheckCpcResult->fetchAll(PDO::FETCH_ASSOC)) {
                    $sqlScoreResultUpdate = "UPDATE $cpc_score_result SET 
                                        `cpc_score_result_yourself` = :cpcSum2_user,
                                        `cpc_score_result_head` = :cpcSum2_,
                                        `cpc_sum_weight` = :cpc_sum_weight,
                                        `scoring` = :scoring ,
                                        `timestamp` = current_timestamp
                                    WHERE  per_cardno = :per_cardno";

                    $stmScoreUpdate = $this->conn->prepare($sqlScoreResultUpdate);
                    $stmScoreUpdate->bindParam(":cpcSum2_user", $cpc['cpcSum2_user']);
                    $stmScoreUpdate->bindParam(":cpcSum2_", $cpc['cpcSum2_']);
                    $stmScoreUpdate->bindParam(":cpc_sum_weight", $cpc['cpcSumWeight_']);
                    $stmScoreUpdate->bindParam(":scoring", $cpcScoring);
                    $stmScoreUpdate->bindParam(":per_cardno", $cpc['per_cardno']);

                    $stmScoreUpdate->execute();
                } else {
                    $sqlScoreResultInsert = "INSERT INTO $cpc_score_result (`per_cardno`,
                                                                        `cpc_score_result_yourself`,
                                                                        `cpc_score_result_head`,
                                                                        `cpc_sum_weight`,
                                                                        `scoring`,
                                                                        `years`,
                                                                        `timestamp`
                                                                        ) VALUES (
                                                                            :per_cardno,
                                                                            :cpcSum2_user,
                                                                            :cpcSum2_,
                                                                            :cpc_sum_weight,
                                                                            :scoring,
                                                                            :years,
                                                                            current_timestamp
                                                                        )";
                    $stmScoreInsert = $this->conn->prepare($sqlScoreResultInsert);
                    $stmScoreInsert->bindParam(":per_cardno", $cpc['per_cardno']);
                    $stmScoreInsert->bindParam(":cpcSum2_user", $cpc['cpcSum2_user']);
                    $stmScoreInsert->bindParam(":cpcSum2_", $cpc['cpcSum2_']);
                    $stmScoreInsert->bindParam(":cpc_sum_weight", $cpc['cpcSumWeight_']);
                    $stmScoreInsert->bindParam(":scoring", $cpcScoring);
                    $stmScoreInsert->bindParam(":years", $years);
                    $stmScoreInsert->execute();
                }
            } catch (\Exception $e) {
                $errUpdate = $e->getMessage();

                $log_['per_cardno'] = $cpc['per_cardno'];
                $log_['id'] = NULL;
                $log_['error'] = $cpc_score_result . " " . $errUpdate;
                $log[] = $log_;
                $log_ = [];
            }
        }  //end if 
        if (count($log_) > 0) {
            $success['success'] = false;
            $success['error'] = $log_;
        } else {
            $success['success'] = true;
            $suscess['error'] = $log_;
        }

        return $success;
    }

    function kpiResultUpdate($kpi, $resultYear)
    {
        $success = array(
            'success' => NULL,
            'error' => NULL
        );

        $kpi_score = $resultYear['kpi_score'];
        $years = $resultYear['table_year'];
        $kpi_score_result = $resultYear['kpi_score_result'];
        $log_ = array();
        if (array_key_exists('text', $kpi)) {
            if ($kpi['through_trial'] == 1) {

                $kpiScoring = 70;
            } elseif ($kpi['through_trial'] == 2) {

                $kpiScoring = 50;
            } else {

                $kpiScoring = 70;
            }

            foreach ($kpi['text'] as $keyKPI => $valueKPI) {
                try {

                    $sqlKPIUpdate = "UPDATE $kpi_score SET `kpi_sum_head` = :kpi_sum_head 
                                        WHERE  kpi_score_id = :kpi_score_id ";
                    $stmKPIUpdate = $this->conn->prepare($sqlKPIUpdate);
                    $stmKPIUpdate->bindParam(":kpi_sum_head", $valueKPI['kpiSum_']);
                    $stmKPIUpdate->bindParam(":kpi_score_id", $valueKPI['kpi_score_id']);

                    $stmKPIUpdate->execute();
                } catch (\Exception $e) {
                    $errUpdate = $e->getMessage();

                    $log_['per_cardno'] = $kpi['per_cardno'];
                    $log_['id'] = $valueKPI['kpi_score_id'];
                    $log_['error'] = $kpi_score . " " . $errUpdate;
                    $log[] = $log_;
                    $log_ = [];
                }
            }

            try {

                $sqlCheckKpiResult = "SELECT COUNT(*) FROM $kpi_score_result WHERE `per_cardno` = :per_cardno";
                $stmCheckKpiResult = $this->conn->prepare($sqlCheckKpiResult);
                $stmCheckKpiResult->bindParam(":per_cardno", $kpi['per_cardno']);
                $stmCheckKpiResult->execute();

                if ($stmCheckKpiResult->fetchAll(PDO::FETCH_ASSOC)) {
                    $sqlKPIResultUpdate = "UPDATE $kpi_score_result SET 
                                        `kpi_weight_sum` = :kpi_weight_sum ,
                                        `kpi_score_result` = :kpi_score_result,
                                        `scoring` = :scoring,
                                        `time_stamp` = current_timestamp
                                        WHERE `per_cardno` = :per_cardno ";
                    $stmKPIResultUpdate = $this->conn->prepare($sqlKPIResultUpdate);
                    $stmKPIResultUpdate->bindParam(":kpi_score_result", $kpi['kpiSum2_']);
                    $stmKPIResultUpdate->bindParam(":kpi_weight_sum", $kpi['kpiWeightSum_']);
                    $stmKPIResultUpdate->bindParam(":scoring", $kpiScoring);
                    $stmKPIResultUpdate->bindParam(":per_cardno", $kpi['per_cardno']);

                    $stmKPIResultUpdate->execute();
                } else {
                    $sqlKpiResultInsert = "INSERT INTO $kpi_score_result  (
                                                                         `per_cardno`,
                                                                         `kpi_score_result`,
                                                                         `kpi_weight_sum`,
                                                                         `scoring`,
                                                                         `years`,
                                                                         `time_stamp`
                                                                        ) VALUES (
                                                                            :per_cardno,
                                                                            :kpi_score_result,
                                                                            :kpi_weight_sum,
                                                                            :scoring,
                                                                            :years,
                                                                            current_timestamp
                                                                        )";
                    $stmKpiResultInsert = $this->conn->prepare($sqlKpiResultInsert);
                    $stmKpiResultInsert->bindParam(":per_cardno", $kpi['per_cardno']);
                    $stmKpiResultInsert->bindParam(":kpi_score_result", $kpi['kpiSum2_']);
                    $stmKpiResultInsert->bindParam(":kpi_weight_sum", $kpi['kpiWeightSum_']);
                    $stmKpiResultInsert->bindParam(":scoring", $kpiScoring);
                    $stmKpiResultInsert->bindParam(":years", $years);
                    $stmKpiResultInsert->execute();
                }
            } catch (\Exception $e) {
                $errUpdate = $e->getMessage();

                $log_['per_cardno'] = $kpi['per_cardno'];
                $log_['id'] = NULL;
                $log_['error'] = $kpi_score_result . " " . $errUpdate;
                $log[] = $log_;
                $log_ = [];
            }
        } //end if

        if (count($log_) > 0) {
            $success['success'] = false;
            $success['error'] = $log_;
        } else {
            $success['success'] = true;
            $success['error'] = $log_;
            // $success['error'] = $kpi['kpiWeightSum_'];
        }

        return $success;
    }


    function  cpcCalculate_new($cpcResult)
    {   //ฟังก์ชั่นคำนวณใหม่  ทำให้ใช้ได้ ทั้ง คะแนนปัจจุบัน แล้วก็ ตารางเก่า

        $success = array();
        $r = array();
        $rr = array();

        if ($cpcResult['success'] === true) {
            $cpcSum2 = 0;
            $CPCSumWeight = 0;
            $success['success'] = true;


            foreach ($cpcResult['result'] as $key => $value) {
                $a = array($value['cpc_accept1'], $value['cpc_accept2'], $value['cpc_accept3'], $value['cpc_accept4'], $value['cpc_accept5']);
                $total = $this->getTotal($value['cpc_divisor'], $a);
                $sum1 = (($total * $value['cpc_weight'] * 20) / 100);
                $cpcSum2 = $cpcSum2 + $sum1;
                $CPCSumWeight = $CPCSumWeight + $value['cpc_weight'];


                $r['question_code'] = $value['question_code'];
                $r['question_title'] = $value['question_title'];
                $r['cpc_divisor'] = $value['cpc_divisor'];
                $r['total'] = round($total, 2);
                $r['cpc_weight'] = $value['cpc_weight'];
                $r['sum1']     = round($sum1, 2);

                array_push($rr, $r);
            }
            $success['cpc'] = $rr;
            $success['CPCsumWeight'] = $CPCSumWeight;
            $success['cpcSum2'] = round($cpcSum2, 2);
        }

        return $success;
    }


    // Competency Gap  คำนวณการติด Gap
    function cal_gap_chart($tableCPC)
    {  //แสดงกราฟ หน้า view-evalution-result-cpc.php
        $setData = array();
        foreach ($tableCPC['result'] as $key => $value) {
            $a =  array($value['cpc_accept1'], $value['cpc_accept2'], $value['cpc_accept3'], $value['cpc_accept4'], $value['cpc_accept5']);

            // $point = $this->getPoint($a);
            // $total = $this->getTotal($value['cpc_divisor'],$a);

            $t = intval($value['question_type']);
            if ($t === 1 || $t === 2 || $t === 3) {

                $point = $this->getTotal($value['cpc_divisor'], $a); // คำนวณแบบเดียวกับคะแนน 

                if ($point >= 3) {
                    $pointEqual = $value['cpc_divisor'];
                    $pointOverGap = $this->getTotal_seriesType($value['cpc_divisor'], $a);
                } else {
                    $pointEqual = 0;
                    $pointOverGap = 0;
                }

                $p = $pointEqual;  // คำนวณหาส่วนต่าง 
                if ($point === null) {
                    $result_minus = null;
                } else {
                    // $result_minus = $point-$value['cpc_divisor'];
                    // $result_minus = floor($point)-3;  // ปัดเศษลงอย่างเดียว ลบด้วย 3 เพราะคะแนน 0-5 ต้องผ่านที่ 3 ขึ้นไป
                    $result_minus = $pointEqual - $value['cpc_divisor'];
                }

                if ($point != false) {
                    $pointEqual_OverGap =  $pointEqual + $pointOverGap;
                    $gap_status = (($pointEqual_OverGap - $value['cpc_divisor']) >= 0 ? 0 : 1);
                } elseif ($point == false) {
                    $pointEqual_OverGap =  NULL;
                    $gap_status = NULL;
                }
            } elseif ($t === 4 || $t === 5 || $t === 6) {

                $point456 = $this->getPointForType456($a);

                $p = $point456;
                if ($point456 >=  $value['cpc_divisor']) {
                    $pointEqual = $value['cpc_divisor'];
                    $pointOverGap = $point456 - $pointEqual;
                } elseif ($point456 <  $value['cpc_divisor']) {
                    $pointEqual = $point456;
                    $pointOverGap = 0;
                }

                if ($p === null) {
                    $result_minus = null;
                } else {
                    $result_minus = $point456 - $value['cpc_divisor'];
                }

                if ($point456 != false) {
                    $pointEqual_OverGap =  $pointEqual + $pointOverGap;
                    $gap_status = (($pointEqual_OverGap - $value['cpc_divisor']) >= 0 ? 0 : 1);
                } elseif ($point456 == false) {
                    $gap_status = NULL;
                    $pointEqual_OverGap =  NULL;
                }
            }
            (($t === 4 || $t === 5 || $t === 6) ?  $w = "-" :  $w = $value['cpc_weight'] . "%");



            $setData[$key] =  array(
                'per_cardno' => $value['per_cardno'],
                'cpc_score_id' =>  $value['cpc_score_id'],
                'question_no' => $value['question_no'],
                'question_code' => $value['question_code'],
                'question_type' => $value['question_type'],
                'question_title' => $value['question_title'],
                'cpc_divisor' => $value['cpc_divisor'],
                'years' => $value['years'],
                'question_type' => $t,
                'cpc_weight' => $w,
                'point' => $p,
                'pointEqual' => $pointEqual,
                'pointOverGap' => $pointOverGap,
                'pointEqual_OverGap' => $pointEqual_OverGap,
                'gap_status' => $gap_status,
                'result_minus' => $result_minus
            );
        }

        //point = ระดับที่ได้ 
        //pointEqual = ค่าคาดหวัง 
        //pointOverGap = ส่วนต่าง 
        //pointEqual_OverGap = จำนวนข้อที่ได้ ตั้งแต่ 1-5 
        //result_minus = ผลลัพธ์ส่วนต่าง 
        // 'gap_status' = สถานะการติดgap,

        return $setData;
    }

    // function  gapResultUpdate($arrGap) {
    //     $success = array('success' => NULL,
    //                     'error' => NULL );
    //     $log_ = array();

    //     if (count($arrGap) > 0 ) {

    //         foreach ($arrGap as $key => $gap) {
    //             try {
    //                 $sql = ""
    //             } catch (\Exception $e) {
    //                 //throw $th;
    //             }

    //         }





    //     }

    // }

    // cal_gap ใช้งานในไฟล์ view-evatuation-result.php
    function cal_gap($setData, $idpScoreTable)
    { // cal_gap_chart->cal_gap คำนวณหา gap โดยใช้ข้อมูลจาก cal_gap_chart
        $gapArr = array();
        $db = new DbConn;
        $success = array(
            'success' => NULL,
            'count' => 0,
            'gap' => NULL,
            'msg' => NULL
        );

        foreach ($setData as $key => $value) {
            if ($value['result_minus'] < 0) {

                try {
                    $sql = "SELECT * From $idpScoreTable  WHERE cpc_score_id = :cpc_score_id AND years = :years ";
                    $stm = $db->conn->prepare($sql);
                    $stm->bindParam(":cpc_score_id", $value['cpc_score_id']);
                    $stm->bindParam(":years", $value['years']);
                    $stm->execute();
                    $result = $stm->fetchAll();

                    $count = $stm->rowCount();
                    if ($count == 1) {
                        $setData[$key]['idp_id'] = $result[0]['idp_id'];
                        $setData[$key]['idp_type'] = $result[0]['idp_type'];

                        $setData[$key]['idp_title'] = $result[0]['idp_title'];
                        $setData[$key]['idp_training_method'] = $result[0]['idp_training_method'];
                        $setData[$key]['idp_training_hour'] = $result[0]['idp_training_hour'];
                        $setData[$key]['idp_training_hour_success'] = $result[0]['idp_training_hour_success'];
                        $setData[$key]['idp_accept'] = $result[0]['idp_accept'];
                        $setData[$key]['idp_who_is_accept'] = $result[0]['idp_who_is_accept'];
                    } elseif ($count == 0) {
                        $setData[$key]['idp_id'] = null;
                        $setData[$key]['idp_type'] = $setData[$key]['question_type'];

                        $setData[$key]['idp_title'] = null;
                        $setData[$key]['idp_training_method'] = null;
                        $setData[$key]['idp_training_hour'] = null;
                        $setData[$key]['idp_training_hour_success'] = null;
                        $setData[$key]['idp_accept'] = null;
                        $setData[$key]['idp_who_is_accept'] = null;
                    } else if ($count > 1) {
                        $setData[$key]['idp_id'] = false;
                        $setData[$key]['idp_type'] = false;

                        $setData[$key]['idp_title'] = false;
                        $setData[$key]['idp_training_method'] = false;
                        $setData[$key]['idp_training_hour'] = false;
                        $setData[$key]['idp_training_hour_success'] = false;
                        $setData[$key]['idp_accept'] = false;
                        $setData[$key]['idp_who_is_accept'] = false;
                    }
                } catch (Exception $e) {
                    $success['success'] = false;
                    $success['msg'] = $e->getMessage();
                }
                $gapArr[] = $setData[$key];
            }
            $success['gap'] = $gapArr;
            $success['success'] = true;
        }
        //  echo "<pre>";
        // print_r($gapArr);
        // echo "</pre>";
        return $success;
    }

    function gapUpdateByid($point_result, $gap_status, $cpc_score_id, $cpcScoreTable)
    {
        //point_result =   pointEqual_OverGap จำนวนข้อที่ได้ ตั้งแต่ 1-5
        //gap_status = 0 ไม่ติดgap | 1 ติดgap
        $success = array();
        $c = 0;
        try {
            $sqlUpdate = "UPDATE $cpcScoreTable SET `point_result` = :point_result , `gap_status` = :gap_status WHERE  `cpc_score_id` = :cpc_score_id ";
            $stmUpdate = $this->conn->prepare($sqlUpdate);
            $stmUpdate->bindParam(":point_result", $point_result);
            $stmUpdate->bindParam(":gap_status", $gap_status);
            $stmUpdate->bindParam(":cpc_score_id", $cpc_score_id);
            $stmUpdate->execute();

            $success['success'] = true;
            $success['msg'] = $stmUpdate->rowCount() . " row";
        } catch (\Exception $e) {
            $success['success'] = false;
            $success['msg'] = $e->getMessage();
        }

        return $success;
    }


    // การเพ่ิมจุดแข็ง
    function cal_idp($per_cardno, $years, $idpScoreTable)
    {  //คำนวณหา gap ที่เป็นการเพิ่มเองโดยไม่ได้อ้างอิงกับตัวที่ตก หรือที่เรียกกว่า การพัฒนาตัวเองเพื่อเพิ่มจุดแข็ง 
        // และจะได้ตัวที่มีการล้างข้อมูล แล้วมีการคำนวณคะแนนใหม่ ซึ่งที่จริงกันต้องถูกลบออกให้ตอนที่มีการล้างข้อมูลด้วย 
        $db = new DbConn;
        $success = array(
            'success' => NULL,
            'count' => 0,
            'idp' => NULL,
            'msg' => ''
        );

        try {
            $sql = "SELECT `$idpScoreTable`.*,
                            `cpc_question`.`question_title`,
                            `cpc_question`.`question_code`
                FROM `$idpScoreTable`
                LEFT JOIN `cpc_question` ON `cpc_question`.question_no = `$idpScoreTable`.question_no
                WHERE `$idpScoreTable`.per_cardno = :per_cardno AND `$idpScoreTable`.years = :years AND `$idpScoreTable`.cpc_score_id is null ";
            // echo $sql;
            $stm = $db->conn->prepare($sql);
            $stm->bindParam(":per_cardno", $per_cardno);
            $stm->bindParam(":years", $years);
            $stm->execute();
            $result = $stm->fetchAll(PDO::FETCH_ASSOC);
            $count = $stm->rowCount();
            $success['success'] = true;
            $success['count'] = $count;
            $success['idp'] = $result;
        } catch (Exception $e) {
            $success['msg'] = $e->getMessage();
            $success['success'] = false;
        }
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
        return $success;
    }

    function CPCscoreDone($per_cardno, $tableName, $years)
    {  //เช็คว่าคนนั้นทำประเมินเสร็จยัง
        $db = new DbConn;
        $success  = array(
            'success' => null,
            'result' => null,
            'msg' => null
        );
        try {
            $sql = "SELECT CASE 
                        WHEN `cpc_accept1` IS NULL
                        OR  `cpc_accept2` IS NULL
                        OR  `cpc_accept3` IS NULL
                        OR  `cpc_accept4` IS NULL
                        OR  `cpc_accept5` IS NULL
                        THEN 'false' ELSE 'true' 
                        END as checkresult
                        FROM " . $tableName . "
                        WHERE   `per_cardno` = :per_cardno  AND years = :years ";

            $stm = $db->conn->prepare($sql);
            $stm->bindParam(":per_cardno", $per_cardno);
            $stm->bindParam(":years", $years);
            $stm->execute();
            $result = $stm->fetchAll(PDO::FETCH_COLUMN);

            if ($stm->rowCount() > 0) {
                $success  = array(
                    'success' => true,
                    'result' => $this->countFalse($result),
                    'msg' => null
                );
            } elseif ($stm->rowCount() == 0) {
                $success  = array(
                    'success' => true,
                    'result' => null,
                    'msg' => null
                );
            }
        } catch (\Exception $e) {

            $success  = array(
                'success' => false,
                'result' => null,
                'msg' => $e->getMessage()
            );
        }

        return $success;
    }

    function KPIscoreDone($per_cardno, $tableName, $years)
    { //เช็คว่าคนนั้นทำประเมินเสร็จยัง
        $db = new DbConn;
        $err = "";
        $success  = array(
            'success' => null,
            'result' => null,
            'checkaccept' => null,
            'msg' => null
        );

        try {
            $sql = "SELECT 
                        CASE 
                            WHEN `kpi_score` IS NULL 
                            THEN 'false' ELSE 'true' 
                        END as checkresult
                        FROM $tableName
                        WHERE   `per_cardno` = :per_cardno  AND years = :years AND soft_delete = 0";
            $stm = $db->conn->prepare($sql);
            $stm->bindParam(":per_cardno", $per_cardno);
            $stm->bindParam(":years", $years);
            $stm->execute();
            $result = $stm->fetchAll(PDO::FETCH_COLUMN);

            if ($stm->rowCount() > 0) {
                $success['result'] = $this->countFalse($result);
            } elseif ($stm->rowCount() == 0) {
                $success['result'] = null;
            }
        } catch (\Exception $e) {
            $err = $e->getMessage();
        }

        try {
            $sql = "SELECT 
                        CASE 
                            WHEN `kpi_accept` IS NULL 
                            THEN 'false' ELSE 'true' 
                        END as checkaccept 
                        FROM $tableName
                        WHERE   `per_cardno` = :per_cardno  AND years = :years AND soft_delete = 0";

            $stmKpi_accept = $db->conn->prepare($sql);
            $stmKpi_accept->bindParam(":per_cardno", $per_cardno);
            $stmKpi_accept->bindParam(":years", $years);
            $stmKpi_accept->execute();
            $resultKpi_accept = $stmKpi_accept->fetchAll(PDO::FETCH_COLUMN);

            if ($stmKpi_accept->rowCount() > 0) {
                $success['checkaccept'] = $this->countFalse($resultKpi_accept);
            } elseif ($stmKpi_accept->rowCount() == 0) {
                $success['checkaccept'] = null;
            }
        } catch (\Exception $e) {
            $err = $e->getMessage();
        }

        if ($err != "") {
            $success['success'] = null;
            $success['msg'] = $err;
        } else {
            $success['success'] = true;
        }

        return $success;

        // Array
        // (
        //     [success] => 0,1  query ข้อมูลไม่ err
        //     [result] => 0 หมายถึง ประเมินตนเองครบแล้ว  ถ้าไม่ครบจะ แสดงจำนวนที่ไม่ครบ
        //     [checkaccept] => 0 หมายถึง ยืนยันผลครบแล้ว  ถ้าไม่ครบจะ แสดงจำนวนที่ไม่ครบ
        //     [msg] => messager กรณี err 
        // )

    }

    function cpcUpdateScoreResult($cpc_score_result_table, $per_cardno, $cpc_score_result_head, $cpc_score_result_yourself, $scoring, $years, $cpc_sum_weight)
    {
        $db = new DbConn;
        $success  = array(
            'success' => null,
            'result' => null,
            'msg' => null
        );
        $err = "";
        try {

            $sql_update = "UPDATE $cpc_score_result_table SET `cpc_score_result_head` = :cpc_score_result_head ,
                                                              `cpc_score_result_yourself`  = :cpc_score_result_yourself,
                                                             `scoring` = :scoring ,
                                                            `cpc_sum_weight` = :cpc_sum_weight,
                                                            `timestamp` = current_timestamp
                                WHERE `per_cardno` = :per_cardno AND `years` = :years  ";

            $stm_update = $db->conn->prepare($sql_update);
            $stm_update->bindParam(":cpc_score_result_head", $cpc_score_result_head);
            $stm_update->bindParam(":cpc_score_result_yourself", $cpc_score_result_yourself);
            $stm_update->bindParam(":scoring", $scoring);
            $stm_update->bindParam(":cpc_sum_weight", $cpc_sum_weight);
            $stm_update->bindParam(":per_cardno", $per_cardno);
            $stm_update->bindParam(":years", $years);
            $stm_update->execute();

            if ($stm_update->rowCount() == 0) {
                $sql_insert = "INSERT INTO $cpc_score_result_table (`per_cardno`,
                                                                    `cpc_score_result_head`,
                                                                    `cpc_score_result_yourself`,
                                                                    `scoring`,
                                                                    `cpc_sum_weight`,
                                                                    `years`,
                                                                    `timestamp`) 
                                                            VALUES (:per_cardno,
                                                                    :cpc_score_result_head,
                                                                    :cpc_score_result_yourself,
                                                                    :scoring,
                                                                    :cpc_sum_weight,
                                                                    :years,
                                                                    current_timestamp)  ";
                $stm_insert = $db->conn->prepare($sql_insert);
                $stm_insert->bindParam(":cpc_score_result_head", $cpc_score_result_head);
                $stm_insert->bindParam(":cpc_score_result_yourself", $cpc_score_result_yourself);
                $stm_insert->bindParam(":scoring", $scoring);
                $stm_insert->bindParam(":cpc_sum_weight", $cpc_sum_weight);
                $stm_insert->bindParam(":per_cardno", $per_cardno);
                $stm_insert->bindParam(":years", $years);
                $stm_insert->execute();

                $success  = array(
                    'success' => true,
                    'result' => "insert",
                    'msg' => null
                );
            } else {
                $success  = array(
                    'success' => true,
                    'result' => "update",
                    'msg' => null
                );
            }
        } catch (Exception $e) {
            $err = $e->getMessage();
        }
        if ($err != "") {
            $success  = array(
                'success' => null,
                'result' => null,
                'msg' => $err
            );
        }

        return $success;
    }

    function KPIupdateScoreResult($kpi_score_result_table, $per_cardno, $kpi_score_result, $scoring, $years, $kpi_weight_sum)
    {
        $db = new DbConn;
        $success  = array(
            'success' => null,
            'result' => null,
            'msg' => null
        );
        $err = "";

        try {
            $sql_update = "UPDATE $kpi_score_result_table SET `kpi_score_result` = :kpi_score_result ,
                                                               `scoring` = :scoring,
                                                               `kpi_weight_sum` = :kpi_weight_sum,
                                                               `time_stamp` = current_timestamp 
                                                        WHERE `per_cardno` = :per_cardno AND `years` = :years  ";

            $stm_update = $db->conn->prepare($sql_update);
            $stm_update->bindParam(":kpi_score_result", $kpi_score_result);
            $stm_update->bindParam(":kpi_weight_sum", $kpi_weight_sum);
            $stm_update->bindParam(":scoring", $scoring);
            $stm_update->bindParam(":per_cardno", $per_cardno);
            $stm_update->bindParam(":years", $years);
            $stm_update->execute();

            if ($stm_update->rowCount() == 0) {
                $sql_insert = "INSERT INTO $kpi_score_result_table  ( `per_cardno`, 
                                                                        `kpi_score_result` , 
                                                                        `scoring` , 
                                                                        `kpi_weight_sum`,
                                                                        `years`,
                                                                        `time_stamp`
                                                                    ) 
                                                            VALUES (:per_cardno , 
                                                            :kpi_score_result, 
                                                            :scoring , 
                                                            :kpi_weight_sum,
                                                            :years ,
                                                            current_timestamp)";
                $stm_insert = $db->conn->prepare($sql_insert);
                $stm_insert->bindParam(":per_cardno", $per_cardno);
                $stm_insert->bindParam(":kpi_score_result", $kpi_score_result);
                $stm_insert->bindParam(":kpi_weight_sum", $kpi_weight_sum);
                $stm_insert->bindParam(":scoring", $scoring);
                $stm_insert->bindParam(":years", $years);
                $stm_insert->execute();

                $success  = array(
                    'success' => true,
                    'result' => "insert",
                    'msg' => null
                );
            } elseif ($stm_update->rowCount() > 0) {
                $success  = array(
                    'success' => true,
                    'result' => "update",
                    'msg' => null
                );
            }
        } catch (\Exception $e) {
            $err = $e->getMessage();
        }

        if ($err != "") {
            $success  = array(
                'success' => null,
                'result' => null,
                'msg' => $err
            );
        }
        return $success;
    }

    function countFalse($value)
    {
        $checkCountArr = count(array_filter($value, function ($item_values) {
            if ($item_values == "false") {
                return true;
            }
            return false;
        }));

        return $checkCountArr;
    }

    function countTrue($value)
    {
        $checkCountArr = count(array_filter($value, function ($item_values) {
            if ($item_values == "true") {
                return true;
            }
            return false;
        }));

        return $checkCountArr;
    }



    function percent_complete($arr_per_cardno, $cpc_score_result_table, $kpi_score_result_table, $years)
    {

        $db = new DbConn;
        $success  = array(
            'success' => null,
            'personCount' => "",
            'cpcComplete' => "",
            'cpcTotal' => "",
            'kpiComplete' => "",
            'kpiTotal' => "",
            'percentComplete' => "",
            'msg' => null
        );
        $log = array();
        $err = "";
        if (count($arr_per_cardno) > 0) {
            $text = array_filter($arr_per_cardno);
            $sqlIN = implode(",", $text);

            try {
                $sql = "SELECT 
                            CASE 
                                WHEN `cpc_score_result_head` IS NULL 
                                THEN 'false' ELSE 'true' 
                            END AS checkresult
                        FROM $cpc_score_result_table 
                        WHERE `per_cardno` IN ($sqlIN) AND years =  :years AND soft_delete = 0
                                ";
                $stmCPC = $db->conn->prepare($sql);
                $stmCPC->bindParam(":years", $years);
                $stmCPC->execute();

                $cpcResult = $stmCPC->fetchAll(PDO::FETCH_COLUMN);
                $cpcTotal = $stmCPC->rowCount();
                $cpcCount = $this->countTrue($cpcResult);
            } catch (\Exception $e) {
                $err = $e->getMessage();
                $log[] = $err . "cpc_score_result";
            }

            try {
                $sql = "SELECT 
                            CASE 
                                WHEN `kpi_score_result` IS NULL 
                                THEN 'false' ELSE 'true' 
                            END AS checkresult
                        FROM $kpi_score_result_table 
                        WHERE `per_cardno` IN ($sqlIN) AND years =  :years AND soft_delete = 0
                                ";

                $stmKPI = $db->conn->prepare($sql);
                $stmKPI->bindParam(":years", $years);
                $stmKPI->execute();
                $kpiResult = $stmKPI->fetchAll(PDO::FETCH_COLUMN);
                $kpiTotal = $stmKPI->rowCount();
                $kpiCount = $this->countTrue($kpiResult);
            } catch (\Exception $e) {
                $err = $e->getMessage();
                $log[] = $err . "kpi_score_result";
            }

            if ($err != "") {
                $success  = array(
                    'success' => null,
                    'personCount' => null,
                    'cpcComplete' => null,
                    'cpcTotal' => null,
                    'kpiComplete' => null,
                    'kpiTotal' => null,
                    'percentComplete' => null,
                    'msg' => $sqlIN
                );
            } else {
                $per_cardnoCount = count($arr_per_cardno);
                //  @$sum = floor((($cpcCount+$kpiCount) / ($kpiTotal + $cpcTotal) ) * 100)    ;
                @$sum = floor((($cpcCount + $kpiCount) / ($per_cardnoCount * 2)) * 100);


                $success  = array(
                    'success' => true,
                    'personCount' => $per_cardnoCount,
                    'cpcComplete' => $cpcCount,
                    'cpcTotal' => $cpcTotal,
                    'kpiComplete' => $kpiCount,
                    'kpiTotal' => $kpiTotal,
                    'percentComplete' => @$sum,
                    'msg' => $log
                );
            }
        }

        return $success;
    }

    function sum_cpc_kpi($CPCscore, $KPIscore, $cpc_ratio, $kpi_ratio)
    {
        $sum = ($CPCscore * ($cpc_ratio / 100)) + ($KPIscore * ($kpi_ratio / 100));
        return round($sum, 2);
    }
}
// round($sum,0,PHP_ROUND_HALF_DOWN)