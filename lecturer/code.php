<?php
session_start();
include('../config/db.php');

// Security Check (Ensure only lecturers run this code)
if($_SESSION['auth_role'] != 'lecturer'){
    exit('Unauthorized');
}

// ==========================================
// 1. FETCH STUDENTS FOR A SPECIFIC CLASS (AJAX)
// ==========================================
if(isset($_POST['fetch_students']))
{
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);

    // JOIN Query: Enrollments table with Users table
    $query = "SELECT u.user_id, u.full_name, u.email, e.enrolled_date 
              FROM enrollments e 
              JOIN users u ON e.student_id = u.user_id 
              WHERE e.class_id = '$class_id'
              ORDER BY u.full_name ASC";
    
    $query_run = mysqli_query($conn, $query);

    if(mysqli_num_rows($query_run) > 0)
    {
        $count = 1;
        foreach($query_run as $student)
        {
            // Build the HTML Rows to send back
            echo '
            <tr>
                <td class="fw-bold text-muted">'.$count.'</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-2 fw-bold" style="width:30px; height:30px; font-size:12px;">
                            '.strtoupper(substr($student['full_name'], 0, 1)).'
                        </div>
                        <span class="fw-bold">'.$student['full_name'].'</span>
                    </div>
                </td>
                <td><span class="text-muted small">'.$student['email'].'</span></td>
                <td>'.date('d M Y', strtotime($student['enrolled_date'])).'</td>
            </tr>';
            $count++;
        }
    }
    else
    {
        // If no students are enrolled
        echo '<tr><td colspan="4" class="text-center text-danger py-4"><i class="fas fa-exclamation-circle me-2"></i> No students enrolled in this class yet.</td></tr>';
    }
    exit(0);
}

// ==========================================
// 2. UPLOAD LECTURE MATERIALS
// ==========================================
if(isset($_POST['upload_material_btn']))
{
    $class_id = $_POST['class_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);

    // Check if a file was selected and no upload errors occurred
    if(isset($_FILES['lecture_file']) && $_FILES['lecture_file']['error'] == 0)
    {
        // 1. Get File Info
        $file_name = $_FILES['lecture_file']['name'];
        $file_tmp  = $_FILES['lecture_file']['tmp_name'];
        $file_size = $_FILES['lecture_file']['size'];
        
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // 2. Validation: Allowed File Types
        $allowed_ext = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar', 'txt'];
        if(!in_array($file_ext, $allowed_ext)){
            $_SESSION['message'] = "Invalid File Type! Only PDF, PPT, Word, and ZIP files are allowed.";
            header("Location: upload_notes.php");
            exit(0);
        }

        // 3. Validation: Max File Size (10MB)
        if($file_size > 10485760){ // 10MB in Bytes
            $_SESSION['message'] = "File is too large! Max limit is 10MB.";
            header("Location: upload_notes.php");
            exit(0);
        }

        // 4. Create Unique File Name (to prevent overwriting files with same name)
        // Format: time()_RandomNumber_originalName.ext
        $new_file_name = time() . '_' . rand(100, 999) . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
        
        // Define Upload Directory (Make sure this folder exists in your project!)
        // Path: ../uploads/materials/
        $upload_dir = '../uploads/materials/';
        
        // Create directory if it doesn't exist
        if(!is_dir($upload_dir)){
            mkdir($upload_dir, 0777, true);
        }

        $destination = $upload_dir . $new_file_name;
        $db_path = 'uploads/materials/' . $new_file_name; // Path to save in DB

        // 5. Move File from Temp to Destination
        if(move_uploaded_file($file_tmp, $destination))
        {
            // Insert record into Database
            $query = "INSERT INTO materials (class_id, title, file_path) VALUES ('$class_id', '$title', '$db_path')";
            $query_run = mysqli_query($conn, $query);

            if($query_run) {
                $_SESSION['message'] = "Material Uploaded Successfully!";
            } else {
                $_SESSION['message'] = "Database Error: " . mysqli_error($conn);
            }
        }
        else
        {
            $_SESSION['message'] = "File Upload Failed! Check folder permissions.";
        }
    }
    else
    {
        $_SESSION['message'] = "Please select a valid file.";
    }

    header("Location: upload_notes.php");
    exit(0);
}

// ==========================================
// 3. DELETE LECTURE MATERIAL
// ==========================================
if(isset($_POST['delete_material_btn']))
{
    $material_id = $_POST['material_id'];
    $file_path = "../" . $_POST['file_path']; // Add ../ to go to root directory

    // 1. Delete record from database
    $query = "DELETE FROM materials WHERE material_id='$material_id'";
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        // 2. Delete actual file from the server folder
        if(file_exists($file_path)){
            unlink($file_path); // unlink() deletes the file
        }
        $_SESSION['message'] = "Material Deleted Successfully";
    } else {
        $_SESSION['message'] = "Something went wrong.";
    }

    header("Location: upload_notes.php");
    exit(0);
}
// ==========================================
// FYP REQUESTS LOGIC (Approve / Reject / Complete)
// ==========================================

