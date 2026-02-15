# Tramita

Sistema completo de gestão de processos e tramitação de documentos para órgãos públicos municipais brasileiros, integrado ao Nextcloud.

**Desenvolvido por [Genius Apps](https://geniusapps.com.br)**

## Funcionalidades

- **Formulários configuráveis** — Criação de templates com diversos tipos de campo (texto, data, seleção, upload, etc.)
- **Quadro Kanban** — Visualização e movimentação de solicitações entre etapas com drag-and-drop
- **Protocolo automático** — Numeração sequencial no formato `PREFIXO-AAAA/NNNNNN`
- **Atribuição de responsáveis** — Designação de usuários com papéis (responsável, revisor, aprovador)
- **Prazos e prioridades** — Controle de vencimentos com indicadores visuais
- **Etiquetas** — Categorização por tags coloridas
- **Comentários** — Discussões com threading e menções
- **Notificações** — Integração com o sistema de notificações do Nextcloud
- **Histórico de auditoria** — Log imutável de todas as movimentações
- **Sistema de licença** — Validação por chave com grace period

## Requisitos

| Componente | Versão |
|------------|--------|
| Nextcloud | 28 – 31 |
| PHP | 8.1+ |
| Node.js | 20+ ou 22+ |
| npm | 10+ ou 11+ |
| Banco de dados | SQLite, MySQL 8+ ou PostgreSQL 12+ |

## Instalação

### Via Nextcloud App Store

Procure por **Tramita** na loja de apps do Nextcloud e clique em "Instalar".

### Manual

```bash
# Clonar o repositório na pasta apps do Nextcloud
cd /path/to/nextcloud/apps
git clone https://github.com/geniusapps/tramita.git
cd tramita

# Instalar dependências
composer install
npm ci

# Build do frontend
npm run build

# Habilitar o app
php occ app:enable tramita
```

Acesse o Nextcloud e clique em **Tramita** no menu de navegação.

## Desenvolvimento

### Setup inicial

```bash
make dev-setup    # Instala dependências PHP e JS
```

### Build e watch

```bash
npm run dev       # Build de desenvolvimento
npm run watch     # Build com file watching
npm run serve     # Dev server com hot reload
```

### Linting

```bash
make lint         # Lint de JS e PHP
make lint-fix     # Auto-fix de JS e PHP
```

### Testes

```bash
composer run test        # Todos os testes PHPUnit
composer run test:unit   # Apenas testes unitários
```

### Empacotamento para App Store

```bash
make appstore     # Gera build/tramita.tar.gz
```

## Arquitetura

### Backend

Arquitetura em três camadas sobre o Nextcloud App Framework:

- **Controller** (`lib/Controller/`) — Camada HTTP, delega para services
- **Service** (`lib/Service/`) — Lógica de negócio e validação
- **Mapper/Entity** (`lib/Db/`) — Acesso a dados via QBMapper + entidades OCP

### Frontend

SPA com Vue.js 2.7 montada via `templates/index.php`:

- **Views** (`src/views/`) — Componentes de página (Dashboard, KanbanBoard, RequestForm, etc.)
- **Components** (`src/components/`) — Componentes reutilizáveis (`kanban/`, `forms/`, `common/`)
- **Stores** (`src/store/`) — Estado global com Pinia
- **Services** (`src/services/`) — Clientes API sobre `@nextcloud/axios`

### Banco de dados

16 tabelas prefixadas com `tramita_`, gerenciadas por migrations em `lib/Migration/`. Schema completo em [docs/architecture/banco-de-dados.md](docs/architecture/banco-de-dados.md).

### API

~35 endpoints RESTful em `/apps/tramita/api/v1/`. Documentação completa em [docs/architecture/api-endpoints.md](docs/architecture/api-endpoints.md).

## Documentação

| Documento | Descrição |
|-----------|-----------|
| [Stack Tecnológica](docs/architecture/stack-tecnologica.md) | PHP, Vue.js, banco de dados, build system |
| [Estrutura de Diretórios](docs/architecture/estrutura-diretorios.md) | Organização do projeto |
| [Banco de Dados](docs/architecture/banco-de-dados.md) | Schema, índices, migrations |
| [API Endpoints](docs/architecture/api-endpoints.md) | Endpoints com exemplos de request/response |
| [Sistema de Licença](docs/architecture/sistema-licenca.md) | Validação, middleware, grace period |
| [Guia para Iniciantes](docs/guides/guia-iniciante-nextcloud-dev.md) | Conceitos e padrões do Nextcloud |
| [Publicação na App Store](docs/guides/publicacao-app-store.md) | Certificado, assinatura, regras |
| [Cuidados de Segurança](docs/guides/cuidados-seguranca.md) | OWASP, LGPD, controle de acesso |

## Roadmap

| Fase | Descrição | Status |
|------|-----------|--------|
| 1 | MVP — Formulários, Kanban, protocolo, etiquetas, notificações, licença | Em desenvolvimento |
| 2 | Integração com documentos (Nextcloud Files, Collabora, OnlyOffice) | Planejado |
| 3 | Assinatura digital via GOV.BR | Planejado |
| 4 | Portal externo para cidadãos (formulários públicos) | Planejado |
| 5 | Relatórios, dashboards e analytics | Planejado |

## Licença

[AGPL-3.0-or-later](LICENSE) — Copyright 2026 Genius Apps
