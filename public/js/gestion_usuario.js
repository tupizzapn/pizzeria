document.addEventListener('DOMContentLoaded', function() {
    const usernameInput = document.getElementById('username');
    const errorMessage = document.getElementById('username-error');
    const submitButton = document.querySelector('button[name="agregar_usuario"]');

    // Validación de usuario en tiempo real
    if (usernameInput && errorMessage && submitButton) {
        usernameInput.addEventListener('blur', function() {
            const username = this.value.trim();
            
            if (username) {
                fetch(`${API_BASE_URL}/api.php?username=${encodeURIComponent(username)}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Error en la solicitud');
                        return response.json();
                    })
                    .then(data => {
                        if (data.existe) {
                            errorMessage.textContent = 'El nombre de usuario ya está registrado.';
                            errorMessage.style.display = 'block';
                            submitButton.disabled = true;
                        } else {
                            errorMessage.style.display = 'none';
                            submitButton.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        errorMessage.textContent = 'Error al verificar el usuario.';
                        errorMessage.style.display = 'block';
                    });
            } else {
                errorMessage.style.display = 'none';
                submitButton.disabled = false;
            }
        });
    }

    // Validación de teléfono (solo números)
    const telefonoInput = document.getElementById('telefono');
    if (telefonoInput) {
        telefonoInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
    }

    // Mantén el código existente para mostrar/ocultar campos
    document.getElementById('rol').addEventListener('change', function() {
        const camposAdicionales = document.getElementById('campos-adicionales');
        camposAdicionales.style.display = 
            (this.value === 'pizzero' || this.value === 'delivery') ? 'block' : 'none';
    });
});