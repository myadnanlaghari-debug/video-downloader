import os
import uuid
from fastapi import FastAPI, Query, HTTPException
from fastapi.responses import StreamingResponse, JSONResponse
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import Optional, List
import yt_dlp
from dotenv import load_dotenv

app = FastAPI(
    title="Social Media Video Downloader API",
    description="Download videos from YouTube, Facebook, Pinterest, TikTok with resolution and format selection",
    version="1.0.0"
)

# Load environment variables from .env file
load_dotenv()

# CORS configuration
allowed_origin = os.getenv("ALLOWED_ORIGIN", "*")
if allowed_origin == "*":
    # Allow all origins for development
    app.add_middleware(CORSMiddleware,
        allow_origins=["*"],
        allow_credentials=True,
        allow_methods=["*"],
        allow_headers=["*"],
    )
else:
    app.add_middleware(CORSMiddleware,
        allow_origins=[allowed_origin],
        allow_credentials=True,
        allow_methods=["*"],
        allow_headers=["*"],
    )


class VideoInfo(BaseModel):
    title: str
    duration: Optional[int] = None
    thumbnail: Optional[str] = None
    uploader: Optional[str] = None
    available_formats: List[dict]


class DownloadResponse(BaseModel):
    filename: str
    format: str
    resolution: Optional[str] = None
    size_mb: Optional[float] = None


def get_format_for_resolution(resolution: str, audio_only: bool = False) -> str:
    """Get yt-dlp format string based on resolution selection."""
    if audio_only:
        return "bestaudio/best"
    
    format_map = {
        "360p": "bestvideo[height<=360]+bestaudio/best[height<=360]/best",
        "480p": "bestvideo[height<=480]+bestaudio/best[height<=480]/best",
        "720p": "bestvideo[height<=720]+bestaudio/best[height<=720]/best",
        "1080p": "bestvideo[height<=1080]+bestaudio/best[height<=1080]/best",
        "1440p": "bestvideo[height<=1440]+bestaudio/best[height<=1440]/best",
        "2160p": "bestvideo[height<=2160]+bestaudio/best[height<=2160]/best",
        "best": "bestvideo+bestaudio/best",
    }
    return format_map.get(resolution, "bestvideo+bestaudio/best")


def cleanup_file(file_path: str):
    """Clean up downloaded file."""
    try:
        if os.path.exists(file_path):
            os.unlink(file_path)
    except Exception:
        pass


@app.get("/")
async def root():
    """Welcome endpoint with API information."""
    return {
        "message": "Welcome to the Social Media Video Downloader API",
        "supported_sites": ["YouTube", "Facebook", "Pinterest", "TikTok", "Instagram", "Twitter"],
        "endpoints": {
            "/info": "Get video information without downloading",
            "/download": "Download video with format and resolution options",
            "/formats": "List available format options"
        },
        "usage": "/download?url=<video_url>&format=mp4&resolution=720p or /download?url=<video_url>&format=mp3"
    }


@app.get("/formats")
async def list_formats():
    """List available download formats and resolutions."""
    return {
        "video_formats": ["mp4", "webm"],
        "audio_formats": ["mp3", "m4a", "wav"],
        "resolutions": ["360p", "480p", "720p", "1080p", "1440p", "2160p", "best"],
        "default": {"format": "mp4", "resolution": "720p"}
    }


@app.get("/info", response_model=VideoInfo)
async def get_video_info(url: str = Query(..., description="Video URL from supported platforms")):
    """Extract video metadata without downloading."""
    try:
        ydl_opts = {
            'quiet': True,
            'no_warnings': True,
            'skip_download': True,
            'extract_flat': False,
        }
        
        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            info = ydl.extract_info(url, download=False)
            
            # Extract available formats
            available_formats = []
            formats = info.get('formats', [])
            seen_heights = set()
            
            for f in formats:
                height = f.get('height')
                ext = f.get('ext', '')
                vcodec = f.get('vcodec')
                
                if height and height not in seen_heights:
                    seen_heights.add(height)
                    format_info = {
                        "resolution": f"{height}p",
                        "extension": ext,
                        "has_video": vcodec is not None,
                    }
                    available_formats.append(format_info)
            
            # Sort by resolution
            available_formats.sort(key=lambda x: int(x['resolution'][:-1]) if x['resolution'][:-1].isdigit() else 0)
            
            return VideoInfo(
                title=info.get("title", "Unknown").replace("/", "-").replace("\\", "-"),
                duration=info.get("duration"),
                thumbnail=info.get("thumbnail"),
                uploader=info.get("uploader") or info.get("channel"),
                available_formats=available_formats[-10:]  # Limit to last 10 formats
            )
            
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Error fetching video info: {str(e)}")


