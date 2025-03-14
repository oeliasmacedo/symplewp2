# WordPress Manager

Sistema de gerenciamento completo para WordPress com interface moderna e recursos avançados.

## 🚀 Funcionalidades

- 📊 Dashboard com estatísticas em tempo real
- 📝 Gerenciamento de posts e páginas
- 🔌 Gerenciamento de plugins e temas
- 📁 Gerenciamento de mídia
- 👥 Gerenciamento de usuários
- 💬 Gerenciamento de comentários
- 🔒 Monitoramento de segurança
- 💾 Backup automático
- 📈 Monitoramento de recursos
- 🔔 Sistema de notificações

## 🛠️ Tecnologias

- PHP 8.1
- Vue.js 3
- Tailwind CSS
- MySQL 8.0
- Redis
- Docker
- GitHub Actions
- PHPUnit
- PHPStan
- PHP CS Fixer

## 📋 Pré-requisitos

- PHP 8.1 ou superior
- Composer
- Node.js 16 ou superior
- Docker e Docker Compose
- MySQL 8.0 ou superior
- Redis

## 🚀 Instalação

1. Clone o repositório:
```bash
git clone https://github.com/seu-usuario/wp-manager.git
cd wp-manager
```

2. Instale as dependências:
```bash
composer install
npm install
```

3. Configure o ambiente:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure as variáveis de ambiente no arquivo `.env`

5. Inicie os containers Docker:
```bash
docker-compose up -d
```

6. Execute as migrações:
```bash
php artisan migrate
```

7. Inicie o servidor de desenvolvimento:
```bash
php artisan serve
npm run dev
```

## 🔧 Configuração

### WordPress

Configure o caminho do WordPress no arquivo `.env`:
```
WP_PATH=/caminho/para/wordpress
WP_URL=http://localhost/wordpress
```

### Banco de Dados

Configure as credenciais do banco de dados no arquivo `.env`:
```
DB_HOST=mysql
DB_NAME=wordpress
DB_USER=wordpress
DB_PASSWORD=sua_senha_segura
```

### API

Configure o token da API no arquivo `.env`:
```
API_TOKEN=seu_token_seguro
```

## 🧪 Testes

Execute os testes:
```bash
# Testes unitários
php artisan test --filter=Unit

# Testes de integração
php artisan test --filter=Integration

# Testes end-to-end
npm run test:e2e
```

## 📚 Documentação da API

### Autenticação

Todas as requisições devem incluir o token da API no header:
```
Authorization: Bearer seu_token_seguro
```

### Endpoints

#### Posts

- `GET /api/posts` - Lista todos os posts
- `GET /api/posts/{id}` - Obtém um post específico
- `POST /api/posts` - Cria um novo post
- `PUT /api/posts/{id}` - Atualiza um post
- `DELETE /api/posts/{id}` - Remove um post

#### Páginas

- `GET /api/pages` - Lista todas as páginas
- `GET /api/pages/{id}` - Obtém uma página específica
- `POST /api/pages` - Cria uma nova página
- `PUT /api/pages/{id}` - Atualiza uma página
- `DELETE /api/pages/{id}` - Remove uma página

#### Plugins

- `GET /api/plugins` - Lista todos os plugins
- `GET /api/plugins/{id}` - Obtém um plugin específico
- `POST /api/plugins` - Instala um novo plugin
- `PUT /api/plugins/{id}` - Atualiza um plugin
Para suporte, abra uma issue no repositório. 