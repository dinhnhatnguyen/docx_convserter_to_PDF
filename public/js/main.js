document.getElementById("uploadForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const formData = new FormData();
  const fileInput = document.getElementById("docx_file");
  formData.append("docx_file", fileInput.files[0]);

  try {
    const response = await fetch("/convert.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();
    document.getElementById("result").innerHTML = result.message;
  } catch (error) {
    console.error("Error:", error);
    document.getElementById("result").innerHTML =
      "An error occurred during conversion";
  }
});
