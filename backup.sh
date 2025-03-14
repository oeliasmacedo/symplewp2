#!/bin/bash

# Configurações
BACKUP_DIR="/var/backups/wp-manager"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_NAME="wp-manager_backup_$DATE"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}Iniciando backup do WordPress Manager...${NC}"

# Cria diretório de backup
mkdir -p $BACKUP_DIR

# Backup do banco de dados
echo -e "${YELLOW}Fazendo backup do banco de dados...${NC}"
docker-compose exec -T mysql mysqldump -u$DB_USER -p$DB_PASSWORD $DB_NAME > "$BACKUP_DIR/${BACKUP_NAME}_db.sql"

# Backup dos arquivos
echo -e "${YELLOW}Fazendo backup dos arquivos...${NC}"
tar -czf "$BACKUP_DIR/${BACKUP_NAME}_files.tar.gz" /var/www/wp-manager

# Compacta backup
echo -e "${YELLOW}Compactando backup...${NC}"
tar -czf "$BACKUP_DIR/${BACKUP_NAME}.tar.gz" \
    "$BACKUP_DIR/${BACKUP_NAME}_db.sql" \
    "$BACKUP_DIR/${BACKUP_NAME}_files.tar.gz"

# Remove arquivos temporários
rm "$BACKUP_DIR/${BACKUP_NAME}_db.sql"
rm "$BACKUP_DIR/${BACKUP_NAME}_files.tar.gz"

echo -e "${GREEN}Backup concluído!${NC}"
echo -e "${YELLOW}Arquivo: $BACKUP_DIR/${BACKUP_NAME}.tar.gz${NC}" 