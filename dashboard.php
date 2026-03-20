<?php

require_once 'includes/auth.php';


$user       = htmlspecialchars($_SESSION['user']);
$login_time = isset($_SESSION['login_time'])
    ? date('d M Y, H:i', $_SESSION['login_time'])
    : 'Unknown';
$role       = $_SESSION['role'] ?? 'user';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — LinuxWebUI</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- Chart.js for RAM donut + CPU line chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<style>

:root {
    --bg:           #0a0a0f;
    --bg-2:         #0d1117;
    --sidebar-bg:   rgba(13,17,23,0.85);
    --glass-bg:     rgba(255,255,255,0.06);
    --glass-border: rgba(255,255,255,0.10);
    --glass-shine:  rgba(255,255,255,0.07);
    --text:         #f5f5f7;
    --text-2:       rgba(245,245,247,0.55);
    --text-3:       rgba(245,245,247,0.3);
    --blue:         #0a84ff;
    --green:        #30d158;
    --amber:        #ff9f0a;
    --red:          #ff453a;
    --purple:       #bf5af2;
    --radius:       16px;
    --radius-sm:    10px;
    --sidebar-w:    220px;
}

*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
html,body { height:100%; overflow:hidden; }

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    font-size: 14px;
    background: var(--bg);
    color: var(--text);
    display: flex;
}

/* ── ANIMATED BACKGROUND ────────────────────────────────── */
body::before {
    content: '';
    position: fixed;
    inset: -50%;
    background:
        radial-gradient(ellipse at 20% 30%, rgba(10,132,255,0.1) 0%, transparent 50%),
        radial-gradient(ellipse at 80% 70%, rgba(94,92,230,0.1) 0%, transparent 50%),
        linear-gradient(160deg, #0a0a0f 0%, #0d1117 100%);
    animation: bgShift 25s ease-in-out infinite alternate;
    z-index: 0;
    pointer-events: none;
}

@keyframes bgShift {
    0%   { transform: translate(0,0) scale(1); }
    100% { transform: translate(1%,2%) scale(1.02); }
}

/* ── SIDEBAR ────────────────────────────────────────────── */
.sidebar {
    position: fixed;
    top: 0; left: 0;
    width: var(--sidebar-w);
    height: 100vh;
    background: var(--sidebar-bg);
    backdrop-filter: blur(30px);
    -webkit-backdrop-filter: blur(30px);
    border-right: 1px solid var(--glass-border);
    display: flex;
    flex-direction: column;
    z-index: 100;
    /* Slide in on load */
    animation: sidebarIn 0.4s cubic-bezier(0.25,0.46,0.45,0.94) both;
}

@keyframes sidebarIn {
    from { transform: translateX(-20px); opacity:0; }
    to   { transform: translateX(0);     opacity:1; }
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 1.25rem 1.1rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

.sidebar-brand .logo {
    width: 32px; height: 32px;
    border-radius: 9px;
    background: linear-gradient(145deg, rgba(255,255,255,0.12), rgba(255,255,255,0.04));
    border: 1px solid rgba(255,255,255,0.15);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
}

.sidebar-brand h2 {
    font-size: 0.95rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--text);
}

/* User info block */
.sidebar-user {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.85rem 1.1rem;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

.user-avatar {
    width: 30px; height: 30px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--blue), var(--purple));
    display: flex; align-items: center; justify-content: center;
    font-size: 0.72rem;
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
    /* Shows first letter of username */
}

.user-name  { font-size: 0.82rem; font-weight: 600; color: var(--text); line-height:1.2; }
.user-role  { font-size: 0.65rem; color: var(--text-3); text-transform: uppercase; letter-spacing:0.04em; }

/* Nav */
.sidebar-nav { flex:1; padding: 0.75rem 0; overflow-y: auto; }

.nav-section {
    font-size: 0.62rem;
    font-weight: 600;
    color: var(--text-3);
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 0.5rem 1.1rem 0.25rem;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.55rem 1.1rem;
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--text-2);
    text-decoration: none;
    border-radius: 0;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
    border-left: 2px solid transparent;
    position: relative;
}

.nav-item i { width:16px; text-align:center; font-size:0.8rem; }

.nav-item:hover {
    background: rgba(255,255,255,0.05);
    color: var(--text);
}

.nav-item.active {
    background: rgba(10,132,255,0.1);
    color: var(--blue);
    border-left-color: var(--blue);
}

/* Badge on nav item */
.nav-badge {
    margin-left: auto;
    background: rgba(10,132,255,0.2);
    color: var(--blue);
    font-size: 0.6rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 20px;
    min-width: 18px;
    text-align: center;
}

/* Sidebar footer */
.sidebar-footer { padding: 0.75rem 1.1rem 1rem; border-top: 1px solid rgba(255,255,255,0.06); }

.btn-logout {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.55rem 0.75rem;
    background: rgba(255,69,58,0.08);
    border: 1px solid rgba(255,69,58,0.2);
    border-radius: var(--radius-sm);
    color: var(--red);
    font-family: inherit;
    font-size: 0.82rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s, border-color 0.2s;
}
.btn-logout:hover { background: rgba(255,69,58,0.14); border-color: rgba(255,69,58,0.35); }

