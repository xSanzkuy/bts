@extends('layouts.app')

@section('title', 'Cell ID Lookup - BTS Tracker')

@section('content')
<div class="page-header">
    <h2>Cell ID Location</h2>
    <p>Masukkan MCC, MNC, LAC dan CID sesuai jaringan</p>
</div>

<div class="card">
    <form id="lookupForm">
        @csrf
        
        <div class="form-group">
            <label for="radioType">Request (Radio) <span style="color: #ef4444;">*</span></label>
            <select id="radioType" name="radio_type" required>
                <option value="">-- Pilih Radio Type --</option>
                <option value="lte">LTE / 4G</option>
                <option value="gsm">GSM / 2G</option>
                <option value="umts">UMTS / 3G</option>
                <option value="cdma">CDMA / 3G</option>
                <option value="nr">NR (5G)</option>
            </select>
            <div class="form-hint">Pilih jenis teknologi jaringan</div>
            <div class="error-message" id="error-radioType"></div>
        </div>

        <div class="form-group">
            <label for="mcc">MCC (Mobile Country Code) <span style="color: #ef4444;">*</span></label>
            <input type="number" id="mcc" name="mcc" placeholder="Contoh Indonesia: 510" required min="1" max="999">
            <div class="form-hint">Contoh Indonesia: 510</div>
            <div class="error-message" id="error-mcc"></div>
        </div>

        <div class="form-group">
            <label for="mnc">MNC (Mobile Network Code) <span style="color: #ef4444;">*</span></label>
            <input type="number" id="mnc" name="mnc" placeholder="Contoh Telkomsel: 10" required min="0" max="999">
            <div class="form-hint">Contoh Telkomsel: 10, XL: 11, Indosat: 01</div>
            <div class="error-message" id="error-mnc"></div>
        </div>

        <div class="form-group">
            <label for="lac">LAC (Location Area Code) <span style="color: #ef4444;">*</span></label>
            <input type="number" id="lac" name="lac" placeholder="Contoh: 21071" required min="0">
            <div class="form-hint">LAC atau TAC untuk LTE</div>
            <div class="error-message" id="error-lac"></div>
        </div>

        <div class="form-group">
            <label for="cid">CID (Cell ID) <span style="color: #ef4444;">*</span></label>
            <input type="number" id="cid" name="cid" placeholder="Contoh: 9365762" required min="0">
            <div class="form-hint">Cell ID atau ECI untuk LTE</div>
            <div class="error-message" id="error-cid"></div>
        </div>

        <button type="submit" class="btn btn-primary" id="submitBtn">
            <span>🔍</span>
            <span id="btnText">Cari Lokasi BTS</span>
        </button>
    </form>

    <div id="resultSection" class="result-section">
        <h3 style="margin-bottom: 16px; font-size: 18px; display: flex; align-items: center; gap: 8px;">
            <span style="color: #10b981;">✓</span> Hasil Pencarian
        </h3>
        <div class="result-info" id="resultInfo"></div>
        <div id="map"></div>
    </div>

    <!-- Error Display Card -->
    <div id="errorCard" style="display: none; margin-top: 20px; padding: 20px; background: #fee2e2; border-left: 4px solid #ef4444; border-radius: 8px;">
        <h4 style="color: #991b1b; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 20px;">⚠️</span>
            <span>Terjadi Kesalahan</span>
        </h4>
        <p id="errorMessage" style="color: #7f1d1d; line-height: 1.6;"></p>
        <button onclick="hideError()" style="margin-top: 12px; padding: 8px 16px; background: #991b1b; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">
            Tutup
        </button>
    </div>
</div>
@endsection

