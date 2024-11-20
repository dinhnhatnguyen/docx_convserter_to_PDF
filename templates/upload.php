<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOCX to PDF Converter</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <div class="container">
        <h1>DOCX to PDF Converter</h1>
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="upload-area">
                <input type="file" id="docx_file" name="docx_file" accept=".docx" required>
                <label for="docx_file">Choose DOCX file or drag & drop here</label>
            </div>
            <button type="submit">Convert to PDF</button>
        </form>
        <div id="result"></div>
    </div>
    <script src="/public/js/main.js"></script>
</body>
</html>