/* ── MAIN AREA ──────────────────────────────────────────── */
.main {
    margin-left: var(--sidebar-w);
    flex: 1;
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
    position: relative;
    z-index: 1;
}

/* ── TOP BAR ────────────────────────────────────────────── */
.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1.5rem;
    background: rgba(10,10,15,0.7);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--glass-border);
    flex-shrink: 0;
    animation: topbarIn 0.4s ease 0.1s both;
}

@keyframes topbarIn {
    from { opacity:0; transform:translateY(-10px); }
    to   { opacity:1; transform:translateY(0); }
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    color: var(--text-3);
}
.topbar-left .page-title { color: var(--text); font-weight: 600; }
.topbar-sep { color: var(--text-3); }

.topbar-right {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    font-size: 0.75rem;
    color: var(--text-2);
}

.live-pill {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    background: rgba(48,209,88,0.1);
    border: 1px solid rgba(48,209,88,0.2);
    border-radius: 20px;
    padding: 0.2rem 0.6rem;
    font-size: 0.68rem;
    font-weight: 600;
    color: var(--green);
    letter-spacing: 0.04em;
}

.live-dot {
    width: 5px; height: 5px;
    border-radius: 50%;
    background: var(--green);
    box-shadow: 0 0 5px var(--green);
    animation: breathe 2s ease-in-out infinite;
}

@keyframes breathe {
    0%,100% { opacity:1; transform:scale(1); }
    50%      { opacity:0.4; transform:scale(0.7); }
}

/* ── PAGE BODY ──────────────────────────────────────────── */
.page-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.25rem 1.5rem;
    /* Stagger children in */
    animation: bodyIn 0.5s ease 0.15s both;
}

@keyframes bodyIn {
    from { opacity:0; transform:translateY(10px); }
    to   { opacity:1; transform:translateY(0); }
}

/* Custom scrollbar */
.page-body::-webkit-scrollbar { width:5px; }
.page-body::-webkit-scrollbar-track { background:transparent; }
.page-body::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.1); border-radius:3px; }

/* ── SECTION HEADING ────────────────────────────────────── */
.section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.85rem;
}

.section-title {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--text-3);
    letter-spacing: 0.06em;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.section-meta { font-size: 0.72rem; color: var(--text-3); }

/* ── STAT CARDS ROW ─────────────────────────────────────── */
.stat-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.85rem;
    margin-bottom: 0.85rem;
}

.stat-card {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius);
    padding: 1rem 1.1rem;
    position: relative;
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    border-color: rgba(255,255,255,0.18);
    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
}

/* Coloured top accent line */
.stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    border-radius: var(--radius) var(--radius) 0 0;
}
.stat-card.blue::before   { background: linear-gradient(90deg, transparent, var(--blue),   transparent); }
.stat-card.green::before  { background: linear-gradient(90deg, transparent, var(--green),  transparent); }
.stat-card.amber::before  { background: linear-gradient(90deg, transparent, var(--amber),  transparent); }
.stat-card.purple::before { background: linear-gradient(90deg, transparent, var(--purple), transparent); }

.stat-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.6rem;
}

.stat-label {
    font-size: 0.65rem;
    font-weight: 600;
    color: var(--text-3);
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.stat-icon {
    width: 26px; height: 26px;
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.72rem;
}

.stat-icon.blue   { background:rgba(10,132,255,0.15);  color:var(--blue);   }
.stat-icon.green  { background:rgba(48,209,88,0.15);   color:var(--green);  }
.stat-icon.amber  { background:rgba(255,159,10,0.15);  color:var(--amber);  }
.stat-icon.purple { background:rgba(191,90,242,0.15);  color:var(--purple); }

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    letter-spacing: -0.04em;
    line-height: 1;
    margin-bottom: 0.25rem;
}

.stat-card.blue   .stat-value { color: var(--blue);   }
.stat-card.green  .stat-value { color: var(--green);  }
.stat-card.amber  .stat-value { color: var(--amber);  }
.stat-card.purple .stat-value { color: var(--purple); }

.stat-unit {
    font-size: 0.8rem;
    font-weight: 400;
    color: var(--text-3);
    margin-left: 2px;
}

.stat-sub {
    font-size: 0.68rem;
    color: var(--text-3);
    margin-bottom: 0.5rem;
}

/* Mini progress bar */
.mini-bar {
    height: 3px;
    background: rgba(255,255,255,0.07);
    border-radius: 2px;
    overflow: hidden;
}
.mini-bar-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 0.8s cubic-bezier(0.25,0.46,0.45,0.94);
}
.mini-bar-fill.blue   { background: linear-gradient(90deg, rgba(10,132,255,0.5), var(--blue));   }
.mini-bar-fill.green  { background: linear-gradient(90deg, rgba(48,209,88,0.5),  var(--green));  }
.mini-bar-fill.amber  { background: linear-gradient(90deg, rgba(255,159,10,0.5), var(--amber));  }
.mini-bar-fill.purple { background: linear-gradient(90deg, rgba(191,90,242,0.5), var(--purple)); }

/* ── CHARTS ROW ─────────────────────────────────────────── */
.charts-grid {
    display: grid;
    grid-template-columns: 1fr 1.4fr 1fr;
    gap: 0.85rem;
    margin-bottom: 0.85rem;
}

