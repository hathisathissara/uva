<?php 
include('../includes/header.php'); 
include('../config/db.php');
?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Computer Lab Management</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Lab Schedules</li>
    </ol>

    <?php include('../message.php'); ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-desktop me-1"></i> Lab Timetable</h6>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLabModal">
                <i class="fas fa-plus-circle"></i> Book a Lab
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Lab Name</th>
                            <th>Day & Time</th>
                            <th>Allocated Class / Activity</th>
                            <th>Lecturer In-Charge</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // JOIN Query: ලස්සනට විස්තර ටික ගන්නවා
                        $query = "SELECT ls.*, co.course_name, u.full_name AS lecturer_name 
                                  FROM lab_schedules ls
                                  LEFT JOIN classes cl ON ls.class_id = cl.class_id
                                  LEFT JOIN courses co ON cl.course_code = co.course_code
                                  LEFT JOIN users u ON cl.lecturer_id = u.user_id
                                  ORDER BY 
                                    FIELD(ls.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                                    ls.start_time ASC";
                        
                        $query_run = mysqli_query($conn, $query);

                        if(mysqli_num_rows($query_run) > 0)
                        {
                            foreach($query_run as $row)
                            {
                                ?>
                                <tr>
                                    <td><?= $row['schedule_id']; ?></td>
                                    <td><span class="badge bg-primary"><?= $row['lab_name']; ?></span></td>
                                    <td>
                                        <div class="fw-bold"><?= $row['day_of_week']; ?></div>
                                        <small class="text-muted">
                                            <?= date("g:i A", strtotime($row['start_time'])); ?> - 
                                            <?= date("g:i A", strtotime($row['end_time'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if($row['course_name']): ?>
                                            <strong><?= $row['course_name']; ?></strong>
                                        <?php else: ?>
                                            <span class="text-secondary fst-italic">General / Free Usage</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $row['lecturer_name'] ? $row['lecturer_name'] : '-'; ?>
                                    </td>
                                    <td>
                                        <form action="code.php" method="POST" class="d-inline" onsubmit="return confirm('Delete this schedule?');">
                                            <input type="hidden" name="schedule_id" value="<?= $row['schedule_id']; ?>">
                                            <button type="submit" name="delete_lab_btn" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        else
                        {
                            echo "<tr><td colspan='6' class='text-center'>No Lab Schedules Found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Lab Schedule Modal -->
<div class="modal fade" id="addLabModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Book a Lab Session</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <form action="code.php" method="POST">
        <div class="modal-body">
            
            <div class="mb-3">
                <label>Select Lab</label>
                <select name="lab_name" class="form-select" required>
                    <option value="">-- Choose Lab --</option>
                    <option value="Network Lab">Network Lab</option>
                    <option value="Software Lab 01">Software Lab 01</option>
                    <option value="Software Lab 02">Software Lab 02</option>
                    <option value="Hardware Lab">Hardware Lab</option>
                    <option value="Multimedia Lab">Multimedia Lab</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Assign to a Class (Optional)</label>
                <select name="class_id" class="form-select">
                    <option value="">-- General Usage / No Specific Class --</option>
                    <?php
                    // Fetch Classes with Course Name & Lecturer
                    $class_q = "SELECT cl.class_id, co.course_name, u.full_name 
                                FROM classes cl 
                                JOIN courses co ON cl.course_code = co.course_code
                                LEFT JOIN users u ON cl.lecturer_id = u.user_id";
                    $class_run = mysqli_query($conn, $class_q);

                    if(mysqli_num_rows($class_run) > 0){
                        foreach($class_run as $cls){
                            ?>
                            <option value="<?= $cls['class_id']; ?>">
                                <?= $cls['course_name']; ?> (<?= $cls['full_name']; ?>)
                            </option>
                            <?php
                        }
                    }
                    ?>
                </select>
                <small class="text-muted">If the class is not listed, add it in 'Manage Classes' first.</small>
            </div>

            <div class="mb-3">
                <label>Day of Week</label>
                <select name="day" class="form-select" required>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Start Time</label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>End Time</label>
                    <input type="time" name="end_time" class="form-control" required>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="add_lab_btn" class="btn btn-primary">Save Schedule</button>
        </div>
      </form>

    </div>
  </div>
</div>

<?php include('../includes/footer.php'); ?>