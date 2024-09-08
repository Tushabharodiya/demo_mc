<main role="main" class="container">
<div class="my-3 p-3 bg-white rounded box-shadow">
    <div class="span border border-gray bg-light p-3">
        <h5 class="d-inline-block m-0"> Addons </h5>
        <small class="text-left ml-1"> Edit Addons </small>
        <p class="card-text text-success small mt-2">Hey! <b><?php if(!empty($this->session->userdata['member_name'])){ echo $this->session->userdata['member_name'];  ?> <?php } ?></b> You have all permission.  </p> 
    </div>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="pt-3 overflow-none">     
            <div class="row small">
                <div class="form-group col-md-12">
                    <label>Category Name *</label>
                    <select name="category_id" class="form-control selectpicker" data-live-search="true">
                        <option value="<?php echo $categoryAddonsData->category_id; ?>"><?php echo $categoryAddonsData->category_name; ?></option>
                        <?php foreach($viewCategoryAddons as $data) { ?>
                        <option value="<?php echo $data->category_id; ?>"><?php echo $data->category_name; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group col-md-12">
                    <label>Data Name *</label>
                    <input type="text" name="data_name" class="form-control" value="<?php echo $addonsData->data_name ?>" placeholder="Enter Data Name">
                </div>
                <div class="form-group col-md-12">
                    <label>Data Description *</label>
                    <textarea type="text" name="data_description" class="form-control" placeholder="Enter Data Description"><?php echo $addonsData->data_description ?></textarea> 
                </div>
                <div class="form-group col-md-12">
                    <label>Data Image *</label>
                    <div class="form-group custom-file">
                        <label class="custom-file-label" >Choose Image</label>
                        <input type="file" name="data_image" class="custom-file-input" value="<?php echo $addonsData->data_image ?>">
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label>Data Bundle *</label>
                    <div class="form-group custom-file">
                        <label class="custom-file-label" >Choose Bundle</label>
                        <input type="file" name="data_bundle" class="custom-file-input" value="<?php echo $addonsData->data_bundle ?>">
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label>Data Support Version *</label>
                    <input type="text" name="data_support_version" class="form-control" value="<?php echo $addonsData->data_support_version ?>" placeholder="Enter Data Support Version">
                </div>
                <div class="form-group col-md-12">
                    <label>Data Price *</label>
                    <input type="text" name="data_price" class="form-control" value="<?php echo $addonsData->data_price ?>" placeholder="Enter Data Price">
                </div>
                <div class="form-group col-md-12">
                    <label>Data Size *</label>
                    <input type="text" name="data_size" class="form-control" value="<?php echo $addonsData->data_size ?>" placeholder="Enter Data Size">
                </div>
                <div class="form-group col-md-12">
                    <label>Data View *</label>
                    <input type="text" name="data_view" class="form-control" value="<?php echo $addonsData->data_view ?>" placeholder="Enter Data View">
                </div>
                <div class="form-group col-md-12">
                    <label>Data Download *</label>
                    <input type="text" name="data_download" class="form-control" value="<?php echo $addonsData->data_download ?>" placeholder="Enter Data Download">
                </div>
                <div class="form-group col-md-12">
                    <label>Data Status *</label>
                    <select name="data_status" class="form-control">
                        <option value="<?php echo $addonsData->data_status ?>"> <?php echo $addonsData->data_status ?> </option>
                        <option value="publish">Publish</option>
                        <option value="unpublish ">Unpublish</option>
                    </select>
                </div>
            </div>
        </div>
        <?php if(!empty($this->session->userdata['member_role'])) { ?>
            <?php if($this->session->userdata['member_role'] == "Administrator"){ ?>
                <input type="submit" name="submit" value="Submit" class="btn btn-primary">
            <?php } else if($this->session->userdata['member_role'] == "Developer"){ ?>
                <input type="submit" name="submit" value="Submit" class="btn btn-primary" disabled>
            <?php } ?>
        <?php } ?>
    </form>
</div>
</main>

<script>
    $(".custom-file-input").on("change", function() {
      var fileName = $(this).val().split("\\").pop();
      $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
</script>

