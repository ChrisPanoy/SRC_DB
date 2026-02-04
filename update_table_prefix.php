<?php
/**
 * Script to add lab_ prefix to all table references in PHP files
 * This will update all SQL queries to use the new table names
 */

// Table mapping: old_name => new_name
$tables = [
    'academic_year' => 'lab_academic_year',
    'admission' => 'lab_admission',
    'attendance' => 'lab_attendance',
    'course' => 'lab_course',
    'employees' => 'lab_employees',
    'facility' => 'lab_facility',
    'pc_assignment' => 'lab_pc_assignment',
    'schedule' => 'lab_schedule',
    'section' => 'lab_section',
    'semester' => 'lab_semester',
    'students' => 'lab_students',
    'subject' => 'lab_subject',
    'year_level' => 'lab_year_level'
];

// Directories to scan
$directories = [
    __DIR__ . '/admin',
    __DIR__ . '/ajax',
    __DIR__ . '/includes',
    __DIR__ . '/student',
    __DIR__ . '/teacher',
    __DIR__ // root directory
];

// Files to exclude
$excludeFiles = [
    'update_table_prefix.php',
    'PHPMailer',
    'vendor'
];

$totalFiles = 0;
$totalReplacements = 0;
$updatedFiles = [];

echo "Starting table prefix update...\n\n";

foreach ($directories as $dir) {
    if (!is_dir($dir)) continue;
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($files as $file) {
        if ($file->isDir()) continue;
        if ($file->getExtension() !== 'php') continue;
        
        $filePath = $file->getPathname();
        $fileName = $file->getFilename();
        
        // Skip excluded files
        $skip = false;
        foreach ($excludeFiles as $exclude) {
            if (strpos($filePath, $exclude) !== false) {
                $skip = true;
                break;
            }
        }
        if ($skip) continue;
        
        // Read file content
        $content = file_get_contents($filePath);
        $originalContent = $content;
        $fileReplacements = 0;
        
        // Replace each table name
        foreach ($tables as $oldTable => $newTable) {
            // Pattern 1: FROM table_name
            $pattern1 = '/\bFROM\s+' . $oldTable . '\b/i';
            $replacement1 = 'FROM ' . $newTable;
            $content = preg_replace($pattern1, $replacement1, $content, -1, $count1);
            $fileReplacements += $count1;
            
            // Pattern 2: JOIN table_name
            $pattern2 = '/\bJOIN\s+' . $oldTable . '\b/i';
            $replacement2 = 'JOIN ' . $newTable;
            $content = preg_replace($pattern2, $replacement2, $content, -1, $count2);
            $fileReplacements += $count2;
            
            // Pattern 3: INTO table_name
            $pattern3 = '/\bINTO\s+' . $oldTable . '\b/i';
            $replacement3 = 'INTO ' . $newTable;
            $content = preg_replace($pattern3, $replacement3, $content, -1, $count3);
            $fileReplacements += $count3;
            
            // Pattern 4: UPDATE table_name
            $pattern4 = '/\bUPDATE\s+' . $oldTable . '\b/i';
            $replacement4 = 'UPDATE ' . $newTable;
            $content = preg_replace($pattern4, $replacement4, $content, -1, $count4);
            $fileReplacements += $count4;
            
            // Pattern 5: TABLE table_name (for CREATE, DROP, ALTER, etc.)
            $pattern5 = '/\bTABLE\s+`?' . $oldTable . '`?\b/i';
            $replacement5 = 'TABLE `' . $newTable . '`';
            $content = preg_replace($pattern5, $replacement5, $content, -1, $count5);
            $fileReplacements += $count5;
            
            // Pattern 6: Backtick wrapped table names `table_name`
            $pattern6 = '/`' . $oldTable . '`/';
            $replacement6 = '`' . $newTable . '`';
            $content = preg_replace($pattern6, $replacement6, $content, -1, $count6);
            $fileReplacements += $count6;
        }
        
        // If content changed, write back to file
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $totalFiles++;
            $totalReplacements += $fileReplacements;
            $updatedFiles[] = [
                'file' => str_replace(__DIR__ . '/', '', $filePath),
                'replacements' => $fileReplacements
            ];
            echo "✓ Updated: " . str_replace(__DIR__ . '/', '', $filePath) . " ($fileReplacements replacements)\n";
        }
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Update Complete!\n";
echo str_repeat("=", 60) . "\n";
echo "Total files updated: $totalFiles\n";
echo "Total replacements: $totalReplacements\n";
echo "\nUpdated files:\n";
foreach ($updatedFiles as $info) {
    echo "  - {$info['file']} ({$info['replacements']} changes)\n";
}
echo "\n";
