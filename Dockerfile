FROM node:20-slim

RUN apt-get update && apt-get install -y \
    python3 python3-pip ffmpeg curl \
    && rm -rf /var/lib/apt/lists/* \
    && curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp \
    && chmod a+rx /usr/local/bin/yt-dlp

WORKDIR /app

COPY package*.json ./
RUN npm install --omit=dev

COPY . .

EXPOSE 3000

# Increase memory limit for Railway
ENV NODE_OPTIONS="--max-old-space-size=512"

CMD ["node", "server.js"]
