
const menuToggle = document.getElementById('menuToggle');
const headerNav = document.getElementById('headerNav');

menuToggle.addEventListener('click', () => {
  headerNav.classList.toggle('header__nav--open');
});



document.addEventListener('DOMContentLoaded', () => {
    
   
    const tabClientes = document.getElementById('tab-clientes');
    const tabChoferes = document.getElementById('tab-choferes');
    
 
    const formClientes = document.getElementById('form-clientes');
    const formChoferes = document.getElementById('form-choferes');

    /**
     
     * @param {string} tipo 
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
            event.preventDefault(); // Evita que la página se recargue
            
            const selectBulto = document.getElementById('select-bulto');
            if (selectBulto) {
                const bulto = selectBulto.value;
                
                if (bulto.includes("Muy grande")) {
                    alert("Atención: Logística Nexus no realiza traslados de bultos de tamaño Muy Grande (Línea blanca/Cocinas). Tu consulta no cumple con los requisitos operativos del servicio.");
                    return;
                }
            }
            
           
            alert("¡Solicitud enviada con éxito! Nos comunicaremos a la brevedad.");
            formClientes.reset(); // Limpia los campos
        });
    }

    
    if (formChoferes) {
        formChoferes.addEventListener('submit', (event) => {
            event.preventDefault(); // 
            
           
            alert("¡Postulación recibida con éxito! Nuestro equipo de flota revisará tus datos.");
            formChoferes.reset(); // Limpia los campos
        });
    }

});