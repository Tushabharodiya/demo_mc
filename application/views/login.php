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
  $emailError = null; $passwordError = null;
  if($this->session->userdata('panelLog') == "TRUE"){
    redirect('dashboard');
  } else if ($this->session->userdata('panelLog') == "FALSE"){
    redirect('confirmOTP');
  }
  
  if (form_error('member_email') != null & form_error('member_password') != null){
    $emailError = "Please enter email address";
    $passwordError = "Please enter password";
  } else if (form_error('member_email') != ""){
    $emailError = "Please enter email";
  } else if (form_error('member_password') != ""){
    $passwordError = "Please enter password";
  }
?>
<body class="bg-light p-0">
<div class="container">
  <div class="row justify-content-md-center">
    <div class="col-lg-6 col-sm-12">
      <div class="my-5 p-md-5 p-3 bg-white rounded box-shadow">
        <form action="" method="post">
          <div class="text-center mb-4"> 
            <img class="border rounded box-shadow mb-4" src="<?php echo base_url();?>source/image/logo.jpg" alt="logo" width="100" height="100">
          </div>
          <div class="form-label-group mb-3">
            <label>Email Address</label>
            <input type="email" id="inputEmail" class="form-control" name="member_email" placeholder="Enter Email Address">
            <?php if($emailError != null){ ?>
              <small class="form-text text-danger mt-2"><?php echo $emailError; ?></small>
            <?php } else { ?>
              <small class="form-text text-muted"> We'll never share your email with anyone else.</small>
            <?php } ?>  
          </div>
          <div class="form-label-group mb-3">
            <label>Password</label>
            <input type="password" id="inputPassword" class="form-control" name="member_password" placeholder="Enter Password">
            <?php if($passwordError != null){ ?>
              <small class="form-text text-danger mt-2"><?php echo $passwordError; ?></small>
            <?php } else { ?>
              <small class="form-text text-muted"> We'll never share your password with anyone else.</small>
            <?php } ?>
          </div>
          <div class="form-group">
            <input class="btn btn-md btn-bg mr-3" type="submit" value="Sign In" name="submit">
          </div>
          <?php if($error != ""){ ?>
          <small class="form-text text-danger mt-2"><?php echo $error; ?></small>
          <?php } ?> 
        </form>
      </div>
    </div>
  </div>
</div>
</body>
</html>

