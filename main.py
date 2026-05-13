from fastapi import FastAPI, HTTPException, Query, Request
from fastapi.responses import StreamingResponse, HTMLResponse
from fastapi.staticfiles import StaticFiles
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import Optional, List
import yt_dlp
import io
import os
import tempfile
import uuid

app = FastAPI(
    title="Video Downloader API",
    description="Download videos from YouTube, Facebook, Pinterest, TikTok and more",
    version="1.0.0"
)

# Enable CORS for frontend
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Supported platforms
SUPPORTED_PLATFORMS = [
    "YouTube",
    "Facebook", 
    "Pinterest",
    "TikTok",
    "Instagram",
    "Twitter/X"
]

# Available resolutions
RESOLUTIONS = ["360p", "480p", "720p", "1080p", "1440p", "2160p", "best"]

# Available formats
FORMATS = {
    "video": ["mp4", "webm"],
    "audio": ["mp3", "m4a", "wav"]
}


class VideoInfo(BaseModel):
    title: str
    duration: Optional[int] = None
    thumbnail: Optional[str] = None
    uploader: Optional[str] = None
    available_formats: List[str] = []
    url: str


def get_format_options(format_type: str, resolution: str):
    """Get yt-dlp format options based on user selection"""
    if format_type in ["mp3", "m4a", "wav"]:
        return {
            'format': 'bestaudio/best',
            'postprocessors': [{
                'key': 'FFmpegExtractAudio',
                'preferredcodec': format_type,
                'preferredquality': '192' if format_type == "mp3" else None,
            }],
            'postprocessor_args': ['-ar', '44100'] if format_type == "wav" else []
        }
    
    # Video formats
    quality_map = {
        "2160p": "2160",
        "1440p": "1440", 
        "1080p": "1080",
        "720p": "720",
        "480p": "480",
        "360p": "360",
        "best": "best"
    }
    
    quality = quality_map.get(resolution, "best")
    
    if format_type == "mp4":
        if quality == "best":
            format_str = "bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best"
        else:
            format_str = f"bestvideo[height<={quality}][ext=mp4]+bestaudio[ext=m4a]/best[height<={quality}][ext=mp4]/best[height<={quality}]"
    else:  # webm
        if quality == "best":
            format_str = "bestvideo[ext=webm]+bestaudio[ext=webm]/best[ext=webm]/best"
        else:
            format_str = f"bestvideo[height<={quality}][ext=webm]+bestaudio[ext=webm]/best[height<={quality}][ext=webm]/best[height<={quality}]"
    
    return {
        'format': format_str,
        'merge_output_format': format_type
    }


@app.get("/", response_class=HTMLResponse)
async def root():
    """Serve the frontend HTML interface"""
    html_path = os.path.join(os.path.dirname(__file__), "static", "index.html")
    if os.path.exists(html_path):
        with open(html_path, "r", encoding="utf-8") as f:
            return HTMLResponse(content=f.read())
    
    # Fallback if static file not found
    return HTMLResponse(content="""
    <!DOCTYPE html>
    <html>
    <head>
        <title>Video Downloader - API Running</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
            .success { color: #22c55e; }
            .endpoint { background: #f3f4f6; padding: 10px; margin: 10px 0; border-radius: 5px; }
        </style>
    </head>
    <body>
        <h1 class="success">✓ Video Downloader API is Running!</h1>
        <p>Visit <a href="/docs">/docs</a> for interactive API documentation</p>
        <h3>Available Endpoints:</h3>
        <div class="endpoint">GET /info?url=<video_url> - Get video information</div>
        <div class="endpoint">GET /download?url=<video_url>&format=mp4&resolution=720p - Download video</div>
        <div class="endpoint">GET /formats - Get available formats and resolutions</div>
        <h3>Supported Platforms:</h3>
        <p>YouTube, Facebook, Pinterest, TikTok, Instagram, Twitter/X</p>
    </body>
    </html>
    """)


@app.get("/formats")
async def get_formats():
    """Get available formats and resolutions"""
    return {
        "platforms": SUPPORTED_PLATFORMS,
        "resolutions": RESOLUTIONS,
        "formats": FORMATS
    }


