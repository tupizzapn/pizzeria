/**
 * ScrollManager - Gestor reutilizable de scroll para contenedores
 * @version 1.0.0
 * @license MIT
 */
class ScrollManager {
    /**
     * @param {Object} options - Configuración del ScrollManager
     * @param {string} options.containerSelector - Selector del contenedor con scroll
     * @param {string} options.itemSelector - Selector de los elementos dentro del contenedor
     * @param {number} [options.maxHeight=60] - Altura máxima en vh (viewport height)
     * @param {boolean} [options.autoScroll=true] - Scroll automático al agregar elementos
     * @param {boolean} [options.smoothScroll=true] - Scroll suave
     * @param {string} [options.scrollbarColor='#e74c3c'] - Color de la barra de scroll
     */
    constructor(options) {
      // Configuración con valores por defecto
      this.config = {
        maxHeight: 60,
        autoScroll: true,
        smoothScroll: true,
        scrollbarColor: '#e74c3c',
        ...options
      };
  
      // Validar selectores requeridos
      if (!this.config.containerSelector || !this.config.itemSelector) {
        throw new Error('containerSelector and itemSelector are required');
      }
  
      // Elementos del DOM
      this.container = document.querySelector(this.config.containerSelector);
      if (!this.container) {
        throw new Error(`Container not found with selector: ${this.config.containerSelector}`);
      }
  
      // Inicializar
      this.init();
    }
  
    /**
     * Inicializa el gestor de scroll
     */
    init() {
      // Aplicar estilos al contenedor
      this.applyContainerStyles();
  
      // Configurar MutationObserver para detectar cambios
      this.setupMutationObserver();
  
      // Aplicar eventos a elementos existentes
      this.applyEventsToExistingItems();
    }
  
    /**
     * Aplica los estilos necesarios al contenedor
     */
    applyContainerStyles() {
      Object.assign(this.container.style, {
        maxHeight: `${this.config.maxHeight}vh`,
        overflowY: 'auto',
        scrollBehavior: this.config.smoothScroll ? 'smooth' : 'auto'
      });
  
      // Crear estilo para la barra de scroll personalizada
      const style = document.createElement('style');
      style.textContent = `
        ${this.config.containerSelector}::-webkit-scrollbar {
          width: 6px;
        }
        ${this.config.containerSelector}::-webkit-scrollbar-thumb {
          background: ${this.config.scrollbarColor};
          border-radius: 4px;
        }
      `;
      document.head.appendChild(style);
    }
  
    /**
     * Configura el MutationObserver para detectar nuevos elementos
     */
    setupMutationObserver() {
      this.observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
          if (mutation.addedNodes.length) {
            this.applyEventsToNewItems(mutation.addedNodes);
            if (this.config.autoScroll) {
              this.scrollToLastItem();
            }
          }
        });
      });
  
      this.observer.observe(this.container, { childList: true });
    }
  
    /**
     * Aplica eventos a elementos existentes
     */
    applyEventsToExistingItems() {
      const items = this.container.querySelectorAll(this.config.itemSelector);
      items.forEach(item => this.setupItemEvents(item));
    }
  
    /**
     * Aplica eventos a nuevos elementos
     * @param {NodeList} nodes - Nodos añadidos
     */
    applyEventsToNewItems(nodes) {
      nodes.forEach(node => {
        if (node.nodeType === 1 && node.matches(this.config.itemSelector)) {
          this.setupItemEvents(node);
        } else if (node.nodeType === 1) {
          const children = node.querySelectorAll(this.config.itemSelector);
          children.forEach(child => this.setupItemEvents(child));
        }
      });
    }
  
    /**
     * Configura eventos para un elemento individual
     * @param {HTMLElement} item - Elemento individual
     */
    setupItemEvents(item) {
      // Puedes personalizar los eventos que necesites aquí
      // Ejemplo básico:
      item.addEventListener('click', () => {
        item.classList.toggle('active');
      });
    }
  
    /**
     * Hace scroll hasta el último elemento
     */
    scrollToLastItem() {
      const items = this.container.querySelectorAll(this.config.itemSelector);
      if (items.length > 0) {
        const lastItem = items[items.length - 1];
        lastItem.scrollIntoView({
          behavior: this.config.smoothScroll ? 'smooth' : 'auto',
          block: 'nearest'
        });
      }
    }
  
    /**
     * Hace scroll hasta un elemento específico
     * @param {HTMLElement} element - Elemento al que hacer scroll
     */
    scrollToElement(element) {
      if (element && this.container.contains(element)) {
        element.scrollIntoView({
          behavior: this.config.smoothScroll ? 'smooth' : 'auto',
          block: 'nearest'
        });
      }
    }
  
    /**
     * Destruye la instancia y limpia los observadores
     */
    destroy() {
      if (this.observer) {
        this.observer.disconnect();
      }
      // Eliminar estilos personalizados
      const styles = document.querySelectorAll(`style[data-scroll-manager="${this.config.containerSelector}"]`);
      styles.forEach(style => style.remove());
    }
  }
  
  // Exportar para módulos
  if (typeof module !== 'undefined' && module.exports) {
    module.exports = ScrollManager;
  } else {
    window.ScrollManager = ScrollManager;
  }