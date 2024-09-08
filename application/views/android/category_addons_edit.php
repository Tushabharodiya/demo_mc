<main role="main" class="container">
<div class="my-3 p-3 bg-white rounded box-shadow">
    <div class="span border border-gray bg-light p-3">
        <h5 class="d-inline-block m-0"> Category Addons </h5>
        <small class="text-left ml-1"> Edit Category Addons </small>
        <p class="card-text text-success small mt-2">Hey! <b><?php if(!empty($this->session->userdata['member_name'])){ echo $this->session->userdata['member_name'];  ?> <?php } ?></b> You have all permission.  </p> 
    </div>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="pt-3 overflow-none">     
            <div class="row small">
                <div class="form-group col-md-12">
                    <label>Category Name *</label>
                    <input type="text" name="category_name" class="form-control" value="<?php echo $categoryAddonsData->category_name ?>" placeholder="Enter Category Name">
                </div>
                <div class="form-group col-md-12">
                    <label>Category Status *</label>
                    <select name="category_status" class="form-control">
                        <option value="<?php echo $categoryAddonsData->category_status ?>"> <?php echo $categoryAddonsData->category_status ?> </option>
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


