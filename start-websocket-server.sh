#!/bin/bash

echo "Starting Laravel Chat App WebSocket Server..."
echo

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "ERROR: PHP is not installed or not in PATH"
    echo "Please install PHP and add it to your system PATH"
    exit 1
fi

# Check if we're in the correct directory
if [ ! -f "artisan" ]; then
    echo "ERROR: artisan file not found"
    echo "Please run this script from the Laravel project root directory"
    exit 1
fi

# Set environment variables for WebSocket server
export REVERB_SERVER_HOST=${REVERB_SERVER_HOST:-0.0.0.0}
export REVERB_SERVER_PORT=${REVERB_SERVER_PORT:-8080}

echo "Configuration:"
echo "- Host: $REVERB_SERVER_HOST"
echo "- Port: $REVERB_SERVER_PORT"
echo "- Environment: ${APP_ENV:-local}"
echo

echo "Starting WebSocket server..."
echo "Press Ctrl+C to stop the server"
echo

# Function to handle cleanup on script exit
cleanup() {
    echo
    echo "Shutting down WebSocket server..."
    exit 0
}

# Set up signal handlers
trap cleanup SIGINT SIGTERM

# Start the WebSocket server
php artisan reverb:start --host="$REVERB_SERVER_HOST" --port="$REVERB_SERVER_PORT"

echo
echo "WebSocket server stopped."
