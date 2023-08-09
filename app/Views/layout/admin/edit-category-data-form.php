<!-- Main Sidebar Container -->
<?= $this->include("partials/admin/main-sidebar"); ?>
<!-- Main Sidebar Container -->

<!-- Content Wrapper. Contains page content -->

<div class="content-wrapper">
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row justify-content-center mb-10 mt-10">
        <!-- left column -->
        <div class="col-md-6">
          <!-- general form elements -->
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Edit Category Data</h3>
            </div>
           <?php 
           $option= ''; 
           if($info[0]['status'] == 'active') {
            $option .= "<option selected value=" .$info[0]['status'] . ">" . 'Active' . "</option>";
           } else {
            $option .= "<option selected value=" .$info[0]['status'] . "selected>" . 'Deactive' . "</option>";
           }

           ?>

            <!-- /.card-header -->
            <!-- form start -->
            <form method="post"  action="<?= $formUrl; ?>">
              <?php if (session()->getFlashdata('form_error')) : ?>
                <div class="alert alert-danger">
                  <ul>
                    <?php foreach (session()->getFlashdata('form_error') as $error) : ?>
                      <li><?= $error ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>
              <div class="card-body">
                <div class="col-xs-0 col-sm-6 col-md-12" style="text-align:center; color:<?php echo $status; ?>">
                  <b><?php echo $fmsg; ?></b>
                </div>
              <input type="hidden" name="id" value="<?= $info[0]['id']?>">
              <div class="form-group">
                  <label for="categoryName">Title</label>
                  <input type="text" id="categoryName" name="category_name" value="<?= $info[0]['category_name']?>" class="form-control"  placeholder="Category Name">
                </div>
                <div class="form-group d-flex flex-column">
                  <label for="description">Description</label>
                  <textarea name="description" value="<?= $info[0]['description']?>" id="description" cols="20" rows="5"><?= $info[0]['description']?></textarea>
                
                </div>
                <div class="form-group d-flex flex-column ">
                  <label for="status">Status</label>
                 <select name="status" id="status">
                  <option value="">Select Status</option>
                   <?=$option?>
                  <option value="active">Active</option>
                  <option value="deactive">Deactive</option>
                </select>
                
                </div>
              
              </div>
              <!-- /.card-body -->

              <div class="card-footer">
                <input type="hidden" id="totRow" name="totRow" value="<?php echo 1; ?>" />
                <button type="submit" class="btn btn-primary">Submit</button>
              </div>
            </form>
          </div>
          <!-- /.card -->
        </div>
        <!--/.col (left) -->
      </div>
      <!-- /.row -->
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->