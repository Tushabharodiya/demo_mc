<main role="main" class="container">
<?php
    $appName = null; $appCode = null; $appTable = null; $appRsa = null;   

    if (form_error('app_name') != null){
        $appName = "Please enter app name *";
    }
    if (form_error('app_code') != null){
        $appCode = "Please enter app code *";
    }
    if (form_error('app_table') != null){
        $appTable = "Please enter app table *";
    }
    if (form_error('app_rsa') != null){
        $appRsa = "Please enter app rsa *";
    }
?>
<div class="my-3 p-3 bg-white rounded box-shadow">
    <div class="span border border-gray bg-light p-3">
        <h5 class="d-inline-block m-0"> App Data </h5>
        <small class="text-left ml-1"> New App Data </small> 
    </div>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="pt-3 overflow-none">     
            <div class="row small">
                <div class="form-group col-md-12">
                    <?php if($appName != null){ ?>
                        <label class="text-danger"><?php echo $appName; ?></label>
                    <?php } else { ?>
                        <label>App Name *</label>
                    <?php } ?>
                    <input type="text" name="app_name" class="form-control" placeholder="Enter App Name">
                </div>
                <div class="form-group col-md-12">
                    <?php if($appCode != null){ ?>
                        <label class="text-danger"><?php echo $appCode; ?></label>
                    <?php } else { ?>
                        <label>App Code *</label>
                    <?php } ?>
                    <input type="text" name="app_code" class="form-control" placeholder="Enter App Code">
                </div>
                <div class="form-group col-md-12">
                    <?php if($appTable != null){ ?>
                        <label class="text-danger"><?php echo $appTable; ?></label>
                    <?php } else { ?>
                        <label>App Table *</label>
                    <?php } ?>
                    <input type="text" name="app_table" class="form-control" placeholder="Enter App Table">
                </div>
                <div class="form-group col-md-12">
                    <?php if($appRsa != null){ ?>
                        <label class="text-danger"><?php echo $appRsa; ?></label>
                    <?php } else { ?>
                        <label>App RSA *</label>
                    <?php } ?>
                    <input type="text" name="app_rsa" class="form-control" placeholder="Enter App RSA">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
</main>
