const form = document.querySelector('#order-form');
const fileInput = document.querySelector('#files');
const fileError = document.querySelector('#file-error');
const allowedExtensions = ['stp', 'step'];

function validateFiles() {
  const files = Array.from(fileInput.files);
  const invalidFiles = files.filter((file) => {
    const extension = file.name.split('.').pop().toLowerCase();
    return !allowedExtensions.includes(extension);
  });

  if (!files.length) {
    fileError.textContent = 'Veuillez ajouter au moins un fichier .stp ou .step.';
    return false;
  }

  if (invalidFiles.length) {
    fileError.textContent = `Format refusé : ${invalidFiles.map((file) => file.name).join(', ')}.`;
    fileInput.value = '';
    return false;
  }

  fileError.textContent = '';
  return true;
}

fileInput.addEventListener('change', validateFiles);

form.addEventListener('submit', (event) => {
  if (!validateFiles() || !form.checkValidity()) {
    event.preventDefault();
    form.reportValidity();
  }
});
