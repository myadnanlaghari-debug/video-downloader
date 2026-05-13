# Video Downloader App

A modern, full-featured video downloader web application built with FastAPI and yt-dlp. Download videos and audio from YouTube, Facebook, Pinterest, TikTok, Instagram, and Twitter/X.

## Features

- 🎯 **Multiple Platforms**: YouTube, Facebook, Pinterest, TikTok, Instagram, Twitter/X
- 📹 **Video Formats**: MP4, WebM
- 🎵 **Audio Formats**: MP3, M4A, WAV
- 🎬 **Resolution Selection**: 360p, 480p, 720p, 1080p, 1440p, 2160p (4K), Best Available
- 🖥️ **Beautiful Frontend**: Modern, responsive UI with real-time video info preview
- ⚡ **Fast Downloads**: Streaming downloads with automatic cleanup
- 🔒 **Privacy First**: No registration required, no data stored

## Project Structure

```
/workspace
├── main.py              # FastAPI backend
├── requirements.txt     # Python dependencies
├── Procfile            # Railway deployment config
├── static/
│   └── index.html      # Frontend UI
└── README.md           # This file
```

## API Endpoints

### GET `/`
Serves the frontend HTML interface

### GET `/formats`
Returns available platforms, resolutions, and formats

**Response:**
```json
{
  "platforms": ["YouTube", "Facebook", "Pinterest", "TikTok", "Instagram", "Twitter/X"],
  "resolutions": ["360p", "480p", "720p", "1080p", "1440p", "2160p", "best"],
  "formats": {
    "video": ["mp4", "webm"],
    "audio": ["mp3", "m4a", "wav"]
  }
}
```

### GET `/info?url=<video_url>`
Get video information without downloading

**Parameters:**
- `url` (required): Video URL to analyze

**Response:**
```json
{
  "title": "Video Title",
  "duration": 180,
  "thumbnail": "https://...",
  "uploader": "Channel Name",
  "available_formats": ["360p", "480p", "720p", "1080p"],
  "url": "https://..."
}
```

### GET `/download?url=<video_url>&format=mp4&resolution=720p`
Download video/audio in specified format and resolution

**Parameters:**
- `url` (required): Video URL to download
- `format` (optional, default: mp4): Output format (mp4, webm, mp3, m4a, wav)
- `resolution` (optional, default: 720p): Video resolution (360p-2160p, best)

**Response:** File download stream

## Local Development

### Prerequisites
- Python 3.8+
- FFmpeg (for audio conversion)

### Installation

1. Clone or navigate to the project:
```bash
cd /workspace
```

2. Install dependencies:
```bash
pip install -r requirements.txt
```

3. Run the server:
```bash
python main.py
```

Or with uvicorn directly:
```bash
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

4. Open your browser:
- Frontend: http://localhost:8000
- API Docs: http://localhost:8000/docs

## Deploy to Railway

### Option 1: GitHub Integration (Recommended)

1. Push code to GitHub:
```bash
git init
git add .
git commit -m "Initial commit: Video Downloader App"
git branch -M main
git remote add origin <your-github-repo-url>
git push -u origin main
```

2. Deploy on Railway:
   - Go to [railway.app](https://railway.app)
   - Click "New Project"
   - Select "Deploy from GitHub repo"
   - Choose your repository
   - Railway will automatically detect and deploy

### Option 2: Railway CLI

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login to Railway
railway login

# Initialize project
railway init

# Deploy
railway up
```

### Environment Variables (Optional)

Railway automatically sets the `PORT` environment variable. No additional configuration needed.

## Usage Examples

### Download YouTube video in 1080p MP4
```
GET /download?url=https://youtube.com/watch?v=VIDEO_ID&format=mp4&resolution=1080p
```

### Extract audio as MP3
```
GET /download?url=https://youtube.com/watch?v=VIDEO_ID&format=mp3
```

### Download TikTok video (best quality)
```
GET /download?url=https://tiktok.com/@user/video/123...&format=mp4&resolution=best
```

### Get video info before downloading
```
GET /info?url=https://youtube.com/watch?v=VIDEO_ID
```

## Supported Platforms

| Platform | Video | Audio | Max Resolution |
|----------|-------|-------|----------------|
| YouTube | ✅ | ✅ | 4K (2160p) |
| Facebook | ✅ | ✅ | 1080p |
| Pinterest | ✅ | ❌ | 1080p |
| TikTok | ✅ | ✅ | 1080p |
| Instagram | ✅ | ✅ | 1080p |
| Twitter/X | ✅ | ✅ | 1080p |

## Technical Details

- **Backend**: FastAPI (Python)
- **Download Engine**: yt-dlp
- **Frontend**: Vanilla HTML/CSS/JavaScript
- **File Handling**: Streaming response with automatic cleanup
- **CORS**: Enabled for cross-origin requests

## Notes

- Some platforms may have restrictions on downloading certain content
- Copyright: Only download content you have permission to download
- Large files may take longer to process and download
- Temporary files are automatically deleted after download

## Troubleshooting

### FFmpeg not found
Install FFmpeg for your system:
- Ubuntu/Debian: `sudo apt-get install ffmpeg`
- macOS: `brew install ffmpeg`
- Windows: Download from [ffmpeg.org](https://ffmpeg.org)

### Download fails for specific videos
- The video may be private or restricted
- Try a different resolution or format
- Check if the URL is correct and accessible

### Railway deployment issues
- Check build logs in Railway dashboard
- Ensure all files are committed to Git
- Verify `requirements.txt` includes all dependencies

## License

This project is for educational purposes. Please respect copyright laws and terms of service of the platforms you download from.

## Support

For issues or questions:
1. Check the API documentation at `/docs`
2. Review troubleshooting section above
3. Check Railway logs if deployed

---

Built with ❤️ using FastAPI and yt-dlp
