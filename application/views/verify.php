<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="icon" href="<?php echo base_url()?>source/image/favicon.ico">
<meta name="robots" content="noindex, nofollow" />
<title>MCPE Panel</title>

<link href="<?php echo base_url();?>source/css/bootstrap.min.css" rel="stylesheet">
<link href="<?php echo base_url();?>source/css/theme.css" rel="stylesheet">
<link rel="stylesheet" href="<?php echo base_url();?>source/css/all.min.css">
<link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
</head>
<?php
    if($this->session->userdata('panelLog') == ""){
  	    redirect('login');
    } else if($this->session->userdata('panelLog') == "TRUE"){
  	    redirect('dashboard');
    }
?>
<body class="bg-light p-0">
  <div class="container">
    <div class="row justify-content-md-center">
      <div class="col-lg-6 col-sm-12">
        <div class="my-5 p-md-5 p-3 bg-white rounded box-shadow verify">
          <form action="<?php echo base_url();?>login/confirmOTP" method="post">
            <div class="text-center mb-4"> 
            	<img class="border rounded box-shadow mb-4" src="<?php echo base_url();?>source/image/logo.jpg" alt="logo" width="100" height="100">
            </div>
            <h1 class="h3 mb-3 font-weight-normal text-center">Plaese Verify Your OTP Number</h1>
            <p>Thanks for giving your login details. An OTP has been sent to your register Mobile Number. 
            Please enter the 8 digit OTP below for Successful Login.</p>
            <div class="form-label-group mb-3">
              <input type="password" class="form-control" name="confirm_otp" placeholder="Enter Your OTP Here">
              <small class="form-text text-muted"> Your OTP will be continue for 15 minutes. </small>
            </div>
            <div class="form-group text-center">
              <input class="btn btn-md btn-bg mr-3" type="submit" name="submit" value="Confirm OTP">
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
