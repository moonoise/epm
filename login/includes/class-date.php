<?php
class dateCovert
{
    function shortMonth($mo)
    {
        if (substr($mo, 0, 1) == 0) {
            $mo = substr($mo, 1, 1);
        }

        $mm = array("ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
        return $mm[$mo - 1];
    }

    function fullMonth($mo)
    {
        if (substr($mo, 0, 1) == 0) {
            $mo = substr($mo, 1, 1);
        }

        $mm = array("มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม");
        return $mm[$mo - 1];
    }

    function covertMonthThaiToNumber($monthThai)
    {
        $ok = 0;
        $mm = array("มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม");
        for ($i = 0; $i < 12; $i++) {
            if (strcmp($monthThai, $mm[$i]) == 0) {
                $m = $i + 1;
                if ($m < 10) {
                    $m = "0" . $m;
                }

                $ok = 1;
                break;
            } //if
        } //for
        if ($ok == 0) {
            $m = "";
        }

        return $m;
    }

    function shortDateEngToThai($ymd)
    {
        $exp   = explode('-', $ymd);
        $y     = $exp[0] + 543;
        $yy    = substr($y, 2, 2);
        $m     = $exp[1];
        $d     = $exp[2];
        $month = $this->shortMonth($m);
        if (substr($d, 0, 1) == 0) {
            $d = substr($d, 1, 1);
        }

        $rs = $d . " " . $month . " " . $yy;
        return $rs;
    }

    function fullDateEngToThai($ymd)  //return  1 กันยายน 2561
    {
        $exp   = explode('-', $ymd);
        $y     = $exp[0] + 543;
        //$yy    = substr($y, 2, 2);
        $m     = $exp[1];
        $d     = $exp[2];
        $month = $this->fullMonth($m);
        if (substr($d, 0, 1) == 0) {
            $d = substr($d, 1, 1);
        }

        $rs = $d . " " . $month . " " . $y;
        return $rs;
    }

    //Converse Date
    function converseDate($DATE, $TYPE)
    {
        $Dexp    = explode("-", $DATE);
        $NewDate = "";
        if ($TYPE == "TH->DB") {
            $NewDate = ($Dexp[2] - 543) . "-" . $Dexp[1] . "-" . $Dexp[0];
        } else if ($TYPE == "EN->TH") {
            $NewDate = $Dexp[2] . "-" . $Dexp[1] . "-" . ($Dexp[0] + 543);
        } else if ($TYPE == "EN->SHTH") {
            $NewDate = intval($Dexp[2]) . " " . $this->shortMonth($Dexp[1]) . " " . ($Dexp[0] + 543);
        }
        return $NewDate;
    }
}
