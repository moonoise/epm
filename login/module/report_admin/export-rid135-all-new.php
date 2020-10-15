<?php
session_start();
include_once '../../config.php';
include_once '../../includes/dbconn.php';
include_once "../../includes/class.permission.php";
include_once "../../includes/class-date.php";
include_once "../report/class-report.php";
include_once "../myClass.php";

require_once "../../vendor/autoload.php";

if(!isset($_SESSION[__USER_ID__]) ){ 
    header("location:../../login-dpis.php");
  }

  activeTime($login_timeout,$_SESSION[__SESSION_TIME_LIFE__],"../../login-dpis.php");

// $success =  groupUsers($_SESSION[__USER_ID__]);

// if (($success['success'] === false)   ) {
//     header("location:../../login-dpis.php");
// }


if (isset($_POST['per_cardno']) && isset($_POST['years'])) {

$per_cardno = $_POST['per_cardno'];

$err = "";
$db = new DbConn;
$dateCovert = new dateCovert;
$report = new report;
$myClass= new myClass;

$yearById = $myClass->callYearByID($_POST['years']);
$tablePersonal = $yearById['data']['per_personal']; 
$tableCPCscore = $yearById['data']['cpc_score'];
$tableKPIscore = $yearById['data']['kpi_score'];
$startEvaluation_part1 = $yearById['data']['start_evaluation'];  //วันเริ่มต้นช่วงที่ 1
$endEvaluation_part1 = $yearById['data']['end_evaluation'];
$startEvaluation_part2 = $yearById['data']['start_evaluation_2']; //วันเริ่มต้นช่วงที่ 2
$endEvaluation_part2 = $yearById['data']['end_evaluation_2'];
$tableYear = $yearById['data']['table_year'];
$part = explode("-",$tableYear);

    try{
		$sql = "SELECT `".$tablePersonal."`.`per_id`,
						`".$tablePersonal."`.`per_cardno`,
						`".$tablePersonal."`.`pn_name`,
						`".$tablePersonal."`.`per_name`,
						`".$tablePersonal."`.`per_surname`,
						`".$tablePersonal."`.`pos_no`,
						`".$tablePersonal."`.`level_no`,
						`".$tablePersonal."`.`org_id`,
						`".$tablePersonal."`.`org_id_1`,
						`".$tablePersonal."`.`org_id_2`,
                        `".$tablePersonal."`.`through_trial`,
						`per_level`.`level_seq_no`,
						`per_level`.`position_type`,
                        `per_level`.`position_level`,
						`per_line`.`pl_name`,
						`per_mgt`.`pm_name`,
						(SELECT org_name FROM per_org WHERE `per_org`.`org_id` = `".$tablePersonal."`.`org_id`) AS org_name,
						(SELECT org_name FROM per_org WHERE `per_org`.`org_id` = `".$tablePersonal."`.`org_id_1`) AS org_name1, 
						(SELECT org_name FROM per_org WHERE `per_org`.`org_id` = `".$tablePersonal."`.`org_id_2`) AS org_name2
				FROM ".$tablePersonal." 
				LEFT JOIN per_level ON per_level.level_no = ".$tablePersonal.".level_no
				LEFT JOIN per_line ON per_line.pl_code = ".$tablePersonal.".pl_code		
				LEFT JOIN per_mgt ON per_mgt.pm_code = ".$tablePersonal.".pm_code
		WHERE per_cardno = :per_cardno ";
        $stm = $db->conn->prepare($sql);
        $stm->bindParam(":per_cardno",$per_cardno);
        $stm->execute();
        $result = $stm->fetchAll();
        //  echo $sql;

    }catch(Exception $e)
    {
        $err = $e->getMessage();
    }
    if ($err != '') {
        echo $err;
    }
    

$err2 = "";
$table_cpc = $report->tableCPC($per_cardno,$tableYear,array(1,2,3),$tablePersonal,$tableCPCscore);
$resultCPC = $report->cpcCalculate_new($table_cpc);  

$kpiResult = $report->tableKPI($per_cardno,$tableYear,$tablePersonal,$tableKPIscore);
$kpi = $report->reportKPI1($kpiResult);


// echo "<pre>";
// print_r($resultCPC);
// echo "</pre>";

// echo "<pre>";
// print_r($kpi);
// echo "</pre>";

 
//  $dateEvaluation  config.php  ช่วงการประมาณ ครึ่งแรก / ครึ่งหลัง 
$date1 = $dateCovert->fullDateEngToThai($startEvaluation_part1);
$date11 = $dateCovert->fullDateEngToThai($endEvaluation_part1);

$date2 = $dateCovert->fullDateEngToThai($startEvaluation_part2);
$date22 = $dateCovert->fullDateEngToThai($endEvaluation_part2);

$name_lastname = $result[0]['pn_name'].$result[0]['per_name']." ".$result[0]['per_surname'];

$position_type = $result[0]['position_type'];
$pl_name = $result[0]['pl_name'];
$pm_name = $result[0]['pm_name'];
$position_level = $result[0]['position_level'];
$pos_no = $result[0]['pos_no'];

$org_name = $result[0]['org_name'];
$org_name1 = $result[0]['org_name1'];
$org_name2 = $result[0]['org_name2'];
$org = "สังกัด(ส่วน/กลุ่ม/ฝ่าย/โครงการ) $org_name2 $org_name1 สำนัก/กอง $org_name ";

if(count($resultCPC['cpcSum2']) > 0 && count($kpi['kpiSum2'] > 0) ){
    $cpcScore = $resultCPC['cpcSum2'];
    $kpiScore = $kpi['kpiSum2'];
     ($cpcScore == "-" ? $cpcScoreShow = "notShow" : $cpcScoreShow = "Show");
     ($kpiScore == "-" ? $kpiScoreShow = "notShow" : $kpiScoreShow = "Show");
    $ck = $cpcScore + $kpiScore;


    if ($result[0]['through_trial'] == 1) {
        $cpcScoring =  30 ;
        $kpiScoring =  70;
    
        $c = $cpcScore * ($cpcScoring /100);
        $k = $kpiScore * ($kpiScoring / 100);
    
        $ckResult = $c + $k ;

        $cpcScore_1 = $cpcScore;
        $kpiScore_1 =  $kpiScore;
        // $ck_1     = $ck;
        $cpcScoring_1 = $cpcScoring . "%";
        $kpiScoring_1 = $kpiScoring . "%";
        $ScoringSum_1 = $cpcScoring + $kpiScoring;
        $ScoringSum_1 .= "%";
        $c_1 = round($c,2);
        $k_1 = round($k,2);
        $ckResult_1 = round($ckResult,2); 

        $cpcScore_2 = "-";
        $kpiScore_2 =  "-";
        // $ck_2     = "-";
        $cpcScoring_2 = "50%";
        $kpiScoring_2 = "50%";
        $ScoringSum_2 = "100%";
        $c_2 = "-";
        $k_2 = "-";
        $ckResult_2 = "-"; 
    }elseif($result[0]['through_trial'] == 2){

        $cpcScoring =  50 ;
        $kpiScoring =  50;
    
        $c = $cpcScore * ($cpcScoring /100);
        $k = $kpiScore * ($kpiScoring / 100);
    
        $ckResult = $c + $k ;

        $cpcScore_2 = $cpcScore;
        $kpiScore_2 =  $kpiScore;
        // $ck_2     = $ck;
        $cpcScoring_2 = $cpcScoring . "%";
        $kpiScoring_2 = $kpiScoring . "%";
        $ScoringSum_2 = $cpcScoring + $kpiScoring;
        $ScoringSum_2 .= "%";
        $c_2 = round($c,2);
        $k_2 = round($k,2);
        $ckResult_2 = $ckResult; 

        $cpcScore_1 = "-";
        $kpiScore_1 =  "-";
        // $ck_1     = "-";
        $cpcScoring_1 = "70%";
        $kpiScoring_1 = "30%";
        $ScoringSum_1 = "100%";
        $c_1 = "-";
        $k_1 = "-";
        $ckResult_1 = "-";
    }


    // echo "<pre>";
    // print_r($result);
    // echo "</pre>";


    // echo "<pre>";
    // print_r($resultCPC);
    // echo "</pre>";

    // echo "<pre>";
    // print_r($kpi);
    // echo "</pre>";


    $html = "
    <style>
    @page {
        margin-top: 0.1cm;
        margin-bottom: 0.1cm;
        margin-left: 0.5cm;
        margin-right: 0cm;
    }
    table.inner {
        border-collapse: collapse;
        border: 2px solid #000088;
        padding: 3px;
        margin: 5px;
        empty-cells: show;
        
    }
    </style>

        ";
    $html .= "
    <table cellpadding='0' cellspacing='0' border='0' width='100%'>
        <tbody>
            <tr valign='top'>
                <td width='30%'></td>
                <td align='center' width='40%'><img src='../../../external/logo_rid/rid_112x132.png' border='0'></td>
                <td width='30%' align='right'><div style='float:right;'><b>ชป.135</b></div></td>
            </tr>
        </tbody>
    </table>
    ";
    $html .= "<table cellpadding='0' cellspacing='0' border='0' width='100%' style='padding:5px 0;'>
                <tbody>
                    <tr><td align='center' style='font-size: 18pt;'><b>แบบสรุปการประเมินผลการปฏิบัติราชการของข้าราชการ</b></td></tr>
                </tbody>
            </table>";

    $html .= "
    <table cellpadding='0' cellspacing='0' border='0' width='100%'>
        <tbody>
            <tr>
                <td>
                    <table cellpadding='0' cellspacing='0' border='0' style='padding-top:5px; '>
                        <tbody>
                            <tr>
                                <td style='text-decoration:underline; font-size: 14pt;'><b>ส่วนที่ 1 : ข้อมูลของผู้รับการประเมิน</b></td>
                            </tr>
                        </tbody>
                    </table>


                <table cellpadding='0' cellspacing='0' border='0' style='font-size: 14pt;'>
                    <tbody>
                        <tr>
                            <td align='left' style='padding-left:60px;'>รอบการประเมิน</td>
                            <td style='padding:0 0 5px 20px;'><img src='".($part[1]==1?"../../../external/report/block_ok.png" : "../../../external/report/block.png" )."' border='0'></td>
                            <td style='padding-left:10px;'>รอบที่ <span style='padding:5px;'>1</span> ตั้งแต่วันที่ $date1</td>
                            <td style='padding-left:10px;'>ถึงวันที่ $date11 </td>
                        </tr>
                        <tr>
                            <td align='left' style='padding-left:60px;'></td>
                            <td style='padding:0 0 5px 20px;'><img src='".($part[1]==2?"../../../external/report/block_ok.png" : "../../../external/report/block.png" )."' border='0'></td>
                            <td style='padding-left:10px;'>รอบที่  <span style='padding:5px;'>2</span> ตั้งแต่วันที่ $date2 </td>
                            <td style='padding-left:10px;'>ถึงวันที่ $date22 </td>
                        </tr>
                    </tbody>
                </table>

                <table cellpadding='0' cellspacing='0' border='0' width='100%' style='font-size: 14pt;'>
                    <tbody>
                        <tr>
                            <td>ชื่อผู้รับการประเมิน  $name_lastname </td>
                        </tr>
                    </tbody>
                </table>

                <table cellpadding='0' cellspacing='0' border='0' style='font-size: 14pt; padding-top:3px;' >
                    <tbody>
                        <tr>
                            <td style='padding-right:5px;'>ประเภทตำแหน่ง</td>
                            <td> $position_type </td><td style='padding:0 10px;'>ตำแหน่ง/ระดับ ".($pm_name == $pl_name ? ' '.$pl_name.' '.$position_level.' ' : $pm_name.' ('.$pl_name.' '.$position_level.')')."  ตำแหน่งเลขที่ $pos_no </td>
                        </tr>
                    </tbody>
                </table>

                <table cellpadding='0' cellspacing='0' border='0' style='font-size: 14pt; padding-top:3px;'>
                    <tbody>
                        <tr>
                            <td> $org </td>
                            
                        </tr>
                    </tbody>
                </table>

                <table cellpadding='0' cellspacing='0' border='0' style='font-size: 14pt; padding-top:3px;'>
                    <tbody>
                        <tr>
                            <td style='padding-right:10px;'>ใบอนุญาตประกอบวิชาชีพ (ถ้ามี)</td><td>..............................................</td>
                            <td style='padding:0 10px;'>ระดับ</td><td>.....................</td>
                            <td style='padding:0 10px;'>ออกให้เมื่อวันที่</td>
                            <td>.......................</td>
                            <td style='padding:0 10px;'>ถึงวันที่</td>
                            <td>.......................</td>
                        </tr>
                    </tbody>
                </table>

                </td>
            </tr>
        </tbody>
    </table>";

    $html .= "
    <table cellpadding='0' cellspacing='0' border='0' style='padding-top:15px;' width='100%'>
        <tbody>
            <tr>
                <td>
                    <table cellpadding='0' cellspacing='0' border='0' style='padding-top:1px;'>
                        <tbody>
                            <tr>
                                <td style='text-decoration:underline; font-size: 14pt;'><b>ส่วนที่ 2 : การสรุปผลการประเมิน</b></td>
                            
                            </tr>
                            <tr>
                            <td style='font-size: 14pt;'><b>กรณีที่ 1</b> สำหรับข้าราชการทั่วไป</td>
                            </tr>
                        </tbody>
                    </table>

                    <table cellpadding='3' cellspacing='0' border='1'  style='font-size: 14pt; ' width=700 class='inner'>
                        <tbody>
                            <tr>
                                <td width='400' align='center' >องค์ประกอบการประเมิน</td>
                                <td width='80' align='center' >คะแนน (ก)</td>
                                <td width='80' align='center' >น้ำหนัก (ข)</td>
                                <td width='140' align='center' >รวมคะแนน (ก x ข)</td>
                            </tr>
                            <tr>
                                <td>องค์ประกอบที่ 1 : ผลสัมฤทธิ์ของงาน</td>
                                <td align='center'>".($kpiScoreShow == "Show" ? $kpiScore_1 : "-")."</td>
                                <td align='center'>$kpiScoring_1</td>
                                <td align='center'>".($kpiScoreShow == "Show" ? $k_1 : "-")."</td>
                            </tr>
                            <tr>
                                <td>องค์ประกอบที่ 2 : พฤติกรรมการปฏิบัติราชการ (สมรรถนะ)</td>
                                <td align='center'> ".($cpcScoreShow == "Show" ? $cpcScore_1 : "-" )."</td>
                                <td align='center'> $cpcScoring_1 </td>
                                <td align='center'>".($cpcScoreShow == "Show" ? $c_1 : "-" )."</td>
                            </tr>
                            <tr>
                                <td align='center'>รวม</td>
                                <td align='center'> </td>
                                <td align='center'> $ScoringSum_1 </td>
                                <td align='center'>".($cpcScoreShow == "Show" ? $ckResult_1 : "-" )."</td>
                            </tr>
                        </tbody>
                    </table>

                    <table cellpadding='0' cellspacing='0' border='0' style='padding-top:5px; font-size: 14pt;' width=700 >
                        <tbody>
                            <tr>
                                <td><b>กรณีที่ 2</b> สำหรับข้าราชการที่อยู่ระหว่างทดลองปฏิบัติหน้าที่ราชการ  หรือมีระยะเวลาทดลองปฏิบัติหน้าที่ราชการอยู่ในระหว่างรอบการประเมิน</td>
                            </tr>
                        </tbody>
                    </table>

                    <table cellpadding='3' cellspacing='0' border='1' style='font-size: 14pt;' width=700 class='inner'>
                        <tbody>
                            <tr>
                                <td width='400' align='center'>องค์ประกอบการประเมิน</td>
                                <td width='80' align='center'>คะแนน (ก)</td>
                                <td width='80' align='center'>น้ำหนัก (ข)</td>
                                <td width='140' align='center'>รวมคะแนน (ก x ข)</td>
                            </tr>
                            <tr>
                                <td>องค์ประกอบที่ 1 : ผลสัมฤทธิ์ของงาน</td>
                                <td align='center'>".($kpiScoreShow == "Show" ? $kpiScore_2 : "-")."</td>
                                <td align='center'> $kpiScoring_2 </td>
                                <td align='center'>".($kpiScoreShow == "Show" ? $k_2 : "-")."</td>
                            </tr>
                            <tr>
                                <td>องค์ประกอบที่ 2 : พฤติกรรมการปฏิบัติราชการ (สมรรถนะ)</td>
                                <td align='center'>".($cpcScoreShow == "Show" ? $cpcScore_2 : "-" )."</td>
                                <td align='center'> $cpcScoring_2 </td>
                                <td align='center'>".($cpcScoreShow == "Show" ?  $c_2 : "-" )."</td>
                            </tr>
                            <tr>
                                <td align='center'>รวม</td>
                                <td align='center'>  </td>
                                <td align='center'> $ScoringSum_2 </td>
                                <td align='center'>".($cpcScoreShow == "Show" ? $ckResult_2 : "-" )."</td>
                            </tr>
                        </tbody>
                    </table>

                </td>
            </tr>
        </tbody>
    </table>";

    //  echo $html;

    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new \Mpdf\Mpdf(
    [
        'fontDir' => array_merge($fontDirs, [
            __DIR__ . '../../vendor/mpdf/ttfonts',
        ]),
        'fontdata' => $fontData + [
            'thsarabun' => [
                'R' => 'THSarabun.ttf',
                'I' => 'THSarabun Italic.ttf',
                'B' => 'THSarabun Bold.ttf',
            ]
        ],
        'default_font' => 'thsarabun',
        'orientation' => 'P'
    ]);


    $mpdf->WriteHTML($html);
   

}else 
{
    echo '<h3 style="color:red;">ข้อมูลการประเมินยังไม่สมบูรณ์ ไม่สามารถพิมพ์รายงานได้  <a href="'.__PATH_WEB__.'/login/view-report.php" > <-ย้อนกลับคลิ๊ก </a> || <a href="javascript:close();" > (X) ปิดหน้าต่าง </a></h3>';
}

 
$html2 = "";
$html2 .= '<style> 
            @page {
                size: 8.5in 11in; /* <length>{1,2} | auto | portrait | landscape */ 
                margin: 0%; /* <any of the usual CSS values for margins> */
                            /*(% of page-box width for LR, of height for TB) */
                margin-header: 1mm; /* <any of the usual CSS values for margins> */
                margin-footer: 1mm; /* <any of the usual CSS values for margins> */
                margin-left: 2%;
                margin-right: 2%;
                marks: /*crop | cross | none*/
                header: html_myHTMLHeaderOdd;
                footer: html_myHTMLFooterOdd;
                background: ...
                background-image: ...
                background-position ...
                background-repeat ...
                background-color ...
                background-gradient: ...
            }
            table.inner {
                border-collapse: collapse;
                border-top: 1px solid #1a1a1b;
                border-left: 1px solid #1a1a1b;
                border-right: 1px solid #1a1a1b;
                border-bottom: 1px solid #1a1a1b;
                
                padding: 3px;
                margin: 5px;
                empty-cells: show;
                
            }
            td.inner_td {
                border-collapse: collapse;
                border-top: 0px solid #1a1a1b;
                border-left: 1px solid #1a1a1b;
                border-right: 1px solid #1a1a1b;
                border-bottom: 1px solid #1a1a1b;
                padding: 1px;
                margin: 3px;
                empty-cells: show;
                
               }
            
            </style>';


$html2 .= '<table cellpadding="0" cellspacing="0" border="0">
            <tbody>
                <tr>
                    <td style="text-decoration:underline;">
                    <b style="font-size:14pt;">ส่วนที่  3  :  แผนพัฒนาการปฏิบัติราชการรายบุคคล</b>
                    </td>
                </tr>
            </tbody>
        </table>';

$html2 .= '<table cellpadding="3" cellspacing="0" border="0" width="100%" class="inner">			
            <tbody>
                <tr>
                    <td class="inner_td" width="20%" align="center" style="background-color:#eee;" nowrap=""><b>ชื่อความรู้ / ทักษะ / สมรรถนะ<br>ที่ต้องได้รับการพัฒนา</b></td>
                    <td class="inner_td"  width="20%" align="center" style="background-color:#eee;" nowrap=""><b>หัวข้อ / ประเด็น / เรื่อง<br>ที่จะพัฒนาในรอบการประเมินนี้</b></td>
                    <td class="inner_td" width="20%" align="center" style="background-color:#eee;" nowrap=""><b>วิธีการพัฒนา</b></td>
                    <td class="inner_td" width="20%" align="center" style="background-color:#eee;"><b>ระยะเวลาการพัฒนา<br>ตามแผน (ชม.)</b></td>
                    <td  class="inner_td" width="20%" align="center" style="background-color:#eee;"><b>ระยะเวลาการพัฒนา<br>ตามจริง (ชม.)</b></td>
                </tr>
                <tr height="40">
                    <td class="inner_td" >&nbsp;</td>
                    <td class="inner_td" >&nbsp;</td>
                    <td class="inner_td" >&nbsp;</td>
                    <td class="inner_td" >&nbsp;</td>
                    <td class="inner_td" >&nbsp;</td>
                </tr>
                <tr height="40">
                    <td class="inner_td" >&nbsp;</td>
                    <td class="inner_td" >&nbsp;</td>
                    <td class="inner_td" >&nbsp;</td>
                    <td class="inner_td" >&nbsp;</td>
                    <td class="inner_td" >&nbsp;</td>
                </tr>
              
                <tr height="40">
                    <td class="inner_td" >&nbsp;</td>
                    <td class="inner_td" >&nbsp;</td>
                    <td class="inner_td" >&nbsp;</td>
                    <td class="inner_td" >&nbsp;</td>
                    <td class="inner_td" >&nbsp;</td>
                </tr>
                <tr height="40">
                    <td class="inner_td" >&nbsp;</td>
                    <td class="inner_td" >&nbsp;</td>
                    <td class="inner_td" >&nbsp;</td>
                    <td class="inner_td" >&nbsp;</td>
                    <td class="inner_td" >&nbsp;</td>
                </tr>			
                <tr>
                    <td class="inner_td"  align="center" style="background-color:#f5eb95;"><b>ผลการพัฒนารายบุคคล</b></td>
                    <td class="inner_td"  style="background-color:#f5eb95;"></td>
                    <td class="inner_td"  style="background-color:#f5eb95;padding:5px;" align="center">
                      
                    </td>
                    <td class="inner_td"  style="background-color:#f5eb95;" align="center"></td>
                    <td class="inner_td"  style="background-color:#f5eb95;padding:5px;" align="center"></td>
                </tr>
                <tr>
                    <td class="inner_td"  align="center" style="background-color:#a3f1a6;" colspan="3"><b>ผลการพัฒนารายบุคคล</b></td>
                    <td class="inner_td"  style="background-color:#a3f1a6;" align="center"></td>
                    <td class="inner_td"  style="background-color:#a3f1a6;" align="center"></td>
                </tr>
            </tbody>
            </table>';

            $mpdf->AddPage(); 
            $mpdf->WriteHTML($html2);
            // echo $html2;
            $str_gap = '  รวมจำนวนชั่วโมงที่พัฒนา รอบที่ '.$part[1].'<br>
                        <span style="padding:2px;">'.$date1.'</span> - <span style="padding:2px;">'.$date11.'</span> ';  // ไม่ได้ใช้ ยังไม่ได้เขียน code ต่อ
           
$html3 = "";
$html3 .= '<style> 
            @page {
                size: 8.5in 11in; /* <length>{1,2} | auto | portrait | landscape */ 
                margin: 0%; /* <any of the usual CSS values for margins> */
                            /*(% of page-box width for LR, of height for TB) */
                margin-header: 1mm; /* <any of the usual CSS values for margins> */
                margin-footer: 1mm; /* <any of the usual CSS values for margins> */
                margin-left: 2%;
                margin-right: 2%;
                marks: /*crop | cross | none*/
                header: html_myHTMLHeaderOdd;
                footer: html_myHTMLFooterOdd;
                background: ...
                background-image: ...
                background-position ...
                background-repeat ...
                background-color ...
                background-gradient: ...
            }
            table.inner {
                border-collapse: collapse;
                border-top: 1px solid #1a1a1b;
                border-left: 1px solid #1a1a1b;
                border-right: 1px solid #1a1a1b;
                border-bottom: 1px solid #1a1a1b;
                
                padding: 2px;
                margin: 2px;
                empty-cells: show;
                
            }
            td.inner_td {
                border-collapse: collapse;
                border-top: 0px solid #1a1a1b;
                border-left: 1px solid #1a1a1b;
                border-right: 1px solid #1a1a1b;
                border-bottom: 1px solid #1a1a1b;
                padding: 1px;
                margin: 3px;
                empty-cells: show;
               }
            
            </style>';


$html3 .= '<table cellpadding="0" cellspacing="0" border="0">
            <tbody>
                <tr>
                    <td style="text-decoration:underline;">
                    <b style="font-size:14pt;">ส่วนที่ 4 : การรับทราบผลการประเมิน </b>
                    </td>
                </tr>
            </tbody>
        </table>';

$html3 .= '<table  cellpadding="3" cellspacing="0" border="0" width="100%" class="inner" >
            <tbody>
                <tr>
                    <td ><b style="font-size: 12pt;">ผู้รับการประเมิน :</b></td>
                    <td></td>
                    
                </tr>
                <tr>
                    <td  style="padding:0 0 5px 20px; font-size: 12pt;"><img src="../../../external/report/block.png" > ได้รับทราบผลการประเมินและแผนพัฒนาการปฏิบัติราชการรายบุคคลแล้ว </td>
                    <td></td>
                    
                </tr>
                <tr>
                   
                    <td></td>
                    <td nowrap="" style="padding-right:10px; font-size: 12pt;" align="right" >
                        ลงชื่อ : ...............................................................
                    </td>
                </tr>
                <tr>
                   
                    <td></td>
                    <td nowrap="" style="padding-right:10px; font-size: 12pt;" align="right" >
                        ตำแหน่ง : ...............................................................
                    </td>
                </tr>
                <tr>
                   
                    <td></td>
                    <td nowrap="" style="padding-right:10px; font-size: 12pt;" align="right" >
                        วันที่ : ...............................................................
                    </td>
                </tr>
              
                <tr>
                    
                    <td></td>
                    <td></td>
                </tr>
                <tr> 
                <td style="padding-left:10px; border-top: 1px solid #1a1a1b; font-size: 12pt;" ><b>ผู้ประเมิน :</b></td>
                <td style=" border-top: 1px solid #1a1a1b; font-size: 12pt;"></td>
                
               </tr>
               <tr>
              
                <td style="padding-left:10px; font-size: 12pt;" ><img src="../../../external/report/block.png" > ได้แจ้งผลการประเมินและผู้รับการประเมินได้ลงลายมือชื่อรับทราบ </td>
                <td></td>

               </tr>
               <tr>
                    <td style="padding-left:10px; font-size: 12pt;" ><img src="../../../external/report/block.png" > ได้แจ้งผลการประเมินเมื่อวันที่ ........................................................</td>
                    <td></td>
 
               </tr>
               <tr>
                    
                    <td style="padding-left:10px; font-size: 12pt;">แต่ผู้รับการประเมินไม่ลงลายมือชื่อรับทราบ</td>
                    <td></td>
           
               </tr>
               <tr>
                    <td style="padding-left:10px; font-size: 12pt;" >โดยมี ................................................................. เป็นพยาน</td>
                    <td></td>
      
                </tr>
                <tr>
                    <td style="padding-left:10px; font-size: 12pt;">ลงชื่อ : ...............................................................</td>
                   
                    <td style="padding-right:10px; font-size: 12pt;" align="right" >ลงชื่อ : ................................................................</td>
                </tr>
                <tr>
                    <td style="padding-left:10px; font-size: 12pt;" >ตำแหน่ง : ..........................................................</td>
                   
                    <td style="padding-right:10px; font-size: 12pt;" align="right" >ตำแหน่ง : ................................................................</td>
                </tr>
                <tr>
                    <td style="padding-left:10px; font-size: 12pt;" >วันที่ : ................................................................</td>
                   
                    <td style="padding-right:10px; font-size: 12pt;" align="right" >วันที่ : ................................................................</td>
                </tr>
                <tr>
                   
                    <td></td>
                    <td></td>
                </tr>

            </tbody>
        </table>
        <br>
        
';

$html3 .= '<table cellpadding="0" cellspacing="0" border="0" >
            <tbody>
                <tr>
                    <td style="text-decoration:underline;">
                    <b style="font-size:14pt;">ส่วนที่ 5 : ความเห็นของผู้บังคับบัญชาเหนือขึ้นไป</b>
                    </td>
                </tr>
            </tbody>
        </table>';
// --------------------------------------------

$html3 .= '<table  cellpadding="3" cellspacing="0" border="0" width="100%" class="inner" >
            <tbody>
                <tr>
                    <td><b style="font-size: 12pt;"> ผู้บังคับบัญชาเหนือขึ้นไป : </b></td>
                    <td></td>
                    
                </tr>
                <tr>
                    <td  style="padding:0 0 5px 20px; font-size: 12pt;"><img src="../../../external/report/block.png" > เห็นด้วยกับผลการประเมิน </td>
                    <td></td>
                </tr>
                <tr>
                    <td  style="padding:0 0 5px 20px; font-size: 12pt;"><img src="../../../external/report/block.png" > มีความเห็นต่าง ดังนี้ ............................................................................................................................................... </td>
                    <td></td>
                   
                </tr>
                <tr>
                <td  style="padding:0 0 5px 20px; font-size: 12pt;">......................................................................................................................................................................................... </td>
                <td></td>
               
            </tr>
                <tr>
                    
                    <td></td>
                    <td nowrap="" style="padding-right:10px; font-size: 12pt;" align="right" >
                        ลงชื่อ : ...............................................................
                    </td>
                </tr>
                <tr>
                   
                    <td></td>
                    <td nowrap="" style="padding-right:10px; font-size: 12pt;" align="right" >
                        ตำแหน่ง : ...............................................................
                    </td>
                </tr>
                <tr>
                    
                    <td></td>
                    <td nowrap="" style="padding-right:10px; font-size: 12pt;" align="right" >
                        วันที่ : ...............................................................
                    </td>
                </tr>
                
                <tr>
                   
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                <td style="border-top: 1px solid #1a1a1b;"><b style="font-size: 12pt;"> ผู้บังคับบัญชาเหนือขึ้นไปอีกชั้นหนึ่ง (ถ้ามี) : </b></td>
                <td style="border-top: 1px solid #1a1a1b;"></td>
                
                </tr>
            <tr>
                <td style="padding:0 0 5px 20px; font-size: 12pt;"><img src="../../../external/report/block.png" > เห็นด้วยกับผลการประเมิน </td>
                <td></td>
            </tr>
            <tr>
                <td  style="padding:0 0 5px 20px; font-size: 12pt;"><img src="../../../external/report/block.png" > มีความเห็นต่าง ดังนี้ ............................................................................................................................................... </td>
                <td></td>
               
            </tr>
            <tr>
            <td  style="padding:0 0 5px 20px; font-size: 12pt;">......................................................................................................................................................................................... </td>
            <td></td>
           
        </tr>
            <tr>
                
                <td></td>
                <td nowrap="" style="padding-right:10px; font-size: 12pt;" align="right" >
                    ลงชื่อ : ...............................................................
                </td>
            </tr>
            <tr>
               
                <td></td>
                <td nowrap="" style="padding-right:10px; font-size: 12pt;" align="right" >
                    ตำแหน่ง : ...............................................................
                </td>
            </tr>
            <tr>
                
                <td></td>
                <td nowrap="" style="padding-right:10px; font-size: 12pt;" align="right" >
                    วันที่ : ...............................................................
                </td>
            </tr>
            
                <tr>
                    <td></td>
                    <td></td>
                    
                </tr>

            </tbody>
            </table>
            <br>


';

$html3 .= '<table cellpadding="0" cellspacing="0" border="0">
            <tbody>
                <tr>
                    <td>
                    <b><u style="font-size:12pt;">หมายเหตุ</u></b>	: แบบสรุปการประเมินผลการปฏิบัติราชการให้ใช้ในรอบการประเมินแต่ละครั้ง เท่านั้น
                    </td>
                </tr>
            </tbody>
        </table>';

$mpdf->AddPage(); 
$mpdf->WriteHTML($html3);
$mpdf->Output();

// echo $html3;
}else {
    echo '<table  cellpadding="3" cellspacing="0" border="0" width="100%" class="inner" >
            <tbody>
                <tr style="background-color: red;text-align: center;">
                    <td><b>&nbsp;&nbsp;&nbsp;&nbsp; </b></td>
                </tr>
                <tr style="background-color: red;text-align: center;">
                    <td><b>&nbsp;&nbsp;&nbsp;&nbsp; </b></td>
                </tr>
                <tr style="background-color: red;text-align: center;">
                    <td><b>&nbsp;&nbsp;&nbsp;&nbsp; </b></td>
                </tr>
                <tr style="background-color: red;text-align: center;">
                    <td><b>  ไม่สามารถแสดงข้อมูลได้  กรุณากลับไปที่หน้ารายงาน แล้วกดปุ่มพิมพ์ ใหม่อีกครั้ง  </b></td>
                </tr>
                <tr style="background-color: red;text-align: center;">
                    <td><b>&nbsp;&nbsp;&nbsp;&nbsp; </b></td>
                </tr>
                <tr style="background-color: red;text-align: center;">
                    <td><b>&nbsp;&nbsp;&nbsp;&nbsp; </b></td>
                </tr>
                <tr style="background-color: red;text-align: center;">
                    <td><b>&nbsp;&nbsp;&nbsp;&nbsp; </b></td>
                </tr>
            </tbody>
         </table>';
}
?>