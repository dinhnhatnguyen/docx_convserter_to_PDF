<?php
namespace App\Controllers;

class ConvertController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function convert() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES['docx_file'])) {
                $uploadDir = __DIR__ . '/../../uploads/';
                $uploadFile = $uploadDir . basename($_FILES['docx_file']['name']);

                if (move_uploaded_file($_FILES['docx_file']['tmp_name'], $uploadFile)) {
                    // Here we'll add the conversion logic
                    return json_encode(['success' => true, 'message' => 'File uploaded successfully']);
                }
            }
        }
        return json_encode(['success' => false, 'message' => 'No file uploaded']);
    }
}