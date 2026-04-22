<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UC Share Downloader</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Syne', sans-serif; }
        .mono { font-family: 'Space Mono', monospace !important; }
        textarea, input, select { font-family: 'Space Mono', monospace !important; }

        .spinner { border: 3px solid rgba(255,255,255,0.1); border-top: 3px solid #a855f7; border-radius: 50%; width: 16px; height: 16px; animation: spin 0.8s linear infinite; flex-shrink: 0; }
        @keyframes spin { to { transform: rotate(360deg); } }

        @keyframes fadeSlide { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        .fade-in { animation: fadeSlide 0.35s ease forwards; }

        @keyframes shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
        .shimmer-row { background: linear-gradient(90deg, transparent, rgba(168,85,247,0.12), transparent) !important; background-size: 200% 100% !important; animation: shimmer 1.4s infinite !important; }

        .card-hover { transition: transform 0.25s, box-shadow 0.25s; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(168,85,247,0.3); }

        .input-glow:focus-within { box-shadow: 0 0 0 2px rgba(168,85,247,0.45), 0 8px 32px rgba(168,85,247,0.12); }

        .url-tag { display:inline-flex; align-items:center; gap:5px; background:rgba(168,85,247,0.14); border:1px solid rgba(168,85,247,0.35); border-radius:20px; padding:3px 10px; font-size:11px; color:#c084fc; font-family:'Space Mono',monospace; }
        .url-tag button { opacity:0.5; transition:opacity 0.15s; line-height:1; font-size:10px; }
        .url-tag button:hover { opacity:1; }

        .prog-track { height:3px; background:rgba(255,255,255,0.08); border-radius:2px; overflow:hidden; }
        .prog-fill { height:100%; background:linear-gradient(90deg,#a855f7,#ec4899); border-radius:2px; transition:width 0.35s ease; }

        .s-row { display:flex; align-items:center; gap:8px; padding:7px 11px; border-radius:10px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); transition:border-color 0.2s; }
        .s-row.loading { border-color:rgba(234,179,8,0.3); }
        .s-row.done { border-color:rgba(74,222,128,0.3); }
        .s-row.error { border-color:rgba(239,68,68,0.3); }

        ::-webkit-scrollbar { width:5px; }
        ::-webkit-scrollbar-thumb { background:rgba(168,85,247,0.35); border-radius:3px; }

        .glass { background:rgba(255,255,255,0.04); backdrop-filter:blur(20px); border:1px solid rgba(255,255,255,0.07); }
        .glass-p { background:rgba(168,85,247,0.07); backdrop-filter:blur(20px); border:1px solid rgba(168,85,247,0.2); }

        #tagStrip { display: none; }
        #tagStrip.visible { display: flex; flex-wrap: wrap; gap: 6px; }
        #parallelWrap { display: none; }
        #parallelWrap.visible { display: flex; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-[#190a2d] to-slate-950">
<div class="p-4 md:p-6 max-w-7xl mx-auto">

    <!-- Header -->
    <div class="text-center pt-6 mb-10">
        <div class="flex items-center justify-center gap-3 mb-3">
            <svg class="w-9 h-9 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight">
                UC Share <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">Downloader</span>
            </h1>
        </div>
        <p class="text-gray-500 text-sm mono">Single · Bulk · Auto Detect · Parallel · Share</p>
    </div>

    <!-- ═══════════ UNIFIED FORM ═══════════ -->
    <div class="max-w-4xl mx-auto mb-8">
        <div class="glass-p rounded-2xl input-glow transition-all duration-300">

            <!-- Main textarea -->
            <textarea
                id="mainInput"
                rows="4"
                placeholder="Tempel URL UC Share, banyak URL sekaligus, atau teks apapun yang mengandung link UC Share..."
                class="w-full px-5 pt-4 pb-3 bg-transparent text-white text-sm placeholder-gray-600 focus:outline-none resize-none leading-relaxed rounded-t-2xl"
                oninput="onInputChange()"
                onpaste="setTimeout(onInputChange,30)"
            ></textarea>

            <!-- Auto-detected URL tags (shown only when URLs found inside free text) -->
            <div id="tagStrip" class="px-5 pb-3 border-t border-purple-500/10 pt-3"></div>

            <!-- Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 border-t border-purple-500/15 rounded-b-2xl">

                <!-- Left -->
                <div class="flex items-center gap-3 flex-wrap">
                    <span id="urlBadge" class="mono text-xs text-gray-500">0 URL</span>

                    <span id="modeBadge" class="mono text-xs hidden px-2 py-0.5 rounded-full border"></span>

                    <div id="parallelWrap" class="items-center gap-1.5">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" id="parallelOn" checked class="w-3.5 h-3.5 rounded accent-purple-500">
                            <span class="text-gray-400 text-xs">⚡ Parallel</span>
                        </label>
                        <select id="workerSel" class="ml-1 bg-black/30 border border-purple-500/20 text-gray-300 text-xs rounded-lg px-1.5 py-0.5 focus:outline-none">
                            <option value="2">×2</option>
                            <option value="3" selected>×3</option>
                            <option value="5">×5</option>
                        </select>
                    </div>
                </div>

                <!-- Right -->
                <div class="flex items-center gap-2">
                    <button onclick="pasteClipboard()" class="flex items-center gap-1.5 text-gray-400 hover:text-white text-xs px-3 py-2 glass rounded-xl transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" stroke-width="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke-width="2"/></svg>
                        Paste
                    </button>
                    <button onclick="clearForm()" class="text-gray-500 hover:text-gray-300 text-xs px-3 py-2 glass rounded-xl transition-all">Clear</button>
                    <button onclick="runDownload()" id="runBtn"
                        class="flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 disabled:opacity-50 text-white rounded-xl text-sm font-bold transition-all shadow-lg">
                        <div id="runSpinner" class="spinner hidden"></div>
                        <svg id="runIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke-width="2"/></svg>
                        <span id="runLabel">Search</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Progress panel -->
        <div id="progressPanel" class="hidden mt-4 glass-p rounded-2xl p-5 fade-in">
            <div class="flex items-center justify-between mb-2">
                <span class="text-white text-sm font-bold">📊 Processing</span>
                <span id="progLabel" class="mono text-xs text-gray-400">0 / 0</span>
            </div>
            <div class="prog-track mb-3"><div id="progFill" class="prog-fill" style="width:0%"></div></div>
            <div id="statusRows" class="space-y-1.5 max-h-52 overflow-y-auto pr-1"></div>
        </div>
    </div>

    <!-- Error -->
    <div id="errorMsg" class="max-w-4xl mx-auto mb-5 hidden">
        <div class="bg-red-500/10 border border-red-500/30 rounded-2xl px-5 py-3.5 text-red-300 text-sm flex items-center gap-3 fade-in">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/><line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/></svg>
            <span id="errorTxt"></span>
        </div>
    </div>

    <!-- Stats -->
    <div id="statsBar" class="max-w-4xl mx-auto mb-5 hidden">
        <div class="glass-p rounded-2xl p-4 fade-in grid grid-cols-2 md:grid-cols-4 gap-3 text-center">
            <div><p class="text-gray-500 text-xs mb-0.5">Files</p><p class="text-white font-bold mono" id="stFiles">0</p></div>
            <div><p class="text-gray-500 text-xs mb-0.5">Videos</p><p class="text-purple-400 font-bold mono" id="stVideos">0</p></div>
            <div><p class="text-gray-500 text-xs mb-0.5">Size</p><p class="text-pink-400 font-bold mono" id="stSize">0 MB</p></div>
            <div><p class="text-gray-500 text-xs mb-0.5">Folders</p><p class="text-green-400 font-bold mono" id="stFolders">0</p></div>
        </div>
    </div>

    <!-- Results -->
    <div id="resultsSection" class="hidden fade-in space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3 px-1">
            <h2 class="text-2xl font-extrabold text-white" id="resultsTitle">📹 0 videos</h2>
            <button onclick="openShare()" class="flex items-center gap-2 px-4 py-2 glass-p text-purple-300 hover:text-white rounded-xl text-sm font-bold border border-purple-500/20 hover:border-purple-400/50 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8M16 6l-4-4-4 4M12 2v13" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Share
            </button>
        </div>

        <div id="videosGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5"></div>

        <div class="glass-p rounded-2xl p-5">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div>
                    <h3 class="text-white font-bold text-sm">🔗 Semua URL Tonton</h3>
                    <p class="text-gray-500 text-xs mono mt-0.5" id="urlCount">0 URL</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="copyAllUrls()" class="flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl text-xs font-bold transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" stroke-width="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke-width="2"/></svg>
                        <span id="copyTxt">Copy Semua</span>
                    </button>
                    <button onclick="saveTxt()" class="px-3 py-2 glass text-gray-400 hover:text-white rounded-xl text-xs font-bold transition-all">.txt</button>
                    <button onclick="saveJson()" class="px-3 py-2 glass text-gray-400 hover:text-white rounded-xl text-xs font-bold transition-all">.json</button>
                </div>
            </div>
            <textarea id="urlList" readonly rows="8"
                class="w-full px-4 py-3 rounded-xl bg-black/20 border border-purple-500/15 text-green-300 text-xs mono focus:outline-none resize-y"
                placeholder="URL akan muncul di sini..."></textarea>
        </div>
    </div>

    <!-- Empty state -->
    <div id="emptyState" class="text-center py-16 fade-in">
        <svg class="w-20 h-20 text-gray-700 mx-auto mb-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <h3 class="text-gray-400 text-lg font-bold mb-2">Siap untuk download?</h3>
        <p class="text-gray-600 text-sm mb-10">Tempel URL UC Share, banyak URL, atau teks apapun ke kolom di atas</p>
        <div class="max-w-2xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="glass rounded-xl p-4 border border-purple-500/10"><p class="text-2xl mb-2">🔗</p><p class="text-white text-xs font-bold">Single URL</p><p class="text-gray-600 text-xs mt-1">1 link, langsung jalan</p></div>
            <div class="glass rounded-xl p-4 border border-purple-500/10"><p class="text-2xl mb-2">📋</p><p class="text-white text-xs font-bold">Bulk</p><p class="text-gray-600 text-xs mt-1">Banyak URL sekaligus</p></div>
            <div class="glass rounded-xl p-4 border border-purple-500/10"><p class="text-2xl mb-2">🔍</p><p class="text-white text-xs font-bold">Auto Detect</p><p class="text-gray-600 text-xs mt-1">Dari teks sembarang</p></div>
            <div class="glass rounded-xl p-4 border border-purple-500/10"><p class="text-2xl mb-2">⚡</p><p class="text-white text-xs font-bold">Parallel</p><p class="text-gray-600 text-xs mt-1">Multi-worker, lebih cepat</p></div>
        </div>
    </div>

    <p class="text-center text-gray-700 text-xs mono mt-16 pb-6">UC Share Downloader v2.0 · Made with ❤️</p>
</div>

<!-- Share Modal -->
<div id="shareModal" class="fixed inset-0 bg-black/75 backdrop-blur-sm flex items-center justify-center z-50 hidden p-4" onclick="if(event.target===this)closeShare()">
    <div class="glass-p rounded-2xl p-6 max-w-sm w-full border border-purple-500/25 fade-in">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-white font-bold">📤 Bagikan Hasil</h3>
            <button onclick="closeShare()" class="text-gray-500 hover:text-white transition-all text-lg leading-none">✕</button>
        </div>
        <div class="space-y-2">
            <button onclick="doShare('copy')" class="w-full flex items-center gap-3 p-3.5 glass rounded-xl hover:border-purple-500/40 border border-transparent transition-all text-left">
                <span class="text-xl w-8 text-center">📋</span>
                <div><p class="text-white text-sm font-bold">Copy URL</p><p class="text-gray-500 text-xs">Salin semua download URL</p></div>
            </button>
            <button onclick="doShare('txt')" class="w-full flex items-center gap-3 p-3.5 glass rounded-xl hover:border-purple-500/40 border border-transparent transition-all text-left">
                <span class="text-xl w-8 text-center">💾</span>
                <div><p class="text-white text-sm font-bold">Simpan .txt</p><p class="text-gray-500 text-xs">File teks semua URL</p></div>
            </button>
            <button onclick="doShare('json')" class="w-full flex items-center gap-3 p-3.5 glass rounded-xl hover:border-purple-500/40 border border-transparent transition-all text-left">
                <span class="text-xl w-8 text-center">🗂️</span>
                <div><p class="text-white text-sm font-bold">Export JSON</p><p class="text-gray-500 text-xs">Data video lengkap</p></div>
            </button>
            <button onclick="doShare('native')" class="w-full flex items-center gap-3 p-3.5 glass rounded-xl hover:border-purple-500/40 border border-transparent transition-all text-left">
                <span class="text-xl w-8 text-center">📲</span>
                <div><p class="text-white text-sm font-bold">Share Perangkat</p><p class="text-gray-500 text-xs">Menu share sistem (mobile)</p></div>
            </button>
        </div>
        <p id="shareStatus" class="hidden text-center text-green-400 text-sm mono mt-4"></p>
    </div>
</div>

<script>
const UC_RE = /https?:\/\/(?:drive\.ucweb\.com|ucshare\.[a-z]+)\/s\/[A-Za-z0-9_%-]+/gi;

let allVideos = [];
let detectedUrls = [];
let running = false;

/* ══════════ INPUT LOGIC ══════════ */
function onInputChange() {
    const raw = document.getElementById('mainInput').value;
    UC_RE.lastIndex = 0;
    const found = [...new Set(raw.match(UC_RE) || [])];
    detectedUrls = found;

    const lines = raw.split('\n').map(l => l.trim()).filter(Boolean);
    const pureLines = lines.filter(l => UC_RE.test(l) && l.startsWith('http'));
    UC_RE.lastIndex = 0;

    const urlBadge = document.getElementById('urlBadge');
    const modeBadge = document.getElementById('modeBadge');
    const parallelWrap = document.getElementById('parallelWrap');
    const tagStrip = document.getElementById('tagStrip');

    urlBadge.textContent = `${found.length} URL`;

    if (found.length === 0) {
        modeBadge.classList.add('hidden');
        parallelWrap.classList.remove('visible');
        tagStrip.classList.remove('visible');
        return;
    }

    // Mode badge
    modeBadge.classList.remove('hidden');
    if (found.length === 1 && pureLines.length >= 1) {
        modeBadge.textContent = '🔗 Single';
        modeBadge.className = 'mono text-xs px-2 py-0.5 rounded-full border bg-blue-500/15 border-blue-500/30 text-blue-300';
    } else if (pureLines.length === found.length && found.length > 1) {
        modeBadge.textContent = `📋 Bulk ×${found.length}`;
        modeBadge.className = 'mono text-xs px-2 py-0.5 rounded-full border bg-purple-500/15 border-purple-500/30 text-purple-300';
    } else {
        modeBadge.textContent = `🔍 Auto Detect ×${found.length}`;
        modeBadge.className = 'mono text-xs px-2 py-0.5 rounded-full border bg-pink-500/15 border-pink-500/30 text-pink-300';
    }

    // Parallel controls
    if (found.length > 1) parallelWrap.classList.add('visible');
    else parallelWrap.classList.remove('visible');

    // Tag strip for auto-detect mode
    const isAutoDetect = found.length > 0 && pureLines.length < found.length;
    if (isAutoDetect) {
        tagStrip.classList.add('visible');
        renderTags();
    } else {
        tagStrip.classList.remove('visible');
    }
}

function renderTags() {
    const tagStrip = document.getElementById('tagStrip');
    tagStrip.innerHTML = detectedUrls.map((u, i) =>
        `<span class="url-tag">
            <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.1-1.1" stroke-width="2"/></svg>
            ${escHtml(shortUrl(u))}
            <button onclick="removeTag(${i})">✕</button>
        </span>`
    ).join('');
}

function removeTag(i) {
    detectedUrls.splice(i, 1);
    document.getElementById('urlBadge').textContent = `${detectedUrls.length} URL`;
    if (detectedUrls.length === 0) {
        document.getElementById('tagStrip').classList.remove('visible');
        document.getElementById('modeBadge').classList.add('hidden');
        document.getElementById('parallelWrap').classList.remove('visible');
    } else {
        renderTags();
    }
}

async function pasteClipboard() {
    try {
        const t = await navigator.clipboard.readText();
        document.getElementById('mainInput').value = t;
        onInputChange();
    } catch { alert('Izinkan akses clipboard di browser'); }
}

function clearForm() {
    document.getElementById('mainInput').value = '';
    detectedUrls = [];
    onInputChange();
    ['statsBar','errorMsg','resultsSection'].forEach(id => document.getElementById(id).classList.add('hidden'));
    document.getElementById('progressPanel').classList.add('hidden');
    document.getElementById('emptyState').classList.remove('hidden');
}

/* ══════════ RUN ══════════ */
async function runDownload() {
    if (running) return;
    if (!detectedUrls.length) { showError('Masukkan atau tempel URL UC Share terlebih dahulu'); return; }

    running = true;
    setRunBtn(true);
    ['statsBar','errorMsg','resultsSection'].forEach(id => document.getElementById(id).classList.add('hidden'));
    document.getElementById('emptyState').classList.add('hidden');
    allVideos = [];

    const urls = [...detectedUrls];
    const parallel = document.getElementById('parallelOn').checked;
    const workers = parseInt(document.getElementById('workerSel').value);

    if (urls.length === 1) {
        document.getElementById('progressPanel').classList.add('hidden');
        try {
            const data = await fetchApi(urls[0]);
            if (data.status === 'success' && data.videos?.length) {
                allVideos = data.videos;
                renderStats(data.share_info);
                renderVideos(data.videos);
            } else {
                showError(data.message || 'Tidak ada video ditemukan');
            }
        } catch(e) { showError('Gagal: ' + e.message); }
    } else {
        buildProgressRows(urls);
        if (parallel) await runParallel(urls, workers);
        else await runSequential(urls);
        if (allVideos.length > 0) renderVideos(allVideos);
        else showError('Tidak ada video berhasil diambil');
    }

    running = false;
    setRunBtn(false);
}

function setRunBtn(on) {
    document.getElementById('runBtn').disabled = on;
    document.getElementById('runSpinner').classList.toggle('hidden', !on);
    document.getElementById('runIcon').classList.toggle('hidden', on);
    document.getElementById('runLabel').textContent = on ? 'Loading...' : 'Search';
}

/* ══════════ PROGRESS ══════════ */
function buildProgressRows(urls) {
    document.getElementById('progressPanel').classList.remove('hidden');
    document.getElementById('statusRows').innerHTML = urls.map((u, i) =>
        `<div class="s-row" id="sr-${i}">
            <span id="sri-${i}" class="w-5 text-center text-sm flex-shrink-0">⏳</span>
            <span class="text-gray-400 text-xs mono truncate flex-1" title="${escHtml(u)}">${escHtml(shortUrl(u))}</span>
            <span class="text-gray-600 text-xs mono flex-shrink-0" id="srt-${i}">waiting</span>
        </div>`
    ).join('');
    updateProg(0, urls.length);
}

function setRow(i, st, msg) {
    const row = document.getElementById(`sr-${i}`);
    if (!row) return;
    row.className = `s-row ${st}`;
    if (st === 'loading') row.classList.add('shimmer-row');
    document.getElementById(`sri-${i}`).textContent = {loading:'⏳', done:'✅', error:'❌'}[st] || '⏳';
    document.getElementById(`srt-${i}`).textContent = msg || st;
}

function updateProg(done, total) {
    document.getElementById('progFill').style.width = total ? (done/total*100)+'%' : '0%';
    document.getElementById('progLabel').textContent = `${done} / ${total}`;
}

async function runParallel(urls, workers) {
    let done = 0, active = 0;
    const queue = urls.map((u, i) => ({u, i}));
    await new Promise(resolve => {
        function spawn() {
            while (active < workers && queue.length) {
                const {u, i} = queue.shift();
                active++;
                setRow(i, 'loading', 'fetching...');
                fetchApi(u)
                    .then(d => {
                        if (d.status === 'success' && d.videos?.length) {
                            allVideos.push(...d.videos);
                            setRow(i, 'done', `${d.videos.length} video`);
                        } else setRow(i, 'error', (d.message || 'error').slice(0,24));
                    })
                    .catch(e => setRow(i, 'error', e.message.slice(0,24)))
                    .finally(() => {
                        active--; done++;
                        updateProg(done, urls.length);
                        if (done === urls.length) resolve();
                        else spawn();
                    });
            }
        }
        spawn();
    });
}

async function runSequential(urls) {
    for (let i = 0; i < urls.length; i++) {
        setRow(i, 'loading', 'fetching...');
        try {
            const d = await fetchApi(urls[i]);
            if (d.status === 'success' && d.videos?.length) {
                allVideos.push(...d.videos);
                setRow(i, 'done', `${d.videos.length} video`);
            } else setRow(i, 'error', (d.message || 'error').slice(0,24));
        } catch(e) { setRow(i, 'error', e.message.slice(0,24)); }
        updateProg(i+1, urls.length);
    }
}

/* ══════════ RENDER ══════════ */
function renderStats(info) {
    if (!info) return;
    document.getElementById('stFiles').textContent = info.total_files || 0;
    document.getElementById('stVideos').textContent = info.total_videos || 0;
    document.getElementById('stSize').textContent = (info.total_size_mb||0).toFixed(2) + ' MB';
    document.getElementById('stFolders').textContent = info.folders_scanned || 0;
    document.getElementById('statsBar').classList.remove('hidden');
}

function renderVideos(videos) {
    document.getElementById('resultsTitle').textContent = `📹 ${videos.length} video${videos.length!==1?'s':''} ditemukan`;
    const grid = document.getElementById('videosGrid');
    grid.innerHTML = '';
    const watchUrls = [];
    videos.forEach((v, i) => {
        grid.appendChild(makeCard(v, i));
        if (v.status !== 'error' && v.download?.url) watchUrls.push(v.download.url);
    });
    document.getElementById('urlList').value = watchUrls.join('\n');
    document.getElementById('urlCount').textContent = `${watchUrls.length} URL`;
    document.getElementById('resultsSection').classList.remove('hidden');
}

function makeCard(v, idx) {
    const err = v.status === 'error';
    const name = v.name || 'Unknown';
    const videoUrl = v.download?.url || '#';
    const direct = v.download?.direct_download || videoUrl;
    const thumb = v.download?.thumbnail || '';
    const mb = (v.size_mb || 0).toFixed(1);
    const depth = v.depth || 0;

    const card = document.createElement('div');
    card.className = `card-hover glass rounded-2xl overflow-hidden border ${err ? 'border-red-500/30' : 'border-purple-500/10'} fade-in`;
    card.style.animationDelay = (idx * 0.035) + 's';

    const thumbHtml = !err && thumb
        ? `<img src="${escHtml(thumb)}" alt="${escHtml(name)}" class="w-full h-44 object-cover group-hover:scale-110 transition-transform duration-500" onerror="this.parentElement.innerHTML='<div class=\\'w-full h-44 bg-gray-800/50 flex items-center justify-center\\'><svg class=\\'w-10 h-10 text-gray-600\\' fill=\\'none\\' stroke=\\'currentColor\\' viewBox=\\'0 0 24 24\\'><path d=\\'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z\\' stroke-width=\\'1.5\\'/></svg></div>'">`
        : `<div class="w-full h-44 bg-gray-800/50 flex items-center justify-center"><svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" stroke-width="1.5"/></svg></div>`;

    card.innerHTML = `
        <a href="${err ? '#' : escHtml(videoUrl)}" target="_blank" rel="noopener noreferrer"
           class="block relative group overflow-hidden ${err ? 'pointer-events-none' : ''}">
            ${thumbHtml}
            ${!err ? `<div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <svg class="w-11 h-11 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.5"/><polygon points="10 8 16 12 10 16 10 8" fill="currentColor"/></svg>
            </div>` : ''}
            ${v.video_info?.duration_formatted ? `<div class="absolute bottom-2 right-2 bg-black/80 px-2 py-0.5 rounded-lg text-white text-xs mono">${escHtml(v.video_info.duration_formatted)}</div>` : ''}
            ${depth > 0 ? `<div class="absolute top-2 left-2 bg-blue-600/80 px-2 py-0.5 rounded-lg text-white text-xs">L${depth}</div>` : ''}
        </a>
        <div class="p-4 space-y-3">
            <h3 class="text-white font-bold text-sm line-clamp-2 min-h-[2.5rem]" title="${escHtml(name)}">${escHtml(name)}</h3>
            ${err
                ? `<div class="bg-red-500/10 border border-red-500/30 rounded-lg px-3 py-2 text-red-300 text-xs">❌ ${escHtml(v.error || 'Error')}</div>`
                : `<div class="flex flex-wrap gap-1.5 text-xs mono">
                    ${v.video_info?.resolution?.label ? `<span class="bg-purple-500/15 text-purple-300 px-2 py-1 rounded-full border border-purple-500/20">${escHtml(v.video_info.resolution.label)}</span>` : ''}
                    <span class="bg-blue-500/15 text-blue-300 px-2 py-1 rounded-full border border-blue-500/20">💾 ${mb} MB</span>
                    ${v.video_info?.fps ? `<span class="bg-green-500/15 text-green-300 px-2 py-1 rounded-full border border-green-500/20">${escHtml(v.video_info.fps)} fps</span>` : ''}
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="${escHtml(videoUrl)}" target="_blank" rel="noopener noreferrer"
                       class="flex items-center justify-center gap-1.5 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white rounded-xl text-xs font-bold transition-all">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg> Tonton
                    </a>
                    <a href="${escHtml(direct)}" target="_blank" rel="noopener noreferrer"
                       class="flex items-center justify-center gap-1.5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl text-xs font-bold transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke-width="2"/><polyline points="7 10 12 15 17 10" stroke-width="2"/><line x1="12" y1="15" x2="12" y2="3" stroke-width="2"/></svg> Download
                    </a>
                </div>`
            }
        </div>`;
    return card;
}

/* ══════════ SHARE ══════════ */
function openShare() { document.getElementById('shareModal').classList.remove('hidden'); }
function closeShare() { document.getElementById('shareModal').classList.add('hidden'); document.getElementById('shareStatus').classList.add('hidden'); }

async function doShare(type) {
    const urls = document.getElementById('urlList').value;
    const st = document.getElementById('shareStatus');
    const flash = m => { st.textContent = m; st.classList.remove('hidden'); setTimeout(() => st.classList.add('hidden'), 3000); };
    if (type === 'copy') { await navigator.clipboard.writeText(urls); flash('✅ Disalin!'); }
    else if (type === 'txt') { saveTxt(); flash('✅ Mengunduh .txt'); }
    else if (type === 'json') { saveJson(); flash('✅ Mengunduh .json'); }
    else if (type === 'native') {
        if (navigator.share) { try { await navigator.share({ title: 'UC Share URLs', text: urls }); } catch {} }
        else flash('❌ Tidak tersedia di browser ini');
    }
}

/* ══════════ UTILS ══════════ */
async function fetchApi(url) {
    const r = await fetch(`https://ucweb-five.vercel.app/api/?url=${encodeURIComponent(url)}`);
    return r.json();
}

function showError(msg) {
    document.getElementById('errorTxt').textContent = msg;
    document.getElementById('errorMsg').classList.remove('hidden');
    document.getElementById('emptyState').classList.add('hidden');
}

function copyAllUrls() {
    const v = document.getElementById('urlList').value;
    if (!v.trim()) return;
    navigator.clipboard.writeText(v).then(() => {
        const el = document.getElementById('copyTxt');
        el.textContent = '✅ Tersalin!';
        setTimeout(() => el.textContent = 'Copy Semua', 2000);
    });
}

function saveTxt() {
    const v = document.getElementById('urlList').value;
    if (!v.trim()) return;
    dlBlob(new Blob([v], { type: 'text/plain' }), 'uc-share-urls.txt');
}

function saveJson() {
    dlBlob(new Blob([JSON.stringify(allVideos, null, 2)], { type: 'application/json' }), 'uc-share-videos.json');
}

function dlBlob(blob, name) {
    const a = Object.assign(document.createElement('a'), { href: URL.createObjectURL(blob), download: name });
    a.click(); URL.revokeObjectURL(a.href);
}

function escHtml(s) {
    const d = document.createElement('div');
    d.textContent = String(s ?? '');
    return d.innerHTML;
}

function shortUrl(url) {
    try { const p = new URL(url).pathname.split('/').filter(Boolean); return (p[p.length-1] || url).slice(-20); }
    catch { return String(url).slice(-20); }
}

// Ctrl+Enter to submit
document.getElementById('mainInput').addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') { e.preventDefault(); runDownload(); }
});
</script>
</body>
</html>
