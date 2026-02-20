# Mover gestão de Tipos de Processo para Admin do Nextcloud

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Mover ProcessTypeList, StageManager e FormTemplateEditor para o painel de administração do Nextcloud (`/settings/admin/tramita`), deixando o app principal (`/apps/tramita`) focado apenas no uso diário (Kanban + Requisições).

**Architecture:** Criar um Vue Router separado (hash mode) para o painel admin, reutilizando as views existentes sem alterá-las. O app principal perde as rotas de administração e o sidebar fica simplificado (só "Painel").

**Tech Stack:** Vue 2.7, Vue Router 3, Pinia, @nextcloud/vue, webpack multi-entry (já configurado)

---

## Tarefa 1: Criar o router do painel admin

**Files:**
- Create: `src/adminRouter.js`

**Contexto:** O admin Vue app vive dentro da página de configurações do Nextcloud (`/settings/admin/tramita`). Para não conflitar com o roteamento do Nextcloud, usamos `mode: 'hash'`. Os nomes de rota (`processTypes`, `stageManager`, `formEditor`) devem ser **idênticos** aos do router principal para que `ProcessTypeList.vue` funcione sem modificação — ela já chama `$router.push({ name: 'stageManager', ... })`.

**Passo 1: Criar o arquivo**

```js
import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

export default new Router({
    mode: 'hash',
    routes: [
        {
            path: '/',
            name: 'processTypes',
            component: () => import('./views/ProcessTypeList.vue'),
        },
        {
            path: '/:id/stages',
            name: 'stageManager',
            component: () => import('./views/StageManager.vue'),
        },
        {
            path: '/:id/form',
            name: 'formEditor',
            component: () => import('./views/FormTemplateEditor.vue'),
        },
    ],
})
```

**Passo 2: Verificar que não há erros de sintaxe**

```bash
node -e "require('./src/adminRouter.js')" 2>&1 || echo "ok (esperado falhar sem webpack)"
```

Resultado esperado: erro de require (normal, o arquivo usa ESM) — apenas confirmar que não tem typos óbvios lendo o arquivo.

---

## Tarefa 2: Adicionar Vue Router ao entry do admin

**Files:**
- Modify: `src/admin.js`

**Contexto:** Atualmente `src/admin.js` monta `AdminSettings.vue` sem router. Precisamos injetar o router do admin para que as views admin possam usar `$route` e `$router`.

**Passo 1: Substituir o conteúdo do arquivo**

```js
import Vue from 'vue'
import adminRouter from './adminRouter.js'
import AdminSettings from './views/AdminSettings.vue'
import { createPinia, PiniaVuePlugin } from 'pinia'

Vue.use(PiniaVuePlugin)

const pinia = createPinia()

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('tramita-admin-settings')
    if (el) {
        new Vue({
            el,
            pinia,
            router: adminRouter,
            render: h => h(AdminSettings),
        })
    }
})
```

---

## Tarefa 3: Transformar AdminSettings.vue em shell de navegação

**Files:**
- Modify: `src/views/AdminSettings.vue`

**Contexto:** `AdminSettings.vue` vira o "frame" do mini-SPA admin. Exibe um botão "Voltar" quando estamos em StageManager ou FormTemplateEditor, e renderiza a view atual via `<router-view />`. O `<router-view />` renderizará ProcessTypeList, StageManager ou FormTemplateEditor conforme a rota.

**Passo 1: Substituir o conteúdo do arquivo**

```vue
<template>
    <div class="tramita-admin">
        <div v-if="showBack" class="tramita-admin__back">
            <NcButton type="tertiary" @click="$router.push({ name: 'processTypes' })">
                <template #icon>
                    <ArrowLeft :size="20" />
                </template>
                Voltar para Tipos de Processo
            </NcButton>
        </div>
        <router-view />
    </div>
</template>

<script>
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'

export default {
    name: 'AdminSettings',
    components: { NcButton, ArrowLeft },
    computed: {
        showBack() {
            return this.$route.name !== 'processTypes'
        },
    },
}
</script>

<style lang="scss" scoped>
.tramita-admin {
    padding: 0;

    &__back {
        margin-bottom: 16px;
    }
}
</style>
```

