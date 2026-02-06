@extends('layouts.app')

@section('title', 'Sector Calculator - BTS Tracker')

@section('content')
<div class="page-header">
    <h2>📡 Sector Calculator (4G/LTE)</h2>
    <p>Hitung sektor coverage dari BTS Tower berdasarkan Cell ID</p>
</div>

<!-- Info Alert -->
<div class="info-alert" style="margin-bottom: 24px;">
    <div style="display: flex; gap: 16px;">
        <div style="font-size: 32px;">💡</div>
        <div>
            <h4 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 700; color: #1e40af;">Cara Menggunakan:</h4>
            <ul style="margin: 0; padding-left: 20px; font-size: 13px; line-height: 1.8; color: #1e3a8a;">
                <li>Masukkan <strong>koordinat Tower</strong> (lokasi BTS fisik)</li>
                <li>Masukkan <strong>koordinat Cell</strong> (hasil dari Cell ID Lookup)</li>
                <li>Masukkan <strong>CID</strong> untuk menghitung sektor otomatis</li>
                <li>Klik <strong>"Periksa & Gambar"</strong> untuk visualisasi</li>
            </ul>
        </div>
    </div>
</div>

<div class="card">
    <form id="sectorForm">
        @csrf
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
            <!-- Tower Coordinates -->
            <div>
                <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <span>🗼</span> Koordinat Tower (BTS Fisik)
                </h3>
                
                <div class="form-group">
                    <label for="towerLat">Tower Latitude <span style="color: #ef4444;">*</span></label>
                    <input type="number" id="towerLat" name="tower_lat" step="any" placeholder="Contoh: -6.1753900" required>
                    <div class="form-hint">Koordinat latitude lokasi tower BTS</div>
                </div>

                <div class="form-group">
                    <label for="towerLon">Tower Longitude <span style="color: #ef4444;">*</span></label>
                    <input type="number" id="towerLon" name="tower_lon" step="any" placeholder="Contoh: 106.8270400" required>
                    <div class="form-hint">Koordinat longitude lokasi tower BTS</div>
                </div>
            </div>

            <!-- Cell Coordinates -->
            <div>
                <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <span>📍</span> Koordinat Cell (Hasil Cell ID)
                </h3>
                
                <div class="form-group">
                    <label for="cellLat">Cell Latitude <span style="color: #ef4444;">*</span></label>
                    <input type="number" id="cellLat" name="cell_lat" step="any" placeholder="Contoh: -1.23456" required>
                    <div class="form-hint">Hasil latitude dari Cell ID Lookup</div>
                </div>

                <div class="form-group">
                    <label for="cellLon">Cell Longitude <span style="color: #ef4444;">*</span></label>
                    <input type="number" id="cellLon" name="cell_lon" step="any" placeholder="Contoh: 103.456789" required>
                    <div class="form-hint">Hasil longitude dari Cell ID Lookup</div>
                </div>
            </div>
        </div>

        <!-- CID -->
        <div class="form-group">
            <label for="cid">CID (Cell ID) <span style="color: #ef4444;">*</span></label>
            <input type="number" id="cid" name="cid" placeholder="Masukkan nilai CID (angka)" required min="0">
            <div class="form-hint">Cell ID untuk menghitung sektor (eNodeB ID dan Sector ID akan dihitung otomatis)</div>
        </div>

        <!-- Buttons -->
        <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px;">
            <button type="submit" class="btn btn-primary" id="calculateBtn">
                <span>📡</span>
                <span id="btnText">Periksa & Gambar</span>
            </button>
            <button type="button" onclick="resetForm()" class="btn" style="background: #6b7280; color: white; width: auto;">
                <span>🔄</span>
                <span>Bersihkan</span>
            </button>
        </div>
    </form>
</div>

<!-- Result Section -->
<div id="resultSection" style="display: none; margin-top: 32px;">
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #4F7CFF, #3A5FD8); padding: 20px 24px; color: white;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">✓</span> Hasil Perhitungan Sektor
            </h3>
        </div>
        
        <div style="padding: 24px;">
            <!-- Info Grid -->
            <div id="sectorInfo" class="info-grid" style="margin-bottom: 24px;"></div>
            
            <!-- Map -->
            <div id="sectorMap" style="width: 100%; height: 500px; border-radius: 12px; border: 2px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"></div>
        </div>
    </div>
</div>

