<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
 
  <?php require('inc/links.php'); ?>
  <title><?php echo $settings_r['site_title'] ?> - TRANG CHỦ</title>
  <style>
    .availability-form{
      margin-top: -50px;
      z-index: 2;
      position: relative;
    }

    @media screen and (max-width: 575px) {
      .availability-form{
        margin-top: 25px;
        padding: 0 35px;
      } 
    }
  </style>
</head>
<body class="bg-light">

  <?php require('inc/header.php'); ?>

  <!-- Ảnh trang chủ -->

  <div class="container-fluid px-lg-4 mt-4">
    <div >  
        <?php 
          $res = selectAll('carousel');
          while($row = mysqli_fetch_assoc($res))
          {
            $path = anh_trang_chu;
            echo <<<data
              <div class="swiper-slide">
                <img src="$path$row[image]" class="w-100 d-block">
              </div>
            data;
          }
        ?>
      </div>
  </div>

  <!-- Tìm kiếm phòng -->

  <div class="container availability-form">
    <div class="row">
      <div class="col-lg-12 bg-white shadow p-4 rounded">
        <h5 class="mb-4">Tìm Phòng</h5>
        <form action="rooms.php">
          <div class="row align-items-end">
            <div class="col-lg-3 mb-3">
              <label class="form-label" style="font-weight: 500;">Ngày Nhận Phòng</label>
              <input type="date" class="form-control shadow-none" name="checkin" required>
            </div>
            <div class="col-lg-3 mb-3">
              <label class="form-label" style="font-weight: 500;">Ngày Trả Phòng</label>
              <input type="date" class="form-control shadow-none" name="checkout" required>
            </div>
            <div class="col-lg-2 mb-3">
              <label class="form-label" style="font-weight: 500;">Người Lớn</label>
              <select class="" name="adult">
                <?php 
                  $khach = mysqli_query($con,
                  "SELECT MAX(adult) AS `max_adult`, MAX(children) AS `max_children` 
                    FROM rooms WHERE status ='1' AND removed='0'");  
                  $khach_1 = mysqli_fetch_assoc($khach);
                  for($i=1; $i<=$khach_1['max_adult']; $i++){
                    echo"<option value='$i'>$i</option>";
                  }
                ?>
              </select>
            </div>
            <div class="col-lg-2 mb-3">
              <label class="form-label" style="font-weight: 500;">Trẻ Em</label>
              <select class="" name="children">
                <?php 
                  for($i=1; $i<=$khach_1['max_children']; $i++){
                    echo"<option value='$i'>$i</option>";
                  }
                ?>
              </select>
            </div>
            <input type="hidden" name="check_availability">
            <div class="col-lg-2 mb-lg-3 mt-2">
              <button type="submit" class="btn text-white shadow-none custom-bg">Tìm Phòng</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Our Rooms -->

  <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font">PHÒNG</h2>

  <div class="container">
    <div class="row">

      <?php 
            
        $room_res = select("SELECT * FROM rooms WHERE status =? AND removed =? ORDER BY id DESC LIMIT 3",[1,0],'ii');

        while($phong_data = mysqli_fetch_assoc($room_res))
        {
          // get tính năng phòng

          $features = mysqli_query($con,"SELECT f.name FROM `features` f
            INNER JOIN room_features roomfea ON f.id = roomfea.features_id 
            WHERE roomfea.room_id = '$phong_data[id]'");

          $features_data = "";
          while($fea_row = mysqli_fetch_assoc($room_res)){
            $features_data .="<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
              $fea_row[name]
            </span>";
          }

          // get tiện nghi của phòng

          $fac_q = mysqli_query($con,"SELECT f.name FROM `facilities` f 
            INNER JOIN `room_facilities` rfac ON f.id = rfac.facilities_id 
            WHERE rfac.room_id = '$phong_data[id]'");

          $facilities_data = "";
          while($fac_row = mysqli_fetch_assoc($fac_q)){
            $facilities_data .="<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
              $fac_row[name]
            </span>";
          }

          // lấy hình ảnh thu nhỏ

          $room_thumb = anh_phong."thumbnail.jpg";
          $thumb_q = mysqli_query($con,"SELECT * FROM room_images 
            WHERE room_id='$phong_data[id]' 
            AND thumb ='1'");

          if(mysqli_num_rows($thumb_q)>0){
            $thumb_res = mysqli_fetch_assoc($thumb_q);
            $room_thumb = anh_phong.$thumb_res['image'];
          }

          $book_btn = "";

          if(!$settings_r['shutdown']){
            $login=0;
            if(isset($_SESSION['login']) && $_SESSION['login']==true){
              $login=1;
            }

            $book_btn = "<button onclick='checkLoginToBook($login,$phong_data[id])' class='btn btn-sm text-white custom-bg shadow-none'>Đặt Ngay</button>";
          }

          $rating_q = "SELECT AVG(rating) AS `avg_rating` FROM `rating_review`
            WHERE `room_id`='$phong_data[id]' ORDER BY `sr_no` DESC LIMIT 20";

          $rating_res = mysqli_query($con,$rating_q);
          $rating_fetch = mysqli_fetch_assoc($rating_res);

          $rating_data = "";

          if($rating_fetch['avg_rating']!=NULL)
          {
            $rating_data = "<div class='rating mb-4'>
              <h6 class='mb-1'>Rating</h6>
              <span class='badge rounded-pill bg-light'>
            ";

            for($i=0; $i<$rating_fetch['avg_rating']; $i++){
              $rating_data .="<i class='bi bi-star-fill text-warning'></i> ";
            }

            $rating_data .= "</span>
              </div>
            ";
          }

          // print room card

          echo <<<data
            <div class="col-lg-4 col-md-6 my-3">
              <div class="card border-0 shadow" style="max-width: 350px; margin: auto;">
                <img src="$room_thumb" class="card-img-top">
                <div class="card-body">
                  <h5>$phong_data[name]</h5>
                  <h6 class="mb-4">$phong_data[price] vnđ mỗi đêm</h6>
                  <div class="features mb-4">
                    <h6 class="mb-1">Cơ sở</h6>
                    $features_data
                  </div>
                  <div class="facilities mb-4">
                    <h6 class="mb-1">Tiện nghi & Trang thiết bị</h6>
                    $facilities_data
                  </div>
                  <div class="guests mb-4">
                    <h6 class="mb-1">Khách Hàng</h6>
                    <span class="badge rounded-pill bg-light text-dark text-wrap">
                      $phong_data[adult] Người Lớn
                    </span>
                    <span class="badge rounded-pill bg-light text-dark text-wrap">
                      $phong_data[children] Trẻ Em
                    </span>
                  </div>
                  $rating_data
                  <div class="d-flex justify-content-evenly mb-2">
                    $book_btn
                    <a href="room_details.php?id=$phong_data[id]" class="btn btn-sm btn-outline-dark shadow-none">Chi tiết</a>
                  </div>
                </div>
              </div>
            </div>
          data;

        }

      ?>

      <div class="col-lg-12 text-center mt-5">
        <a href="rooms.php" class="btn btn-sm btn-outline-dark rounded-0 fw-bold shadow-none">Xem Thêm >>></a>
      </div>
    </div>
  </div>

  <!-- Our Facilities -->

  <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font">CÁC TIỆN NGHI</h2>

  <div class="container">
    <div class="row justify-content-evenly px-lg-0 px-md-0 px-5">
      <?php 
        $res = mysqli_query($con,"SELECT * FROM `facilities` ORDER BY `id` DESC LIMIT 5");
       

        while($row = mysqli_fetch_assoc($res)){
          echo<<<data
            <div class="col-lg-2 col-md-2 text-center bg-white rounded shadow py-4 my-3">
           
              <h5 class="mt-3">$row[name]</h5>
            </div>
          data;
        }
      ?>

      <div class="col-lg-12 text-center mt-5">
        <a href="facilities.php" class="btn btn-sm btn-outline-dark rounded-0 fw-bold shadow-none">Xem thêm >>></a>
      </div>
    </div>
  </div>

  <!-- Testimonials -->

  <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font">ĐÁNH GIÁ TỪ KHÁCH HÀNG</h2>

  <div class="container mt-5">
    <div class="swiper swiper-testimonials">
      <div class="swiper-wrapper mb-5">
        <?php

          $review_q = "SELECT rr.*,uc.name AS uname, uc.profile, r.name AS rname FROM `rating_review` rr
            INNER JOIN `user_cred` uc ON rr.user_id = uc.id
            INNER JOIN `rooms` r ON rr.room_id = r.id
            ORDER BY `sr_no` DESC LIMIT 6";

          $review_res = mysqli_query($con,$review_q);
          $img_path = USERS_IMG_PATH;

          if(mysqli_num_rows($review_res)==0){
            echo 'Chưa có đánh giá nào!';
          }
          else
          {
            while($row = mysqli_fetch_assoc($review_res))
            {
              $stars = "<i class='bi bi-star-fill text-warning'></i> ";
              for($i=1; $i<$row['rating']; $i++){
                $stars .= " <i class='bi bi-star-fill text-warning'></i>";
              }

              echo<<<slides
                <div class="swiper-slide bg-white p-4">
                  <div class="profile d-flex align-items-center mb-3">
                    <img src="$img_path$row[profile]" class="rounded-circle" loading="lazy" width="30px">
                    <h6 class="m-0 ms-2">$row[uname]</h6>
                  </div>
                  <p>
                    $row[review]
                  </p>
                  <div class="rating">
                    $stars
                  </div>
                </div>
              slides;
            }
          }
        
        ?>
      </div>
      <div class="swiper-pagination"></div>
    </div>
    <div class="col-lg-12 text-center mt-5">
      <a href="about.php" class="btn btn-sm btn-outline-dark rounded-0 fw-bold shadow-none">Xem thêm >>></a>
    </div>
  </div>

  <!-- Reach us -->

  <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font">LIÊN HỆ</h2>

<div class="container">
    <div class="col-lg-4 col-md-4">
      <div class="bg-white p-4 rounded mb-4">
        <h5>Bạn cần hỗ trợ ? Hãy gọi ngay</h5>
        <a href="tel: +<?php echo $contact_r['pn1'] ?>" class="d-inline-block mb-2 text-decoration-none text-dark">
          <i class="bi bi-telephone-fill"></i> +<?php echo $contact_r['pn1'] ?>
        </a>
        <br>
        <?php 
          if($contact_r['pn2']!=''){
            echo<<<data
              <a href="tel: +$contact_r[pn2]" class="d-inline-block text-decoration-none text-dark">
                <i class="bi bi-telephone-fill"></i> +$contact_r[pn2]
              </a>
            data;
          }
        
        ?>
      </div>
    </div>
  </div>
</div>  



  <!-- Password reset modal and code -->

  <div class="modal fade" id="recoveryModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="recovery-form">
          <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center">
              <i class="bi bi-shield-lock fs-3 me-2"></i> Thiết lập mật khẩu mới
            </h5>
          </div>
          <div class="modal-body">
            <div class="mb-4">
              <label class="form-label">Mật khẩu mới</label>
              <input type="password" name="pass" required class="form-control shadow-none">
              <input type="hidden" name="email">
              <input type="hidden" name="token">
            </div>
            <div class="mb-2 text-end">
              <button type="button" class="btn shadow-none me-2" data-bs-dismiss="modal">HUỶ</button>
              <button type="submit" class="btn btn-dark shadow-none">XÁC NHẬN</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>


  <?php require('inc/footer.php'); ?>

  <?php
  
    if(isset($_GET['account_recovery']))
    {
      $data = filteration($_GET);

      $t_date = date("Y-m-d");

      $query = select("SELECT * FROM `user_cred` WHERE `email`=? AND `token`=? AND `t_expire`=? LIMIT 1",
        [$data['email'],$data['token'],$t_date],'sss');

      if(mysqli_num_rows($query)==1)
      {
        echo<<<showModal
          <script>
            var myModal = document.getElementById('recoveryModal');

            myModal.querySelector("input[name='email']").value = '$data[email]';
            myModal.querySelector("input[name='token']").value = '$data[token]';

            var modal = bootstrap.Modal.getOrCreateInstance(myModal);
            modal.show();
          </script>
        showModal;
      }
      else{
        alert("error","Invalid or Expired Link !");
      }

    }

  ?>
  
  <script src="https://unpkg.com/swiper@7/swiper-bundle.min.js"></script>

  <script>
    var swiper = new Swiper(".swiper-container", {
      spaceBetween: 30,
      effect: "fade",
      loop: true,
      autoplay: {
        delay: 3500,
        disableOnInteraction: false,
      }
    });

    var swiper = new Swiper(".swiper-testimonials", {
      effect: "coverflow",
      grabCursor: true,
      centeredSlides: true,
      slidesPerView: "auto",
      slidesPerView: "3",
      loop: true,
      coverflowEffect: {
        rotate: 50,
        stretch: 0,
        depth: 100,
        modifier: 1,
        slideShadows: false,
      },
      pagination: {
        el: ".swiper-pagination",
      },
      breakpoints: {
        320: {
          slidesPerView: 1,
        },
        640: {
          slidesPerView: 1,
        },
        768: {
          slidesPerView: 2,
        },
        1024: {
          slidesPerView: 3,
        },
      }
    });

    // recover account
    
    let recovery_form = document.getElementById('recovery-form');

    recovery_form.addEventListener('submit', (e)=>{
      e.preventDefault();

      let data = new FormData();

      data.append('email',recovery_form.elements['email'].value);
      data.append('token',recovery_form.elements['token'].value);
      data.append('pass',recovery_form.elements['pass'].value);
      data.append('recover_user','');

      var myModal = document.getElementById('recoveryModal');
      var modal = bootstrap.Modal.getInstance(myModal);
      modal.hide();

      let xhr = new XMLHttpRequest();
      xhr.open("POST","ajax/login_register.php",true);

      xhr.onload = function(){
        if(this.responseText == 'failed'){
          alert('error',"Đặt lại tài khoản không thành công!");
        }
        else{
          alert('success',"Đặt lại tài khoản thành công!");
          recovery_form.reset();
        }
      }

      xhr.send(data);
    });

  </script>

</body>
</html>