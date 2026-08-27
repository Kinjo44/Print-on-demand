const form = document.querySelector('#order-form');
const fileInput = document.querySelector('#files');
const fileError = document.querySelector('#file-error');
const allowedExtensions = ['stl', 'stp', 'step'];
const maxFileSize = 15 * 1024 * 1024;

function formatFileSize(bytes) {
  return `${Math.round(bytes / (1024 * 1024))} Mo`;
}

function validateFiles() {
  const files = Array.from(fileInput.files);
  const invalidFiles = files.filter((file) => {
    const extension = file.name.split('.').pop().toLowerCase();
    return !allowedExtensions.includes(extension);
  });
  const oversizedFiles = files.filter((file) => file.size > maxFileSize);

  if (!files.length) {
    fileError.textContent = 'Veuillez ajouter au moins un fichier .stl, .stp ou .step.';
    return false;
  }

  if (invalidFiles.length) {
    fileError.textContent = `Format refusé : ${invalidFiles.map((file) => file.name).join(', ')}. Formats acceptés : .stl, .stp ou .step.`;
    fileInput.value = '';
    return false;
  }

  if (oversizedFiles.length) {
    fileError.textContent = `Fichier trop volumineux : ${oversizedFiles.map((file) => `${file.name} (${formatFileSize(file.size)})`).join(', ')}. Chaque fichier doit faire moins de 15 Mo.`;
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
