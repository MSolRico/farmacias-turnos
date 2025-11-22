@section('map_script')

<script>
// Inicializar mapa
var map = L.map('mapa').setView([-31.6333, -60.7000], 13);

// Capa base (OpenStreetMap)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 20,
    attribution: '© OpenStreetMap'
}).addTo(map);

// --- Marcadores de farmacias ---
var farmacias = @json($farmacias);
var markers = [];

farmacias.forEach(function(f) {
    if (f.lat && f.lng) {

        // Popup de cada farmacia
        let popup = `
            <div style="font-size:14px; line-height:1.4;">
                <b>${f.nombre}</b><br>
                ${f.direccion}<br>
                <b>Tel:</b> ${f.telefono}
                ${f.reportada_cerrada ? '<br><span style="color:#e63946;font-weight:bold;">⚠ Reportada cerrada</span>' : ''}
            </div>
        `;

        let marker = L.marker([f.lat, f.lng]).addTo(map).bindPopup(popup);
        markers.push([f.lat, f.lng]);
    }
});

// Ajustar zoom automáticamente
if (markers.length > 1) {
    map.fitBounds(markers, { padding: [50, 50] });
} else if (markers.length === 1) {
    map.setView(markers[0], 15);
}

// --- GELOCALIZACIÓN DEL USUARIO ---
console.log("📡 Intentando obtener geolocalización...");

map.locate({ setView: false });

map.on('locationfound', function(e) {
    console.log("📍 Ubicación encontrada:", e.latlng);

    L.circleMarker(e.latlng, {
        radius: 8,
        fillColor: "#007bff",
        color: "#fff",
        weight: 2,
        opacity: 1,
        fillOpacity: 0.8
    }).addTo(map)
    .bindPopup("📍 Estás aquí")
    .openPopup();
});

map.on("locationerror", function(err) {
    console.log("❌ ERROR de geolocalización:", err.message);

    if (err.message.includes("secure origins")) {
        console.log("⚠ Chrome exige HTTPS o localhost para obtener ubicación.");
        console.log("👉 Usá: http://localhost:8000");
    }
});

</script>

@endsection
