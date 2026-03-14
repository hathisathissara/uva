<?php
session_start();
include('../config/db.php');

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
// FYP SUBMISSION
// ==========================================


if(isset($_POST['submit_fyp_btn']))
{
    $student_id = $_SESSION['auth_user']['user_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $supervisor_id = $_POST['supervisor_id'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    // File Upload Logic
    if(isset($_FILES['proposal_file']) && $_FILES['proposal_file']['error'] == 0)
    {
        $file_name = $_FILES['proposal_file']['name'];
        $file_tmp = $_FILES['proposal_file']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if($file_ext == 'pdf')
        {
            $new_file_name = "FYP_" . $student_id . "_" . time() . ".pdf";
            $upload_path = "../uploads/fyp/" . $new_file_name;
            $db_path = "uploads/fyp/" . $new_file_name;

            if(move_uploaded_file($file_tmp, $upload_path))
            {
                // Check if it's a resubmission (if status was rejected)
                $check_old = mysqli_query($conn, "SELECT * FROM fyp_projects WHERE student_id='$student_id'");
                
                if(mysqli_num_rows($check_old) > 0) {
                    // Update existing
                    $query = "UPDATE fyp_projects SET title='$title', description='$description', supervisor_id='$supervisor_id', submission_file='$db_path', status='pending' WHERE student_id='$student_id'";
                } else {
                    // Insert new
                    $query = "INSERT INTO fyp_projects (title, description, student_id, supervisor_id, submission_file, status) 
                              VALUES ('$title', '$description', '$student_id', '$supervisor_id', '$db_path', 'pending')";
                }

                $query_run = mysqli_query($conn, $query);

                if($query_run) {
                    $_SESSION['message'] = "Proposal Submitted Successfully!";
                    header("Location: submit_fyp.php");
                    exit(0);
                }
            }
        } else {
            $_SESSION['message'] = "Only PDF files are allowed.";
            header("Location: submit_fyp.php");
            exit(0);
        }
    }
}

?>