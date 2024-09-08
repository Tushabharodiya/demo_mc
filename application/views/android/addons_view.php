<main role="main" class="container">
    <?php if(!empty($viewAddons)) { ?>
    <div class="my-3 p-3 bg-white rounded box-shadow">
        <div class="span border border-gray bg-light p-3">
            <h5 class="d-inline-block m-0"> Addons </h5>
            <small class="text-left ml-1"> All Addons </small>
            <p class="card-text text-success small mt-2">Hey! <b><?php if(!empty($this->session->userdata['member_name'])){ echo $this->session->userdata['member_name'];  ?> <?php } ?></b> You have all permission.  </p>
            <div class="btn btn-primary btn-sm mb-0 mb-md-0"><a href="<?php echo base_url();?>addons-new" class="text-white"> New Addons </a></div>
            <div class="btn btn-info btn-sm mb-0 mb-md-0"><a href="<?php echo base_url();?>copy-bucket/addons" class="text-white"> Copy Bucket </a></div>
            <?php $sessionAddonsView =  $this->session->userdata('session_addons_view'); ?>
            <form method="post">
                <div class="row">
                    <div class="col-md-9 mt-3">
                        <input type="text" name="search_addons_view" class="form-control" autocomplete="off" value="<?php echo $sessionAddonsView; ?>" placeholder="Search..." >
                    </div>
                    <div class="col-md3 mt-3">
                        <input type="submit" name="search" class="btn btn-outline-success" value="Search">
                        <input type="submit" name="reset" class="btn btn-outline-danger" value="Reset">
                    </div>
                </div>
            </form>
        </div>

        <div class="pt-2 overflow-none">
            <div class="row small">
                <?php foreach($viewAddons as $data) { ?>
                <div class="col-sm-6 col-md-6 col-lg-3">
                    <div class="card my-2">
                        <div class="p-2 bg-gray">
                            <img class="card-img-top" src="<?php echo $data['data_image']?>">
                        </div>
                        <div class="card-body">
                            <p class="card-title mb-1"><b><?php echo $data['data_name'] ?></b></p>
                            <p class="card-title mb-1">Category : <?php echo $data['category_name'] ?></p>
                            <p class="mb-1">
                                <img class="img-fluid" src="<?php echo base_url();?>source/image/icon_version.png" width = 16 height=16> <b class="mr-2"><?php echo $data['data_support_version'] ?></b>
                                <img class="img-fluid" src="<?php echo base_url();?>source/image/icon_size.png" width = 16 height=16> <b class="mr-2"><?php echo $data['data_size'] ?></b>
                                <img class="img-fluid" src="<?php echo base_url();?>source/image/icon_price.png" width = 16 height=16> <b><?php echo $data['data_price'] ?></b>
                            </p>
                            <p class="mb-1">
                                <img class="img-fluid" src="<?php echo base_url();?>source/image/icon_view.png" width = 16 height=16> <b class="mr-2"><?php echo $data['data_view'] ?></b>
                                <img class="img-fluid" src="<?php echo base_url();?>source/image/icon_download.png" width = 16 height=16> <b class="mr-2"><?php echo $data['data_download'] ?></b>
                            </p>
                            <p class="m-0">Bucket Copy : <?php echo $data['is_copy'] ?></p>
                            <p class="m-0">Status : <?php echo $data['data_status'] ?></p>
                            <div class="mt-2">
                                <div class="btn btn-primary btn-sm mb-0 mb-md-2"><a href="<?php echo base_url();?>addons-edit/<?php echo md5($data['unique_id']);?>" class="text-white"> Edit Data </a></div>
                            </div>
                        </div>
                    </div> 
                </div>
                <?php } ?>
            </div>
        </div>
        
        <ul class="pagination justify-content-center mt-3">
            <?php echo $this->pagination->create_links(); ?>
        </ul>
    </div>
    <?php } ?>
  
    <?php if(empty($viewAddons)) { ?>
        <div class="my-3 p-4 bg-white rounded box-shadow">
            <div class="span small text-center">
                <img src="<?php echo base_url();?>source/image/nodata.webp" alt="NoData" height="200" width="200">
                <h5 class="d-block mb-1">Addons Database is Empty</h5>
                <p class="d-block mb-3">Please add addons from the below button.</p>
                <div class="btn btn-primary btn-sm mb-0 mb-md-2"><a href="<?php echo base_url();?>addons-new" class="text-white"> New ddons </a></div>
            </div> 
        </div>
    <?php } ?>
</main>






