<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

class UCWebDownloader {
    
    private $sharePageUrl = 'https://drive.ucweb.com/s/';
    private $apiBaseUrl = 'https://m-intldrive.ucweb.com/1/clouddrive/share/sharepage';
    private $maxDepth = 5; // Maksimal kedalaman folder untuk mencegah infinite loop
    
    /**
     * Ekstrak pwd_id dari URL folder
     */
    private function extractPwdId($url) {
        if (preg_match('/\/s\/([a-z0-9]+)/i', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    /**
     * Ambil stoken dari halaman share (untuk fallback)
     */
    private function getStokenFromPage($pwdId) {
        $url = $this->sharePageUrl . $pwdId . '?la=id';
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7'
            ]
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return null;
        }
        
        if (preg_match('/"stoken"\s*:\s*"([^"]+)"/i', $html, $m)) {
            return $m[1];
        }
        
        return null;
    }
    
    /**
     * Ambil detail folder dan file list dari API v2
     * Sekarang dengan support untuk pdir_fid (parent directory fid) untuk subfolder
     */
    private function getV2Detail($pwdId, $stoken, $pdirFid = '', $page = 1, $size = 100) {
        $url = $this->apiBaseUrl . '/detail';
        
        $params = [
            'pr' => 'UCBrowser',
            'fr' => 'h5',
            '__t' => round(microtime(true) * 1000),
            'pwd_id' => $pwdId,
            'stoken' => $stoken,
            'pdir_fid' => $pdirFid,
            'force' => 0,
            '_page' => $page,
            '_size' => $size,
            '_fetch_banner' => 0,
            '_fetch_share' => 0,
            '_fetch_total' => 1,
            '_sort' => 'file_type:asc,updated_at:desc',
            'ip_limit' => ''
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url . '?' . http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
                'Accept: application/json, text/plain, */*',
                'Accept-Language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                'Origin: https://drive.ucweb.com',
                'Referer: https://drive.ucweb.com/s/' . $pwdId
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return [
                'success' => false,
                'http_code' => $httpCode,
                'raw' => substr($response, 0, 500)
            ];
        }
        
        $data = json_decode($response, true);
        
        if (!$data || $data['code'] !== 0) {
            return [
                'success' => false,
                'error' => $data['message'] ?? 'Unknown error',
                'data' => $data
            ];
        }
        
        return [
            'success' => true,
            'data' => $data['data']
        ];
    }
    
    /**
     * Ambil download URL langsung dari API (alternatif untuk non-video files)
     */
    private function getDownloadUrl($pwdId, $stoken, $fid, $fidToken) {
        $url = $this->apiBaseUrl . '/download_url';
        $timestamp = round(microtime(true) * 1000);
        
        $params = [
            'pr' => 'UCBrowser',
            'fr' => 'h5',
            '__t' => $timestamp,
            'pwd_id' => $pwdId,
            'stoken' => $stoken,
            'fid' => $fid,
            'fid_token' => $fidToken
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url . '?' . http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
                'Accept: application/json, text/plain, */*',
                'Accept-Language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                'Origin: https://drive.ucweb.com',
                'Referer: https://drive.ucweb.com/s/' . $pwdId
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'http_code' => $httpCode,
            'data' => $httpCode === 200 ? json_decode($response, true) : null,
            'raw' => $httpCode !== 200 ? substr($response, 0, 500) : null
        ];
    }
    
    /**
     * Ambil video info dengan fid_token
     */
    private function getVideoInfo($pwdId, $stoken, $fid, $fidToken) {
        $url = $this->apiBaseUrl . '/video_preview';
        $timestamp = round(microtime(true) * 1000);
        
        $params = [
            'pr' => 'UCBrowser',
            'fr' => 'h5',
            '__t' => $timestamp,
            'pwd_id' => $pwdId,
            'stoken' => $stoken,
            'fid' => $fid,
            'fid_token' => $fidToken
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url . '?' . http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
                'Accept: application/json, text/plain, */*',
                'Accept-Language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                'Origin: https://drive.ucweb.com',
                'Referer: https://drive.ucweb.com/s/' . $pwdId
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'http_code' => $httpCode,
            'data' => $httpCode === 200 ? json_decode($response, true) : null,
            'raw' => $httpCode !== 200 ? substr($response, 0, 500) : null
        ];
    }
    
    /**
     * Helper function untuk cek apakah item adalah folder
     */
    private function isFolder($item) {
        // Cek dari dir flag
        if (isset($item['dir']) && $item['dir'] === true) {
            return true;
        }
        
        // Cek dari file flag (inverse)
        if (isset($item['file']) && $item['file'] === false) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Helper function untuk cek apakah file adalah video
     */
    private function isVideoFile($file) {
        if (isset($file['format_type']) && strpos($file['format_type'], 'video/') === 0) {
            return true;
        }
        
        if (isset($file['file_name'])) {
            $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
            return in_array($ext, ['mp4', 'mov', 'avi', 'mkv', 'wmv', 'flv', 'm4v', 'webm', '3gp']);
        }
        
        return false;
    }
    
    /**
     * Recursive function untuk mengambil semua file dari folder dan subfolder
     */
    private function getAllFilesRecursive($pwdId, $stoken, $pdirFid = '', $depth = 0, $path = '', &$debugInfo = null, &$visitedFids = []) {
        if ($depth > $this->maxDepth) {
            return [
                'files' => [],
                'folders_scanned' => 0,
                'error' => 'Max depth reached'
            ];
        }
        
        // Deteksi circular reference
        if (!empty($pdirFid) && in_array($pdirFid, $visitedFids)) {
            if ($debugInfo !== null) {
                $debugInfo['circular_detected'][] = [
                    'fid' => $pdirFid,
                    'depth' => $depth,
                    'path' => $path
                ];
            }
            return [
                'files' => [],
                'folders_scanned' => 0,
                'error' => 'Circular reference detected'
            ];
        }
        
        if (!empty($pdirFid)) {
            $visitedFids[] = $pdirFid;
        }
        
        $allFiles = [];
        $foldersScanned = 0;
        
        $v2Result = $this->getV2Detail($pwdId, $stoken, $pdirFid);
        
        if (!$v2Result['success']) {
            return [
                'files' => [],
                'folders_scanned' => 0,
                'error' => 'Failed to fetch folder: ' . ($v2Result['error'] ?? 'Unknown')
            ];
        }
        
        $v2Data = $v2Result['data'];
        $items = $v2Data['list'] ?? [];
        
        // Debug: simpan raw items
        if ($debugInfo !== null && !isset($debugInfo['raw_items'])) {
            $debugInfo['raw_items'] = [];
            $debugInfo['circular_detected'] = [];
        }
        
        foreach ($items as $item) {
            $itemPath = $path . '/' . ($item['file_name'] ?? 'unknown');
            $itemFid = $item['fid'] ?? '';
            
            // Debug info
            if ($debugInfo !== null) {
                $debugInfo['raw_items'][] = [
                    'name' => $item['file_name'] ?? 'unknown',
                    'path' => $itemPath,
                    'depth' => $depth,
                    'fid' => $itemFid,
                    'dir_flag' => $item['dir'] ?? 'not-set',
                    'file_flag' => $item['file'] ?? 'not-set',
                    'format_type' => $item['format_type'] ?? 'not-set',
                    'size' => $item['size'] ?? 'not-set',
                    'is_folder_detected' => $this->isFolder($item),
                    'has_fid_token' => !empty($item['share_fid_token']) || !empty($item['fid_token'])
                ];
            }
            
            // Cek apakah ini folder atau file
            $isFolder = $this->isFolder($item);
            
            if ($isFolder && !in_array($itemFid, $visitedFids)) {
                // Ini adalah folder, rekursif masuk ke dalamnya
                $foldersScanned++;
                
                $subResult = $this->getAllFilesRecursive(
                    $pwdId, 
                    $stoken, 
                    $itemFid, 
                    $depth + 1,
                    $itemPath,
                    $debugInfo,
                    $visitedFids
                );
                
                $allFiles = array_merge($allFiles, $subResult['files']);
                $foldersScanned += $subResult['folders_scanned'];
                
            } else {
                // Ini adalah file, tambahkan ke list
                $item['path'] = $itemPath;
                $item['depth'] = $depth;
                $allFiles[] = $item;
            }
        }
        
        return [
            'files' => $allFiles,
            'folders_scanned' => $foldersScanned
        ];
    }
    
    /**
     * Main function: Process dari URL folder sampai download link
     * Dengan support recursive subfolder
     */
    public function processFolder($folderUrl, $fileIndex = null, $fetchAll = false, $recursive = true) {
        try {
            $pwdId = $this->extractPwdId($folderUrl);
            if (!$pwdId) {
                throw new Exception("Invalid folder URL format");
            }
            
            // Get stoken first from page
            $stoken = $this->getStokenFromPage($pwdId);
            
            if (!$stoken) {
                return [
                    'status' => 'error',
                    'message' => 'Failed to get stoken from share page'
                ];
            }
            
            // Ambil semua file (dengan atau tanpa recursive)
            if ($recursive) {
                $debugInfo = ['raw_items' => [], 'circular_detected' => []];
                $visitedFids = [];
                $recursiveResult = $this->getAllFilesRecursive($pwdId, $stoken, '', 0, '', $debugInfo, $visitedFids);
                $files = $recursiveResult['files'];
                $foldersScanned = $recursiveResult['folders_scanned'];
            } else {
                $v2Result = $this->getV2Detail($pwdId, $stoken, '');
                
                if (!$v2Result['success']) {
                    return [
                        'status' => 'error',
                        'message' => 'Failed to fetch folder details',
                        'debug' => $v2Result
                    ];
                }
                
                $files = $v2Result['data']['list'] ?? [];
                $foldersScanned = 0;
                $debugInfo = null;
                // Filter hanya file (bukan folder)
                $files = array_filter($files, function($item) {
                    return !$this->isFolder($item);
                });
            }
            
            if (empty($files)) {
                return [
                    'status' => 'error',
                    'message' => 'No files found in folder',
                    'recursive' => $recursive,
                    'folders_scanned' => $foldersScanned,
                    'debug' => [
                        'pwd_id' => $pwdId,
                        'stoken_found' => !empty($stoken),
                        'items_found' => $debugInfo ? count($debugInfo['raw_items']) : 0,
                        'circular_loops' => $debugInfo ? count($debugInfo['circular_detected']) : 0,
                        'circular_info' => $debugInfo['circular_detected'] ?? [],
                        'raw_items_sample' => $debugInfo ? array_slice($debugInfo['raw_items'], 0, 10) : []
                    ]
                ];
            }
            
            // Get share info from first API call
            $shareInfoResult = $this->getV2Detail($pwdId, $stoken, '');
            $shareInfo = [
                'title' => 'Unknown',
                'creator' => 'Unknown',
                'total_files' => count($files),
                'total_size_mb' => round(array_sum(array_column($files, 'size')) / 1024 / 1024, 2)
            ];
            
            // Filter video files
            $videoFiles = array_filter($files, [$this, 'isVideoFile']);
            
            if (empty($videoFiles)) {
                $videoFiles = $files;
            }
            
            $videoFiles = array_values($videoFiles);
            
            // FETCH ALL MODE
            if ($fetchAll) {
                $allVideos = [];
                $successCount = 0;
                $failedCount = 0;
                
                foreach ($videoFiles as $idx => $file) {
                    $fid = $file['fid'];
                    $fidToken = $file['share_fid_token'] ?? $file['fid_token'] ?? null;
                    
                    $baseInfo = [
                        'index' => $idx,
                        'name' => $file['file_name'] ?? 'unknown',
                        'path' => $file['path'] ?? '/' . ($file['file_name'] ?? 'unknown'),
                        'depth' => $file['depth'] ?? 0,
                        'fid' => $fid,
                        'size' => $file['size'] ?? 0,
                        'size_mb' => round(($file['size'] ?? 0) / 1024 / 1024, 2),
                        'type' => $file['format_type'] ?? 'unknown'
                    ];
                    
                    if (!$fidToken) {
                        $allVideos[] = array_merge($baseInfo, [
                            'status' => 'error',
                            'error' => 'fid_token not found'
                        ]);
                        $failedCount++;
                        continue;
                    }
                    
                    // Try video_preview first
                    $videoInfoResult = $this->getVideoInfo($pwdId, $stoken, $fid, $fidToken);
                    
                    // If video_preview fails with 404, try download_url endpoint
                    if ($videoInfoResult['http_code'] === 404) {
                        $videoInfoResult = $this->getDownloadUrl($pwdId, $stoken, $fid, $fidToken);
                    }
                    
                    if ($videoInfoResult['http_code'] !== 200) {
                        $allVideos[] = array_merge($baseInfo, [
                            'status' => 'error',
                            'error' => 'Failed to fetch file info. HTTP ' . $videoInfoResult['http_code'],
                            'raw_response' => substr($videoInfoResult['raw'] ?? '', 0, 200)
                        ]);
                        $failedCount++;
                        continue;
                    }
                    
                    $videoInfo = $videoInfoResult['data'];
                    
                    if ($videoInfo['code'] !== 0) {
                        $allVideos[] = array_merge($baseInfo, [
                            'status' => 'error',
                            'error' => $videoInfo['message'] ?? 'Unknown error'
                        ]);
                        $failedCount++;
                        continue;
                    }
                    
                    $data = $videoInfo['data'];
                    $playInfo = $data['play_info'] ?? [];
                    
                    // Handle different response formats
                    $downloadUrl = $playInfo['url'] ?? $data['download_url'] ?? $data['url'] ?? null;
                    $fileName = $file['file_name'] ?? 'video.mp4';
                    
                    $allVideos[] = array_merge($baseInfo, [
                        'status' => 'success',
                        'created_at' => isset($file['created_at']) ? 
                            date('Y-m-d H:i:s', intval($file['created_at'] / 1000)) : null,
                        'video_info' => [
                            'duration' => $playInfo['duration'] ?? 0,
                            'duration_formatted' => gmdate("H:i:s", $playInfo['duration'] ?? 0),
                            'resolution' => [
                                'width' => $playInfo['width'] ?? 0,
                                'height' => $playInfo['height'] ?? 0,
                                'label' => ($playInfo['height'] ?? 0) . 'p'
                            ],
                            'bitrate_kbps' => round(($playInfo['bitrate'] ?? 0) / 1000),
                            'fps' => $playInfo['fps'] ?? 0,
                            'codec' => $playInfo['codec'] ?? 'unknown'
                        ],
                        'download' => [
                            'url' => $downloadUrl,
                            'thumbnail' => $data['preview_url'] ?? null,
                            'direct_download' => 'https://upload.vbi1.my.id/ucweb/ucweb.php?url=' . urlencode($downloadUrl ?? '') . '&download=1&filename=' . urlencode($fileName)
                        ]
                    ]);
                    $successCount++;
                    
                    usleep(100000); // 0.1 second delay
                }
                
                return [
                    'status' => 'success',
                    'mode' => 'fetch_all',
                    'recursive' => $recursive,
                    'share_info' => [
                        'title' => $shareInfo['title'] ?? 'Unknown',
                        'creator' => $shareInfo['creator_name'] ?? 'Unknown',
                        'total_files' => count($files),
                        'total_videos' => count($videoFiles),
                        'total_size_mb' => round(array_sum(array_column($files, 'size')) / 1024 / 1024, 2),
                        'folders_scanned' => $foldersScanned
                    ],
                    'fetch_summary' => [
                        'total' => count($videoFiles),
                        'success' => $successCount,
                        'failed' => $failedCount
                    ],
                    'videos' => $allVideos
                ];
                
            } else {
                // SINGLE FILE MODE
                if ($fileIndex === null) {
                    $fileIndex = 0;
                }
                
                if ($fileIndex >= count($videoFiles)) {
                    $fileIndex = 0;
                }
                
                $selectedFile = $videoFiles[$fileIndex];
                $fid = $selectedFile['fid'];
                $fidToken = $selectedFile['share_fid_token'] ?? $selectedFile['fid_token'] ?? null;
                
                if (!$fidToken) {
                    return [
                        'status' => 'error',
                        'message' => 'fid_token not found in file info',
                        'debug' => [
                            'pwd_id' => $pwdId,
                            'fid' => $fid,
                            'file' => $selectedFile
                        ]
                    ];
                }
                
                // Try video_preview first
                $videoInfoResult = $this->getVideoInfo($pwdId, $stoken, $fid, $fidToken);
                
                // If video_preview fails with 404, try download_url endpoint
                if ($videoInfoResult['http_code'] === 404) {
                    $videoInfoResult = $this->getDownloadUrl($pwdId, $stoken, $fid, $fidToken);
                }
                
                if ($videoInfoResult['http_code'] !== 200) {
                    return [
                        'status' => 'error',
                        'message' => 'Failed to fetch file info. HTTP Code: ' . $videoInfoResult['http_code'],
                        'debug' => [
                            'pwd_id' => $pwdId,
                            'fid' => $fid,
                            'raw_response' => $videoInfoResult['raw']
                        ]
                    ];
                }
                
                $videoInfo = $videoInfoResult['data'];
                
                if ($videoInfo['code'] !== 0) {
                    return [
                        'status' => 'error',
                        'message' => 'File info error: ' . ($videoInfo['message'] ?? 'Unknown error'),
                        'debug' => $videoInfo
                    ];
                }
                
                $data = $videoInfo['data'];
                $playInfo = $data['play_info'] ?? [];
                
                // Handle different response formats
                $downloadUrl = $playInfo['url'] ?? $data['download_url'] ?? $data['url'] ?? null;
                
                return [
                    'status' => 'success',
                    'mode' => 'single_file',
                    'recursive' => $recursive,
                    'share_info' => [
                        'title' => $shareInfo['title'] ?? 'Unknown',
                        'creator' => $shareInfo['creator_name'] ?? 'Unknown',
                        'total_files' => count($files),
                        'total_size_mb' => round(array_sum(array_column($files, 'size')) / 1024 / 1024, 2),
                        'folders_scanned' => $foldersScanned
                    ],
                    'file_info' => [
                        'name' => $selectedFile['file_name'] ?? 'unknown',
                        'path' => $selectedFile['path'] ?? '/' . ($selectedFile['file_name'] ?? 'unknown'),
                        'depth' => $selectedFile['depth'] ?? 0,
                        'size' => $selectedFile['size'] ?? 0,
                        'size_mb' => round(($selectedFile['size'] ?? 0) / 1024 / 1024, 2),
                        'type' => $selectedFile['format_type'] ?? 'unknown',
                        'created_at' => isset($selectedFile['created_at']) ? 
                            date('Y-m-d H:i:s', intval($selectedFile['created_at'] / 1000)) : null,
                        'fid' => $fid
                    ],
                    'video_info' => [
                        'duration' => $playInfo['duration'] ?? 0,
                        'duration_formatted' => gmdate("H:i:s", $playInfo['duration'] ?? 0),
                        'size' => $playInfo['size'] ?? 0,
                        'size_mb' => round(($playInfo['size'] ?? 0) / 1024 / 1024, 2),
                        'format' => $playInfo['format'] ?? 'unknown',
                        'resolution' => [
                            'width' => $playInfo['width'] ?? 0,
                            'height' => $playInfo['height'] ?? 0,
                            'label' => ($playInfo['height'] ?? 0) . 'p'
                        ],
                        'bitrate' => $playInfo['bitrate'] ?? 0,
                        'bitrate_kbps' => round(($playInfo['bitrate'] ?? 0) / 1000),
                        'fps' => $playInfo['fps'] ?? 0,
                        'codec' => $playInfo['codec'] ?? 'unknown',
                        'audio' => $playInfo['audio'] ?? null
                    ],
                    'download' => [
                        'url' => $downloadUrl,
                        'thumbnail' => $data['preview_url'] ?? null,
                        'direct_download' => 'https://ucweb-five.vercel.app/api/?url=' . urlencode($downloadUrl ?? '') . '&download=1&filename=' . urlencode($selectedFile['file_name'] ?? 'video.mp4')
                    ],
                    'available_files' => array_map(function($f, $idx) {
                        return [
                            'index' => $idx,
                            'name' => $f['file_name'] ?? 'unknown',
                            'path' => $f['path'] ?? '/',
                            'fid' => $f['fid'] ?? '',
                            'size' => $f['size'] ?? 0,
                            'size_mb' => round(($f['size'] ?? 0) / 1024 / 1024, 2),
                            'type' => $f['format_type'] ?? 'unknown',
                            'has_fid_token' => !empty($f['share_fid_token']) || !empty($f['fid_token'])
                        ];
                    }, $videoFiles, array_keys($videoFiles))
                ];
            }
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile()),
                'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 5)
            ];
        }
    }
    
