<?php
/**
 * UC Share Video Player with Built-in Proxy
 */

// ========== PROXY MODE ==========
if (isset($_GET['proxy']) && $_GET['proxy'] === '1') {
    $videoUrl = isset($_GET['url']) ? $_GET['url'] : '';
    
    if (empty($videoUrl)) {
        http_response_code(400);
        die('Video URL required');
    }
    
    // Remove download-forcing parameters
    $videoUrl = preg_replace('/&?callback=[^&]*/', '', $videoUrl);
    $videoUrl = preg_replace('/&?callback-var=[^&]*/', '', $videoUrl);
    
    // Set no time limit
    set_time_limit(0);
    
    // Get Range header for seeking support
    $range = isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : '';
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $videoUrl,
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_BUFFERSIZE => 128 * 1024,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_HTTPHEADER => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Referer: https://drive.ucweb.com/',
            'Accept: video/mp4,video/*,*/*',
            ($range ? $range : '')
        ],
        
        // Forward headers
        CURLOPT_HEADERFUNCTION => function($curl, $header) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            
            if (count($header) < 2) return $len;
            
            $name = strtolower(trim($header[0]));
            $value = trim($header[1]);
            
            switch ($name) {
                case 'content-type':
                    header('Content-Type: ' . $value);
                    break;
                case 'content-length':
                    header('Content-Length: ' . $value);
                    break;
                case 'content-range':
                    header('Content-Range: ' . $value);
                    header('HTTP/1.1 206 Partial Content');
                    break;
                case 'accept-ranges':
                    header('Accept-Ranges: ' . $value);
                    break;
            }
            
            return $len;
        },
        
        // Stream output
        CURLOPT_WRITEFUNCTION => function($curl, $data) {
            echo $data;
            if (ob_get_level() > 0) ob_flush();
            flush();
            return strlen($data);
        }
    ]);
    
    // Set streaming headers
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('X-Accel-Buffering: no');
    
    // Execute
    curl_exec($ch);
    curl_close($ch);
    exit;
}

// ========== PLAYER MODE ==========
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

// Create proxy URL
$proxyUrl = 'player.php?proxy=1&url=' . urlencode($videoUrl);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($videoName); ?> - UC Share Player</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 16px;
            background: #000;
        }
        .video-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>

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
                        <p class="text-gray-400 text-sm">
                            <span class="inline-flex items-center gap-2">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                Streaming Ready • Klik play untuk menonton
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Video Player -->
        <div class="mb-6">
            <div class="bg-white/5 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-4">
                <div class="video-container">
                    <video 
                        id="videoPlayer"
                        controls 
                        preload="metadata" 
                        poster="<?php echo htmlspecialchars($thumbnail); ?>"
                        playsinline
                    >
                        <source src="<?php echo htmlspecialchars($proxyUrl); ?>" type="video/mp4">
                        Browser Anda tidak mendukung video player.
                    </video>
                </div>
                
                <!-- Loading Indicator -->
                <div id="loadingIndicator" class="mt-4 hidden">
                    <div class="flex items-center justify-center gap-3 text-purple-400">
                        <svg class="animate-spin w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke-width="4" stroke-dasharray="60" stroke-dashoffset="15"/>
                        </svg>
                        <span class="text-sm">Loading video...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            
            <!-- Download -->
            <a href="<?php echo htmlspecialchars($videoUrl); ?>" target="_blank" download class="block bg-white/5 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-6 hover:border-purple-500/50 transition-all group">
                <div class="flex items-center gap-4">
                    <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-4 rounded-xl group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke-width="2"/>
                            <polyline points="7 10 12 15 17 10" stroke-width="2"/>
                            <line x1="12" y1="15" x2="12" y2="3" stroke-width="2"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-lg">Download</h3>
                        <p class="text-gray-400 text-sm">Unduh video ini</p>
                    </div>
                </div>
            </a>

            <!-- Copy Link -->
            <button onclick="copyVideoUrl()" class="block bg-white/5 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-6 hover:border-purple-500/50 transition-all group text-left">
                <div class="flex items-center gap-4">
                    <div class="bg-gradient-to-r from-blue-600 to-cyan-600 p-4 rounded-xl group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" stroke-width="2"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-lg">Salin Link</h3>
                        <p class="text-gray-400 text-sm">Copy URL video</p>
                    </div>
                </div>
            </button>

            <!-- Share -->
            <button onclick="shareVideo()" class="block bg-white/5 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-6 hover:border-purple-500/50 transition-all group text-left">
                <div class="flex items-center gap-4">
                    <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-4 rounded-xl group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" stroke-width="2"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-lg">Share</h3>
                        <p class="text-gray-400 text-sm">Bagikan video</p>
                    </div>
                </div>
            </button>

        </div>

        <!-- Video Info -->
        <div class="bg-white/5 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-6 mb-6">
            <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2"/>
                </svg>
                Status Video
            </h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white/5 rounded-lg p-4">
                    <div class="text-gray-400 text-sm mb-1">Status</div>
                    <div class="text-white font-bold" id="playStatus">Ready</div>
                </div>
                <div class="bg-white/5 rounded-lg p-4">
                    <div class="text-gray-400 text-sm mb-1">Durasi</div>
                    <div class="text-white font-bold" id="videoDuration">--:--</div>
                </div>
                <div class="bg-white/5 rounded-lg p-4">
                    <div class="text-gray-400 text-sm mb-1">Waktu</div>
                    <div class="text-white font-bold" id="currentTime">00:00</div>
                </div>
                <div class="bg-white/5 rounded-lg p-4">
                    <div class="text-gray-400 text-sm mb-1">Buffered</div>
                    <div class="text-white font-bold" id="buffered">0%</div>
                </div>
            </div>
        </div>

        <!-- Tips -->
        <div class="bg-gradient-to-r from-purple-500/10 to-pink-500/10 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-6">
            <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-width="2"/>
                </svg>
                Tips
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex gap-3">
                    <span class="text-2xl">⌨️</span>
                    <div>
                        <div class="text-white font-semibold">Keyboard</div>
                        <div class="text-gray-400">Space = Play/Pause • ← → = Skip 10s</div>
                    </div>
                </div>
                <div class="flex gap-3">
                    <span class="text-2xl">🎬</span>
                    <div>
                        <div class="text-white font-semibold">Streaming</div>
                        <div class="text-gray-400">Video di-stream via proxy server</div>
                    </div>
                </div>
                <div class="flex gap-3">
                    <span class="text-2xl">📱</span>
                    <div>
                        <div class="text-white font-semibold">Mobile</div>
                        <div class="text-gray-400">Support all devices & browsers</div>
                    </div>
                </div>
                <div class="flex gap-3">
                    <span class="text-2xl">⚡</span>
                    <div>
                        <div class="text-white font-semibold">Speed</div>
                        <div class="text-gray-400">Klik kanan video → Playback speed</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-gray-500 text-sm">
                Powered by UC Share API • Streaming via Proxy • Made with ❤️
            </p>
        </div>

    </div>
