<main role="main" class="container">
<div class="my-3 p-3 bg-white rounded box-shadow">
    <div class="span border border-gray bg-light p-3">
        <h5 class="d-inline-block m-0"> App Data </h5>
        <small class="text-left ml-1"> Edit App Data </small>
    </div>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="pt-3 overflow-none">     
            <div class="row small">
                <div class="form-group col-md-12">
                    <label>App Name *</label>
                    <input type="text" name="app_name" class="form-control" value="<?php echo $appData->app_name ?>" placeholder="Enter App Name">
                </div>
                <div class="form-group col-md-12">
                    <label>App Code *</label>
                    <input type="text" name="app_code" class="form-control" value="<?php echo $appData->app_code ?>" placeholder="Enter App Code">
                </div>
                <div class="form-group col-md-12">
                    <label>App Table *</label>
                    <input type="text" name="app_table" class="form-control" value="<?php echo $appData->app_table ?>" placeholder="Enter App Table">
                </div>
                <div class="form-group col-md-12">
                    <label>App RSA *</label>
                    <input type="text" name="app_rsa" class="form-control" value="<?php echo $appData->app_rsa ?>" placeholder="Enter App RSA">
                </div>
            </div>
        </div>
        <input type="submit" name="submit" value="Submit" class="btn btn-primary">
    </form>
</div>
</main>


