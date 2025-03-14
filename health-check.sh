#!/bin/bash

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Função para verificar status
check_status() {
    local name="$1"
    local command="$2"
    local expected="$3"
    
    echo -e "${YELLOW}Verificando $name...${NC}"
    if eval "$command" | grep -q "$expected"; then
        echo -e "${GREEN}✓ $name está funcionando corretamente${NC}"
        return 0
    else
        echo -e "${RED}✗ $name não está funcionando corretamente${NC}"
        return 1
    fi
}

# Verifica serviços do sistema
echo -e "${GREEN}Iniciando verificação de saúde do sistema...${NC}"

# Verifica Nginx
check_status "Nginx" "systemctl is-active nginx" "active"

# Verifica PHP-FPM
check_status "PHP-FPM" "systemctl is-active php8.1-fpm" "active"

# Verifica Redis
check_status "Redis" "systemctl is-active redis" "active"

# Verifica Docker
check_status "Docker" "systemctl is-active docker" "active"

# Verifica containers
echo -e "${YELLOW}Verificando containers Docker...${NC}"
if docker-compose ps | grep -q "Up"; then
    echo -e "${GREEN}✓ Todos os containers estão rodando${NC}"
else
    echo -e "${RED}✗ Alguns containers não estão rodando${NC}"
fi

# Verifica banco de dados
echo -e "${YELLOW}Verificando conexão com o banco de dados...${NC}"
if docker-compose exec -T mysql mysqladmin ping -h localhost -u$DB_USER -p$DB_PASSWORD --silent; then
    echo -e "${GREEN}✓ Conexão com o banco de dados está funcionando${NC}"
else
    echo -e "${RED}✗ Erro na conexão com o banco de dados${NC}"
fi

# Verifica API
echo -e "${YELLOW}Verificando API...${NC}"
if curl -s http://localhost/api/health | grep -q "status\":\"ok"; then
    echo -e "${GREEN}✓ API está respondendo corretamente${NC}"
else
    echo -e "${RED}✗ API não está respondendo corretamente${NC}"
fi

# Verifica espaço em disco
echo -e "${YELLOW}Verificando espaço em disco...${NC}"
DISK_USAGE=$(df -h / | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $DISK_USAGE -lt 80 ]; then
    echo -e "${GREEN}✓ Espaço em disco está adequado ($DISK_USAGE%)${NC}"
else
    echo -e "${RED}✗ Espaço em disco está crítico ($DISK_USAGE%)${NC}"
fi

# Verifica memória
echo -e "${YELLOW}Verificando uso de memória...${NC}"
MEM_USAGE=$(free | grep Mem | awk '{print $3/$2 * 100.0}' | cut -d. -f1)
if [ $MEM_USAGE -lt 80 ]; then
    echo -e "${GREEN}✓ Uso de memória está adequado ($MEM_USAGE%)${NC}"
else
    echo -e "${RED}✗ Uso de memória está crítico ($MEM_USAGE%)${NC}"
fi

# Verifica CPU
echo -e "${YELLOW}Verificando uso de CPU...${NC}"
CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d. -f1)
if [ $CPU_USAGE -lt 80 ]; then
    echo -e "${GREEN}✓ Uso de CPU está adequado ($CPU_USAGE%)${NC}"
else
    echo -e "${RED}✗ Uso de CPU está crítico ($CPU_USAGE%)${NC}"
fi

# Verifica logs de erro
echo -e "${YELLOW}Verificando logs de erro...${NC}"
ERROR_COUNT=$(tail -n 100 /var/log/nginx/error.log | grep -c "error")
if [ $ERROR_COUNT -lt 10 ]; then
    echo -e "${GREEN}✓ Número de erros está dentro do normal ($ERROR_COUNT)${NC}"
else
    echo -e "${RED}✗ Número de erros está alto ($ERROR_COUNT)${NC}"
fi

# Verifica certificado SSL
echo -e "${YELLOW}Verificando certificado SSL...${NC}"
if openssl x509 -checkend 2592000 -noout -in /etc/letsencrypt/live/$(hostname)/fullchain.pem; then
    echo -e "${GREEN}✓ Certificado SSL está válido${NC}"
else
    echo -e "${RED}✗ Certificado SSL está próximo de expirar ou expirado${NC}"
fi

echo -e "${GREEN}Verificação de saúde concluída!${NC}" 