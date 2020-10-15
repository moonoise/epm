<?php
session_start();
include_once "config.php";
include_once "includes/dbconn.php";
include_once "includes/class-userOnline.php";
$userOnline = new userOnline;
$_SESSION[__USERONLINE__] = $userOnline->count_users();


if (isset($_SESSION[__USER_NAME__])) {
  header("location:index.php");
}

$db = new dbConn;
$sql = "SELECT * FROM `news` WHERE solfdelete = 0 AND new_public = 1  ORDER BY new_order DESC ";
$stm = $db->conn->prepare($sql);
$stm->execute();
$result = $stm->fetchAll();


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href=<?php echo __PATH_EXTERNAL__ . "icon/rid.png"; ?>> <!-- Bootstrap -->
  <!-- Bootstrap -->
  <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">

  <!-- Custom Theme Style -->
  <link href="../vendors/bootstrap/dist/css/custom.index.css" rel="stylesheet">
  <style>
    .bg-head {
      /* The image used */
      background-image: url(<?php echo __PATH_EXTERNAL__ . "background/6726.png"; ?> );

      /* Full height */
      height: 20%;


      /* Center and scale the image nicely */
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
    }

    body {
      background-image: url(<?php echo __PATH_EXTERNAL__ . "background/04.jpg"; ?> );
      background-repeat: no-repeat;
    }

    .x-panel-bg {
      background: #ffffffb5;
    }
  </style>

</head>

<body>
  <!-- หัว banner -->
  <div class="col-md-12 col-sm-12 col-xs-12 bg-head ">
    <div class="page-header">

      <h1 class="text-info">
        <span class="text-success">Welcome to </span> e-Performance Management
        <small class="blue">กรมชลประทาน</small>
        <img src="../external/icon/rid.png" width="80px" alt="..." style="float:none;">
      </h1>
    </div>
  </div>



  <!-- หัว banner -->
  <div class="col-md-9 col-sm-12 col-xs-12">
    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel x-panel-bg">
          <div class="x_title">
            <h2 class="text-primary">ประกาศ/ประชาสัมพันธ์ <small class="text-success ">EPM News</small></h2>

            </ul>
            <div class="clearfix"></div>
          </div>
          <div class="x_content">
            <div class="row">
              <div class="col-sm-12 mail_list_column">

                <?php
                foreach ($result as $key => $row) {
                  echo '<a href="#">
                      <div class="mail_list">
                        <div class="right">
                          <h3 class="text-success"><i class="glyphicon glyphicon-flag text-success"></i> ' . $row['new_title'] . '</h3>
                          <p> ' . $row['new_content'] . '</p>
                        </div>
                      </div>
                    </a>';
                }

                ?>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 col-sm-12 col-xs-12">
    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div>
          <!-- login  -->
          <div class="login_wrapper">

            <section class="login_content">
              <form class="form-signin" name="form1" method="post" action="checklogin.php">
                <h1 class="text-info">เข้าสู่ระบบ</h1><small>โปรกรอกข้อมูลให้ครบก่อนล๊อคอินเข้าสู่ระบบ</small>
                <div>
                  <input type="text" class="form-control" required="" name="myusername" id="myusername" placeholder="เลขบัตรประจำตัวประชาชน" autofocus />
                </div>
                <div>
                  <input type="password" class="form-control" placeholder="รหัสผ่านเดียวกันกับระบบ DPIS" required="" name="mypassword" id="mypassword" />
                </div>
                <div>
                  <button class="btn btn-info submit" name="Submit" id="submit">เข้าสู่ระบบ</button>
                  <!-- <a class="reset_pass" href="forgot-password.php">ลืมรหัสผ่าน ?</a> -->
                </div>
                <!-- <label class="checkbox"> -->
                <!-- <input type="checkbox" value="remember-me"> <small>คลิ๊ก เพื่อจำบัญชีนี้</small> -->

                </label>

                <div id="message"></div>
                <div class="clearfix"></div>
              </form>
            </section>



          </div> <!-- end login_wrapper -->

        </div> <!-- end login -->
      </div> <!-- end col-md-12 col-sm-12 col-xs-12 -->

      <div class="col-md-12 col-sm-12 col-xs-12">

        <a class="btn btn-app">
          <span class="badge bg-green"><?php echo $_SESSION[__USERONLINE__]; ?></span>
          <i class="fa fa-users text-success"></i> <span class="text-info">จำนวนผู้ใช้ระบบ </span>
        </a>

        <a class="btn btn-app" href="main_login.php">

          <i class="fa fa-user text-success"></i> <span class="text-info">สำหรับผู้ดูแลระบบ </span>
        </a>
      </div><!-- end col-md-12 col-sm-12 col-xs-12 -->

      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel x-panel-bg">
          <div class="x_title">
            <h4>ระบบรองรับ</h4>

            <div class="clearfix"></div>
          </div>
          <div class="x_content">
            <div class="row">
              <h4 class="text-danger"> คำแนะนำ:</h4>
              <span>เครื่องมือสำหรับใช้ในการประเมินให้เลือกใช้ตัวเลือกใดตัวหนึ่งจากด้านล่าง (Best View)
                หากไม่มีให้ท่านคลิกที่รูปเครื่องมือที่ต้องการเพื่อดาวน์โหลดและทำการติดตั้ง *แนะนำเครื่องมือที่ขีดเส้นใต้สีแดง (Google Chrome)
              </span>

              <div class="col-md-12 col-sm-12 col-xs-12">
                <img src="../assets/images/if_chrome_245982.png" class="img-rounded" alt="Cinque Terre" width="50px">


                <img src="../assets/images/if_firefox_245988.png" class="img-rounded" alt="Cinque Terre" width="50px">


                <img src="../assets/images/if_opera_317734.png" class="img-rounded" alt="Cinque Terre" width="50px">


                <img src="../assets/images/if_safari_317730.png" class="img-rounded" alt="Cinque Terre" width="70px">
              </div>

            </div>


          </div>
        </div>

      </div>



    </div> <!-- row -->

  </div> <!-- end col-md-3 -->



</body>




</html>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->

<script src="../vendors/jquery/dist/jquery.min.js"></script>

<!-- Include all compiled plugins (below), or include individual files as needed -->

<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- The AJAX login script -->
<script src="js/login-dpis.js"></script>

<!-- Custom Theme Scripts -->
<script src="../vendors/bootstrap/dist/js/custom.min.js"></script>

<script>
  $('#form-signin').keydown(function(e) {

    var key = e.which;
    // console.log(key)
    if (key == 13) {
      // As ASCII code for ENTER key is "13"
      $('#submit').trigger("click"); // Submit form code
    }
  });
</script>