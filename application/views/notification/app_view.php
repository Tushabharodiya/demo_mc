<main role="main" class="container">
  <?php if(!empty($viewApp)) { ?>
  <div class="my-3 p-3 bg-white rounded box-shadow">
    <div class="span border border-gray bg-light p-3">
      <h5 class="d-inline-block m-0"> App Data </h5>
      <small class="text-left ml-1"> All App Data </small><br>
      <div class="btn btn-primary btn-sm mb-0 mb-md-0 mt-4"><a href="<?php echo base_url();?>app-new" class="text-white"> New App </a></div>
    </div>

    <div class="pt-3 overflow-none">
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead class="thead-dark small">
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Code</th>
              <th>Table</th>
              <th>Active User</th>
              <th>Action</th>
            </tr>
          </thead>
          <?php foreach($viewApp as $data) { ?>
          <tr class="small">
            <th scope="row"> <?php echo $data['app_id']; ?> </th>
            <td> <?php echo $data['app_name']; ?> </td>
            <td> <?php echo $data['app_code']; ?> </td>
            <td> <?php echo $data['app_table']; ?> </td>
            <td> <?php echo $data['app_user']; ?> </td>
            <td> 
              <div class="btn btn-primary btn-sm mb-0 mb-md-2"><a href="<?php echo base_url();?>app-edit/<?php echo md5($data['app_id']);?>" class="text-white"><i class="far fa-edit"></i></a></div>
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
  <?php if(empty($viewApp)) { ?>
    <div class="my-3 p-4 bg-white rounded box-shadow">
      <div class="span small text-center">
        <img src="<?php echo base_url();?>source/image/nodata.webp" alt="NoData" height="200" width="200">
        <h5 class="d-block mb-1">App Data Database is Empty</h5>
        <p class="d-block mb-3">Please add app data from the below button.</p>
        <div class="btn btn-primary btn-sm mb-0 mb-md-2"><a href="<?php echo base_url();?>app-new" class="text-white"> New App Data </a></div>
      </div> 
    </div>
  <?php } ?>
</main>






