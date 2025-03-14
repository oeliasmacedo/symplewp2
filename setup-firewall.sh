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

echo -e "${GREEN}Configurando firewall para o WordPress Manager...${NC}"

# Instala UFW se não estiver instalado
if ! command -v ufw &> /dev/null; then
    echo -e "${YELLOW}Instalando UFW...${NC}"
    apt-get update
    apt-get install -y ufw
fi

# Reseta as regras do firewall
echo -e "${YELLOW}Resetando regras do firewall...${NC}"
ufw --force reset

# Configura regras padrão
echo -e "${YELLOW}Configurando regras padrão...${NC}"
ufw default deny incoming
ufw default allow outgoing

# Permite SSH (porta 22) para não perder acesso
echo -e "${YELLOW}Configurando acesso SSH...${NC}"
ufw allow 22/tcp

# Permite HTTP (porta 80)
echo -e "${YELLOW}Configurando acesso HTTP...${NC}"
ufw allow 80/tcp

# Permite HTTPS (porta 443)
echo -e "${YELLOW}Configurando acesso HTTPS...${NC}"
ufw allow 443/tcp

# Permite portas do Docker
echo -e "${YELLOW}Configurando portas do Docker...${NC}"
ufw allow 3306/tcp  # MySQL
ufw allow 6379/tcp  # Redis
ufw allow 9000/tcp  # PHP-FPM

# Permite portas do WordPress
echo -e "${YELLOW}Configurando portas do WordPress...${NC}"
ufw allow 8080/tcp  # WordPress (alternativa)
ufw allow 8443/tcp  # WordPress (alternativa SSL)

# Configura rate limiting
echo -e "${YELLOW}Configurando rate limiting...${NC}"
ufw limit 22/tcp    # Limita tentativas de SSH
ufw limit 80/tcp    # Limita requisições HTTP
ufw limit 443/tcp   # Limita requisições HTTPS

# Habilita o firewall
echo -e "${YELLOW}Habilitando firewall...${NC}"
ufw --force enable

# Verifica status
echo -e "${YELLOW}Verificando status do firewall...${NC}"
ufw status verbose

# Configura logging
echo -e "${YELLOW}Configurando logging...${NC}"
ufw logging on
ufw logging level medium

# Configura backup das regras
echo -e "${YELLOW}Configurando backup das regras...${NC}"
mkdir -p /etc/ufw/backups
cp /etc/ufw/user.rules /etc/ufw/backups/user.rules.$(date +%Y%m%d)

echo -e "${GREEN}Configuração do firewall concluída!${NC}"
echo -e "${YELLOW}Regras configuradas:${NC}"
echo -e "1. SSH (22/tcp) - Rate limited"
echo -e "2. HTTP (80/tcp) - Rate limited"
echo -e "3. HTTPS (443/tcp) - Rate limited"
echo -e "4. MySQL (3306/tcp)"
echo -e "5. Redis (6379/tcp)"
echo -e "6. PHP-FPM (9000/tcp)"
echo -e "7. WordPress (8080/tcp, 8443/tcp)" 