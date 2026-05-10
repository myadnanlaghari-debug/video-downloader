# Use official Node.js LTS image
FROM node:20-slim

# Install system dependencies for ffmpeg
RUN apt-get update && apt-get install -y \
    python3 \
    make \
    g++ \
    ffmpeg \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Copy package files first (better caching)
COPY package*.json ./

# Install dependencies
RUN npm install --production

# Copy the rest of the application
COPY . .

# Create public folder if not exists (safety)
RUN mkdir -p public

# Expose port (Railway, Render, etc. will use this)
EXPOSE 3000

# Health check (optional but good)
HEALTHCHECK --interval=30s --timeout=5s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:3000 || exit 1

# Start the application
CMD ["node", "server.js"]
