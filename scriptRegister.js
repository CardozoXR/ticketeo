document.addEventListener("DOMContentLoaded", () => {
  const nombreInput = document.getElementById('nombre');
  const pinInputs = document.querySelectorAll('.pin-container input');
  const btnSubmit = document.querySelector('.btn-submit');

  pinInputs.forEach((input, index) => {
    
    input.addEventListener('input', (e) => {
      input.value = input.value.replace(/[^0-9]/g, '');

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

    const nombre = nombreInput.value.trim();
    let pinCompleto = '';
    
    pinInputs.forEach(input => { pinCompleto += input.value; });

    if (nombre === '') {
      alert('Por favor, ingresá tu nombre y apellido.');
      nombreInput.focus();
      return;
    }

    if (pinCompleto.length < 4) {
      alert('Por favor, completá el PIN de 4 dígitos.');
      return;
    }

    // --- ENVÍO AL BACKEND ---
    try {
      // Cambiar esta URL por la de la API real
      const respuesta = await fetch('https://ticketeo.uy/api/iniciar-caja', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          nombre: nombre,
          pin: pinCompleto
        })
      });

      const datos = await respuesta.json();

      if (respuesta.ok) {
        alert('¡Caja iniciada con éxito!');
        // Redirigir a otra pantalla: window.location.href = '/panel.html';
      } else {
        // Mostrar el error que devuelva el back
        alert('Error: ' + datos.mensaje);
      }

    } catch (error) {
      console.error('Error al conectar con el servidor:', error);
      alert('Hubo un problema al intentar conectar. Revisá tu conexión.');
    }
  });
});