---

## Tarefa 4: Simplificar o router principal (remover rotas admin)

**Files:**
- Modify: `src/router.js`

**Contexto:** As rotas `processTypes`, `stageManager` e `formEditor` agora vivem no admin router. O router principal deve manter apenas as rotas de uso diário. A rota `adminSettings` também pode ser removida (o admin é acessado pelo painel do Nextcloud, não pela SPA).

**Passo 1: Substituir o conteúdo do arquivo**

```js
import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

export default new Router({
    routes: [
        {
            path: '/',
            name: 'dashboard',
            component: () => import('./views/Dashboard.vue'),
        },
        {
            path: '/board/:processTypeId',
            name: 'kanbanBoard',
            component: () => import('./views/KanbanBoard.vue'),
        },
        {
            path: '/request/new/:processTypeId',
            name: 'newRequest',
            component: () => import('./views/RequestForm.vue'),
        },
        {
            path: '/request/:id',
            name: 'requestDetail',
            component: () => import('./views/RequestDetail.vue'),
        },
        {
            path: '/request/:id/edit',
            name: 'editRequest',
            component: () => import('./views/RequestForm.vue'),
        },
    ],
})
```

---

## Tarefa 5: Simplificar App.vue (remover "Tipos de Processo" do sidebar)

**Files:**
- Modify: `src/App.vue`

**Contexto:** O sidebar do app principal não precisa mais de "Tipos de Processo" nem "Configurações" (ambos estão no admin do Nextcloud). Para MVP, podemos remover o sidebar completamente — a navegação acontece via botões dentro do Dashboard (Kanban). Se no futuro houver múltiplas seções de usuário, o sidebar pode ser reintroduzido.

**Passo 1: Substituir o conteúdo do arquivo**

```vue
<template>
    <NcContent app-name="tramita">
        <NcAppContent>
            <LicenseWarning v-if="licenseRequired" :show="licenseRequired" />
            <router-view v-else />
        </NcAppContent>
    </NcContent>
</template>

<script>
import { NcContent, NcAppContent } from '@nextcloud/vue'
import LicenseWarning from './components/common/LicenseWarning.vue'

export default {
    name: 'App',
    components: {
        NcContent,
        NcAppContent,
        LicenseWarning,
    },
    data() {
        return {
            licenseRequired: false,
        }
    },
    created() {
        this._licenseHandler = () => {
            this.licenseRequired = true
        }
        window.addEventListener('tramita:license-required', this._licenseHandler)
    },
    beforeDestroy() {
        window.removeEventListener('tramita:license-required', this._licenseHandler)
    },
}
</script>

<style lang="scss">
.app-tramita {
    height: 100%;

    #app-content {
        height: 100%;
    }
}
</style>
```

---

## Tarefa 6: Build e verificação

**Passo 1: Build de produção**

```bash
npm run build 2>&1 | tail -15
```

Resultado esperado: `webpack X compiled with 2 warnings` (warnings de tamanho de bundle são normais). Não deve haver erros.

**Passo 2: Verificar que o bundle admin foi gerado**

```bash
ls -la js/tramita-admin* 2>/dev/null
```

Resultado esperado: arquivos `tramita-admin.js` e `tramita-admin.js.map` atualizados (timestamp recente).

**Passo 3: Commit**

```bash
git add src/adminRouter.js src/admin.js src/views/AdminSettings.vue src/router.js src/App.vue js/
git commit -m "Mover gestão de tipos de processo para painel admin do Nextcloud"
```

---

## Verificação pós-deploy

1. Acessar `/settings/admin/tramita` — deve mostrar a lista de tipos de processo
2. Clicar em "Configurar Etapas" de um tipo — deve mostrar o StageManager com botão "Voltar"
3. Clicar em "Configurar Formulário" — deve mostrar o FormTemplateEditor com botão "Voltar"
4. Acessar `/apps/tramita` — deve mostrar o Dashboard/Kanban sem sidebar de admin
5. Criar uma requisição — fluxo deve funcionar normalmente
