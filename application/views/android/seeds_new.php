<main role="main" class="container">
<?php
    $categoryName = null; $dataName = null;  $dataDescription = null;  $dataImage = null;  $dataBundle = null; 
    $dataSupportVersion = null;  $dataPrice = null;  $dataSize = null;  $dataView = null;  $dataDownload = null;   

    if (form_error('category_id') != null){
        $categoryName = "Please enter category name *";
    }
    if (form_error('data_name') != null){
        $dataName = "Please enter data name *";
    }
    if (form_error('data_description') != null){
        $dataDescription = "Please enter data description *";
    }
    if (form_error('data_image') != null){
        $dataImage = "Please enter data image *";
    }
    if (form_error('data_bundle') != null){
        $dataBundle = "Please enter data bundle *";
    }
    if (form_error('data_support_version') != null){
        $dataSupportVersion = "Please enter data support version *";
    }
    if (form_error('data_price') != null){
        $dataPrice = "Please enter data price *";
    }
    if (form_error('data_size') != null){
        $dataSize = "Please enter data size *";
    }
    if (form_error('data_view') != null){
        $dataView = "Please enter data view *";
    }
    if (form_error('data_download') != null){
        $dataDownload = "Please enter data download *";
    }
?>
<div class="my-3 p-3 bg-white rounded box-shadow">
    <div class="span border border-gray bg-light p-3">
        <h5 class="d-inline-block m-0"> Seeds </h5>
        <small class="text-left ml-1"> New Seeds </small> 
        <p class="card-text text-success small mt-2">Hey! <b><?php if(!empty($this->session->userdata['member_name'])){ echo $this->session->userdata['member_name'];  ?> <?php } ?></b> You have all permission.  </p>
    </div>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="pt-3 overflow-none">     
            <div class="row small">
                <div class="form-group col-md-12">
                    <?php if($categoryName != null){ ?>
                        <label class="text-danger"><?php echo $categoryName; ?></label>
                    <?php } else { ?>
                        <label>Category Name *</label>
                    <?php } ?>
                    <select name="category_id" class="form-control selectpicker" data-live-search="true">
                        <?php if(!empty($categorySeedsData)) { ?>
                            <?php foreach($categorySeedsData as $data) { ?>
                                <option value="<?php echo $data->category_id; ?>"><?php echo $data->category_name; ?></option>
                            <?php } ?>
                        <?php } else { ?>
                            <option value="">Empty</option>
                        <?php }  ?>
                    </select>
                </div>
                <div class="form-group col-md-12">
                    <?php if($dataName != null){ ?>
                        <label class="text-danger"><?php echo $dataName; ?></label>
                    <?php } else { ?>
                        <label>Data Name *</label>
                    <?php } ?>
                    <input type="text" name="data_name" class="form-control" placeholder="Enter Data Name">
                </div>
                <div class="form-group col-md-12">
                    <?php if($dataDescription != null){ ?>
                        <label class="text-danger"><?php echo $dataDescription; ?></label>
                    <?php } else { ?>
                        <label>Data Description *</label>
                    <?php } ?>
                    <textarea type="text" name="data_description" class="form-control" placeholder="Enter Data Description"></textarea>
                </div>
                <div class="form-group col-md-12">
                    <?php if($dataImage != null){ ?>
                        <label class="text-danger"><?php echo $dataImage; ?></label>
                    <?php } else { ?>
                        <label>Data Image *</label>
                    <?php } ?>
                    <div class="form-group custom-file">
                        <label class="custom-file-label" >Choose Image</label>
                        <input type="file" name="data_image" class="custom-file-input">
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <?php if($dataBundle != null){ ?>
                        <label class="text-danger"><?php echo $dataBundle; ?></label>
                    <?php } else { ?>
                        <label>Data Bundle *</label>
                    <?php } ?>
                    <div class="form-group custom-file">
                        <label class="custom-file-label" >Choose Bundle</label>
                        <input type="file" name="data_bundle" class="custom-file-input">
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <?php if($dataSupportVersion != null){ ?>
                        <label class="text-danger"><?php echo $dataSupportVersion; ?></label>
                    <?php } else { ?>
                        <label>Data Support Version *</label>
                    <?php } ?>
                    <input type="text" name="data_support_version" class="form-control" placeholder="Enter Data Support Version">
                </div>
                <div class="form-group col-md-12">
                    <?php if($dataPrice != null){ ?>
                        <label class="text-danger"><?php echo $dataPrice; ?></label>
                    <?php } else { ?>
                        <label>Data Price *</label>
                    <?php } ?>
                    <input type="text" name="data_price" class="form-control" placeholder="Enter Data Price">
                </div>
                <div class="form-group col-md-12">
                    <?php if($dataSize != null){ ?>
                        <label class="text-danger"><?php echo $dataSize; ?></label>
                    <?php } else { ?>
                        <label>Data Size *</label>
                    <?php } ?>
                    <input type="text" name="data_size" class="form-control" placeholder="Enter Data Size">
                </div>
                <div class="form-group col-md-12">
                    <?php if($dataView != null){ ?>
                        <label class="text-danger"><?php echo $dataView; ?></label>
                    <?php } else { ?>
                        <label>Data View *</label>
                    <?php } ?>
                    <input type="text" name="data_view" class="form-control" placeholder="Enter Data View">
                </div>
                <div class="form-group col-md-12">
                    <?php if($dataDownload != null){ ?>
                        <label class="text-danger"><?php echo $dataDownload; ?></label>
                    <?php } else { ?>
                        <label>Data Download *</label>
                    <?php } ?>
                    <input type="text" name="data_download" class="form-control" placeholder="Enter Data Download">
                </div>
                <div class="form-group col-md-12">
                    <label>Data Status *</label>
                    <select name="data_status" class="form-control">
                        <option value="publish">Publish</option>
                        <option value="unpublish ">Unpublish</option>
                    </select>
                </div>
            </div>
        </div>
        <?php if(!empty($this->session->userdata['member_role'])) { ?>
            <?php if($this->session->userdata['member_role'] == "Administrator"){ ?>
                <button type="submit" class="btn btn-primary">Submit</button>
            <?php } else if($this->session->userdata['member_role'] == "Developer"){ ?>
                <button type="submit" class="btn btn-primary" disabled>Submit</button>
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

