<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UC Share Viewer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<?php
$videos = [];
$error = '';
$loading = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = trim($_POST['url']);
    
    if (empty($url)) {
        $error = 'Please enter a UC Share URL';
    } else {
        $apiUrl = 'https://ucweb-five.vercel.app/api/?url=' . urlencode($url);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = 'Failed to fetch videos. Please check the URL and try again.';
        } else {
            $data = json_decode($response, true);
            
            if ($data && isset($data['status']) && $data['status'] === 'success' && isset($data['videos'])) {
                $videos = $data['videos'];
            } else {
                $error = 'No videos found or invalid response';
            }
        }
        
        curl_close($ch);
    }
}

$inputUrl = isset($_POST['url']) ? htmlspecialchars($_POST['url']) : '';
?>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 p-4">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-3 mb-4">
                <svg class="w-10 h-10 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="7" y1="2" x2="7" y2="22" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="17" y1="2" x2="17" y2="22" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="2" y1="12" x2="22" y2="12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="2" y1="7" x2="7" y2="7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="2" y1="17" x2="7" y2="17" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="17" y1="17" x2="22" y2="17" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="17" y1="7" x2="22" y2="7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <h1 class="text-4xl font-bold text-white">UC Share Viewer</h1>
            </div>
            <p class="text-gray-300">Download and view videos from UC Share links</p>
        </div>

        <!-- Search Form -->
        <div class="mb-8 max-w-3xl mx-auto">
            <form method="POST" class="relative">
                <input
                    type="text"
                    name="url"
                    value="<?php echo $inputUrl; ?>"
                    placeholder="Enter UC Share URL (e.g., https://uc-share.com/s/6541c36f1a754?la=id)"
                    class="w-full px-5 py-3 pr-28 rounded-xl bg-white/10 backdrop-blur border border-purple-500/30 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                />
                <button
                    type="submit"
                    class="absolute right-2 top-2 bottom-2 px-5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-lg font-semibold transition-all flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="m21 21-4.35-4.35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="hidden sm:inline">Search</span>
                </button>
            </form>
        </div>

        <!-- Error Message -->
        <?php if (!empty($error)): ?>
        <div class="max-w-3xl mx-auto mb-6">
            <div class="bg-red-500/20 border border-red-500/50 rounded-lg px-5 py-3 text-red-200">
                <?php echo htmlspecialchars($error); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Videos Grid -->
        <?php if (!empty($videos)): ?>
        <div class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-2xl font-bold text-white">
                    Found <?php echo count($videos); ?> video<?php echo count($videos) !== 1 ? 's' : ''; ?>
                </h2>
                <div class="text-gray-400">
                    Total: <?php echo number_format(array_sum(array_column($videos, 'size_mb')), 2); ?> MB
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php foreach ($videos as $index => $video): ?>
                <div class="bg-white/5 backdrop-blur rounded-xl overflow-hidden border border-purple-500/20 hover:border-purple-500/50 transition-all hover:scale-105">
                    <a
                        href="<?php echo htmlspecialchars($video['download']['url']); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="block relative group"
                    >
                        <img
                            src="<?php echo htmlspecialchars($video['download']['thumbnail']); ?>"
                            alt="<?php echo htmlspecialchars($video['name']); ?>"
                            class="w-full h-44 object-cover"
                        />
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <polyline points="15 3 21 3 21 9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="10" y1="14" x2="21" y2="3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="absolute top-2 right-2 bg-black/70 px-2 py-1 rounded text-white text-xs">
                            <?php echo htmlspecialchars($video['video_info']['duration_formatted']); ?>
                        </div>
                    </a>

                    <div class="p-4 space-y-3">
                        <h3 class="text-white font-semibold line-clamp-2 min-h-[3rem]">
                            <?php echo htmlspecialchars($video['name']); ?>
                        </h3>

                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="bg-purple-500/20 text-purple-300 px-2 py-1 rounded-full">
                                <?php echo htmlspecialchars($video['video_info']['resolution']['label']); ?>
                            </span>
                            <span class="bg-blue-500/20 text-blue-300 px-2 py-1 rounded-full">
                                <?php echo number_format($video['size_mb'], 2); ?> MB
                            </span>
                            <span class="bg-green-500/20 text-green-300 px-2 py-1 rounded-full">
                                <?php echo htmlspecialchars($video['video_info']['fps']); ?> FPS
                            </span>
                        </div>

                        <a
                            href="<?php echo htmlspecialchars($video['download']['url']); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="block w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white py-2.5 rounded-lg font-semibold transition-all text-center flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <polyline points="7 10 12 15 17 10" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="12" y1="15" x2="12" y2="3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
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
        <?php if (empty($videos) && empty($error) && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
        <div class="text-center py-16">
            <svg class="w-20 h-20 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="7" y1="2" x2="7" y2="22" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="17" y1="2" x2="17" y2="22" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="2" y1="12" x2="22" y2="12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <p class="text-gray-400 text-lg">
                Enter a UC Share URL above to get started
            </p>
        </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
