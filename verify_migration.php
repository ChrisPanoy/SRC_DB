<?php
/**
 * VERIFY MIGRATION
 * Quick verification script to check if data was migrated successfully
 */

$host = "localhost";
$user = "root";
$pass = "";
$db = "isrc_db";

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset('utf8mb4');
    
    echo "==============================================\n";
    echo "MIGRATION VERIFICATION REPORT\n";
    echo "==============================================\n\n";
    
    // Check each table
    $tables = [
        'lab_students' => 'Students',
        'lab_year_level' => 'Year Levels',
        'lab_section' => 'Sections',
        'lab_course' => 'Courses',
        'lab_subject' => 'Subjects',
        'lab_employees' => 'Employees',
        'lab_facility' => 'Facilities',
        'lab_academic_year' => 'Academic Years',
        'lab_semester' => 'Semesters',
        'lab_schedule' => 'Schedules',
        'lab_admission' => 'Admissions',
        'lab_pc_assignment' => 'PC Assignments',
        'lab_attendance' => 'Attendance Records'
    ];
    
    $totalRecords = 0;
    
    foreach ($tables as $table => $name) {
        $result = $conn->query("SELECT COUNT(*) as cnt FROM $table");
        $count = $result->fetch_assoc()['cnt'];
        $totalRecords += $count;
        
        $status = $count > 0 ? '✓' : '✗';
        echo sprintf("%s %-25s: %5d records\n", $status, $name, $count);
    }
    
    echo "\n" . str_repeat("-", 46) . "\n";
    echo sprintf("%-27s: %5d records\n", "TOTAL", $totalRecords);
    echo str_repeat("=", 46) . "\n\n";
    
    // Sample data verification
    echo "SAMPLE DATA VERIFICATION\n";
    echo str_repeat("=", 46) . "\n\n";
    
    // Check students
    echo "📚 Sample Students:\n";
    $students = $conn->query("SELECT student_id, first_name, last_name FROM lab_students LIMIT 5");
    while ($row = $students->fetch_assoc()) {
        echo "   - {$row['student_id']}: {$row['first_name']} {$row['last_name']}\n";
    }
    
    // Check subjects
    echo "\n📖 Sample Subjects:\n";
    $subjects = $conn->query("SELECT subject_code, subject_name FROM lab_subject LIMIT 5");
    while ($row = $subjects->fetch_assoc()) {
        echo "   - {$row['subject_code']}: {$row['subject_name']}\n";
    }
    
    // Check schedules
    echo "\n📅 Sample Schedules:\n";
    $schedules = $conn->query("
        SELECT 
            s.schedule_id,
            sub.subject_code,
            CONCAT(e.firstname, ' ', e.lastname) as teacher,
            s.time_start,
            s.time_end
        FROM lab_schedule s
        JOIN lab_subject sub ON s.subject_id = sub.subject_id
        JOIN lab_employees e ON s.employee_id = e.employee_id
        LIMIT 5
    ");
    while ($row = $schedules->fetch_assoc()) {
        echo "   - {$row['subject_code']} by {$row['teacher']} ({$row['time_start']}-{$row['time_end']})\n";
    }
    
    // Check active academic year and semester
    echo "\n🎓 Active Academic Settings:\n";
    $ay = $conn->query("SELECT ay_name FROM lab_academic_year WHERE status = 'Active' LIMIT 1");
    if ($ay && $ay->num_rows > 0) {
        $ayRow = $ay->fetch_assoc();
        echo "   - Academic Year: {$ayRow['ay_name']}\n";
    } else {
        echo "   - Academic Year: None active\n";
    }
    
    $sem = $conn->query("SELECT semester_now FROM lab_semester WHERE status = 'Active' LIMIT 1");
    if ($sem && $sem->num_rows > 0) {
        $semRow = $sem->fetch_assoc();
        echo "   - Semester: {$semRow['semester_now']}\n";
    } else {
        echo "   - Semester: None active\n";
    }
    
    echo "\n" . str_repeat("=", 46) . "\n";
    echo "✓ Migration verification complete!\n";
    echo str_repeat("=", 46) . "\n\n";
    
    $conn->close();
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}
