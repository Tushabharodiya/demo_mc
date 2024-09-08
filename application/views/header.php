<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="icon" href="<?php echo base_url()?>source/image/favicon.ico">
<meta name="robots" content="noindex, nofollow" />
<title>MCPE Panel</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">

<script src="<?php echo base_url();?>source/js/jquery.min.js"></script> 
<script src="<?php echo base_url();?>source/js/popper.min.js"></script>
<script src="<?php echo base_url();?>source/js/bootstrap.min.js"></script>
<script src="<?php echo base_url();?>source/js/bootstrap-select.min.js"></script>

<link rel="stylesheet" href="<?php echo base_url();?>source/css/bootstrap.min.css" >
<link rel="stylesheet" href="<?php echo base_url();?>source/css/bootstrap-select.min.css">
<link rel="stylesheet" href="<?php echo base_url();?>source/css/theme.css">
<link rel="stylesheet" href="<?php echo base_url();?>source/css/all.min.css">

<!-- multiple drop down selection -->
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js"></script>
 
</head>
<?php
    if($this->session->userdata('panelLog') == ""){
        redirect('login');
    } else if($this->session->userdata('panelLog') == "FALSE"){
        redirect('confirmOTP');
    }
?>  
<body class="bg-light">

    <header>
        <nav class="navbar navbar-expand-md navbar-dark fixed-top navbg-color">
        <div class="container"> 
            <a class="navbar-brand" href="<?php echo base_url();?>">
            <?php if($this->session->userdata != null) { ?>
                <?php echo $this->session->userdata['member_role'] ?>
            <?php } ?>  
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span> </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarCollapse">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Dashboard </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="<?php echo base_url();?>"> Dashboard </a>  
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo base_url();?>search-view"> Search Query </a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Mods </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="<?php echo base_url();?>category-mods-view"> Mods Category </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo base_url();?>mods-view"> Mods Data </a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Addons </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="<?php echo base_url();?>category-addons-view"> Addons Category </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo base_url();?>addons-view"> Addons Data </a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Maps </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="<?php echo base_url();?>category-maps-view"> Maps Category </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo base_url();?>maps-view"> Maps Data </a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Seeds </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="<?php echo base_url();?>category-seeds-view"> Seeds Category </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo base_url();?>seeds-view"> Seeds Data </a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Textures </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="<?php echo base_url();?>category-textures-view"> Textures Category </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo base_url();?>textures-view"> Textures Data </a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Shaders </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="<?php echo base_url();?>category-shaders-view"> Shaders Category </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo base_url();?>shaders-view"> Shaders Data </a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Skin </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="<?php echo base_url();?>category-skin-view"> Skin Category </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo base_url();?>skin-view"> Skin Data </a>
                        </div>
                    </li>
                    <?php if(!empty($this->session->userdata['member_role'])) { ?>
                        <?php if($this->session->userdata['member_role'] == "Administrator") { ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Extra </a>
                                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="<?php echo base_url();?>app-view"> App Data </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="<?php echo base_url();?>notification-view"> Notification Data </a>
                                </div>
                            </li>
                        <?php } ?>
                    <?php } ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="<?php echo base_url();?>logout"> Logout </a>
                    </li>
                </ul>
            </div>
        </div>
        </nav>
    </header>
    
</body>
</html>



