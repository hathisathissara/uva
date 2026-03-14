<?php 
include('../includes/header.php'); 
include('../config/db.php');
?>

<div class="container-fluid px-4">
    <h3 class="mt-4">User Management</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Users</li>
    </ol>

    <!-- Message Alert -->
    <?php include('../message.php'); ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-users me-1"></i> Registered Users</h6>
            <!-- Button to Open Modal -->
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-plus-circle"></i> Add User
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM users ORDER BY user_id DESC";
                        $query_run = mysqli_query($conn, $query);

                        if(mysqli_num_rows($query_run) > 0)
                        {
                            foreach($query_run as $row)
                            {
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <!-- Random Avatar based on name -->
                                            <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-2" style="width:35px; height:35px; font-size:14px;">
                                                <?= strtoupper(substr($row['full_name'], 0, 1)); ?>
                                            </div>
                                            <?= $row['full_name']; ?>
                                        </div>
                                    </td>
                                    <td><?= $row['email']; ?></td>
                                    <td>
                                        <?php 
                                        if($row['role'] == 'admin'){
                                            echo '<span class="badge bg-danger">Admin</span>';
                                        } elseif($row['role'] == 'lecturer'){
                                            echo '<span class="badge bg-warning text-dark">Lecturer</span>';
                                        } else {
                                            echo '<span class="badge bg-success">Student</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="user_edit.php?id=<?= $row['user_id']; ?>" class="btn btn-success btn-sm"><i class="fas fa-edit"></i></a>
                                        
                                        <form action="code.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            <input type="hidden" name="delete_user_id" value="<?= $row['user_id']; ?>">
                                            <button type="submit" name="delete_user_btn" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        else
                        {
                            echo "<tr><td colspan='5' class='text-center'>No Users Found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add New User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <form action="code.php" method="POST">
        <div class="modal-body">
            
            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="Enter full name">
            </div>

            <div class="mb-3">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="Enter email">
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Enter password">
            </div>

            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-select" required>
                    <option value="">--Select Role--</option>
                    <option value="admin">Admin</option>
                    <option value="lecturer">Lecturer</option>
                    <option value="student">Student</option>
                </select>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="add_user_btn" class="btn btn-primary">Save User</button>
        </div>
      </form>

    </div>
  </div>
</div>

<?php include('../includes/footer.php'); ?>