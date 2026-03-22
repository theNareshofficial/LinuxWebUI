#!/bin/bash
# run.sh
# Choose HOW to run LinuxWebUI
# Usage:
#   ./run.sh docker    → runs with Docker
#   ./run.sh linux     → runs directly on Linux Apache

MODE=$1

if [ "$MODE" = "docker" ]; then

    echo "Starting with Docker..."
    docker compose up --build

elif [ "$MODE" = "linux" ]; then

    echo "Copying files to Apache..."

    # Create folder in Apache web root
    sudo mkdir -p /var/www/html/LinuxWebUI

    # Copy all project files there
    sudo cp -r ./* /var/www/html/LinuxWebUI/

    # Fix permissions so Apache can read them
    sudo chown -R www-data:www-data /var/www/html/LinuxWebUI/
    sudo chmod -R 755 /var/www/html/LinuxWebUI/

    # Restart Apache
    sudo systemctl restart apache2

    echo "Done! Visit: http://localhost/LinuxWebUI"

else
    echo "Usage: ./run.sh docker"
    echo "       ./run.sh linux"
fi