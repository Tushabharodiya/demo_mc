<main role="main" class="container">
<?php
    $categoryName = null; 

    if (form_error('category_name') != null){
        $categoryName = "Please enter category name *";
    }
?>
<div class="my-3 p-3 bg-white rounded box-shadow">
    <div class="span border border-gray bg-light p-3">
        <h5 class="d-inline-block m-0"> Category Skin </h5>
        <small class="text-left ml-1"> New Category Skin </small> 
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
                    <input type="text" name="category_name" class="form-control" placeholder="Enter Category Name">
                </div>
                <div class="form-group col-md-12">
                    <label>Category Status *</label>
                    <select name="category_status" class="form-control">
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
