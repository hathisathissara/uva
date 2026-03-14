<?php
session_start();
include('../config/db.php');

// 1. ADD USER
if(isset($_POST['add_user_btn']))
{
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = $_POST['role'];

    // Hash Password for Security
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    $check_email = "SELECT email FROM users WHERE email='$email'";
    $check_email_run = mysqli_query($conn, $check_email);

    if(mysqli_num_rows($check_email_run) > 0) {
        $_SESSION['message'] = "Email Already Exists";
        header("Location: users.php");
        exit(0);
    }
    else {
        $query = "INSERT INTO users (full_name, email, password_hash, role) VALUES ('$name', '$email', '$password_hashed', '$role')";
        $query_run = mysqli_query($conn, $query);

        if($query_run) {
            $_SESSION['message'] = "User Added Successfully";
            header("Location: users.php");
            exit(0);
        } else {
            $_SESSION['message'] = "Something Went Wrong";
            header("Location: users.php");
            exit(0);
        }
    }
}

// 2. UPDATE USER
if(isset($_POST['update_user_btn']))
{
    $user_id = $_POST['user_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Only update password if user typed a new one
    if(!empty($password)){
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET full_name='$name', email='$email', password_hash='$password_hashed', role='$role' WHERE user_id='$user_id'";
    } else {
        $query = "UPDATE users SET full_name='$name', email='$email', role='$role' WHERE user_id='$user_id'";
    }

    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        $_SESSION['message'] = "User Updated Successfully";
        header("Location: users.php");
        exit(0);
    }
}

// 3. DELETE USER
if(isset($_POST['delete_user_btn']))
{
    $user_id = $_POST['delete_user_id'];
    
    // Prevent deleting yourself
    if($user_id == $_SESSION['auth_user']['user_id']){
        $_SESSION['message'] = "You cannot delete yourself!";
        header("Location: users.php");
        exit(0);
    }

    $query = "DELETE FROM users WHERE user_id='$user_id' ";
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        $_SESSION['message'] = "User Deleted Successfully";
        header("Location: users.php");
        exit(0);
    } else {
        $_SESSION['message'] = "Something Went Wrong";
        header("Location: users.php");
        exit(0);
    }
}

// ==========================================
// COURSE MANAGEMENT LOGIC
// ==========================================

// 1. ADD COURSE
if(isset($_POST['add_course_btn']))
{
    $course_code = mysqli_real_escape_string($conn, $_POST['course_code']);
    $course_name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $credits = $_POST['credits'];
    $semester = $_POST['semester_level'];
    $desc = mysqli_real_escape_string($conn, $_POST['description']);

    // Check if Course Code already exists
    $check = "SELECT course_code FROM courses WHERE course_code='$course_code'";
    $check_run = mysqli_query($conn, $check);

    if(mysqli_num_rows($check_run) > 0){
        $_SESSION['message'] = "Course Code Already Exists!";
        header("Location: courses.php");
        exit(0);
    }

    $query = "INSERT INTO courses (course_code, course_name, credits, semester_level, description) VALUES ('$course_code', '$course_name', '$credits', '$semester', '$desc')";
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        $_SESSION['message'] = "Course Added Successfully";
        header("Location: courses.php");
        exit(0);
    } else {
        $_SESSION['message'] = "Something Went Wrong";
        header("Location: courses.php");
        exit(0);
    }
}

// 2. UPDATE COURSE
if(isset($_POST['update_course_btn']))
{
    $course_code = mysqli_real_escape_string($conn, $_POST['course_code']);
    $course_name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $credits = $_POST['credits'];
    $semester = $_POST['semester_level'];
    $desc = mysqli_real_escape_string($conn, $_POST['description']);

    $query = "UPDATE courses SET course_name='$course_name', credits='$credits', semester_level='$semester', description='$desc' WHERE course_code='$course_code'";
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        $_SESSION['message'] = "Course Updated Successfully";
        header("Location: courses.php");
        exit(0);
    }
}

// 3. DELETE COURSE
if(isset($_POST['delete_course_btn']))
{
    $course_code = mysqli_real_escape_string($conn, $_POST['course_code']);

    $query = "DELETE FROM courses WHERE course_code='$course_code'";
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        $_SESSION['message'] = "Course Deleted Successfully";
        header("Location: courses.php");
        exit(0);
    }
}

// ==========================================
// LAB SCHEDULE LOGIC
// ==========================================

// 1. ADD LAB SCHEDULE
if(isset($_POST['add_lab_btn']))
{
    $lab_name = $_POST['lab_name'];
    $day = $_POST['day'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    // Handle Empty Class Selection (Save as NULL in DB)
    $class_id = !empty($_POST['class_id']) ? "'".$_POST['class_id']."'" : "NULL";

    if($start_time >= $end_time){
        $_SESSION['message'] = "Start time must be before End time";
        header("Location: labs.php");
        exit(0);
    }

    // CONFLICT CHECK
    $check_query = "SELECT * FROM lab_schedules 
                    WHERE lab_name = '$lab_name' 
                    AND day_of_week = '$day' 
                    AND (
                        (start_time <= '$start_time' AND end_time > '$start_time') OR
                        (start_time < '$end_time' AND end_time >= '$end_time') OR
                        (start_time >= '$start_time' AND end_time <= '$end_time')
                    )";
    
    $check_run = mysqli_query($conn, $check_query);

    if(mysqli_num_rows($check_run) > 0){
        $_SESSION['message'] = "Conflict! This Lab is already booked.";
        header("Location: labs.php");
        exit(0);
    }

    // Insert Data (Notice I removed quotes around $class_id variable because I handled it above)
    $query = "INSERT INTO lab_schedules (lab_name, class_id, day_of_week, start_time, end_time) 
              VALUES ('$lab_name', $class_id, '$day', '$start_time', '$end_time')";
    
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        $_SESSION['message'] = "Lab Scheduled Successfully";
        header("Location: labs.php");
        exit(0);
    } else {
        $_SESSION['message'] = "Error: " . mysqli_error($conn);
        header("Location: labs.php");
        exit(0);
    }
}
// 2. DELETE LAB SCHEDULE
if(isset($_POST['delete_lab_btn']))
{
    $schedule_id = $_POST['schedule_id'];

    $query = "DELETE FROM lab_schedules WHERE schedule_id='$schedule_id'";
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        $_SESSION['message'] = "Schedule Removed Successfully";
        header("Location: labs.php");
        exit(0);
    }
}

