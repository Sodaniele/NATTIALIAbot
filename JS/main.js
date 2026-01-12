// --- REFERENCIAS AL DOM ---
const videoIdle = document.getElementById('video-idle');
const videoAction = document.getElementById('video-action');
const inputField = document.getElementById('user-input');
const btnEnviar = document.getElementById('send-btn');
const btnStart = document.getElementById('btn-start');
const chatInterface = document.getElementById('chat-interface');

// --- ESTADO DE LA CONVERSACIÓN ---
// Controla en qué punto del guion estamos
let estadoActual = 'ESPERANDO'; 

// --- FUNCIONES DE CONTROL DE VIDEO ---

function reproducirVideo(nombreArchivo) {
    // Construimos la ruta completa
    videoAction.src = `assets/${nombreArchivo}`;
    
    // Cuando el video carga, lo mostramos y le damos play
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
    // Ocultamos Action y pausamos para ahorrar recursos
    videoAction.style.display = 'none';
    videoAction.pause();
    
    // Mostramos Idle (que siempre está corriendo en bucle)
    videoIdle.style.opacity = 1;
    videoIdle.play();
}

// EVENTO CLAVE: Cuando termina el video de hablar, vuelve a reposo
videoAction.addEventListener('ended', () => {
    volverAlReposo();
});

// --- LÓGICA DEL CHATBOT (MAQUINA DE ESTADOS) ---

function iniciarConversacion() {
    // 1. Ocultamos botón inicio, mostramos chat
    btnStart.style.display = 'none';
    chatInterface.style.display = 'block';

    // 2. Reproducimos el saludo inicial
    // Video: "Hola soy Nattialia, ¿Soporte o Desarrollo?"
    reproducirVideo('intro.mp4'); 
    
    // 3. Actualizamos el estado
    estadoActual = 'SELECCION_SERVICIO';
}

function procesarInput() {
    let texto = inputField.value.trim().toLowerCase();
    if (texto === "") return;

    console.log(`Estado: ${estadoActual} | Usuario dice: ${texto}`);

    switch(estadoActual) {
        
        case 'SELECCION_SERVICIO':
            // Esperamos: "soporte" o "desarrollo"
            if (texto.includes('soporte')) {
                // Video: "¿Presencial, remoto o ambos?"
                reproducirVideo('rama_soporte.mp4'); 
                estadoActual = 'TIPO_SOPORTE';
            } 
            else if (texto.includes('desarrollo') || texto.includes('software')) {
                // Video: "¿Qué lenguaje? ¿Java, .net...?"
                reproducirVideo('rama_desarrollo.mp4');
                estadoActual = 'LENGUAJE';
            } 
            else {
                alert("Por favor, escribe 'Soporte' o 'Desarrollo'.");
            }
            break;

        case 'TIPO_SOPORTE':
            // Esperamos: "presencial", "remoto", "ambos"
            if (texto.includes('presencial') || texto.includes('remoto') || texto.includes('ambos')) {
                // Video: "Perfecto, te paso con ventas..."
                reproducirVideo('final_ventas.mp4');
                estadoActual = 'FIN';
                setTimeout(() => alert("📧 Contacto Ventas: soporte@nattia.com"), 4000);
            } else {
                alert("¿Prefieres presencial o remoto?");
            }
            break;

        case 'LENGUAJE':
            // Esperamos: cualquier lenguaje
            // Video: "Perfecto, te paso con desarrollo..."
            reproducirVideo('final_dev.mp4');
            estadoActual = 'FIN';
            setTimeout(() => alert("📧 Contacto Desarrollo: dev@nattia.com"), 4000);
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