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

# Verifica se o tipo de storage foi fornecido
if [ -z "$1" ]; then
    echo -e "${RED}Erro: Por favor, especifique o tipo de storage${NC}"
    echo -e "${YELLOW}Uso: $0 [s3|ftp|scp]${NC}"
    exit 1
fi

STORAGE_TYPE="$1"
BACKUP_DIR="/var/backups/wp-manager"
CRON_FILE="/etc/cron.d/wp-manager-backup"

echo -e "${GREEN}Configurando backup remoto ($STORAGE_TYPE)...${NC}"

# Instala dependências
echo -e "${YELLOW}Instalando dependências...${NC}"
apt-get update
apt-get install -y s3cmd lftp openssh-client

# Cria diretório de backup se não existir
mkdir -p $BACKUP_DIR

# Configura baseado no tipo de storage
case $STORAGE_TYPE in
    "s3")
        echo -e "${YELLOW}Configurando backup para Amazon S3...${NC}"
        read -p "Digite o Access Key ID: " AWS_ACCESS_KEY
        read -p "Digite o Secret Access Key: " AWS_SECRET_KEY
        read -p "Digite o nome do bucket: " BUCKET_NAME
        read -p "Digite a região (ex: us-east-1): " AWS_REGION

        # Configura s3cmd
        cat > /root/.s3cfg << EOF
[default]
access_key = $AWS_ACCESS_KEY
secret_key = $AWS_SECRET_KEY
host_base = s3.$AWS_REGION.amazonaws.com
host_bucket = %(bucket)s.s3.$AWS_REGION.amazonaws.com
use_https = True
EOF

        # Cria script de backup
        cat > "$BACKUP_DIR/backup-to-s3.sh" << EOF
#!/bin/bash
BACKUP_FILE="wp-manager_backup_\$(date +%Y%m%d_%H%M%S).tar.gz"
cd $BACKUP_DIR
tar -czf \$BACKUP_FILE wp-manager_backup_*.tar.gz
s3cmd put \$BACKUP_FILE s3://$BUCKET_NAME/
rm \$BACKUP_FILE
EOF
        chmod +x "$BACKUP_DIR/backup-to-s3.sh"
        ;;
    "ftp")
        echo -e "${YELLOW}Configurando backup para FTP...${NC}"
        read -p "Digite o host FTP: " FTP_HOST
        read -p "Digite o usuário FTP: " FTP_USER
        read -p "Digite a senha FTP: " FTP_PASS
        read -p "Digite o diretório remoto: " FTP_DIR

        # Cria script de backup
        cat > "$BACKUP_DIR/backup-to-ftp.sh" << EOF
#!/bin/bash
BACKUP_FILE="wp-manager_backup_\$(date +%Y%m%d_%H%M%S).tar.gz"
cd $BACKUP_DIR
tar -czf \$BACKUP_FILE wp-manager_backup_*.tar.gz
lftp -u $FTP_USER,$FTP_PASS $FTP_HOST << END
cd $FTP_DIR
put \$BACKUP_FILE
quit
END
rm \$BACKUP_FILE
EOF
        chmod +x "$BACKUP_DIR/backup-to-ftp.sh"
        ;;
    "scp")
        echo -e "${YELLOW}Configurando backup para SCP...${NC}"
        read -p "Digite o host remoto: " REMOTE_HOST
        read -p "Digite o usuário remoto: " REMOTE_USER
        read -p "Digite o diretório remoto: " REMOTE_DIR

        # Gera chave SSH se não existir
        if [ ! -f "/root/.ssh/id_rsa" ]; then
            ssh-keygen -t rsa -b 4096 -f /root/.ssh/id_rsa -N ""
        fi

        # Cria script de backup
        cat > "$BACKUP_DIR/backup-to-scp.sh" << EOF
#!/bin/bash
BACKUP_FILE="wp-manager_backup_\$(date +%Y%m%d_%H%M%S).tar.gz"
cd $BACKUP_DIR
tar -czf \$BACKUP_FILE wp-manager_backup_*.tar.gz
scp \$BACKUP_FILE $REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR/
rm \$BACKUP_FILE
EOF
        chmod +x "$BACKUP_DIR/backup-to-scp.sh"
        ;;
    *)
        echo -e "${RED}Tipo de storage inválido: $STORAGE_TYPE${NC}"
        exit 1
        ;;
esac

# Configura cron job
echo -e "${YELLOW}Configurando cron job...${NC}"
case $STORAGE_TYPE in
    "s3")
        echo "0 2 * * * root $BACKUP_DIR/backup-to-s3.sh" > $CRON_FILE
        ;;
    "ftp")
        echo "0 2 * * * root $BACKUP_DIR/backup-to-ftp.sh" > $CRON_FILE
        ;;
    "scp")
        echo "0 2 * * * root $BACKUP_DIR/backup-to-scp.sh" > $CRON_FILE
        ;;
esac

chmod 644 $CRON_FILE

# Testa o backup
echo -e "${YELLOW}Testando backup...${NC}"
case $STORAGE_TYPE in
    "s3")
        $BACKUP_DIR/backup-to-s3.sh
        ;;
    "ftp")
        $BACKUP_DIR/backup-to-ftp.sh
        ;;
    "scp")
        $BACKUP_DIR/backup-to-scp.sh
        ;;
esac

echo -e "${GREEN}Configuração do backup remoto concluída!${NC}"
echo -e "${YELLOW}Backup será executado diariamente às 02:00${NC}" 