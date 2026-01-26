<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UC Share Downloader</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(168, 85, 247, 0.4);
        }
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-top: 4px solid #a855f7;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 p-6">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="flex items-center justify-center gap-4 mb-4">
                <svg class="w-16 h-16 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <h1 class="text-5xl font-bold text-white">UC Share Downloader</h1>
            </div>
            <p class="text-gray-300 text-lg">Download semua video dari UC Share dengan mudah</p>
        </div>

        <!-- Search Form -->
        <div class="mb-10 max-w-5xl mx-auto">
            <form id="searchForm" class="relative">
                <input
                    type="text"
                    id="urlInput"
                    placeholder="Masukkan URL UC Share (contoh: https://drive.ucweb.com/s/079eb21d6f504)"
                    class="w-full px-6 py-5 pr-36 rounded-2xl bg-white/10 backdrop-blur-lg border-2 border-purple-500/30 text-white text-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                    required
                />
                <button
                    type="submit"
                    id="searchBtn"
                    class="absolute right-2 top-2 bottom-2 px-8 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg"
                >
                    <svg id="searchIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" stroke-width="2"/>
                        <path d="m21 21-4.35-4.35" stroke-width="2"/>
                    </svg>
                    <div id="loadingSpinner" class="spinner hidden"></div>
                    <span id="searchText">Search</span>
                </button>
            </form>
            <p class="text-gray-400 text-sm mt-3 text-center">
                💡 API akan otomatis mengambil <strong class="text-purple-400">semua video</strong> dari folder dan subfolder
            </p>
        </div>

        <!-- Share Info -->
        <div id="shareInfo" class="max-w-5xl mx-auto mb-8 hidden">
            <div class="bg-white/5 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
                    <div>
                        <div class="text-gray-400 text-sm mb-1">Total Files</div>
                        <div class="text-white text-2xl font-bold" id="totalFiles">0</div>
                    </div>
                    <div>
                        <div class="text-gray-400 text-sm mb-1">Total Videos</div>
                        <div class="text-purple-400 text-2xl font-bold" id="totalVideos">0</div>
                    </div>
                    <div>
                        <div class="text-gray-400 text-sm mb-1">Total Size</div>
                        <div class="text-pink-400 text-2xl font-bold" id="totalSize">0 MB</div>
                    </div>
                    <div>
                        <div class="text-gray-400 text-sm mb-1">Folders Scanned</div>
                        <div class="text-green-400 text-2xl font-bold" id="foldersScanned">0</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fetch Summary -->
        <div id="fetchSummary" class="max-w-5xl mx-auto mb-8 hidden">
            <div class="bg-gradient-to-r from-green-500/20 to-blue-500/20 backdrop-blur-lg rounded-2xl border-2 border-green-500/30 p-5">
                <div class="flex items-center justify-center gap-6 text-center">
                    <div>
                        <div class="text-gray-300 text-sm mb-1">Total Processed</div>
                        <div class="text-white text-xl font-bold" id="summaryTotal">0</div>
                    </div>
                    <div class="h-10 w-px bg-white/20"></div>
                    <div>
                        <div class="text-gray-300 text-sm mb-1">Success</div>
                        <div class="text-green-400 text-xl font-bold" id="summarySuccess">0</div>
                    </div>
                    <div class="h-10 w-px bg-white/20"></div>
                    <div>
                        <div class="text-gray-300 text-sm mb-1">Failed</div>
                        <div class="text-red-400 text-xl font-bold" id="summaryFailed">0</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <div id="errorMessage" class="max-w-5xl mx-auto mb-8 hidden">
            <div class="bg-red-500/20 border-2 border-red-500/50 rounded-2xl px-6 py-4 text-red-200 backdrop-blur-lg">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                        <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                    </svg>
                    <span id="errorText"></span>
                </div>
            </div>
        </div>

        <!-- Videos Grid -->
        <div id="videosContainer" class="hidden space-y-8">
            <div class="flex flex-wrap items-center justify-between gap-4 px-2">
                <h2 class="text-3xl font-bold text-white" id="videosTitle">📹 Found 0 videos</h2>
            </div>

            <div id="videosGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <!-- Videos will be inserted here -->
            </div>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="text-center py-20">
            <svg class="w-32 h-32 text-gray-600 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h3 class="text-gray-300 text-2xl font-bold mb-3">Siap untuk download?</h3>
            <p class="text-gray-400 text-lg mb-2">Masukkan URL UC Share di atas untuk memulai</p>
            <p class="text-gray-500 text-sm">Contoh: https://drive.ucweb.com/s/079eb21d6f504</p>
            
            <div class="mt-12 max-w-3xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white/5 backdrop-blur rounded-xl p-6 border border-purple-500/20">
                    <div class="text-4xl mb-3">🚀</div>
                    <h4 class="text-white font-bold mb-2">Auto Scan</h4>
                    <p class="text-gray-400 text-sm">Otomatis scan semua subfolder</p>
                </div>
                <div class="bg-white/5 backdrop-blur rounded-xl p-6 border border-purple-500/20">
                    <div class="text-4xl mb-3">📦</div>
                    <h4 class="text-white font-bold mb-2">Batch Download</h4>
                    <p class="text-gray-400 text-sm">Download semua video sekaligus</p>
                </div>
                <div class="bg-white/5 backdrop-blur rounded-xl p-6 border border-purple-500/20">
                    <div class="text-4xl mb-3">⚡</div>
                    <h4 class="text-white font-bold mb-2">Super Fast</h4>
                    <p class="text-gray-400 text-sm">Proses download cepat & mudah</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-16 pb-8">
            <p class="text-gray-500 text-sm">Made with ❤️ using UC Share API</p>
        </div>

    </div>
