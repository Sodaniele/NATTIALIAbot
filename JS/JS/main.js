// --- REFERENCIAS AL DOM ---
const videoIdle = document.getElementById('video-idle');
const videoAction = document.getElementById('video-action');
const inputField = document.getElementById('user-input');
const btnEnviar = document.getElementById('send-btn');
const btnStart = document.getElementById('btn-start');
const chatInterface = document.getElementById('chat-interface');

// --- ESTADO DE LA CONVERSACIÓN ---
// Esto controla en qué punto del guion estamos
let estadoActual = 'ESPERANDO'; 

// --- FUNCIONES DE CONTROL DE VIDEO ---

function reproducirVideo(nombreArchivo) {
    console.log("Cargando video: " + nombreArchivo);
    
// IMPORTANTE ./ASSETS/ en mayúsculas!!
    videoAction.src = "./ASSETS/" + nombreArchivo;
    

// Cuando el video carga, lo muestro y le damos play
    videoAction.onloadeddata = () => {
        videoIdle.style.opacity = 0;       // Ocultamos Idle
        videoAction.classList.remove('hidden'); // Mostramos Action
        videoAction.style.display = 'block';
        
        videoAction.currentTime = 0;
        videoAction.play();
        videoAction.muted = false; // Audio ON
    };
}

function volverAlReposo() {
    // Oculto Action y pausamos para ahorrar recursos
    videoAction.style.display = 'none';
    videoAction.pause();
    
    // Muestro Idle (que siempre está corriendo en bucle)
    videoIdle.style.opacity = 1;
    videoIdle.play();
}

// Cuando termina el video de hablar, vuelve a reposo
videoAction.addEventListener('ended', () => {
    volverAlReposo();
});

// --- LÓGICA DEL CHATBOT (MAQUINA DE ESTADOS) ---

function iniciarConversacion() {
    // 1. Ocultamos botón inicio, mostramos chat
    btnStart.style.display = 'none';
    chatInterface.style.display = 'block';

    // 2. Reproduzco el saludo inicial
    // Video: "Hola soy Nattialia, Soporte o Desarrollo?"
    reproducirVideo('intro.mp4'); 
    
    // 3. Actualizo el estado
    estadoActual = 'SELECCION_SERVICIO';
}

function procesarInput() {
    let texto = inputField.value.trim().toLowerCase();
    if (texto === "") return;

    console.log(`Estado: ${estadoActual} | Usuario dice: ${texto}`);

    switch(estadoActual) {
        
        case 'SELECCION_SERVICIO':
            // 1. Opción SOPORTE
            if (texto.includes('soporte')) {
                reproducirVideo('rama_soporte.mp4'); 
                estadoActual = 'TIPO_SOPORTE';
            } 
            // 2. Opción DESARROLLO
            else if (texto.includes('desarrollo') || texto.includes('software')) {
                reproducirVideo('rama_desarrollo.mp4');
                estadoActual = 'LENGUAJE';
            } 
            // 3. NUEVA OPCIÓN: OTRA NECESIDAD (Sin video, directo al contacto)
            else if (texto.includes('otra') || texto.includes('otro') || texto.includes('necesidad')) {
                // Como no hay video, mostramos la tarjeta directamente
                mostrarContacto("📧 Para otras consultas: info@nattia.com");
                estadoActual = 'FIN';
            }
            // 4. Si no entiende nada
            else {
                // Actualizamos el mensaje de error para que sepa qué puede decir
                mostrarError("⚠️ Escribe: Soporte, Desarrollo u Otra");
            }
            break;

        case 'TIPO_SOPORTE':
            // Esperamos: presencial, remoto, ambos
            if (texto.includes('presencial') || texto.includes('remoto') || texto.includes('ambos')) {
                // Video: Perfecto, te paso con ventas...
                reproducirVideo('final_ventas.mp4');
                estadoActual = 'FIN';
                setTimeout(() => {
    mostrarContacto("📧 Escribe a: soporte@nattia.com");
}, 4000); // Espera 4 segundos (lo que dura el video) y muestra la tarjeta
            } else {
                alert("¿Prefieres presencial o remoto?");
            }
            break;

        case 'LENGUAJE':
            // Esperamos: cualquier lenguaje
            // Video: Perfecto, te paso con desarrollo...
            reproducirVideo('final_dev.mp4');
            estadoActual = 'FIN';
            setTimeout(() => {
    mostrarContacto("📧 Escribe a: dev@nattia.com");
}, 4000);
            break;

        case 'FIN':
            alert("La conversación ha terminado. Recarga para empezar de nuevo.");
            break;
    }

    // Limpiamos el input después de enviar
    inputField.value = '';
}

// --- EVENTOS DE INTERACCIÓN ---
btnEnviar.addEventListener('click', procesarInput);

// Permitir enviar pulsando ENTER
inputField.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') procesarInput();
});

// Función para mostrar la tarjeta final bonita
function mostrarContacto(mensaje) {
    const card = document.getElementById('contact-card');
    const texto = document.getElementById('contact-text');
    const chat = document.getElementById('chat-interface');
    
    // Ocultamos el chat para que no moleste
    if(chat) chat.style.display = 'none';
    
    // Ponemos el texto y mostramos la tarjeta
    texto.innerText = mensaje;
    card.classList.remove('hidden');
    card.style.display = 'block';
}
// Función para mostrar errores temporales
function mostrarError(texto) {
    const errorDiv = document.getElementById('error-message');
    
    if (errorDiv) {
        errorDiv.innerText = texto; // Ponemos el texto que queramos
        errorDiv.style.display = 'block'; // Lo mostramos
        errorDiv.classList.remove('hidden');

        // Hacemos que desaparezca solo a los 3 segundos
        setTimeout(() => {
            errorDiv.style.display = 'none';
        }, 3000);
    }
}