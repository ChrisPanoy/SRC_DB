<?php
/**
 * DATA MIGRATION SCRIPT
 * Migrates data from src_db to isrc_db with lab_ prefix
 * 
 * This script will copy:
 * - Students
 * - Year Levels & Sections
 * - Subjects
 * - Schedules
 * - Employees (Teachers)
 * - Academic Years & Semesters
 * - Admissions (Enrollments)
 * - PC Assignments
 * - Attendance Records
 */

// Database connection settings
$host = "localhost";
$user = "root";
$pass = "";

echo "==============================================\n";
echo "DATA MIGRATION: src_db → isrc_db (lab_ prefix)\n";
echo "==============================================\n\n";

try {
    // Connect to MySQL server
    $conn = new mysqli($host, $user, $pass);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "✓ Connected to MySQL server\n\n";
    
    // Disable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Migration steps
    $migrations = [
        [
            'name' => 'Students',
            'query' => "INSERT INTO isrc_db.lab_students 
                (student_id, rfid_number, profile_picture, first_name, middle_name, last_name, suffix, gender)
                SELECT student_id, rfid_number, profile_picture, first_name, middle_name, last_name, suffix, gender
                FROM src_db.students
                ON DUPLICATE KEY UPDATE
                    rfid_number = VALUES(rfid_number),
                    profile_picture = VALUES(profile_picture),
                    first_name = VALUES(first_name),
                    middle_name = VALUES(middle_name),
                    last_name = VALUES(last_name),
                    suffix = VALUES(suffix),
                    gender = VALUES(gender)"
        ],
        [
            'name' => 'Year Levels',
            'query' => "INSERT INTO isrc_db.lab_year_level (year_id, year_name, level)
                SELECT year_id, year_name, level FROM src_db.year_level
                ON DUPLICATE KEY UPDATE year_name = VALUES(year_name), level = VALUES(level)"
        ],
        [
            'name' => 'Sections',
            'query' => "INSERT INTO isrc_db.lab_section (section_id, section_name, level)
                SELECT section_id, section_name, level FROM src_db.section
                ON DUPLICATE KEY UPDATE section_name = VALUES(section_name), level = VALUES(level)"
        ],
        [
            'name' => 'Courses',
            'query' => "INSERT INTO isrc_db.lab_course (course_id, course_code, course_name)
                SELECT course_id, course_code, course_name FROM src_db.course
                ON DUPLICATE KEY UPDATE course_code = VALUES(course_code), course_name = VALUES(course_name)"
        ],
        [
            'name' => 'Subjects',
            'query' => "INSERT INTO isrc_db.lab_subject (subject_id, subject_code, subject_name, units, lecture, laboratory)
                SELECT subject_id, subject_code, subject_name, units, lecture, laboratory FROM src_db.subject
                ON DUPLICATE KEY UPDATE 
                    subject_code = VALUES(subject_code),
                    subject_name = VALUES(subject_name),
                    units = VALUES(units),
                    lecture = VALUES(lecture),
                    laboratory = VALUES(laboratory)"
        ],
        [
            'name' => 'Employees (Teachers)',
            'query' => "INSERT INTO isrc_db.lab_employees (employee_id, firstname, lastname, email, password, role, profile_pic, barcode)
                SELECT employee_id, firstname, lastname, email, password, role, profile_pic, barcode FROM src_db.employees
                ON DUPLICATE KEY UPDATE 
                    firstname = VALUES(firstname),
                    lastname = VALUES(lastname),
                    email = VALUES(email),
                    password = VALUES(password),
                    role = VALUES(role),
                    profile_pic = VALUES(profile_pic),
                    barcode = VALUES(barcode)"
        ],
        [
            'name' => 'Facilities (Labs)',
            'query' => "INSERT INTO isrc_db.lab_facility (lab_id, lab_name, location)
                SELECT lab_id, lab_name, location FROM src_db.facility
                ON DUPLICATE KEY UPDATE lab_name = VALUES(lab_name), location = VALUES(location)"
        ],
        [
            'name' => 'Academic Years',
            'query' => "INSERT INTO isrc_db.lab_academic_year (ay_id, ay_name, date_start, date_end, status)
                SELECT ay_id, ay_name, date_start, date_end, status FROM src_db.academic_year
                ON DUPLICATE KEY UPDATE 
                    ay_name = VALUES(ay_name),
                    date_start = VALUES(date_start),
                    date_end = VALUES(date_end),
                    status = VALUES(status)"
        ],
        [
            'name' => 'Semesters',
            'query' => "INSERT INTO isrc_db.lab_semester (semester_id, ay_id, semester_now, status)
                SELECT semester_id, ay_id, semester_now, status FROM src_db.semester
                ON DUPLICATE KEY UPDATE 
                    ay_id = VALUES(ay_id),
                    semester_now = VALUES(semester_now),
                    status = VALUES(status)"
        ],
        [
            'name' => 'Schedules',
            'query' => "INSERT INTO isrc_db.lab_schedule (schedule_id, lab_id, subject_id, employee_id, time_start, time_end, schedule_days, academic_year_id, semester_id)
                SELECT schedule_id, lab_id, subject_id, employee_id, time_start, time_end, schedule_days, academic_year_id, semester_id FROM src_db.schedule
                ON DUPLICATE KEY UPDATE 
                    lab_id = VALUES(lab_id),
                    subject_id = VALUES(subject_id),
                    employee_id = VALUES(employee_id),
                    time_start = VALUES(time_start),
                    time_end = VALUES(time_end),
                    schedule_days = VALUES(schedule_days),
                    academic_year_id = VALUES(academic_year_id),
                    semester_id = VALUES(semester_id)"
        ],
        [
            'name' => 'Admissions (Enrollments)',
            'query' => "INSERT INTO isrc_db.lab_admission (admission_id, student_id, academic_year_id, semester_id, section_id, year_level_id, course_id, subject_id, schedule_id)
                SELECT admission_id, student_id, academic_year_id, semester_id, section_id, year_level_id, course_id, subject_id, schedule_id FROM src_db.admission
                ON DUPLICATE KEY UPDATE 
                    student_id = VALUES(student_id),
                    academic_year_id = VALUES(academic_year_id),
                    semester_id = VALUES(semester_id),
                    section_id = VALUES(section_id),
                    year_level_id = VALUES(year_level_id),
                    course_id = VALUES(course_id),
                    subject_id = VALUES(subject_id),
                    schedule_id = VALUES(schedule_id)"
        ],
        [
            'name' => 'PC Assignments',
            'query' => "INSERT INTO isrc_db.lab_pc_assignment (pc_assignment_id, student_id, lab_id, pc_number, date_assigned)
                SELECT pc_assignment_id, student_id, lab_id, pc_number, date_assigned FROM src_db.pc_assignment
                ON DUPLICATE KEY UPDATE 
                    student_id = VALUES(student_id),
                    lab_id = VALUES(lab_id),
                    pc_number = VALUES(pc_number),
                    date_assigned = VALUES(date_assigned)"
        ],
        [
            'name' => 'Attendance Records',
            'query' => "INSERT INTO isrc_db.lab_attendance (attendance_id, attendance_date, schedule_id, time_in, time_out, status, admission_id)
                SELECT attendance_id, attendance_date, schedule_id, time_in, time_out, status, admission_id FROM src_db.attendance
                ON DUPLICATE KEY UPDATE 
                    attendance_date = VALUES(attendance_date),
                    schedule_id = VALUES(schedule_id),
                    time_in = VALUES(time_in),
                    time_out = VALUES(time_out),
                    status = VALUES(status),
                    admission_id = VALUES(admission_id)"
        ]
    ];
    
    $totalMigrated = 0;
    $errors = [];
    
    foreach ($migrations as $migration) {
        echo "Migrating {$migration['name']}... ";
        
        try {
            $result = $conn->query($migration['query']);
            $affected = $conn->affected_rows;
            $totalMigrated += $affected;
            echo "✓ ($affected rows)\n";
        } catch (Exception $e) {
            echo "✗ FAILED\n";
            $errors[] = [
                'table' => $migration['name'],
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "MIGRATION SUMMARY\n";
    echo str_repeat("=", 60) . "\n";
    
    // Get counts from isrc_db
    $counts = [
        'Students' => 'lab_students',
        'Year Levels' => 'lab_year_level',
        'Sections' => 'lab_section',
        'Courses' => 'lab_course',
        'Subjects' => 'lab_subject',
        'Employees' => 'lab_employees',
        'Facilities' => 'lab_facility',
        'Academic Years' => 'lab_academic_year',
        'Semesters' => 'lab_semester',
        'Schedules' => 'lab_schedule',
        'Admissions' => 'lab_admission',
        'PC Assignments' => 'lab_pc_assignment',
        'Attendance' => 'lab_attendance'
    ];
    
    foreach ($counts as $name => $table) {
        $result = $conn->query("SELECT COUNT(*) as cnt FROM isrc_db.$table");
        $count = $result->fetch_assoc()['cnt'];
        echo sprintf("%-20s: %d records\n", $name, $count);
    }
    
    echo "\nTotal rows migrated: $totalMigrated\n";
    
    if (!empty($errors)) {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "ERRORS ENCOUNTERED\n";
        echo str_repeat("=", 60) . "\n";
        foreach ($errors as $error) {
            echo "Table: {$error['table']}\n";
            echo "Error: {$error['error']}\n\n";
        }
    } else {
        echo "\n✓ Migration completed successfully!\n";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}

echo "\n";