</div>

<script>
document.getElementById('searchForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const urlInput = document.getElementById('urlInput');
    const url = urlInput.value.trim();
    
    if (!url) {
        showError('Silakan masukkan URL UC Share');
        return;
    }
    
    // Show loading
    setLoading(true);
    hideAll();
    
    try {
        const apiUrl = `https://ucweb-five.vercel.app/api/?url=${encodeURIComponent(url)}`;
        const response = await fetch(apiUrl);
        const data = await response.json();
        
        setLoading(false);
        
        if (data.status === 'success' && data.videos && data.videos.length > 0) {
            displayVideos(data);
        } else {
            showError(data.message || 'Tidak ada video yang ditemukan');
        }
    } catch (error) {
        setLoading(false);
        showError('Gagal mengambil data dari API: ' + error.message);
    }
});

function setLoading(loading) {
    const searchBtn = document.getElementById('searchBtn');
    const searchIcon = document.getElementById('searchIcon');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const searchText = document.getElementById('searchText');
    
    if (loading) {
        searchBtn.disabled = true;
        searchIcon.classList.add('hidden');
        loadingSpinner.classList.remove('hidden');
        searchText.textContent = 'Loading...';
    } else {
        searchBtn.disabled = false;
        searchIcon.classList.remove('hidden');
        loadingSpinner.classList.add('hidden');
        searchText.textContent = 'Search';
    }
}

function hideAll() {
    document.getElementById('shareInfo').classList.add('hidden');
    document.getElementById('fetchSummary').classList.add('hidden');
    document.getElementById('errorMessage').classList.add('hidden');
    document.getElementById('videosContainer').classList.add('hidden');
    document.getElementById('emptyState').classList.add('hidden');
}

function showError(message) {
    hideAll();
    document.getElementById('errorText').textContent = message;
    document.getElementById('errorMessage').classList.remove('hidden');
}

function displayVideos(data) {
    hideAll();
    
    // Show share info
    if (data.share_info) {
        const info = data.share_info;
        document.getElementById('totalFiles').textContent = info.total_files || 0;
        document.getElementById('totalVideos').textContent = info.total_videos || 0;
        document.getElementById('totalSize').textContent = (info.total_size_mb || 0).toFixed(2) + ' MB';
        document.getElementById('foldersScanned').textContent = info.folders_scanned || 0;
        document.getElementById('shareInfo').classList.remove('hidden');
    }
    
    // Show fetch summary
    if (data.fetch_summary) {
        const summary = data.fetch_summary;
        document.getElementById('summaryTotal').textContent = summary.total || 0;
        document.getElementById('summarySuccess').textContent = summary.success || 0;
        document.getElementById('summaryFailed').textContent = summary.failed || 0;
        document.getElementById('fetchSummary').classList.remove('hidden');
    }
    
    // Show videos
    const videos = data.videos || [];
    const videosTitle = document.getElementById('videosTitle');
    videosTitle.textContent = `📹 Found ${videos.length} video${videos.length !== 1 ? 's' : ''}`;
    
    const videosGrid = document.getElementById('videosGrid');
    videosGrid.innerHTML = '';
    
    videos.forEach((video, index) => {
        const card = createVideoCard(video, index);
        videosGrid.appendChild(card);
    });
    
    document.getElementById('videosContainer').classList.remove('hidden');
}

