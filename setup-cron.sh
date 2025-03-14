#!/bin/bash

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}Configurando cron jobs para o WordPress Manager...${NC}"

# Define os caminhos dos scripts
BACKUP_SCRIPT="/var/www/wp-manager/backup.sh"
MONITOR_SCRIPT="/var/www/wp-manager/monitor.sh"

# Verifica se os scripts existem
if [ ! -f "$BACKUP_SCRIPT" ]; then
    echo -e "${RED}Erro: Script de backup não encontrado em $BACKUP_SCRIPT${NC}"
    exit 1
fi

if [ ! -f "$MONITOR_SCRIPT" ]; then
    echo -e "${RED}Erro: Script de monitoramento não encontrado em $MONITOR_SCRIPT${NC}"
    exit 1
fi

# Torna os scripts executáveis
chmod +x "$BACKUP_SCRIPT"
chmod +x "$MONITOR_SCRIPT"

# Remove cron jobs existentes
(crontab -l | grep -v "$BACKUP_SCRIPT") | crontab -
(crontab -l | grep -v "$MONITOR_SCRIPT") | crontab -

# Adiciona novos cron jobs
# Backup diário às 02:00
(crontab -l 2>/dev/null; echo "0 2 * * * $BACKUP_SCRIPT") | crontab -

# Monitoramento a cada 5 minutos
(crontab -l 2>/dev/null; echo "*/5 * * * * $MONITOR_SCRIPT") | crontab -

# Verifica se os cron jobs foram adicionados
if crontab -l | grep -q "$BACKUP_SCRIPT"; then
    echo -e "${GREEN}Cron job de backup configurado com sucesso${NC}"
else
    echo -e "${RED}Erro ao configurar cron job de backup${NC}"
    exit 1
fi

if crontab -l | grep -q "$MONITOR_SCRIPT"; then
    echo -e "${GREEN}Cron job de monitoramento configurado com sucesso${NC}"
else
    echo -e "${RED}Erro ao configurar cron job de monitoramento${NC}"
    exit 1
fi

echo -e "${GREEN}Configuração dos cron jobs concluída com sucesso!${NC}"
echo -e "${YELLOW}Cron jobs configurados:${NC}"
echo -e "1. Backup diário às 02:00"
echo -e "2. Monitoramento a cada 5 minutos" 