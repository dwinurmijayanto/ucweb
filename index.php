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
        .loader {
            border: 3px solid rgba(255, 255, 255, 0.2);
            border-top: 3px solid #a855f7;
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
<?php
$videos = [];
$error = '';
$loading = false;
$shareInfo = null;
$fetchSummary = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = trim($_POST['url']);
    
    if (empty($url)) {
        $error = 'Silakan masukkan URL UC Share';
    } else {
        // Call API lokal (ganti dengan URL API Anda)
        $apiUrl = 'https://ucweb-five.vercel.app/api/?url=' . urlencode($url);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = 'Gagal terhubung ke API. Error: ' . curl_error($ch);
        } else {
            $data = json_decode($response, true);
            
            if ($data && isset($data['status']) && $data['status'] === 'success') {
                $videos = $data['videos'] ?? [];
                $shareInfo = $data['share_info'] ?? null;
                $fetchSummary = $data['fetch_summary'] ?? null;
                
                if (empty($videos)) {
                    $error = 'Tidak ada video yang ditemukan';
                }
            } else {
                $error = $data['message'] ?? 'Gagal mengambil data dari API';
            }
        }
        
        curl_close($ch);
    }
}

$inputUrl = isset($_POST['url']) ? htmlspecialchars($_POST['url']) : '';
?>

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
            <form method="POST" class="relative" id="searchForm">
                <input
                    type="text"
                    name="url"
                    value="<?php echo $inputUrl; ?>"
                    placeholder="Masukkan URL UC Share (contoh: https://drive.ucweb.com/s/079eb21d6f504)"
                    class="w-full px-6 py-5 pr-36 rounded-2xl bg-white/10 backdrop-blur-lg border-2 border-purple-500/30 text-white text-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                    required
                />
                <button
                    type="submit"
                    class="absolute right-2 top-2 bottom-2 px-8 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" stroke-width="2"/>
                        <path d="m21 21-4.35-4.35" stroke-width="2"/>
                    </svg>
                    <span>Search</span>
                </button>
            </form>
            <p class="text-gray-400 text-sm mt-3 text-center">
                💡 API akan otomatis mengambil <strong class="text-purple-400">semua video</strong> dari folder dan subfolder
            </p>
        </div>

        <!-- Share Info -->
        <?php if ($shareInfo): ?>
        <div class="max-w-5xl mx-auto mb-8">
            <div class="bg-white/5 backdrop-blur-lg rounded-2xl border-2 border-purple-500/30 p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
                    <div>
                        <div class="text-gray-400 text-sm mb-1">Total Files</div>
                        <div class="text-white text-2xl font-bold"><?php echo $shareInfo['total_files']; ?></div>
                    </div>
                    <div>
                        <div class="text-gray-400 text-sm mb-1">Total Videos</div>
                        <div class="text-purple-400 text-2xl font-bold"><?php echo $shareInfo['total_videos'] ?? 0; ?></div>
                    </div>
                    <div>
                        <div class="text-gray-400 text-sm mb-1">Total Size</div>
                        <div class="text-pink-400 text-2xl font-bold"><?php echo number_format($shareInfo['total_size_mb'], 2); ?> MB</div>
                    </div>
                    <div>
                        <div class="text-gray-400 text-sm mb-1">Folders Scanned</div>
                        <div class="text-green-400 text-2xl font-bold"><?php echo $shareInfo['folders_scanned'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Fetch Summary -->
        <?php if ($fetchSummary): ?>
        <div class="max-w-5xl mx-auto mb-8">
            <div class="bg-gradient-to-r from-green-500/20 to-blue-500/20 backdrop-blur-lg rounded-2xl border-2 border-green-500/30 p-5">
                <div class="flex items-center justify-center gap-6 text-center">
                    <div>
                        <div class="text-gray-300 text-sm mb-1">Total Processed</div>
                        <div class="text-white text-xl font-bold"><?php echo $fetchSummary['total']; ?></div>
                    </div>
                    <div class="h-10 w-px bg-white/20"></div>
                    <div>
                        <div class="text-gray-300 text-sm mb-1">Success</div>
                        <div class="text-green-400 text-xl font-bold"><?php echo $fetchSummary['success']; ?></div>
                    </div>
                    <div class="h-10 w-px bg-white/20"></div>
                    <div>
                        <div class="text-gray-300 text-sm mb-1">Failed</div>
                        <div class="text-red-400 text-xl font-bold"><?php echo $fetchSummary['failed']; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if (!empty($error)): ?>
        <div class="max-w-5xl mx-auto mb-8">
            <div class="bg-red-500/20 border-2 border-red-500/50 rounded-2xl px-6 py-4 text-red-200 backdrop-blur-lg">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                        <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                    </svg>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Videos Grid -->
        <?php if (!empty($videos)): ?>
        <div class="space-y-8">
            <!-- Header Info -->
            <div class="flex flex-wrap items-center justify-between gap-4 px-2">
                <h2 class="text-3xl font-bold text-white">
                    📹 Found <?php echo count($videos); ?> video<?php echo count($videos) !== 1 ? 's' : ''; ?>
                </h2>
            </div>

            <!-- Video Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($videos as $index => $video): ?>
                <?php 
                    $isError = isset($video['status']) && $video['status'] === 'error';
                    $name = $video['name'] ?? 'Unknown';
                    $thumbnail = $video['download']['thumbnail'] ?? '';
                    $downloadUrl = $video['download']['url'] ?? '#';
                    $directDownload = $video['download']['direct_download'] ?? $downloadUrl;
                ?>
                
                <div class="card-hover bg-white/5 backdrop-blur-lg rounded-2xl overflow-hidden border-2 <?php echo $isError ? 'border-red-500/50' : 'border-purple-500/20'; ?>">
                    
                    <!-- Thumbnail -->
                    <a
                        href="<?php echo $isError ? '#' : htmlspecialchars($downloadUrl); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="block relative group overflow-hidden <?php echo $isError ? 'pointer-events-none' : ''; ?>"
                    >
                        <?php if (!$isError && !empty($thumbnail)): ?>
                        <img
                            src="<?php echo htmlspecialchars($thumbnail); ?>"
                            alt="<?php echo htmlspecialchars($name); ?>"
                            class="w-full h-48 object-cover group-hover:scale-110 transition-transform duration-500"
                            onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect fill=%22%23374151%22 width=%22400%22 height=%22300%22/%3E%3Ctext fill=%22%23fff%22 font-family=%22Arial%22 font-size=%2220%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22%3ENo Preview%3C/text%3E%3C/svg%3E'"
                        />
                        <?php else: ?>
                        <div class="w-full h-48 bg-gray-700 flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" stroke-width="2"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!$isError): ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                <polygon points="10 8 16 12 10 16 10 8" fill="currentColor"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Duration Badge -->
                        <?php if (!$isError && isset($video['video_info']['duration_formatted'])): ?>
                        <div class="absolute top-3 right-3 bg-black/80 backdrop-blur px-3 py-1.5 rounded-lg text-white text-sm font-bold">
                            ⏱️ <?php echo htmlspecialchars($video['video_info']['duration_formatted']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Path Badge -->
                        <?php if (!$isError && isset($video['path']) && $video['depth'] > 0): ?>
                        <div class="absolute top-3 left-3 bg-blue-600/80 backdrop-blur px-3 py-1.5 rounded-lg text-white text-xs font-bold">
                            📁 Level <?php echo $video['depth']; ?>
                        </div>
                        <?php endif; ?>
                    </a>

                    <!-- Content -->
                    <div class="p-5 space-y-4">
                        <!-- Name -->
                        <h3 class="text-white font-bold text-base line-clamp-2 min-h-[3rem]" title="<?php echo htmlspecialchars($name); ?>">
                            <?php echo htmlspecialchars($name); ?>
                        </h3>

                        <!-- Path (if in subfolder) -->
                        <?php if (!$isError && isset($video['path']) && $video['path'] !== '/' . $name): ?>
                        <div class="text-gray-400 text-xs truncate" title="<?php echo htmlspecialchars($video['path']); ?>">
                            📂 <?php echo htmlspecialchars($video['path']); ?>
                        </div>
                        <?php endif; ?>

                        <!-- Error Message -->
                        <?php if ($isError): ?>
                        <div class="bg-red-500/20 border border-red-500/50 rounded-lg px-3 py-2 text-red-300 text-sm">
                            ❌ <?php echo htmlspecialchars($video['error'] ?? 'Unknown error'); ?>
                        </div>
                        <?php else: ?>
                        
                        <!-- Info Badges -->
                        <div class="flex flex-wrap gap-2 text-xs font-semibold">
                            <?php if (isset($video['video_info']['resolution']['label'])): ?>
                            <span class="bg-purple-500/30 text-purple-200 px-3 py-1.5 rounded-full border border-purple-400/30">
                                🎬 <?php echo htmlspecialchars($video['video_info']['resolution']['label']); ?>
                            </span>
                            <?php endif; ?>
                            
                            <span class="bg-blue-500/30 text-blue-200 px-3 py-1.5 rounded-full border border-blue-400/30">
                                💾 <?php echo number_format($video['size_mb'], 2); ?> MB
                            </span>
                            
                            <?php if (isset($video['video_info']['fps'])): ?>
                            <span class="bg-green-500/30 text-green-200 px-3 py-1.5 rounded-full border border-green-400/30">
                                🎞️ <?php echo htmlspecialchars($video['video_info']['fps']); ?> FPS
                            </span>
                            <?php endif; ?>
                        </div>

                        <!-- Download Button -->
                        <a
                            href="<?php echo htmlspecialchars($directDownload); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="block w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white py-3 rounded-xl font-bold transition-all text-center flex items-center justify-center gap-2 shadow-lg hover:shadow-purple-500/50"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke-width="2"/>
                                <polyline points="7 10 12 15 17 10" stroke-width="2"/>
                                <line x1="12" y1="15" x2="12" y2="3" stroke-width="2"/>
                            </svg>
                            <span>Download</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Empty State -->
        <?php if (empty($videos) && empty($error)): ?>
        <div class="text-center py-20">
            <svg class="w-32 h-32 text-gray-600 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h3 class="text-gray-300 text-2xl font-bold mb-3">
                Siap untuk download?
            </h3>
            <p class="text-gray-400 text-lg mb-2">
                Masukkan URL UC Share di atas untuk memulai
            </p>
            <p class="text-gray-500 text-sm">
                Contoh: https://drive.ucweb.com/s/079eb21d6f504
            </p>
            
            <!-- Features -->
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
        <?php endif; ?>

        <!-- Footer -->
        <div class="text-center mt-16 pb-8">
            <p class="text-gray-500 text-sm">
                Made with ❤️ using UC Share API
            </p>
        </div>

    </div>
</div>

</body>
</html>
