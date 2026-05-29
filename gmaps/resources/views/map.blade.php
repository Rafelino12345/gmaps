<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Peta Navigasi Real-Time & Satelit Google</title>

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    {{-- Leaflet Routing Machine CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --accent: #4f8ef7;
            --accent-hover: #3b74d6;
            --accent2: #f74f7b;
            --text: #ffffff;
            --muted: #a0a6bd;
            --success: #4fcf8e;
            --glass-bg: rgba(20, 22, 29, 0.85);
            --glass-border: rgba(255, 255, 255, 0.08);
            --radius: 20px;
        }

        body {
            font-family: 'Space Grotesk', sans-serif;
            background: #000;
            color: var(--text);
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            position: relative;
        }

        /* ── MAP CONTAINER ── */
        #map {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        /* ── FLOATING HEADER ── */
        header {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            z-index: 1000;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }

        .logo { display: flex; align-items: center; gap: 10px; }
        .logo-icon {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 10px; display: flex; align-items: center;
            justify-content: center; font-size: 16px;
        }
        .logo h1 { font-size: 16px; font-weight: 700; }
        .logo span { color: var(--accent); }

        /* ── DROPDOWN MENU KANAN ── */
        .dropdown-content {
            position: absolute; top: calc(100% + 12px); right: 0;
            background: var(--glass-bg); backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border); border-radius: 14px;
            min-width: 230px; overflow: hidden;
            opacity: 0; visibility: hidden; transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
        }
        .dropdown-content.show {
            opacity: 1; visibility: visible; transform: translateY(0);
        }
        .dropdown-item {
            padding: 14px 18px; font-size: 13px; font-weight: 600; color: var(--text);
            cursor: pointer; transition: background 0.2s; 
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex; align-items: center; gap: 10px;
        }
        .dropdown-item:last-child { border-bottom: none; }
        .dropdown-item:hover { background: rgba(255,255,255,0.1); color: var(--accent); }
        .dropdown-item.danger:hover { color: var(--accent2); background: rgba(247, 79, 123, 0.1); }

        /* ── FLOATING SIDEBAR ── */
        .sidebar {
            position: absolute;
            top: 20px;
            left: 20px;
            bottom: 20px;
            width: 360px;
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 15px 40px rgba(0,0,0,0.5);
        }

        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }

        .sidebar-section { padding: 20px; border-bottom: 1px solid var(--glass-border); }

        .section-label {
            font-size: 11px; font-weight: 600; letter-spacing: 1.2px;
            text-transform: uppercase; color: var(--muted); margin-bottom: 14px;
        }

        /* Mode Selector */
        .mode-options { display: flex; gap: 10px; }
        .mode-card {
            flex: 1; background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;
            padding: 12px 6px; text-align: center; cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--muted); font-size: 12px; font-weight: 600;
        }
        .mode-card:hover { background: rgba(255,255,255,0.08); transform: translateY(-2px); }
        .mode-card.active {
            background: rgba(79, 142, 247, 0.15); border-color: var(--accent);
            color: #fff; box-shadow: 0 4px 15px rgba(79, 142, 247, 0.2);
        }
        .mode-icon { font-size: 24px; margin-bottom: 6px; display: block; }

        /* Inputs */
        .location-inputs { display: flex; flex-direction: column; gap: 12px; }
        .input-group { position: relative; }
        .input-dot {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            width: 10px; height: 10px; border-radius: 50%; z-index: 1;
        }
        .input-dot.origin { background: var(--accent); }
        .input-dot.dest { background: var(--accent2); }

        .loc-input {
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px; padding: 14px 14px 14px 38px; color: var(--text);
            font-family: 'Space Grotesk', sans-serif; font-size: 13px; outline: none;
        }
        .connector-line {
            position: absolute; left: 18px; top: 40px;
            width: 2px; height: 20px; background: rgba(255,255,255,0.1);
        }

        /* Buttons */
        .btn {
            width: 100%; padding: 14px; border-radius: 12px;
            font-family: 'Space Grotesk', sans-serif; font-size: 13px; font-weight: 600;
            cursor: pointer; transition: all 0.2s; border: none;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-primary { background: var(--accent); color: #fff; box-shadow: 0 4px 15px rgba(79, 142, 247, 0.3); }
        .btn-primary:hover:not(:disabled) { background: var(--accent-hover); transform: translateY(-2px); }
        .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; background: rgba(255,255,255,0.1); color: var(--muted); }
        .btn-ghost { background: rgba(255,255,255,0.05); color: var(--text); border: 1px solid rgba(255,255,255,0.05); }
        .btn-ghost:hover { background: rgba(255,255,255,0.1); }
        .btn-danger { background: rgba(247, 79, 123, 0.2); color: var(--accent2); }
        .btn-danger:hover { background: rgba(247, 79, 123, 0.3); }
        .btn-group { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .btn-group .full-width { grid-column: span 2; }

        /* Stats */
        .route-card { display: none; flex-direction: column; }
        .route-card.visible { display: flex; }
        .route-stat { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px dashed rgba(255,255,255,0.1); }
        .route-stat:last-child { border-bottom: none; }
        .route-stat-label { font-size: 13px; color: var(--muted); }
        .route-stat-val { font-size: 16px; font-weight: 700; }
        .route-stat-val.accent { color: var(--accent); }
        .route-stat-val.green { color: var(--success); }

        .empty-state {
            padding: 40px 20px; text-align: center; color: var(--muted);
            display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;
        }
        .empty-icon { font-size: 48px; margin-bottom: 16px; }

        /* Loading */
        #loading {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0, 0.6); backdrop-filter: blur(4px); z-index: 2000;
            align-items: center; justify-content: center;
        }
        #loading.active { display: flex; }
        .loading-box {
            background: var(--glass-bg); border: 1px solid var(--glass-border);
            border-radius: var(--radius); padding: 30px 40px; text-align: center;
        }
        .spinner {
            width: 40px; height: 40px; border: 3px solid rgba(255,255,255,0.1);
            border-top-color: var(--accent); border-radius: 50%;
            animation: spin 0.8s linear infinite; margin: 0 auto 16px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Marker CSS */
        .sim-marker { transition: transform 0.25s linear; will-change: transform; z-index: 1000 !important; }
        
        /* CSS Animasi Halus (Smooth) untuk GPS */
        .live-gps-marker {
            transition: transform 1s linear; 
            will-change: transform;
            z-index: 1000 !important;
        }

        .pulse-dot {
            width: 18px; height: 18px; background: #4f8ef7; border: 3px solid #fff;
            border-radius: 50%; box-shadow: 0 0 14px #4f8ef7; animation: pulseGps 2s infinite;
        }
        @keyframes pulseGps {
            0% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(79, 142, 247, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 12px rgba(79, 142, 247, 0); }
            100% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(79, 142, 247, 0); }
        }

        /* Toast */
        #toast { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 3000; display: flex; flex-direction: column; gap: 10px; }
        .toast-item { padding: 12px 24px; border-radius: 30px; font-size: 13px; font-weight: 600; background: rgba(20, 22, 29, 0.95); border: 1px solid var(--glass-border); box-shadow: 0 10px 25px rgba(0,0,0,0.5); text-align: center; color: #fff; }
        .toast-item.info { color: var(--accent); }
        .toast-item.success { color: var(--success); }
        .toast-item.error { color: var(--accent2); }

        .map-hint {
            position: absolute; top: 20px; left: 50%; transform: translateX(-50%);
            z-index: 500; background: rgba(0, 0, 0, 0.7); border: 1px solid var(--glass-border);
            padding: 12px 24px; border-radius: 30px; font-size: 14px; font-weight: 600; color: var(--text);
        }
        .hint-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 8px; background: var(--accent); }
        .hint-dot.red { background: var(--accent2); }
    </style>
</head>
<body>

<div id="loading">
    <div class="loading-box">
        <div class="spinner"></div>
        <div id="loading-text" style="color:var(--muted)">Memproses...</div>
    </div>
</div>

<div id="toast"></div>
<div id="map"></div>

<div class="map-hint" id="map-hint">
    <span class="hint-dot" id="hint-dot"></span>
    <span id="hint-text">Klik peta untuk menentukan Titik Awal</span>
</div>

{{-- HEADER & MENU KANAN --}}
<header>
    <div class="logo">
        <div class="logo-icon">🗺️</div>
        <h1>Peta<span>Satelit</span></h1>
    </div>
    <div style="margin-left: auto; position: relative;">
        <button id="menu-toggle-btn" class="btn btn-ghost" style="padding: 8px 16px; font-size:12px;">
            <span style="font-size: 14px;">⚙️</span> Opsi Peta
        </button>
        
        {{-- Dropdown Menu Kanan --}}
        <div id="right-menu" class="dropdown-content">
            <div class="dropdown-item" id="menu-use-gps">
                <span style="font-size: 16px;">📍</span> Gunakan Lokasi Saat Ini
            </div>
            <div class="dropdown-item" id="menu-track">
                <span style="font-size: 16px;">📡</span> Lacak Pergerakan Saya
            </div>
        </div>
    </div>
</header>

<aside class="sidebar">
    <div class="sidebar-section">
        <div class="section-label">Lokasi Perjalanan</div>
        <div class="location-inputs">
            <div class="input-group">
                <div class="input-dot origin"></div>
                <input class="loc-input" id="origin-input" placeholder="Titik Awal (klik peta)" readonly>
            </div>
            <div class="connector-line"></div>
            <div class="input-group">
                <div class="input-dot dest"></div>
                <input class="loc-input" id="dest-input" placeholder="Titik Tujuan (klik peta)" readonly>
            </div>
        </div>
    </div>

    <div class="sidebar-section" id="controls-section">
        <div class="btn-group">
            <button class="btn btn-primary full-width" id="btn-start-process" disabled>🚀 Mulai Perjalanan</button>
            <button class="btn btn-ghost" id="btn-use-gps-sidebar">📍 Gunakan GPS Saya</button>
            <button class="btn btn-ghost" id="btn-swap" disabled>⇅ Tukar Posisi</button>
        </div>
    </div>

    <div class="sidebar-section" id="mode-section" style="display:none;">
        <div class="section-label">Pilih Jalur Kendaraan</div>
        <div class="mode-options">
            <div class="mode-card active" data-mode="driving">
                <span class="mode-icon">🚗</span>Mobil
            </div>
            <div class="mode-card" data-mode="cycling">
                <span class="mode-icon">🏍️</span>Motor
            </div>
            <div class="mode-card" data-mode="foot">
                <span class="mode-icon">🚶</span>Jalan Kaki
            </div>
        </div>
    </div>

    <div class="sidebar-section" id="route-info-section" style="display:none;">
        <div class="section-label">Panduan Rute Tercepat</div>
        <div class="route-card visible" style="margin-bottom: 16px;">
            <div class="route-stat">
                <span>📏 Sisa Jarak</span>
                <span class="route-stat-val accent" id="info-distance">—</span>
            </div>
            <div class="route-stat">
                <span>⏱️ Estimasi Waktu</span>
                <span class="route-stat-val green" id="info-time">—</span>
            </div>
        </div>
        <div class="btn-group">
            <button class="btn btn-primary full-width" id="btn-start-realtime">🧭 Mulai Navigasi Nyata (GPS)</button>
            <button class="btn btn-primary" id="btn-run-sim" style="background:#4fcf8e; color:#000;">▶️ Simulasi</button>
            <button class="btn btn-danger" id="btn-reset">✕ Batalkan Rute</button>
        </div>
    </div>

    <div class="empty-state" id="empty-state">
        <div class="empty-icon">🌍</div>
        <div class="empty-text">Silakan pilih titik awal dan tujuan<br>langsung pada peta satelit.</div>
    </div>
</aside>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

<script>
    // Konfigurasi Dasar Peta
    let originLatLng = null, destLatLng = null;
    let originMarker = null, destMarker = null;
    let routingControl = null;
    let clickMode = 'origin';
    let currentMode = 'driving';

    let isLiveTracking = false, watchId = null, liveUserMarker = null, isNavigatingRealTime = false;
    let simMarker = null, simInterval = null;
    let fullRouteCoordinates = [], totalRouteDistance = 0, totalRouteTime = 0;
    
    // Variabel Navigasi Nyata & Reroute
    let realTimeNavWatchId = null;
    let isRealTimeNavigating = false;
    let isRerouting = false;

    const map = L.map('map', { zoomControl: false }).setView([-7.5463, 112.2370], 13);
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    L.tileLayer('http://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
        maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3'], attribution: '© Google Maps Satelit'
    }).addTo(map);

    // ────────────────────────────────────────────────────────
    // SISTEM MENU KANAN (DROPDOWN)
    // ────────────────────────────────────────────────────────
    const menuToggleBtn = document.getElementById('menu-toggle-btn');
    const rightMenu = document.getElementById('right-menu');
    
    menuToggleBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        rightMenu.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
        if (!rightMenu.contains(e.target)) rightMenu.classList.remove('show');
    });

    // ────────────────────────────────────────────────────────
    // PEMBUATAN MAKER
    // ────────────────────────────────────────────────────────
    function createCustomMarker(color, text) {
        return L.divIcon({
            className: '',
            html: `<div style="position:relative; display:flex; flex-direction:column; align-items:center;">
                <div style="width:34px; height:34px; background:${color}; border:3px solid #fff; border-radius:50% 50% 50% 0; transform:rotate(-45deg); box-shadow:0 6px 12px rgba(0,0,0,0.4);"></div>
                <div style="position:absolute; top:5px; font-size:14px; color:#fff; font-weight:bold; cursor:grab;">${text}</div>
            </div>`,
            iconSize: [34, 45], iconAnchor: [17, 45]
        });
    }

    const iconA = createCustomMarker('#4f8ef7', 'A'), iconB = createCustomMarker('#f74f7b', 'B');

    function setOriginMarker(latlng) {
        originLatLng = latlng;
        if (originMarker) map.removeLayer(originMarker);
        originMarker = L.marker(originLatLng, { icon: iconA, draggable: true }).addTo(map);
        originMarker.on('dragend', function(ev) {
            originLatLng = ev.target.getLatLng();
            document.getElementById('origin-input').value = originLatLng.lat.toFixed(5) + ', ' + originLatLng.lng.toFixed(5);
            if (destLatLng && document.getElementById('mode-section').style.display === 'block') processRoutingEngine();
        });
        document.getElementById('origin-input').value = originLatLng.lat.toFixed(5) + ', ' + originLatLng.lng.toFixed(5);
    }

    function setDestMarker(latlng) {
        destLatLng = latlng;
        if (destMarker) map.removeLayer(destMarker);
        destMarker = L.marker(destLatLng, { icon: iconB, draggable: true }).addTo(map);
        destMarker.on('dragend', function(ev) {
            destLatLng = ev.target.getLatLng();
            document.getElementById('dest-input').value = destLatLng.lat.toFixed(5) + ', ' + destLatLng.lng.toFixed(5);
            if (originLatLng && document.getElementById('mode-section').style.display === 'block') processRoutingEngine();
        });
        document.getElementById('dest-input').value = destLatLng.lat.toFixed(5) + ', ' + destLatLng.lng.toFixed(5);
    }

    map.on('click', function(e) {
        if (isNavigatingRealTime || simInterval || isRealTimeNavigating) return;

        if (clickMode === 'origin') {
            setOriginMarker(e.latlng); clickMode = 'dest';
            updateHint('dest', 'Klik peta untuk menentukan Titik Tujuan');
        } else if (clickMode === 'dest') {
            setDestMarker(e.latlng); clickMode = 'ready';
            document.getElementById('map-hint').style.display = 'none';
            document.getElementById('btn-start-process').disabled = false;
            document.getElementById('btn-swap').disabled = false;
        }
    });

    // ────────────────────────────────────────────────────────
    // FITUR: ALGORITMA SMART-FALLBACK GPS (Anti Timeout di PC)
    // ────────────────────────────────────────────────────────
    function handleGpsError(err, fallbackTimer) {
        if(fallbackTimer) clearTimeout(fallbackTimer);
        document.getElementById('loading').classList.remove('active');
        
        let msg = 'Gagal mengambil lokasi GPS Anda.';
        if(err) {
            if(err.code === 1) msg = 'Izin lokasi GPS ditolak oleh browser. Harap izinkan akses lokasi di pengaturan situs.';
            else if(err.code === 2) msg = 'Lokasi gagal dideteksi (Periksa fitur "Location Services" Windows Anda).';
            else if(err.code === 3) msg = 'Waktu permintaan habis (Sinyal satelit tidak merespons).';
        }
        showNotification('error', msg);
    }

    function executeGpsLocationAction() {
        if (!navigator.geolocation) { showNotification('error', 'Browser tidak mendukung GPS.'); return; }
        
        document.getElementById('loading').classList.add('active');
        document.getElementById('loading-text').textContent = "Mencari sinyal satelit GPS (Akurasi Tinggi)...";

        const fallbackTimer = setTimeout(() => {
            document.getElementById('loading').classList.remove('active');
            showNotification('error', 'Waktu habis. Komputer mungkin tidak mendeteksi jaringan yang valid.');
        }, 12000);

        const successCallback = position => {
            clearTimeout(fallbackTimer);
            document.getElementById('loading').classList.remove('active');
            
            const gpsLatLng = L.latLng(position.coords.latitude, position.coords.longitude);
            setOriginMarker(gpsLatLng);
            map.setView(gpsLatLng, 15);
            
            if (destLatLng) {
                document.getElementById('empty-state').style.display = 'none';
                document.getElementById('controls-section').style.display = 'none';
                document.getElementById('mode-section').style.display = 'block';
                processRoutingEngine();
                showNotification('success', 'Lokasi diperbarui. Rute otomatis dikalkulasi.');
            } else {
                clickMode = 'dest';
                updateHint('dest', 'Lokasi dikunci! Sekarang Klik Titik Tujuan (B).');
                showNotification('success', 'Lokasi Anda berhasil ditemukan.');
            }
        };

        const errorCallback = err => {
            if (err.code === 2 || err.code === 3) {
                document.getElementById('loading-text').textContent = "Beralih mencari titik lokasi lewat jaringan WIFI/IP...";
                navigator.geolocation.getCurrentPosition(
                    successCallback, 
                    (err2) => handleGpsError(err2, fallbackTimer), 
                    { enableHighAccuracy: false, timeout: 6000, maximumAge: Infinity }
                );
            } else {
                handleGpsError(err, fallbackTimer);
            }
        };

        navigator.geolocation.getCurrentPosition(
            successCallback, 
            errorCallback, 
            { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
        );
    }

    document.getElementById('menu-use-gps').addEventListener('click', () => {
        rightMenu.classList.remove('show');
        executeGpsLocationAction();
    });
    document.getElementById('btn-use-gps-sidebar').addEventListener('click', executeGpsLocationAction);


    // ────────────────────────────────────────────────────────
    // FITUR: PELACAKAN PERGERAKAN (LIVE TRACKING BEBAS)
    // ────────────────────────────────────────────────────────
    document.getElementById('menu-track').addEventListener('click', function() {
        rightMenu.classList.remove('show');
        const menuTrackBtn = document.getElementById('menu-track');

        if (!isLiveTracking) {
            if (!navigator.geolocation) { showNotification('error', 'Browser tidak mendukung akses GPS.'); return; }
            
            menuTrackBtn.innerHTML = '<span style="font-size: 16px;">🛑</span> Hentikan Pelacakan';
            menuTrackBtn.classList.add('danger');
            isLiveTracking = true;
            showNotification('info', 'Menghubungkan ke satelit GPS gawai Anda...');

            watchId = navigator.geolocation.watchPosition(function(position) {
                const currentGpsLatLng = L.latLng(position.coords.latitude, position.coords.longitude);
                if (!liveUserMarker) {
                    liveUserMarker = L.marker(currentGpsLatLng, { 
                        icon: L.divIcon({ className: 'live-gps-marker', html: '<div class="pulse-dot"></div>', iconSize: [18, 18], iconAnchor: [9, 9] }) 
                    }).addTo(map);
                } else {
                    liveUserMarker.setLatLng(currentGpsLatLng);
                }
                
                // Animasi pan halus mengunci lokasi pengguna saat track biasa
                if (!isNavigatingRealTime || !destLatLng) {
                    map.panTo(currentGpsLatLng, { animate: true, duration: 1.0 });
                }
            }, function(err) {
                showNotification('error', 'Koneksi pelacakan terputus. Pastikan fitur lokasi Anda aktif.'); stopLiveGpsTracking();
            }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
        } else {
            stopLiveGpsTracking();
        }
    });

    function stopLiveGpsTracking() {
        if (watchId) { navigator.geolocation.clearWatch(watchId); watchId = null; }
        if (liveUserMarker && !isRealTimeNavigating) { map.removeLayer(liveUserMarker); liveUserMarker = null; }
        
        const menuTrackBtn = document.getElementById('menu-track');
        menuTrackBtn.innerHTML = '<span style="font-size: 16px;">📡</span> Lacak Pergerakan Saya';
        menuTrackBtn.classList.remove('danger');
        
        isLiveTracking = false;
        showNotification('info', 'Pelacakan GPS dinonaktifkan.');
    }


    // ────────────────────────────────────────────────────────
    // PEMROSESAN RUTE (OSRM ROUTING ENGINE)
    // ────────────────────────────────────────────────────────
    document.getElementById('btn-start-process').addEventListener('click', function() {
        document.getElementById('empty-state').style.display = 'none';
        document.getElementById('controls-section').style.display = 'none';
        document.getElementById('mode-section').style.display = 'block';
        isNavigatingRealTime = true;
        processRoutingEngine();
    });

    document.querySelectorAll('.mode-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.mode-card').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            currentMode = this.dataset.mode;
            processRoutingEngine();
        });
    });

    function processRoutingEngine(showLoadingOverlay = true) {
        if (!originLatLng || !destLatLng) return;
        if (showLoadingOverlay) document.getElementById('loading').classList.add('active');
        if (routingControl) { map.removeControl(routingControl); routingControl = null; }

        routingControl = L.Routing.control({
            waypoints: [L.latLng(originLatLng.lat, originLatLng.lng), L.latLng(destLatLng.lat, destLatLng.lng)],
            routeWhileDragging: false, addWaypoints: false, fitSelectedRoutes: showLoadingOverlay, showAlternatives: true,
            lineOptions: { styles: [{ color: '#4f8ef7', weight: 8, opacity: 0.85 }, { color: '#ffffff', weight: 3, opacity: 0.9 }] },
            altLineOptions: { styles: [{ color: '#a0a6bd', weight: 6, opacity: 0.6 }] },
            createMarker: function() { return null; },
            router: L.Routing.osrmv1({ serviceUrl: 'https://router.project-osrm.org/route/v1', profile: currentMode })
        }).addTo(map);

        routingControl.on('routesfound', function(e) {
            isRerouting = false; // Reset rerouting lock state
            document.getElementById('loading').classList.remove('active');
            const primaryRoute = e.routes[0];
            fullRouteCoordinates = primaryRoute.coordinates;
            totalRouteDistance = primaryRoute.summary.totalDistance;
            totalRouteTime = primaryRoute.summary.totalTime;
            
            document.getElementById('info-distance').textContent = (totalRouteDistance / 1000).toFixed(1) + ' km';
            document.getElementById('info-time').textContent = Math.round(totalRouteTime / 60) + ' menit';
            document.getElementById('route-info-section').style.display = 'block';
            
            if (showLoadingOverlay) {
                if (e.routes.length > 1) showNotification('success', 'Rute utama dan alternatif ditemukan.');
                else showNotification('info', 'Rute optimal berhasil dimuat.');
            }
        });

        routingControl.on('routingerror', function() {
            isRerouting = false; // Reset lock on error
            document.getElementById('loading').classList.remove('active');
            showNotification('error', 'Gagal memetakan rute pada jalur kendaraan ini.');
        });
    }

    // ────────────────────────────────────────────────────────
    // NAVIGASI NYATA SMOOTH + DETEKSI SALAH JALAN (REROUTE)
    // ────────────────────────────────────────────────────────
    document.getElementById('btn-start-realtime').addEventListener('click', function() {
        if (!isRealTimeNavigating) {
            startRealTimeNavigation();
        } else {
            stopRealTimeNavigation();
        }
    });

    function startRealTimeNavigation() {
        if (!navigator.geolocation) { 
            showNotification('error', 'Browser Anda tidak mendukung fitur GPS.'); 
            return; 
        }

        isRealTimeNavigating = true;
        
        // Sesuaikan Tombol
        const btn = document.getElementById('btn-start-realtime');
        btn.innerHTML = '🛑 Hentikan Navigasi';
        btn.style.background = '#f74f7b'; // Warna danger
        document.getElementById('btn-run-sim').disabled = true;
        
        showNotification('info', 'Menunggu pergerakan Anda via GPS satelit...');

        // Mulai memantau lokasi pengguna secara real-time
        realTimeNavWatchId = navigator.geolocation.watchPosition(function(position) {
            const currentGpsLatLng = L.latLng(position.coords.latitude, position.coords.longitude);
            
            // Buat atau perbarui Live Marker biru dengan animasi "Smooth" class
            if (!liveUserMarker) {
                liveUserMarker = L.marker(currentGpsLatLng, { 
                    icon: L.divIcon({ className: 'live-gps-marker', html: '<div class="pulse-dot"></div>', iconSize: [18, 18], iconAnchor: [9, 9] }) 
                }).addTo(map);
            } else {
                liveUserMarker.setLatLng(currentGpsLatLng);
            }
            
            // Kunci dan posisikan kamera halus mengikuti pergerakan tanpa reload putus-putus
            if (map.getZoom() < 16) map.setZoom(17); 
            map.panTo(currentGpsLatLng, { animate: true, duration: 1.0 });
            
            // FITUR REROUTE: Cek apakah user keluar dari jalur (Jarak Toleransi 50 Meter)
            if (!isRerouting && fullRouteCoordinates && fullRouteCoordinates.length > 0) {
                let minDistance = Infinity;
                
                // Cari titik kordinat garis rute yang paling dekat dengan pengguna saat ini
                for (let i = 0; i < fullRouteCoordinates.length; i++) {
                    const pt = L.latLng(fullRouteCoordinates[i].lat, fullRouteCoordinates[i].lng);
                    const dist = currentGpsLatLng.distanceTo(pt);
                    if (dist < minDistance) {
                        minDistance = dist;
                    }
                }
                
                // Jika posisi Anda lebih dari 50 meter dari garis biru navigasi
                if (minDistance > 50) {
                    isRerouting = true;
                    showNotification('error', 'Anda salah jalan! Mencari rute baru (Reroute)...');
                    
                    // Pindahkan origin ke GPS saat ini secara background
                    setOriginMarker(currentGpsLatLng);
                    
                    // Trigger engine kalkulasi rute baru tanpa loading screen
                    processRoutingEngine(false); 
                }
            }
            
        }, function(err) {
            showNotification('error', 'Sinyal GPS terputus atau gagal diakses. Pastikan Anda di luar ruangan.');
            stopRealTimeNavigation();
        }, { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }); // Maximum speed respons untuk Realtime Navigation
    }

    function stopRealTimeNavigation() {
        if (realTimeNavWatchId) { 
            navigator.geolocation.clearWatch(realTimeNavWatchId); 
            realTimeNavWatchId = null; 
        }
        if (liveUserMarker && !isLiveTracking) { 
            map.removeLayer(liveUserMarker); 
            liveUserMarker = null; 
        }
        
        isRealTimeNavigating = false;
        isRerouting = false;
        
        // Kembalikan Tombol ke semula
        const btn = document.getElementById('btn-start-realtime');
        btn.innerHTML = '🧭 Mulai Navigasi Nyata (GPS)';
        btn.style.background = ''; // reset ke CSS awal
        document.getElementById('btn-run-sim').disabled = false;
        
        showNotification('info', 'Navigasi GPS dihentikan.');
    }


    // ────────────────────────────────────────────────────────
    // SIMULASI NYATA (ANIMASI)
    // ────────────────────────────────────────────────────────
    document.getElementById('btn-run-sim').addEventListener('click', function() {
        if (fullRouteCoordinates.length === 0) return;
        clearActiveSimulation();

        let currentStep = 0;
        let vehicleEmoji = currentMode === 'driving' ? '🚗' : (currentMode === 'cycling' ? '🏍️' : '🚶');

        simMarker = L.marker(fullRouteCoordinates[0], {
            icon: L.divIcon({
                className: 'sim-marker',
                html: `<div style="font-size:26px; filter: drop-shadow(0px 4px 6px rgba(0,0,0,0.4));">${vehicleEmoji}</div>`,
                iconSize: [30, 30], iconAnchor: [15, 15]
            })
        }).addTo(map);

        map.setView(fullRouteCoordinates[0], 18);
        const totalSteps = fullRouteCoordinates.length;
        
        simInterval = setInterval(() => {
            currentStep++;
            if (currentStep >= totalSteps) {
                clearActiveSimulation();
                document.getElementById('info-distance').textContent = '0 km';
                document.getElementById('info-time').textContent = '0 menit';
                showNotification('success', '🏁 Perjalanan selesai! Anda telah sampai di tempat tujuan.');
                return;
            }
            const currentPos = fullRouteCoordinates[currentStep];
            simMarker.setLatLng(currentPos);
            map.panTo(currentPos, { animate: true, duration: 0.25 });

            const percentageLeft = (totalSteps - currentStep) / totalSteps;
            document.getElementById('info-distance').textContent = ((totalRouteDistance * percentageLeft) / 1000).toFixed(1) + ' km';
            document.getElementById('info-time').textContent = Math.round((totalRouteTime * percentageLeft) / 60) + ' menit';
        }, 250); 
        
        showNotification('info', `Navigasi aktif dipandu via ${currentMode === 'foot' ? 'Jalur Pedestrian' : (currentMode === 'cycling' ? 'Jalur Motor' : 'Jalan Raya')}.`);
    });

    function clearActiveSimulation() {
        if (simInterval) { clearInterval(simInterval); simInterval = null; }
        if (simMarker) { map.removeLayer(simMarker); simMarker = null; }
    }

    // ────────────────────────────────────────────────────────
    // UTILITAS
    // ────────────────────────────────────────────────────────
    function updateHint(mode, txt) {
        document.getElementById('hint-text').textContent = txt;
        document.getElementById('hint-dot').className = mode === 'origin' ? 'hint-dot' : 'hint-dot red';
    }

    function showNotification(type, message) {
        const container = document.getElementById('toast');
        const element = document.createElement('div');
        element.className = 'toast-item ' + type; element.textContent = message;
        container.appendChild(element);
        setTimeout(() => {
            element.style.opacity = '0'; element.style.transition = 'opacity 0.4s ease';
            setTimeout(() => element.remove(), 400);
        }, 4500);
    }

    document.getElementById('btn-swap').addEventListener('click', function() {
        if (!originLatLng || !destLatLng) return;
        const tempOrigin = originLatLng;
        const tempDest = destLatLng;
        setOriginMarker(tempDest);
        setDestMarker(tempOrigin);
        processRoutingEngine();
    });

    document.getElementById('btn-reset').addEventListener('click', function() {
        clearActiveSimulation(); stopLiveGpsTracking(); stopRealTimeNavigation();
        if (routingControl) { map.removeControl(routingControl); routingControl = null; }
        if (originMarker) map.removeLayer(originMarker);
        if (destMarker) map.removeLayer(destMarker);
        originLatLng = null; destLatLng = null; originMarker = null; destMarker = null;
        fullRouteCoordinates = []; isNavigatingRealTime = false; isRerouting = false;
        document.getElementById('origin-input').value = '';
        document.getElementById('dest-input').value = '';
        document.getElementById('controls-section').style.display = 'block';
        document.getElementById('mode-section').style.display = 'none';
        document.getElementById('route-info-section').style.display = 'none';
        document.getElementById('empty-state').style.display = 'flex';
        document.getElementById('btn-start-process').disabled = true;
        document.getElementById('btn-swap').disabled = true;
        clickMode = 'origin';
        document.getElementById('map-hint').style.display = 'block';
        updateHint('origin', 'Klik peta untuk menentukan Titik Awal');
    });

    const hideLrmCss = document.createElement('style');
    hideLrmCss.textContent = '.leaflet-routing-container { display: none !important; }';
    document.head.appendChild(hideLrmCss);
</script>
</body>
</html>