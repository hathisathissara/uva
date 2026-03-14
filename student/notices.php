<?php 
// paths (පිටුව තියෙන තැන අනුව වෙනස් කරගන්න)
$path = "../"; 
include('../includes/header.php'); 
include('../config/db.php');

// Search සහ Filter දත්ත ලබා ගැනීම
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

// Query එක සකස් කිරීම
$query = "SELECT * FROM notices WHERE 1";
if($search != '') {
    $query .= " AND (title LIKE '%$search%' OR message LIKE '%$search%')";
}
if($category != '') {
    $query .= " AND category = '$category'";
}
$query .= " ORDER BY id DESC";

$notices_run = mysqli_query($conn, $query);
?>

<div class="container-fluid px-4">
    <h3 class="mt-4 fw-bold text-dark">Notice Archive</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item active">All Notices</li>
    </ol>

    <!-- Search & Filter Section -->
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search notices..." value="<?= $search; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <option value="General" <?= $category == 'General' ? 'selected' : ''; ?>>General</option>
                        <option value="Exam" <?= $category == 'Exam' ? 'selected' : ''; ?>>Exam</option>
                        <option value="Event" <?= $category == 'Event' ? 'selected' : ''; ?>>Event</option>
                        <option value="Lab" <?= $category == 'Lab' ? 'selected' : ''; ?>>Lab Notice</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="notices.php" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Notices Display Area -->
    <div class="row">
        <?php if(mysqli_num_rows($notices_run) > 0): ?>
            <?php while($not = mysqli_fetch_array($notices_run)): 
                // කලින් අපි ලියපු CSS classes සහ icons තෝරා ගැනීම
                $cat_class = strtolower($not['category']);
                $icon = 'fa-info-circle';
                if($not['category'] == 'Exam') $icon = 'fa-file-signature';
                elseif($not['category'] == 'Event') $icon = 'fa-calendar-alt';
                elseif($not['category'] == 'Lab') $icon = 'fa-desktop';
            ?>
            <div class="col-md-12 mb-3">
                <!-- Archive Item Card -->
                <div class="card shadow-sm border-0 border-start border-5 border-<?= $cat_class; ?> notice-item-card">
                    <div class="card-body py-3 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="notice-icon bg-light me-3">
                                    <i class="fas <?= $icon; ?> text-<?= ($cat_class == 'lab') ? 'warning' : $cat_class; ?>"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold text-dark"><?= $not['title']; ?></h5>
                                    <small class="text-muted"><?= date('F d, Y', strtotime($not['created_at'])); ?> | Posted by Admin</small>
                                </div>
                            </div>
                            <span class="badge badge-<?= $cat_class; ?> shadow-sm"><?= $not['category']; ?></span>
                        </div>
                        <div class="mt-3">
                            <p class="text-secondary mb-0"><?= nl2br($not['message']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3 opacity-25"></i>
                <h5 class="text-muted">No notices found matching your criteria.</h5>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>