@extends('layouts.app')

@section('title', 'Triangulasi BTS')

@section('content')
<div class="page-header">
    <h2>🗺️ Triangulasi BTS</h2>
    <p>Tentukan lokasi target berdasarkan 3 BTS Tower</p>
</div>

<!-- Info Card -->
<div class="card" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border-left: 4px solid #3b82f6; margin-bottom: 24px;">
    <div style="display: flex; gap: 16px; align-items: start;">
        <div style="font-size: 32px;">💡</div>
        <div>
            <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 700; color: #1e40af;">Cara Kerja Triangulasi</h3>
            <p style="margin: 0; font-size: 14px; line-height: 1.7; color: #1e3a8a;">
                Masukkan koordinat dan radius coverage dari <strong>3 BTS Tower</strong> berbeda. 
                Sistem akan menghitung titik perpotongan coverage area untuk menentukan lokasi target.
                Map akan otomatis update saat Anda mengubah nilai input.
            </p>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- LEFT: Form Inputs -->
    <div class="card">
        <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 20px; color: #1e293b; display: flex; align-items: center; gap: 8px;">
            <span>📡</span> Input BTS Towers
        </h3>

        <!-- Tower 1 -->
        <div style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border-left: 4px solid #ef4444; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
            <div style="font-weight: 700; color: #991b1b; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">🔴</span> Tower 1 (Merah)
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px; font-weight: 600; color: #64748b;">Latitude</label>
                    <input type="number" id="lat1" step="any" value="-7.797068" 
                           style="padding: 8px 12px; font-size: 13px;" placeholder="-7.797068">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px; font-weight: 600; color: #64748b;">Longitude</label>
                    <input type="number" id="lng1" step="any" value="110.370529" 
                           style="padding: 8px 12px; font-size: 13px;" placeholder="110.370529">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px; font-weight: 600; color: #64748b;">Radius (m)</label>
                    <input type="number" id="radius1" value="1000" min="100" max="50000" 
                           style="padding: 8px 12px; font-size: 13px;" placeholder="1000">
                </div>
            </div>
        </div>

        <!-- Tower 2 -->
        <div style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border-left: 4px solid #3b82f6; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
            <div style="font-weight: 700; color: #1e40af; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">🔵</span> Tower 2 (Biru)
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px; font-weight: 600; color: #64748b;">Latitude</label>
                    <input type="number" id="lat2" step="any" value="-7.782500" 
                           style="padding: 8px 12px; font-size: 13px;" placeholder="-7.782500">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px; font-weight: 600; color: #64748b;">Longitude</label>
                    <input type="number" id="lng2" step="any" value="110.380000" 
                           style="padding: 8px 12px; font-size: 13px;" placeholder="110.380000">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px; font-weight: 600; color: #64748b;">Radius (m)</label>
                    <input type="number" id="radius2" value="800" min="100" max="50000" 
                           style="padding: 8px 12px; font-size: 13px;" placeholder="800">
                </div>
            </div>
        </div>

        <!-- Tower 3 -->
        <div style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-left: 4px solid #10b981; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
            <div style="font-weight: 700; color: #065f46; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">🟢</span> Tower 3 (Hijau)
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px; font-weight: 600; color: #64748b;">Latitude</label>
                    <input type="number" id="lat3" step="any" value="-7.785000" 
                           style="padding: 8px 12px; font-size: 13px;" placeholder="-7.785000">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px; font-weight: 600; color: #64748b;">Longitude</label>
                    <input type="number" id="lng3" step="any" value="110.365000" 
                           style="padding: 8px 12px; font-size: 13px;" placeholder="110.365000">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px; font-weight: 600; color: #64748b;">Radius (m)</label>
                    <input type="number" id="radius3" value="600" min="100" max="50000" 
                           style="padding: 8px 12px; font-size: 13px;" placeholder="600">
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px;">
            <button onclick="calculateTriangulation()" id="calculateBtn" class="btn btn-primary">
                <span id="btnIcon">🎯</span>
                <span id="btnText">Hitung Triangulasi</span>
            </button>
            <button onclick="resetCalculation()" class="btn" style="background: #6b7280; color: white; width: auto;">
                <span>🔄</span>
                <span>Reset</span>
            </button>
        </div>

        <!-- Result Info -->
        <div id="resultInfo" style="display: none; margin-top: 24px; padding: 20px; background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-left: 4px solid #10b981; border-radius: 8px;">
            <div style="font-weight: 700; color: #065f46; margin-bottom: 12px; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                <span>🎯</span> Hasil Triangulasi
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Latitude</div>
                    <div id="resultLat" style="font-size: 18px; font-weight: 700; color: #065f46;">-</div>
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Longitude</div>
                    <div id="resultLng" style="font-size: 18px; font-weight: 700; color: #065f46;">-</div>
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Avg Coverage</div>
                    <div id="resultRadius" style="font-size: 18px; font-weight: 700; color: #065f46;">-</div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT: Map (Single Map) -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #4F7CFF, #3A5FD8); padding: 16px 20px; color: white;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; display: flex; align-items: center; justify-content: space-between;">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <span>🗺️</span> Coverage Map
                </span>
                <span id="mapStatus" style="font-size: 11px; background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 12px;">
                    Real-time
                </span>
            </h3>
        </div>
        
        <div id="triangulationMap" style="width: 100%; height: 600px;"></div>
        
        <!-- Legend -->
        <div style="padding: 16px 20px; background: #f8fafc; border-top: 2px solid #e5e7eb;">
            <div style="display: flex; justify-content: center; gap: 24px; flex-wrap: wrap; font-size: 13px;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <div style="width: 12px; height: 12px; background: #ef4444; border-radius: 50%;"></div>
                    <span style="color: #64748b; font-weight: 600;">Tower 1</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <div style="width: 12px; height: 12px; background: #3b82f6; border-radius: 50%;"></div>
                    <span style="color: #64748b; font-weight: 600;">Tower 2</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <div style="width: 12px; height: 12px; background: #10b981; border-radius: 50%;"></div>
                    <span style="color: #64748b; font-weight: 600;">Tower 3</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="font-size: 16px;">📍</span>
                    <span style="color: #64748b; font-weight: 600;">Target</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tips -->
