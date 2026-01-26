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
      console.error(err);
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
    <div className="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
      <div className="container mx-auto px-4 py-8 max-w-7xl">
        {/* Header */}
        <div className="text-center mb-12">
          <div className="flex items-center justify-center gap-3 mb-4">
            <Film className="w-12 h-12 text-purple-400" />
            <h1 className="text-5xl font-bold text-white">UC Share Viewer</h1>
          </div>
          <p className="text-gray-300 text-lg">Download and view videos from UC Share links</p>
        </div>

        {/* Search Input */}
        <div className="mb-12">
          <div className="max-w-3xl mx-auto">
            <div className="relative">
              <input
                type="text"
                value={url}
                onChange={(e) => setUrl(e.target.value)}
                onKeyPress={handleKeyPress}
                placeholder="Enter UC Share URL (e.g., https://uc-share.com/s/6541c36f1a754?la=id)"
                className="w-full px-6 py-4 pr-32 rounded-2xl bg-white/10 backdrop-blur-lg border border-purple-500/30 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-lg"
              />
              <button
                onClick={fetchVideos}
                disabled={loading}
                className="absolute right-2 top-2 bottom-2 px-6 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-semibold transition-all duration-200 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {loading ? (
                  <>
                    <Loader2 className="w-5 h-5 animate-spin" />
                    <span>Loading...</span>
                  </>
                ) : (
                  <>
                    <Search className="w-5 h-5" />
                    <span>Search</span>
                  </>
                )}
              </button>
            </div>
          </div>
        </div>

        {/* Error Message */}
        {error && (
          <div className="max-w-3xl mx-auto mb-8">
            <div className="bg-red-500/20 border border-red-500/50 rounded-xl px-6 py-4 text-red-200">
              {error}
            </div>
          </div>
        )}

        {/* Videos Grid */}
        {videos.length > 0 && (
          <div className="space-y-6">
            <div className="flex items-center justify-between max-w-7xl mx-auto">
              <h2 className="text-2xl font-bold text-white">
                Found {videos.length} video{videos.length !== 1 ? 's' : ''}
              </h2>
              <div className="text-gray-400">
                Total size: {videos.reduce((acc, v) => acc + v.size_mb, 0).toFixed(2)} MB
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {videos.map((video, index) => (
                <div
                  key={index}
                  className="bg-white/5 backdrop-blur-lg rounded-2xl overflow-hidden border border-purple-500/20 hover:border-purple-500/50 transition-all duration-300 hover:transform hover:scale-105"
                >
                  {/* Thumbnail */}
                  <a
                    href={video.download.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="block relative group"
                  >
                    <img
                      src={video.download.thumbnail}
                      alt={video.name}
                      className="w-full h-48 object-cover"
                    />
                    <div className="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                      <ExternalLink className="w-12 h-12 text-white" />
                    </div>
                    <div className="absolute top-3 right-3 bg-black/70 px-3 py-1 rounded-full text-white text-sm">
                      {video.video_info.duration_formatted}
                    </div>
                  </a>

                  {/* Content */}
                  <div className="p-5 space-y-4">
                    {/* Name */}
                    <h3 className="text-white font-semibold text-lg line-clamp-2 min-h-[3.5rem]">
                      {video.name}
                    </h3>

                    {/* Video Info */}
                    <div className="flex flex-wrap gap-2 text-sm">
                      <span className="bg-purple-500/20 text-purple-300 px-3 py-1 rounded-full">
                        {video.video_info.resolution.label}
                      </span>
                      <span className="bg-blue-500/20 text-blue-300 px-3 py-1 rounded-full">
                        {video.size_mb.toFixed(2)} MB
                      </span>
                      <span className="bg-green-500/20 text-green-300 px-3 py-1 rounded-full">
                        {video.video_info.fps} FPS
                      </span>
                    </div>

                    {/* Download Button */}
                    <a
                      href={video.download.url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="block w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white py-3 rounded-xl font-semibold transition-all duration-200 text-center flex items-center justify-center gap-2"
                    >
                      <Download className="w-5 h-5" />
                      <span>Download Video</span>
                    </a>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Empty State */}
        {!loading && videos.length === 0 && !error && (
          <div className="text-center py-20">
            <Film className="w-24 h-24 text-gray-600 mx-auto mb-4" />
            <p className="text-gray-400 text-xl">
              Enter a UC Share URL above to get started
            </p>
          </div>
        )}
      </div>
    </div>
  );
}