/* ── PANEL (shared by charts + service section) ─────────── */
.panel {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius);
    overflow: hidden;
    transition: border-color 0.2s;
}

.panel:hover { border-color: rgba(255,255,255,0.16); }

.panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.panel-title {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--text-2);
    letter-spacing: 0.04em;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.panel-title i { font-size: 0.75rem; }

.panel-tag {
    font-size: 0.62rem;
    padding: 2px 7px;
    border-radius: 20px;
    font-weight: 600;
}

.panel-tag.green {
    background: rgba(48,209,88,0.12);
    border: 1px solid rgba(48,209,88,0.2);
    color: var(--green);
}

.panel-tag.blue {
    background: rgba(10,132,255,0.12);
    border: 1px solid rgba(10,132,255,0.2);
    color: var(--blue);
}

.panel-body { padding: 1rem; }

/* RAM donut chart */
.donut-wrap {
    position: relative;
    width: 120px; height: 120px;
    margin: 0 auto 0.75rem;
}

.donut-label {
    position: absolute;
    top:50%; left:50%;
    transform: translate(-50%,-50%);
    text-align: center;
    pointer-events: none;
}

.donut-label .big {
    font-size: 1.4rem;
    font-weight: 700;
    letter-spacing: -0.04em;
    line-height: 1;
    color: var(--text);
}

.donut-label .small {
    font-size: 0.6rem;
    color: var(--text-3);
    letter-spacing: 0.04em;
}

.chart-stats {
    display: flex;
    justify-content: space-between;
    font-size: 0.65rem;
}

.chart-stat .v { font-weight:600; color:var(--text); }
.chart-stat .l { color:var(--text-3); margin-top:1px; }

/* CPU sparkline */
.cpu-chart-wrap { height:70px; margin-bottom:0.6rem; }

.cpu-loads {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 0.5rem;
}

.cpu-load-item {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 8px;
    padding: 0.4rem;
    text-align: center;
}

.cpu-load-item .v { font-size:0.8rem; font-weight:600; color:var(--amber); }
.cpu-load-item .l { font-size:0.6rem; color:var(--text-3); margin-top:1px; }

/* Disk bars */
.disk-item { margin-bottom: 0.75rem; }
.disk-item:last-child { margin-bottom: 0; }

.disk-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.7rem;
    margin-bottom: 0.3rem;
}

.disk-name { display:flex; align-items:center; gap:0.35rem; color:var(--text-2); }
.disk-name i { color:var(--text-3); font-size:0.65rem; }
.disk-pct  { color:var(--text-3); font-size:0.65rem; }

.disk-bar {
    height: 4px;
    background: rgba(255,255,255,0.07);
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 0.25rem;
}

.disk-bar-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 0.8s cubic-bezier(0.25,0.46,0.45,0.94);
}

.disk-info {
    display: flex;
    justify-content: space-between;
    font-size: 0.62rem;
    color: var(--text-3);
}

/* ── SERVICE CARDS ──────────────────────────────────────── */
.services-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.85rem;
}

.svc-card {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius);
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
}

.svc-card:hover {
    border-color: rgba(255,255,255,0.16);
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
}

.svc-card.running { border-color: rgba(48,209,88,0.25); }
.svc-card.stopped { border-color: rgba(255,69,58,0.2);  }

.svc-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.svc-info { display:flex; align-items:center; gap:0.6rem; }

.svc-icon {
    width: 34px; height: 34px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
}

.svc-name { font-size:0.82rem; font-weight:600; color:var(--text); line-height:1.2; }
.svc-desc { font-size:0.62rem; color:var(--text-3); }

/* Status badge */
.svc-badge {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.62rem;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 20px;
    letter-spacing: 0.03em;
}

.svc-badge::before {
    content:''; width:5px; height:5px;
    border-radius:50%;
}

.svc-badge.running {
    background: rgba(48,209,88,0.1);
    border: 1px solid rgba(48,209,88,0.2);
    color: var(--green);
}
.svc-badge.running::before {
    background: var(--green);
    box-shadow: 0 0 4px var(--green);
    animation: breathe 2s infinite;
}

.svc-badge.stopped {
    background: rgba(255,69,58,0.1);
    border: 1px solid rgba(255,69,58,0.2);
    color: var(--red);
}
.svc-badge.stopped::before { background: var(--red); }

.svc-badge.checking {
    background: rgba(255,159,10,0.1);
    border: 1px solid rgba(255,159,10,0.2);
    color: var(--amber);
}
.svc-badge.checking::before { background: var(--amber); }

/* Service body — port + buttons */
.svc-body { padding: 0.75rem 1rem; }

.svc-port {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.65rem;
    color: var(--text-3);
    margin-bottom: 0.65rem;
}

.port-tag {
    background: rgba(10,132,255,0.1);
    border: 1px solid rgba(10,132,255,0.2);
    color: var(--blue);
    font-size: 0.62rem;
    padding: 1px 6px;
    border-radius: 5px;
}

/* Action buttons row */
.svc-actions {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 0.4rem;
}

.btn-svc {
    padding: 0.35rem 0;
    border-radius: 7px;
    border: 1px solid;
    background: transparent;
    font-family: inherit;
    font-size: 0.65rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    transition: background 0.15s, transform 0.1s;
    letter-spacing: 0.02em;
}

