# Tramita - Stack Tecnológica

## Visão Geral da Stack

O Tramita é um app Nextcloud que segue a arquitetura padrão do framework OCP (OwnCloud Platform). Isso significa:

- **Backend em PHP** usando o AppFramework do Nextcloud
- **Frontend em Vue.js** usando a biblioteca de componentes @nextcloud/vue
- **Banco de dados abstrato** que funciona com SQLite, MySQL e PostgreSQL

---

## Backend

### PHP 8.1+

O Nextcloud usa PHP como linguagem principal. O app Tramita roda dentro do contexto do Nextcloud, não como aplicação standalone.

**Framework**: OCP (OwnCloud Platform) / AppFramework
- **Não é** Laravel, Symfony ou outro framework PHP
- É o framework próprio do Nextcloud, com convenções específicas
- Fornece: Controllers, Dependency Injection, ORM (QBMapper), Middleware, etc.

**Namespace**: `OCA\Tramita` (OCA = OwnCloud Apps)

### Dependências PHP (composer.json)

```json
{
    "require": {
        "php": ">=8.1"
    },
    "require-dev": {
        "nextcloud/ocp": "dev-master",
        "phpunit/phpunit": "^9.6",
        "nextcloud/coding-standard": "^1.2"
    },
    "autoload": {
        "psr-4": {
            "OCA\\Tramita\\": "lib/"
        }
    }
}
```

**Nota**: O `nextcloud/ocp` é incluído apenas como dependência de desenvolvimento para autocompletar e análise estática. Em produção, o Nextcloud fornece essas classes.

---

## Frontend

### Vue.js 2.7

O Nextcloud usa Vue.js 2.x (não Vue 3 ainda). A versão 2.7 é a recomendada pois inclui Composition API backport.

### @nextcloud/vue 8.x

Biblioteca oficial de componentes UI do Nextcloud. Fornece componentes prontos que seguem o design system do Nextcloud:

- `NcAppContent`, `NcAppNavigation`, `NcAppSidebar` (layout)
- `NcButton`, `NcTextField`, `NcSelect`, `NcDateTimePicker` (formulários)
- `NcModal`, `NcDialog`, `NcPopover` (overlays)
- `NcAvatar`, `NcUserBubble` (usuários)
- `NcActionButton`, `NcActions` (menus de ação)
- E muitos mais

**Documentação**: https://nextcloud-vue-components.netlify.app/

### Pinia 2.x

State management moderno para Vue.js. Substitui o Vuex como recomendação oficial.
- Stores modulares (um por domínio: requests, stages, labels, etc.)
- TypeScript-friendly
- Dev tools integradas

### Vue Router 3.x

Roteamento SPA (Single Page Application). Versão 3.x é para Vue 2.x.
- Lazy loading de views para performance
- Catch-all route para SPA (todas as rotas são servidas pelo mesmo index.php)

### vuedraggable 2.x

Componente Vue para drag-and-drop baseado no Sortable.js.
- Usado para mover cards entre colunas do Kanban
- Reordenar etapas e campos de formulário

### Pacotes Nextcloud

| Pacote | Uso |
|--------|-----|
| `@nextcloud/axios` | HTTP client pré-configurado com tokens CSRF |
| `@nextcloud/router` | Gera URLs corretas para o contexto do Nextcloud |
| `@nextcloud/l10n` | Internacionalização (translate, translatePlural) |
| `@nextcloud/initial-state` | Receber dados do PHP para o Vue na carga inicial |
| `@nextcloud/dialogs` | Toasts, confirmações, seletores de arquivo |
| `@nextcloud/moment` | Manipulação de datas |

---

## Banco de Dados

### Abstração via OCP\IDBConnection

O Nextcloud abstrai o acesso ao banco através da interface `OCP\IDBConnection`. Você **nunca** escreve SQL direto — usa o QueryBuilder:

```php
$qb = $this->db->getQueryBuilder();
$qb->select('*')
   ->from('tramita_requests')
   ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
```

### Bancos Suportados

| Banco | Versão Mínima | Notas |
|-------|---------------|-------|
| **SQLite** | — | Bom para desenvolvimento, não recomendado para produção |
| **MySQL/MariaDB** | 8.0 / 10.6 | Mais comum em produção |
| **PostgreSQL** | 12 | Alternativa robusta |

### Migrações

- Definidas em classes PHP em `lib/Migration/`
- Nome da classe: `VersionXXXXDateYYYYMMDDHHmmss`
- Estendem `OCP\Migration\SimpleMigrationStep`
- Executadas automaticamente na habilitação/atualização do app
- **Nunca alterar migrações existentes** — criar novas para mudanças

