<?php 
$path = "../"; 
include('../includes/header.php'); 
include('../config/db.php');
?>

<div class="container-fluid px-4">
    <h3 class="mt-4 fw-bold">Notice Board Management</h3>
    
    <?php include('../message.php'); ?>

    <div class="row">
        <!-- Add Notice Form -->
        <div class="col-md-4">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white">Post New Notice</div>
                <div class="card-body">
                    <form action="code.php" method="POST">
                        <div class="mb-3">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Category</label>
                            <select name="category" class="form-select">
                                <option value="General">General</option>
                                <option value="Exam">Exam</option>
                                <option value="Event">Event</option>
                                <option value="Lab">Lab Notice</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Message</label>
                            <textarea name="message" rows="4" class="form-control" required></textarea>
                        </div>
                        <button type="submit" name="add_notice_btn" class="btn btn-primary w-100">Post Notice</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notices List -->
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-white fw-bold">Recent Notices</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $notices = mysqli_query($conn, "SELECT * FROM notices ORDER BY id DESC");
                                while($row = mysqli_fetch_array($notices)){
                                    ?>
                                    <tr>
                                        <td><?= $row['title']; ?></td>
                                        <td><span class="badge bg-info"><?= $row['category']; ?></span></td>
                                        <td><?= date('M d, Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <form action="code.php" method="POST">
                                                <input type="hidden" name="notice_id" value="<?= $row['id']; ?>">
                                                <button type="submit" name="delete_notice_btn" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>