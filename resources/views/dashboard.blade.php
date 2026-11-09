<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#153653">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Sensor Monitor">
    <link rel="icon" href="/icons/icon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <title>ESP32 Sensor Monitoring</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background: #f2f5fa;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background: #153653;
        }

        .sensor-card {
            border: 0;
            border-left: 6px solid #adb5bd;
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(0,0,0,.06);
            height: 100%;
            transition: border-color .3s;
        }

        .sensor-value {
            font-size: 2rem;
            font-weight: 700;
            color: #153653;
        }

        .sensor-label {
            color: #6c757d;
            font-size: .9rem;
        }

        .chart-card {
            border: 0;
            border-radius: 16px;
        }

        .status-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #adb5bd;
            margin-right: 5px;
        }

        /* ---------- Status colors for light cards (water level) ---------- */
        .sensor-card.status-ok   { border-left-color: #198754; }
        .sensor-card.status-warn { border-left-color: #ffc107; }
        .sensor-card.status-bad  { border-left-color: #dc3545; }
        .sensor-card.status-none { border-left-color: #adb5bd; }

        .sensor-card.status-warn .sensor-value { color: #b58100; }
        .sensor-card.status-bad  .sensor-value { color: #dc3545; }

        .status-badge {
            font-size: .72rem;
            font-weight: 700;
            padding: .2rem .6rem;
            border-radius: 999px;
            white-space: nowrap;
            background: #e9ecef;
            color: #495057;
        }

        .status-ok   .status-badge { background: #d1e7dd; color: #0f5132; }
        .status-warn .status-badge { background: #fff3cd; color: #664d03; }
        .status-bad  .status-badge { background: #f8d7da; color: #842029; }

        .overall-status {
            border-radius: 12px;
            padding: .75rem 1rem;
            font-weight: 600;
            background: #e9ecef;
            color: #495057;
        }

        .overall-status.status-ok   { background: #d1e7dd; color: #0f5132; }
        .overall-status.status-warn { background: #fff3cd; color: #664d03; }
        .overall-status.status-bad  { background: #f8d7da; color: #842029; }

        /* ---------- Gauge groups (same look as the other cards) ---------- */
        .gauge-group-title {
            color: #153653;
            font-weight: 700;
        }

        .gauge-group-note {
            color: #6c757d;
            font-size: .85rem;
        }

        .gauge-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
        }

        @media (min-width: 992px) {
            .gauge-grid.wide {
                grid-template-columns: repeat(6, minmax(0, 1fr));
            }
        }

        .gauge-card {
            background: #fff;
            border-left: 6px solid #adb5bd;
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(0,0,0,.06);
            padding: .75rem .4rem .7rem;
            text-align: center;
            transition: border-color .3s;
        }

        .gauge-title {
            color: #153653;
            font-size: .85rem;
            font-weight: 700;
            margin-bottom: .2rem;
        }

        .gauge-svg {
            width: 100%;
            max-width: 220px;
            height: auto;
        }

        .gauge-tick {
            fill: #6c757d;
            font-size: 9px;
        }

        .gauge-needle {
            fill: #153653;
            transform-origin: 100px 90px;
            transition: transform .6s ease;
        }

        .gauge-value-text {
            font-size: 19px;
            font-weight: 700;
            fill: #153653;
        }

        .gauge-value-big {
            font-size: 28px;
        }

        .gauge-status {
            display: inline-block;
            font-size: .72rem;
            font-weight: 700;
            padding: .2rem .6rem;
            border-radius: 999px;
            background: #e9ecef;
            color: #495057;
        }

        .gauge-sub {
            color: #6c757d;
            font-size: .72rem;
            margin-top: .25rem;
            min-height: 1em;
        }

        .gauge-card.status-ok   { border-left-color: #198754; }
        .gauge-card.status-warn { border-left-color: #ffc107; }
        .gauge-card.status-bad  { border-left-color: #dc3545; }
        .gauge-card.status-none { border-left-color: #adb5bd; }

        .gauge-card.status-warn .gauge-value-text { fill: #b58100; }
        .gauge-card.status-bad  .gauge-value-text { fill: #dc3545; }
        .gauge-card.status-none .gauge-value-text { fill: #6c757d; }

        .gauge-card.status-ok   .gauge-status { background: #d1e7dd; color: #0f5132; }
        .gauge-card.status-warn .gauge-status { background: #fff3cd; color: #664d03; }
        .gauge-card.status-bad  .gauge-status { background: #f8d7da; color: #842029; }

        .gauge-card.status-none .gauge-svg { opacity: .55; }

        @media (prefers-reduced-motion: reduce) {
            .gauge-needle { transition: none; }
        }

        /* ---------- Navigation (sidebar on desktop, bottom bar on phones) ---------- */
        .app-shell {
            display: flex;
            align-items: flex-start;
        }

        .app-content {
            flex: 1 1 auto;
            min-width: 0;
        }

        .app-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem 1rem;
        }

        .app-nav {
            background: #fff;
        }

        .app-nav a {
            position: relative;
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .7rem 1rem;
            border-radius: 12px;
            color: #6c757d;
            font-weight: 600;
            text-decoration: none;
        }

        .app-nav a svg {
            width: 20px;
            height: 20px;
            flex: none;
        }

        .app-nav a:hover {
            background: #f2f5fa;
            color: #153653;
        }

        .app-nav a.active {
            background: #e8eef6;
            color: #153653;
        }

        .app-nav a:focus-visible,
        .seg-btn:focus-visible {
            outline: 2px solid #153653;
            outline-offset: 2px;
        }

        .nav-badge {
            margin-left: auto;
            min-width: 1.25rem;
            padding: .1rem .4rem;
            border-radius: 999px;
            background: #dc3545;
            color: #fff;
            font-size: .68rem;
            font-weight: 700;
            text-align: center;
        }

        .nav-badge[hidden] { display: none; }

        @media (min-width: 992px) {
            .app-nav {
                position: sticky;
                top: 0;
                flex: 0 0 230px;
                width: 230px;
                height: 100vh;
                display: flex;
                flex-direction: column;
                gap: .25rem;
                padding: 1rem .75rem;
                border-right: 1px solid #e3e8ef;
            }

            .label-short { display: none; }
        }

        @media (max-width: 991.98px) {
            .app-nav {
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 1030;
                display: flex;
                justify-content: space-around;
                padding: .35rem .25rem calc(.35rem + env(safe-area-inset-bottom, 0px));
                border-top: 1px solid #e3e8ef;
                box-shadow: 0 -4px 18px rgba(0,0,0,.06);
            }

            .app-nav a {
                flex: 1 1 0;
                min-width: 0;
                flex-direction: column;
                gap: .15rem;
                padding: .4rem .15rem;
                border-radius: 10px;
                font-size: .68rem;
                text-align: center;
            }

            .app-nav a svg {
                width: 22px;
                height: 22px;
            }

            .nav-badge {
                position: absolute;
                top: .1rem;
                left: calc(50% + .45rem);
                margin: 0;
            }

            .label-long { display: none; }

            .app-main {
                padding-bottom: calc(5.5rem + env(safe-area-inset-bottom, 0px));
            }
        }

        /* ---------- Sensors sub-tabs ---------- */
        .segmented {
            display: inline-flex;
            gap: .2rem;
            padding: .2rem;
            border-radius: 999px;
            background: #e9ecef;
        }

        .seg-btn {
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: #6c757d;
            font-size: .9rem;
            font-weight: 600;
            padding: .4rem 1rem;
        }

        .seg-btn.active {
            background: #fff;
            color: #153653;
            box-shadow: 0 1px 4px rgba(0,0,0,.12);
        }

        /* ---------- Notifications ---------- */
        .alert-list {
            display: flex;
            flex-direction: column;
            gap: .5rem;
        }

        .alert-item {
            border-left: 4px solid #adb5bd;
            border-radius: 12px;
            background: #f7f9fc;
            padding: .6rem .85rem;
        }

        .alert-item.level-ok   { border-left-color: #198754; }
        .alert-item.level-warn { border-left-color: #ffc107; }
        .alert-item.level-bad  { border-left-color: #dc3545; }

        .alert-text {
            color: #153653;
            font-weight: 600;
        }

        .alert-time {
            color: #6c757d;
            font-size: .78rem;
        }

        /* ---------- Controls ---------- */
        .mode-group {
            display: inline-flex;
            gap: .2rem;
            padding: .2rem;
            border-radius: 999px;
            background: #e9ecef;
        }

        .mode-btn {
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: #6c757d;
            font-size: .85rem;
            font-weight: 600;
            padding: .35rem .9rem;
        }

        .mode-btn.active {
            background: #fff;
            color: #153653;
            box-shadow: 0 1px 4px rgba(0,0,0,.12);
        }

        .mode-btn:focus-visible {
            outline: 2px solid #153653;
            outline-offset: 2px;
        }

        .ctrl-title {
            color: #153653;
            font-weight: 700;
        }

        .ctrl-msg {
            min-height: 1.2em;
            color: #6c757d;
        }

        .ctrl-msg.error {
            color: #dc3545;
        }
        @media (display-mode: standalone) {
            #installButton { display: none !important; }
        }

        /* ===== Android / small-screen responsive fixes ===== */
        html, body { width: 100%; max-width: 100%; overflow-x: hidden; }
        *, *::before, *::after { box-sizing: border-box; }
        .app-shell, .app-content, .app-main { width: 100%; min-width: 0; max-width: 100%; }
        .navbar .container { min-width: 0; }
        .navbar-brand { white-space: normal; overflow-wrap: anywhere; min-width: 0; max-width: 100%; line-height: 1.35; }
        .app-main .row > * { min-width: 0; }
        .sensor-card, .chart-card { min-width: 0; }
        .sensor-card .card-body { min-width: 0; }
        .alert-text, .alert-time, .overall-status, .ctrl-title, .sensor-label,
        .sum-sub, #lastUpdated { overflow-wrap: anywhere; }
        .table-responsive { max-width: 100%; }
        @media (max-width: 575.98px) {
            .navbar { padding-top: .7rem !important; padding-bottom: .7rem !important; }
            .navbar .container { padding-left: 1rem; padding-right: 1rem; gap: .5rem !important; }
            .navbar-brand { font-size: .92rem; width: 100%; margin-right: 0; }
            .navbar #installButton { font-size: .75rem; }
            .app-main { padding: 1rem .9rem calc(5.75rem + env(safe-area-inset-bottom, 0px)); }
            .app-main > .d-flex:first-child { align-items: flex-start !important; flex-wrap: wrap; gap: .5rem !important; }
            .app-main > .d-flex:first-child > div:first-child { flex: 1 1 140px; min-width: 0; }
            .app-main > .d-flex:first-child > div:last-child { flex: 1 1 140px; min-width: 0; font-size: .75rem; }
            #pageTitle { font-size: 1.4rem; }
            #pageSubtitle { font-size: .85rem; }
            #page-dashboard > .row > [class*="col-"] { flex: 0 0 100%; width: 100%; max-width: 100%; }
            #page-dashboard .sensor-card .card-body { padding: 1rem; }
            #page-dashboard .sensor-value { font-size: 1.85rem; }
            .chart-card .card-body { padding: 1rem !important; }
            .gauge-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; }
            .gauge-card { min-width: 0; }
            .segmented, .mode-group { max-width: 100%; flex-wrap: wrap; }
            .app-nav { width: 100%; max-width: 100%; }
            .app-nav a { padding: .35rem .05rem; font-size: .6rem; }
            .app-nav a svg { width: 20px; height: 20px; }
        }
    </style>
</head>

<body>
<nav class="navbar navbar-dark py-3">
    <div class="container d-flex flex-wrap align-items-center justify-content-between gap-2">
        <span class="navbar-brand fw-bold mb-0">
            AUTOMATED POULTRY MONITORING SYSTEM
        </span>

        <div class="d-flex align-items-center gap-2">
            <span class="text-white small d-none d-md-inline">
                IoT Dashboard
            </span>

            <button
                id="installButton"
                class="btn btn-light btn-sm fw-semibold"
                type="button"
                aria-label="Install Sensor Monitor app">
                <span aria-hidden="true">⬇</span> Install App
            </button>
        </div>
    </div>
</nav>

<div class="app-shell">

    <nav class="app-nav" aria-label="Main navigation">
        <a href="#dashboard" data-page="dashboard"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg><span>Dashboard</span></a>
        <a href="#sensors" data-page="sensors"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"></path></svg><span>Sensors</span></a>
        <a href="#controls" data-page="controls"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path><line x1="12" y1="2" x2="12" y2="12"></line></svg><span>Controls</span></a>
        <a href="#history" data-page="history"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg><span>History</span></a>
        <a href="#readings" data-page="readings"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg><span>Readings</span></a>
        <a href="#notifications" data-page="notifications"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg><span class="label-long">Notifications</span><span class="label-short">Alerts</span><span class="nav-badge" id="notifBadge" hidden>0</span></a>
    </nav>

    <div class="app-content">
    <main class="app-main">

        <div class="d-flex justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-1" id="pageTitle">Dashboard</h3>
                <div class="text-secondary" id="pageSubtitle">Overview of your poultry house</div>
            </div>

            <div class="text-end small">
                <div>
                    <span class="status-dot" id="statusDot"></span>
                    <span id="connectionStatus">Connecting...</span>
                </div>
                <div class="text-secondary mt-1" id="lastUpdated">
                    Waiting for readings...
                </div>
            </div>
        </div>

        <!-- ===== Dashboard ===== -->
        <section class="page" id="page-dashboard">
            <div id="overallStatus" class="overall-status status-none mb-3" role="status">
                Waiting for readings...
            </div>

            <div class="row g-3 mb-4">
            <div class="col-6 col-xl-4">
                <div class="card sensor-card status-none" data-sum="temp">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-1">
                            <div class="sensor-label">Temperature</div>
                            <span class="status-badge">--</span>
                        </div>
                        <div class="sensor-value mt-2">
                            <span class="sum-value">--</span>
                            <small class="fs-6">°C</small>
                        </div>
                        <div class="text-secondary small mt-2 sum-sub">Waiting for readings...</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-4">
                <div class="card sensor-card status-none" data-sum="gas">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-1">
                            <div class="sensor-label">Amonia</div>
                            <span class="status-badge">--</span>
                        </div>
                        <div class="sensor-value mt-2">
                            <span class="sum-value">--</span>
                            <small class="fs-6">%</small>
                        </div>
                        <div class="text-secondary small mt-2 sum-sub">Waiting for readings...</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-4">
                <div class="card sensor-card status-none" data-sum="water">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-1">
                            <div class="sensor-label">Water level</div>
                            <span class="status-badge">--</span>
                        </div>
                        <div class="sensor-value mt-2">
                            <span class="sum-value">--</span>
                            <small class="fs-6">%</small>
                        </div>
                        <div class="text-secondary small mt-2 sum-sub">Waiting for readings...</div>
                    </div>
                </div>
            </div>
            </div>

            <div class="card chart-card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Latest alerts</h5>
                        <a href="#notifications" class="small fw-semibold" style="color:#153653">View all</a>
                    </div>
                    <div class="alert-list" id="dashAlerts"></div>
                </div>
            </div>
        </section>

        <!-- ===== Sensors ===== -->
        <section class="page" id="page-sensors" hidden>
            <div class="segmented mb-3" role="group" aria-label="Sensor type">
                <button type="button" class="seg-btn active" data-tab="monitors" aria-pressed="true">Monitors</button>
                <button type="button" class="seg-btn" data-tab="water" aria-pressed="false">Water level</button>
            </div>

            <div id="sensors-monitors">
                <!-- Group 1: temperature (3 x DHT11) -->
                <section class="mb-4" aria-labelledby="dhtTitle">
                    <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-1 mb-3">
                        <h5 class="gauge-group-title mb-0" id="dhtTitle">Temperature Sensors</h5>
                        <span class="gauge-group-note" id="dhtNote"></span>
                    </div>
                    <div class="gauge-grid wide" id="dhtGrid"></div>
                </section>

                <!-- Group 2: ammonia (2 x MQ-135) -->
                <section class="mb-4" aria-labelledby="mqTitle">
                    <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-1 mb-3">
                        <h5 class="gauge-group-title mb-0" id="mqTitle">Ammonia Sensors</h5>
                        <span class="gauge-group-note" id="mqNote"></span>
                    </div>
                    <div class="gauge-grid wide" id="mqGrid"></div>
                </section>
            </div>

            <div id="sensors-water" hidden>
                <!-- Group 3: water level (HC-SR04) -->
                <section class="mb-4" aria-labelledby="waterTitle">
                    <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-1 mb-3">
                        <h5 class="gauge-group-title mb-0" id="waterTitle">Water Level Sensor</h5>
                        <span class="gauge-group-note" id="waterNote"></span>
                    </div>
                    <div class="gauge-grid wide" id="waterGrid"></div>
                </section>
            </div>
        </section>

        <!-- ===== Controls ===== -->
        <section class="page" id="page-controls" hidden>
            <div class="row g-3">

                <!-- Water pump spraying -->
                <div class="col-12 col-lg-4">
                    <div class="card sensor-card status-none" data-ctrl="pump">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                <div>
                                    <div class="ctrl-title">Water spraying</div>
                                    <div class="sensor-label">Water pump</div>
                                </div>
                                <span class="status-badge ctrl-badge">--</span>
                            </div>
                             <div class="mb-3"><span class="status-badge">Automatic Mode</span></div>

                            <div data-panel="pump-auto">
                                <label class="form-label small mb-1">Spray when temperature is above (°C)</label>
                                <input type="number" class="form-control mb-2" data-device="pump" data-field="temp_above" min="0" max="60" step="0.5">

                                <label class="form-label small mb-1">Spray for (seconds)</label>
                                <input type="number" class="form-control mb-2" data-device="pump" data-field="spray_seconds" min="1" max="600" step="1">

                                <label class="form-label small mb-1">Wait between sprays (minutes)</label>
                                <input type="number" class="form-control mb-3" data-device="pump" data-field="wait_minutes" min="1" max="1440" step="1">

                                <button type="button" class="btn btn-sm text-white" style="background:#153653" data-save="pump">Save settings</button>
                            </div>

                            <div class="ctrl-msg small mt-2" role="status"></div>
                        </div>
                    </div>
                </div>

                <!-- Heat lamps (temperature based) -->
                <div class="col-12 col-lg-4">
                    <div class="card sensor-card status-none" data-ctrl="heat">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                <div>
                                    <div class="ctrl-title">Heat lamps</div>
                                    <div class="sensor-label">Temperature based</div>
                                </div>
                                <span class="status-badge ctrl-badge">--</span>
                            </div>
                             <div class="mb-3"><span class="status-badge">Automatic Mode</span></div>

                            <div data-panel="heat-auto">
                                <label class="form-label small mb-1">Turn on when temperature is below (°C)</label>
                                <input type="number" class="form-control mb-2" data-device="heat" data-field="temp_below" min="0" max="50" step="0.5">
                                <div class="text-secondary small mb-3">Turns off again once the temperature is back in range.</div>

                                <button type="button" class="btn btn-sm text-white" style="background:#153653" data-save="heat">Save settings</button>
                            </div>

                            <div class="ctrl-msg small mt-2" role="status"></div>
                        </div>
                    </div>
                </div>

                <!-- Normal lights (schedule based) -->
                <div class="col-12 col-lg-4">
                    <div class="card sensor-card status-none" data-ctrl="lights">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                <div>
                                    <div class="ctrl-title">Lights</div>
                                    <div class="sensor-label">Schedule based</div>
                                </div>
                                <span class="status-badge ctrl-badge">--</span>
                            </div>
                             <div class="mb-3"><span class="status-badge">Schedule Mode</span></div>

                            <div data-panel="lights-auto">
                                <label class="form-label small mb-1">Turn on at</label>
                                <input type="time" class="form-control mb-2" data-device="lights" data-field="on_time">

                                <label class="form-label small mb-1">Turn off at</label>
                                <input type="time" class="form-control mb-3" data-device="lights" data-field="off_time">

                                <button type="button" class="btn btn-sm text-white" style="background:#153653" data-save="lights">Save schedule</button>
                            </div>

                            <div class="ctrl-msg small mt-2" role="status"></div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- ===== History ===== -->
        <section class="page" id="page-history" hidden>
            <div class="card chart-card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold">Temperature History</h5>
                    <div class="text-secondary small mb-2">Green band = target range</div>
                    <div style="height:300px">
                        <canvas id="temperatureChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="card chart-card shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold">Humidity History</h5>
                    <div class="text-secondary small mb-2">Green band = ideal range</div>
                    <div style="height:300px">
                        <canvas id="humidityChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== Readings ===== -->
        <section class="page" id="page-readings" hidden>
            <div class="card chart-card shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold">Recent Sensor Readings</h5>

                    <div class="table-responsive">
                        <table class="table table-striped align-middle text-nowrap">
                            <thead>
                                <tr id="historyHead"></tr>
                            </thead>
                            <tbody id="historyTable"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== Notifications ===== -->
        <section class="page" id="page-notifications" hidden>
            <div class="card chart-card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">All notifications</h5>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearNotifications">
                            Clear all
                        </button>
                    </div>
                    <div class="alert-list" id="notifList"></div>
                </div>
            </div>
        </section>

    </main>
    </div>

</div>

<script>
    /* =====================================================================
       SENSORS: rename them here, or change the field names to match what
       your server returns. A sensor whose field is missing shows "No data".
       (The hum fields stay: History and Readings still use them.)
       ===================================================================== */
    const SENSORS = {
        dht: [
            { id: 'dht1', name: '#1', temp: 'temperature_c',   hum: 'humidity_percent'   },
            { id: 'dht2', name: '#2', temp: 'temperature_c_2', hum: 'humidity_percent_2' },
            { id: 'dht3', name: '#3', temp: 'temperature_c_3', hum: 'humidity_percent_3' }
        ],
        mq: [
            // percent is optional: if missing, it is calculated as raw / 4095 * 100
            { id: 'mq1', name: '#1 Amonia', percent: 'mq135_signal_percent',   raw: 'mq135_raw'   },
            { id: 'mq2', name: '#2 Amonia', percent: 'mq135_signal_percent_2', raw: 'mq135_raw_2' }
        ],
        water: [
            { id: 'tank1', name: 'Water tank 1', distance: 'distance_cm' }
        ]
    };

    /* =====================================================================
       THRESHOLDS: edit these numbers to match your farm.
       ===================================================================== */
    const CONFIG = {
        // Temperature (°C) for laying hens: green inside the range,
        // yellow within the margin outside it, red beyond that.
        temp: { min: 18, max: 24, margin: 2 },

        // Humidity (%): only used for the History chart band now.
        humidity: { min: 50, max: 70, margin: 10 },

        // MQ-135 relative signal (%). Not calibrated to ppm. Observe your
        // own reading in clean air, then set these just above that baseline.
        gas: { warn: 25, critical: 50 },

        // Water tank. The HC-SR04 sits on top and measures down to the surface.
        // fullCm  = distance to the water when the tank is FULL
        // emptyCm = distance to the water when the tank is EMPTY
        // Measure both once and enter them here. The gauge shows level in %.
        // Yellow at warnPercent or below, red at criticalPercent or below.
        water: { fullCm: 5, emptyCm: 40, warnPercent: 30, criticalPercent: 15 }
    };

    /* ===================================================================== */

    const numberFormat = (value, digits = 1) => {
        if (value === null || value === undefined || value === '') {
            return '--';
        }

        const number = Number(value);
        return Number.isFinite(number)
            ? number.toFixed(digits)
            : '--';
    };

    const hasValue = value =>
        value !== null && value !== undefined && value !== '' &&
        Number.isFinite(Number(value));

    function setConnection(connected, message) {
        document.getElementById('connectionStatus').textContent = message;

        document.getElementById('statusDot').style.background =
            connected ? '#198754' : '#dc3545';
    }

    /* ---------- Status evaluation ---------- */

    // Healthy inside [min, max]; too low or too high otherwise.
    function rangeStatus(value, min, max, margin, lowText, highText) {
        if (!hasValue(value)) return { level: 'none', text: 'No data' };

        const v = Number(value);
        if (v >= min && v <= max) return { level: 'ok', text: 'Normal' };

        const distance = v < min ? min - v : v - max;
        return {
            level: distance <= margin ? 'warn' : 'bad',
            text: v < min ? lowText : highText
        };
    }

    // Higher value = worse (gas signal, water distance).
    function highIsBad(value, warnAt, criticalAt, warnText, badText) {
        if (!hasValue(value)) return { level: 'none', text: 'No data' };

        const v = Number(value);
        if (v >= criticalAt) return { level: 'bad', text: badText };
        if (v >= warnAt) return { level: 'warn', text: warnText };
        return { level: 'ok', text: 'Normal' };
    }

    // Lower value = worse (tank level).
    function lowIsBad(value, warnAt, criticalAt, warnText, badText) {
        if (!hasValue(value)) return { level: 'none', text: 'No data' };

        const v = Number(value);
        if (v <= criticalAt) return { level: 'bad', text: badText };
        if (v <= warnAt) return { level: 'warn', text: warnText };
        return { level: 'ok', text: 'Normal' };
    }

    // Converts the HC-SR04 distance (cm) into tank level (0-100 %).
    function waterLevelPercent(distance) {
        if (!hasValue(distance)) return null;

        const { fullCm, emptyCm } = CONFIG.water;
        const percent = (emptyCm - Number(distance)) / (emptyCm - fullCm) * 100;
        return Math.min(100, Math.max(0, percent));
    }

    /* ---------- Gauge widget (SVG, no extra library) ---------- */
    const ZONE_COLORS = { ok: '#198754', warn: '#ffc107', bad: '#dc3545' };

    function polar(cx, cy, r, deg) {
        const rad = deg * Math.PI / 180;
        return [cx + r * Math.cos(rad), cy + r * Math.sin(rad)];
    }

    function arcPath(cx, cy, r, a1, a2) {
        const [x1, y1] = polar(cx, cy, r, a1);
        const [x2, y2] = polar(cx, cy, r, a2);
        const large = (a2 - a1) > 180 ? 1 : 0;
        return `M ${x1.toFixed(2)} ${y1.toFixed(2)} ` +
               `A ${r} ${r} 0 ${large} 1 ${x2.toFixed(2)} ${y2.toFixed(2)}`;
    }

    function createGauge(parent, { title, min, max, ticks, zones, unit, decimals = 1 }) {
        const cx = 100, cy = 90, r = 70;
        const startAngle = 135, sweep = 270;

        const angleOf = value => {
            const clamped = Math.min(max, Math.max(min, value));
            return startAngle + sweep * (clamped - min) / (max - min);
        };

        const arcs = zones.map(zone => {
            const from = Math.max(zone.from, min);
            const to = Math.min(zone.to, max);
            if (to <= from) return '';

            return `<path d="${arcPath(cx, cy, r, angleOf(from), angleOf(to))}"
                          fill="none" stroke="${ZONE_COLORS[zone.level]}"
                          stroke-width="12"></path>`;
        }).join('');

        const tickLabels = ticks.map(tick => {
            const [x, y] = polar(cx, cy, r - 17, angleOf(tick));
            return `<text class="gauge-tick" x="${x.toFixed(1)}" y="${y.toFixed(1)}"
                          text-anchor="middle" dominant-baseline="middle">${tick}</text>`;
        }).join('');

        const card = document.createElement('div');
        card.className = 'gauge-card status-none';
        card.innerHTML = `
            <div class="gauge-title"></div>
            <svg class="gauge-svg" viewBox="0 0 200 160" role="img">
                ${arcs}
                ${tickLabels}
                <g class="gauge-needle">
                    <polygon points="94,86.5 158,90 94,93.5"></polygon>
                    <circle cx="100" cy="90" r="6"></circle>
                </g>
                <text class="gauge-value-text" x="100" y="146" text-anchor="middle">--</text>
            </svg>
            <div class="gauge-status">No data</div>
            <div class="gauge-sub"></div>`;

        card.querySelector('.gauge-title').textContent = title;
        card.querySelector('svg').setAttribute('aria-label', title);

        const needle = card.querySelector('.gauge-needle');
        const valueText = card.querySelector('.gauge-value-text');
        const statusEl = card.querySelector('.gauge-status');
        const subEl = card.querySelector('.gauge-sub');

        needle.style.transform = `rotate(${angleOf(min)}deg)`;
        parent.appendChild(card);

        return {
            update(value, status, sub = '') {
                card.classList.remove(
                    'status-ok', 'status-warn', 'status-bad', 'status-none'
                );
                card.classList.add('status-' + status.level);

                const valid = hasValue(value);
                valueText.textContent = valid
                    ? `${Number(value).toFixed(decimals)}${unit ? ' ' + unit : ''}`
                    : '--';
                needle.style.transform =
                    `rotate(${angleOf(valid ? Number(value) : min)}deg)`;
                statusEl.textContent = status.text;
                subEl.textContent = sub;
            }
        };
    }

    // Half-donut gauge (0-100 %) used for the water tank
    function createLevelGauge(parent, { title }) {
        const cx = 100, cy = 95, r = 68;

        const card = document.createElement('div');
        card.className = 'gauge-card status-none';
        card.innerHTML = `
            <div class="gauge-title"></div>
            <svg class="gauge-svg" viewBox="0 0 200 125" role="img">
                <path d="${arcPath(cx, cy, r, 180, 360)}"
                      fill="none" stroke="#e9ecef" stroke-width="22"></path>
                <path class="gauge-fill" d="" fill="none"
                      stroke="#adb5bd" stroke-width="22"></path>
                <text class="gauge-tick" x="${cx - r}" y="${cy + 18}"
                      text-anchor="middle">0</text>
                <text class="gauge-tick" x="${cx + r}" y="${cy + 18}"
                      text-anchor="middle">100</text>
                <text class="gauge-value-text gauge-value-big" x="100" y="90"
                      text-anchor="middle">--</text>
            </svg>
            <div class="gauge-status">No data</div>
            <div class="gauge-sub"></div>`;

        card.querySelector('.gauge-title').textContent = title;
        card.querySelector('svg').setAttribute('aria-label', title);

        const fill = card.querySelector('.gauge-fill');
        const valueText = card.querySelector('.gauge-value-text');
        const statusEl = card.querySelector('.gauge-status');
        const subEl = card.querySelector('.gauge-sub');

        parent.appendChild(card);

        return {
            update(value, status, sub = '') {
                card.classList.remove(
                    'status-ok', 'status-warn', 'status-bad', 'status-none'
                );
                card.classList.add('status-' + status.level);

                const valid = hasValue(value);
                const fraction = valid
                    ? Math.min(0.9999, Math.max(0, Number(value) / 100))
                    : 0;

                fill.setAttribute(
                    'd',
                    fraction > 0 ? arcPath(cx, cy, r, 180, 180 + 180 * fraction) : ''
                );
                fill.setAttribute('stroke', ZONE_COLORS[status.level] || '#adb5bd');

                valueText.textContent = valid ? `${Math.round(Number(value))}%` : '--';
                statusEl.textContent = status.text;
                subEl.textContent = sub;
            }
        };
    }

    /* ---------- Build the gauges from SENSORS + CONFIG ---------- */
    const gauges = {};

    (function buildGauges() {
        const dhtGrid = document.getElementById('dhtGrid');
        const mqGrid = document.getElementById('mqGrid');
        const t = CONFIG.temp;
        const g = CONFIG.gas;

        // Colored arc zones follow the thresholds, so gauge and badge always agree.
        const tempZones = [
            { from: 0,                 to: t.min - t.margin, level: 'bad'  },
            { from: t.min - t.margin,  to: t.min,            level: 'warn' },
            { from: t.min,             to: t.max,            level: 'ok'   },
            { from: t.max,             to: t.max + t.margin, level: 'warn' },
            { from: t.max + t.margin,  to: 50,               level: 'bad'  }
        ];

        const gasZones = [
            { from: 0,          to: g.warn,     level: 'ok'   },
            { from: g.warn,     to: g.critical, level: 'warn' },
            { from: g.critical, to: 100,        level: 'bad'  }
        ];

        SENSORS.dht.forEach(sensor => {
            gauges[sensor.id + ':temp'] = createGauge(dhtGrid, {
                title: `${sensor.name} Temperature`,
                min: 0, max: 50, ticks: [0, 10, 20, 30, 40, 50],
                zones: tempZones, unit: '°C'
            });
        });

        SENSORS.mq.forEach(sensor => {
            gauges[sensor.id] = createGauge(mqGrid, {
                title: `${sensor.name} Signal`,
                min: 0, max: 100, ticks: [0, 20, 40, 60, 80, 100],
                zones: gasZones, unit: '%'
            });
        });

        document.getElementById('dhtNote').textContent =
            `Target: temperature ${t.min}–${t.max} °C`;

        document.getElementById('mqNote').textContent =
            `Relative signal, not ppm. Warning at ${g.warn} %, critical at ${g.critical} %`;

        SENSORS.water.forEach(sensor => {
            gauges[sensor.id] = createLevelGauge(
                document.getElementById('waterGrid'), { title: sensor.name }
            );
        });

        const wc = CONFIG.water;
        document.getElementById('waterNote').textContent =
            `Warning at ${wc.warnPercent} % or less, critical at ${wc.criticalPercent} % or less`;
    })();

    /* ---------- Render the latest reading ---------- */
    function renderSensors(data) {
        const t = CONFIG.temp;
        const g = CONFIG.gas;
        const w = CONFIG.water;
        const results = [];
        const summary = { temps: [], gas: [], water: [] };

        SENSORS.dht.forEach(sensor => {
            const tempValue = data?.[sensor.temp];

            const tempStatus = rangeStatus(
                tempValue, t.min, t.max, t.margin, 'Too cold', 'Too hot'
            );

            gauges[sensor.id + ':temp'].update(tempValue, tempStatus);

            results.push({
                label: `${sensor.name} temperature`, status: tempStatus,
                valueText: `${numberFormat(tempValue)} °C`
            });

            summary.temps.push({ value: tempValue, status: tempStatus });
        });

        SENSORS.mq.forEach(sensor => {
            const rawValue = data?.[sensor.raw];
            let percent = data?.[sensor.percent];

            if (!hasValue(percent) && hasValue(rawValue)) {
                percent = Number(rawValue) / 4095 * 100;
            }

            const status = highIsBad(
                percent, g.warn, g.critical, 'Elevated', 'High gas'
            );

            gauges[sensor.id].update(
                percent, status,
                hasValue(rawValue) ? `ADC ${rawValue}` : ''
            );

            results.push({
                label: `${sensor.name} gas`, status,
                valueText: `${numberFormat(percent)} %`
            });
            summary.gas.push({ value: percent, status });
        });

        SENSORS.water.forEach(sensor => {
            const distance = data?.[sensor.distance];
            const level = waterLevelPercent(distance);
            const status = lowIsBad(
                level, w.warnPercent, w.criticalPercent, 'Getting low', 'Very low'
            );

            gauges[sensor.id].update(
                level, status,
                hasValue(distance)
                    ? `Distance ${numberFormat(distance)} cm`
                    : 'No measurement'
            );

            results.push({
                label: sensor.name, status,
                valueText: `${numberFormat(level, 0)} %`
            });
            summary.water.push({ value: level, status });
        });

        renderOverall(results, !!data);
        renderSummary(summary);
        recordNotifications(results, !!data);
    }

    function renderOverall(results, hasData) {
        const banner = document.getElementById('overallStatus');

        const bad = results.filter(r => r.status.level === 'bad');
        const warn = results.filter(r => r.status.level === 'warn');
        const none = results.filter(r => r.status.level === 'none');

        const describe = list =>
            list.map(r => `${r.label} (${r.status.text.toLowerCase()})`).join(', ');

        let level = 'none';
        let text = 'Waiting for readings...';

        if (hasData) {
            if (bad.length) {
                level = 'bad';
                text = 'Critical: ' + describe(bad);
                if (warn.length) text += '. Warning: ' + describe(warn);
            } else if (warn.length) {
                level = 'warn';
                text = 'Warning: ' + describe(warn);
            } else if (none.length === results.length) {
                level = 'none';
                text = 'No valid sensor data yet';
            } else {
                level = 'ok';
                text = 'All monitored readings are within range';
            }

            if (none.length && none.length < results.length) {
                text += `. ${none.length} reading${none.length > 1 ? 's' : ''} unavailable`;
            }
        }

        banner.classList.remove(
            'status-ok', 'status-warn', 'status-bad', 'status-none'
        );
        banner.classList.add('status-' + level);
        banner.textContent = text;
    }

    /* ---------- Dashboard summary cards ---------- */
    const SEVERITY = { none: 0, ok: 1, warn: 2, bad: 3 };

    // The card shows the worst status among its sensors, so one hot
    // sensor is not hidden by a normal average.
    function worstStatus(items) {
        return items.reduce(
            (worst, item) =>
                SEVERITY[item.status.level] > SEVERITY[worst.level]
                    ? item.status
                    : worst,
            { level: 'none', text: 'No data' }
        );
    }

    function fillSummary(key, items, { mode, digits, noun }) {
        const card = document.querySelector(`[data-sum="${key}"]`);
        if (!card) return;

        const numbers = items
            .filter(item => hasValue(item.value))
            .map(item => Number(item.value));

        let text = '--';
        if (numbers.length) {
            const result = mode === 'max'
                ? Math.max(...numbers)
                : mode === 'min'
                    ? Math.min(...numbers)
                    : numbers.reduce((sum, n) => sum + n, 0) / numbers.length;
            text = result.toFixed(digits);
        }

        const status = worstStatus(items);

        card.classList.remove(
            'status-ok', 'status-warn', 'status-bad', 'status-none'
        );
        card.classList.add('status-' + status.level);
        card.querySelector('.status-badge').textContent = status.text;
        card.querySelector('.sum-value').textContent = text;
        card.querySelector('.sum-sub').textContent = items.length === 1
            ? (numbers.length ? 'Reporting' : 'Not reporting')
            : `${numbers.length} of ${items.length} ${noun} reporting`;
    }

    function renderSummary(summary) {
        fillSummary('temp',  summary.temps, { mode: 'avg', digits: 1, noun: 'sensors' });
        fillSummary('gas',   summary.gas,   { mode: 'max', digits: 1, noun: 'sensors' });
        fillSummary('water', summary.water, { mode: 'min', digits: 0, noun: 'tanks' });
    }

    /* ---------- Notifications (saved in this browser) ---------- */
    const NOTIF_KEY = 'poultryNotifications';
    const NOTIF_MAX = 100;
    let currentPage = 'dashboard';

    function loadNotif() {
        try {
            const saved = JSON.parse(localStorage.getItem(NOTIF_KEY));
            if (saved && Array.isArray(saved.items)) {
                return {
                    items: saved.items,
                    levels: saved.levels || {},
                    unread: Number(saved.unread) || 0
                };
            }
        } catch (error) {
            console.error('Could not read notifications:', error);
        }
        return { items: [], levels: {}, unread: 0 };
    }

    function saveNotif() {
        try {
            localStorage.setItem(NOTIF_KEY, JSON.stringify(notifState));
        } catch (error) {
            console.error('Could not save notifications:', error);
        }
    }

    let notifState = loadNotif();

    // Adds a notification whenever a sensor changes status
    // (into warning/critical, or back to normal).
    function recordNotifications(results, hasData) {
        if (!hasData) return;

        let changed = false;

        results.forEach(({ label, status, valueText }) => {
            if (status.level === 'none') return;

            const previous = notifState.levels[label];
            if (previous === status.level) return;

            notifState.levels[label] = status.level;
            changed = true;

            const recovered = status.level === 'ok';
            if (recovered && previous !== 'warn' && previous !== 'bad') return;

            notifState.items.unshift({
                time: new Date().toISOString(),
                level: status.level,
                title: label,
                message: recovered ? 'Back to normal' : status.text,
                value: valueText
            });

            if (currentPage !== 'notifications') notifState.unread += 1;
        });

        if (!changed) return;

        notifState.items = notifState.items.slice(0, NOTIF_MAX);
        saveNotif();
        renderNotifications();
    }

    function alertItemElement(item) {
        const element = document.createElement('div');
        element.className = 'alert-item level-' + item.level;

        const text = document.createElement('div');
        text.className = 'alert-text';
        text.textContent =
            `${item.title}: ${item.message}${item.value ? ' (' + item.value + ')' : ''}`;

        const time = document.createElement('div');
        time.className = 'alert-time';
        const date = new Date(item.time);
        time.textContent = Number.isNaN(date.getTime()) ? '' : date.toLocaleString();

        element.append(text, time);
        return element;
    }

    function fillAlertList(container, items, emptyText) {
        container.replaceChildren();

        if (!items.length) {
            const empty = document.createElement('div');
            empty.className = 'text-secondary small';
            empty.textContent = emptyText;
            container.appendChild(empty);
            return;
        }

        items.forEach(item => container.appendChild(alertItemElement(item)));
    }

    function renderNotifications() {
        const badge = document.getElementById('notifBadge');
        badge.hidden = notifState.unread <= 0;
        badge.textContent = notifState.unread > 99 ? '99+' : String(notifState.unread);

        fillAlertList(
            document.getElementById('notifList'), notifState.items,
            'No notifications yet. Alerts appear here when a reading goes out of range.'
        );
        fillAlertList(
            document.getElementById('dashAlerts'), notifState.items.slice(0, 5),
            'No alerts so far.'
        );

        document.getElementById('clearNotifications').disabled =
            notifState.items.length === 0;
    }

    document.getElementById('clearNotifications').addEventListener('click', () => {
        notifState.items = [];
        notifState.unread = 0;
        saveNotif();
        renderNotifications();
    });

    /* ---------- Page navigation ---------- */
    const PAGES = {
        dashboard:     { title: 'Dashboard',     subtitle: 'Overview of your poultry house' },
        sensors:       { title: 'Sensors',       subtitle: 'Live readings from every sensor' },
        controls:      { title: 'Controls',      subtitle: 'Water pump, heat lamps and lights' },
        history:       { title: 'History',       subtitle: 'How temperature and humidity changed' },
        readings:      { title: 'Readings',      subtitle: 'Recent readings from all sensors' },
        notifications: { title: 'Notifications', subtitle: 'Alerts when a reading goes out of range' }
    };

    function showPage(name) {
        if (!PAGES[name]) name = 'dashboard';
        currentPage = name;

        document.querySelectorAll('.page').forEach(page => {
            page.hidden = page.id !== 'page-' + name;
        });

        document.querySelectorAll('.app-nav a').forEach(link => {
            const active = link.dataset.page === name;
            link.classList.toggle('active', active);

            if (active) {
                link.setAttribute('aria-current', 'page');
            } else {
                link.removeAttribute('aria-current');
            }
        });

        document.getElementById('pageTitle').textContent = PAGES[name].title;
        document.getElementById('pageSubtitle').textContent = PAGES[name].subtitle;

        if (name === 'history') {
            temperatureChart.resize();
            humidityChart.resize();
        }

        if (name === 'notifications') {
            notifState.unread = 0;
            saveNotif();
            renderNotifications();
        }

        window.scrollTo(0, 0);
    }

    window.addEventListener('hashchange', () => showPage(location.hash.slice(1)));

    document.querySelectorAll('.seg-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.seg-btn').forEach(other => {
                const active = other === button;
                other.classList.toggle('active', active);
                other.setAttribute('aria-pressed', String(active));
            });

            document.getElementById('sensors-monitors').hidden =
                button.dataset.tab !== 'monitors';
            document.getElementById('sensors-water').hidden =
                button.dataset.tab !== 'water';
        });
    });

    /* ---------- History charts (one line per sensor) ---------- */
    const idealBandPlugin = {
        id: 'idealBand',
        beforeDatasetsDraw(chart, args, opts) {
            if (!opts || opts.min == null || opts.max == null) return;

            const { ctx, chartArea, scales } = chart;
            const y = scales.y;
            if (!chartArea || !y) return;

            const top = y.getPixelForValue(Math.min(opts.max, y.max));
            const bottom = y.getPixelForValue(Math.max(opts.min, y.min));
            if (bottom <= top) return;

            ctx.save();
            ctx.fillStyle = 'rgba(25, 135, 84, 0.12)';
            ctx.fillRect(chartArea.left, top, chartArea.width, bottom - top);
            ctx.restore();
        }
    };

    const SERIES_COLORS = ['#0d6efd', '#fd7e14', '#6f42c1'];

    function makeChart(canvasId, series, yScale = {}, band = {}) {
        return new Chart(document.getElementById(canvasId), {
            type: 'line',
            plugins: [idealBandPlugin],
            data: {
                labels: [],
                datasets: series.map(item => ({
                    label: item.label,
                    data: [],
                    borderColor: item.color,
                    backgroundColor: item.color,
                    tension: 0.3,
                    pointRadius: 2,
                    spanGaps: true
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: yScale,
                    x: {
                        grid: { display: false },
                        ticks: {
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: window.innerWidth < 576 ? 4 : 8
                        }
                    }
                },
                plugins: {
                    idealBand: band,
                    tooltip: {
                        callbacks: {
                            // Hover shows the full date and time with seconds
                            title: items =>
                                items[0].chart.fullLabels?.[items[0].dataIndex] ??
                                items[0].label
                        }
                    }
                }
            }
        });
    }

    const temperatureChart = makeChart(
        'temperatureChart',
        SENSORS.dht.map((s, i) => ({
            label: `${s.name} (°C)`, color: SERIES_COLORS[i % SERIES_COLORS.length]
        })),
        { suggestedMin: 15, suggestedMax: 40 },
        { min: CONFIG.temp.min, max: CONFIG.temp.max }
    );

    const humidityChart = makeChart(
        'humidityChart',
        SENSORS.dht.map((s, i) => ({
            label: `${s.name} (%)`, color: SERIES_COLORS[i % SERIES_COLORS.length]
        })),
        { min: 0, max: 100 },
        { min: CONFIG.humidity.min, max: CONFIG.humidity.max }
    );

    /* ---------- History table ---------- */
    function formatTime(value) {
        if (!value) return '--';

        const date = new Date(value.replace(' ', 'T'));

        return Number.isNaN(date.getTime())
            ? value
            : date.toLocaleString();
    }

    const TABLE_COLUMNS = [
        { label: 'Date and Time',    get: row => formatTime(row.recorded_at) },
        { label: 'Water Level (cm)', get: row => numberFormat(row.distance_cm) },
        ...SENSORS.dht.map(s => ({
            label: `${s.name} Temp (°C)`, get: row => numberFormat(row[s.temp])
        })),
        ...SENSORS.dht.map(s => ({
            label: `${s.name} Humidity (%)`, get: row => numberFormat(row[s.hum])
        })),
        ...SENSORS.mq.map(s => ({
            label: `${s.name} ADC`, get: row => row[s.raw] ?? '--'
        }))
    ];

    (function buildTableHead() {
        const head = document.getElementById('historyHead');

        TABLE_COLUMNS.forEach(column => {
            const th = document.createElement('th');
            th.textContent = column.label;
            head.appendChild(th);
        });
    })();

    function showTableMessage(message) {
        const table = document.getElementById('historyTable');
        table.replaceChildren();

        const tr = document.createElement('tr');
        const td = document.createElement('td');
        td.colSpan = TABLE_COLUMNS.length;
        td.className = 'text-center';
        td.textContent = message;
        tr.appendChild(td);
        table.appendChild(tr);
    }

    /* ---------- Data loading ---------- */
    async function loadLatest() {
        const response = await fetch('/readings/latest', {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        });

        if (!response.ok) {
            throw new Error('Unable to load latest readings.');
        }

        const result = await response.json();
        const data = result.data;

        if (!data) {
            renderSensors(null);
            setConnection(false, 'No readings yet');
            return;
        }

        document.getElementById('lastUpdated').textContent =
            'Last reading: ' + formatTime(data.recorded_at);

        renderSensors(data);
        setConnection(true, 'Server connected');
    }

    async function loadHistory() {
        const response = await fetch('/readings/history', {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        });

        if (!response.ok) {
            throw new Error('Unable to load history.');
        }

        const result = await response.json();
        const rows = result.data || [];

        const dates = rows.map(row =>
            new Date(String(row.recorded_at).replace(' ', 'T'))
        );
        const validDates = dates.filter(date => !Number.isNaN(date.getTime()));
        const spansDays = validDates.length > 1 &&
            validDates[0].toDateString() !==
            validDates[validDates.length - 1].toDateString();

        const labels = rows.map((row, index) => {
            const date = dates[index];
            if (Number.isNaN(date.getTime())) return String(row.recorded_at);

            const time = date.toLocaleTimeString(
                [], { hour: 'numeric', minute: '2-digit' }
            );

            return spansDays
                ? [date.toLocaleDateString([], { month: 'short', day: 'numeric' }), time]
                : time;
        });

        const fullLabels = rows.map((row, index) =>
            Number.isNaN(dates[index].getTime())
                ? String(row.recorded_at)
                : dates[index].toLocaleString()
        );

        [temperatureChart, humidityChart].forEach(chart => {
            chart.data.labels = labels;
            chart.fullLabels = fullLabels;
        });

        SENSORS.dht.forEach((sensor, index) => {
            temperatureChart.data.datasets[index].data =
                rows.map(row => row[sensor.temp] ?? null);

            humidityChart.data.datasets[index].data =
                rows.map(row => row[sensor.hum] ?? null);
        });

        temperatureChart.update();
        humidityChart.update();

        if (rows.length === 0) {
            showTableMessage('No readings recorded yet.');
            return;
        }

        const table = document.getElementById('historyTable');
        table.replaceChildren();

        rows.slice().reverse().forEach(row => {
            const tr = document.createElement('tr');

            TABLE_COLUMNS.forEach(column => {
                const td = document.createElement('td');
                td.textContent = column.get(row);
                tr.appendChild(td);
            });

            table.appendChild(tr);
        });
    }

    /* ---------- Controls (pump, heat lamps, lights) ---------- */
    const DEVICES = ['pump', 'heat', 'lights'];

    const controls = {
        pump:   { mode: 'auto',     manual_on: false, temp_above: 30, spray_seconds: 10, wait_minutes: 15 },
        heat:   { mode: 'auto',     manual_on: false, temp_below: CONFIG.temp.min },
        lights: { mode: 'schedule', manual_on: false, on_time: '18:00', off_time: '06:00' }
    };

    const running = { pump: null, heat: null, lights: null }; // real state reported by the ESP32
    const unsaved = { pump: false, heat: false, lights: false };

    const ctrlCard = dev => document.querySelector(`[data-ctrl="${dev}"]`);

    function setCtrlMessage(dev, text, isError = false) {
        const el = ctrlCard(dev).querySelector('.ctrl-msg');
        el.textContent = text;
        el.classList.toggle('error', isError);
    }

    function renderControls() {
        DEVICES.forEach(dev => {
            const c = controls[dev];
            const card = ctrlCard(dev);

            document.querySelectorAll(`[data-device="${dev}"][data-field]`).forEach(input => {
                if (document.activeElement !== input && !unsaved[dev]) {
                    input.value = c[input.dataset.field];
                }
            });

            const state = running[dev];
            card.classList.remove('status-ok', 'status-none');
            card.classList.add(state ? 'status-ok' : 'status-none');
            card.querySelector('.ctrl-badge').textContent =
                state === null ? 'No data' : state ? 'ON' : 'OFF';
        });
    }

    async function sendControl(dev) {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch('/controls', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...(token ? { 'X-CSRF-TOKEN': token } : {})
            },
            body: JSON.stringify({ device: dev, settings: controls[dev] })
        });

        if (!response.ok) throw new Error('Save failed');
    }

    async function saveControl(dev, okMessage = 'Saved') {
        controls[dev].mode = dev === 'lights' ? 'schedule' : 'auto';
        controls[dev].manual_on = false;
        setCtrlMessage(dev, 'Saving...');
        try {
            await sendControl(dev);
            unsaved[dev] = false;
            setCtrlMessage(dev, okMessage);
        } catch (error) {
            setCtrlMessage(dev, 'Could not save. Check the server connection.', true);
            console.error(error);
        }
    }

    async function loadControls() {
        const response = await fetch('/controls', {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        });
        if (!response.ok) throw new Error('Unable to load controls.');

        const data = (await response.json()).data;
        if (!data) return;

        DEVICES.forEach(dev => {
            if (!data[dev]) return;
            if (!unsaved[dev]) Object.assign(controls[dev], data[dev].settings || {});
            controls[dev].mode = dev === 'lights' ? 'schedule' : 'auto';
            controls[dev].manual_on = false;
            running[dev] = typeof data[dev].running === 'boolean' ? data[dev].running : null;
        });

        renderControls();
    }

    document.querySelectorAll('[data-field]').forEach(input => {
        input.addEventListener('input', () => { unsaved[input.dataset.device] = true; });
    });

    document.querySelectorAll('[data-save]').forEach(button => {
        button.addEventListener('click', () => {
            const dev = button.dataset.save;
            const next = {};

            for (const input of document.querySelectorAll(`[data-device="${dev}"][data-field]`)) {
                const field = input.dataset.field;
                const value = input.type === 'number' ? Number(input.value) : input.value;

                if (input.value === '' || (input.type === 'number' && !Number.isFinite(value))) {
                    setCtrlMessage(dev, 'Fill in every field before saving.', true);
                    return;
                }
                next[field] = value;
            }

            if (dev === 'lights' && next.on_time === next.off_time) {
                setCtrlMessage(dev, 'On and off times must be different.', true);
                return;
            }

            Object.assign(controls[dev], next);
            saveControl(dev);
        });
    });

    renderControls();

    async function refreshDashboard() {
        try {
            await Promise.all([loadLatest(), loadHistory()]);
        } catch (error) {
            setConnection(false, 'Connection error');
            console.error(error);
        }

        // Controls have their own error handling so a missing /controls
        // endpoint never shows "Connection error" for the sensors.
        loadControls().catch(error => console.error(error));
    }

    showTableMessage('Waiting for sensor readings...');
    renderSensors(null);
    renderNotifications();
    showPage(location.hash.slice(1));
    refreshDashboard();
    setInterval(refreshDashboard, 3000);


if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        try {
            await navigator.serviceWorker.register('/sw.js');
            console.log('Sensor Monitor PWA service worker registered.');
        } catch (error) {
            console.error('Service worker registration failed:', error);
        }
    });
}

let deferredInstallPrompt = null;

const installButton = document.getElementById('installButton');
const standaloneQuery = window.matchMedia('(display-mode: standalone)');

function isStandaloneApp() {
    return standaloneQuery.matches || window.navigator.standalone === true;
}

function updateInstallButton() {
    if (!installButton) return;
    installButton.hidden = isStandaloneApp();
    installButton.style.display = isStandaloneApp() ? 'none' : '';
}

// Hide the button whenever the installed PWA runs in its own window.
updateInstallButton();
if (typeof standaloneQuery.addEventListener === 'function') {
    standaloneQuery.addEventListener('change', updateInstallButton);
} else if (typeof standaloneQuery.addListener === 'function') {
    standaloneQuery.addListener(updateInstallButton);
}

window.addEventListener('beforeinstallprompt', event => {
    event.preventDefault();
    deferredInstallPrompt = event;
    updateInstallButton();
});

if (installButton) {
    installButton.addEventListener('click', async () => {
        if (isStandaloneApp()) {
            updateInstallButton();
            return;
        }

        if (deferredInstallPrompt) {
            const promptEvent = deferredInstallPrompt;
            deferredInstallPrompt = null;
            promptEvent.prompt();

            try {
                await promptEvent.userChoice;
            } catch (error) {
                console.error('Install prompt was dismissed or failed:', error);
            }
            return;
        }

        // Browser fallback for browsers that do not expose the install prompt.
        alert(
            'To install Sensor Monitor, open your browser menu and choose ' +
            '"Install app" or "Add to Home screen". ' +
            'If this option is missing, check that the site is using HTTPS ' +
            '(or localhost), the manifest and icons load correctly, and the ' +
            'service worker is active.'
        );
    });
}

window.addEventListener('appinstalled', () => {
    deferredInstallPrompt = null;
    if (installButton) {
        installButton.hidden = true;
        installButton.style.display = 'none';
    }
    console.log('Sensor Monitor PWA installed.');
});
</script>

</body>

</html>