---

## Build System

### Webpack (@nextcloud/webpack-vue-config)

Configuração Webpack pré-configurada pela equipe Nextcloud. Inclui:
- Babel para transpilação
- SCSS/CSS processing
- Vue single-file component support
- Source maps
- Minificação em produção

**Arquivo**: `webpack.js`
```javascript
const webpackConfig = require('@nextcloud/webpack-vue-config')
module.exports = webpackConfig
```

### npm Scripts

```bash
npm run dev      # Build desenvolvimento
npm run watch    # Build + watch para hot-reload
npm run build    # Build produção (minificado)
npm run lint     # Lint JavaScript/Vue
```

### Composer Scripts

```bash
composer install          # Instalar dependências
composer run lint         # Verificar sintaxe PHP
composer run cs:check     # Verificar coding standard
composer run cs:fix       # Corrigir coding standard
composer run test         # Executar testes
```

### Makefile

O Makefile automatiza tarefas comuns:

```bash
make dev-setup            # Instalar tudo (composer + npm)
make build-js-production  # Build frontend para produção
make watch                # Desenvolvimento com hot-reload
make lint                 # Lint PHP + JS
make test                 # Executar testes
make appstore             # Gerar .tar.gz para App Store
make clean                # Limpar artefatos de build
```

---

## Ferramentas de Desenvolvimento

### Necessárias

| Ferramenta | Versão | Uso |
|-----------|--------|-----|
| **PHP** | 8.1+ | Backend |
| **Composer** | 2.x | Gerenciador de pacotes PHP |
| **Node.js** | 20+ ou 22+ | Build frontend |
| **npm** | 10+ ou 11+ | Gerenciador de pacotes JS |
| **Git** | 2.x | Controle de versão |

### Recomendadas

| Ferramenta | Uso |
|-----------|-----|
| **VS Code** | IDE com extensões PHP Intelephense + Volar (Vue) |
| **Docker** | Ambiente de desenvolvimento Nextcloud isolado |
| **php-cs-fixer** | Formatação automática de código PHP |
| **ESLint** | Lint de JavaScript/Vue |
| **Postman / Thunder Client** | Testar endpoints da API |

---

## Ambiente de Desenvolvimento

### Opção 1: Nextcloud existente em produção
Se você já tem uma instância Nextcloud rodando:
1. Clone o repositório em `/path/to/nextcloud/apps/tramita`
2. `cd tramita && composer install && npm ci && npm run dev`
3. `php occ app:enable tramita`

### Opção 2: Docker para desenvolvimento
```bash
docker run -d \
  --name nextcloud-dev \
  -p 8080:80 \
  -v /home/leandro/Projects/nextcloud/apps/tramita:/var/www/html/apps/tramita \
  nextcloud:latest
```

### Opção 3: Nextcloud development environment
A equipe Nextcloud oferece um ambiente de desenvolvimento completo:
- https://github.com/juliushaertl/nextcloud-docker-dev

---

## Compatibilidade com Nextcloud

| Versão Nextcloud | Suporte |
|-----------------|---------|
| 28 | Sim (min-version) |
| 29 | Sim |
| 30 | Sim |
| 31 | Sim (max-version) |

A cada nova major version do Nextcloud, será necessário testar e possivelmente atualizar a max-version no info.xml.

---

## Migração para Vue 3 (Roadmap)

### Contexto

O Vue 2.7 atingiu **End of Life em 31/12/2023**, mas o Nextcloud ainda não completou a migração para Vue 3. O plano oficial é:

