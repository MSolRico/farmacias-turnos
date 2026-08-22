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


    // Crear marcadores
    const markers = [];

    farmacias.forEach(function (farmacia) {

        if (!farmacia.lat || !farmacia.lng) {
            return;
        }

        const lat = parseFloat(farmacia.lat);
        const lng = parseFloat(farmacia.lng);

        if (isNaN(lat) || isNaN(lng)) {
            return;
        }

        const marker = L.marker([lat, lng])
            .addTo(map)
            .bindPopup(`
                <div style="min-width: 190px;">
                    <strong>${escapeHtml(farmacia.nombre)}</strong>
                    <br>
                    <span>${escapeHtml(farmacia.direccion ?? '')}</span>
                    <br>
                    <span>${escapeHtml(farmacia.telefono ?? '')}</span>
                </div>
            `);

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