.btn-svc:active   { transform: scale(0.96); }
.btn-svc:disabled { opacity:0.35; cursor:not-allowed; }

.btn-svc.start   { border-color:rgba(48,209,88,0.3);  color:var(--green); }
.btn-svc.stop    { border-color:rgba(255,69,58,0.3);  color:var(--red);   }
.btn-svc.restart { border-color:rgba(255,159,10,0.3); color:var(--amber); }

.btn-svc.start:hover:not(:disabled)   { background:rgba(48,209,88,0.1);  }
.btn-svc.stop:hover:not(:disabled)    { background:rgba(255,69,58,0.1);  }
.btn-svc.restart:hover:not(:disabled) { background:rgba(255,159,10,0.1); }

/* ── OUTPUT MODAL ───────────────────────────────────────── */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.modal-overlay.open { display:flex; }

.modal-box {
    background: #0d1117;
    border: 1px solid var(--glass-border);
    border-radius: var(--radius);
    width: 100%;
    max-width: 500px;
    box-shadow: 0 24px 48px rgba(0,0,0,0.6);
    animation: modalIn 0.25s cubic-bezier(0.25,0.46,0.45,0.94);
}

@keyframes modalIn {
    from { opacity:0; transform:scale(0.95) translateY(10px); }
    to   { opacity:1; transform:scale(1)    translateY(0);    }
}

.modal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.85rem 1.1rem;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

