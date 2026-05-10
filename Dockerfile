FROM node:20-slim

# Install system dependencies + yt-dlp properly via pip (recommended in 2026)
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-venv \
    ffmpeg \
    curl \
    && rm -rf /var/lib/apt/lists/* \
    && python3 -m venv /opt/yt-dlp-venv \
    && /opt/yt-dlp-venv/bin/pip install --upgrade pip \
    && /opt/yt-dlp-venv/bin/pip install yt-dlp[default] \
    && ln -s /opt/yt-dlp-venv/bin/yt-dlp /usr/local/bin/yt-dlp

WORKDIR /app

COPY package*.json ./
RUN npm install --omit=dev

COPY . .

RUN mkdir -p temp

EXPOSE 3000

ENV NODE_OPTIONS="--max-old-space-size=512"
ENV PATH="/opt/yt-dlp-venv/bin:$PATH"

CMD ["node", "server.js"]
