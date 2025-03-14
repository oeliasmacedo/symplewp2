# Guia de Contribuição

Obrigado pelo seu interesse em contribuir com o WordPress Manager! Este documento fornece diretrizes e instruções para contribuir com o projeto.

## 📋 Como Contribuir

1. Faça um fork do repositório
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 🧪 Desenvolvimento

### Pré-requisitos

- PHP 8.1 ou superior
- Composer
- Node.js 16 ou superior
- Docker e Docker Compose
- MySQL 8.0 ou superior
- Redis

### Configuração do Ambiente

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

4. Inicie os containers Docker:
```bash
docker-compose up -d
```

5. Execute as migrações:
```bash
php artisan migrate
```

### Padrões de Código

- Siga os padrões PSR-12
- Use PHP CS Fixer para formatar o código
- Execute PHPStan para verificar tipos
- Escreva testes unitários e de integração
- Documente suas alterações

### Testes

Execute os testes antes de enviar um Pull Request:
```bash
# Testes unitários
php artisan test --filter=Unit

# Testes de integração
php artisan test --filter=Integration

# Testes end-to-end
npm run test:e2e
```

## 📝 Pull Requests

1. Atualize a documentação se necessário
2. Adicione testes para novas funcionalidades
3. Certifique-se de que todos os testes passam
4. Descreva detalhadamente as mudanças no PR
5. Inclua screenshots para mudanças na UI

## 🐛 Reportando Bugs

1. Use o template de issue para bugs
2. Descreva o comportamento esperado
3. Inclua passos para reproduzir o bug
4. Adicione logs e screenshots se relevante
5. Mencione sua versão do PHP e WordPress

## 💡 Sugestões de Features

1. Use o template de issue para features
2. Descreva o problema que a feature resolve
3. Explique como a feature funcionaria
4. Mencione alternativas consideradas
5. Inclua exemplos de uso

## 📚 Documentação

- Atualize o README.md se necessário
- Documente novas APIs
- Adicione exemplos de uso
- Atualize a documentação de configuração
- Inclua screenshots para mudanças na UI

## 🔒 Segurança

- Não inclua credenciais no código
- Use variáveis de ambiente para dados sensíveis
- Siga as melhores práticas de segurança
- Reporte vulnerabilidades em privado
- Mantenha as dependências atualizadas

## 📅 Manutenção

- Mantenha o código atualizado
- Remova código não utilizado
- Atualize as dependências
- Limpe o histórico de commits
- Mantenha a documentação atualizada

## 🤝 Comunidade

- Seja respeitoso e profissional
- Ajude outros contribuidores
- Participe das discussões
- Compartilhe conhecimento
- Mantenha o foco no projeto

## 📄 Licença

Ao contribuir, você concorda que suas contribuições serão licenciadas sob a licença MIT do projeto. 