@app.get("/download")
async def download_video(
    url: str = Query(..., description="Video URL from YouTube, Facebook, Pinterest, TikTok, etc."),
    format: str = Query("mp4", description="Output format: mp4, mp3, m4a, webm"),
    resolution: Optional[str] = Query("720p", description="Video resolution: 360p, 480p, 720p, 1080p, 1440p, 2160p, best")
):
    """
    Download video/audio from supported platforms.
    
    - **url**: Video URL from YouTube, Facebook, Pinterest, TikTok, Instagram, Twitter
    - **format**: Output format (mp4 for video, mp3 for audio)
    - **resolution**: Video quality (ignored for audio-only downloads)
    """
    try:
        # Validate format
        format = format.lower().strip()
        audio_only = format in ["mp3", "m4a", "wav", "aac"]
        
        # Get appropriate format string
        yt_dlp_format = get_format_for_resolution(resolution, audio_only)
        
        # Extract metadata first
        with yt_dlp.YoutubeDL({'quiet': True, 'skip_download': True, 'no_warnings': True}) as ydl:
            info = ydl.extract_info(url, download=False)
            title = info.get("title", "video").replace("/", "-").replace("\\", "-")
            # Sanitize filename
            title = "".join(c for c in title if c.isalnum() or c in " -_.").strip()[:100]
        
        # Create unique output template
        uid = uuid.uuid4().hex[:8]
        output_template = f"/tmp/{uid}.%(ext)s"
        
        # Configure yt-dlp options
        ydl_opts = {
            'format': yt_dlp_format,
            'outtmpl': output_template,
            'quiet': True,
            'no_warnings': True,
            'merge_output_format': 'mp4' if not audio_only else None,
        }
        
        # Add post-processing for audio conversion
        if audio_only:
            ydl_opts['postprocessors'] = [{
                'key': 'FFmpegExtractAudio',
                'preferredcodec': format,
                'preferredquality': '192',
            }]
        
        # Download using yt-dlp Python API
        downloaded_file_path = None
        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            result = ydl.download([url])
            
            # Get the actual file path from the download result
            if result == 0:
                # Find the downloaded file
                base_name = f"/tmp/{uid}"
                if audio_only:
                    actual_path = f"{base_name}.{format}"
                else:
                    actual_path = f"{base_name}.mp4"
                
                # Try different possible extensions
                for ext in [format, 'mp4', 'webm', 'mkv', 'm4a']:
                    test_path = f"{base_name}.{ext}"
                    if os.path.exists(test_path):
                        downloaded_file_path = test_path
                        break
                
                # Fallback: search tmp directory
                if not downloaded_file_path:
                    for f in os.listdir("/tmp"):
                        if f.startswith(uid):
                            downloaded_file_path = os.path.join("/tmp", f)
                            break
        
        if not downloaded_file_path or not os.path.exists(downloaded_file_path):
            raise HTTPException(status_code=500, detail="Download failed or file not found.")
        
        # Get file size
        file_size_mb = round(os.path.getsize(downloaded_file_path) / (1024 * 1024), 2)
        
        # Determine output filename
        output_ext = format if audio_only else "mp4"
        filename = f"{title}.{output_ext}"
        
        # Stream file
        def iterfile():
            try:
                with open(downloaded_file_path, "rb") as f:
                    yield from f
            finally:
                cleanup_file(downloaded_file_path)
        
        return StreamingResponse(
            iterfile(),
            media_type="application/octet-stream",
            headers={
                "Content-Disposition": f'attachment; filename="{filename}"',
                "X-File-Size-MB": str(file_size_mb),
                "X-Original-Title": title,
            }
        )
        
    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Error during download: {str(e)}")


@app.post("/download")
async def download_video_post(
    url: str,
    format: str = "mp4",
    resolution: Optional[str] = "720p"
):
    """POST endpoint for downloading (useful for longer URLs)."""
    return await download_video(url=url, format=format, resolution=resolution)


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)
