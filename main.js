// Contenido de main.js
const menuToggle = document.getElementById('menuToggle');
const headerNav = document.getElementById('headerNav');

menuToggle.addEventListener('click', () => {
  headerNav.classList.toggle('header__nav--open');
});

/* ==========================================================================
   Controlador del Formulario de Contacto - Logística Nexus
   ========================================================================== */

document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Captura de botones de pestañas (Tabs)
    const tabClientes = document.getElementById('tab-clientes');
    const tabChoferes = document.getElementById('tab-choferes');
    
    // 2. Captura de los bloques de formulario
    const formClientes = document.getElementById('form-clientes');
    const formChoferes = document.getElementById('form-choferes');

    /**
     * Alterna de forma limpia las clases activas del diseño
     * @param {string} tipo - 'clientes' o 'choferes'
     */
    function switchNexusForm(tipo) {
        // Removemos el estado activo de ambos formularios y botones
        document.querySelectorAll('.nexus-form').forEach(form => form.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        
        // Aplicamos la clase active según la pestaña seleccionada
        if (tipo === 'clientes') {
            formClientes.classList.add('active');
            tabClientes.classList.add('active');
        } else if (tipo === 'choferes') {
            formChoferes.classList.add('active');
            tabChoferes.classList.add('active');
        }
    }

    // 3. Asignación de eventos de clic a las pestañas
    if (tabClientes && tabChoferes) {
        tabClientes.addEventListener('click', () => switchNexusForm('clientes'));
        tabChoferes.addEventListener('click', () => switchNexusForm('choferes'));
    }

    // 4. Procesar envío del Formulario de Clientes (Empresas)
    if (formClientes) {
        formClientes.addEventListener('submit', (event) => {
            event.preventDefault(); // Evita que la página se recargue
            
            const selectBulto = document.getElementById('select-bulto');
            if (selectBulto) {
                const bulto = selectBulto.value;
                // Validación de bulto "Muy Grande" según requerimientos del diseño
                if (bulto.includes("Muy grande")) {
                    alert("Atención: Logística Nexus no realiza traslados de bultos de tamaño Muy Grande (Línea blanca/Cocinas). Tu consulta no cumple con los requisitos operativos del servicio.");
                    return;
                }
            }
            
            // Aquí podrás insertar tu lógica de EmailJS en el futuro
            alert("¡Solicitud enviada con éxito! Nos comunicaremos a la brevedad.");
            formClientes.reset(); // Limpia los campos
        });
    }

    // 5. Procesar envío del Formulario de Choferes
    if (formChoferes) {
        formChoferes.addEventListener('submit', (event) => {
            event.preventDefault(); // Evita que la página se recargue
            
            // Aquí podrás insertar tu lógica de EmailJS en el futuro
            alert("¡Postulación recibida con éxito! Nuestro equipo de flota revisará tus datos.");
            formChoferes.reset(); // Limpia los campos
        });
    }

});