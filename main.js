// Funcionalidad Menú Móvil
const menuToggle = document.getElementById('menuToggle');
const headerNav = document.getElementById('headerNav');

if (menuToggle && headerNav) {
    menuToggle.addEventListener('click', () => {
        headerNav.classList.toggle('header__nav--open');
    });
}

// Inicialización general al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    
    // Configuración para que funcione tanto en root como en la subcarpeta /pages/
    const isRoot = window.location.pathname.endsWith('index.html') || window.location.pathname === '/' || window.location.pathname === '';
    const scriptPath = isRoot ? 'procesar_whatsapp.php' : '../procesar_whatsapp.php';

    // 1. Lógica para Pestañas (Sección Contacto)
    const tabs = document.querySelectorAll('.tab-btn');
    const forms = document.querySelectorAll('.nexus-form');

    if (tabs.length > 0 && forms.length > 0) {
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Limpiar activos
                tabs.forEach(t => t.classList.remove('active'));
                forms.forEach(f => f.classList.remove('active'));

                // Activar seleccionado
                tab.classList.add('active');
                const targetFormId = tab.getAttribute('data-target');
                const formTarget = document.getElementById(targetFormId);
                
                if(formTarget) {
                    formTarget.classList.add('active');
                }
            });
        });
    }

    // 2. Interceptador de Envíos para todos los formularios
    forms.forEach(form => {
        form.addEventListener('submit', (event) => {
            event.preventDefault(); 
            
            const formData = new FormData(form);
            
            // Validaciones específicas según el formulario activo
            const formType = formData.get('form_type');
            
            if (formType === 'empresa') {
                const checkedZonas = document.querySelectorAll('#form-empresa input[name="zonas"]:checked');
                const checkedVehiculos = document.querySelectorAll('#form-empresa input[name="vehiculo"]:checked');
                
                if(checkedZonas.length === 0) {
                    mostrarAlertaNexus("Datos Incompletos", "Por favor, selecciona al menos una zona operativa.");
                    return;
                }
                if(checkedVehiculos.length === 0) {
                    mostrarAlertaNexus("Datos Incompletos", "Por favor, selecciona al menos un tipo de vehículo requerido.");
                    return;
                }
            } else if (formType === 'chofer') {
                const checkedReq = document.querySelectorAll('#form-chofer input[name="req"]:checked');
                // Puedes agregar validaciones extras para choferes aquí si es necesario
            } else if (formType === 'alianzas') {
                const checkedServicios = document.querySelectorAll('#form-alianzas input[name="alianza_servicios"]:checked');
                const checkedVehiculosAli = document.querySelectorAll('#form-alianzas input[name="alianza_vehiculo"]:checked');
                
                if(checkedServicios.length === 0) {
                    mostrarAlertaNexus("Datos Incompletos", "Por favor, indica qué servicios podés cubrir en tu alianza.");
                    return;
                }
                if(checkedVehiculosAli.length === 0) {
                     mostrarAlertaNexus("Datos Incompletos", "Por favor, especifica qué vehículos tenés disponibles.");
                     return;
                }
            }

            // Construcción del mensaje para WhatsApp basado en el FormData
            let mensajeWhatsApp = "Hola Rupack, me contacto desde la web.%0A%0A";

            if (formType === 'empresa') {
                mensajeWhatsApp += `*SOLICITUD EMPRESARIAL*%0A`;
                mensajeWhatsApp += `Empresa: ${formData.get('empresa_nombre')}%0A`;
                if(formData.get('empresa_cuit')) mensajeWhatsApp += `CUIT: ${formData.get('empresa_cuit')}%0A`;
                mensajeWhatsApp += `Contacto: ${formData.get('empresa_contacto')} (${formData.get('empresa_tel')})%0A`;
                mensajeWhatsApp += `Email: ${formData.get('empresa_email')}%0A`;
                
                mensajeWhatsApp += `%0A*Vehículos requeridos:*%0A`;
                document.querySelectorAll('#form-empresa input[name="vehiculo"]:checked').forEach(c => mensajeWhatsApp += `- ${c.value}%0A`);
                
                mensajeWhatsApp += `%0A*Zonas requeridas:*%0A`;
                document.querySelectorAll('#form-empresa input[name="zonas"]:checked').forEach(c => mensajeWhatsApp += `- ${c.value}%0A`);
                
                mensajeWhatsApp += `%0AModalidad: ${formData.get('modalidad')}%0A`;
                if(formData.get('detalles')) mensajeWhatsApp += `Detalles: ${formData.get('detalles')}`;

            } else if (formType === 'chofer') {
                mensajeWhatsApp += `*POSTULACIÓN DE CHOFER*%0A`;
                mensajeWhatsApp += `Nombre: ${formData.get('chofer_nombre')}%0A`;
                mensajeWhatsApp += `DNI/CUIT: ${formData.get('chofer_dni')}%0A`;
                mensajeWhatsApp += `Teléfono: ${formData.get('chofer_tel')}%0A`;
                mensajeWhatsApp += `Zona: ${formData.get('chofer_zona')}%0A`;
                
                mensajeWhatsApp += `%0A*Vehículo:*%0A`;
                mensajeWhatsApp += `Unidad: ${formData.get('tipo_unidad')}%0A`;
                mensajeWhatsApp += `Marca/Modelo: ${formData.get('marca_modelo')} (${formData.get('anio_vehiculo')})%0A`;
                
                mensajeWhatsApp += `%0A*Requisitos cumplidos:*%0A`;
                document.querySelectorAll('#form-chofer input[name="req"]:checked').forEach(c => mensajeWhatsApp += `- ${c.value}%0A`);
                
                mensajeWhatsApp += `%0ADisponibilidad: ${formData.get('disponibilidad')}`;

            } else if (formType === 'alianzas') {
                mensajeWhatsApp += `*PROPUESTA DE ALIANZA*%0A`;
                mensajeWhatsApp += `Empresa: ${formData.get('alianza_nombre')}%0A`;
                if(formData.get('alianza_cuit')) mensajeWhatsApp += `CUIT: ${formData.get('alianza_cuit')}%0A`;
                mensajeWhatsApp += `Contacto: ${formData.get('alianza_contacto')} (${formData.get('alianza_cargo')})%0A`;
                mensajeWhatsApp += `Tel/Email: ${formData.get('alianza_tel')} | ${formData.get('alianza_email')}%0A`;
                
                mensajeWhatsApp += `%0A*Perfil:*%0A`;
                mensajeWhatsApp += `Tipo: ${formData.get('tipo_empresa')}%0A`;
                mensajeWhatsApp += `Zonas: ${formData.get('alianza_zonas')}%0A`;
                mensajeWhatsApp += `Flota: ${formData.get('cant_vehiculos')} vehículos%0A`;
                
                mensajeWhatsApp += `%0A*Vehículos:*%0A`;
                document.querySelectorAll('#form-alianzas input[name="alianza_vehiculo"]:checked').forEach(c => mensajeWhatsApp += `- ${c.value}%0A`);
                
                mensajeWhatsApp += `%0A*Servicios:*%0A`;
                document.querySelectorAll('#form-alianzas input[name="alianza_servicios"]:checked').forEach(c => mensajeWhatsApp += `- ${c.value}%0A`);
                
                mensajeWhatsApp += `%0A*Propuesta:*%0A`;
                mensajeWhatsApp += `Interés: ${formData.get('tipo_alianza')}%0A`;
                mensajeWhatsApp += `Experiencia Flex: ${formData.get('exp_flex')}%0A`;
                if(formData.get('propuesta_desc')) mensajeWhatsApp += `Detalles: ${formData.get('propuesta_desc')}`;
            }

            // 1. Enviar datos a un backend por correo de respaldo (Opcional - requiere PHP modificado)
            /* 
            fetch(scriptPath, { method: 'POST', body: formData })
                .then(r => r.json())
                .catch(e => console.error("Respaldo backend no disponible:", e));
            */

            // 2. Redirección inmediata a WhatsApp
            const numeroWhatsApp = "5491154597235"; 
            const url = `https://wa.me/${numeroWhatsApp}?text=${mensajeWhatsApp}`;
            
            // Abrir WhatsApp en nueva pestaña
            window.open(url, '_blank');
            
            // Mostrar modal de éxito y limpiar formulario
            mostrarAlertaNexus("¡Solicitud Formateada!", "Se abrió WhatsApp con tu solicitud lista para enviar a nuestro equipo.");
            form.reset();
        });
    });

});

// Sistema de Modal / Alertas
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