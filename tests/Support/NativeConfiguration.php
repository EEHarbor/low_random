<?php

declare(strict_types=1);

// This file is required only by an owned -r probe, which cannot execute auto-prepend.
$files = [];
$selected = php_ini_loaded_file();
if ($selected !== false) {
    $files[] = $selected;
}
$scanned = php_ini_scanned_files();
if ($scanned !== false && $scanned !== '') {
    foreach (explode(',', $scanned) as $file) {
        $files[] = trim($file);
    }
}
$hashes = [];
foreach ($files as $file) {
    $hashes[] = ['path' => $file, 'sha256' => hash_file('sha256', $file)];
}
fwrite(STDOUT, json_encode(['prepend_base64' => base64_encode(ini_get('auto_prepend_file')),
    'files' => $hashes], JSON_THROW_ON_ERROR));
