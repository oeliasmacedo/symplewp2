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

echo -e "${GREEN}Configurando monitoramento com Prometheus e Grafana...${NC}"

# Cria diretório para os dados
echo -e "${YELLOW}Criando diretórios...${NC}"
mkdir -p /var/lib/prometheus
mkdir -p /var/lib/grafana

# Instala Docker se não estiver instalado
if ! command -v docker &> /dev/null; then
    echo -e "${YELLOW}Instalando Docker...${NC}"
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    rm get-docker.sh
fi

# Instala Docker Compose se não estiver instalado
if ! command -v docker-compose &> /dev/null; then
    echo -e "${YELLOW}Instalando Docker Compose...${NC}"
    curl -L "https://github.com/docker/compose/releases/download/v2.24.1/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
fi

# Cria arquivo docker-compose.yml
echo -e "${YELLOW}Criando arquivo docker-compose.yml...${NC}"
cat > "/var/www/wp-manager/docker-compose.monitoring.yml" << EOF
version: '3.8'

services:
  prometheus:
    image: prom/prometheus:latest
    container_name: prometheus
    volumes:
      - /var/lib/prometheus:/prometheus
      - ./prometheus.yml:/etc/prometheus/prometheus.yml
    command:
      - '--config.file=/etc/prometheus/prometheus.yml'
      - '--storage.tsdb.path=/prometheus'
      - '--web.console.libraries=/usr/share/prometheus/console_libraries'
      - '--web.console.templates=/usr/share/prometheus/consoles'
    ports:
      - "9090:9090"
    restart: unless-stopped

  grafana:
    image: grafana/grafana:latest
    container_name: grafana
    volumes:
      - /var/lib/grafana:/var/lib/grafana
    environment:
      - GF_SECURITY_ADMIN_PASSWORD=admin
      - GF_USERS_ALLOW_SIGN_UP=false
    ports:
      - "3000:3000"
    restart: unless-stopped

  node-exporter:
    image: prom/node-exporter:latest
    container_name: node-exporter
    volumes:
      - /proc:/host/proc:ro
      - /sys:/host/sys:ro
      - /:/rootfs:ro
    command:
      - '--path.procfs=/host/proc'
      - '--path.sysfs=/host/sys'
      - '--collector.filesystem.mount-points-exclude=^/(sys|proc|dev|host|etc)($$|/)'
    ports:
      - "9100:9100"
    restart: unless-stopped

  cadvisor:
    image: gcr.io/cadvisor/cadvisor:latest
    container_name: cadvisor
    volumes:
      - /:/rootfs:ro
      - /var/run:/var/run:ro
      - /sys:/sys:ro
      - /var/lib/docker/:/var/lib/docker:ro
    ports:
      - "8080:8080"
    restart: unless-stopped

  mysql-exporter:
    image: prom/mysqld-exporter:latest
    container_name: mysql-exporter
    environment:
      - DATA_SOURCE_NAME=root:${DB_PASSWORD}@mysql:3306/
    ports:
      - "9104:9104"
    restart: unless-stopped

  redis-exporter:
    image: oliver006/redis_exporter:latest
    container_name: redis-exporter
    environment:
      - REDIS_ADDR=redis:6379
    ports:
      - "9121:9121"
    restart: unless-stopped
EOF

# Cria arquivo de configuração do Prometheus
echo -e "${YELLOW}Criando arquivo de configuração do Prometheus...${NC}"
cat > "/var/www/wp-manager/prometheus.yml" << EOF
global:
  scrape_interval: 15s
  evaluation_interval: 15s

scrape_configs:
  - job_name: 'prometheus'
    static_configs:
      - targets: ['localhost:9090']

  - job_name: 'node-exporter'
    static_configs:
      - targets: ['node-exporter:9100']

  - job_name: 'cadvisor'
    static_configs:
      - targets: ['cadvisor:8080']

  - job_name: 'mysql-exporter'
    static_configs:
      - targets: ['mysql-exporter:9104']

  - job_name: 'redis-exporter'
    static_configs:
      - targets: ['redis-exporter:9121']
EOF

# Ajusta permissões
echo -e "${YELLOW}Ajustando permissões...${NC}"
chown -R nobody:nogroup /var/lib/prometheus
chown -R 472:472 /var/lib/grafana

# Inicia os containers
echo -e "${YELLOW}Iniciando containers...${NC}"
cd /var/www/wp-manager
docker-compose -f docker-compose.monitoring.yml up -d

# Verifica se os containers estão rodando
echo -e "${YELLOW}Verificando status dos containers...${NC}"
if docker-compose -f docker-compose.monitoring.yml ps | grep -q "Up"; then
    echo -e "${GREEN}Containers iniciados com sucesso!${NC}"
else
    echo -e "${RED}Erro ao iniciar containers${NC}"
    exit 1
fi

# Configura firewall
echo -e "${YELLOW}Configurando firewall...${NC}"
ufw allow 9090/tcp  # Prometheus
ufw allow 3000/tcp  # Grafana
ufw allow 9100/tcp  # Node Exporter
ufw allow 8080/tcp  # cAdvisor
ufw allow 9104/tcp  # MySQL Exporter
ufw allow 9121/tcp  # Redis Exporter

echo -e "${GREEN}Configuração do monitoramento concluída!${NC}"
echo -e "${YELLOW}Acessos:${NC}"
echo -e "1. Prometheus: http://seu-ip:9090"
echo -e "2. Grafana: http://seu-ip:3000 (usuário: admin, senha: admin)"
echo -e "3. Node Exporter: http://seu-ip:9100"
echo -e "4. cAdvisor: http://seu-ip:8080"
echo -e "5. MySQL Exporter: http://seu-ip:9104"
echo -e "6. Redis Exporter: http://seu-ip:9121" 