| Versão NC | Vue | Status |
|-----------|-----|--------|
| NC 28–30 | Vue 2.7 | Produção estável |
| NC 31–32 | Vue 2.7 + Vue 3 | Período de transição — ambas as versões coexistem |
| **NC 33** (fev 2026) | **Vue 3** | Migração oficial ([roadmap](https://github.com/nextcloud/server/issues/55428)) |

O Tramita **v1.x** foi construído com Vue 2.7 para máxima compatibilidade (NC 28–31). A migração para Vue 3 deve ser feita no **Tramita v2.x**, alinhada com NC 33.

### O que muda na migração

| Item | Vue 2 (atual) | Vue 3 (futuro) |
|------|---------------|----------------|
| `vue` | `^2.7.16` | `^3.5.x` |
| `@nextcloud/vue` | `^8.x` | `^9.x` |
| `vue-router` | `^3.6.x` | `^4.x` |
| `pinia` | `^2.x` (sem mudança) | `^2.x` (sem mudança) |
| `vuedraggable` | `^2.24.x` | `^4.x` ([vue.draggable.next](https://github.com/SortableJS/vue.draggable.next)) |
| `vue-loader` | `^15.x` | `^17.x` |
| Build tool | Webpack (`@nextcloud/webpack-vue-config`) | **Vite** ([boilerplate oficial](https://help.nextcloud.com/t/release-modern-vue-3-vite-boilerplate-for-nextcloud-33/237903)) |
| API de componentes | Options API (`export default {}`) | Composition API (`<script setup>`) |
| Template refs | `this.$refs` | `ref()` / `useTemplateRef()` |
| Event Bus | `this.$emit` / `EventBus` | `defineEmits()` |
| v-model em componentes | `value` prop + `input` event | `modelValue` prop + `update:modelValue` event |

### Checklist de migração

Quando chegar a hora (NC 33 estável + `@nextcloud/vue` v9 estável):

1. **Atualizar dependências** no `package.json`:
   ```bash
   npm install vue@3 vue-router@4 @nextcloud/vue@9 vuedraggable@4
   npm install -D vue-loader@17
   # Remover @nextcloud/webpack-vue-config e adotar Vite
   ```

2. **Migrar build system** de Webpack para Vite:
   - Substituir `webpack.js` por `vite.config.js`
   - Usar o [boilerplate Vue 3 + Vite para NC 33+](https://help.nextcloud.com/t/release-modern-vue-3-vite-boilerplate-for-nextcloud-33/237903)

3. **Atualizar `main.js`**:
   ```javascript
   // Vue 2 (atual)
   import Vue from 'vue'
   new Vue({ render: h => h(App), router, pinia }).$mount('#content')

   // Vue 3 (futuro)
   import { createApp } from 'vue'
   const app = createApp(App)
   app.use(router)
   app.use(pinia)
   app.mount('#content')
   ```

4. **Migrar componentes** de Options API para Composition API:
   ```vue
   <!-- Vue 2 (atual) -->
   <script>
   export default {
       data() { return { count: 0 } },
       methods: { increment() { this.count++ } },
   }
   </script>

   <!-- Vue 3 (futuro) -->
   <script setup>
   import { ref } from 'vue'
   const count = ref(0)
   const increment = () => count.value++
   </script>
   ```

5. **Atualizar imports do @nextcloud/vue**:
   ```javascript
   // Vue 2 (atual)
   import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'

   // Vue 3 (futuro) — imports diretos
   import { NcButton } from '@nextcloud/vue'
   ```

6. **Atualizar vuedraggable** (API diferente na v4):
   ```vue
   <!-- Vue 2 (atual) -->
   <draggable v-model="items" group="cards" @end="onDragEnd">
       <div v-for="item in items" :key="item.id">...</div>
   </draggable>

   <!-- Vue 3 (futuro) -->
   <draggable v-model="items" group="cards" item-key="id" @end="onDragEnd">
       <template #item="{ element }">
           <div>...</div>
       </template>
   </draggable>
   ```

7. **Atualizar `info.xml`**: Mudar `min-version` para 33 e ajustar `max-version`.

8. **Testar em NC 33** com SQLite, MySQL e PostgreSQL.

### Estratégia recomendada

- **Não migrar prematuramente** — esperar o `@nextcloud/vue` v9 estar estável e os apps oficiais (Files, Deck) terem migrado
- Manter o **Tramita v1.x com Vue 2.7** para NC 28–32
- Lançar **Tramita v2.x com Vue 3** para NC 33+
- Usar branches separadas: `main` (Vue 2, v1.x) e `vue3` (Vue 3, v2.x)

### Referências

- [Vue 3 Migration — NC33 Roadmap (GitHub Issue)](https://github.com/nextcloud/server/issues/55428)
- [Boilerplate Vue 3 + Vite para NC 33+](https://help.nextcloud.com/t/release-modern-vue-3-vite-boilerplate-for-nextcloud-33/237903)
- [Preparando App Nextcloud para Vue 3](https://help.nextcloud.com/t/preparing-nextcloud-app-for-vue-3-composition-api-and-typescript/224902)
- [Vue 3 Migration Guide (oficial Vue)](https://v3-migration.vuejs.org/)
- [Upgrade to Nextcloud 31 (Developer Manual)](https://docs.nextcloud.com/server/stable/developer_manual/app_publishing_maintenance/app_upgrade_guide/upgrade_to_31.html)
