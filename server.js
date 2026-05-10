const express = require('express');
const ytdl = require('@distube/ytdl-core');
const ffmpeg = require('fluent-ffmpeg');
const cors = require('cors');

const app = express();
app.use(cors());
app.use(express.static('public'));

// YouTube Download Endpoint
app.get('/download', async (req, res) => {
  const { url, format = 'mp4', quality = 'highest' } = req.query;

  if (!url) return res.status(400).send('URL is required');

  try {
    const info = await ytdl.getInfo(url);
    const title = info.videoDetails.title
      .replace(/[^a-zA-Z0-9._-]/g, '_')
      .substring(0, 100);

    res.setHeader('Content-Disposition', `attachment; filename="${title}.${format}"`);

    if (format === 'mp3') {
      ytdl(url, { quality: 'highestaudio' })
        .pipe(
          ffmpeg()
            .audioBitrate(quality === 'highest' ? 192 : 128)
            .format('mp3')
            .pipe(res)
        );
    } else {
      // MP4
      const videoFormat = ytdl.chooseFormat(info.formats, {
        quality: quality,
        filter: 'videoandaudio'
      });

      ytdl(url, { format: videoFormat }).pipe(res);
    }
  } catch (err) {
    console.error(err);
    res.status(500).send('Failed to process video. It may be private, age-restricted, or blocked.');
  }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`🚀 Server running on port ${PORT}`);
});
