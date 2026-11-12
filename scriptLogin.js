document.addEventListener("DOMContentLoaded", () => {
  const pinInputs = document.querySelectorAll('.pin-container input');
  const btnSubmit = document.querySelector('.btn-submit');

  pinInputs.forEach((input, index) => {
    input.addEventListener('input', () => {
      input.value = input.value.replace(/[^0-9]/g, ''); // Solo números

      if (input.value !== '' && index < pinInputs.length - 1) {
        pinInputs[index + 1].focus();
      }
    });

    input.addEventListener('keydown', (e) => {
      if (e.key === 'Backspace' && input.value === '' && index > 0) {
        pinInputs[index - 1].focus();
      }
    });
  });

  btnSubmit.addEventListener('click', async (e) => {
    e.preventDefault();

    let pinCompleto = '';
    pinInputs.forEach(input => { pinCompleto += input.value; });

    if (pinCompleto.length < 4) {
      alert('Por favor, completá tu PIN de 4 dígitos.');
      return;
    }

    // Conexión del back para validar el PIN
    try {
      /* Ejemplo de petición fetch:
      const respuesta = await fetch('/api/validar-pin', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pin: pinCompleto })
      });
      */
      
      console.log("Validando PIN:", pinCompleto);
      alert('Autenticando...'); // Reemplazar por lógica real
      
    } catch (error) {
      console.error('Error al validar:', error);
    }
  });
});