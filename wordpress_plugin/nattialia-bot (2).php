<?php
/**
 * Plugin Name: Nattialia Bot Final (Re-apertura + R + Emails)
 * Description: Asistente completo: opción R, doble correo, posición elevada y botón flotante para reabrir.
 * Version: 12.3
 * Author: Nattia Team
 */

add_action('wp_footer', 'nattialia_render_reopen_bot');

function nattialia_render_reopen_bot() {
    ?>
    <style>
        /* --- 1. CONTENEDOR PRINCIPAL DEL BOT --- */
        #nattialia-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 250px;
            height: 400px;
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
            z-index: 999999; /* Muy por encima */
            font-family: 'Segoe UI', sans-serif;
            transition: all 0.3s ease;
            display: block; /* Visible por defecto */
        }

        /* --- 2. BOTÓN BURBUJA (LAUNCHER) --- */
        #nattialia-launcher {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #6c5ce7, #a55eea);
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(108, 92, 231, 0.5);
            z-index: 999998; /* Justo debajo del bot abierto */
            cursor: pointer;
            display: none; /* Oculto por defecto (porque el bot está abierto) */
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: white;
            transition: transform 0.2s;
            animation: float 3s ease-in-out infinite;
        }

        #nattialia-launcher:hover { transform: scale(1.1); }
        
        /* Animación suave de flotación para la burbuja */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        /* --- 3. ELEMENTOS INTERNOS DEL BOT --- */
        .btn-close {
            position: absolute; top: 8px; left: 8px;
            width: 24px; height: 24px;
            background: #ff4757; color: white;
            border: none; border-radius: 50%;
            font-weight: bold; cursor: pointer;
            z-index: 100; font-size: 12px;
        }

        .media-wrapper {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: #000;
        }
        .media-wrapper img, .media-wrapper video {
            width: 100%; height: 100%; object-fit: cover;
        }
        
        /* Clase utilitaria para ocultar cosas internas */
        .hidden { display: none !important; }

        .controls-wrapper {
            position: absolute; bottom: 0; width: 100%;
            padding: 12px; box-sizing: border-box;
            background: linear-gradient(to top, rgba(0,0,0,0.9) 25%, transparent);
            display: flex; flex-direction: column; align-items: center; gap: 8px;
            z-index: 50;
        }

        #btn-start {
            background: linear-gradient(45deg, #6c5ce7, #a55eea);
            color: white; border: none; padding: 10px 25px;
            border-radius: 20px; font-weight: bold; cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            font-size: 13px; animation: pulse 2s infinite;
        }

        .options-grid {
            display: flex; gap: 4px; width: 100%; flex-wrap: wrap; justify-content: center;
        }
        
        .btn-option {
            flex: 1; min-width: 70px;
            padding: 8px 4px;
            border: none; border-radius: 6px;
            font-size: 11px; font-weight: bold; color: white;
            cursor: pointer; text-align: center;
            transition: transform 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .color-dev { background: #0984e3; }
        .color-sup { background: #00b894; }
        .color-sub { background: #6c5ce7; }

        .btn-option:hover { transform: scale(1.05); filter: brightness(1.1); }

        #btn-email-final {
            width: 100%; padding: 10px;
            background: #d63031; color: white;
            border: none; border-radius: 8px;
            font-weight: bold; cursor: pointer;
            text-decoration: none; display: block; text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            font-size: 12px;
        }

        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }

        /* --- MÓVIL: ELEVACIÓN 85px (Aplica a Bot y Burbuja) --- */
        @media (max-width: 600px) {
            #nattialia-container { 
                width: 150px; 
                height: 260px; 
                bottom: 85px !important; 
                right: 10px; 
            }
            /* La burbuja también debe subir para no tapar cookies */
            #nattialia-launcher {
                bottom: 85px !important;
                right: 10px;
                width: 50px; height: 50px; font-size: 24px;
            }
            
            #btn-start { padding: 6px 14px; font-size: 11px; }
            .btn-option { font-size: 9px; padding: 5px 2px; min-width: 100%; margin-bottom: 2px;} 
            #btn-email-final { font-size: 10px; padding: 6px; }
            .btn-close { width: 20px; height: 20px; font-size: 10px; }
        }
    </style>

    <div id="nattialia-launcher" onclick="toggleBot(true)">
        💬
    </div>

    <div id="nattialia-container">
        <button class="btn-close" onclick="toggleBot(false)">✕</button>

        <div class="media-wrapper">
            <img id="img-idle" src="https://nattia.com/wp-content/uploads/2026/01/idle.png">
            <video id="vid-player" class="hidden" playsinline muted></video>
        </div>

        <div class="controls-wrapper">
            <button id="btn-start" onclick="startBot()">👋 HABLA CON NATTIALIA</button>
            
            <div id="main-options" class="options-grid hidden">
                <button class="btn-option color-dev" onclick="chooseMain('DEV')">💻 Desarrollo</button>
                <button class="btn-option color-sup" onclick="chooseMain('SUP')">🛠️ Soporte</button>
            </div>

            <div id="sub-options-dev" class="options-grid hidden">
                <button class="btn-option color-sub" onclick="chooseSub('Java', 'DEV')">Java</button>
                <button class="btn-option color-sub" onclick="chooseSub('.NET', 'DEV')">.NET</button>
                <button class="btn-option color-sub" onclick="chooseSub('R', 'DEV')">R</button>
                <button class="btn-option color-sub" onclick="chooseSub('Otro', 'DEV')">Otro</button>
            </div>

            <div id="sub-options-sup" class="options-grid hidden">
                <button class="btn-option color-sub" onclick="chooseSub('Presencial', 'SUP')">Presencial</button>
                <button class="btn-option color-sub" onclick="chooseSub('Remoto', 'SUP')">Remoto</button>
                <button class="btn-option color-sub" onclick="chooseSub('Híbrido', 'SUP')">Ambos</button>
            </div>

            <a id="btn-email-final" class="hidden" href="#" target="_blank">📅 Solicitar Reunión</a>
        </div>
    </div>

    <script>
        const URLS = {
            intro: 'https://nattia.com/wp-content/uploads/2026/01/intro.mp4',
            soporte_intro: 'https://nattia.com/wp-content/uploads/2026/01/rama_soporte.mp4',
            dev_intro: 'https://nattia.com/wp-content/uploads/2026/01/rama_desarrollo.mp4',
            fin_soporte: 'https://nattia.com/wp-content/uploads/2026/01/final_ventas.mp4',
            fin_dev: 'https://nattia.com/wp-content/uploads/2026/01/final_dev.mp4'
        };

        const EMAIL = "jmlh@nattia.com, info@nattia.com";

        const botContainer = document.getElementById('nattialia-container');
        const botLauncher = document.getElementById('nattialia-launcher');

        const imgIdle = document.getElementById('img-idle');
        const vidPlayer = document.getElementById('vid-player');
        const btnStart = document.getElementById('btn-start');
        
        const boxMain = document.getElementById('main-options');
        const boxSubDev = document.getElementById('sub-options-dev');
        const boxSubSup = document.getElementById('sub-options-sup');
        const btnEmail = document.getElementById('btn-email-final');
        
        // --- FUNCIÓN NUEVA: MINIMIZAR / MAXIMIZAR ---
        function toggleBot(show) {
            if(show) {
                // Abrir bot, ocultar burbuja
                botContainer.style.display = 'block';
                botLauncher.style.display = 'none';
            } else {
                // Cerrar bot, mostrar burbuja
                botContainer.style.display = 'none';
                botLauncher.style.display = 'flex'; // Flex para centrar el icono
            }
        }

        function playVideo(url, onEndCallback) {
            vidPlayer.src = url;
            vidPlayer.classList.remove('hidden');
            imgIdle.style.display = 'none';
            vidPlayer.style.display = 'block';
            vidPlayer.currentTime = 0;
            
            var p = vidPlayer.play();
            if (p !== undefined) {
                p.then(_ => { vidPlayer.muted = false; }).catch(e => { vidPlayer.muted = true; vidPlayer.play(); });
            }

            vidPlayer.onended = () => {
                if(onEndCallback) onEndCallback();
            };
        }

        // 1. Inicio
        function startBot() {
            btnStart.style.display = 'none';
            playVideo(URLS.intro, function() {
                vidPlayer.style.display = 'none';
                imgIdle.style.display = 'block';
                boxMain.classList.remove('hidden');
                boxMain.style.display = 'flex';
            });
        }

        // 2. Elegir Principal
        function chooseMain(type) {
            boxMain.style.display = 'none'; 
            if(type === 'DEV') {
                playVideo(URLS.dev_intro, function() {
                    vidPlayer.style.display = 'none';
                    imgIdle.style.display = 'block';
                    boxSubDev.classList.remove('hidden');
                    boxSubDev.style.display = 'flex';
                });
            } else {
                playVideo(URLS.soporte_intro, function() {
                    vidPlayer.style.display = 'none';
                    imgIdle.style.display = 'block';
                    boxSubSup.classList.remove('hidden');
                    boxSubSup.style.display = 'flex';
                });
            }
        }

        // 3. Elegir Detalle
        function chooseSub(detalle, categoria) {
            boxSubDev.style.display = 'none';
            boxSubSup.style.display = 'none';

            let videoFinal = (categoria === 'DEV') ? URLS.fin_dev : URLS.fin_soporte;
            let nombreServicio = (categoria === 'DEV') ? 'Desarrollo Web' : 'Soporte Informático';

            playVideo(videoFinal, function() {
                vidPlayer.style.display = 'none';
                imgIdle.style.display = 'block';
                
                let asunto = "Solicitud de reunión inicial - " + nombreServicio;
                let cuerpo = "Buenos días,\n\n" +
                             "Tras completar el sistema guiado de " + nombreServicio + " (Interés: " + detalle + "), nos gustaría concertar una reunión inicial para comentar con vuestro equipo las necesidades actuales de nuestra empresa.\n\n" +
                             "Datos de contacto:\n" +
                             "Nombre: \n" +
                             "Empresa: \n" +
                             "Email: \n" +
                             "Teléfono: \n\n" +
                             "Disponibilidad para la reunión:\n" +
                             "Fecha o fechas preferidas: \n" +
                             "Franja horaria: \n\n" +
                             "Quedamos a la espera de vuestra confirmación.\n\n" +
                             "Un saludo cordial.";

                btnEmail.href = `mailto:${EMAIL}?subject=${encodeURIComponent(asunto)}&body=${encodeURIComponent(cuerpo)}`;
                btnEmail.innerText = "📅 Solicitar Reunión";
                btnEmail.classList.remove('hidden');
            });
        }
    </script>
    <?php
}
?>