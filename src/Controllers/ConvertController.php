<?php
namespace App\Controllers;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

class ConvertController {
    private $db;
    private $uploadDir;
    private $outputDir;

    public function __construct($db = null) {
        $this->db = $db;
        $this->uploadDir = __DIR__ . '/../../uploads/';
        $this->outputDir = __DIR__ . '/../../uploads/converted/';
        
        // Create output directory if it doesn't exist
        if (!file_exists($this->outputDir)) {
            mkdir($this->outputDir, 0777, true);
        }
    }

    public function convert() {
        try {
            if (!isset($_FILES['docx_file'])) {
                throw new \Exception('No file uploaded');
            }

            $file = $_FILES['docx_file'];
            $originalName = $file['name'];
            $tempPath = $file['tmp_name'];
            
            // Validate file type
            $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if ($fileExtension !== 'docx') {
                throw new \Exception('Only DOCX files are allowed');
            }

            // Generate unique filenames
            $timestamp = time();
            $docxPath = $this->uploadDir . $timestamp . '_' . $originalName;
            $pdfName = pathinfo($originalName, PATHINFO_FILENAME) . '.pdf';
            $pdfPath = $this->outputDir . $timestamp . '_' . $pdfName;

            // Save uploaded file
            if (!move_uploaded_file($tempPath, $docxPath)) {
                throw new \Exception('Failed to save uploaded file');
            }

            // Convert DOCX to PDF
            $result = $this->convertDocxToPdf($docxPath, $pdfPath);

            // Save conversion record to database if db connection exists
            if ($this->db) {
                $stmt = $this->db->prepare("
                    INSERT INTO conversions (
                        original_filename, 
                        original_file_size,
                        converted_filename,
                        converted_file_size,
                        status,
                        ip_address
                    ) VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $originalName,
                    filesize($docxPath),
                    $pdfName,
                    filesize($pdfPath),
                    'completed',
                    $_SERVER['REMOTE_ADDR']
                ]);
            }

            // Return success response with download link
            $downloadUrl = '/download.php?file=' . basename($pdfPath);
            return json_encode([
                'success' => true,
                'message' => 'File converted successfully',
                'download_url' => $downloadUrl
            ]);

        } catch (\Exception $e) {
            // Log error
            error_log('Conversion error: ' . $e->getMessage());
            
            return json_encode([
                'success' => false,
                'message' => 'Conversion failed: ' . $e->getMessage()
            ]);
        }
    }

    private function convertDocxToPdf($docxPath, $pdfPath) {
        // Method 1: Using PhpWord
        try {
            Settings::setPdfRenderer(Settings::PDF_RENDERER_TCPDF, __DIR__ . '/../../vendor/tecnickcom/tcpdf');
            $phpWord = IOFactory::load($docxPath);
            $pdfWriter = IOFactory::createWriter($phpWord, 'PDF');
            $pdfWriter->save($pdfPath);
            return true;
        } catch (\Exception $e) {
            // If PhpWord fails, try LibreOffice
            return $this->convertUsingLibreOffice($docxPath, $pdfPath);
        }
    }

    private function convertUsingLibreOffice($docxPath, $pdfPath) {
        $command = "soffice --headless --convert-to pdf:writer_pdf_Export --outdir " . 
                   escapeshellarg(dirname($pdfPath)) . " " . 
                   escapeshellarg($docxPath) . " 2>&1";
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception('LibreOffice conversion failed: ' . implode("\n", $output));
        }
        
        // Rename the output file to match our expected path
        $defaultOutput = dirname($pdfPath) . '/' . pathinfo($docxPath, PATHINFO_FILENAME) . '.pdf';
        if (file_exists($defaultOutput) && $defaultOutput !== $pdfPath) {
            rename($defaultOutput, $pdfPath);
        }
        
        return true;
    }
}