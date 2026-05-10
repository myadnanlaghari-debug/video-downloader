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
  const filename = `video_${timestamp}.${format}`;
  const outputPath = path.join(TEMP_DIR, filename);

  console.log(`📥 Starting download: ${format} → ${url}`);

  let command;
  if (format === 'mp3') {
    command = `yt-dlp -f bestaudio --extract-audio --audio-format mp3 --audio-quality 0 -o "${outputPath}" "${url}"`;
  } else {
    command = `yt-dlp -f "bestvideo[ext=mp4]+bestaudio[ext=m4a]/best" --merge-output-format mp4 -o "${outputPath}" "${url}"`;
  }

  exec(command, { maxBuffer: 1000 * 1024 * 1024 }, (error, stdout, stderr) => {
    if (error) {
      console.error(`Error: ${error.message}`);
      return res.status(500).send('Download failed. Video may be restricted.');
    }

    if (!fs.existsSync(outputPath) || fs.statSync(outputPath).size === 0) {
      return res.status(500).send('Downloaded file is empty. Try another video.');
    }

    res.setHeader('Content-Type', format === 'mp3' ? 'audio/mpeg' : 'video/mp4');
    res.setHeader('Content-Disposition', `attachment; filename="${filename}"`);

    const fileStream = fs.createReadStream(outputPath);
    fileStream.pipe(res);

    fileStream.on('end', () => {
      // Delete file after sending
      setTimeout(() => {
        fs.unlink(outputPath, () => {});
      }, 10000);
    });
  });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`🚀 Server running on port ${PORT}`);
});