@push('scripts')
<style>
    /* ========================================
       MAP CONTAINER
       ======================================== */
    #map {
        width: 100%;
        height: 500px;
        border-radius: 12px;
        border: 2px solid #e5e7eb;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        margin-top: 24px;
    }

    /* ========================================
       RESULT SECTION
       ======================================== */
    .result-section {
        display: none;
        margin-top: 32px;
        padding: 0;
        background: transparent;
        animation: fadeInUp 0.5s ease-out;
    }

    .result-section.show {
        display: block;
    }

    .result-section h3 {
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* ========================================
       WARNING BOXES (Accuracy Warnings)
       ======================================== */
    .accuracy-warning {
        padding: 20px 24px;
        border-radius: 12px;
        margin-bottom: 24px;
        border-left: 4px solid;
        background: linear-gradient(135deg, var(--bg-start), var(--bg-end));
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .accuracy-warning h4 {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .accuracy-warning p {
        font-size: 14px;
        line-height: 1.7;
        margin: 0;
    }

    /* Low Accuracy - Red */
    .accuracy-warning.low {
        --bg-start: #fef2f2;
        --bg-end: #fee2e2;
        border-color: #ef4444;
    }

    .accuracy-warning.low h4 {
        color: #991b1b;
    }

    .accuracy-warning.low p {
        color: #7f1d1d;
    }

    /* Medium Accuracy - Orange */
    .accuracy-warning.medium {
        --bg-start: #fffbeb;
        --bg-end: #fef3c7;
        border-color: #f59e0b;
    }

    .accuracy-warning.medium h4 {
        color: #92400e;
    }

    .accuracy-warning.medium p {
        color: #78350f;
    }

    /* Good Accuracy - Blue */
    .accuracy-warning.good {
        --bg-start: #eff6ff;
        --bg-end: #dbeafe;
        border-color: #3b82f6;
    }

    .accuracy-warning.good h4 {
        color: #1e40af;
    }

    .accuracy-warning.good p {
        color: #1e3a8a;
    }

    /* High Accuracy - Green */
    .accuracy-warning.high {
        --bg-start: #f0fdf4;
        --bg-end: #dcfce7;
        border-color: #10b981;
    }

    .accuracy-warning.high h4 {
        color: #065f46;
    }

    .accuracy-warning.high p {
        color: #064e3b;
    }

    /* ========================================
       INFO GRID & CARDS
       ======================================== */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .info-item {
        padding: 20px 24px;
        border-radius: 12px;
        border-left: 4px solid;
        background: linear-gradient(135deg, var(--card-bg-start), var(--card-bg-end));
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .info-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .info-item::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(50%, -50%);
    }

    /* Default - Blue */
    .info-item {
        --card-bg-start: #eff6ff;
        --card-bg-end: #dbeafe;
        border-color: #3b82f6;
    }

    /* Low Accuracy - Red */
    .info-item.low-accuracy {
        --card-bg-start: #fef2f2;
        --card-bg-end: #fee2e2;
        border-color: #ef4444;
    }

    /* Medium Accuracy - Orange */
    .info-item.medium-accuracy {
        --card-bg-start: #fffbeb;
        --card-bg-end: #fef3c7;
        border-color: #f59e0b;
    }

    /* Good Accuracy - Blue */
    .info-item.good-accuracy {
        --card-bg-start: #eff6ff;
        --card-bg-end: #dbeafe;
        border-color: #3b82f6;
    }

    /* High Accuracy - Green */
    .info-item.high-accuracy {
        --card-bg-start: #f0fdf4;
        --card-bg-end: #dcfce7;
        border-color: #10b981;
    }

    .info-item-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 8px;
        position: relative;
        z-index: 1;
    }

    .info-item-value {
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
        word-break: break-word;
        position: relative;
        z-index: 1;
    }

    .info-item .subtitle {
        font-size: 12px;
        color: #64748b;
        margin-top: 6px;
        font-weight: 500;
    }

    /* ========================================
       ERROR HANDLING
       ======================================== */
    .error-message {
        color: #ef4444;
        font-size: 12px;
        margin-top: 6px;
        display: none;
        font-weight: 600;
        animation: shake 0.3s;
    }

    .error-message.show {
        display: block;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }

    .form-group input.error,
    .form-group select.error {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .form-group input.success,
    .form-group select.success {
        border-color: #10b981;
        background: #f0fdf4;
    }

    #errorCard {
        margin-top: 24px;
        padding: 24px;
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border-left: 4px solid #ef4444;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
    }

    #errorCard h4 {
        color: #991b1b;
        margin-bottom: 12px;
        font-size: 16px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #errorCard p {
        color: #7f1d1d;
        line-height: 1.7;
        margin: 0;
    }

    #errorCard button {
        margin-top: 16px;
        padding: 10px 20px;
        background: #991b1b;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s;
    }

    #errorCard button:hover {
        background: #7f1d1d;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    /* ========================================
       ANIMATIONS
       ======================================== */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ========================================
       LEAFLET POPUP STYLING
       ======================================== */
    .leaflet-popup-content-wrapper {
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .leaflet-popup-content {
        margin: 16px 20px;
        font-family: 'Sora', sans-serif;
    }

    .leaflet-popup-tip {
        box-shadow: 0 3px 14px rgba(0, 0, 0, 0.1);
    }

    /* ========================================
       RESPONSIVE
       ======================================== */
    @media (max-width: 768px) {
        #map {
            height: 400px;
        }

        .info-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .result-section h3 {
            font-size: 18px;
        }

        .info-item-value {
            font-size: 18px;
        }

        .accuracy-warning {
            padding: 16px 20px;
        }
    }