.modal-title {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.modal-close {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 6px;
    color: var(--text-2);
    cursor: pointer;
    width: 24px; height: 24px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem;
    transition: background 0.15s;
}
.modal-close:hover { background:rgba(255,255,255,0.12); color:var(--text); }

/* Terminal output inside modal */
.terminal {
    background: #020608;
    margin: 0.85rem;
    border-radius: var(--radius-sm);
    border: 1px solid rgba(255,255,255,0.06);
    padding: 0.85rem 1rem;
    font-family: 'SF Mono', 'Monaco', 'Fira Code', monospace;
    font-size: 0.75rem;
    color: #a8d8b0;
    white-space: pre-wrap;
    word-break: break-all;
    max-height: 280px;
    overflow-y: auto;
    line-height: 1.6;
}

.modal-foot {
    padding: 0 0.85rem 0.85rem;
    display: flex;
    justify-content: flex-end;
}

.btn-modal-close {
    padding: 0.45rem 1rem;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 7px;
    color: var(--text-2);
    font-family: inherit;
    font-size: 0.8rem;
    cursor: pointer;
    transition: background 0.15s;
}
.btn-modal-close:hover { background:rgba(255,255,255,0.1); color:var(--text); }

/* ── TOAST ──────────────────────────────────────────────── */
.toast-stack {
    position: fixed;
    bottom: 1.25rem;
    right: 1.25rem;
    z-index: 2000;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.toast {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    background: rgba(28,28,30,0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 0.65rem 1rem;
    font-size: 0.8rem;
    color: var(--text);
    min-width: 240px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    animation: toastIn 0.3s cubic-bezier(0.25,0.46,0.45,0.94);
}

.toast.success i { color: var(--green); }
.toast.error   i { color: var(--red);   }

@keyframes toastIn {
    from { opacity:0; transform:translateX(20px); }
    to   { opacity:1; transform:translateX(0); }
}

/* ── UTILITY ────────────────────────────────────────────── */
.fa-spin { animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── RESPONSIVE ─────────────────────────────────────────── */
@media(max-width:1200px) {
    .stat-grid     { grid-template-columns: repeat(2,1fr); }
    .services-grid { grid-template-columns: repeat(2,1fr); }
}

@media(max-width:900px) {
    :root { --sidebar-w: 0px; }
    .sidebar { display:none; }
    .charts-grid { grid-template-columns: 1fr 1fr; }
}

@media(max-width:600px) {
    .stat-grid     { grid-template-columns: 1fr 1fr; }
    .charts-grid   { grid-template-columns: 1fr; }
    .services-grid { grid-template-columns: 1fr; }
    .page-body     { padding: 1rem; }
}
</style>
</head>
<body>

<!-- ── SIDEBAR ─────────────────────────────────────────── -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="logo"><i class="fa-brands fa-linux"></i></div>
        <h2>LinuxWebUI</h2>
    </div>

    <div class="sidebar-user">
        <!-- Shows first letter of username as avatar -->
        <div class="user-avatar">
            <?= strtoupper(substr($user, 0, 1)) ?>
        </div>
        <div>
            <div class="user-name"><?= $user ?></div>
            <div class="user-role"><?= $role ?></div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Main</div>
        <a class="nav-item active" href="dashboard.php">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
        </a>
        <a class="nav-item" href="#services">
            <i class="fa-solid fa-server"></i> Services
            <span class="nav-badge" id="nav-svc-count">—</span>
        </a>

        <div class="nav-section" style="margin-top:0.5rem;">System</div>
        <a class="nav-item" href="#">
            <i class="fa-solid fa-microchip"></i> Processes
        </a>
        <a class="nav-item" href="#">
            <i class="fa-solid fa-network-wired"></i> Network
        </a>
        <a class="nav-item" href="#">
            <i class="fa-solid fa-folder-open"></i> Files
        </a>

        <div class="nav-section" style="margin-top:0.5rem;">Account</div>
        <a class="nav-item" href="#">
            <i class="fa-solid fa-key"></i> Change Password
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="btn-logout">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            Sign Out
        </a>
    </div>
</aside>

<!-- ── MAIN ────────────────────────────────────────────── -->
<div class="main">

    <!-- Top bar -->
    <div class="topbar">
        <div class="topbar-left">
            <span>LinuxWebUI</span>
            <span class="topbar-sep">/</span>
            <span class="page-title">Dashboard</span>
        </div>
        <div class="topbar-right">
            <span>Uptime: <span id="uptime-val">—</span></span>
            <div class="live-pill">
                <div class="live-dot"></div> LIVE
            </div>
            <span id="clock">—</span>
        </div>
    </div>

    <!-- Page content -->
    <div class="page-body">

        <!-- ── STAT CARDS ──────────────────────────────── -->
        <div class="section-head">
            <div class="section-title">
                <i class="fa-solid fa-chart-simple"></i> Overview
            </div>
            <div class="section-meta">
                Logged in as <strong><?= $user ?></strong>
                since <?= $login_time ?>
            </div>
        </div>

        <div class="stat-grid" style="margin-bottom:0.85rem;">

            <div class="stat-card blue">
                <div class="stat-top">
                    <span class="stat-label">RAM Usage</span>
                    <span class="stat-icon blue">
                        <i class="fa-solid fa-memory"></i>
                    </span>
                </div>
                <div class="stat-value">
                    <span id="s-ram-pct">—</span>
                    <span class="stat-unit">%</span>
                </div>
                <div class="stat-sub">
                    <span id="s-ram-used">—</span> MB used
                    of <span id="s-ram-total">—</span> MB
                </div>
                <div class="mini-bar">
                    <div class="mini-bar-fill blue"
                         id="s-ram-bar" style="width:0%"></div>
                </div>
            </div>

            <div class="stat-card amber">
                <div class="stat-top">
                    <span class="stat-label">CPU Load</span>
                    <span class="stat-icon amber">
                        <i class="fa-solid fa-microchip"></i>
                    </span>
                </div>
                <div class="stat-value">
                    <span id="s-cpu-val">—</span>
                </div>
                <div class="stat-sub">
                    5m: <span id="s-cpu-5m">—</span>
                    &nbsp; 15m: <span id="s-cpu-15m">—</span>
                </div>
                <div class="mini-bar">
                    <div class="mini-bar-fill amber"
                         id="s-cpu-bar" style="width:0%"></div>
                </div>
            </div>

            <div class="stat-card green">
                <div class="stat-top">
                    <span class="stat-label">Disk</span>
                    <span class="stat-icon green">
                        <i class="fa-solid fa-hard-drive"></i>
                    </span>
                </div>
                <div class="stat-value">
                    <span id="s-disk-pct">—</span>
                    <span class="stat-unit">%</span>
                </div>
                <div class="stat-sub">
                    <span id="s-disk-used">—</span> GB used
                    of <span id="s-disk-total">—</span> GB
                </div>
                <div class="mini-bar">
                    <div class="mini-bar-fill green"
                         id="s-disk-bar" style="width:0%"></div>
                </div>
            </div>

            <div class="stat-card purple">
                <div class="stat-top">
                    <span class="stat-label">Services</span>
                    <span class="stat-icon purple">
                        <i class="fa-solid fa-server"></i>
                    </span>
                </div>
                <div class="stat-value">
                    <span id="s-svc-running">—</span>
                    <span class="stat-unit">
                        / <span id="s-svc-total">—</span>
                    </span>
                </div>
                <div class="stat-sub">Services active</div>
                <div class="mini-bar">
                    <div class="mini-bar-fill purple"
                         id="s-svc-bar" style="width:0%"></div>
                </div>
            </div>

        </div>

        <!-- ── CHARTS ROW ──────────────────────────────── -->
        <div class="charts-grid" style="margin-bottom:0.85rem;">

            <!-- RAM Donut -->
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">
                        <i class="fa-solid fa-memory"
                           style="color:var(--blue)"></i>
                        Memory
                    </div>
                    <span class="panel-tag blue" id="ram-tag">—</span>
                </div>
                <div class="panel-body">
                    <div class="donut-wrap">
                        <canvas id="ramChart"></canvas>
                        <div class="donut-label">
                            <div class="big" id="ram-donut-pct">—</div>
                            <div class="small">USED</div>
                        </div>
                    </div>
                    <div class="chart-stats">
                        <div class="chart-stat">
                            <div class="v" id="c-ram-used">—</div>
                            <div class="l">Used</div>
                        </div>
                        <div class="chart-stat">
                            <div class="v" id="c-ram-free">—</div>
                            <div class="l">Free</div>
                        </div>
                        <div class="chart-stat">
                            <div class="v" id="c-ram-total">—</div>
                            <div class="l">Total</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CPU History Line -->
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">
                        <i class="fa-solid fa-microchip"
                           style="color:var(--amber)"></i>
                        CPU Load History
                    </div>
                    <span class="panel-tag blue">30s window</span>
                </div>
                <div class="panel-body">
                    <div class="cpu-chart-wrap">
                        <canvas id="cpuChart"></canvas>
                    </div>
                    <div class="cpu-loads">
                        <div class="cpu-load-item">
                            <div class="v" id="c-cpu-1m">—</div>
                            <div class="l">1 min</div>
                        </div>
                        <div class="cpu-load-item">
                            <div class="v" id="c-cpu-5m">—</div>
                            <div class="l">5 min</div>
                        </div>
                        <div class="cpu-load-item">
                            <div class="v" id="c-cpu-15m">—</div>
                            <div class="l">15 min</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Disk -->
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">
                        <i class="fa-solid fa-hard-drive"
                           style="color:var(--green)"></i>
                        Storage
                    </div>
                    <span class="panel-tag green">Live</span>
                </div>
                <div class="panel-body">
                    <div class="disk-item">
                        <div class="disk-row">
                            <span class="disk-name">
                                <i class="fa-solid fa-folder"></i>
                                / root
                            </span>
                            <span class="disk-pct" id="d-root-pct">—%</span>
                        </div>
                        <div class="disk-bar">
                            <div class="disk-bar-fill"
                                 id="d-root-bar"
                                 style="width:0%; background:var(--green)">
                            </div>
                        </div>
                        <div class="disk-info">
                            <span id="d-root-used">—</span>
                            <span id="d-root-free">— free</span>
                        </div>
                    </div>

                    <div style="margin-top:1rem; padding-top:1rem;
                                border-top:1px solid rgba(255,255,255,0.05);
                                font-size:0.68rem; color:var(--text-3);">
                        <i class="fa-solid fa-circle-info"
                           style="color:var(--blue); margin-right:0.3rem;"></i>
                        Free space:
                        <strong style="color:var(--text-2)">
                            <span id="d-free-gb">—</span> GB
                        </strong>
                        available
                    </div>
                </div>
            </div>

        </div>

        <!-- ── SERVICE CARDS ───────────────────────────── -->
        <div id="services">
            <div class="section-head">
                <div class="section-title">
                    <i class="fa-solid fa-server"></i> Services
                </div>
                <div class="section-meta" id="svc-meta">
                    Checking...
                </div>
            </div>
            <div class="services-grid" id="services-grid">
                <!-- Built by JavaScript below -->
            </div>
        </div>

    </div><!-- /page-body -->
</div><!-- /main -->

<!-- ── OUTPUT MODAL ──────────────────────────────────────── -->
<div class="modal-overlay" id="modal">
    <div class="modal-box">
        <div class="modal-head">
            <div class="modal-title">
                <i class="fa-solid fa-terminal"></i>
                <span id="modal-title">Output</span>
            </div>
            <button class="modal-close" onclick="closeModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <pre class="terminal" id="modal-output"></pre>
        <div class="modal-foot">
            <button class="btn-modal-close" onclick="closeModal()">
                Close
            </button>
        </div>
    </div>
</div>

<!-- ── TOAST STACK ────────────────────────────────────────── -->
<div class="toast-stack" id="toasts"></div>

<script>
// ─────────────────────────────────────────────────────────────
//  CONFIG
// ─────────────────────────────────────────────────────────────
const POLL    = 3000;  // stats refresh every 3 seconds
const CPU_PTS = 30;    // points in CPU sparkline

// Services list — add/remove as needed
const SERVICES = [
    { id:'apache2', name:'Apache2',  desc:'Web Server',    port:'80, 443',
      icon:'fa-brands fa-html5',        color:'#ff6534', bg:'rgba(255,101,52,0.12)' },
    { id:'ssh',     name:'SSH',      desc:'Secure Shell',  port:'22',
      icon:'fa-solid fa-terminal',      color:'#30d158', bg:'rgba(48,209,88,0.12)'  },
    { id:'vsftpd',  name:'FTP',      desc:'File Transfer', port:'21',
      icon:'fa-solid fa-file-arrow-up', color:'#0a84ff', bg:'rgba(10,132,255,0.12)' },
    { id:'mongod',  name:'MongoDB',  desc:'NoSQL DB',      port:'27017',
      icon:'fa-solid fa-database',      color:'#30d158', bg:'rgba(48,209,88,0.12)'  },
    { id:'mysql',   name:'MySQL',    desc:'SQL Database',  port:'3306',
      icon:'fa-solid fa-table',         color:'#0a84ff', bg:'rgba(10,132,255,0.12)' },
    { id:'nginx',   name:'Nginx',    desc:'Proxy Server',  port:'80, 443',
      icon:'fa-solid fa-shield-halved', color:'#30d158', bg:'rgba(48,209,88,0.12)'  },
];

// ─────────────────────────────────────────────────────────────
//  CLOCK — updates every second, no server needed
// ─────────────────────────────────────────────────────────────
function updateClock() {
    document.getElementById('clock').textContent =
        new Date().toLocaleTimeString('en-GB');
}
setInterval(updateClock, 1000);
updateClock();

// ─────────────────────────────────────────────────────────────
//  CHARTS SETUP
// ─────────────────────────────────────────────────────────────
Chart.defaults.color = 'rgba(245,245,247,0.3)';
Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, Segoe UI, sans-serif';

// RAM donut chart
const ramChart = new Chart(
    document.getElementById('ramChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        datasets: [{
            data: [0, 100],
            backgroundColor: ['#0a84ff', 'rgba(255,255,255,0.05)'],
            borderColor:     ['#0a84ff', 'rgba(255,255,255,0.03)'],
            borderWidth: 1,
            hoverOffset: 2
        }]
    },
    options: {
        cutout: '74%',
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: { enabled: false }
        },
        animation: { duration: 600 }
    }
});

