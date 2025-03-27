const botonBienvenida = document.getElementById("botonBienvenida");
const formulariologin = document.getElementById("loginform");
const loginForm = document.getElementById("loginForm");

if (botonBienvenida && formulariologin && loginForm) {
    let isLoginVisible = false; // Estado para rastrear si el formulario está visible

    botonBienvenida.addEventListener("click", (event) => {
        if (!isLoginVisible) {
            // Primer clic: Mostrar el formulario y cambiar el texto a "Login"
            formulariologin.classList.add('active');
            botonBienvenida.classList.add('volteado');
            const span = botonBienvenida.querySelector('span');
            if (span) span.textContent = "Login";
            document.getElementById("username").focus(); // Enfocar el campo de usuario
            isLoginVisible = true; // Actualizar el estado
        } else {
            // Segundo clic: Enviar el formulario
            event.preventDefault(); // Evitar el comportamiento predeterminado del botón
            loginForm.submit(); // Enviar el formulario
        }
    });
} else {
    console.error("No se encontraron los elementos necesarios para el botón de bienvenida.");
}