</style>

<script>
    let map = null;
    let marker = null;
    let circle = null;
    let isSubmitting = false;

    $(document).ready(function() {
        initializeMap();
        setupFormValidation();
        
        $('#lookupForm').on('submit', function(e) {
            e.preventDefault();
            if (!isSubmitting) {
                searchCellLocation();
            }
        });
    });

    function initializeMap() {
        try {
            if (!map) {
                map = L.map('map').setView([-7.7956, 110.3695], 13);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap',
                    maxZoom: 19,
                    minZoom: 3
                }).addTo(map);

                setTimeout(() => {
                    map.invalidateSize();
                }, 100);
            }
        } catch (error) {
            console.error('Map initialization error:', error);
            showError('Gagal menginisialisasi peta. Pastikan koneksi internet aktif.');
        }
    }

    function setupFormValidation() {
        $('#mcc, #mnc, #lac, #cid').on('input', function() {
            const field = $(this);
            const value = field.val();
            const fieldName = field.attr('name');
            
            clearFieldError(fieldName);
            
            if (value && parseInt(value) < 0) {
                showFieldError(fieldName, 'Nilai tidak boleh negatif');
                field.addClass('error').removeClass('success');
            } else if (value) {
                field.addClass('success').removeClass('error');
            }
        });

        $('#radioType').on('change', function() {
            const field = $(this);
            clearFieldError('radio_type');
            if (field.val()) {
                field.addClass('success').removeClass('error');
            }
        });
    }

    function validateForm() {
        let isValid = true;
        clearAllErrors();

        const radioType = $('#radioType').val();
        const mcc = $('#mcc').val();
        const mnc = $('#mnc').val();
        const lac = $('#lac').val();
        const cid = $('#cid').val();

        if (!radioType) {
            showFieldError('radioType', 'Radio type harus dipilih');
            $('#radioType').addClass('error');
            isValid = false;
        }

        if (!mcc || parseInt(mcc) < 1 || parseInt(mcc) > 999) {
            showFieldError('mcc', 'MCC harus antara 1-999');
            $('#mcc').addClass('error');
            isValid = false;
        }

        if (!mnc || parseInt(mnc) < 0 || parseInt(mnc) > 999) {
            showFieldError('mnc', 'MNC harus antara 0-999');
            $('#mnc').addClass('error');
            isValid = false;
        }

        if (!lac || parseInt(lac) < 0) {
            showFieldError('lac', 'LAC harus diisi dengan nilai positif');
            $('#lac').addClass('error');
            isValid = false;
        }

        if (!cid || parseInt(cid) < 0) {
            showFieldError('cid', 'CID harus diisi dengan nilai positif');
            $('#cid').addClass('error');
            isValid = false;
        }

        return isValid;
    }

    function showFieldError(fieldName, message) {
        $(`#error-${fieldName}`).text(message).addClass('show');
    }

    function clearFieldError(fieldName) {
        $(`#error-${fieldName}`).removeClass('show').text('');
    }

    function clearAllErrors() {
        $('.error-message').removeClass('show').text('');
        $('.form-group input, .form-group select').removeClass('error');
    }

    function showError(message) {
        $('#errorMessage').text(message);
        $('#errorCard').slideDown(300);
        
        $('html, body').animate({
            scrollTop: $('#errorCard').offset().top - 100
        }, 500);
    }

    function hideError() {
        $('#errorCard').slideUp(300);
    }

    function searchCellLocation() {
        if (!validateForm()) {
            showAlert('error', 'Mohon lengkapi semua field dengan benar');
            return;
        }

        hideError();
        isSubmitting = true;

        const formData = {
            radio_type: $('#radioType').val(),
            mcc: $('#mcc').val(),
            mnc: $('#mnc').val(),
            lac: $('#lac').val(),
            cid: $('#cid').val(),
        };

        const submitBtn = $('#submitBtn');
        const btnText = $('#btnText');
        submitBtn.prop('disabled', true);
        btnText.text('Mencari...');

        showLoading();
        $('#resultSection').removeClass('show');

        $.ajax({
            url: '{{ route("api.search") }}',
            method: 'POST',
            data: formData,
            timeout: 30000,
            success: function(response) {
                hideLoading();
                isSubmitting = false;
                submitBtn.prop('disabled', false);
                btnText.text('Cari Lokasi BTS');

                if (response.success && response.data) {
                    displayResult(response.data, formData);
                    showAlert('success', '✓ ' + response.message);
                    
                    setTimeout(() => {
                        $('html, body').animate({
                            scrollTop: $('#resultSection').offset().top - 100
                        }, 500);
                    }, 300);
                } else {
                    showError(response.message || 'Lokasi tidak ditemukan');
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                isSubmitting = false;
                submitBtn.prop('disabled', false);
                btnText.text('Cari Lokasi BTS');

                let errorMessage = 'Terjadi kesalahan saat mencari lokasi BTS.';

                if (status === 'timeout') {
                    errorMessage = 'Request timeout. Koneksi terlalu lama. Silakan coba lagi.';
                } else if (xhr.status === 0) {
                    errorMessage = 'Tidak ada koneksi internet. Pastikan Anda terhubung ke internet.';
                } else if (xhr.status === 400) {
                    errorMessage = xhr.responseJSON?.message || 'Data yang Anda masukkan tidak valid.';
                } else if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        errorMessage = 'Validasi gagal:\n';
                        Object.keys(errors).forEach(key => {
                            errorMessage += `- ${errors[key][0]}\n`;
                        });
                    }
                } else if (xhr.status === 500) {
                    errorMessage = 'Terjadi kesalahan pada server. Silakan coba lagi nanti.';
                } else if (xhr.responseJSON?.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                showError(errorMessage);
                showAlert('error', '✕ Pencarian gagal');

                console.error('Error details:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    response: xhr.responseJSON,
                    error: error
                });
            }
        });
    }

    function displayResult(data, params) {
        try {
            if (!data.lat || !data.lon) {
                throw new Error('Koordinat tidak valid');
            }

            let accuracyWarning = '';
            let accuracyClass = '';
            let warningLevel = '';
            
            if (data.accuracy > 10000) {
                warningLevel = 'low';
                accuracyClass = 'low-accuracy';
                accuracyWarning = `
                    <div class="accuracy-warning low">
                        <h4><span style="font-size: 20px;">⚠️</span> Akurasi Sangat Rendah</h4>
                        <p>
                            Cell ID yang Anda masukkan kemungkinan <strong>tidak ditemukan</strong> di database. 
                            Hasil ini hanya <strong>estimasi area berdasarkan LAC</strong> dengan radius ketidakpastian 
                            <strong>${data.accuracy.toLocaleString()}m (~${(data.accuracy/1000).toFixed(1)}km)</strong>.
                            <br><br>
                            <strong>Saran:</strong> Periksa kembali Cell ID Anda atau coba dengan Cell ID lain.
                        </p>
                    </div>
                `;
            } else if (data.accuracy > 5000) {
                warningLevel = 'medium';
                accuracyClass = 'medium-accuracy';
                accuracyWarning = `
                    <div class="accuracy-warning medium">
                        <h4><span style="font-size: 20px;">⚠️</span> Akurasi Rendah</h4>
                        <p>
                            Akurasi lokasi cukup rendah (${data.accuracy.toLocaleString()}m). Hasil mungkin tidak tepat. 
                            Lokasi yang ditampilkan adalah estimasi dengan radius ketidakpastian ~${(data.accuracy/1000).toFixed(1)}km.
                        </p>
                    </div>
                `;
            } else if (data.accuracy > 1000) {
                warningLevel = 'good';
                accuracyClass = 'good-accuracy';
                accuracyWarning = `
                    <div class="accuracy-warning good">
                        <h4><span style="font-size: 20px;">ℹ️</span> Akurasi Moderate</h4>
                        <p>
                            Akurasi moderate (${data.accuracy.toLocaleString()}m). Lokasi cukup akurat dengan radius ~${(data.accuracy/1000).toFixed(1)}km.
                        </p>
                    </div>
                `;
            } else {
                warningLevel = 'high';
                accuracyClass = 'high-accuracy';
                accuracyWarning = `
                    <div class="accuracy-warning high">
                        <h4><span style="font-size: 20px;">✓</span> Akurasi Tinggi</h4>
                        <p>
                            Akurasi tinggi! Lokasi BTS sangat akurat dengan radius ${data.accuracy.toLocaleString()}m.
                        </p>
                    </div>
                `;
            }

            const resultInfo = `
                ${accuracyWarning}
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-item-label">📍 Latitude</div>
                        <div class="info-item-value">${parseFloat(data.lat).toFixed(6)}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-item-label">📍 Longitude</div>
                        <div class="info-item-value">${parseFloat(data.lon).toFixed(6)}</div>
                    </div>
                    <div class="info-item ${accuracyClass}">
                        <div class="info-item-label">🎯 Accuracy</div>
                        <div class="info-item-value">${data.accuracy.toLocaleString()} m</div>
                        <div class="subtitle">~${(data.accuracy/1000).toFixed(2)} km radius</div>
                    </div>
                    <div class="info-item">
                        <div class="info-item-label">📮 Address</div>
                        <div class="info-item-value" style="font-size: 14px; line-height: 1.5;">${data.address || 'N/A'}</div>
                    </div>
                </div>
            `;

            $('#resultInfo').html(resultInfo);

            if (marker) {
                map.removeLayer(marker);
            }
            if (circle) {
                map.removeLayer(circle);
            }

            marker = L.marker([data.lat, data.lon], {
                title: 'Lokasi BTS'
            }).addTo(map);

            const popupContent = `
                <div style="min-width: 220px; font-family: 'Sora', sans-serif;">
                    <div style="text-align: center; padding-bottom: 12px; border-bottom: 2px solid #e5e7eb; margin-bottom: 12px;">
                        <strong style="font-size: 16px; color: #1e293b;">📡 Lokasi BTS</strong>
                    </div>
                    <div style="font-size: 13px; line-height: 1.8;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="color: #64748b; font-weight: 600;">Radio:</span>
                            <span style="font-weight: 700; color: #4F7CFF;">${params.radio_type.toUpperCase()}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="color: #64748b; font-weight: 600;">MCC:</span>
                            <span style="font-weight: 700;">${params.mcc}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="color: #64748b; font-weight: 600;">MNC:</span>
                            <span style="font-weight: 700;">${params.mnc}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="color: #64748b; font-weight: 600;">LAC:</span>
                            <span style="font-weight: 700;">${params.lac}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                            <span style="color: #64748b; font-weight: 600;">CID:</span>
                            <span style="font-weight: 700;">${params.cid}</span>
                        </div>
                        <div style="padding-top: 12px; border-top: 2px solid #e5e7eb; text-align: center;">
                            <span style="color: #64748b; font-weight: 600;">Accuracy:</span>
                            <span style="font-weight: 700; color: ${warningLevel === 'low' ? '#ef4444' : warningLevel === 'medium' ? '#f59e0b' : warningLevel === 'good' ? '#3b82f6' : '#10b981'};">
                                ${data.accuracy.toLocaleString()}m
                            </span>
                            ${data.accuracy > 5000 ? '<br><span style="color: #ef4444; font-weight: 700; font-size: 11px;">⚠️ Low Accuracy</span>' : ''}
                        </div>
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent).openPopup();

            let circleColor = '#4F7CFF';
            if (data.accuracy > 10000) {
                circleColor = '#ef4444';
            } else if (data.accuracy > 5000) {
                circleColor = '#f59e0b';
            } else if (data.accuracy > 1000) {
                circleColor = '#3b82f6';
            } else {
                circleColor = '#10b981';
            }

            circle = L.circle([data.lat, data.lon], {
                radius: data.accuracy,
                color: circleColor,
                fillColor: circleColor,
                fillOpacity: 0.15,
                weight: 2
            }).addTo(map);

            const bounds = circle.getBounds();
            map.fitBounds(bounds, { 
                padding: [50, 50],
                maxZoom: 16
            });

            $('#resultSection').addClass('show');

            setTimeout(() => {
                map.invalidateSize();
            }, 400);

            if (data.accuracy > 10000) {
                showAlert('warning', '⚠️ Akurasi sangat rendah! Cell ID mungkin tidak valid.');
            } else if (data.accuracy > 5000) {
                showAlert('warning', '⚠️ Akurasi rendah. Hasil adalah estimasi area.');
            }

        } catch (error) {
            console.error('Display result error:', error);
            showError('Gagal menampilkan hasil: ' + error.message);
        }
    }
</script>
@endpush