    /**
     * Proxy download untuk streaming langsung
     */
    public function proxyDownload($downloadUrl, $filename = 'video.mp4') {
        set_time_limit(0);
        
        if (!filter_var($downloadUrl, FILTER_VALIDATE_URL)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'Invalid download URL']);
            exit;
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $downloadUrl,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_BUFFERSIZE => 16384,
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Referer: https://drive.ucweb.com/'
            ],
            CURLOPT_HEADERFUNCTION => function($ch, $header) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                
                if (count($header) >= 2) {
                    $name = strtolower(trim($header[0]));
                    
                    if (in_array($name, ['content-type', 'content-length', 'accept-ranges'])) {
                        header(trim($header[0]) . ': ' . trim($header[1]));
                    }
                }
                
                return $len;
            },
            CURLOPT_WRITEFUNCTION => function($ch, $data) {
                echo $data;
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                return strlen($data);
            }
        ]);
        
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('X-Accel-Buffering: no');
        
        curl_exec($ch);
        
        if (curl_errno($ch)) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => 'Download failed: ' . curl_error($ch)]);
        }
        
        curl_close($ch);
    }
}

// Main handler
$downloader = new UCWebDownloader();

$url = $_GET['url'] ?? $_POST['url'] ?? '';

if (empty($url)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Parameter url diperlukan',
        'usage' => [
            'get_all_videos_recursive' => '?url=https://drive.ucweb.com/s/079eb21d6f504',
            'get_all_videos_no_subfolder' => '?url=https://drive.ucweb.com/s/079eb21d6f504&recursive=0',
            'select_specific_file' => '?url=https://drive.ucweb.com/s/079eb21d6f504&file=3',
            'download_proxy' => '?url=DOWNLOAD_URL&download=1&filename=video.mp4'
        ],
        'notes' => [
            'default_behavior' => 'fetch_all=1 dan recursive=1 (scan semua subfolder)',
            'single_file' => 'Tambahkan &file=0 untuk mendapatkan 1 video saja',
            'no_recursive' => 'Tambahkan &recursive=0 untuk hanya scan folder utama',
            'max_depth' => 'Maksimal kedalaman subfolder: 5 level'
        ],
        'examples' => [
            'Get ALL videos + subfolders (DEFAULT)' => 'GET ?url=https://drive.ucweb.com/s/ede38c0e46044',
            'Get videos in root folder only' => 'GET ?url=https://drive.ucweb.com/s/ede38c0e46044&recursive=0',
            'Get specific video only' => 'GET ?url=https://drive.ucweb.com/s/ede38c0e46044&file=5',
            'Download via proxy' => 'GET ?url=https://pds-sg363...&download=1&filename=video.mp4'
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// Mode download langsung
if (isset($_GET['download']) || isset($_POST['download'])) {
    $filename = $_GET['filename'] ?? $_POST['filename'] ?? 'video.mp4';
    $downloader->proxyDownload($url, $filename);
    exit;
}

// Mode get info / process folder
$fileIndex = isset($_GET['file']) || isset($_POST['file']) ? 
    intval($_GET['file'] ?? $_POST['file'] ?? 0) : null;

// Default fetch_all=1 dan recursive=1
$fetchAll = true;
$recursive = true;

if ($fileIndex !== null) {
    $fetchAll = false;
}

// Override jika explicitly set
if (isset($_GET['fetch_all']) || isset($_POST['fetch_all'])) {
    $fetchAll = (bool)($_GET['fetch_all'] ?? $_POST['fetch_all']);
}

if (isset($_GET['recursive']) || isset($_POST['recursive'])) {
    $recursive = (bool)($_GET['recursive'] ?? $_POST['recursive']);
}

$result = $downloader->processFolder($url, $fileIndex, $fetchAll, $recursive);

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
