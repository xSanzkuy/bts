@extends('layouts.app')

@section('title', 'Analytics - BTS Tracker')

@section('content')
<div class="page-header">
    <h2>📊 Analytics Dashboard</h2>
    <p>Statistik penggunaan aplikasi BTS Tracker</p>
</div>

<!-- Overview Stats -->
<div class="stats-grid" style="margin-bottom: 28px;">
    <div class="stat-card" style="border-top-color: #4F7CFF;">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
            <span style="font-size: 32px;">🔍</span>
            <span style="font-size: 12px; background: #eff6ff; color: #1e40af; padding: 4px 8px; border-radius: 12px; font-weight: 700;">Database</span>
        </div>
        <h3 style="font-size: 13px; color: #64748b; margin-bottom: 8px; font-weight: 600;">Cell ID Searches</h3>
        <div class="stat-value" style="color: #4F7CFF;">{{ $stats['total'] }}</div>
        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
            {{ $stats['successful'] }} berhasil ({{ $stats['success_rate'] }}%)
        </div>
    </div>

    <div class="stat-card" style="border-top-color: #8b5cf6;">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
            <span style="font-size: 32px;">🗺️</span>
            <span style="font-size: 12px; background: #faf5ff; color: #6b21a8; padding: 4px 8px; border-radius: 12px; font-weight: 700;">LocalStorage</span>
        </div>
        <h3 style="font-size: 13px; color: #64748b; margin-bottom: 8px; font-weight: 600;">Triangulations</h3>
        <div class="stat-value" style="color: #8b5cf6;" id="triangulationCount">-</div>
        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
            Saved calculations
        </div>
    </div>

    <div class="stat-card" style="border-top-color: #ec4899;">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
            <span style="font-size: 32px;">📡</span>
            <span style="font-size: 12px; background: #fdf2f8; color: #9f1239; padding: 4px 8px; border-radius: 12px; font-weight: 700;">LocalStorage</span>
        </div>
        <h3 style="font-size: 13px; color: #64748b; margin-bottom: 8px; font-weight: 600;">Sector Calculations</h3>
        <div class="stat-value" style="color: #ec4899;" id="sectorCount">-</div>
        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
            Saved calculations
        </div>
    </div>

    <div class="stat-card" style="border-top-color: #10b981;">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
            <span style="font-size: 32px;">🎯</span>
            <span style="font-size: 12px; background: #f0fdf4; color: #065f46; padding: 4px 8px; border-radius: 12px; font-weight: 700;">Combined</span>
        </div>
        <h3 style="font-size: 13px; color: #64748b; margin-bottom: 8px; font-weight: 600;">Total Operations</h3>
        <div class="stat-value" style="color: #10b981;" id="totalOperations">-</div>
        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
            All features combined
        </div>
    </div>
</div>