// 1. APPROVE FYP
if(isset($_POST['approve_fyp_btn']))
{
    $project_id = $_POST['project_id'];

    $query = "UPDATE fyp_projects SET status='approved' WHERE project_id='$project_id'";
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        $_SESSION['message'] = "Project Approved Successfully!";
    } else {
        $_SESSION['message'] = "Something went wrong.";
    }

    header("Location: fyp_requests.php");
    exit(0);
}

// 2. REJECT FYP
if(isset($_POST['reject_fyp_btn']))
{
    $project_id = $_POST['project_id'];

    $query = "UPDATE fyp_projects SET status='rejected' WHERE project_id='$project_id'";
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        $_SESSION['message'] = "Project Rejected. The student will be notified.";
    } else {
        $_SESSION['message'] = "Something went wrong.";
    }

    header("Location: fyp_requests.php");
    exit(0);
}

// 3. MARK FYP AS COMPLETED (End of the year)
if(isset($_POST['complete_fyp_btn']))
{
    $project_id = $_POST['project_id'];

    $query = "UPDATE fyp_projects SET status='completed' WHERE project_id='$project_id'";
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        $_SESSION['message'] = "Project Marked as Completed!";
    } else {
        $_SESSION['message'] = "Something went wrong.";
    }

    header("Location: fyp_requests.php");
    exit(0);
}
// ==========================================
// ADD STUDENT RESULTS (MARKS)
// ==========================================
if(isset($_POST['save_marks_btn']))
{
    $class_id = $_POST['class_id'];
    $assessment_type = $_POST['assessment_type'];
    $total_marks = $_POST['total_marks'];
    
    // These are arrays sent from the form
    $enrollment_ids = $_POST['enrollment_ids'];
    $marks = $_POST['marks'];

    $success_count = 0;

    // Loop through each student's data
    for($i = 0; $i < count($enrollment_ids); $i++)
    {
        $enrollment_id = $enrollment_ids[$i];
        $mark_obtained = $marks[$i];

        // Only save if the lecturer actually typed a mark (Ignore empty boxes)
        if($mark_obtained != "")
        {
            // Check if result already exists for this specific assessment
            $check_q = "SELECT * FROM results WHERE enrollment_id='$enrollment_id' AND assessment_type='$assessment_type'";
            $check_run = mysqli_query($conn, $check_q);

            if(mysqli_num_rows($check_run) > 0)
            {
                // UPDATE existing mark
                $update_q = "UPDATE results SET marks_obtained='$mark_obtained', total_marks='$total_marks' 
                             WHERE enrollment_id='$enrollment_id' AND assessment_type='$assessment_type'";
                mysqli_query($conn, $update_q);
                $success_count++;
            }
            else
            {
                // INSERT new mark
                $insert_q = "INSERT INTO results (enrollment_id, assessment_type, marks_obtained, total_marks) 
                             VALUES ('$enrollment_id', '$assessment_type', '$mark_obtained', '$total_marks')";
                mysqli_query($conn, $insert_q);
                $success_count++;
            }
        }
    }

    if($success_count > 0){
        $_SESSION['message'] = "Marks saved successfully for $success_count student(s).";
    } else {
        $_SESSION['message'] = "No marks were entered. Nothing was saved.";
    }

    header("Location: add_results.php?class_id=".$class_id);
    exit(0);
}

