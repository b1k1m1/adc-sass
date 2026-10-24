<?php

$patterns = [
    'Save' => 'common.save',
    'Cancel' => 'common.cancel',
    'Edit' => 'common.edit',
    'Delete' => 'common.delete',
    'Add' => 'common.add',
    'Search' => 'common.search',
    'Reset' => 'common.reset',
    'Update' => 'common.update',
    'Close' => 'common.close',
    'Export' => 'common.export',
    'Print' => 'common.print',
    'View' => 'common.view',
    'Submit' => 'common.submit',
    'Confirm' => 'common.confirm',
    'Yes' => 'common.yes',
    'No' => 'common.no',
    'Active' => 'common.active',
    'Inactive' => 'common.inactive',
    'Status' => 'common.status',
    'Action' => 'common.action',
    'ID' => 'common.id',
    'Photo' => 'common.photo',
    'Description' => 'common.description',
    'Reference' => 'common.reference',
    'Amount' => 'common.amount',
    'Date' => 'common.date',
    'Remarks' => 'common.remarks',
    'Export to PDF' => 'report.export_pdf',
    'Export to Excel' => 'report.export_excel',
    'Print Table' => 'report.print_table',
];

$baseDir = getcwd();
$bladeDir = $baseDir . '/resources/views';
$results = [];

// Use glob to find all blade files
$files = glob($bladeDir . '/**/*.blade.php', GLOB_BRACE);

foreach ($files as $filePath) {
    $relativePath = str_replace($baseDir . '/', '', $filePath);
    $content = file_get_contents($filePath);
    $lines = file($filePath);
    
    foreach ($patterns as $text => $key) {
        // Skip if file already uses this translation key
        $alreadyTranslated = "t('" . $key . "'";
        if (strpos($content, $alreadyTranslated) !== false) {
            continue;
        }
        
        foreach ($lines as $lineNum => $line) {
            // Skip empty lines
            if (trim($line) === '') continue;
            
            // Skip lines already containing translation function
            if (strpos($line, "t('") !== false) continue;
            
            // Match patterns
            $found = false;
            $matchPattern = '';
            
            if (preg_match('/>\s*' . preg_quote($text, '/') . '\s*</', $line)) {
                $found = true;
                $matchPattern = '>' . $text . '<';
            }
            elseif (preg_match('/>\s*' . preg_quote($text, '/') . '\s*<\/button/', $line)) {
                $found = true;
                $matchPattern = '>' . $text . '</button';
            }
            elseif (preg_match('/>\s*' . preg_quote($text, '/') . '\s*<\/a/', $line)) {
                $found = true;
                $matchPattern = '>' . $text . '</a';
            }
            elseif (preg_match('/>\s*' . preg_quote($text, '/') . '\s*<\/label/', $line)) {
                $found = true;
                $matchPattern = '>' . $text . '</label';
            }
            elseif (preg_match('/>\s*' . preg_quote($text, '/') . '\s*<\/option/', $line)) {
                $found = true;
                $matchPattern = '>' . $text . '</option';
            }
            elseif (preg_match('/>\s*' . preg_quote($text, '/') . '\s*<\/span/', $line)) {
                $found = true;
                $matchPattern = '>' . $text . '</span';
            }
            
            if ($found) {
                $results[$relativePath][] = [
                    'line' => $lineNum + 1,
                    'key' => $key,
                    'text' => $text,
                    'pattern' => $matchPattern,
                    'original' => trim($line)
                ];
            }
        }
    }
}

// Remove duplicates per file
foreach ($results as $file => $changes) {
    $seen = [];
    $unique = [];
    foreach ($changes as $change) {
        $sig = $change['line'] . '-' . $change['text'];
        if (!isset($seen[$sig])) {
            $seen[$sig] = true;
            $unique[] = $change;
        }
    }
    $results[$file] = $unique;
}

// Generate report
$report = "================================================================================\n";
$report .= "BLADE FILES REQUIRING TRANSLATION CONVERSION\n";
$report .= "================================================================================\n\n";
$report .= "Pattern to use: {{ t('key', 'Default Text') }}\n";
$report .= "Example: <button>Save</button> becomes <button>{{ t('common.save', 'Save') }}</button>\n\n";

$totalChanges = 0;
$fileCount = 0;
foreach ($results as $file => $changes) {
    if (empty($changes)) continue;
    $fileCount++;
    
    $report .= "FILE: $file\n";
    $report .= str_repeat('-', 80) . "\n";
    foreach ($changes as $change) {
        $report .= sprintf("  Line %4d | Key: %-25s | Replace: '%s' with {{ t('%s', '%s') }}\n", 
            $change['line'], 
            $change['key'], 
            $change['text'],
            $change['key'],
            $change['text']
        );
        $totalChanges++;
    }
    $report .= "\n";
}

$report .= "================================================================================\n";
$report .= "SUMMARY\n";
$report .= "================================================================================\n";
$report .= "Total files: $fileCount\n";
$report .= "Total changes needed: $totalChanges\n\n";

$report .= "Translation Keys Reference:\n";
$report .= "----------------------------\n";
foreach ($patterns as $text => $key) {
    $report .= "  $key => '$text'\n";
}

file_put_contents($baseDir . '/translations_required_in_code.txt', $report);
echo "Report generated: translations_required_in_code.txt\n";
echo "Total files: $fileCount\n";
echo "Total changes needed: $totalChanges\n";
