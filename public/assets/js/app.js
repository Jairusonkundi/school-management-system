document.addEventListener('change', event => {
  if (event.target.name === 'student_id') {
    const selected = event.target.selectedOptions[0];
    const classInput = document.querySelector('input[name="class_id"]');
    if (classInput && selected?.dataset.class) classInput.value = selected.dataset.class;
  }
});
