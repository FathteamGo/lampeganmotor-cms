#!/bin/bash

echo "Stopping Lampegan Motor CMS..."

# Stop Laravel server
if [ -f storage/framework/server.pid ]; then
    SERVER_PID=$(cat storage/framework/server.pid)
    if kill -0 $SERVER_PID 2>/dev/null; then
        echo "Stopping PHP built-in server (PID: $SERVER_PID)..."
        kill $SERVER_PID 2>/dev/null
    fi
    rm storage/framework/server.pid
else
    # Fallback
    SERVER_PID=$(lsof -t -i:8000)
    if [ ! -z "$SERVER_PID" ]; then
        echo "Stopping PHP built-in server (PID: $SERVER_PID)..."
        kill $SERVER_PID 2>/dev/null
    fi
fi

# Stop Queue worker
if [ -f storage/framework/queue.pid ]; then
    QUEUE_PID=$(cat storage/framework/queue.pid)
    if kill -0 $QUEUE_PID 2>/dev/null; then
        echo "Stopping queue worker (PID: $QUEUE_PID)..."
        kill $QUEUE_PID 2>/dev/null
    fi
    rm storage/framework/queue.pid
elif [ -f storage/app/queue.pid ]; then
    QUEUE_PID=$(cat storage/app/queue.pid)
    if kill -0 $QUEUE_PID 2>/dev/null; then
        echo "Stopping queue worker (PID: $QUEUE_PID)..."
        kill $QUEUE_PID 2>/dev/null
    fi
    rm storage/app/queue.pid
else
    # Fallback
    pkill -f "php artisan queue:work" 2>/dev/null
fi

# Stop database container
echo "Stopping database container..."
docker-compose stop mysql

echo "All services stopped."
