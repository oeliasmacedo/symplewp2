#!/bin/bash

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Verifica se está rodando como root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Por favor, execute como root${NC}"
    exit 1
fi

# Verifica se o domínio foi fornecido
if [ -z "$1" ]; then
    echo -e "${RED}Erro: Por favor, especifique o domínio${NC}"
    echo -e "${YELLOW}Uso: $0 dominio.com${NC}"
    exit 1
fi

DOMAIN="$1"
EMAIL="admin@$DOMAIN"

echo -e "${GREEN}Configurando SSL para $DOMAIN...${NC}"

# Instala Certbot se não estiver instalado
if ! command -v certbot &> /dev/null; then
    echo -e "${YELLOW}Instalando Certbot...${NC}"
    apt-get update
    apt-get install -y certbot python3-certbot-nginx
fi

# Para o Nginx temporariamente
echo -e "${YELLOW}Parando Nginx...${NC}"
systemctl stop nginx

# Obtém certificado SSL
echo -e "${YELLOW}Obtendo certificado SSL...${NC}"
certbot certonly --standalone \
    --preferred-challenges http \
    --agree-tos \
    --email "$EMAIL" \
    -d "$DOMAIN" \
    -d "www.$DOMAIN"

# Verifica se o certificado foi obtido
if [ ! -f "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" ]; then
    echo -e "${RED}Erro ao obter certificado SSL${NC}"
    exit 1
fi

# Configura Nginx
echo -e "${YELLOW}Configurando Nginx...${NC}"
cat > "/etc/nginx/conf.d/wp-manager.conf" << EOF
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    server_name $DOMAIN www.$DOMAIN;

    ssl_certificate /etc/letsencrypt/live/$DOMAIN/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/$DOMAIN/privkey.pem;
    ssl_trusted_certificate /etc/letsencrypt/live/$DOMAIN/chain.pem;

    # SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_timeout 1d;
    ssl_session_cache shared:SSL:50m;
    ssl_session_tickets off;
    ssl_stapling on;
    ssl_stapling_verify on;
    resolver 8.8.8.8 8.8.4.4 valid=300s;
    resolver_timeout 5s;

    # HSTS
    add_header Strict-Transport-Security "max-age=63072000" always;

    # Root directory
    root /var/www/wp-manager/public;
    index index.php index.html;

    # Logs
    access_log /var/log/nginx/wp-manager.access.log;
    error_log /var/log/nginx/wp-manager.error.log;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }
}
EOF

# Inicia o Nginx
echo -e "${YELLOW}Iniciando Nginx...${NC}"
systemctl start nginx

# Configura renovação automática
echo -e "${YELLOW}Configurando renovação automática...${NC}"
echo "0 0,12 * * * root python -c 'import random; import time; time.sleep(random.random() * 3600)' && certbot renew -q" > /etc/cron.d/certbot

# Verifica configuração do Nginx
echo -e "${YELLOW}Verificando configuração do Nginx...${NC}"
if nginx -t; then
    echo -e "${GREEN}Configuração do Nginx está correta${NC}"
else
    echo -e "${RED}Erro na configuração do Nginx${NC}"
    exit 1
fi

# Testa SSL
echo -e "${YELLOW}Testando SSL...${NC}"
if curl -sI "https://$DOMAIN" | grep -q "200 OK"; then
    echo -e "${GREEN}SSL está funcionando corretamente${NC}"
else
    echo -e "${RED}Erro ao testar SSL${NC}"
    exit 1
fi

echo -e "${GREEN}Configuração SSL concluída com sucesso!${NC}"
echo -e "${YELLOW}Certificado será renovado automaticamente${NC}" 