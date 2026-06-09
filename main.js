
const menuToggle = document.getElementById('menuToggle');
const headerNav = document.getElementById('headerNav');

if (menuToggle && headerNav) {
    menuToggle.addEventListener('click', () => {
        headerNav.classList.toggle('header__nav--open');
    });
}


document.addEventListener('DOMContentLoaded', () => {
    
    const tabClientes = document.getElementById('tab-clientes');
    const tabChoferes = document.getElementById('tab-choferes');
    
    const formClientes = document.getElementById('form-clientes');
    const formChoferes = document.getElementById('form-choferes');

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
            
            
            fetch('../enviar.php', {
                method: 'POST',
                body: new FormData(formClientes) 
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlertaNexus("¡Solicitud Enviada!", "¡Solicitud enviada con éxito! Nos comunicaremos a la brevedad.");
                    formClientes.reset(); 
                } else {
                    mostrarAlertaNexus("Error de Envío", "Hubo un problema en el servidor. Por favor, comunícate por canales directos.");
                }
            })
            .catch(error => {
                mostrarAlertaNexus("Error de Conexión", "No se pudo conectar con el servidor de correos.");
                console.error('Error:', error);
            });
        });
    }

    
    if (formChoferes) {
        formChoferes.addEventListener('submit', (event) => {
            event.preventDefault(); // Detiene la recarga de la página
            
            
            fetch('../enviar.php', {
                method: 'POST',
                body: new FormData(formChoferes)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlertaNexus("¡Postulación Recibida!", "¡Postulación recibida con éxito! Nuestro equipo de flota revisará tus datos.");
                    formChoferes.reset(); // Vacía los campos
                } else {
                    mostrarAlertaNexus("Error de Envío", "Hubo un problema al procesar tu postulación.");
                }
            })
            .catch(error => {
                mostrarAlertaNexus("Error de Conexión", "Error al procesar los datos de transporte.");
                console.error('Error:', error);
            });
        });
    }

});


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