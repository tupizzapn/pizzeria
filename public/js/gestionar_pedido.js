document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let contadorPizzas = 1;
    
    // Elementos del DOM
    const formularioPedido = document.getElementById('formularioPedido');
    const listaPedidos = document.getElementById('listaPedidos');
    const btnNuevo = document.getElementById('btnNuevo');
    const btnLista = document.getElementById('btnLista');
    const agregarPizzaBtn = document.getElementById('agregarPizzaBtn');
    const cancelarPedidoBtn = document.getElementById('cancelarPedidoBtn');
    const pedidoForm = document.getElementById('pedidoForm');
    
    // ========== INICIALIZAR SCROLLMANAGER ==========
    const pizzasContainer = document.getElementById('pizzas');
    const scrollManager = new ScrollManager({
        containerSelector: '#pizzas',
        itemSelector: '.pizza',
        maxHeight: '60vh',
        scrollbarColor: '#e74c3c'
    });
    
    // ========== FUNCIONES PARA CÁLCULO EN TIEMPO REAL ==========
    
    // Función para extraer precio del texto de la opción
    function extraerPrecio(textoOpcion) {
        const match = textoOpcion.match(/\$(\d+\.?\d*)/);
        return match ? parseFloat(match[1]) : 0;
    }
    
    // Calcular precio de una pizza individual
    function calcularPrecioPizza(pizzaElement) {
        const select = pizzaElement.querySelector('.select-pizza');
        const cantidadInput = pizzaElement.querySelector('input[type="number"]');
        const toppings = pizzaElement.querySelectorAll('input[type="checkbox"]:checked');
        
        // Obtener precio base y tamaño
        const textoOpcion = select.options[select.selectedIndex].text;
        const precioBase = extraerPrecio(textoOpcion);
        const tamaño = textoOpcion.includes('Familiar') ? 'familiar' : 'pequeña';
        const cantidad = parseInt(cantidadInput.value) || 0;
        
        // Sumar toppings
        let totalToppings = 0;
        toppings.forEach(topping => {
            const precio = parseFloat(topping.getAttribute(`data-precio-${tamaño}`)) || 0;
            totalToppings += precio;
        });
        
        return (precioBase + totalToppings) * cantidad;
    }
    
    // Actualizar resumen del pedido
    function actualizarResumen() {
        const pizzas = document.querySelectorAll('.pizza');
        let totalPizzas = 0;
        let totalPagar = 0;
        
        pizzas.forEach(pizza => {
            const precioPizza = calcularPrecioPizza(pizza);
            const cantidad = parseInt(pizza.querySelector('input[type="number"]').value) || 0;
            
            totalPizzas += cantidad;
            totalPagar += precioPizza;
        });
        
        // Actualizar UI
        document.getElementById('resumen-cantidad').textContent = totalPizzas;
        document.getElementById('resumen-total').textContent = totalPagar.toFixed(2);
        
        // Animación de cambio
        animarCambioPrecio();
    }
    
    // Animación para feedback visual
    function animarCambioPrecio() {
        const resumen = document.querySelector('.resumen-pedido');
        resumen.classList.add('precio-cambiando');
        setTimeout(() => resumen.classList.remove('precio-cambiando'), 300);
    }
    
    // Asignar eventos para cálculo
    function asignarEventosCalculo() {
        document.querySelectorAll('.select-pizza, input[type="number"]').forEach(el => {
            el.addEventListener('change', actualizarResumen);
        });
        
        document.querySelectorAll('.topping-item input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', actualizarResumen);
        });
    }
    
    // Observador para nuevas pizzas
    const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (mutation.addedNodes.length) {
                const nuevaPizza = document.querySelector('.pizza:last-child');
                nuevaPizza.querySelector('.select-pizza').addEventListener('change', actualizarResumen);
                nuevaPizza.querySelector('input[type="number"]').addEventListener('change', actualizarResumen);
                nuevaPizza.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    checkbox.addEventListener('change', actualizarResumen);
                });
                actualizarResumen();
            }
        });
    });
    
    observer.observe(document.getElementById('pizzas'), { childList: true });
    
    // ========== FUNCIONES EXISTENTES (MODIFICADAS) ==========
    
    function mostrarFormulario() {
        formularioPedido.classList.add('active');
        listaPedidos.classList.add('hidden');
    }
    
    function mostrarLista() {
        formularioPedido.classList.remove('active');
        listaPedidos.classList.remove('hidden');
    }
    
    function actualizarNumerosPizza() {
        document.querySelectorAll('.pizza').forEach((pizza, index) => {
            const numero = pizza.querySelector('.numero-pizza');
            if (numero) numero.textContent = `Pizza #${index + 1}`;
        });
    }
    
    function actualizarToppings(select) {
        const tamaño = select.options[select.selectedIndex].text.includes('Familiar') ? 'familiar' : 'pequeña';
        const pizzaContenedor = select.closest('.pizza');
        
        pizzaContenedor.querySelectorAll('.topping-item input[type="checkbox"]').forEach(topping => {
            const precioElement = topping.closest('.topping-item').querySelector('.topping-price');
            if (precioElement) {
                const precio = topping.getAttribute(`data-precio-${tamaño}`);
                precioElement.textContent = `($${precio})`;
            }
        });
        
        actualizarResumen(); // Actualizar al cambiar tamaño
    }
    
    function eliminarPizza(boton) {
        const pizzas = document.querySelectorAll('.pizza');
        if (pizzas.length <= 1) {
            alert('Debe haber al menos una pizza en el pedido.');
            return;
        }
        
        boton.closest('.pizza').style.transform = 'translateX(-100%)';
        boton.closest('.pizza').style.opacity = '0';
        
        setTimeout(() => {
            boton.closest('.pizza').remove();
            reorganizarIndicesPizzas();
            actualizarNumerosPizza();
            actualizarResumen(); // Actualizar al eliminar
        }, 300);
    }
    
    function reorganizarIndicesPizzas() {
        document.querySelectorAll('.pizza').forEach((pizza, index) => {
            pizza.querySelectorAll('[name^="pizzas["]').forEach(element => {
                const name = element.getAttribute('name').replace(/pizzas\[\d+\]/g, `pizzas[${index}]`);
                element.setAttribute('name', name);
                
                const id = element.getAttribute('id');
                if (id && (id.includes('pizza_') || id.includes('cantidad_'))) {
                    element.setAttribute('id', id.replace(/(pizza|cantidad)_\d+/, `$1_${index}`));
                }
            });
        });
        contadorPizzas = document.querySelectorAll('.pizza').length;
    }
    
    // ========== EVENT LISTENERS ==========
    
    btnNuevo.addEventListener('click', function(e) {
        e.preventDefault();
        mostrarFormulario();
    });
    
    btnLista.addEventListener('click', function(e) {
        e.preventDefault();
        mostrarLista();
    });
    
    cancelarPedidoBtn.addEventListener('click', function() {
        if(confirm('¿Estás seguro de que deseas cancelar este pedido?')) {
            pedidoForm.reset();
            mostrarLista();
        }
    });
    
    // Modificado para incluir eventos de cálculo
    agregarPizzaBtn.addEventListener('click', function() {
        const divPizzas = document.getElementById('pizzas');
        const primeraPizza = document.querySelector('.pizza');
        const nuevaPizza = document.createElement('div');
        nuevaPizza.className = 'pizza';
        
        const opcionesPizza = primeraPizza.querySelector('.select-pizza').innerHTML;
        const toppingsHTML = Array.from(primeraPizza.querySelectorAll('.columna-toppings'))
                             .map(col => col.innerHTML).join('');
        
        nuevaPizza.innerHTML = `
            <h3 class="numero-pizza">Pizza #${contadorPizzas + 1}</h3>
            <label for="pizza_${contadorPizzas}">Pizza:</label>
            <select id="pizza_${contadorPizzas}" name="pizzas[${contadorPizzas}][id]" required class="select-pizza">
                ${opcionesPizza}
            </select>

            <label for="cantidad_${contadorPizzas}">Cantidad:</label>
            <input type="number" id="cantidad_${contadorPizzas}" name="pizzas[${contadorPizzas}][cantidad]" value="1" min="1" required>

            <div class="toppings-label">Toppings:</div>
            <div class="toppings-grid">
                ${toppingsHTML.replace(/pizzas\[0\]/g, `pizzas[${contadorPizzas}]`)}
            </div>
            
            <button type="button" class="eliminar-pizza">- Pizza</button>
        `;
        
        divPizzas.appendChild(nuevaPizza);
        
        const selectPizza = nuevaPizza.querySelector('.select-pizza');
        selectPizza.addEventListener('change', function() {
            actualizarToppings(this);
        });
        
        nuevaPizza.querySelector('.eliminar-pizza').addEventListener('click', function(e) {
            e.stopPropagation();
            eliminarPizza(this);
        });
        
        actualizarToppings(selectPizza);
        contadorPizzas++;
        actualizarNumerosPizza();
        actualizarResumen();
        
        // Reemplazar scrollIntoView con ScrollManager
        scrollManager.scrollToElement(nuevaPizza);
    });
    
    pedidoForm.addEventListener('submit', function(e) {
        const telefonoCliente = document.getElementById('telefono_cliente').value.trim();
        
        if (!telefonoCliente) {
            e.preventDefault();
            alert('Por favor complete todos los datos del cliente.');
            return;
        }
        
        let cantidadTotal = 0;
        document.querySelectorAll('input[name^="pizzas["][name$="[cantidad]"]').forEach(input => {
            cantidadTotal += parseInt(input.value) || 0;
        });
        
        if (cantidadTotal === 0) {
            e.preventDefault();
            alert('Debe pedir al menos una pizza.');
            return;
        }
    });
    
    // ========== INICIALIZACIÓN ==========
    
    mostrarLista();
    asignarEventosCalculo();
    actualizarResumen(); // Calcular inicialmente
    actualizarNumerosPizza();
    
    // Asignar eventos a elementos iniciales
    document.querySelectorAll('.select-pizza').forEach(select => {
        select.addEventListener('change', function() {
            actualizarToppings(this);
        });
    });
    
    document.querySelectorAll('.eliminar-pizza').forEach(btn => {
        btn.addEventListener('click', function() {
            eliminarPizza(this);
        });
    });
});