// CPU sparkline — 30 data points
const cpuHistory = Array(CPU_PTS).fill(0);
const cpuChart   = new Chart(
    document.getElementById('cpuChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: Array(CPU_PTS).fill(''),
        datasets: [{
            data: cpuHistory,
            borderColor: '#ff9f0a',
            borderWidth: 1.5,
            backgroundColor: 'rgba(255,159,10,0.08)',
            fill: true,
            tension: 0.4,
            pointRadius: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: { display: false },
            y: { display: false, min: 0, suggestedMax: 2 }
        },
        plugins: {
            legend: { display: false },
            tooltip: { enabled: false }
        },
        animation: { duration: 300 }
    }
});

function fetchStats() {
    fetch('api/stats.php', { credentials: 'same-origin' })
        .then(r => r.json())
        .then(d => {
            // ── RAM
            const r = d.ram;
            set('s-ram-pct',   r.percent);
            set('s-ram-used',  r.used);
            set('s-ram-total', r.total);
            set('ram-donut-pct', r.percent + '%');
            set('ram-tag',     r.percent + '% used');
            set('c-ram-used',  r.used + ' MB');
            set('c-ram-free',  r.free + ' MB');
            set('c-ram-total', r.total + ' MB');

            // Update bar width
            barWidth('s-ram-bar', r.percent);

            // Update donut chart — colour changes at thresholds
            const rColor = r.percent > 90 ? '#ff453a'
                         : r.percent > 75 ? '#ff9f0a'
                         : '#0a84ff';
            ramChart.data.datasets[0].data            = [r.used, r.free];
            ramChart.data.datasets[0].backgroundColor = [rColor, 'rgba(255,255,255,0.05)'];
            ramChart.data.datasets[0].borderColor     = [rColor, 'rgba(255,255,255,0.03)'];
            ramChart.update();

            // ── CPU
            const c = d.cpu;
            set('s-cpu-val',  c.load_1m);
            set('s-cpu-5m',   c.load_5m);
            set('s-cpu-15m',  c.load_15m);
            set('c-cpu-1m',   c.load_1m);
            set('c-cpu-5m',   c.load_5m);
            set('c-cpu-15m',  c.load_15m);
            barWidth('s-cpu-bar', Math.min(c.load_1m * 100, 100));

            // Push new point into sparkline
            cpuHistory.push(parseFloat(c.load_1m));
            cpuHistory.shift();
            cpuChart.data.datasets[0].data = [...cpuHistory];
            cpuChart.update();

            // ── DISK
            const dk = d.disk;
            set('s-disk-pct',   dk.percent);
            set('s-disk-used',  dk.used);
            set('s-disk-total', dk.total);
            set('d-root-pct',   dk.percent + '%');
            set('d-root-used',  dk.used + ' GB used');
            set('d-root-free',  dk.free + ' GB free');
            set('d-free-gb',    dk.free);
            barWidth('s-disk-bar', dk.percent);

            // Disk bar colour
            const dBar = document.getElementById('d-root-bar');
            dBar.style.background = dk.percent > 90 ? '#ff453a'
                                  : dk.percent > 75 ? '#ff9f0a'
                                  : '#30d158';
            barWidth('d-root-bar', dk.percent);

            // ── UPTIME
            set('uptime-val', d.uptime || '—');
        })
        .catch(() => {
            // Silently ignore — will retry on next poll
        });
}

