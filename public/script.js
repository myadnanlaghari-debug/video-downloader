async function getVideoInfo() {
  const url = document.getElementById('urlInput').value.trim();
  const resultDiv = document.getElementById('result');

  if (!url) {
    resultDiv.innerHTML = `<p style="color:red;">Please paste a valid URL</p>`;
    return;
  }

  resultDiv.innerHTML = `<p class="loading">⏳ Processing your video...</p>`;

  const baseUrl = window.location.origin;

  let downloadUrl = `${baseUrl}/download?url=${encodeURIComponent(url)}`;

  resultDiv.innerHTML = `
    <h3>✅ Video Ready!</h3>
    <a href="${downloadUrl}&format=mp4&quality=highest" class="format-btn" target="_blank">
      📹 Download MP4 - Highest Quality
    </a>
    <a href="${downloadUrl}&format=mp4&quality=medium" class="format-btn" target="_blank">
      📹 Download MP4 - Medium Quality
    </a>
    <a href="${downloadUrl}&format=mp3&quality=highest" class="format-btn" target="_blank">
      🎵 Download MP3 - High Audio (192kbps)
    </a>
    <a href="${downloadUrl}&format=mp3&quality=medium" class="format-btn" target="_blank">
      🎵 Download MP3 - Normal Audio (128kbps)
    </a>
  `;
}