<div class="card" style="background: linear-gradient(135deg, #fef3c7, #fde68a); border-left: 4px solid #f59e0b; margin-top: 24px;">
    <div style="display: flex; gap: 16px; align-items: start;">
        <div style="font-size: 28px;">⚠️</div>
        <div>
            <h3 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 700; color: #92400e;">Catatan Penting:</h3>
            <ul style="margin: 0; padding-left: 20px; font-size: 13px; line-height: 1.8; color: #78350f;">
                <li><strong>Latitude:</strong> -90 sampai 90 | <strong>Longitude:</strong> -180 sampai 180</li>
                <li><strong>Radius:</strong> 100m - 50,000m (100m - 50km)</li>
                <li>Ketiga koordinat tower <strong>harus berbeda</strong></li>
                <li>Map akan <strong>update otomatis</strong> saat input berubah</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    /* Remove spinner arrows on number input */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        opacity: 1;
    }

    /* Leaflet marker fix */
    .leaflet-marker-icon {
        background: none !important;
        border: none !important;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<script>
    let map = null;
    let mapLayers = [];
    let updateTimeout = null;

    // Initialize map on load
    document.addEventListener('DOMContentLoaded', function() {
        try {
            console.log('🚀 Initializing map...');
            
            map = L.map('triangulationMap').setView([-7.7956, 110.3695], 12);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap',
                maxZoom: 19,
                minZoom: 3
            }).addTo(map);

            console.log('✅ Map initialized');

            // Initial draw
            updateMap();

            // Restore saved state
            restoreTriangulationState();

            // Add input listeners
            const inputs = ['lat1', 'lng1', 'radius1', 'lat2', 'lng2', 'radius2', 'lat3', 'lng3', 'radius3'];
            inputs.forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    input.addEventListener('input', function() {
                        if (updateTimeout) clearTimeout(updateTimeout);
                        updateTimeout = setTimeout(updateMap, 500);
                    });
                }
            });

        } catch (error) {
            console.error('❌ Map initialization error:', error);
            showAlert('error', 'Gagal menginisialisasi map');
        }
    });

    // Update map with towers
    function updateMap() {
        try {
            // Clear previous layers
            mapLayers.forEach(layer => {
                try { map.removeLayer(layer); } catch (e) {}
            });
            mapLayers = [];

            const towers = [
                { lat: parseFloat($('#lat1').val()) || 0, lng: parseFloat($('#lng1').val()) || 0, radius: parseFloat($('#radius1').val()) || 0, color: '#ef4444', label: 'Tower 1' },
                { lat: parseFloat($('#lat2').val()) || 0, lng: parseFloat($('#lng2').val()) || 0, radius: parseFloat($('#radius2').val()) || 0, color: '#3b82f6', label: 'Tower 2' },
                { lat: parseFloat($('#lat3').val()) || 0, lng: parseFloat($('#lng3').val()) || 0, radius: parseFloat($('#radius3').val()) || 0, color: '#10b981', label: 'Tower 3' }
            ];

            const validTowers = towers.filter(t => 
                t.lat && t.lng && t.radius &&
                Math.abs(t.lat) <= 90 && Math.abs(t.lng) <= 180 &&
                t.radius >= 100 && t.radius <= 50000
            );

            if (validTowers.length === 0) return;

            validTowers.forEach(tower => {
                // Circle
                const circle = L.circle([tower.lat, tower.lng], {
                    color: tower.color,
                    fillColor: tower.color,
                    fillOpacity: 0.15,
                    radius: tower.radius,
                    weight: 2
                }).addTo(map);
                mapLayers.push(circle);

                // Marker
                const icon = L.divIcon({
                    html: `<div style="font-size: 24px; text-shadow: 2px 2px 4px rgba(0,0,0,0.4);">📡</div>`,
                    className: '',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });
                
                const marker = L.marker([tower.lat, tower.lng], { icon: icon });
                marker.bindPopup(`<div style="text-align: center;"><b style="color: ${tower.color};">${tower.label}</b><br><small>Lat: ${tower.lat.toFixed(6)}<br>Lng: ${tower.lng.toFixed(6)}<br>Radius: ${tower.radius.toLocaleString()}m</small></div>`);
                marker.addTo(map);
                mapLayers.push(marker);
            });

            // Fit bounds
            if (mapLayers.length > 0) {
                const bounds = L.featureGroup(mapLayers).getBounds();
                map.fitBounds(bounds.pad(0.2));
            }

            console.log('✅ Map updated');

        } catch (error) {
            console.error('❌ Map update error:', error);
        }
    }

    // Calculate triangulation
    function calculateTriangulation() {
        try {
            const btn = $('#calculateBtn');
            const btnText = $('#btnText');
            const btnIcon = $('#btnIcon');
            
            btn.prop('disabled', true);
            btnIcon.text('⏳');
            btnText.text('Menghitung...');

            // Validate
            const values = validateInputs();

            // Calculate centroid
            const resultLat = (values.lat1 + values.lat2 + values.lat3) / 3;
            const resultLng = (values.lng1 + values.lng2 + values.lng3) / 3;
            const avgRadius = Math.round((values.r1 + values.r2 + values.r3) / 3);

            // Clear previous target
            mapLayers.forEach(layer => {
                try { map.removeLayer(layer); } catch (e) {}
            });
            mapLayers = [];

            // Redraw towers
            const towers = [
                { lat: values.lat1, lng: values.lng1, radius: values.r1, color: '#ef4444', label: 'Tower 1' },
                { lat: values.lat2, lng: values.lng2, radius: values.r2, color: '#3b82f6', label: 'Tower 2' },
                { lat: values.lat3, lng: values.lng3, radius: values.r3, color: '#10b981', label: 'Tower 3' }
            ];

            towers.forEach(tower => {
                const circle = L.circle([tower.lat, tower.lng], {
                    color: tower.color,
                    fillColor: tower.color,
                    fillOpacity: 0.15,
                    radius: tower.radius,
                    weight: 2
                }).addTo(map);
                mapLayers.push(circle);

                const icon = L.divIcon({
                    html: `<div style="font-size: 24px; text-shadow: 2px 2px 4px rgba(0,0,0,0.4);">📡</div>`,
                    className: '',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });
                
                const marker = L.marker([tower.lat, tower.lng], { icon: icon });
                marker.bindPopup(`<div style="text-align: center;"><b style="color: ${tower.color};">${tower.label}</b><br><small>Lat: ${tower.lat.toFixed(6)}<br>Lng: ${tower.lng.toFixed(6)}<br>Coverage: ${tower.radius.toLocaleString()}m</small></div>`);
                marker.addTo(map);
                mapLayers.push(marker);
            });

            // Add target marker
            const targetIcon = L.divIcon({
                html: `<div style="font-size: 32px; text-shadow: 3px 3px 6px rgba(0,0,0,0.5);">📍</div>`,
                className: '',
                iconSize: [32, 32],
                iconAnchor: [16, 32]
            });

            const targetMarker = L.marker([resultLat, resultLng], { icon: targetIcon });
            targetMarker.bindPopup(`<div style="text-align: center; min-width: 160px;"><b style="color: #6366f1; font-size: 15px;">🎯 LOKASI TARGET</b><br><div style="margin-top: 8px; background: #eff6ff; padding: 10px; border-radius: 6px; font-size: 13px;"><b>Hasil Triangulasi:</b><br>Lat: ${resultLat.toFixed(6)}<br>Lng: ${resultLng.toFixed(6)}<br>Avg: ${avgRadius.toLocaleString()}m</div></div>`);
            targetMarker.addTo(map);
            targetMarker.openPopup();
            mapLayers.push(targetMarker);

            // Fit bounds
            const bounds = L.featureGroup(mapLayers).getBounds();
            map.fitBounds(bounds.pad(0.15));

            // Show result
            $('#resultLat').text(resultLat.toFixed(6));
            $('#resultLng').text(resultLng.toFixed(6));
            $('#resultRadius').text(`${avgRadius.toLocaleString()}m (~${(avgRadius/1000).toFixed(1)}km)`);
            $('#resultInfo').slideDown(300);

            // Update map status
            $('#mapStatus').text('Hasil Ditampilkan').css('background', 'rgba(16, 185, 129, 0.3)');

            showAlert('success', '✅ Triangulasi berhasil dihitung!');

            // Save state
            saveTriangulationState(values, resultLat, resultLng);

        } catch (error) {
            console.error('❌ Calculation error:', error);
            showAlert('error', '❌ ' + error.message);
        } finally {
            setTimeout(() => {
                $('#calculateBtn').prop('disabled', false);
                $('#btnIcon').text('🎯');
                $('#btnText').text('Hitung Triangulasi');
            }, 500);
        }
    }

    // Validate inputs
    function validateInputs() {
        const values = {
            lat1: parseFloat($('#lat1').val()),
            lng1: parseFloat($('#lng1').val()),
            r1: parseFloat($('#radius1').val()),
            lat2: parseFloat($('#lat2').val()),
            lng2: parseFloat($('#lng2').val()),
            r2: parseFloat($('#radius2').val()),
            lat3: parseFloat($('#lat3').val()),
            lng3: parseFloat($('#lng3').val()),
            r3: parseFloat($('#radius3').val())
        };

        // Check NaN
        for (let key in values) {
            if (isNaN(values[key])) {
                throw new Error('Semua field harus diisi dengan angka yang valid!');
            }
        }

        // Validate ranges
        [values.lat1, values.lat2, values.lat3].forEach(lat => {
            if (Math.abs(lat) > 90) throw new Error('Latitude harus antara -90 sampai 90!');
        });

        [values.lng1, values.lng2, values.lng3].forEach(lng => {
            if (Math.abs(lng) > 180) throw new Error('Longitude harus antara -180 sampai 180!');
        });

        [values.r1, values.r2, values.r3].forEach(r => {
            if (r < 100 || r > 50000) throw new Error('Radius harus antara 100m sampai 50,000m!');
        });

        // Check duplicates
        if ((values.lat1 === values.lat2 && values.lng1 === values.lng2) ||
            (values.lat1 === values.lat3 && values.lng1 === values.lng3) ||
            (values.lat2 === values.lat3 && values.lng2 === values.lng3)) {
            throw new Error('Koordinat ketiga BTS tower harus berbeda!');
        }

        return values;
    }

    // Reset
    function resetCalculation() {
        $('#resultInfo').slideUp(300);
        $('#mapStatus').text('Real-time').css('background', 'rgba(255,255,255,0.2)');
        
        mapLayers.forEach(layer => {
            try { map.removeLayer(layer); } catch (e) {}
        });
        mapLayers = [];
        
        updateMap();
        
        localStorage.removeItem('triangulation_state');
        showAlert('success', '🔄 Reset berhasil!');
    }

    // Save state
    function saveTriangulationState(values, lat, lng) {
        const state = {
            ...values,
            resultLat: lat,
            resultLng: lng,
            hasResult: true,
            timestamp: new Date().toISOString()
        };
        localStorage.setItem('triangulation_state', JSON.stringify(state));
           let history = JSON.parse(localStorage.getItem('triangulation_history') || '[]');
    history.unshift(state); // Add to beginning
    if (history.length > 50) history = history.slice(0, 50); // Keep only 50 latest
    localStorage.setItem('triangulation_history', JSON.stringify(history));
}

    // Restore state
    function restoreTriangulationState() {
        const saved = localStorage.getItem('triangulation_state');
        if (!saved) return;

        try {
            const state = JSON.parse(saved);
            
            $('#lat1').val(state.lat1);
            $('#lng1').val(state.lng1);
            $('#radius1').val(state.r1);
            $('#lat2').val(state.lat2);
            $('#lng2').val(state.lng2);
            $('#radius2').val(state.r2);
            $('#lat3').val(state.lat3);
            $('#lng3').val(state.lng3);
            $('#radius3').val(state.r3);
            
            if (state.hasResult) {
                setTimeout(() => {
                    calculateTriangulation();
                    showAlert('success', '✓ Data triangulasi dimuat!');
                }, 1000);
            }
        } catch (e) {
            console.error('Error restoring state:', e);
        }
    }
</script>
@endpush