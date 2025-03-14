#!/bin/bash

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Diretório da aplicação
APP_DIR="/var/www/wp-manager"

# Verifica se está no diretório correto
if [ ! -d "$APP_DIR" ]; then
    echo -e "${RED}Erro: Diretório da aplicação não encontrado: $APP_DIR${NC}"
    exit 1
fi

# Faz backup antes da atualização
echo -e "${YELLOW}Fazendo backup antes da atualização...${NC}"
./backup.sh

# Para os serviços
echo -e "${YELLOW}Parando serviços...${NC}"
docker-compose down
systemctl stop nginx php8.1-fpm redis

# Atualiza o código
echo -e "${YELLOW}Atualizando código...${NC}"
cd "$APP_DIR"
git fetch origin
git reset --hard origin/main
git clean -fd

# Atualiza dependências
echo -e "${YELLOW}Atualizando dependências...${NC}"
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Limpa cache
echo -e "${YELLOW}Limpando cache...${NC}"
rm -rf storage/framework/cache/*
rm -rf storage/framework/views/*
rm -rf storage/framework/sessions/*
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Atualiza banco de dados
echo -e "${YELLOW}Atualizando banco de dados...${NC}"
php artisan migrate --force

# Ajusta permissões
echo -e "${YELLOW}Ajustando permissões...${NC}"
chown -R www-data:www-data .
chmod -R 755 .
chmod -R 775 storage

# Reinicia os serviços
echo -e "${YELLOW}Reiniciando serviços...${NC}"
docker-compose up -d
systemctl start nginx php8.1-fpm redis

# Verifica se a atualização foi bem sucedida
if docker-compose ps | grep -q "Up"; then
    echo -e "${GREEN}Atualização concluída com sucesso!${NC}"
else
    echo -e "${RED}Erro: Alguns serviços não iniciaram corretamente${NC}"
    exit 1
fi

# Verifica a versão
echo -e "${YELLOW}Versão atual:${NC}"
php artisan --version 