// ─────────────────────────────────────────────────────────────
//  BUILD SERVICE CARDS
// ─────────────────────────────────────────────────────────────
function buildServiceCards() {
    const grid = document.getElementById('services-grid');
    grid.innerHTML = '';

    SERVICES.forEach(s => {
        grid.innerHTML += `
        <div class="svc-card" id="svc-${s.id}">
            <div class="svc-head">
                <div class="svc-info">
                    <div class="svc-icon"
                         style="background:${s.bg};
                                color:${s.color};
                                border:1px solid ${s.color}33;">
                        <i class="${s.icon}"></i>
                    </div>
                    <div>
                        <div class="svc-name">${s.name}</div>
                        <div class="svc-desc">${s.desc}</div>
                    </div>
                </div>
                <span class="svc-badge checking" id="badge-${s.id}">
                    Checking
                </span>
            </div>
            <div class="svc-body">
                <div class="svc-port">
                    <i class="fa-solid fa-plug"
                       style="font-size:0.6rem;"></i>
                    Port:
                    <span class="port-tag">${s.port}</span>
                </div>
                <div class="svc-actions">
                    <button class="btn-svc start"
                            id="btn-${s.id}-start"
                            onclick="svcAction('${s.id}','start')"
                            disabled>
                        <i class="fa-solid fa-play"></i> Start
                    </button>
                    <button class="btn-svc stop"
                            id="btn-${s.id}-stop"
                            onclick="svcAction('${s.id}','stop')"
                            disabled>
                        <i class="fa-solid fa-stop"></i> Stop
                    </button>
                    <button class="btn-svc restart"
                            id="btn-${s.id}-restart"
                            onclick="svcAction('${s.id}','restart')"
                            disabled>
                        <i class="fa-solid fa-rotate-right"></i> Restart
                    </button>
                </div>
            </div>
        </div>`;
    });
}

