#!/bin/bash

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}Verificando status do WordPress Manager...${NC}"

# Verifica serviços
echo -e "${YELLOW}Verificando serviços...${NC}"
if systemctl is-active --quiet nginx; then
    echo -e "${GREEN}✓ Nginx está rodando${NC}"
else
    echo -e "${RED}✗ Nginx não está rodando${NC}"
fi

if systemctl is-active --quiet php8.1-fpm; then
    echo -e "${GREEN}✓ PHP-FPM está rodando${NC}"
else
    echo -e "${RED}✗ PHP-FPM não está rodando${NC}"
fi

# Verifica containers
echo -e "${YELLOW}Verificando containers Docker...${NC}"
if docker-compose ps | grep -q "Up"; then
    echo -e "${GREEN}✓ Containers estão rodando${NC}"
else
    echo -e "${RED}✗ Containers não estão rodando${NC}"
fi

# Verifica espaço em disco
echo -e "${YELLOW}Verificando espaço em disco...${NC}"
DISK_USAGE=$(df -h / | tail -1 | awk '{print $5}')
echo -e "Uso de disco: $DISK_USAGE"

# Verifica memória
echo -e "${YELLOW}Verificando uso de memória...${NC}"
MEM_USAGE=$(free | grep Mem | awk '{print $3/$2 * 100.0}' | cut -d. -f1)
echo -e "Uso de memória: $MEM_USAGE%"

echo -e "${GREEN}Verificação concluída!${NC}" 