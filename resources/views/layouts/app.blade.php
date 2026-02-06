<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BTS Tracker')</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Sora', sans-serif;
            background: #f5f7fa;
            color: #2c3e50;
        }

        .container { 
            display: flex; 
            min-height: 100vh; 
            position: relative;
        }

        /* Sidebar with Toggle */
        .sidebar {
            width: 240px;
            background: #1a1f36;
            color: white;
            position: fixed;
            height: 100vh;
            box-shadow: 4px 0 12px rgba(0,0,0,0.15);
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar.collapsed {
            transform: translateX(-240px);
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar-header h1 {
            font-size: 20px;
            font-weight: 700;
        }

        .menu-item {
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .menu-item:hover {
            background: #252b42;
            padding-left: 28px;
        }

        .menu-item.active {
            background: #4F7CFF;
            border-left: 4px solid white;
        }

        /* Toggle Button */
        .sidebar-toggle {
            position: fixed;
            left: 250px;
            top: 20px;
            width: 40px;
            height: 40px;
            background: #4F7CFF;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            z-index: 1001;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .sidebar-toggle:hover {
            background: #3A5FD8;
            transform: scale(1.1);
        }

        .sidebar.collapsed ~ .sidebar-toggle {
            left: 10px;
        }

        .main-content {
            flex: 1;
            margin-left: 240px;
            padding: 32px;
            transition: margin-left 0.3s ease;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: 0;
        }

        .page-header {
            background: linear-gradient(135deg, #4F7CFF, #3A5FD8);
            padding: 28px 32px;
            border-radius: 16px;
            color: white;
            margin-bottom: 28px;
        }

        .page-header h2 {
            font-size: 26px;
            margin-bottom: 8px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4F7CFF;
            box-shadow: 0 0 0 3px rgba(79, 124, 255, 0.1);
        }

        .form-hint {
            font-size: 12px;
            color: #6b7280;
            margin-top: 6px;
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #4F7CFF;
            color: white;
            width: 100%;
            justify-content: center;
        }

        .btn-primary:hover {
            background: #3A5FD8;
            transform: translateY(-2px);
        }

        .btn-primary:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        /* Fixed Alert Container */
        #alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 500px;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: none;
            animation: slideInRight 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .alert.show { 
            display: block; 
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }

        .loading-overlay.show {
            display: flex;
        }

        .loading-content {
            text-align: center;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid #4F7CFF;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .info-item {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 16px;
            border-radius: 10px;
            border-left: 4px solid #4F7CFF;
        }

        .info-item-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .info-item-value {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
            word-break: break-word;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #e1e8ed;
        }

        .table th {
            background: #f8fafc;
            font-weight: 600;
            font-size: 13px;
        }

        .table tr:hover {
            background: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-top: 4px solid #4F7CFF;
        }

        .stat-card h3 {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #4F7CFF;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-240px);
            }
            
            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 16px;
            }

            .sidebar-toggle {
                left: 10px;
            }

            #alert-container {
                top: 10px;
                right: 10px;
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h1>📡 BTS Tracker</h1>
            </div>
            <nav>
                <a href="{{ route('home') }}" class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
                    <span>🔍</span>
                    <span>Cell ID Lookup</span>
                </a>
                <a href="{{ route('triangulation') }}" class="menu-item {{ request()->routeIs('triangulation') ? 'active' : '' }}">
                    <span>🗺️</span>
                    <span>Triangulasi</span>
                </a>
                <a href="{{ route('sector.calculator') }}" class="menu-item {{ request()->routeIs('sector.calculator') ? 'active' : '' }}">
        <span>📡</span>
        <span>Sector Calculator</span>
    </a>
                <a href="{{ route('history') }}" class="menu-item {{ request()->routeIs('history') ? 'active' : '' }}">
                    <span>📋</span>
                    <span>History</span>
                </a>
                <a href="{{ route('analytics') }}" class="menu-item {{ request()->routeIs('analytics') ? 'active' : '' }}">
                    <span>📊</span>
                    <span>Analytics</span>
                </a>
            </nav>
        </aside>

        <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
            ☰
        </button>

        <main class="main-content">
            <!-- Fixed Alert Container -->
            <div id="alert-container"></div>

            @yield('content')
        </main>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <p style="color: #64748b; font-weight: 600;">Mencari lokasi BTS...</p>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        function showAlert(type, message) {
            const alert = $(`<div class="alert alert-${type} show">${message}</div>`);
            $('#alert-container').append(alert);
            
            setTimeout(() => {
                alert.removeClass('show');
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        }

        function showLoading() {
            $('#loadingOverlay').addClass('show');
        }

        function hideLoading() {
            $('#loadingOverlay').removeClass('show');
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            
            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
                sidebar.classList.add('open');
                toggle.innerHTML = '✕';
            } else {
                sidebar.classList.add('collapsed');
                sidebar.classList.remove('open');
                toggle.innerHTML = '☰';
            }
        }

        // Auto-hide sidebar on mobile
        if (window.innerWidth <= 768) {
            document.getElementById('sidebar').classList.add('collapsed');
        }
    </script>

    @stack('scripts')
</body>
</html>