<!-- 3D Tower Illustration -->
<div class="card" style="text-align: center; padding: 32px;">
    <h3 style="margin-bottom: 24px; font-size: 18px; font-weight: 700; color: #1e293b;">
        Ilustrasi Tower dengan 3 Sektor Coverage
    </h3>
    <div style="max-width: 500px; margin: 0 auto;">
        <svg viewBox="0 0 400 300" style="width: 100%; height: auto;">
            <!-- Base -->
            <ellipse cx="200" cy="250" rx="150" ry="30" fill="#94a3b8" opacity="0.3"/>
            
            <!-- Tower Body -->
            <polygon points="190,250 210,250 205,100 195,100" fill="#64748b"/>
            
            <!-- Tower Top -->
            <circle cx="200" cy="90" r="12" fill="#334155"/>
            
            <!-- Antenna -->
            <rect x="197" y="50" width="6" height="40" fill="#1e293b" rx="3"/>
            <polygon points="200,45 195,55 205,55" fill="#ef4444"/>
            
            <!-- Sector 1 (0-120°) - Red -->
            <path d="M 200 150 L 280 120 A 100 100 0 0 1 280 180 Z" fill="#ef4444" opacity="0.4"/>
            <text x="260" y="145" font-size="12" font-weight="bold" fill="#991b1b">Sektor 1</text>
            <circle cx="280" cy="150" r="4" fill="#dc2626"/>
            
            <!-- Sector 2 (120-240°) - Blue -->
            <path d="M 200 150 L 120 180 A 100 100 0 0 1 120 120 Z" fill="#3b82f6" opacity="0.4"/>
            <text x="105" y="145" font-size="12" font-weight="bold" fill="#1e40af">Sektor 2</text>
            <circle cx="120" cy="150" r="4" fill="#2563eb"/>
            
            <!-- Sector 3 (240-360°) - Green -->
            <path d="M 200 150 L 200 230 A 100 100 0 0 1 280 180 Z" fill="#10b981" opacity="0.4"/>
            <text x="210" y="210" font-size="12" font-weight="bold" fill="#065f46">Sektor 3</text>
            <circle cx="240" cy="215" r="4" fill="#059669"/>
            
            <!-- Signal Waves -->
            <path d="M 200 90 Q 180 80 170 70" stroke="#f59e0b" stroke-width="2" fill="none" opacity="0.6"/>
            <path d="M 200 90 Q 220 80 230 70" stroke="#f59e0b" stroke-width="2" fill="none" opacity="0.6"/>
            <path d="M 200 90 Q 190 75 180 60" stroke="#f59e0b" stroke-width="2" fill="none" opacity="0.4"/>
            <path d="M 200 90 Q 210 75 220 60" stroke="#f59e0b" stroke-width="2" fill="none" opacity="0.4"/>
        </svg>
    </div>
    <p style="margin-top: 20px; font-size: 13px; color: #64748b; line-height: 1.6;">
        Setiap BTS Tower 4G biasanya memiliki <strong>3 sektor</strong> dengan coverage <strong>120° masing-masing</strong>.<br>
        CID digunakan untuk menentukan sektor mana yang aktif untuk koneksi tertentu.
    </p>
</div>
@endsection

