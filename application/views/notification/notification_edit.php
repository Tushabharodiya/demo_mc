<main role="main" class="container">
<div class="my-3 p-3 bg-white rounded box-shadow">
    <div class="span border border-gray bg-light p-3">
        <h5 class="d-inline-block m-0"> Notification </h5>
        <small class="text-left ml-1"> Edit Notification </small>
    </div>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="pt-3 overflow-none">     
            <div class="row small">
                <div class="form-group col-md-12">
                    <label>App Name *</label>
                    <select name="app_id" class="form-control selectpicker" data-live-search="true">
                        <option value="<?php echo $appData->app_id; ?>"><?php echo $appData->app_name; ?></option>
                        <?php foreach($viewApp as $data) { ?>
                        <option value="<?php echo $data->app_id; ?>"><?php echo $data->app_name; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group col-md-12">
                    <label>Notification Title *</label>
                    <input type="text" name="notification_title" class="form-control" value="<?php echo $notificationData->notification_title ?>" placeholder="Enter Notification Title">
                </div>
                <div class="form-group col-md-12">
                    <label>Notification Message *</label>
                    <input type="text" name="notification_message" class="form-control" value="<?php echo $notificationData->notification_message ?>" placeholder="Enter Notification Message">
                </div>
                <div class="form-group col-md-12">
                    <label>Notification URL *</label>
                    <input type="text" name="notification_url" class="form-control" value="<?php echo $notificationData->notification_url ?>" placeholder="Enter Notification URL">
                </div>
                <div class="form-group col-md-12">
                    <label>Notification Image *</label>
                    <input type="text" name="notification_image" class="form-control" value="<?php echo $notificationData->notification_image ?>" placeholder="Enter Notification Image">
                </div>
                <div class="form-group col-md-12">
                    <label>Notification Status *</label>
                    <select name="notification_status" class="form-control">
                        <option value="<?php echo $notificationData->notification_status ?>"> <?php echo $notificationData->notification_status ?> </option>
                        <option value="publish">Publish</option>
                        <option value="unpublish ">Unpublish</option>
                    </select>
                </div>
            </div>
        </div>
        <input type="submit" name="submit" value="Submit" class="btn btn-primary">
    </form>
</div>
</main>


