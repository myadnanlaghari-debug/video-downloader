const express = require('express');
const cors = require('cors');
const { exec } = require('child_process');
const path = require('path');
const fs = require('fs');

const app = express();
app.use(cors());
app.use(express.static('public'));

app.get('/download', async (req, res) => {
  const { url, format = 'mp4' } = req.query;

  if (!url) return res.status(400).send('URL is required');

  try {
    const title = `video_${Date.now()}`;
    const outputPath = path.join(__dirname, `${title}.${format}`);

    let command;

    if (format === 'mp3') {
      command = `yt-dlp -f bestaudio --extract-audio --audio-format mp3 --audio-quality 0 -o "${outputPath}" "${url}"`;
    } else {
      command = `yt-dlp -f "bestvideo[ext=mp4]+bestaudio[ext=m4a]/best" --merge-output-format mp4 -o "${outputPath}" "${url}"`;
    }

    res.setHeader('Content-Disposition', `attachment; filename="downloaded.${format}"`);

    exec(command, (error, stdout, stderr) => {
      if (error) {
        console.error(`Error: ${error.message}`);
        return res.status(500).send('Download failed. Try a different video.');
      }

      // Stream the file to user
      const fileStream = fs.createReadStream(outputPath);
      fileStream.pipe(res);

      fileStream.on('end', () => {
        // Clean up file after download
        setTimeout(() => fs.unlink(outputPath, () => {}), 5000);
      });
    });

  } catch (err) {
    res.status(500).send('Server error occurred.');
  }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`🚀 Server running on port ${PORT}`);
});
