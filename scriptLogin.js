    const form   = document.getElementById('formCaja');
    const celdas = document.querySelectorAll('.pin-container input');

    celdas.forEach((c, i) => {
      c.addEventListener('input', () => {
        c.value = c.value.replace(/\D/g, '');
        if (c.value && celdas[i + 1]) celdas[i + 1].focus();
      });
      c.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !c.value && celdas[i - 1]) celdas[i - 1].focus();
      });
    });

    form.addEventListener('submit', () => {
      document.getElementById('pin').value = [...celdas].map(c => c.value).join('');
    });