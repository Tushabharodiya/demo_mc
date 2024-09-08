<main role="main" class="container">
<?php
    $notificationTitle = null; $notificationMessage = null; $notificationUrl = null; $notificationImage = null;   

    if (form_error('notification_title') != null){
        $notificationTitle = "Please enter notification title *";
    }
    if (form_error('notification_message') != null){
        $notificationMessage = "Please enter notification message *";
    }
    if (form_error('notification_url') != null){
        $notificationUrl = "Please enter notification url *";
    }
    if (form_error('notification_image') != null){
        $notificationImage = "Please enter notification image *";
    }
?>
<div class="my-3 p-3 bg-white rounded box-shadow">
    <div class="span border border-gray bg-light p-3">
        <h5 class="d-inline-block m-0"> Notification </h5>
        <small class="text-left ml-1"> New Notification </small> 
    </div>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="pt-3 overflow-none">     
            <div class="row small">
                <div class="form-group col-md-12">
                    <label>App Name *</label>
                    <select name="app_id" class="form-control selectpicker" data-live-search="true">
                        <?php if(!empty($appData)) { ?>
                            <?php foreach($appData as $data) {  ?>
                                <option value="<?php echo $data->app_id; ?>"><?php echo $data->app_name; ?></option>
                            <?php } ?>
                        <?php } else { ?>
                            <option value="">Empty</option>
                        <?php }  ?>
                    </select>
                </div>
                <div class="form-group col-md-12">
                    <?php if($notificationTitle != null){ ?>
                        <label class="text-danger"><?php echo $notificationTitle; ?></label>
                    <?php } else { ?>
                        <label>Notification Title *</label>
                    <?php } ?>
                    <input type="text" name="notification_title" class="form-control" placeholder="Enter Notification Title">
                </div>
                <div class="form-group col-md-12">
                    <?php if($notificationMessage != null){ ?>
                        <label class="text-danger"><?php echo $notificationMessage; ?></label>
                    <?php } else { ?>
                        <label>Notification Message *</label>
                    <?php } ?>
                    <input type="text" name="notification_message" class="form-control" placeholder="Enter Notification Message">
                </div>
                <div class="form-group col-md-12">
                    <?php if($notificationUrl != null){ ?>
                        <label class="text-danger"><?php echo $notificationUrl; ?></label>
                    <?php } else { ?>
                        <label>Notification URL *</label>
                    <?php } ?>
                    <input type="text" name="notification_url" class="form-control" placeholder="Enter Notification URL">
                </div>
                <div class="form-group col-md-12">
                    <?php if($notificationImage != null){ ?>
                        <label class="text-danger"><?php echo $notificationImage; ?></label>
                    <?php } else { ?>
                        <label>Notification Image *</label>
                    <?php } ?>
                    <input type="text" name="notification_image" class="form-control" placeholder="Enter Notification Image">
                </div>
                <div class="form-group col-md-12">
                    <label>Notification Status *</label>
                    <select name="notification_status" class="form-control">
                        <option value="publish">Publish</option>
                        <option value="unpublish ">Unpublish</option>
                    </select>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
</main>
