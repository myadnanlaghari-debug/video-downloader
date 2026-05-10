const express = require('express');
const cors = require('cors');
const { exec } = require('child_process');

const app = express();
app.use(cors());
app.use(express.static('public'));

app.get('/download', (req, res) => {
  const { url, format = 'mp4' } = req.query;

  if (!url) return res.status(400).send('URL is required');

  console.log(`📥 Download request: ${format} - ${url}`);

  res.setHeader('Content-Type', format === 'mp3' ? 'audio/mpeg' : 'video/mp4');
  res.setHeader('Content-Disposition', `attachment; filename="video.${format}"`);

  let command;

  if (format === 'mp3') {
    command = `yt-dlp -f bestaudio --extract-audio --audio-format mp3 --audio-quality 0 -o - "${url}"`;
  } else {
    command = `yt-dlp -f "bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best" --merge-output-format mp4 -o - "${url}"`;
  }

  const proc = exec(command);

  proc.stdout.pipe(res);

  proc.stderr.on('data', (data) => {
    console.log(`[yt-dlp] ${data}`);
  });

  proc.on('error', (err) => {
    console.error(err);
    if (!res.headersSent) res.status(500).send('Download failed');
  });

  proc.on('close', (code) => {
    if (code !== 0) {
      console.error(`Process exited with code ${code}`);
    }
  });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`🚀 Server running on port ${PORT}`);
});
