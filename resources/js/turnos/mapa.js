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

    farmacias.forEach(function (farmacia, index) {

        if (!farmacia.lat || !farmacia.lng) {
            return;
        }

        const lat = parseFloat(farmacia.lat);
        const lng = parseFloat(farmacia.lng);

        if (isNaN(lat) || isNaN(lng)) {
            return;
        }

        const numero = index + 1;

        const farmaciaIcon = L.divIcon({
            className: 'farmacia-marker',
            html: `
        <svg width="38" height="48" viewBox="0 0 38 48" xmlns="http://www.w3.org/2000/svg">
            <!-- Pin de ubicación -->
            <path d="M19 2 C9.6 2 2 9.6 2 19 C2 30.5 12.5 40.5 19 46 C25.5 40.5 36 30.5 36 19 C36 9.6 28.4 2 19 2Z" fill="#047857" stroke="white" stroke-width="3"/>
            <!-- Número -->
            <text x="19" y="24" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" font-weight="700" fill="white">
                ${numero}
            </text>
        </svg>
    `,
            iconSize: [38, 48],
            iconAnchor: [19, 48],
            popupAnchor: [0, -48]
        });

        const marker = L.marker([lat, lng], {
            icon: farmaciaIcon
        })
            .addTo(map)
            .bindPopup(`
    <div style="
        min-width: 220px;
        font-family: Arial, sans-serif;
        padding: 2px;
    ">

        <div style="
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        ">

            <div style="
                width: 30px;
                height: 30px;
                flex-shrink: 0;
                background: #d1fae5;
                color: #047857;
                border-radius: 9px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 13px;
            ">
                ${numero}
            </div>

            <strong style="
                color: #0f172a;
                font-size: 15px;
                line-height: 1.2;
            ">
                ${escapeHtml(farmacia.nombre)}
            </strong>

        </div>

        <div style="
            display: flex;
            align-items: flex-start;
            gap: 8px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.4;
            margin-bottom: 7px;
        ">
            <span style="color: #047857;">📍</span>
            <span>
                ${escapeHtml(farmacia.direccion ?? '')}
            </span>
        </div>

        ${farmacia.telefono
                    ? `
                    <div style="
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        color: #64748b;
                        font-size: 13px;
                    ">
                        <span style="color: #047857;">☎</span>
                        <span>${escapeHtml(farmacia.telefono)}</span>
                    </div>
                  `
                    : ''
                }

    </div>
`)

        markers.push(marker);
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