# Gestão de Tarefas por Times

API REST desenvolvida em Laravel para gerenciamento de tarefas organizadas por times, permitindo que usuários criem times, projetos e tarefas de forma estruturada.

## Tecnologias

- Laravel 11
- MySQL
- Laravel Sanctum
- Laravel Sail

## Funcionalidades

- Autenticação de usuários (registro/login/logout)
- CRUD completo de Times
- CRUD de Projetos por Time
- CRUD de Tarefas por Projeto
- Controle de status das tarefas

## Instalação

### Pré-requisitos
- Docker e Docker Compose
- Git

### Configuração

1. Clone o repositório
```bash
git clone https://github.com/seu-usuario/gestao-de-tarefas-por-times-laravel.git
cd gestao-de-tarefas-por-times-laravel
```

2. Inicie os containers
```bash
cd src
./vendor/bin/sail up -d
```

3. Instale as dependências
```bash
./vendor/bin/sail composer install
```

4. Configure o ambiente
```bash
cp .env.example .env
./vendor/bin/sail artisan key:generate
```

5. Execute as migrações
```bash
./vendor/bin/sail artisan migrate
```

## Endpoints da API

### Autenticação
- `POST /api/register` - Registrar usuário
- `POST /api/login` - Fazer login
- `POST /api/logout` - Fazer logout
- `GET /api/me` - Dados do usuário autenticado

### Times
- `GET /api/teams` - Listar times
- `POST /api/teams` - Criar time
- `GET /api/teams/{id}` - Visualizar time
- `PUT /api/teams/{id}` - Atualizar time
- `DELETE /api/teams/{id}` - Deletar time

### Projetos
- `GET /api/teams/{teamId}/projects` - Listar projetos do time
- `POST /api/teams/{teamId}/projects` - Criar projeto

### Tarefas
- `GET /api/projects/{projectId}/tasks` - Listar tarefas do projeto
- `POST /api/projects/{projectId}/tasks` - Criar tarefa
- `PUT /api/projects/{projectId}/tasks/{taskId}` - Atualizar tarefa
- `PATCH /api/projects/{projectId}/tasks/{taskId}/status` - Atualizar status

## Autenticação

A API utiliza Laravel Sanctum. Inclua o token no header das requisições:
```
Authorization: Bearer {token}
```

## Comandos Úteis

```bash
# Iniciar containers
./vendor/bin/sail up -d

# Parar containers
./vendor/bin/sail down

# Executar migrações
./vendor/bin/sail artisan migrate
```