function createVideoCard(video, index) {
    const isError = video.status === 'error';
    const name = video.name || 'Unknown';
    const thumbnail = video.download?.thumbnail || '';
    const videoUrl = video.download?.url || '#';
    const directDownload = video.download?.direct_download || videoUrl;
    const sizeMb = video.size_mb || 0;
    const depth = video.depth || 0;
    const path = video.path || '';
    
    const card = document.createElement('div');
    card.className = `card-hover bg-white/5 backdrop-blur-lg rounded-2xl overflow-hidden border-2 ${isError ? 'border-red-500/50' : 'border-purple-500/20'}`;
    
    // Create player URL - UPDATED TO USE player.php
    const playerUrl = `player.php?url=${encodeURIComponent(videoUrl)}&name=${encodeURIComponent(name)}&thumb=${encodeURIComponent(thumbnail)}&back=index.html`;
    
    const thumbnailHtml = !isError && thumbnail ? 
        `<img src="${escapeHtml(thumbnail)}" alt="${escapeHtml(name)}" class="w-full h-48 object-cover group-hover:scale-110 transition-transform duration-500" onerror="this.parentElement.innerHTML='<div class=\\'w-full h-48 bg-gray-700 flex items-center justify-center\\'><svg class=\\'w-16 h-16 text-gray-500\\' fill=\\'none\\' stroke=\\'currentColor\\' viewBox=\\'0 0 24 24\\'><path d=\\'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z\\' stroke-width=\\'2\\'/></svg></div>';">` :
        `<div class="w-full h-48 bg-gray-700 flex items-center justify-center"><svg class="w-16 h-16 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" stroke-width="2"/></svg></div>`;
    
    const durationBadge = !isError && video.video_info?.duration_formatted ? 
        `<div class="absolute top-3 right-3 bg-black/80 backdrop-blur px-3 py-1.5 rounded-lg text-white text-sm font-bold">⏱️ ${escapeHtml(video.video_info.duration_formatted)}</div>` : '';
    
    const levelBadge = !isError && depth > 0 ? 
        `<div class="absolute top-3 left-3 bg-blue-600/80 backdrop-blur px-3 py-1.5 rounded-lg text-white text-xs font-bold">📁 Level ${depth}</div>` : '';
    
    const pathInfo = !isError && path && path !== '/' + name ? 
        `<div class="text-gray-400 text-xs truncate" title="${escapeHtml(path)}">📂 ${escapeHtml(path)}</div>` : '';
    
    const errorContent = isError ? 
        `<div class="bg-red-500/20 border border-red-500/50 rounded-lg px-3 py-2 text-red-300 text-sm">❌ ${escapeHtml(video.error || 'Unknown error')}</div>` : '';
    
    const resolutionBadge = video.video_info?.resolution?.label ? 
        `<span class="bg-purple-500/30 text-purple-200 px-3 py-1.5 rounded-full border border-purple-400/30">🎬 ${escapeHtml(video.video_info.resolution.label)}</span>` : '';
    
    const fpsBadge = video.video_info?.fps ? 
        `<span class="bg-green-500/30 text-green-200 px-3 py-1.5 rounded-full border border-green-400/30">🎞️ ${escapeHtml(video.video_info.fps)} FPS</span>` : '';
    
    // UPDATED: Tombol Tonton sekarang membuka player.php
    const actionButtons = !isError ? `
        <div class="grid grid-cols-2 gap-2">
            <a href="${playerUrl}" class="block w-full bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white py-3 rounded-xl font-bold transition-all text-center flex items-center justify-center gap-2 shadow-lg hover:shadow-blue-500/50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><polygon points="10 8 16 12 10 16 10 8" fill="currentColor"/></svg>
                <span>Tonton</span>
            </a>
            <a href="${escapeHtml(directDownload)}" target="_blank" rel="noopener noreferrer" class="block w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white py-3 rounded-xl font-bold transition-all text-center flex items-center justify-center gap-2 shadow-lg hover:shadow-purple-500/50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke-width="2"/><polyline points="7 10 12 15 17 10" stroke-width="2"/><line x1="12" y1="15" x2="12" y2="3" stroke-width="2"/></svg>
                <span>Download</span>
            </a>
        </div>` : '';
    
    card.innerHTML = `
        <a href="${isError ? '#' : playerUrl}" class="block relative group overflow-hidden ${isError ? 'pointer-events-none' : ''}">
            ${thumbnailHtml}
            ${!isError ? '<div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center"><svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><polygon points="10 8 16 12 10 16 10 8" fill="currentColor"/></svg></div>' : ''}
            ${durationBadge}
            ${levelBadge}
        </a>
        <div class="p-5 space-y-4">
            <h3 class="text-white font-bold text-base line-clamp-2 min-h-[3rem]" title="${escapeHtml(name)}">${escapeHtml(name)}</h3>
            ${pathInfo}
            ${errorContent}
            ${!isError ? `
                <div class="flex flex-wrap gap-2 text-xs font-semibold">
                    ${resolutionBadge}
                    <span class="bg-blue-500/30 text-blue-200 px-3 py-1.5 rounded-full border border-blue-400/30">💾 ${sizeMb.toFixed(2)} MB</span>
                    ${fpsBadge}
                </div>
                ${actionButtons}
            ` : ''}
        </div>
    `;
    
    return card;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

</body>
</html>
