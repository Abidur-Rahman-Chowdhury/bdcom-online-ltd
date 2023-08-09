
  <!-- Main Sidebar Container -->
  <?= $this->include("partials/admin/main-sidebar");?>
  <!-- Main Sidebar Container -->
<!-- Main content -->
<div class="content-wrapper">
<?php if ($data) : ?>
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h1 class="card-title"><b></b></h1>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <table id="example2" class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <th>Category Name</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Active</th>
                    <th>Created At</th>
                    <th>Edit</th>
                    <th>Delete</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($data as $info) { ?>
                    <tr>
                      <td><?= $info['category_name']; ?></td>
                      <td><?= $info['title']; ?></td>
                      <td><?= $info['description']; ?></td>
                      <td><?= $info['status']; ?></td>
                      <td><?= date('Y-m-d', strtotime($info['created_at'])); ?></td>
                      <td> <a href="<?= base_url('admin/edit-item' .  '/'. $info['id']); ?>">Edit</a></td>
                      <td><a href="<?= base_url('admin/delete-item/' . $info['id']); ?>">Delete</a></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
  </section>


<?php else : ?>
  <a href="<?= base_url('admin/add-category'); ?>">
    <button class="btn btn-success ml-2 font-bold">Create</button>
  </a>
<?php endif; ?>
</div>
