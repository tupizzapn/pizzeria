/**
 * Configuración global
 */
const config = {
    minUsernameLength: 3,
    phonePattern: /^\d{9,15}$/
};

/**
 * Inicialización cuando el DOM esté listo
 */
document.addEventListener('DOMContentLoaded', function() {
    // Configurar eventos
    configurarEventos();
    
    // Mostrar lista por defecto
    cambiarVista('lista');
});

/**
 * Configura todos los eventos necesarios
 */
function configurarEventos() {
    // Botones de navegación
    document.getElementById('btnNuevo')?.addEventListener('click', function(e) {
        e.preventDefault();
        cambiarVista('formulario');
    });
    
    document.getElementById('btnEditar')?.addEventListener('click', function(e) {
        e.preventDefault();
        cambiarVista('lista');
    });

    // Validación de usuario existente
    document.getElementById('username')?.addEventListener('blur', validarUsuarioExistente);

    // Validación de formato de teléfono
    document.getElementById('telefono')?.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
}

/**
 * Cambia entre la vista de formulario y lista
 * @param {string} vista - 'formulario' o 'lista'
 */
function cambiarVista(vista) {
    const formulario = document.querySelector('.nuevo_ingreso');
    const lista = document.querySelector('.lista-usuarios');
    
    if (vista === 'formulario') {
        formulario.style.display = 'block';
        lista.style.display = 'none';
        limpiarFormulario();
    } else {
        formulario.style.display = 'none';
        lista.style.display = 'block';
    }
}

/**
 * Limpia el formulario de usuario
 */
function limpiarFormulario() {
    const form = document.querySelector('.nuevo_ingreso form');
    if (!form) return;

    form.reset();
    ocultarErrores();
}

/**
 * Oculta todos los mensajes de error
 */
function ocultarErrores() {
    const errorElements = document.querySelectorAll('.error-msg');
    errorElements.forEach(el => el.style.display = 'none');
    
    const submitButton = document.querySelector('button[name="agregar_usuario"]');
    if (submitButton) submitButton.disabled = false;
}

/**
 * Valida si el usuario ya existe
 */
async function validarUsuarioExistente() {
    const usernameInput = document.getElementById('username');
    const errorElement = document.getElementById('username-error');
    const submitButton = document.querySelector('button[name="agregar_usuario"]');
    
    if (!usernameInput || !errorElement || !submitButton) return;
    
    const username = usernameInput.value.trim();
    
    // Validación de longitud mínima
    if (username.length < config.minUsernameLength) {
        errorElement.textContent = `Mínimo ${config.minUsernameLength} caracteres`;
        errorElement.style.display = 'block';
        submitButton.disabled = true;
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE_URL}/api.php?username=${encodeURIComponent(username)}`);
        const data = await response.json();
        
        if (data.existe) {
            errorElement.textContent = 'Usuario ya registrado';
            errorElement.style.display = 'block';
            submitButton.disabled = true;
        } else {
            errorElement.style.display = 'none';
            submitButton.disabled = false;
        }
    } catch (error) {
        console.error('Error:', error);
        errorElement.textContent = 'Error al verificar';
        errorElement.style.display = 'block';
    }
}

/**
 * Valida el formato del teléfono
 */
function validarTelefono() {
    const telefonoInput = document.getElementById('telefono');
    if (!telefonoInput) return;
    
    return config.phonePattern.test(telefonoInput.value);
}