<!-- Cell ID Lookup Stats -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
            <span>🔍</span> Cell ID Lookup Statistics
        </h3>
        <span style="font-size: 12px; background: #eff6ff; color: #1e40af; padding: 6px 12px; border-radius: 12px; font-weight: 700;">Database Records</span>
    </div>
    
    @if($operatorStats->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Operator</th>
                    <th>MNC</th>
                    <th>Total Searches</th>
                    <th>Percentage</th>
                    <th style="text-align: center;">Chart</th>
                </tr>
            </thead>
            <tbody>
                @foreach($operatorStats as $stat)
                @php
                    $operators = [
                        '10' => 'Telkomsel',
                        '11' => 'XL Axiata',
                        '01' => 'Indosat Ooredoo',
                        '89' => 'Tri (3)',
                        '27' => 'Smartfren',
                    ];
                    $operatorName = $operators[$stat->mnc] ?? "MNC {$stat->mnc}";
                    $percentage = $stats['total'] > 0 ? round(($stat->count / $stats['total']) * 100, 1) : 0;
                @endphp
                <tr>
                    <td><strong>{{ $operatorName }}</strong></td>
                    <td><span style="background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600;">{{ $stat->mnc }}</span></td>
                    <td><strong>{{ $stat->count }}</strong></td>
                    <td>
                        <span style="background: #eff6ff; color: #1e40af; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">
                            {{ $percentage }}%
                        </span>
                    </td>
                    <td>
                        <div style="background: #e5e7eb; border-radius: 4px; height: 8px; position: relative; overflow: hidden;">
                            <div style="background: linear-gradient(90deg, #4F7CFF, #3A5FD8); height: 100%; width: {{ $percentage }}%; transition: width 0.3s;"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 60px 20px; color: #9ca3af;">
            <div style="font-size: 64px; margin-bottom: 16px;">📊</div>
            <h3 style="font-size: 18px; margin-bottom: 8px; color: #64748b;">Belum Ada Data</h3>
            <p style="color: #9ca3af;">Lakukan pencarian Cell ID untuk melihat statistik</p>
        </div>
    @endif
</div>

<!-- Recent Activity -->
<div class="card" style="margin-top: 24px;">
    <h3 style="margin-bottom: 20px; font-size: 18px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
        <span>🕒</span> Recent Activity
    </h3>
    
    <div id="recentActivityList">
        <div style="text-align: center; padding: 40px; color: #9ca3af;">
            <div class="spinner" style="margin: 0 auto 16px;"></div>
            <p>Loading recent activity...</p>
        </div>
    </div>
</div>

<!-- Quick Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 24px;">
    <div class="card" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border-left: 4px solid #3b82f6;">
        <h4 style="font-size: 14px; font-weight: 700; color: #1e40af; margin-bottom: 12px;">📱 Unique Cells Tracked</h4>
        <div style="font-size: 32px; font-weight: 700; color: #1e40af; margin-bottom: 8px;">{{ $uniqueCells }}</div>
        <p style="font-size: 12px; color: #1e3a8a; margin: 0;">Different Cell IDs in database</p>
    </div>

    <div class="card" style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-left: 4px solid #10b981;">
        <h4 style="font-size: 14px; font-weight: 700; color: #065f46; margin-bottom: 12px;">✅ Success Rate</h4>
        <div style="font-size: 32px; font-weight: 700; color: #065f46; margin-bottom: 8px;">{{ $stats['success_rate'] }}%</div>
        <p style="font-size: 12px; color: #064e3b; margin: 0;">{{ $stats['successful'] }} out of {{ $stats['total'] }} searches</p>
    </div>

    <div class="card" style="background: linear-gradient(135deg, #fef3c7, #fde68a); border-left: 4px solid #f59e0b;">
        <h4 style="font-size: 14px; font-weight: 700; color: #92400e; margin-bottom: 12px;">💾 Storage Usage</h4>
        <div style="font-size: 32px; font-weight: 700; color: #92400e; margin-bottom: 8px;" id="storageSize">-</div>
        <p style="font-size: 12px; color: #78350f; margin: 0;">LocalStorage data size</p>
    </div>
</div>
@endsection

@push('scripts')
<style>
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

    .activity-item {
        padding: 16px;
        border-left: 4px solid;
        border-radius: 8px;
        margin-bottom: 12px;
        background: linear-gradient(135deg, var(--bg-start), var(--bg-end));
        transition: all 0.3s;
    }

    .activity-item:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .activity-item.cellid {
        --bg-start: #eff6ff;
        --bg-end: #dbeafe;
        border-color: #3b82f6;
    }

    .activity-item.triangulation {
        --bg-start: #faf5ff;
        --bg-end: #f3e8ff;
        border-color: #8b5cf6;
    }

    .activity-item.sector {
        --bg-start: #fdf2f8;
        --bg-end: #fce7f3;
        border-color: #ec4899;
    }
</style>

<script>
    $(document).ready(function() {
        loadLocalStorageStats();
        loadRecentActivity();
    });

    function loadLocalStorageStats() {
        // Count triangulation history
        const triangulationHistory = JSON.parse(localStorage.getItem('triangulation_history') || '[]');
        $('#triangulationCount').text(triangulationHistory.length);

        // Count sector calculator history
        const sectorHistory = JSON.parse(localStorage.getItem('sector_history') || '[]');
        $('#sectorCount').text(sectorHistory.length);

        // Total operations (DB + localStorage)
        const totalDB = {{ $stats['total'] }};
        const totalOperations = totalDB + triangulationHistory.length + sectorHistory.length;
        $('#totalOperations').text(totalOperations.toLocaleString());

        // Calculate storage size
        let totalSize = 0;
        for (let key in localStorage) {
            if (localStorage.hasOwnProperty(key)) {
                totalSize += localStorage[key].length + key.length;
            }
        }
        const sizeKB = (totalSize / 1024).toFixed(2);
        $('#storageSize').text(sizeKB + ' KB');
    }

    function loadRecentActivity() {
        const activities = [];

        // Get Cell ID searches from server
        @if($recentSearches->count() > 0)
            @foreach($recentSearches as $search)
                activities.push({
                    type: 'cellid',
                    icon: '🔍',
                    title: 'Cell ID Lookup',
                    description: 'MCC: {{ $search->mcc }}, MNC: {{ $search->mnc }}, LAC: {{ $search->lac }}, CID: {{ $search->cid }}',
                    location: '{{ Str::limit($search->address ?? "N/A", 50) }}',
                    status: '{{ $search->status }}',
                    timestamp: '{{ $search->created_at->diffForHumans() }}'
                });
            @endforeach
        @endif

        // Get triangulation from localStorage
        const triangulationHistory = JSON.parse(localStorage.getItem('triangulation_history') || '[]');
        triangulationHistory.slice(0, 5).forEach(item => {
            activities.push({
                type: 'triangulation',
                icon: '🗺️',
                title: 'Triangulation',
                description: `3 Towers → Target: ${item.resultLat.toFixed(4)}, ${item.resultLng.toFixed(4)}`,
                location: `Avg Radius: ${Math.round((item.r1 + item.r2 + item.r3) / 3)}m`,
                status: 'ok',
                timestamp: formatTimestamp(item.timestamp)
            });
        });

        // Get sector calculations from localStorage
        const sectorHistory = JSON.parse(localStorage.getItem('sector_history') || '[]');
        sectorHistory.slice(0, 5).forEach(item => {
            activities.push({
                type: 'sector',
                icon: '📡',
                title: 'Sector Calculator',
                description: `CID: ${item.cid} → eNodeB: ${item.eNodeBId}, Sector: ${item.sectorNumber}`,
                location: `Bearing: ${item.bearing.toFixed(1)}°, Distance: ${(item.distance/1000).toFixed(2)}km`,
                status: 'ok',
                timestamp: formatTimestamp(item.timestamp)
            });
        });

        // Sort by timestamp (newest first)
        activities.sort((a, b) => {
            const timeA = a.timestamp.includes('ago') ? parseRelativeTime(a.timestamp) : new Date(a.timestamp);
            const timeB = b.timestamp.includes('ago') ? parseRelativeTime(b.timestamp) : new Date(b.timestamp);
            return timeB - timeA;
        });

        // Display activities
        displayActivities(activities.slice(0, 10));
    }

    function displayActivities(activities) {
        if (activities.length === 0) {
            $('#recentActivityList').html(`
                <div style="text-align: center; padding: 60px 20px; color: #9ca3af;">
                    <div style="font-size: 64px; margin-bottom: 16px;">📭</div>
                    <h3 style="font-size: 18px; margin-bottom: 8px; color: #64748b;">Belum Ada Activity</h3>
                    <p style="color: #9ca3af;">Gunakan fitur-fitur aplikasi untuk melihat activity</p>
                </div>
            `);
            return;
        }

        let html = '';
        activities.forEach(activity => {
            const statusBadge = activity.status === 'ok' 
                ? '<span style="background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700;">SUCCESS</span>'
                : '<span style="background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700;">FAILED</span>';

            html += `
                <div class="activity-item ${activity.type}">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 20px;">${activity.icon}</span>
                            <strong style="font-size: 14px; color: #1e293b;">${activity.title}</strong>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            ${statusBadge}
                            <span style="font-size: 11px; color: #64748b; font-weight: 600;">${activity.timestamp}</span>
                        </div>
                    </div>
                    <div style="font-size: 13px; color: #475569; margin-bottom: 4px;">${activity.description}</div>
                    <div style="font-size: 12px; color: #64748b;">📍 ${activity.location}</div>
                </div>
            `;
        });

        $('#recentActivityList').html(html);
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

    function parseRelativeTime(timeStr) {
        // Simple parser for "X min ago", "X hours ago", etc.
        const now = new Date();
        if (timeStr.includes('min')) {
            const mins = parseInt(timeStr);
            return new Date(now - mins * 60000);
        }
        if (timeStr.includes('hour')) {
            const hours = parseInt(timeStr);
            return new Date(now - hours * 3600000);
        }
        if (timeStr.includes('day')) {
            const days = parseInt(timeStr);
            return new Date(now - days * 86400000);
        }
        return now;
    }
</script>
@endpush