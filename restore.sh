#!/bin/bash

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Verifica se o arquivo de backup foi fornecido
if [ -z "$1" ]; then
    echo -e "${RED}Erro: Por favor, especifique o arquivo de backup${NC}"
    echo -e "${YELLOW}Uso: $0 caminho/do/backup.tar.gz${NC}"
    exit 1
fi

BACKUP_FILE="$1"
TEMP_DIR="/tmp/wp-manager-restore"

# Verifica se o arquivo existe
if [ ! -f "$BACKUP_FILE" ]; then
    echo -e "${RED}Erro: Arquivo de backup não encontrado: $BACKUP_FILE${NC}"
    exit 1
fi

# Cria diretório temporário
echo -e "${YELLOW}Criando diretório temporário...${NC}"
rm -rf "$TEMP_DIR"
mkdir -p "$TEMP_DIR"

# Extrai o backup
echo -e "${YELLOW}Extraindo backup...${NC}"
tar -xzf "$BACKUP_FILE" -C "$TEMP_DIR"

# Verifica se a extração foi bem sucedida
if [ ! -f "$TEMP_DIR/wp-manager_backup_*_db.sql" ] || \
   [ ! -f "$TEMP_DIR/wp-manager_backup_*_files.tar.gz" ] || \
   [ ! -f "$TEMP_DIR/wp-manager_backup_*_config.tar.gz" ]; then
    echo -e "${RED}Erro: Arquivo de backup inválido ou corrompido${NC}"
    rm -rf "$TEMP_DIR"
    exit 1
fi

# Para os serviços
echo -e "${YELLOW}Parando serviços...${NC}"
docker-compose down
systemctl stop nginx php8.1-fpm redis

# Restaura o banco de dados
echo -e "${YELLOW}Restaurando banco de dados...${NC}"
docker-compose up -d mysql
sleep 10
docker-compose exec -T mysql mysql -u$DB_USER -p$DB_PASSWORD $DB_NAME < "$TEMP_DIR/wp-manager_backup_*_db.sql"

# Restaura os arquivos
echo -e "${YELLOW}Restaurando arquivos...${NC}"
tar -xzf "$TEMP_DIR/wp-manager_backup_*_files.tar.gz" -C /var/www/wp-manager

# Restaura as configurações
echo -e "${YELLOW}Restaurando configurações...${NC}"
tar -xzf "$TEMP_DIR/wp-manager_backup_*_config.tar.gz" -C /

# Ajusta permissões
echo -e "${YELLOW}Ajustando permissões...${NC}"
chown -R www-data:www-data /var/www/wp-manager
chmod -R 755 /var/www/wp-manager
chmod -R 775 /var/www/wp-manager/storage

# Limpa cache
echo -e "${YELLOW}Limpando cache...${NC}"
rm -rf /var/www/wp-manager/storage/framework/cache/*
rm -rf /var/www/wp-manager/storage/framework/views/*
rm -rf /var/www/wp-manager/storage/framework/sessions/*

# Reinicia os serviços
echo -e "${YELLOW}Reiniciando serviços...${NC}"
docker-compose up -d
systemctl start nginx php8.1-fpm redis

# Limpa arquivos temporários
echo -e "${YELLOW}Limpando arquivos temporários...${NC}"
rm -rf "$TEMP_DIR"

# Verifica se a restauração foi bem sucedida
if docker-compose ps | grep -q "Up"; then
    echo -e "${GREEN}Restauração concluída com sucesso!${NC}"
else
    echo -e "${RED}Erro: Alguns serviços não iniciaram corretamente${NC}"
    exit 1
fi 