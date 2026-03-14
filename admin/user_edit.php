<?php 
include('../includes/header.php'); 
include('../config/db.php');
?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Edit User</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="users.php">Users</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>

    <div class="card shadow">
        <div class="card-header">
            <h6>Update User Details</h6>
        </div>
        <div class="card-body">
            
            <?php
            if(isset($_GET['id']))
            {
                $user_id = $_GET['id'];
                $users_query = "SELECT * FROM users WHERE user_id='$user_id' LIMIT 1";
                $users_run = mysqli_query($conn, $users_query);

                if(mysqli_num_rows($users_run) > 0)
                {
                    foreach($users_run as $user)
                    {
                    ?>
                    
                    <form action="code.php" method="POST">
                        <input type="hidden" name="user_id" value="<?= $user['user_id']; ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Full Name</label>
                                <input type="text" name="name" value="<?= $user['full_name']; ?>" class="form-control" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label>Email Address</label>
                                <input type="email" name="email" value="<?= $user['email']; ?>" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected':'' ?>>Admin</option>
                                    <option value="lecturer" <?= $user['role'] == 'lecturer' ? 'selected':'' ?>>Lecturer</option>
                                    <option value="student" <?= $user['role'] == 'student' ? 'selected':'' ?>>Student</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Password (Leave blank if not changing)</label>
                                <input type="password" name="password" class="form-control">
                            </div>

                            <div class="col-md-12 mb-3">
                                <button type="submit" name="update_user_btn" class="btn btn-primary">Update User</button>
                                <a href="users.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>

                    <?php
                    }
                }
                else
                {
                    echo "<h4>No Record Found</h4>";
                }
            }
            ?>

        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>