</div>

<script>
const video = document.getElementById('videoPlayer');
const loadingIndicator = document.getElementById('loadingIndicator');

// Video event listeners
video.addEventListener('loadstart', () => {
    loadingIndicator.classList.remove('hidden');
    document.getElementById('playStatus').textContent = 'Loading...';
});

video.addEventListener('loadedmetadata', () => {
    loadingIndicator.classList.add('hidden');
    const duration = formatTime(video.duration);
    document.getElementById('videoDuration').textContent = duration;
    document.getElementById('playStatus').textContent = 'Ready';
});

video.addEventListener('playing', () => {
    document.getElementById('playStatus').textContent = 'Playing';
});

video.addEventListener('pause', () => {
    document.getElementById('playStatus').textContent = 'Paused';
});

video.addEventListener('timeupdate', () => {
    document.getElementById('currentTime').textContent = formatTime(video.currentTime);
});

video.addEventListener('progress', () => {
    if (video.buffered.length > 0) {
        const bufferedEnd = video.buffered.end(video.buffered.length - 1);
        const duration = video.duration;
        const bufferedPercent = (bufferedEnd / duration) * 100;
        document.getElementById('buffered').textContent = Math.round(bufferedPercent) + '%';
    }
});

video.addEventListener('ended', () => {
    document.getElementById('playStatus').textContent = 'Ended';
});

video.addEventListener('error', (e) => {
    console.error('Video error:', e);
    document.getElementById('playStatus').innerHTML = '<span class="text-red-400">Error</span>';
    loadingIndicator.classList.add('hidden');
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

function copyVideoUrl() {
    const url = '<?php echo addslashes($videoUrl); ?>';
    navigator.clipboard.writeText(url).then(() => {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = `
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-4 rounded-xl">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M5 13l4 4L19 7" stroke-width="2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg">Tersalin!</h3>
                    <p class="text-gray-400 text-sm">Link berhasil dicopy</p>
                </div>
            </div>
        `;
        setTimeout(() => { btn.innerHTML = originalHtml; }, 2000);
    });
}

function shareVideo() {
    const shareData = {
        title: '<?php echo addslashes($videoName); ?>',
        text: 'Tonton video ini!',
        url: window.location.href
    };
    
    if (navigator.share) {
        navigator.share(shareData);
    } else {
        copyVideoUrl();
    }
}
</script>

</body>
</html>
