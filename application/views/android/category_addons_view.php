<main role="main" class="container">
  <?php if(!empty($viewCategoryAddons)) { ?>
  <div class="my-3 p-3 bg-white rounded box-shadow">
    <div class="span border border-gray bg-light p-3">
      <h5 class="d-inline-block m-0"> Category Addons </h5>
      <small class="text-left ml-1"> All Category Addons </small>
      <p class="card-text text-success small mt-2">Hey! <b><?php if(!empty($this->session->userdata['member_name'])){ echo $this->session->userdata['member_name'];  ?> <?php } ?></b> You have all permission.  </p>
      <div class="btn btn-primary btn-sm mb-0 mb-md-0"><a href="<?php echo base_url();?>category-addons-new" class="text-white"> New Category Addons </a></div>
    </div>

    <div class="pt-3 overflow-none">
      <div class="table-responsive">
        <table class="table">
          <thead class="thead-dark small">
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <?php foreach($viewCategoryAddons as $data) { ?>
          <tr class="small">
            <th scope="row"> <?php echo $data['category_id']; ?> </th>
            <td> <?php echo $data['category_name']; ?> </td>
            <td> <?php echo $data['category_status']; ?> </td>
            <td> 
              <div class="btn btn-primary btn-sm mb-0 mb-md-2"><a href="<?php echo base_url();?>category-addons-edit/<?php echo md5($data['category_id']);?>" class="text-white"><i class="far fa-edit"></i></a></div>
            </td>
          </tr>
          <?php } ?>
        </table>
      </div>
    </div>
    <ul class="pagination justify-content-center mt-3">
        <?php echo $this->pagination->create_links(); ?>
    </ul>
  </div>
  <?php } ?>
  <?php if(empty($viewCategoryAddons)) { ?>
    <div class="my-3 p-4 bg-white rounded box-shadow">
      <div class="span small text-center">
        <img src="<?php echo base_url();?>source/image/nodata.webp" alt="NoData" height="200" width="200">
        <h5 class="d-block mb-1">Category Addons Database is Empty</h5>
        <p class="d-block mb-3">Please add category addons from the below button.</p>
        <div class="btn btn-primary btn-sm mb-0 mb-md-2"><a href="<?php echo base_url();?>category-addons-new" class="text-white"> New Category Addons </a></div>
      </div> 
    </div>
  <?php } ?>
</main>






