#!/bin/bash

# Ngrok Server Launcher for Workspace Booking System
# This script starts the Laravel development server and exposes it via Ngrok

echo "🚀 Starting Workspace Booking System with Ngrok..."

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if ngrok binary exists
if [ ! -f "./ngrok" ]; then
    echo -e "${YELLOW}⚠️  Ngrok binary not found in project root${NC}"
    exit 1
fi

# Check if Laravel server is already running on port 8000
if lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo -e "${BLUE}ℹ️  Laravel server already running on port 8000${NC}"
else
    echo -e "${GREEN}▶️  Starting Laravel development server...${NC}"
    php artisan serve > /dev/null 2>&1 &
    LARAVEL_PID=$!
    echo "Laravel server started (PID: $LARAVEL_PID)"
    sleep 2
fi

# Check if ngrok is already running
if pgrep -f "ngrok http" > /dev/null; then
    echo -e "${YELLOW}⚠️  Ngrok is already running. Stopping it first...${NC}"
    pkill -f "ngrok http"
    sleep 2
fi

# Start ngrok tunnel
echo -e "${GREEN}🌐 Starting Ngrok tunnel...${NC}"
./ngrok http 8000 > /dev/null 2>&1 &
NGROK_PID=$!

# Wait for ngrok to initialize
sleep 3

# Get the public URL from ngrok API
PUBLIC_URL=$(curl -s http://localhost:4040/api/tunnels | grep -o '"public_url":"https://[^"]*' | grep -o 'https://[^"]*' | head -1)

if [ -z "$PUBLIC_URL" ]; then
    echo -e "${YELLOW}⚠️  Failed to get ngrok URL. Check if authtoken is configured.${NC}"
    echo "Run: ./ngrok config add-authtoken YOUR_TOKEN"
    exit 1
fi

echo ""
echo -e "${GREEN}✅ Server is now running!${NC}"
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}🌍 Public URL:${NC} $PUBLIC_URL"
echo -e "${BLUE}🏠 Local URL:${NC}  http://127.0.0.1:8000"
echo -e "${BLUE}📊 Ngrok Dashboard:${NC} http://localhost:4040"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${YELLOW}📝 Demo Accounts:${NC}"
echo "   Admin:   admin@workspace.com / admin123"
echo "   Owner:   owner@workspace.com / password"
echo "   Manager: manager@workspace.com / password"
echo "   User:    user@workspace.com / password"
echo ""
echo -e "${YELLOW}⚠️  Note:${NC} First-time visitors may see ngrok warning (click 'Visit Site')"
echo ""
echo -e "${GREEN}Press Ctrl+C to stop the server${NC}"
echo ""

# Keep script running and handle Ctrl+C
trap 'echo -e "\n${YELLOW}🛑 Stopping servers...${NC}"; pkill -f "ngrok http"; echo -e "${GREEN}✅ Ngrok stopped${NC}"; exit 0' INT

# Wait indefinitely
while true; do
    sleep 1
done