// ==========================================
// TAKE STUDENT ATTENDANCE
// ==========================================
if(isset($_POST['save_attendance_btn']))
{
    $class_id = $_POST['class_id'];
    $attendance_date = $_POST['attendance_date'];
    
    // Arrays from the form
    $enrollment_ids = $_POST['enrollment_ids'];
    $statuses = $_POST['status']; // e.g., $statuses['enrollment_id'] = 'Present'
    $remarks_arr = $_POST['remarks'];

    $success_count = 0;

    // Loop through each student's enrollment ID
    foreach($enrollment_ids as $enroll_id)
    {
        // Get the radio button value (Present/Absent/Late) for this specific student
        $status = isset($statuses[$enroll_id]) ? $statuses[$enroll_id] : 'Present'; 
        
        // Get remarks if any
        $remarks = mysqli_real_escape_string($conn, $remarks_arr[$enroll_id]);

        // Check if attendance is already marked for this student on this date
        $check_q = "SELECT * FROM attendance WHERE enrollment_id='$enroll_id' AND attendance_date='$attendance_date'";
        $check_run = mysqli_query($conn, $check_q);

        if(mysqli_num_rows($check_run) > 0)
        {
            // Already marked, so UPDATE it
            $update_q = "UPDATE attendance SET status='$status', remarks='$remarks' 
                         WHERE enrollment_id='$enroll_id' AND attendance_date='$attendance_date'";
            mysqli_query($conn, $update_q);
            $success_count++;
        }
        else
        {
            // Not marked yet, so INSERT
            $insert_q = "INSERT INTO attendance (enrollment_id, attendance_date, status, remarks) 
                         VALUES ('$enroll_id', '$attendance_date', '$status', '$remarks')";
            mysqli_query($conn, $insert_q);
            $success_count++;
        }
    }

    $_SESSION['message'] = "Attendance Saved Successfully for " . date('d M Y', strtotime($attendance_date));
    header("Location: attendance.php?class_id=".$class_id."&attendance_date=".$attendance_date);
    exit(0);
}
// ==========================================
// UPDATE PROFILE
// ==========================================


if(isset($_POST['update_profile_btn']))
{
    $user_id = $_POST['user_id'];
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $old_image = $_POST['old_image'];
    $image_name = $_FILES['profile_image']['name'];

    // 1. ඉමේජ් එකක් අප්ලෝඩ් කරලා තියෙනවාද බලන්න
    if($image_name != NULL) {
        // ඉමේජ් එකට අලුත් නමක් හදනවා (Rename)
        $image_extension = pathinfo($image_name, PATHINFO_EXTENSION);
        $filename = time() . '.' . $image_extension; // උදා: 17123456.jpg

        $allowed_ext = ['png', 'jpg', 'jpeg'];
        if(!in_array($image_extension, $allowed_ext)) {
            $_SESSION['message'] = "Invalid image format! (Only JPG, PNG allowed)";
            header("Location: profile.php");
            exit(0);
        }
        $update_filename = $filename;
    } else {
        // අලුත් එකක් නැතිනම් පරණ එකම තියනවා
        $update_filename = $old_image;
    }

    // 2. පාස්වර්ඩ් එක චෙක් කිරීම (කලින් ලොජික් එකමයි)
    $new_password = $_POST['new_password'];
    if(!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET full_name='$full_name', phone='$phone', profile_pic='$update_filename', password_hash='$hashed_password' WHERE user_id='$user_id'";
    } else {
        $query = "UPDATE users SET full_name='$full_name', phone='$phone', profile_pic='$update_filename' WHERE user_id='$user_id'";
    }

    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        // Query එක සාර්ථක නම් විතරක් ෆයිල් එක අප්ලෝඩ් කරනවා
        if($image_name != NULL) {
            move_uploaded_file($_FILES['profile_image']['tmp_name'], '../uploads/profile/'.$filename);
            
            // පරණ ඉමේජ් එක 'default.png' නෙවෙයි නම් විතරක් මකන්න
            if($old_image != 'default.png' && file_exists("../uploads/profile/".$old_image)) {
                unlink("../uploads/profile/".$old_image);
            }
        }
        
        $_SESSION['auth_user']['name'] = $full_name; // නම අප්ඩේට් කිරීම
        $_SESSION['message'] = "Profile Updated Successfully!";
        header("Location: profile.php");
        exit(0);
    }
}
if(isset($_POST['update_theme']))
{
    $user_id = $_POST['user_id'];
    $theme = $_POST['theme'];

    $query = "UPDATE users SET theme='$theme' WHERE user_id='$user_id'";
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        echo "success";
    } else {
        echo "error";
    }
    exit();
}

?>