@app.get("/info")
async def get_video_info(url: str = Query(..., description="Video URL to analyze")):
    """Get video information without downloading"""
    # Always use no format restriction for info endpoint
    ydl_opts = {
        'quiet': True,
        'no_warnings': True,
        'extract_flat': False,
        'ignoreerrors': True,
        'format': 'best'  # Just for metadata, won't fail
    }
    
    try:
        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            info = ydl.extract_info(url, download=False)
            
            if not info:
                raise HTTPException(status_code=404, detail="Video not found")
            
            # Extract available formats
            formats_list = []
            if 'formats' in info:
                for fmt in info['formats']:
                    if fmt.get('height'):
                        formats_list.append(f"{fmt['height']}p")
            formats_list = sorted(list(set(formats_list)), key=lambda x: int(x[:-1]) if x[:-1].isdigit() else 0)
            
            return VideoInfo(
                title=info.get('title', 'Unknown'),
                duration=info.get('duration'),
                thumbnail=info.get('thumbnail'),
                uploader=info.get('uploader') or info.get('channel'),
                available_formats=formats_list[:10] if formats_list else ["best"],
                url=url
            )
            
    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=400, detail=f"Error fetching video info: {str(e)}")


@app.get("/download")
async def download_video(
    url: str = Query(..., description="Video URL to download"),
    format: str = Query("mp4", description="Output format (mp4, webm, mp3, m4a, wav)"),
    resolution: str = Query("720p", description="Video resolution (360p, 480p, 720p, 1080p, 1440p, 2160p, best)")
):
    """Download video/audio from supported platforms"""
    
    # Validate format
    format = format.lower()
    all_formats = FORMATS["video"] + FORMATS["audio"]
    if format not in all_formats:
        raise HTTPException(status_code=400, detail=f"Unsupported format. Choose from: {all_formats}")
    
    # Validate resolution
    resolution = resolution.lower()
    if resolution not in [r.lower() for r in RESOLUTIONS]:
        raise HTTPException(status_code=400, detail=f"Unsupported resolution. Choose from: {RESOLUTIONS}")
    
    try:
        # Create temporary directory
        temp_dir = tempfile.mkdtemp()
        filename = f"download_{uuid.uuid4().hex}"
        output_path = os.path.join(temp_dir, filename)
        
        # Configure yt-dlp options
        ydl_opts = {
            'outtmpl': output_path + '.%(ext)s',
            'quiet': True,
            'no_warnings': True,
            'noplaylist': True,
            **get_format_options(format, resolution)
        }
        
        # Download the video
        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            info = ydl.extract_info(url, download=True)
            
            # Find the actual downloaded file
            downloaded_file = None
            for ext in ['mp4', 'webm', 'mp3', 'm4a', 'wav', 'mkv']:
                potential_file = f"{output_path}.{ext}"
                if os.path.exists(potential_file):
                    downloaded_file = potential_file
                    break
            
            if not downloaded_file:
                # Try to find any file in the temp directory
                files = os.listdir(temp_dir)
                if files:
                    downloaded_file = os.path.join(temp_dir, files[0])
                else:
                    raise HTTPException(status_code=500, detail="Download failed - no file created")
        
        # Get file metadata
        file_size = os.path.getsize(downloaded_file)
        content_type = "video/mp4" if format == "mp4" else \
                      "video/webm" if format == "webm" else \
                      "audio/mpeg" if format == "mp3" else \
                      "audio/mp4" if format == "m4a" else \
                      "audio/wav"
        
        # Generate filename
        safe_title = "".join(c for c in (info.get('title', 'video') if isinstance(info, dict) else 'video') 
                           if c.isalnum() or c in ' -_')[:50]
        download_filename = f"{safe_title}.{format}"
        
        # Create streaming response with cleanup
        def iterfile():
            try:
                with open(downloaded_file, "rb") as f:
                    while chunk := f.read(8192):
                        yield chunk
            finally:
                # Cleanup: remove temp files
                try:
                    os.remove(downloaded_file)
                    os.rmdir(temp_dir)
                except:
                    pass
        
        headers = {
            "Content-Disposition": f'attachment; filename="{download_filename}"',
            "Content-Length": str(file_size)
        }
        
        return StreamingResponse(
            iterfile(),
            media_type=content_type,
            headers=headers
        )
        
    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Download failed: {str(e)}")


if __name__ == "__main__":
    import os
    port = int(os.environ.get("PORT", 8000))
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=port)
