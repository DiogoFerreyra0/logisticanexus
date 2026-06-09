
const menuToggle = document.getElementById('menuToggle');
const headerNav = document.getElementById('headerNav');

if (menuToggle && headerNav) {
    menuToggle.addEventListener('click', () => {
        headerNav.classList.toggle('header__nav--open');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    
    // Captura de pestañas (Tabs)
    const tabClientes = document.getElementById('tab-clientes');
    const tabChoferes = document.getElementById('tab-choferes');
    
    // Captura de bloques de formulario
    const formClientes = document.getElementById('form-clientes');
    const formChoferes = document.getElementById('form-choferes');

    /**
      
     @param {string} tipo 
     */
    function switchNexusForm(tipo) {
        document.querySelectorAll('.nexus-form').forEach(form => form.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        
        if (tipo === 'clientes') {
            formClientes.classList.add('active');
            tabClientes.classList.add('active');
        } else if (tipo === 'choferes') {
            formChoferes.classList.add('active');
            tabChoferes.classList.add('active');
        }
    }

    
    if (tabClientes && tabChoferes) {
        tabClientes.addEventListener('click', () => switchNexusForm('clientes'));
        tabChoferes.addEventListener('click', () => switchNexusForm('choferes'));
    }

    
    if (formClientes) {
        formClientes.addEventListener('submit', (event) => {
            event.preventDefault(); 
            
            const selectBulto = document.getElementById('select-bulto');
            if (selectBulto) {
                const bulto = selectBulto.value;
                
                
                if (bulto.includes("Muy grande")) {
                    mostrarAlertaNexus(
                        "Límite Operativo", 
                        "Atención: Logística Nexus no realiza traslados de bultos de tamaño Muy Grande (Línea blanca/Cocinas). Tu consulta no cumple con los requisitos operativos del servicio."
                    );
                    return; 
                }
            }
            
            
            mostrarAlertaNexus(
                "¡Solicitud Enviada!", 
                "¡Solicitud enviada con éxito! Nos comunicaremos a la brevedad."
            );
            formClientes.reset(); 
        });
    }

    
    if (formChoferes) {
        formChoferes.addEventListener('submit', (event) => {
            event.preventDefault(); 
            
            
            mostrarAlertaNexus(
                "¡Postulación Recibida!", 
                "¡Postulación recibida con éxito! Nuestro equipo de flota revisará tus datos."
            );
            formChoferes.reset(); 
        });
    }

});

/**
 * 
 * @param {string} titulo 
 * @param {string} mensaje 
 */
function mostrarAlertaNexus(titulo, mensaje) {
    const modal = document.getElementById('nexusModal');
    const modalTitle = document.getElementById('nexusModalTitle');
    const modalMessage = document.getElementById('nexusModalMessage');
    const modalCloseBtn = document.getElementById('nexusModalClose');

    if (modal && modalTitle && modalMessage) {
        
        modalTitle.textContent = titulo;
        modalMessage.textContent = mensaje;
        
        
        modal.classList.add('active');

        
        modalCloseBtn.onclick = function() {
            modal.classList.remove('active');
        };

        
        modal.onclick = function(event) {
            if (event.target === modal) {
                modal.classList.remove('active');
            }
        };
    }
}