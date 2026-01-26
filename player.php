<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UC Share Video Player</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://vjs.zencdn.net/8.6.1/video-js.css" rel="stylesheet" />
    <style>
        .video-js {
            width: 100%;
            height: 100%;
        }
        .video-js .vjs-big-play-button {
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            width: 80px;
            height: 80px;
            font-size: 48px;
            border: 3px solid #a855f7;
            background-color: rgba(168, 85, 247, 0.8);
        }
        .video-js .vjs-big-play-button:hover {
            background-color: rgba(168, 85, 247, 1);
        }
        .player-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
            background: #000;
            border-radius: 16px;
            overflow: hidden;
        }
        .player-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>

<?php
$videoUrl = isset($_GET['url']) ? $_GET['url'] : '';
$videoName = isset($_GET['name']) ? $_GET['name'] : 'UC Share Video';
$thumbnail = isset($_GET['thumb']) ? $_GET['thumb'] : '';
$backUrl = isset($_GET['back']) ? $_GET['back'] : 'index.html';

if (empty($videoUrl)) {
    echo '<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 flex items-center justify-center p-6">
            <div class="text-center">
                <svg class="w-24 h-24 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                </svg>
                <h1 class="text-3xl font-bold text-white mb-4">Video URL Tidak Ditemukan</h1>
                <p class="text-gray-400 mb-6">Silakan akses halaman ini melalui link video yang valid</p>
                <a href="' . htmlspecialchars($backUrl) . '" class="inline-block px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-bold hover:from-purple-700 hover:to-pink-700 transition-all">
                    Kembali ke Beranda
                </a>
            </div>
          </div>';
    exit;
}
?>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 p-6">
    <div class="max-w-6xl mx-auto">
        
        <!-- Header -->
        <div class="mb-6">
            <a href="<?php echo htmlspecialchars($backUrl); ?>" class="inline-flex items-center gap-2 text-purple-400 hover:text-purple-300 transition-colors mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="font-semibold">Kembali</span>
            </a>
            
            <div class="bg-white/5 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-12 h-12 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            <polygon points="10 8 16 12 10 16 10 8" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl font-bold text-white mb-2 break-words">
                            <?php echo htmlspecialchars($videoName); ?>
                        </h1>
                        <p class="text-gray-400 text-sm">UC Share Video Player</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Video Player -->
        <div class="mb-6">
            <div class="bg-white/5 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-4 overflow-hidden">
                <div class="player-container">
                    <video
                        id="videoPlayer"
                        class="video-js vjs-big-play-centered"
                        controls
                        preload="auto"
                        poster="<?php echo htmlspecialchars($thumbnail); ?>"
                        data-setup='{}'
                    >
                        <source src="<?php echo htmlspecialchars($videoUrl); ?>" type="video/mp4">
                        <p class="vjs-no-js">
                            Browser Anda tidak mendukung video HTML5. Silakan gunakan browser modern.
                        </p>
                    </video>
                </div>
            </div>
        </div>

        <!-- Video Controls & Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            
            <!-- Playback Controls -->
            <div class="bg-white/5 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-6">
                <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Kontrol Pemutaran
                </h3>
                
                <div class="space-y-3">
                    <button onclick="changeSpeed(0.5)" class="w-full bg-white/10 hover:bg-white/20 text-white py-2 px-4 rounded-lg transition-all flex items-center justify-between">
                        <span>Kecepatan 0.5x</span>
                        <span class="text-gray-400 text-sm">Lambat</span>
                    </button>
                    <button onclick="changeSpeed(1)" class="w-full bg-white/10 hover:bg-white/20 text-white py-2 px-4 rounded-lg transition-all flex items-center justify-between">
                        <span>Kecepatan 1x</span>
                        <span class="text-purple-400 text-sm">Normal</span>
                    </button>
                    <button onclick="changeSpeed(1.5)" class="w-full bg-white/10 hover:bg-white/20 text-white py-2 px-4 rounded-lg transition-all flex items-center justify-between">
                        <span>Kecepatan 1.5x</span>
                        <span class="text-gray-400 text-sm">Cepat</span>
                    </button>
                    <button onclick="changeSpeed(2)" class="w-full bg-white/10 hover:bg-white/20 text-white py-2 px-4 rounded-lg transition-all flex items-center justify-between">
                        <span>Kecepatan 2x</span>
                        <span class="text-gray-400 text-sm">Sangat Cepat</span>
                    </button>
                </div>
            </div>

            <!-- Download & Share -->
            <div class="bg-white/5 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-6">
                <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Aksi
                </h3>
                
                <div class="space-y-3">
                    <a href="<?php echo htmlspecialchars($videoUrl); ?>" download class="block w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white py-3 px-4 rounded-lg transition-all text-center font-bold flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke-width="2"/>
                            <polyline points="7 10 12 15 17 10" stroke-width="2"/>
                            <line x1="12" y1="15" x2="12" y2="3" stroke-width="2"/>
                        </svg>
                        Download Video
                    </a>
                    
                    <button onclick="copyUrl()" class="w-full bg-white/10 hover:bg-white/20 text-white py-3 px-4 rounded-lg transition-all font-bold flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" stroke-width="2"/>
                        </svg>
                        Salin Link Video
                    </button>
                    
                    <button onclick="toggleFullscreen()" class="w-full bg-white/10 hover:bg-white/20 text-white py-3 px-4 rounded-lg transition-all font-bold flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Layar Penuh
                    </button>
                </div>
            </div>
        </div>

        <!-- Video Info -->
        <div class="bg-white/5 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-6">
            <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Informasi Video
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white/5 rounded-lg p-4">
                    <div class="text-gray-400 text-sm mb-1">Status</div>
                    <div class="text-white font-bold" id="videoStatus">Memuat...</div>
                </div>
                <div class="bg-white/5 rounded-lg p-4">
                    <div class="text-gray-400 text-sm mb-1">Durasi</div>
                    <div class="text-white font-bold" id="videoDuration">--:--</div>
                </div>
                <div class="bg-white/5 rounded-lg p-4">
                    <div class="text-gray-400 text-sm mb-1">Waktu Saat Ini</div>
                    <div class="text-white font-bold" id="videoCurrentTime">00:00</div>
                </div>
            </div>
            
            <div class="mt-4 bg-white/5 rounded-lg p-4">
                <div class="text-gray-400 text-sm mb-2">URL Video</div>
                <div class="text-white text-xs break-all font-mono bg-black/30 p-3 rounded">
                    <?php echo htmlspecialchars($videoUrl); ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-gray-500 text-sm">
                Powered by UC Share API • Made with ❤️
            </p>
        </div>

    </div>
