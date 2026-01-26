<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UC Share Video Player</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        #videoPlayer {
            width: 100%;
            max-height: 80vh;
            background: #000;
        }
        .player-container {
            position: relative;
            background: #000;
            border-radius: 16px;
            overflow: hidden;
        }
        .controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            padding: 20px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .player-container:hover .controls {
            opacity: 1;
        }
        .progress-bar {
            height: 6px;
            background: rgba(255,255,255,0.3);
            border-radius: 3px;
            cursor: pointer;
            margin-bottom: 10px;
        }
        .progress-filled {
            height: 100%;
            background: #a855f7;
            border-radius: 3px;
            width: 0%;
            transition: width 0.1s;
        }
        .control-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .control-btn:hover {
            background: rgba(168, 85, 247, 0.8);
        }
        .volume-slider {
            width: 80px;
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

// Remove callback parameter from URL to prevent download
$cleanUrl = preg_replace('/&?callback=[^&]*/', '', $videoUrl);
$cleanUrl = preg_replace('/&?callback-var=[^&]*/', '', $cleanUrl);
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
                        poster="<?php echo htmlspecialchars($thumbnail); ?>"
                        preload="metadata"
                        crossorigin="anonymous"
                    >
                        <source src="<?php echo htmlspecialchars($cleanUrl); ?>" type="video/mp4">
                        Browser Anda tidak mendukung video HTML5.
                    </video>
                    
                    <!-- Custom Controls -->
                    <div class="controls">
                        <div class="progress-bar" id="progressBar">
                            <div class="progress-filled" id="progressFilled"></div>
                        </div>
                        
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <button class="control-btn" id="playPauseBtn">
                                    <svg id="playIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <polygon points="5 3 19 12 5 21 5 3" fill="currentColor"/>
                                    </svg>
                                    <svg id="pauseIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <rect x="6" y="4" width="4" height="16" fill="currentColor"/>
                                        <rect x="14" y="4" width="4" height="16" fill="currentColor"/>
                                    </svg>
                                </button>
                                
                                <div class="flex items-center gap-2">
                                    <button class="control-btn" id="muteBtn">
                                        <svg id="volumeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5" fill="currentColor"/>
                                            <path d="M15.54 8.46a5 5 0 010 7.07" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                        <svg id="muteIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5" fill="currentColor"/>
                                            <line x1="23" y1="9" x2="17" y2="15" stroke-width="2"/>
                                            <line x1="17" y1="9" x2="23" y2="15" stroke-width="2"/>
                                        </svg>
                                    </button>
                                    <input type="range" id="volumeSlider" class="volume-slider" min="0" max="100" value="100">
                                </div>
                                
                                <span class="text-white text-sm" id="timeDisplay">0:00 / 0:00</span>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <select id="speedSelect" class="control-btn text-sm">
                                    <option value="0.5">0.5x</option>
                                    <option value="1" selected>1x</option>
                                    <option value="1.5">1.5x</option>
                                    <option value="2">2x</option>
                                </select>
                                
                                <button class="control-btn" id="fullscreenBtn">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Video Controls & Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            
            <!-- Quick Actions -->
            <div class="bg-white/5 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-6">
                <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Aksi Cepat
                </h3>
                
                <div class="space-y-3">
                    <button onclick="skipTime(-10)" class="w-full bg-white/10 hover:bg-white/20 text-white py-2 px-4 rounded-lg transition-all flex items-center justify-between">
                        <span>⏪ Mundur 10 Detik</span>
                    </button>
                    <button onclick="skipTime(10)" class="w-full bg-white/10 hover:bg-white/20 text-white py-2 px-4 rounded-lg transition-all flex items-center justify-between">
                        <span>⏩ Maju 10 Detik</span>
                    </button>
                    <button onclick="restartVideo()" class="w-full bg-white/10 hover:bg-white/20 text-white py-2 px-4 rounded-lg transition-all flex items-center justify-between">
                        <span>🔄 Ulang dari Awal</span>
                    </button>
                </div>
            </div>

            <!-- Download & Share -->
            <div class="bg-white/5 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-6">
                <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Lainnya
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
                        Salin Link
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
                    <div class="text-white font-bold" id="videoStatus">Siap Diputar</div>
                </div>
                <div class="bg-white/5 rounded-lg p-4">
                    <div class="text-gray-400 text-sm mb-1">Durasi</div>
                    <div class="text-white font-bold" id="videoDuration">--:--</div>
                </div>
                <div class="bg-white/5 rounded-lg p-4">
                    <div class="text-gray-400 text-sm mb-1">Buffered</div>
                    <div class="text-white font-bold" id="bufferedInfo">0%</div>
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

<script>
const video = document.getElementById('videoPlayer');
const playPauseBtn = document.getElementById('playPauseBtn');
const playIcon = document.getElementById('playIcon');
const pauseIcon = document.getElementById('pauseIcon');
const muteBtn = document.getElementById('muteBtn');
const volumeIcon = document.getElementById('volumeIcon');
const muteIcon = document.getElementById('muteIcon');
const volumeSlider = document.getElementById('volumeSlider');
const progressBar = document.getElementById('progressBar');
const progressFilled = document.getElementById('progressFilled');
const timeDisplay = document.getElementById('timeDisplay');
const speedSelect = document.getElementById('speedSelect');
const fullscreenBtn = document.getElementById('fullscreenBtn');

// Play/Pause
playPauseBtn.addEventListener('click', togglePlay);
video.addEventListener('click', togglePlay);

function togglePlay() {
    if (video.paused) {
        video.play();
        playIcon.classList.add('hidden');
        pauseIcon.classList.remove('hidden');
        document.getElementById('videoStatus').textContent = 'Sedang Diputar';
    } else {
        video.pause();
        playIcon.classList.remove('hidden');
        pauseIcon.classList.add('hidden');
        document.getElementById('videoStatus').textContent = 'Dijeda';
    }
}

// Mute/Unmute
muteBtn.addEventListener('click', () => {
    video.muted = !video.muted;
    if (video.muted) {
        volumeIcon.classList.add('hidden');
        muteIcon.classList.remove('hidden');
    } else {
        volumeIcon.classList.remove('hidden');
        muteIcon.classList.add('hidden');
    }
});

// Volume
volumeSlider.addEventListener('input', (e) => {
    video.volume = e.target.value / 100;
    video.muted = false;
    volumeIcon.classList.remove('hidden');
    muteIcon.classList.add('hidden');
});

// Progress
video.addEventListener('timeupdate', () => {
    const percent = (video.currentTime / video.duration) * 100;
    progressFilled.style.width = percent + '%';
    timeDisplay.textContent = formatTime(video.currentTime) + ' / ' + formatTime(video.duration);
});

progressBar.addEventListener('click', (e) => {
    const rect = progressBar.getBoundingClientRect();
    const percent = (e.clientX - rect.left) / rect.width;
    video.currentTime = percent * video.duration;
});

// Speed
speedSelect.addEventListener('change', (e) => {
    video.playbackRate = parseFloat(e.target.value);
});

// Fullscreen
fullscreenBtn.addEventListener('click', () => {
    if (document.fullscreenElement) {
        document.exitFullscreen();
    } else {
        document.querySelector('.player-container').requestFullscreen();
    }
});

// Video events
video.addEventListener('loadedmetadata', () => {
    document.getElementById('videoDuration').textContent = formatTime(video.duration);
});

video.addEventListener('progress', () => {
    if (video.buffered.length > 0) {
        const buffered = (video.buffered.end(video.buffered.length - 1) / video.duration) * 100;
        document.getElementById('bufferedInfo').textContent = Math.round(buffered) + '%';
    }
});

video.addEventListener('ended', () => {
    playIcon.classList.remove('hidden');
    pauseIcon.classList.add('hidden');
    document.getElementById('videoStatus').textContent = 'Selesai';
});

video.addEventListener('error', (e) => {
    console.error('Video error:', e);
    document.getElementById('videoStatus').innerHTML = '<span class="text-red-400">Error Loading Video</span>';
});

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    switch(e.key) {
        case ' ':
            e.preventDefault();
            togglePlay();
            break;
        case 'ArrowLeft':
            e.preventDefault();
            skipTime(-10);
            break;
        case 'ArrowRight':
            e.preventDefault();
            skipTime(10);
            break;
        case 'f':
            e.preventDefault();
            fullscreenBtn.click();
            break;
        case 'm':
            e.preventDefault();
            muteBtn.click();
            break;
    }
});

// Helper functions
function formatTime(seconds) {
    if (isNaN(seconds) || seconds === 0) return '0:00';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);
    if (h > 0) {
        return `${h}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    }
    return `${m}:${s.toString().padStart(2, '0')}`;
}

function skipTime(seconds) {
    video.currentTime += seconds;
}

function restartVideo() {
    video.currentTime = 0;
    video.play();
}

function copyUrl() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Link Disalin!';
        btn.classList.add('bg-green-600');
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('bg-green-600');
        }, 2000);
    });
}
</script>

</body>
</html>