// ─────────────────────────────────────────────────────────────
//  UPDATE SERVICE STATUS IN UI
// ─────────────────────────────────────────────────────────────
function updateSvcUI(id, running) {
    const card  = document.getElementById(`svc-${id}`);
    const badge = document.getElementById(`badge-${id}`);
    if (!card || !badge) return;

    card.classList.remove('running','stopped');
    card.classList.add(running ? 'running' : 'stopped');

    badge.className  = `svc-badge ${running ? 'running' : 'stopped'}`;
    badge.textContent = running ? 'Running' : 'Stopped';

    document.getElementById(`btn-${id}-start`).disabled   = running;
    document.getElementById(`btn-${id}-stop`).disabled    = !running;
    document.getElementById(`btn-${id}-restart`).disabled = !running;
}

// ─────────────────────────────────────────────────────────────
//  FETCH ALL SERVICE STATUSES
// ─────────────────────────────────────────────────────────────
function fetchServices() {
    let done = 0;
    let activeCount = 0;

    SERVICES.forEach(s => {
        // POST to service.php with action=status
        // First fetch inside fetchServices (status check)
fetch('api/service.php', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type':'application/x-www-form-urlencoded' },
    body: `service=${s.id}&action=status`
})
        .then(r => r.json())
        .then(res => {
            if (res.running) activeCount++;
            updateSvcUI(s.id, res.running);
        })
        .catch(() => {})
        .finally(() => {
            done++;
            if (done === SERVICES.length) {
                // All services checked — update counters
                set('s-svc-running', activeCount);
                set('s-svc-total',   SERVICES.length);
                set('nav-svc-count', activeCount);
                barWidth('s-svc-bar',
                    (activeCount / SERVICES.length) * 100);
                set('svc-meta',
                    `${activeCount} of ${SERVICES.length} services active`);
            }
        });
    });
}

// ─────────────────────────────────────────────────────────────
//  SERVICE ACTION — start / stop / restart
// ─────────────────────────────────────────────────────────────
function svcAction(id, action) {
    const svc   = SERVICES.find(s => s.id === id);
    const name  = svc?.name || id;
    const btnEl = document.getElementById(`btn-${id}-${action}`);

    // Disable all 3 buttons while running
    ['start','stop','restart'].forEach(a => {
        const b = document.getElementById(`btn-${id}-${a}`);
        if (b) b.disabled = true;
    });

    btnEl.innerHTML =
        '<i class="fa-solid fa-spinner fa-spin"></i>';

    fetch('api/service.php', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type':'application/x-www-form-urlencoded' },
    body: `service=${id}&action=${action}`
})
    .then(r => r.json())
    .then(res => {
        updateSvcUI(id, res.running);
        fetchServices();

        const label = action.charAt(0).toUpperCase() + action.slice(1);
        toast(
            res.success ? 'success' : 'error',
            `${name}: ${label} ${res.success ? 'executed' : 'failed'}`
        );

        // Show terminal output if there is any
        if (res.output && res.output.trim()) {
            document.getElementById('modal-title').textContent =
                `${name} — ${label}`;
            document.getElementById('modal-output').textContent =
                res.output;
            document.getElementById('modal').classList.add('open');
        }
    })
    .catch(() => {
        fetchServices();
        toast('error', `${name}: Request failed`);
    });
}

// ─────────────────────────────────────────────────────────────
//  MODAL
// ─────────────────────────────────────────────────────────────
function closeModal() {
    document.getElementById('modal').classList.remove('open');
}

// Close modal on overlay click
document.getElementById('modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Close modal on Escape key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
});

// ─────────────────────────────────────────────────────────────
//  TOAST NOTIFICATION
// ─────────────────────────────────────────────────────────────
function toast(type, msg) {
    const stack = document.getElementById('toasts');
    const el    = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `
        <i class="fa-solid ${type === 'success'
            ? 'fa-circle-check'
            : 'fa-circle-xmark'}"></i>
        ${msg}`;
    stack.appendChild(el);
    // Auto remove after 3.5 seconds
    setTimeout(() => {
        el.style.opacity   = '0';
        el.style.transform = 'translateX(20px)';
        el.style.transition = 'all 0.3s ease';
        setTimeout(() => el.remove(), 300);
    }, 3500);
}

// ─────────────────────────────────────────────────────────────
//  HELPERS
// ─────────────────────────────────────────────────────────────
// Short helper — sets text content of an element by id
function set(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}

// Sets width of a progress bar
function barWidth(id, pct) {
    const el = document.getElementById(id);
    if (el) el.style.width = Math.min(pct, 100) + '%';
}

buildServiceCards();   // draw the service cards first
fetchStats();          // get stats immediately
fetchServices();       // check all service statuses

setInterval(fetchStats,    POLL);
setInterval(fetchServices, 10000);
</script>
</body>
</html>