@push('scripts')
<style>
    .info-alert {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        border-left: 4px solid #3b82f6;
        border-radius: 12px;
        padding: 20px 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .info-alert ul li {
        margin-bottom: 6px;
    }
    
    .info-alert ul li:last-child {
        margin-bottom: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        #sectorForm > div[style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<script>
    let sectorMap = null;
    let towerMarker = null;
    let cellMarker = null;
    let sectorLayer = null;

    $(document).ready(function() {
        $('#sectorForm').on('submit', function(e) {
            e.preventDefault();
            calculateSector();
        });
    });

    function calculateSector() {
        const towerLat = parseFloat($('#towerLat').val());
        const towerLon = parseFloat($('#towerLon').val());
        const cellLat = parseFloat($('#cellLat').val());
        const cellLon = parseFloat($('#cellLon').val());
        const cid = parseInt($('#cid').val());

        // Validate inputs
        if (!towerLat || !towerLon || !cellLat || !cellLon || !cid) {
            showAlert('error', 'Mohon lengkapi semua field!');
            return;
        }

        if (Math.abs(towerLat) > 90 || Math.abs(cellLat) > 90) {
            showAlert('error', 'Latitude harus antara -90 dan 90!');
            return;
        }

        if (Math.abs(towerLon) > 180 || Math.abs(cellLon) > 180) {
            showAlert('error', 'Longitude harus antara -180 dan 180!');
            return;
        }

        // Calculate eNodeB ID and Sector ID
        const eNodeBId = Math.floor(cid / 256); // CID >> 8
        const sectorId = cid % 256; // CID & 0xFF
        
        // Determine sector number (1-3) based on sector ID
        let sectorNumber;
        let sectorColor;
        let sectorName;
        
        if (sectorId >= 0 && sectorId < 85) {
            sectorNumber = 1;
            sectorColor = '#ef4444'; // Red
            sectorName = 'Sektor 1 (0°-120°)';
        } else if (sectorId >= 85 && sectorId < 170) {
            sectorNumber = 2;
            sectorColor = '#3b82f6'; // Blue
            sectorName = 'Sektor 2 (120°-240°)';
        } else {
            sectorNumber = 3;
            sectorColor = '#10b981'; // Green
            sectorName = 'Sektor 3 (240°-360°)';
        }

        // Calculate bearing from tower to cell
        const bearing = calculateBearing(towerLat, towerLon, cellLat, cellLon);
        
        // Calculate distance
        const distance = calculateDistance(towerLat, towerLon, cellLat, cellLon);

        // Display results
        displaySectorResult({
            towerLat,
            towerLon,
            cellLat,
            cellLon,
            cid,
            eNodeBId,
            sectorId,
            sectorNumber,
            sectorColor,
            sectorName,
            bearing,
            distance
        });

        showAlert('success', '✓ Perhitungan sektor berhasil!');
    }

    function calculateBearing(lat1, lon1, lat2, lon2) {
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const y = Math.sin(dLon) * Math.cos(lat2 * Math.PI / 180);
        const x = Math.cos(lat1 * Math.PI / 180) * Math.sin(lat2 * Math.PI / 180) -
                  Math.sin(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.cos(dLon);
        const bearing = Math.atan2(y, x) * 180 / Math.PI;
        return (bearing + 360) % 360;
    }

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // Earth radius in meters
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return Math.round(R * c);
    }

    function displaySectorResult(data) {
        // Info cards
        const infoHtml = `
            <div class="info-item" style="border-color: #6366f1;">
                <div class="info-item-label">🏢 eNodeB ID</div>
                <div class="info-item-value">${data.eNodeBId}</div>
                <div style="font-size: 11px; margin-top: 4px; color: #64748b;">CID ÷ 256</div>
            </div>
            <div class="info-item" style="border-color: ${data.sectorColor};">
                <div class="info-item-label">📡 Sector ID</div>
                <div class="info-item-value">${data.sectorId}</div>
                <div style="font-size: 11px; margin-top: 4px; color: #64748b;">CID % 256</div>
            </div>
            <div class="info-item" style="border-color: ${data.sectorColor};">
                <div class="info-item-label">🎯 Sektor Aktif</div>
                <div class="info-item-value" style="color: ${data.sectorColor};">Sektor ${data.sectorNumber}</div>
                <div style="font-size: 11px; margin-top: 4px; color: #64748b;">${data.sectorName}</div>
            </div>
            <div class="info-item" style="border-color: #f59e0b;">
                <div class="info-item-label">🧭 Bearing</div>
                <div class="info-item-value">${data.bearing.toFixed(2)}°</div>
                <div style="font-size: 11px; margin-top: 4px; color: #64748b;">Arah dari Tower ke Cell</div>
            </div>
            <div class="info-item" style="border-color: #8b5cf6;">
                <div class="info-item-label">📏 Jarak</div>
                <div class="info-item-value">${data.distance.toLocaleString()} m</div>
                <div style="font-size: 11px; margin-top: 4px; color: #64748b;">~${(data.distance/1000).toFixed(2)} km</div>
            </div>
            <div class="info-item" style="border-color: #ec4899;">
                <div class="info-item-label">🔢 CID</div>
                <div class="info-item-value">${data.cid}</div>
                <div style="font-size: 11px; margin-top: 4px; color: #64748b;">Cell ID Input</div>
            </div>
        `;

        $('#sectorInfo').html(infoHtml);

        // Initialize or update map
        if (!sectorMap) {
            sectorMap = L.map('sectorMap').setView([data.towerLat, data.towerLon], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap',
                maxZoom: 19
            }).addTo(sectorMap);
        }

        // Clear previous markers
        if (towerMarker) sectorMap.removeLayer(towerMarker);
        if (cellMarker) sectorMap.removeLayer(cellMarker);
        if (sectorLayer) sectorMap.removeLayer(sectorLayer);

        // Tower marker
        const towerIcon = L.divIcon({
            html: '<div style="font-size: 32px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">🗼</div>',
            className: '',
            iconSize: [32, 32],
            iconAnchor: [16, 32]
        });
        towerMarker = L.marker([data.towerLat, data.towerLon], { icon: towerIcon });
        towerMarker.bindPopup(`<div style="text-align: center;"><b>🗼 BTS Tower</b><br><small>Lat: ${data.towerLat.toFixed(6)}<br>Lng: ${data.towerLon.toFixed(6)}</small></div>`);
        towerMarker.addTo(sectorMap);

        // Cell marker
        const cellIcon = L.divIcon({
            html: '<div style="font-size: 24px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">📍</div>',
            className: '',
            iconSize: [24, 24],
            iconAnchor: [12, 24]
        });
        cellMarker = L.marker([data.cellLat, data.cellLon], { icon: cellIcon });
        cellMarker.bindPopup(`<div style="text-align: center;"><b style="color: ${data.sectorColor};">📍 Cell Location</b><br><small>Lat: ${data.cellLat.toFixed(6)}<br>Lng: ${data.cellLon.toFixed(6)}<br><b>Sektor ${data.sectorNumber}</b></small></div>`);
        cellMarker.addTo(sectorMap);

        // Draw sector wedge
        const sectorStartAngle = (data.sectorNumber - 1) * 120;
        const sectorEndAngle = data.sectorNumber * 120;
        
        // Create sector polygon
        const radius = Math.max(data.distance * 1.5, 1000); // At least 1km
        const points = [[data.towerLat, data.towerLon]];
        
        for (let angle = sectorStartAngle; angle <= sectorEndAngle; angle += 5) {
            const rad = angle * Math.PI / 180;
            const lat = data.towerLat + (radius / 111000) * Math.cos(rad);
            const lon = data.towerLon + (radius / (111000 * Math.cos(data.towerLat * Math.PI / 180))) * Math.sin(rad);
            points.push([lat, lon]);
        }
        points.push([data.towerLat, data.towerLon]);

        sectorLayer = L.polygon(points, {
            color: data.sectorColor,
            fillColor: data.sectorColor,
            fillOpacity: 0.2,
            weight: 2
        }).addTo(sectorMap);

        // Draw line from tower to cell
        L.polyline([[data.towerLat, data.towerLon], [data.cellLat, data.cellLon]], {
            color: data.sectorColor,
            weight: 3,
            dashArray: '10, 10'
        }).addTo(sectorMap);

        // Fit bounds
        const bounds = L.latLngBounds([
            [data.towerLat, data.towerLon],
            [data.cellLat, data.cellLon]
        ]);
        sectorMap.fitBounds(bounds.pad(0.3));

        // Show result section
        $('#resultSection').slideDown(300);
        
        setTimeout(() => {
            sectorMap.invalidateSize();
            $('html, body').animate({
                scrollTop: $('#resultSection').offset().top - 100
            }, 500);
        }, 400);
         const stateToSave = {
        formData: {
            towerLat: data.towerLat,
            towerLon: data.towerLon,
            cellLat: data.cellLat,
            cellLon: data.cellLon,
            cid: data.cid
        },
        result: data,
        timestamp: new Date().toISOString()
    };
    localStorage.setItem('sector_calculator_state', JSON.stringify(stateToSave));
    
    // Save to history array
    let history = JSON.parse(localStorage.getItem('sector_history') || '[]');
    history.unshift(data); // Add to beginning
    if (history.length > 50) history = history.slice(0, 50); // Keep only 50 latest
    localStorage.setItem('sector_history', JSON.stringify(history));
    
    }

    function resetForm() {
        $('#sectorForm')[0].reset();
        $('#resultSection').slideUp(300);
        
        if (towerMarker) sectorMap.removeLayer(towerMarker);
        if (cellMarker) sectorMap.removeLayer(cellMarker);
        if (sectorLayer) sectorMap.removeLayer(sectorLayer);
        
        showAlert('success', '🔄 Form berhasil direset!');
    }
</script>
@endpush