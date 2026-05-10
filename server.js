const express = require('express');
const cors = require('cors');
const fetch = require('node-fetch');

const app = express();
app.use(cors());
app.use(express.static('public'));

const RAPIDAPI_KEY = "d826864211msh7bceb0e21beef5ap1a9e2ajsn3832397c9c32";
const RAPIDAPI_HOST = "youtube-media-downloader.p.rapidapi.com";

app.get('/download', async (req, res) => {
  const { url, format = 'mp4' } = req.query;

  if (!url) return res.status(400).send('URL is required');

  try {
    console.log(`🔍 Fetching info for: ${url}`);

    // Step 1: Get video details + download links
    const response = await fetch(`https://${RAPIDAPI_HOST}/v2/video/details?url=${encodeURIComponent(url)}`, {
      method: 'GET',
      headers: {
        'x-rapidapi-key': RAPIDAPI_KEY,
        'x-rapidapi-host': RAPIDAPI_HOST
      }
    });

    const data = await response.json();

    if (!data || !data.formats) {
      return res.status(500).send('Could not fetch video details.');
    }

    // Find best format
    let selectedFormat;
    if (format === 'mp3') {
      selectedFormat = data.formats.audio.sort((a, b) => b.bitrate - a.bitrate)[0];
    } else {
      selectedFormat = data.formats.video.sort((a, b) => b.quality - a.quality)[0];
    }

    if (!selectedFormat || !selectedFormat.url) {
      return res.status(500).send('No download link found.');
    }

    // Redirect user to direct download link
    res.redirect(selectedFormat.url);

  } catch (err) {
    console.error(err);
    res.status(500).send('Server error. Please try again.');
  }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`🚀 Server running on port ${PORT}`);
});
