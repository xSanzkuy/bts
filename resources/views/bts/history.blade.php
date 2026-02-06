@extends('layouts.app')

@section('title', 'History - BTS Tracker')

@section('content')
<div class="page-header">
    <h2>📋 History</h2>
    <p>Riwayat penggunaan semua fitur BTS Tracker</p>
</div>

<div id="alert-container"></div>

<!-- Filter Tabs -->
<div class="card" style="padding: 16px 24px; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <button onclick="filterHistory('all')" id="filter-all" class="filter-btn active">
                <span>🎯</span> All ({{ $searches->total() + 0 }})
            </button>
            <button onclick="filterHistory('cellid')" id="filter-cellid" class="filter-btn">
                <span>🔍</span> Cell ID (<span id="count-cellid">{{ $searches->total() }}</span>)
            </button>
            <button onclick="filterHistory('triangulation')" id="filter-triangulation" class="filter-btn">
                <span>🗺️</span> Triangulation (<span id="count-triangulation">0</span>)
            </button>
            <button onclick="filterHistory('sector')" id="filter-sector" class="filter-btn">
                <span>📡</span> Sector (<span id="count-sector">0</span>)
            </button>
        </div>
        
        <button onclick="clearAllHistory()" class="btn btn-danger" style="padding: 10px 20px; width: auto; font-size: 13px;">
            <span>🗑️</span> Clear All
        </button>
    </div>
</div>

<!-- History List -->
<div class="card">
    <div id="historyContainer">
        <div style="text-align: center; padding: 40px;">
            <div class="spinner" style="margin: 0 auto 16px;"></div>
            <p style="color: #64748b;">Loading history...</p>
        </div>
    </div>
</div>

<!-- Pagination (for Cell ID from database) -->
@if($searches->count() > 0)
<div style="margin-top: 20px;">
    {{ $searches->links() }}
</div>
@endif
@endsection

