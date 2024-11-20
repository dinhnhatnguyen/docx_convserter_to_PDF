document.getElementById("uploadForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const formData = new FormData();
  const fileInput = document.getElementById("docx_file");
  const resultDiv = document.getElementById("result");

  if (fileInput.files.length === 0) {
    resultDiv.innerHTML = "Please select a file";
    return;
  }

  formData.append("docx_file", fileInput.files[0]);

  try {
    // Show loading message
    resultDiv.innerHTML = "Converting file, please wait...";

    const response = await fetch("/convert.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      // Create download link
      resultDiv.innerHTML = `
              <div class="success">
                  ${result.message}
                  <br>
                  <a href="${result.download_url}" class="download-btn">Download PDF</a>
              </div>
          `;
    } else {
      resultDiv.innerHTML = `<div class="error">Error: ${result.message}</div>`;
    }
  } catch (error) {
    console.error("Error:", error);
    resultDiv.innerHTML = "An error occurred during conversion";
  }
});
