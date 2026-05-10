const express = require('express');
const cors = require('cors');
const { exec } = require('child_process');
const path = require('path');
const fs = require('fs');

const app = express();
app.use(cors());
app.use(express.static('public'));

const TEMP_DIR = path.join(__dirname, 'temp');
if (!fs.existsSync(TEMP_DIR)) fs.mkdirSync(TEMP_DIR);

app.get('/download', (req, res) => {
  const { url, format = 'mp4' } = req.query;

  if (!url) return res.status(400).send('URL is required');

  const timestamp = Date.now();
  const ext = format === 'mp3' ? 'mp3' : 'mp4';
  const outputPath = path.join(TEMP_DIR, `video_${timestamp}.${ext}`);

  console.log(`📥 Download started: ${format.toUpperCase()} - ${url}`);

  let command = format === 'mp3'
    ? `yt-dlp -f bestaudio --extract-audio --audio-format mp3 --audio-quality 0 --no-warnings -o "${outputPath}" "${url}"`
    : `yt-dlp -f "bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best" --merge-output-format mp4 --no-warnings -o "${outputPath}" "${url}"`;

  exec(command, { maxBuffer: 1024 * 1024 * 500 }, (error, stdout, stderr) => {
    if (error) {
      console.error("yt-dlp Error:", stderr || error.message);
      return res.status(500).send(`Download failed.<br><small>${stderr ? stderr.substring(0, 300) : error.message}</small>`);
    }

    if (!fs.existsSync(outputPath) || fs.statSync(outputPath).size < 10000) {
      return res.status(500).send('Downloaded file is empty or too small. Try another video.');
    }

    res.setHeader('Content-Type', format === 'mp3' ? 'audio/mpeg' : 'video/mp4');
    res.setHeader('Content-Disposition', `attachment; filename="video.${ext}"`);

    const stream = fs.createReadStream(outputPath);
    stream.pipe(res);

    stream.on('end', () => {
      setTimeout(() => fs.unlink(outputPath, () => {}), 15000);
    });
  });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`🚀 Server running on port ${PORT}`);
});
