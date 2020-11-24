<?php
class myClass extends DbConn
{
    function callYear()
    {  // ปีปัจจุบัน ที่จะประเมิน
        $success = array(
            'data' => null,
            'success' => null,
            'msg' => null
        );

        try {
            $sql = "SELECT * FROM table_year WHERE default_status = '1' AND use_status = '1' ";
            $stm = $this->conn->prepare($sql);
            $stm->execute();
            $result =  $stm->fetchAll(PDO::FETCH_ASSOC);
            $success['data'] = $result[0];
            $success['success'] = true;
        } catch (\Exception $e) {
            $success['msg'] = $e->getMessage();
            $success['success'] = false;
        }

        return  $success;
    }

    function callYears()
    {  // select ปี ทุกปีที่มีในระบบ
        $success = array(
            'data' => null,
            'success' => null,
            'msg' => null
        );

        try {
            $sql = "SELECT * FROM table_year WHERE   use_status = '1' ";
            $stm = $this->conn->prepare($sql);
            $stm->execute();
            $result =  $stm->fetchAll(PDO::FETCH_ASSOC);
            $success['data'] = $result;
            $success['success'] = true;
        } catch (\Exception $e) {
            $success['msg'] = $e->getMessage();
            $success['success'] = false;
        }

        return  $success;
    }

    function callYearsOld()
    {  // เลือกเฉพาะปีเก่า
        $success = array(
            'data' => null,
            'success' => null,
            'msg' => null
        );

        try {
            $sql = "SELECT * FROM table_year WHERE   use_status = '1' AND default_status is null ";
            $stm = $this->conn->prepare($sql);
            $stm->execute();
            $result =  $stm->fetchAll(PDO::FETCH_ASSOC);
            $success['data'] = $result;
            $success['success'] = true;
        } catch (\Exception $e) {
            $success['msg'] = $e->getMessage();
            $success['success'] = false;
        }

        return  $success;
    }

    function callYearByID($id)
    {  // select โดยใช้ table_id
        $success = array(
            'data' => null,
            'success' => null,
            'msg' => null
        );
        try {
            $sqlYear  = "SELECT  * FROM `table_year` WHERE table_id = :selectYears ";
            $stmYear = $this->conn->prepare($sqlYear);
            $stmYear->bindParam(":selectYears", $id);
            $stmYear->execute();
            $resultYear = $stmYear->fetchAll(PDO::FETCH_ASSOC);

            $success['data'] = $resultYear[0];
            $success['success'] = true;
        } catch (\Exception $e) {
            $success['msg'] = $e->getMessage();
            $success['success'] = false;
        }
        return  $success;
    }

    function callYearByTableYear($TableYear)
    {  // select โดยใช้ table_id
        $success = array(
            'data' => null,
            'success' => null,
            'msg' => null
        );
        try {
            $sqlYear  = "SELECT  * FROM `table_year` WHERE table_year = :table_year ";
            $stmYear = $this->conn->prepare($sqlYear);
            $stmYear->bindParam(":table_year", $TableYear);
            $stmYear->execute();
            $resultYear = $stmYear->fetchAll(PDO::FETCH_ASSOC);

            $success['data'] = $resultYear[0];
            $success['success'] = true;
        } catch (\Exception $e) {
            $success['msg'] = $e->getMessage();
            $success['success'] = false;
        }
        return  $success;
    }

    public function urlPic($idCard, $type = 1)
    {
        $file1 = "http://dpis.rid.go.th:8080/attachment/pic_personal/" . $idCard . "-001.jpg";
        $file2 = "http://dpis.rid.go.th:8080/attachment/pic_personal/" . $idCard . "-002.jpg";
        $file3 = "http://dpis.rid.go.th:8080/attachment/pic_personal/" . $idCard . "-003.jpg";
        $file_headers3 = @get_headers($file3);
        $file_headers2 = @get_headers($file2);
        $file_headers1 = @get_headers($file1);

        if ($file_headers3[0] != 'HTTP/1.1 404 Not Found') {
            return $file3;
        } elseif ($file_headers2[0] != 'HTTP/1.1 404 Not Found') {
            return $file2;
        } elseif ($file_headers1[0] != 'HTTP/1.1 404 Not Found') {
            return $file1;
        } else return "../external/images_profile/no-picture.png";
    }



    public function detail($idcard, $per_personal)
    {
        $db = new DbConn;


        $sql = "SELECT t1.*,
                        t2.pn_name as head_pn_name,
                        t2.per_name as head_per_name,
                        t2.per_surname as head_per_surname,
                        t2.per_cardno as head_per_cardno
                FROM " . $per_personal . " t1 
                LEFT JOIN " . $per_personal . " t2
                ON t1.head = t2.`per_cardno`
                where t1.per_cardno = :idcard";
        $stmt = $db->conn->prepare($sql);
        $stmt->bindParam(":idcard", $idcard);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) == 1) {

            foreach ($result as $row) {
                $p = $row;
                unset($row);
            }
            return $p;
        } else return false;
    }

    function pdoMultiInsert($tableName, $data)
    {
        try {
            $err = "";
            $success = array(
                'msg' => null,
                'success' => null
            );
            //Will contain SQL snippets.
            $rowsSQL = array();

            //Will contain the values that we need to bind.
            $toBind = array();

            //Get a list of column names to use in the SQL statement.
            $columnNames = array_keys($data[0]);

            //Loop through our $data array.
            foreach ($data as $arrayIndex => $row) {
                $params = array();
                foreach ($row as $columnName => $columnValue) {
                    $param = ":" . $columnName . $arrayIndex;
                    $params[] = $param;
                    $toBind[$param] = $columnValue;
                }
                $rowsSQL[] = "(" . implode(", ", $params) . ")";
            }

            //Construct our SQL statement
            $sql = "INSERT INTO `$tableName` (" . implode(", ", $columnNames) . ") VALUES " . implode(", ", $rowsSQL);

            //Prepare our PDO statement.
            $pdoStatement = $this->conn->prepare($sql);

            //Bind our values.
            foreach ($toBind as $param => $val) {
                $pdoStatement->bindValue($param, $val);
            }

            //Execute our statement (i.e. insert the data).
            $pdoStatement->execute();
        } catch (\Exception $e) {
            $err = $e->getMessage();
        }
        if ($err != "") {
            $success['msg'] = $err;
            $success['success'] = false;
        } else {
            $success['success'] = true;
        }
        return $success;
    }
}
