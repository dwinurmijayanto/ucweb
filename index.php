import React, { useState } from 'react';
import { Search, Download, ExternalLink, Loader2, Film } from 'lucide-react';

export default function UCShareViewer() {
  const [url, setUrl] = useState('');
  const [loading, setLoading] = useState(false);
  const [videos, setVideos] = useState([]);
  const [error, setError] = useState('');

  const fetchVideos = async () => {
    if (!url.trim()) {
      setError('Please enter a UC Share URL');
      return;
    }

    setLoading(true);
    setError('');
    setVideos([]);

    try {
      const apiUrl = `https://ucweb-five.vercel.app/api/?url=${encodeURIComponent(url)}`;
      const response = await fetch(apiUrl);
      const data = await response.json();

      if (data.status === 'success' && data.videos) {
        setVideos(data.videos);
      } else {
        setError('No videos found or invalid response');
      }
    } catch (err) {
      setError('Failed to fetch videos. Please check the URL and try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleKeyPress = (e) => {
    if (e.key === 'Enter') {
      fetchVideos();
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 p-4">
      <div className="max-w-7xl mx-auto">
        
        <div className="text-center mb-8">
          <div className="flex items-center justify-center gap-3 mb-4">
            <Film className="w-10 h-10 text-purple-400" />
            <h1 className="text-4xl font-bold text-white">UC Share Viewer</h1>
          </div>
          <p className="text-gray-300">Download and view videos from UC Share links</p>
        </div>

        <div className="mb-8 max-w-3xl mx-auto">
          <div className="relative">
            <input
              type="text"
              value={url}
              onChange={(e) => setUrl(e.target.value)}
              onKeyPress={handleKeyPress}
              placeholder="Enter UC Share URL (e.g., https://uc-share.com/s/6541c36f1a754?la=id)"
              className="w-full px-5 py-3 pr-28 rounded-xl bg-white/10 backdrop-blur border border-purple-500/30 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
            />
            <button
              onClick={fetchVideos}
              disabled={loading}
              className="absolute right-2 top-2 bottom-2 px-5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-lg font-semibold transition-all flex items-center gap-2 disabled:opacity-50"
            >
              {loading ? (
                <>
                  <Loader2 className="w-4 h-4 animate-spin" />
                  <span className="hidden sm:inline">Loading</span>
                </>
              ) : (
                <>
                  <Search className="w-4 h-4" />
                  <span className="hidden sm:inline">Search</span>
                </>
              )}
            </button>
          </div>
        </div>

        {error && (
          <div className="max-w-3xl mx-auto mb-6">
            <div className="bg-red-500/20 border border-red-500/50 rounded-lg px-5 py-3 text-red-200">
              {error}
            </div>
          </div>
        )}

        {videos.length > 0 && (
          <div className="space-y-6">
            <div className="flex flex-wrap items-center justify-between gap-4">
              <h2 className="text-2xl font-bold text-white">
                Found {videos.length} video{videos.length !== 1 ? 's' : ''}
              </h2>
              <div className="text-gray-400">
                Total: {videos.reduce((acc, v) => acc + v.size_mb, 0).toFixed(2)} MB
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
              {videos.map((video, index) => (
                <div
                  key={index}
                  className="bg-white/5 backdrop-blur rounded-xl overflow-hidden border border-purple-500/20 hover:border-purple-500/50 transition-all hover:scale-105"
                >
                  <a
                    href={video.download.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="block relative group"
                  >
                    <img
                      src={video.download.thumbnail}
                      alt={video.name}
                      className="w-full h-44 object-cover"
                    />
                    <div className="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                      <ExternalLink className="w-10 h-10 text-white" />
                    </div>
                    <div className="absolute top-2 right-2 bg-black/70 px-2 py-1 rounded text-white text-xs">
                      {video.video_info.duration_formatted}
                    </div>
                  </a>

                  <div className="p-4 space-y-3">
                    <h3 className="text-white font-semibold line-clamp-2 min-h-[3rem]">
                      {video.name}
                    </h3>

                    <div className="flex flex-wrap gap-2 text-xs">
                      <span className="bg-purple-500/20 text-purple-300 px-2 py-1 rounded-full">
                        {video.video_info.resolution.label}
                      </span>
                      <span className="bg-blue-500/20 text-blue-300 px-2 py-1 rounded-full">
                        {video.size_mb.toFixed(2)} MB
                      </span>
                      <span className="bg-green-500/20 text-green-300 px-2 py-1 rounded-full">
                        {video.video_info.fps} FPS
                      </span>
                    </div>

                    <a
                      href={video.download.url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="block w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white py-2.5 rounded-lg font-semibold transition-all text-center flex items-center justify-center gap-2"
                    >
                      <Download className="w-4 h-4" />
                      <span>Download</span>
                    </a>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {!loading && videos.length === 0 && !error && (
          <div className="text-center py-16">
            <Film className="w-20 h-20 text-gray-600 mx-auto mb-4" />
            <p className="text-gray-400 text-lg">
              Enter a UC Share URL above to get started
            </p>
          </div>
        )}
      </div>
    </div>
  );
}
