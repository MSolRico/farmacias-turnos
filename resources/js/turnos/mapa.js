document.addEventListener('DOMContentLoaded', function () {

    const mapElement = document.getElementById('map');

    if (!mapElement) {
        return;
    }

    const farmacias = JSON.parse(mapElement.dataset.farmacias);

    const centroSantaFe = [-31.6333, -60.7000];

    const map = L.map('map').setView(centroSantaFe, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);


    // Crear marcadores numerados
    const markers = [];

    function crearPopupFarmacia(farmacia, numero) {

        const distancia = farmacia.distancia_km !== undefined &&
            farmacia.distancia_km !== null
            ? `
            <span class="farmacia-popup-distancia">
                ${Number(farmacia.distancia_km).toFixed(2)} km
            </span>
        `
            : '';

        const telefono = farmacia.telefono
            ? `
            <div class="farmacia-popup-fila">

                <div class="farmacia-popup-icono">
                    <svg fill="#0a543f" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07
                            19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67 A2 2 0 0 1 4.11 2h3
                            a2 2 0 0 1 2 1.72 c.12.9.33 1.78.62 2.63 a2 2 0 0 1-.45 2.11L8 9.73
                            a16 16 0 0 0 6 6 l1.27-1.27 a2 2 0 0 1 2.11-.45 c.85.29 1.73.5 2.63.62
                            A2 2 0 0 1 22 16.92z"/>
                    </svg>
                </div>

                <span class="farmacia-popup-texto">
                    ${escapeHtml(farmacia.telefono)}
                </span>
            </div>
        `
            : '';

        return `
        <div class="farmacia-popup">

            <div class="farmacia-popup-header">

                <div class="farmacia-popup-identidad">

                    <div class="farmacia-popup-numero">
                        ${numero}
                    </div>

                    <strong class="farmacia-popup-nombre">
                        ${escapeHtml(farmacia.nombre)}
                    </strong>

                </div>

                ${distancia}

            </div>

            <div class="farmacia-popup-separador"></div>

            <div class="farmacia-popup-fila">

                <div class="farmacia-popup-icono">
                    <svg fill="#0a543f" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12,2C8.1,2,5,5.1,5,9c0,6,7,13,7,13s7-7.1,7-13C19,5.1,15.9,2,12,2z M12,12.5c-1.9,0-3.5-1.6-3.5-3.5s1.6-3.5,3.5-3.5s3.5,1.6,3.5,3.5S13.9,12.5,12,12.5z"></path>
                    </svg>
                </div>

                <span class="farmacia-popup-texto">
                    ${escapeHtml(farmacia.direccion ?? '')}
                </span>

            </div>

            ${telefono}

        </div>
    `;
    }

    function crearIconoFarmacia(numero) {

        return L.divIcon({
            className: 'farmacia-marker',

            html: `
                <svg
                    width="38"
                    height="48"
                    viewBox="0 0 38 48"
                    xmlns="http://www.w3.org/2000/svg">

                    <path
                        d="M19 2 C9.6 2 2 9.6 2 19
                           C2 30.5 12.5 40.5 19 46
                           C25.5 40.5 36 30.5 36 19
                           C36 9.6 28.4 2 19 2Z"
                        fill="#047857"
                        stroke="white"
                        stroke-width="3"
                    />

                    <text
                        x="19"
                        y="24"
                        text-anchor="middle"
                        font-family="Arial, sans-serif"
                        font-size="14"
                        font-weight="700"
                        fill="white">
                        ${numero}
                    </text>

                </svg>
            `,

            iconSize: [38, 48],
            iconAnchor: [19, 48],
            popupAnchor: [0, -48]
        });
    }

    function crearMarcadorFarmacia(farmacia, numero) {

        const lat = parseFloat(farmacia.lat);
        const lng = parseFloat(farmacia.lng);

        if (isNaN(lat) || isNaN(lng)) {
            return null;
        }

        return L.marker([lat, lng], {
            icon: crearIconoFarmacia(numero)
        })
            .addTo(map)
            .bindPopup(
                crearPopupFarmacia(farmacia, numero)
            );
    }

    function mostrarUbicacionUsuario(latitud, longitud) {

        const userIcon = L.divIcon({
            className: 'user-location-marker',

            html: `
            <div style="
                width: 18px;
                height: 18px;
                background: #2563eb;
                border: 3px solid white;
                border-radius: 50%;
                box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.2);
            "></div>
        `,

            iconSize: [18, 18],
            iconAnchor: [9, 9]
        });

        L.marker([latitud, longitud], {
            icon: userIcon
        })
            .addTo(map)
            .bindPopup('Tu ubicación');
    }

    function actualizarListadoFarmacias(data) {

        const tituloListado = document.getElementById('titulo-listado');
        const subtituloListado = document.getElementById('subtitulo-listado');
        const cantidadFarmacias = document.getElementById('cantidad-farmacias');
        const listaFarmacias = document.getElementById('lista-farmacias');

        if (tituloListado) {
            tituloListado.textContent = 'Farmacias cercanas';
        }

        if (subtituloListado) {
            subtituloListado.textContent = 'Ordenadas por distancia';
        }

        if (cantidadFarmacias) {
            cantidadFarmacias.textContent = data.farmacias.length;
        }

        if (listaFarmacias) {
            listaFarmacias.innerHTML = data.html;
        }
    }

    farmacias.forEach(function (farmacia, index) {

        const numero = index + 1;

        const marker = crearMarcadorFarmacia(
            farmacia,
            numero
        );

        if (marker) {
            markers.push(marker);
        }
    });


    // Ajustar el mapa para mostrar todas las farmacias
    if (markers.length > 0) {

        const group = L.featureGroup(markers);

        map.fitBounds(group.getBounds(), {
            padding: [30, 30]
        });

    }


    // Función global para los botones "Ver en el mapa"
    window.centrarMapa = function (lat, lng) {

        lat = parseFloat(lat);
        lng = parseFloat(lng);

        if (isNaN(lat) || isNaN(lng)) {
            return;
        }

        map.setView([lat, lng], 17);
    };

    // Farmacias cercanas
    const botonCercanas = document.getElementById('btn-farmacias-cercanas');
    const textoCercanas = document.getElementById('texto-farmacias-cercanas');

    if (botonCercanas) {

        botonCercanas.addEventListener('click', function () {

            // Verificar que el navegador soporte geolocalización
            if (!navigator.geolocation) {
                alert('Tu navegador no permite obtener tu ubicación.');
                return;
            }

            // Evitar múltiples clics mientras se obtiene la ubicación
            botonCercanas.disabled = true;

            if (textoCercanas) {
                textoCercanas.textContent = 'Buscando farmacias cercanas...';
            }

            navigator.geolocation.getCurrentPosition(

                function (position) {

                    const latitud = position.coords.latitude;
                    const longitud = position.coords.longitude;

                    // Mostrar ubicación del usuario en el mapa
                    mostrarUbicacionUsuario(latitud, longitud);

                    // Centrar el mapa en la ubicación del usuario
                    map.setView([latitud, longitud], 14);

                    // Enviar ubicación al backend
                    fetch('/farmacias/cercanas', {

                        method: 'POST',

                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },

                        body: JSON.stringify({
                            latitud: latitud,
                            longitud: longitud
                        })
                    })
                        .then(async response => {

                            const texto = await response.text();

                            if (!response.ok) {
                                throw new Error('Error al obtener las farmacias cercanas.');
                            }

                            return JSON.parse(texto);
                        })
                        .then(data => {

                            actualizarListadoFarmacias(data);

                            // Eliminar los marcadores anteriores
                            markers.forEach(function (marker) {
                                map.removeLayer(marker);
                            });

                            // Vaciar el array
                            markers.length = 0;

                            // Crear los nuevos marcadores
                            data.farmacias.forEach(function (farmacia, index) {

                                const numero = index + 1;

                                const marker = crearMarcadorFarmacia(
                                    farmacia,
                                    numero
                                );

                                if (marker) {
                                    markers.push(marker);
                                }
                            });

                        })
                        .catch(error => { console.error('❌ Error:', error); })
                        .finally(() => {

                            botonCercanas.disabled = false;

                            if (textoCercanas) {
                                textoCercanas.textContent = 'Buscar farmacias cerca de mí';
                            }

                        });
                },

                function (error) {

                    console.error('Error de geolocalización:', error);
                    botonCercanas.disabled = false;

                    if (textoCercanas) {
                        textoCercanas.textContent = 'Buscar farmacias cerca de mí';
                    }

                    if (error.code === error.PERMISSION_DENIED) {
                        alert('Necesitamos permiso para acceder a tu ubicación.');
                    } else {
                        alert('No pudimos obtener tu ubicación.');
                    }
                }
            );
        });
    }

    // Evita insertar HTML proveniente de la base de datos
    function escapeHtml(value) {

        if (value === null || value === undefined) {
            return '';
        }

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

});