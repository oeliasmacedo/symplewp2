#!/bin/bash

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}Iniciando instalação do WordPress Manager...${NC}"

# Verifica se está rodando como root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Por favor, execute como root${NC}"
    exit 1
fi

# Instala dependências
echo -e "${YELLOW}Instalando dependências...${NC}"
apt-get update
apt-get install -y docker.io docker-compose nginx php8.1-fpm php8.1-mysql php8.1-xml php8.1-curl php8.1-mbstring php8.1-zip php8.1-gd php8.1-intl

# Cria diretórios
echo -e "${YELLOW}Criando diretórios...${NC}"
mkdir -p /var/www/wp-manager
mkdir -p /var/log/wp-manager

# Copia arquivos
echo -e "${YELLOW}Copiando arquivos...${NC}"
cp .env.example .env
cp nginx.conf /etc/nginx/conf.d/wp-manager.conf

# Inicia serviços
echo -e "${YELLOW}Iniciando serviços...${NC}"
docker-compose up -d
systemctl restart nginx

echo -e "${GREEN}Instalação concluída!${NC}"
echo -e "${YELLOW}Acesse http://seu-ip para verificar a instalação${NC}" 