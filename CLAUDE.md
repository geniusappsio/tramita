# CLAUDE.md

Este arquivo orienta o Claude Code (claude.ai/code) ao trabalhar com o código deste repositório.

## Idioma

Toda documentação, comentários no código, mensagens de commit e comunicação devem ser em **Português do Brasil (pt-BR)**.

## Comandos de Build e Desenvolvimento

```bash
# Setup (instalar todas as dependências)
make dev-setup                  # ou: composer install && npm ci

# Builds do frontend
npm run dev                     # Build de desenvolvimento (source maps)
npm run build                   # Build de produção (minificado)
npm run watch                   # Build de dev com file watching
npm run serve                   # Servidor de dev com hot reload

# Linting
npm run lint                    # ESLint (JS/Vue)
npm run lint:fix                # ESLint auto-fix
npm run stylelint               # Stylelint (SCSS)
composer run cs:check           # Padrões de código PHP (dry-run)
composer run cs:fix             # Padrões de código PHP auto-fix
composer run lint               # Verificação de sintaxe PHP
make lint                       # Lint de JS e PHP juntos

# Testes
composer run test               # Todos os testes PHPUnit
composer run test:unit           # Apenas testes unitários

# Empacotamento
make appstore                   # Gerar .tar.gz para a Nextcloud App Store
make clean                      # Remover js/, node_modules/, vendor/, build/
```

## Arquitetura

**Tramita** é um app Nextcloud para gestão de processos e documentos em prefeituras brasileiras. App ID: `tramita`, namespace: `OCA\Tramita`.

### Backend (PHP 8.1+)

Arquitetura em três camadas com injeção de dependência via Nextcloud App Framework:

```
Controller (lib/Controller/)  →  camada HTTP fina, retorna DataResponse
    ↓
Service (lib/Service/)        →  lógica de negócio, validação, orquestração
    ↓
Mapper (lib/Db/*Mapper.php)   →  query builders com QBMapper
    ↓
Entity (lib/Db/*.php)         →  objetos OCP Entity, implementam JsonSerializable
```

- **Application.php** (`lib/AppInfo/`) — bootstrap do app, registra `LicenseMiddleware`
- **LicenseMiddleware** (`lib/Middleware/`) — intercepta todas as requisições API exceto PageController e ConfigController
- **Migrations** (`lib/Migration/`) — schema em três fases: tabelas core → requests → relações/rastreamento
- Todas as entidades usam soft deletes (`deleted_at`) e timestamps de auditoria (`created_at`, `updated_at`)
- Tabelas prefixadas com `tramita_` (16 tabelas no total)

### Frontend (Vue 2.7 + Pinia)

SPA montada no `#content` via `templates/index.php`:

- **Entry**: `src/main.js` → `src/App.vue` → `src/router.js`
- **Views** (`src/views/`): Dashboard, KanbanBoard, RequestForm, RequestDetail, ProcessTypeList, StageManager, FormTemplateEditor, AdminSettings
- **Components** (`src/components/`): organizados em subdiretórios `kanban/`, `forms/`, `common/`
- **Stores** (`src/store/`): stores Pinia com Composition API — um por entidade de domínio
- **Services** (`src/services/`): camada de cliente API sobre `@nextcloud/axios`
- **Estilização**: SCSS com escopo e CSS custom properties do Nextcloud (`--color-primary-element`, etc.), nomenclatura BEM

Dependências frontend principais: `@nextcloud/vue` 8.x (biblioteca de componentes), `vuedraggable` (drag-and-drop no Kanban), `vue-router` 3.x (roteamento client-side).

### Rotas da API (`appinfo/routes.php`)

API RESTful em `/api/v1/` com ~35 endpoints. Recursos: process-types, stages, form-templates, form-fields, requests, labels, config. Stages e form-templates são aninhados sob process-types. Operações de card (assign, labels, deadline, reorder) são aninhadas sob requests.

Rotas de página usam catch-all `/{path}` para roteamento client-side da SPA.

## Convenções Principais

- **Namespace PHP**: `OCA\Tramita` mapeado para `lib/` via PSR-4
- **Config Webpack**: delega inteiramente ao `@nextcloud/webpack-vue-config` — `webpack.js` é um wrapper de uma linha
- **Banco de dados**: suporta SQLite, MySQL 8+, PostgreSQL 12+. Usar `IQueryBuilder` para queries, nunca SQL puro
- **Compatibilidade Nextcloud**: versões 28–31
- **Node**: 20+ ou 22+, npm 10+ ou 11+
- **Licença**: AGPL-3.0-or-later
- **i18n**: usar `@nextcloud/l10n` no frontend, `IL10N` no backend
- **Versão Vue**: Vue 2.7 (NÃO Vue 3) — usar Options API ou Composition API via função `setup()`; `<script setup>` não está disponível
