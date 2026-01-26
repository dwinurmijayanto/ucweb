<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UC Share Viewer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(168, 85, 247, 0.4);
        }
    </style>
</head>
<body>
<?php
$videos = [];
$error = '';
$totalSize = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = trim($_POST['url']);
    
    if (empty($url)) {
        $error = 'Silakan masukkan URL UC Share';
    } else {
        $apiUrl = 'https://ucweb-five.vercel.app/api/?url=' . urlencode($url);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $error = 'Gagal mengambil data. Silakan periksa URL dan coba lagi.';
        } else {
            $data = json_decode($response, true);
            
            if ($data && isset($data['status']) && $data['status'] === 'success' && isset($data['videos'])) {
                $videos = $data['videos'];
                foreach ($videos as $video) {
                    if (isset($video['size_mb'])) {
                        $totalSize += $video['size_mb'];
                    }
                }
            } else {
                $error = 'Video tidak ditemukan atau URL tidak valid';
            }
        }
        
        curl_close($ch);
    }
}

$inputUrl = isset($_POST['url']) ? htmlspecialchars($_POST['url']) : '';
?>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 p-6">
    <div class="max-w-6xl mx-auto">
        
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="flex items-center justify-center gap-4 mb-4">
                <svg class="w-14 h-14 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18" stroke-width="2"/>
                    <line x1="7" y1="2" x2="7" y2="22" stroke-width="2"/>
                    <line x1="17" y1="2" x2="17" y2="22" stroke-width="2"/>
                    <line x1="2" y1="12" x2="22" y2="12" stroke-width="2"/>
                    <line x1="2" y1="7" x2="7" y2="7" stroke-width="2"/>
                    <line x1="2" y1="17" x2="7" y2="17" stroke-width="2"/>
                    <line x1="17" y1="17" x2="22" y2="17" stroke-width="2"/>
                    <line x1="17" y1="7" x2="22" y2="7" stroke-width="2"/>
                </svg>
                <h1 class="text-5xl font-bold text-white">UC Share Viewer</h1>
            </div>
            <p class="text-gray-300 text-lg">Download and view videos from UC Share links</p>
        </div>

        <!-- Search Form -->
        <div class="mb-10 max-w-4xl mx-auto">
            <form method="POST" class="relative">
                <input
                    type="text"
                    name="url"
                    value="<?php echo $inputUrl; ?>"
                    placeholder="Masukkan URL UC Share (contoh: https://uc-share.com/s/6541c36f1a754?la=id)"
                    class="w-full px-6 py-4 pr-32 rounded-2xl bg-white/10 backdrop-blur-lg border-2 border-purple-500/30 text-white text-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                    required
                />
                <button
                    type="submit"
                    class="absolute right-2 top-2 bottom-2 px-6 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" stroke-width="2"/>
                        <path d="m21 21-4.35-4.35" stroke-width="2"/>
                    </svg>
                    <span>Search</span>
                </button>
            </form>
        </div>

        <!-- Error Message -->
        <?php if (!empty($error)): ?>
        <div class="max-w-4xl mx-auto mb-8">
            <div class="bg-red-500/20 border-2 border-red-500/50 rounded-2xl px-6 py-4 text-red-200 text-center backdrop-blur-lg">
                <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                </svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Videos Grid -->
        <?php if (!empty($videos)): ?>
        <div class="space-y-8">
            <!-- Header Info -->
            <div class="flex flex-wrap items-center justify-between gap-4 px-2">
                <h2 class="text-3xl font-bold text-white">
                    Found <?php echo count($videos); ?> video<?php echo count($videos) !== 1 ? 's' : ''; ?>
                </h2>
                <div class="text-gray-300 text-lg font-semibold bg-white/10 px-4 py-2 rounded-lg backdrop-blur">
                    Total: <?php echo number_format($totalSize, 2); ?> MB
                </div>
            </div>

            <!-- Video Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($videos as $index => $video): ?>
                <div class="card-hover bg-white/5 backdrop-blur-lg rounded-2xl overflow-hidden border-2 border-purple-500/20">
                    
                    <!-- Thumbnail with Overlay -->
                    <a
                        href="<?php echo htmlspecialchars($video['download']['url']); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="block relative group overflow-hidden"
                    >
                        <img
                            src="<?php echo htmlspecialchars($video['download']['thumbnail']); ?>"
                            alt="<?php echo htmlspecialchars($video['name']); ?>"
                            class="w-full h-56 object-cover group-hover:scale-110 transition-transform duration-500"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                <polygon points="10 8 16 12 10 16 10 8" fill="currentColor"/>
                            </svg>
                        </div>
                        
                        <!-- Duration Badge -->
                        <?php if (isset($video['video_info']['duration_formatted'])): ?>
                        <div class="absolute top-3 right-3 bg-black/80 backdrop-blur px-3 py-1.5 rounded-lg text-white text-sm font-bold">
                            <?php echo htmlspecialchars($video['video_info']['duration_formatted']); ?>
                        </div>
                        <?php endif; ?>
                    </a>

                    <!-- Content -->
                    <div class="p-5 space-y-4">
                        <!-- Name -->
                        <h3 class="text-white font-bold text-lg line-clamp-2 min-h-[3.5rem]">
                            <?php echo htmlspecialchars($video['name']); ?>
                        </h3>

                        <!-- Info Badges -->
                        <div class="flex flex-wrap gap-2 text-xs font-semibold">
                            <?php if (isset($video['video_info']['resolution']['label'])): ?>
                            <span class="bg-purple-500/30 text-purple-200 px-3 py-1.5 rounded-full border border-purple-400/30">
                                <?php echo htmlspecialchars($video['video_info']['resolution']['label']); ?>
                            </span>
                            <?php endif; ?>
                            
                            <?php if (isset($video['size_mb'])): ?>
                            <span class="bg-blue-500/30 text-blue-200 px-3 py-1.5 rounded-full border border-blue-400/30">
                                <?php echo number_format($video['size_mb'], 2); ?> MB
                            </span>
                            <?php endif; ?>
                            
                            <?php if (isset($video['video_info']['fps'])): ?>
                            <span class="bg-green-500/30 text-green-200 px-3 py-1.5 rounded-full border border-green-400/30">
                                <?php echo htmlspecialchars($video['video_info']['fps']); ?> FPS
                            </span>
                            <?php endif; ?>
                        </div>

                        <!-- Download Button -->
                        <a
                            href="<?php echo htmlspecialchars($video['download']['url']); ?>"
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
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Empty State -->
        <?php if (empty($videos) && empty($error)): ?>
        <div class="text-center py-20">
            <svg class="w-24 h-24 text-gray-600 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18" stroke-width="2"/>
                <line x1="7" y1="2" x2="7" y2="22" stroke-width="2"/>
                <line x1="17" y1="2" x2="17" y2="22" stroke-width="2"/>
                <line x1="2" y1="12" x2="22" y2="12" stroke-width="2"/>
            </svg>
            <p class="text-gray-400 text-xl mb-3">
                Masukkan URL UC Share untuk memulai
            </p>
            <p class="text-gray-500 text-sm">
                Contoh: https://uc-share.com/s/6541c36f1a754?la=id
            </p>
        </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
