<main role="main" class="container">
  <?php if(!empty($viewSearch)) { ?>
  <div class="my-3 p-3 bg-white rounded box-shadow">
    <div class="span border border-gray bg-light p-3">
      <h5 class="d-inline-block m-0"> Search </h5>
      <small class="text-left ml-1"> All Search </small>
      <p class="card-text text-success small mt-2">Hey! <b><?php if(!empty($this->session->userdata['member_name'])){ echo $this->session->userdata['member_name'];  ?> <?php } ?></b> You have all permission.  </p>
      <div class="btn btn-primary btn-sm mb-0 mb-md-0"><a href="<?php echo base_url();?>search-added-view" class="text-white"> View Search Added </a></div>
    </div>

    <div class="pt-3 overflow-none">
      <div class="table-responsive">
        <table class="table">
          <thead class="thead-dark small">
            <tr>
              <th>#</th>
              <th>Category</th>
              <th>Query</th>
              <th>Time</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <?php foreach($viewSearch as $data) { ?>
          <tr class="small">
            <th scope="row" width="5%"> <?php echo $data['search_id']; ?> </th>
            <td width="10%"> <?php echo $data['search_category']; ?> </td>
            <td width="40%"> <?php echo $data['search_query']; ?> </td>
            <td width="15%"> <?php echo $data['search_time']; ?> </td>
            <td width="10%"> <?php echo $data['search_status']; ?> </td>
            <?php if(!empty($this->session->userdata['member_role'])) { ?>
                <?php if($this->session->userdata['member_role'] == "Administrator") { ?>
                    <td width="20%"> 
                      <div class="btn btn-primary btn-sm mb-0 mb-md-0"><a href="<?php echo base_url();?>search-edit/<?php echo $data['search_id'] ?>" class="text-white"> Data Added </div>
                      <div class="btn btn-danger btn-sm mb-0 mb-md-0"><a href="<?php echo base_url();?>search-delete/<?php echo $data['search_id'] ?>" class="text-white"> Delete </div>
                    </td>
                <?php } else if($this->session->userdata['member_role'] == "Developer"){ ?>
                    <td width="20%"> 
                      <a href="<?php echo base_url();?>search-edit/<?php echo $data['search_id'] ?>" class="btn btn-primary btn-sm mb-0 mb-md-0 disabled">Data Added</a>
                      <a href="<?php echo base_url();?>search-delete/<?php echo $data['search_id'] ?>" class="btn btn-danger btn-sm mb-0 mb-md-0 disabled">Delete</a>
                    </td>
                <?php } ?>
            <?php } ?>
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
  <?php if(empty($viewSearch)) { ?>
    <div class="my-3 p-4 bg-white rounded box-shadow">
      <div class="span small text-center">
        <img src="<?php echo base_url();?>source/image/nodata.webp" alt="NoData" height="200" width="200">
        <h5 class="d-block mb-1">Search Database is Empty</h5>
        <p class="d-block mb-3">Please search added data from the below button.</p>
        <div class="btn btn-primary btn-sm mb-0 mb-md-2"><a href="<?php echo base_url();?>search-added-view" class="text-white"> View Search Added</a></div>
      </div> 
    </div>
  <?php } ?>
</main>