// ==========================================
// CLASS MANAGEMENT LOGIC
// ==========================================

// 1. ADD CLASS
if(isset($_POST['add_class_btn']))
{
    $course_code = mysqli_real_escape_string($conn, $_POST['course_code']);
    $lecturer_id = $_POST['lecturer_id'];
    $academic_year = mysqli_real_escape_string($conn, $_POST['academic_year']);
    $semester = $_POST['semester'];

    // Check if this specific class already exists (Optional validation)
    // e.g., Same course, same year, same semester
    $check = "SELECT * FROM classes WHERE course_code='$course_code' AND academic_year='$academic_year' AND semester='$semester'";
    $check_run = mysqli_query($conn, $check);

    if(mysqli_num_rows($check_run) > 0){
        $_SESSION['message'] = "This Course is already scheduled for this Semester!";
        header("Location: classes.php");
        exit(0);
    }

    $query = "INSERT INTO classes (course_code, lecturer_id, academic_year, semester) VALUES ('$course_code', '$lecturer_id', '$academic_year', '$semester')";
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        $_SESSION['message'] = "Class Created Successfully";
        header("Location: classes.php");
        exit(0);
    } else {
        $_SESSION['message'] = "Something Went Wrong: " . mysqli_error($conn);
        header("Location: classes.php");
        exit(0);
    }
}

// 2. DELETE CLASS
if(isset($_POST['delete_class_btn']))
{
    $class_id = $_POST['class_id'];

    $query = "DELETE FROM classes WHERE class_id='$class_id'";
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        $_SESSION['message'] = "Class Deleted Successfully";
        header("Location: classes.php");
        exit(0);
    }
}


// ==========================================
// STUDENT ENROLLMENT LOGIC
// ==========================================

// 1. ENROLL STUDENTS (Batch Enroll)
if(isset($_POST['enroll_students_btn']))
{
    $class_id = $_POST['class_id'];
    $student_ids = $_POST['student_ids']; // This is an Array []

    $success_count = 0;
    $duplicate_count = 0;

    // Loop through each selected student
    foreach($student_ids as $student_id)
    {
        // First, check if the student is ALREADY enrolled in this class
        $check_query = "SELECT * FROM enrollments WHERE student_id='$student_id' AND class_id='$class_id'";
        $check_run = mysqli_query($conn, $check_query);

        if(mysqli_num_rows($check_run) > 0){
            // Already enrolled, skip this student
            $duplicate_count++;
        } else {
            // Not enrolled, insert into database
            $insert_query = "INSERT INTO enrollments (student_id, class_id) VALUES ('$student_id', '$class_id')";
            $insert_run = mysqli_query($conn, $insert_query);
            
            if($insert_run){
                $success_count++;
            }
        }
    }

    // Set dynamic message based on results
    if($success_count > 0 && $duplicate_count == 0){
        $_SESSION['message'] = "$success_count Student(s) Enrolled Successfully!";
    } elseif($success_count > 0 && $duplicate_count > 0){
        $_SESSION['message'] = "$success_count Enrolled Successfully. $duplicate_count were already enrolled.";
    } elseif($success_count == 0 && $duplicate_count > 0){
        $_SESSION['message'] = "All selected students are already enrolled in this class.";
    } else {
        $_SESSION['message'] = "Something went wrong. Please try again.";
    }

    header("Location: enrollments.php");
    exit(0);
}

// 2. UNENROLL STUDENT (Remove from class)
if(isset($_POST['unenroll_btn']))
{
    $enrollment_id = $_POST['enrollment_id'];

    $query = "DELETE FROM enrollments WHERE enrollment_id='$enrollment_id'";
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        $_SESSION['message'] = "Student Removed from Class Successfully";
        header("Location: enrollments.php");
        exit(0);
    } else {
        $_SESSION['message'] = "Error removing student.";
        header("Location: enrollments.php");
        exit(0);
    }
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

// ==========================================
// NOTICE BOARD LOGIC
// ==========================================

if(isset($_POST['add_notice_btn']))
{
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $category = $_POST['category'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $user_id = $_SESSION['auth_user']['user_id'];

    $query = "INSERT INTO notices (title, message, category, posted_by) VALUES ('$title', '$message', '$category', '$user_id')";
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        $_SESSION['message'] = "Notice Posted Successfully!";
        header("Location: notices.php");
        exit(0);
    }
}

if(isset($_POST['delete_notice_btn']))
{
    $id = $_POST['notice_id'];
    mysqli_query($conn, "DELETE FROM notices WHERE id='$id'");
    $_SESSION['message'] = "Notice Deleted!";
    header("Location: notices.php");
    exit(0);
}
?>
