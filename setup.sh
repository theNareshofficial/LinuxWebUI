#!/bin/bash

PROJECT_DIR="/var/www/html/LinuxWebUI"
CONFIG_DIR="$PROJECT_DIR/config"

echo ""
echo "  ██╗     ██╗███╗   ██╗██╗   ██╗██╗  ██╗"
echo "  ██║     ██║████╗  ██║██║   ██║╚██╗██╔╝"
echo "  ██║     ██║██╔██╗ ██║██║   ██║ ╚███╔╝ "
echo "  ██║     ██║██║╚██╗██║██║   ██║ ██╔██╗ "
echo "  ███████╗██║██║ ╚████║╚██████╔╝██╔╝ ██╗"
echo "  ╚══════╝╚═╝╚═╝  ╚═══╝ ╚═════╝ ╚═╝  ╚═╝"
echo "  WebUI Setup — v1.0.0"
echo ""

mkdir -p "$CONFIG_DIR"
echo "[✓] Directories created"

read -p "  Enter admin username [default: admin]: " USERNAME
USERNAME=${USERNAME:-admin}

echo -n "  Enter admin password: "
read -s PASSWORD
echo ""
echo -n "  Confirm password: "
read -s PASSWORD2
echo ""

if [ "$PASSWORD" != "$PASSWORD2" ]; then
    echo "  [✗] Passwords do not match. Aborting."
    exit 1
fi

HASH=$(php -r "echo password_hash('$PASSWORD', PASSWORD_BCRYPT);")

cat > "$CONFIG_DIR/users.json" << EOF
{
    "$USERNAME": "$HASH"
}
EOF
chmod 640 "$CONFIG_DIR/users.json"
chown www-data:www-data "$CONFIG_DIR/users.json"
echo "[✓] Admin user '$USERNAME' created"

# ── Set permissions ──
chown -R www-data:www-data "$PROJECT_DIR"
chmod -R 750 "$PROJECT_DIR"
chmod 700 "$CONFIG_DIR"
echo "[✓] Permissions set"

# ── sudoers rule ──
SUDOERS_FILE="/etc/sudoers.d/LinuxWebUI"
cat > "$SUDOERS_FILE" << 'SUDOERS'
# LinuxWebUI — Allow www-data to control specific services only
www-data ALL=(ALL) NOPASSWD: \
    /bin/systemctl start apache2, \
    /bin/systemctl stop apache2, \
    /bin/systemctl restart apache2, \
    /bin/systemctl status apache2, \
    /bin/systemctl start ssh, \
    /bin/systemctl stop ssh, \
    /bin/systemctl restart ssh, \
    /bin/systemctl status ssh, \
    /bin/systemctl start vsftpd, \
    /bin/systemctl stop vsftpd, \
    /bin/systemctl restart vsftpd, \
    /bin/systemctl status vsftpd, \
    /bin/systemctl start mongod, \
    /bin/systemctl stop mongod, \
    /bin/systemctl restart mongod, \
    /bin/systemctl status mongod, \
    /bin/systemctl start mysql, \
    /bin/systemctl stop mysql, \
    /bin/systemctl restart mysql, \
    /bin/systemctl status mysql, \
    /bin/systemctl start nginx, \
    /bin/systemctl stop nginx, \
    /bin/systemctl restart nginx, \
    /bin/systemctl status nginx
SUDOERS
chmod 440 "$SUDOERS_FILE"
echo "[✓] Sudoers rule written to $SUDOERS_FILE"

echo ""
echo "  ────────────────────────────────────────"
echo "  Setup complete!"
echo "  Open: http://localhost/LinuxWebUI/"
echo "  Username: $USERNAME"
echo "  ────────────────────────────────────────"
echo ""