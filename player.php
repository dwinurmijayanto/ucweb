<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UC Share Video Player</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .video-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 */
            height: 0;
            overflow: hidden;
            border-radius: 16px;
        }
        .video-container iframe,
        .video-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
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
                        <p class="text-gray-400 text-sm">UC Share Video Player • Klik play untuk menonton</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Video Player - Simple Embed -->
        <div class="mb-6">
            <div class="bg-white/5 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-4">
                <div class="video-container bg-black">
                    <video 
                        controls 
                        preload="metadata" 
                        poster="<?php echo htmlspecialchars($thumbnail); ?>"
                        controlsList="nodownload"
                        style="width: 100%; height: 100%;"
                    >
                        <source src="<?php echo htmlspecialchars($videoUrl); ?>" type="video/mp4">
                        Browser Anda tidak mendukung video player.
                    </video>
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

            <!-- Open Direct -->
            <a href="<?php echo htmlspecialchars($videoUrl); ?>" target="_blank" class="block bg-white/5 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-6 hover:border-purple-500/50 transition-all group">
                <div class="flex items-center gap-4">
                    <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-4 rounded-xl group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-lg">Buka Langsung</h3>
                        <p class="text-gray-400 text-sm">Tab baru</p>
                    </div>
                </div>
            </a>

        </div>

        <!-- Info & Tips -->
        <div class="bg-gradient-to-r from-purple-500/10 to-pink-500/10 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-6">
            <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Tips Menonton
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start gap-3">
                    <div class="text-2xl">⌨️</div>
                    <div>
                        <div class="text-white font-semibold mb-1">Keyboard Shortcuts</div>
                        <div class="text-gray-400 text-sm">
                            Spasi = Play/Pause<br>
                            ← → = Skip 10s<br>
                            F = Fullscreen
                        </div>
                    </div>
                </div>
                
                <div class="flex items-start gap-3">
                    <div class="text-2xl">📱</div>
                    <div>
                        <div class="text-white font-semibold mb-1">Mobile Friendly</div>
                        <div class="text-gray-400 text-sm">
                            Support touch gestures<br>
                            Pinch to zoom<br>
                            Swipe untuk skip
                        </div>
                    </div>
                </div>
                
                <div class="flex items-start gap-3">
                    <div class="text-2xl">🎬</div>
                    <div>
                        <div class="text-white font-semibold mb-1">Kualitas Video</div>
                        <div class="text-gray-400 text-sm">
                            Auto adjust quality<br>
                            Streaming adaptif<br>
                            Minimal buffering
                        </div>
                    </div>
                </div>
                
                <div class="flex items-start gap-3">
                    <div class="text-2xl">💾</div>
                    <div>
                        <div class="text-white font-semibold mb-1">Download</div>
                        <div class="text-gray-400 text-sm">
                            Klik tombol download<br>
                            Atau klik kanan video<br>
                            "Save video as..."
                        </div>
                    </div>
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
function copyVideoUrl() {
    const url = '<?php echo addslashes($videoUrl); ?>';
    navigator.clipboard.writeText(url).then(() => {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = `
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-4 rounded-xl">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg">Link Disalin!</h3>
                    <p class="text-gray-400 text-sm">Berhasil copy URL</p>
                </div>
            </div>
        `;
        
        setTimeout(() => {
            btn.innerHTML = originalHtml;
        }, 2000);
    }).catch(err => {
        alert('Gagal menyalin link');
    });
}

// Auto play on load (optional)
window.addEventListener('load', () => {
    const video = document.querySelector('video');
    if (video) {
        // Add event listeners
        video.addEventListener('error', (e) => {
            console.error('Video error:', e);
        });
        
        video.addEventListener('loadedmetadata', () => {
            console.log('Video loaded, duration:', video.duration);
        });
    }
});
</script>

</body>
</html>