</div>

<!-- Video.js Library -->
<script src="https://vjs.zencdn.net/8.6.1/video.min.js"></script>

<script>
// Initialize Video.js player
const player = videojs('videoPlayer', {
    controls: true,
    autoplay: false,
    preload: 'auto',
    fluid: true,
    responsive: true,
    playbackRates: [0.5, 1, 1.5, 2],
    controlBar: {
        volumePanel: {
            inline: false
        }
    }
});

// Update video info
player.on('loadedmetadata', function() {
    const duration = player.duration();
    document.getElementById('videoDuration').textContent = formatTime(duration);
    document.getElementById('videoStatus').textContent = 'Siap Diputar';
});

player.on('timeupdate', function() {
    const currentTime = player.currentTime();
    document.getElementById('videoCurrentTime').textContent = formatTime(currentTime);
});

player.on('playing', function() {
    document.getElementById('videoStatus').textContent = 'Sedang Diputar';
});

player.on('pause', function() {
    document.getElementById('videoStatus').textContent = 'Dijeda';
});

player.on('ended', function() {
    document.getElementById('videoStatus').textContent = 'Selesai';
});

player.on('error', function() {
    document.getElementById('videoStatus').innerHTML = '<span class="text-red-400">Error - Gagal Memuat Video</span>';
});

// Helper Functions
function formatTime(seconds) {
    if (isNaN(seconds) || seconds === 0) return '00:00';
    
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);
    
    if (h > 0) {
        return `${h}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    }
    return `${m}:${s.toString().padStart(2, '0')}`;
}

function changeSpeed(speed) {
    player.playbackRate(speed);
    
    // Visual feedback
    const buttons = document.querySelectorAll('button[onclick^="changeSpeed"]');
    buttons.forEach(btn => {
        const span = btn.querySelector('span:last-child');
        if (btn.onclick.toString().includes(speed)) {
            span.className = 'text-purple-400 text-sm font-bold';
            span.textContent = '✓ Aktif';
        } else {
            span.className = 'text-gray-400 text-sm';
            const speedText = btn.querySelector('span:first-child').textContent;
            if (speedText.includes('0.5')) span.textContent = 'Lambat';
            else if (speedText.includes('1x')) span.textContent = 'Normal';
            else if (speedText.includes('1.5')) span.textContent = 'Cepat';
            else span.textContent = 'Sangat Cepat';
        }
    });
}

function copyUrl() {
    const url = '<?php echo addslashes($videoUrl); ?>';
    navigator.clipboard.writeText(url).then(() => {
        // Show success message
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Link Disalin!';
        btn.classList.add('bg-green-600');
        
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('bg-green-600');
        }, 2000);
    }).catch(err => {
        alert('Gagal menyalin link: ' + err);
    });
}

function toggleFullscreen() {
    if (player.isFullscreen()) {
        player.exitFullscreen();
    } else {
        player.requestFullscreen();
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    switch(e.key) {
        case ' ':
            e.preventDefault();
            if (player.paused()) {
                player.play();
            } else {
                player.pause();
            }
            break;
        case 'ArrowLeft':
            e.preventDefault();
            player.currentTime(player.currentTime() - 5);
            break;
        case 'ArrowRight':
            e.preventDefault();
            player.currentTime(player.currentTime() + 5);
            break;
        case 'f':
            e.preventDefault();
            toggleFullscreen();
            break;
        case 'm':
            e.preventDefault();
            player.muted(!player.muted());
            break;
    }
});
</script>

</body>
</html>
