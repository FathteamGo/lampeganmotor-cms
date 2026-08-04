#!/bin/bash
set -e

echo "Restarting Lampegan Motor CMS..."

./stop.sh

echo "Waiting 2 seconds..."
sleep 2

./start.sh