@push('scripts')
<style>
    .filter-btn {
        padding: 10px 16px;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .filter-btn:hover {
        border-color: #4F7CFF;
        color: #4F7CFF;
        transform: translateY(-2px);
    }

    .filter-btn.active {
        background: linear-gradient(135deg, #4F7CFF, #3A5FD8);
        color: white;
        border-color: #4F7CFF;
    }

    .history-item {
        padding: 20px;
        border-left: 4px solid;
        border-radius: 8px;
        margin-bottom: 16px;
        background: linear-gradient(135deg, var(--bg-start), var(--bg-end));
        transition: all 0.3s;
        position: relative;
    }

    .history-item:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .history-item.cellid {
        --bg-start: #eff6ff;
        --bg-end: #dbeafe;
        border-color: #3b82f6;
    }

    .history-item.triangulation {
        --bg-start: #faf5ff;
        --bg-end: #f3e8ff;
        border-color: #8b5cf6;
    }

    .history-item.sector {
        --bg-start: #fdf2f8;
        --bg-end: #fce7f3;
        border-color: #ec4899;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f4f6;
        border-top: 4px solid #4F7CFF;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<script>
    let currentFilter = 'all';
    let allHistory = [];

    $(document).ready(function() {
        loadAllHistory();
    });

    function loadAllHistory() {
        allHistory = [];

        // Load Cell ID from database (server-side)
        @if($searches->count() > 0)
            @foreach($searches as $search)
                allHistory.push({
                    type: 'cellid',
                    id: '{{ $search->id }}',
                    icon: '🔍',
                    title: 'Cell ID Lookup',
                    data: {
                        radio: '{{ strtoupper($search->radio_type) }}',
                        mcc: '{{ $search->mcc }}',
                        mnc: '{{ $search->mnc }}',
                        lac: '{{ $search->lac }}',
                        cid: '{{ $search->cid }}',
                        operator: '{{ $search->operator_name }}',
                        address: '{{ Str::limit($search->address ?? "N/A", 60) }}',
                        status: '{{ $search->status }}'
                    },
                    timestamp: '{{ $search->created_at->format("Y-m-d H:i:s") }}',
                    timeAgo: '{{ $search->created_at->diffForHumans() }}'
                });
            @endforeach
        @endif

        // Load Triangulation from localStorage
        const triangulationHistory = JSON.parse(localStorage.getItem('triangulation_history') || '[]');
        triangulationHistory.forEach((item, index) => {
            allHistory.push({
                type: 'triangulation',
                id: 'tri-' + index,
                icon: '🗺️',
                title: 'Triangulation',
                data: {
                    tower1: `Lat: ${item.lat1.toFixed(6)}, Lng: ${item.lng1.toFixed(6)}, R: ${item.r1}m`,
                    tower2: `Lat: ${item.lat2.toFixed(6)}, Lng: ${item.lng2.toFixed(6)}, R: ${item.r2}m`,
                    tower3: `Lat: ${item.lat3.toFixed(6)}, Lng: ${item.lng3.toFixed(6)}, R: ${item.r3}m`,
                    result: `📍 Target: ${item.resultLat.toFixed(6)}, ${item.resultLng.toFixed(6)}`,
                    avgRadius: Math.round((item.r1 + item.r2 + item.r3) / 3)
                },
                timestamp: item.timestamp,
                timeAgo: formatTimestamp(item.timestamp)
            });
        });

        // Load Sector Calculator from localStorage
        const sectorHistory = JSON.parse(localStorage.getItem('sector_history') || '[]');
        sectorHistory.forEach((item, index) => {
            allHistory.push({
                type: 'sector',
                id: 'sec-' + index,
                icon: '📡',
                title: 'Sector Calculator',
                data: {
                    tower: `Tower: ${item.towerLat.toFixed(6)}, ${item.towerLon.toFixed(6)}`,
                    cell: `Cell: ${item.cellLat.toFixed(6)}, ${item.cellLon.toFixed(6)}`,
                    cid: item.cid,
                    eNodeBId: item.eNodeBId,
                    sectorId: item.sectorId,
                    sectorNumber: item.sectorNumber,
                    bearing: item.bearing.toFixed(2) + '°',
                    distance: (item.distance / 1000).toFixed(2) + ' km'
                },
                timestamp: item.timestamp,
                timeAgo: formatTimestamp(item.timestamp)
            });
        });

        // Sort by timestamp (newest first)
        allHistory.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));

        // Update counts
        updateCounts();

        // Display filtered history
        displayHistory();
    }

    function updateCounts() {
        const cellidCount = allHistory.filter(h => h.type === 'cellid').length;
        const triangulationCount = allHistory.filter(h => h.type === 'triangulation').length;
        const sectorCount = allHistory.filter(h => h.type === 'sector').length;

        $('#count-cellid').text(cellidCount);
        $('#count-triangulation').text(triangulationCount);
        $('#count-sector').text(sectorCount);
        $('#filter-all').html(`<span>🎯</span> All (${allHistory.length})`);
    }

    function filterHistory(type) {
        currentFilter = type;
        
        // Update active button
        $('.filter-btn').removeClass('active');
        $('#filter-' + type).addClass('active');
        
        // Display filtered history
        displayHistory();
    }

    function displayHistory() {
        const filtered = currentFilter === 'all' 
            ? allHistory 
            : allHistory.filter(h => h.type === currentFilter);

        if (filtered.length === 0) {
            $('#historyContainer').html(`
                <div style="text-align: center; padding: 60px 20px; color: #9ca3af;">
                    <div style="font-size: 64px; margin-bottom: 16px;">📭</div>
                    <h3 style="font-size: 18px; margin-bottom: 8px; color: #64748b;">Belum Ada History</h3>
                    <p style="color: #9ca3af;">Gunakan fitur-fitur aplikasi untuk melihat history</p>
                </div>
            `);
            return;
        }

        let html = '';
        filtered.forEach(item => {
            html += renderHistoryItem(item);
        });

        $('#historyContainer').html(html);
    }

    function renderHistoryItem(item) {
        let detailsHtml = '';
        
        if (item.type === 'cellid') {
            const statusBadge = item.data.status === 'ok'
                ? '<span class="badge badge-success">SUCCESS</span>'
                : '<span class="badge badge-error">FAILED</span>';
            
            detailsHtml = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 12px;">
                    <div>
                        <span style="font-size: 11px; color: #64748b; font-weight: 600;">RADIO TYPE</span>
                        <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-top: 4px;">${item.data.radio}</div>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: #64748b; font-weight: 600;">OPERATOR</span>
                        <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-top: 4px;">${item.data.operator}</div>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: #64748b; font-weight: 600;">CELL ID</span>
                        <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-top: 4px;">${item.data.mcc}-${item.data.mnc}-${item.data.lac}-${item.data.cid}</div>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: #64748b; font-weight: 600;">STATUS</span>
                        <div style="margin-top: 4px;">${statusBadge}</div>
                    </div>
                </div>
                <div style="font-size: 13px; color: #475569; display: flex; align-items: center; gap: 6px;">
                    <span>📍</span> ${item.data.address}
                </div>
            `;
        } else if (item.type === 'triangulation') {
            detailsHtml = `
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <span style="font-size: 11px; color: #64748b; font-weight: 600;">🔴 TOWER 1</span>
                        <div style="font-size: 12px; color: #475569; margin-top: 4px;">${item.data.tower1}</div>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: #64748b; font-weight: 600;">🔵 TOWER 2</span>
                        <div style="font-size: 12px; color: #475569; margin-top: 4px;">${item.data.tower2}</div>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: #64748b; font-weight: 600;">🟢 TOWER 3</span>
                        <div style="font-size: 12px; color: #475569; margin-top: 4px;">${item.data.tower3}</div>
                    </div>
                </div>
                <div style="background: rgba(139, 92, 246, 0.1); padding: 12px; border-radius: 6px;">
                    <div style="font-size: 13px; font-weight: 700; color: #6b21a8; margin-bottom: 4px;">${item.data.result}</div>
                    <div style="font-size: 12px; color: #7c3aed;">Avg Coverage: ${item.data.avgRadius.toLocaleString()}m (~${(item.data.avgRadius/1000).toFixed(1)}km)</div>
                </div>
            `;
        } else if (item.type === 'sector') {
            detailsHtml = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 12px;">
                    <div>
                        <span style="font-size: 11px; color: #64748b; font-weight: 600;">CID</span>
                        <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-top: 4px;">${item.data.cid}</div>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: #64748b; font-weight: 600;">eNodeB ID</span>
                        <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-top: 4px;">${item.data.eNodeBId}</div>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: #64748b; font-weight: 600;">SECTOR</span>
                        <div style="font-size: 14px; font-weight: 700; color: #ec4899; margin-top: 4px;">Sector ${item.data.sectorNumber}</div>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: #64748b; font-weight: 600;">BEARING</span>
                        <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-top: 4px;">${item.data.bearing}</div>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: #64748b; font-weight: 600;">DISTANCE</span>
                        <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-top: 4px;">${item.data.distance}</div>
                    </div>
                </div>
                <div style="font-size: 12px; color: #64748b;">
                    <div>🗼 ${item.data.tower}</div>
                    <div style="margin-top: 4px;">📍 ${item.data.cell}</div>
                </div>
            `;
        }

        return `
            <div class="history-item ${item.type}">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 24px;">${item.icon}</span>
                        <div>
                            <div style="font-size: 16px; font-weight: 700; color: #1e293b;">${item.title}</div>
                            <div style="font-size: 12px; color: #64748b; font-weight: 600; margin-top: 2px;">${item.timeAgo}</div>
                        </div>
                    </div>
                    <button onclick="deleteHistoryItem('${item.type}', '${item.id}')" 
                            class="btn btn-danger" 
                            style="padding: 6px 12px; font-size: 12px; width: auto;">
                        🗑️
                    </button>
                </div>
                ${detailsHtml}
            </div>
        `;
    }

    function deleteHistoryItem(type, id) {
        if (!confirm('Hapus record ini?')) return;

        if (type === 'cellid') {
            // Delete from database
            $.ajax({
                url: `/api/search/${id}`,
                method: 'DELETE',
                success: function(response) {
                    showAlert('success', response.message);
                    setTimeout(() => window.location.reload(), 1000);
                },
                error: function() {
                    showAlert('error', 'Gagal menghapus record');
                }
            });
        } else if (type === 'triangulation') {
            // Delete from localStorage
            let history = JSON.parse(localStorage.getItem('triangulation_history') || '[]');
            const index = parseInt(id.replace('tri-', ''));
            history.splice(index, 1);
            localStorage.setItem('triangulation_history', JSON.stringify(history));
            showAlert('success', 'Record berhasil dihapus');
            loadAllHistory();
        } else if (type === 'sector') {
            // Delete from localStorage
            let history = JSON.parse(localStorage.getItem('sector_history') || '[]');
            const index = parseInt(id.replace('sec-', ''));
            history.splice(index, 1);
            localStorage.setItem('sector_history', JSON.stringify(history));
            showAlert('success', 'Record berhasil dihapus');
            loadAllHistory();
        }
    }

    function clearAllHistory() {
        if (!confirm('Yakin ingin menghapus SEMUA history dari semua fitur?')) return;

        // Clear database
        $.ajax({
            url: '{{ route("api.history.clear") }}',
            method: 'DELETE',
            success: function(response) {
                // Clear localStorage
                localStorage.removeItem('triangulation_history');
                localStorage.removeItem('sector_history');
                
                showAlert('success', 'Semua history berhasil dihapus!');
                setTimeout(() => window.location.reload(), 1000);
            },
            error: function() {
                showAlert('error', 'Gagal menghapus history');
            }
        });
    }

    function formatTimestamp(timestamp) {
        if (!timestamp) return 'Unknown';
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'Just now';
        if (minutes < 60) return `${minutes} min ago`;
        if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        return `${days} day${days > 1 ? 's' : ''} ago`;
    }
</script>
@endpush