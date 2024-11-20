<?php
// download.php

// Basic security checks
if (!isset($_GET['file'])) {
    die('No file specified');
}

$filename = basename($_GET['file']);
$filepath = __DIR__ . '/uploads/converted/' . $filename;

// Validate file exists and is within the correct directory
if (!file_exists($filepath) || !is_file($filepath)) {
    die('File not found');
}

// Check if file is actually a PDF
if (mime_content_type($filepath) !== 'application/pdf') {
    die('Invalid file type');
}

// Set headers for download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache, must-revalidate');

// Output file
readfile($filepath);
exit;