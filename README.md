# Social Media Video Downloader API

A FastAPI-based web API for downloading videos from YouTube, Facebook, Pinterest, TikTok, and more using yt-dlp.

## Features

- **Multi-platform support**: YouTube, Facebook, Pinterest, TikTok, Instagram, Twitter
- **Resolution selection**: 360p, 480p, 720p, 1080p, 1440p, 2160p (4K), or best available
- **Format options**: 
  - Video: MP4, WebM
  - Audio: MP3, M4A, WAV, AAC
- **Stream downloads**: Files are streamed directly without storing on server
- **Auto cleanup**: Downloaded files are automatically removed after streaming
- **CORS enabled**: Ready for frontend integration

## Installation

### Prerequisites

- Python 3.12+
- FFmpeg (required for audio extraction and format conversion)

### Install Dependencies

```bash
# Using uv (recommended)
uv sync

# Or using pip
pip install -r requirements.txt
```

### Install FFmpeg

**Ubuntu/Debian:**
```bash
sudo apt update && sudo apt install ffmpeg
```

**macOS:**
```bash
brew install ffmpeg
```

**Windows:**
Download from https://ffmpeg.org/download.html or use:
```bash
choco install ffmpeg
```

## Usage

### Start the Server

```bash
python main.py
# or
uvicorn main:app --host 0.0.0.0 --port 8000 --reload
```

The API will be available at `http://localhost:8000`

### API Endpoints

#### 1. Root Endpoint
```
GET /
```
Returns API information and usage instructions.

#### 2. List Available Formats
```
GET /formats
```
Returns available video/audio formats and resolutions.

#### 3. Get Video Info
```
GET /info?url=<video_url>
```
Extract metadata without downloading (title, duration, thumbnail, available formats).

**Example:**
```bash
curl "http://localhost:8000/info?url=https://www.youtube.com/watch?v=dQw4w9WgXcQ"
```

#### 4. Download Video/Audio
```
GET /download?url=<video_url>&format=<format>&resolution=<resolution>
POST /download (with JSON body)
```

**Parameters:**
- `url` (required): Video URL from supported platforms
- `format` (optional, default: mp4): Output format (mp4, mp3, m4a, wav, webm)
- `resolution` (optional, default: 720p): Video quality (360p, 480p, 720p, 1080p, 1440p, 2160p, best)

**Examples:**

Download YouTube video in 720p MP4:
```bash
curl -O -J "http://localhost:8000/download?url=https://www.youtube.com/watch?v=dQw4w9WgXcQ&format=mp4&resolution=720p"
```

Download YouTube video in 1080p:
```bash
curl -O -J "http://localhost:8000/download?url=https://www.youtube.com/watch?v=dQw4w9WgXcQ&format=mp4&resolution=1080p"
```

Extract audio as MP3:
```bash
curl -O -J "http://localhost:8000/download?url=https://www.youtube.com/watch?v=dQw4w9WgXcQ&format=mp3"
```

Download TikTok video:
```bash
curl -O -J "http://localhost:8000/download?url=https://www.tiktok.com/@user/video/1234567890&format=mp4&resolution=720p"
```

Download Facebook video:
```bash
curl -O -J "http://localhost:8000/download?url=https://www.facebook.com/watch/?v=1234567890&format=mp4"
```

Download Pinterest video:
```bash
curl -O -J "http://localhost:8000/download?url=https://www.pinterest.com/pin/1234567890/&format=mp4"
```

### POST Request Example

```bash
curl -X POST "http://localhost:8000/download" \
  -H "Content-Type: application/json" \
  -d '{"url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ", "format": "mp4", "resolution": "1080p"}'
```

## API Documentation

Interactive API documentation is available at:
- Swagger UI: `http://localhost:8000/docs`
- ReDoc: `http://localhost:8000/redoc`

## Configuration

### Environment Variables

Create a `.env` file in the project root:

```env
ALLOWED_ORIGIN=*
```

- `ALLOWED_ORIGIN`: CORS allowed origin (default: `*` for all origins)

## Supported Platforms

- ✅ YouTube
- ✅ Facebook
- ✅ Pinterest
- ✅ TikTok
- ✅ Instagram
- ✅ Twitter/X
- And 1000+ more sites supported by yt-dlp

## Response Headers

The download endpoint includes custom headers:
- `Content-Disposition`: Filename for download
- `X-File-Size-MB`: File size in megabytes
- `X-Original-Title`: Original video title

## Error Handling

The API returns appropriate HTTP status codes:
- `200`: Success
- `400`: Bad request (invalid parameters)
- `404`: Video not found or unavailable
- `500`: Server error during download

## Security Notes

⚠️ **Important**: This tool is for educational purposes only. Please respect:
- Copyright laws and terms of service of each platform
- Content creators' rights
- Platform-specific download policies

## License

MIT License - For educational purposes only

## Troubleshooting

### Common Issues

1. **FFmpeg not found**: Install FFmpeg and ensure it's in your PATH
2. **Download fails**: Some videos may be region-restricted or private
3. **Audio extraction fails**: Ensure FFmpeg is properly installed with codec support

### Logs

Run with debug mode for detailed logs:
```bash
uvicorn main:app --host 0.0.0.0 --port 8000 --log-level debug
```
