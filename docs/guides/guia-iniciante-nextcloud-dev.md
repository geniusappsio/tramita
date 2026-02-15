# Guia para Iniciantes - Desenvolvimento de Apps Nextcloud

## O que é um App Nextcloud?

Um app Nextcloud é um **plugin** que estende as funcionalidades do Nextcloud. Diferente de uma aplicação standalone, ele roda **dentro** do contexto do Nextcloud, usando:

- O sistema de usuários/grupos do Nextcloud
- O banco de dados do Nextcloud
- O sistema de arquivos do Nextcloud
- O sistema de notificações do Nextcloud
- A interface visual do Nextcloud

---

## Conceitos Fundamentais

### OCP (OwnCloud Platform)
É o framework PHP do Nextcloud. Fornece classes e interfaces que seu app usa:
- `OCP\AppFramework\Controller` — Base para controllers
- `OCP\AppFramework\Db\Entity` — Base para entidades do banco
- `OCP\AppFramework\Db\QBMapper` — Base para mappers (queries)
- `OCP\IDBConnection` — Acesso ao banco de dados
- `OCP\IRequest` — Informações da requisição HTTP
- `OCP\IConfig` — Configurações do sistema e do app

### OCA (OwnCloud Apps)
É o namespace onde apps de terceiros vivem. Seu app será `OCA\Tramita`.

### Padrão MVC no Nextcloud
```
HTTP Request → Controller → Service → Mapper → Banco de Dados
                   ↓
              JSON Response
```

- **Controller**: Recebe HTTP, extrai parâmetros, retorna response
- **Service**: Lógica de negócio (validações, regras, orquestração)
- **Mapper**: Queries ao banco de dados
- **Entity**: Objeto que representa uma linha da tabela

---

## Ciclo de Vida de um App

### 1. Reconhecimento
O Nextcloud lê o `appinfo/info.xml` para saber que o app existe.

### 2. Habilitação
Quando o admin habilita o app (`php occ app:enable tramita`):
- Migrações de banco são executadas automaticamente
- O app aparece no menu de navegação (se configurado no info.xml)

### 3. Requisição
Quando o usuário acessa o app:
1. Nextcloud lê `appinfo/routes.php` para encontrar o controller correto
2. O `Application.php` é instanciado (bootstrap)
3. Dependências são injetadas automaticamente via DI container
4. Middlewares são executados (ex: LicenseMiddleware)
5. O controller é executado e retorna uma response

### 4. Atualização
Quando a versão no info.xml muda:
- Novas migrações são executadas automaticamente
- Repair steps são executados (se configurados)

---

## Arquivos Obrigatórios

### `appinfo/info.xml`
O "cartão de identidade" do app. Sem ele, o Nextcloud não reconhece o app.

```xml
<?xml version="1.0"?>
<info xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="https://apps.nextcloud.com/schema/apps/info.xsd">
    <id>tramita</id>              <!-- ID único do app (lowercase, sem espaços) -->
    <name>Tramita</name>          <!-- Nome de exibição -->
    <version>1.0.0</version>      <!-- Versão semântica -->
    <licence>agpl</licence>       <!-- Obrigatório: AGPL-3.0-or-later -->
    <namespace>Tramita</namespace> <!-- Namespace PHP: OCA\{Namespace} -->
    <dependencies>
        <nextcloud min-version="28" max-version="31"/>
    </dependencies>
</info>
```

### `appinfo/routes.php`
Define todas as URLs que o app responde:

```php
<?php
return [
    'routes' => [
        // nome => 'nomeDoController#nomeDoMetodo'
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'processType#index', 'url' => '/api/v1/process-types', 'verb' => 'GET'],
        ['name' => 'processType#create', 'url' => '/api/v1/process-types', 'verb' => 'POST'],
    ],
];
```

**Convenção de nomes**: `nomeDoController` em camelCase → PHP procura a classe `NomeDoControllerController` no namespace `OCA\Tramita\Controller`.

### `lib/AppInfo/Application.php`
Bootstrap do app. Registra serviços, middleware e configurações:

```php
<?php
namespace OCA\Tramita\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap {
    public const APP_ID = 'tramita';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void {
        // Registrar middleware, settings, notifiers, etc.
    }

    public function boot(IBootContext $context): void {
        // Lógica adicional de inicialização
    }
}
```

---

## Como Funciona o Backend

### Controller (exemplo simplificado)

```php
<?php
namespace OCA\Tramita\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\IRequest;

class ProcessTypeController extends Controller {
    public function __construct(
        IRequest $request,
        private ProcessTypeService $service,
        private ?string $userId,        // Nextcloud injeta automaticamente!
    ) {
        parent::__construct('tramita', $request);
    }

    #[NoAdminRequired]    // Permite acesso a não-admins
    public function index(): JSONResponse {
        $types = $this->service->findAll($this->userId);
        return new JSONResponse($types);
    }
}
```

**Pontos importantes**:
- `$userId` é injetado automaticamente pelo Nextcloud
- `#[NoAdminRequired]` é necessário para endpoints que não-admins podem acessar
- Sem esse atributo, apenas admins conseguem chamar o endpoint

### Entity (exemplo)

```php
<?php
namespace OCA\Tramita\Db;

use OCP\AppFramework\Db\Entity;

class ProcessType extends Entity {
    protected $name;        // → coluna 'name' no banco
    protected $slug;        // → coluna 'slug'
    protected $prefix;      // → coluna 'prefix'
    protected $isActive;    // → coluna 'is_active' (auto-convertido!)

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('isActive', 'boolean');
    }
}
```

**Mágica**: O Nextcloud converte automaticamente `camelCase` → `snake_case`:
- `$this->isActive` ↔ coluna `is_active`
- Getters/setters são criados automaticamente: `getIsActive()`, `setIsActive()`

### Mapper (exemplo)

```php
<?php
namespace OCA\Tramita\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class ProcessTypeMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        // 2º param = nome da tabela (sem 'oc_')
        // 3º param = classe da Entity
        parent::__construct($db, 'tramita_proc_types', ProcessType::class);
    }

    public function findAll(): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->isNull('deleted_at'));
        return $this->findEntities($qb);
    }
}
```

**Métodos herdados do QBMapper**:
- `findEntity($qb)` — Retorna uma Entity (ou erro se não encontrar)
- `findEntities($qb)` — Retorna array de Entities
- `insert($entity)` — Insere no banco
- `update($entity)` — Atualiza no banco
- `delete($entity)` — Deleta do banco

### Migration (exemplo)

```php
<?php
namespace OCA\Tramita\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version0100Date20260214120000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        $schema = $schemaClosure();

        if (!$schema->hasTable('tramita_proc_types')) {
            $table = $schema->createTable('tramita_proc_types');
            $table->addColumn('id', Types::BIGINT, [
                'autoincrement' => true,
                'notnull' => true,
                'unsigned' => true,
            ]);
            $table->addColumn('name', Types::STRING, [
                'notnull' => true,
                'length' => 256,
            ]);
            // ... mais colunas
            $table->setPrimaryKey(['id']);
            $table->addIndex(['is_active'], 'tramita_ptype_active_idx');
        }

        return $schema;
    }
}
```

**Regras de ouro para migrações**:
1. Nunca altere uma migração depois que ela foi executada
2. Sempre crie uma nova migração para mudanças
3. Use `Types::` constants para tipos de coluna
4. Sempre verifique `!$schema->hasTable()` antes de criar

---

## Como Funciona o Frontend

### Template PHP (templates/index.php)

O Nextcloud renderiza um template PHP simples que carrega o bundle Vue:

```php
<?php
use OCP\Util;

Util::addScript('tramita', 'tramita-main');  // Carrega js/tramita-main.js
Util::addStyle('tramita', 'tramita');        // Carrega css/tramita.css
?>
<div id="content" class="app-tramita"></div>
```

### Entry Point Vue (src/main.js)

```javascript
import Vue from 'vue'
import App from './App.vue'
import router from './router.js'
import { createPinia, PiniaVuePlugin } from 'pinia'

Vue.use(PiniaVuePlugin)
const pinia = createPinia()

new Vue({
    el: '#content',      // Monta no <div id="content">
    router,
    pinia,
    render: h => h(App),
})
```

### API Service (src/services/api.js)

```javascript
import axios from '@nextcloud/axios'          // Axios pré-configurado com CSRF
import { generateUrl } from '@nextcloud/router' // Gera URLs corretas

const baseUrl = generateUrl('/apps/tramita')

export const apiClient = axios.create({
    baseURL: baseUrl,
})
```

**Importante**: Sempre use `@nextcloud/axios` em vez do axios puro. Ele já inclui o token CSRF necessário.

---

## Dependency Injection (Injeção de Dependências)

O Nextcloud tem um container DI automático. Quando você declara um parâmetro no construtor de um controller, o Nextcloud injeta automaticamente:

```php
class RequestController extends Controller {
    public function __construct(
        IRequest $request,           // Nextcloud injeta automaticamente
        private RequestService $service,  // Nextcloud cria e injeta
        private ?string $userId,     // Nextcloud injeta o user logado
    ) {
        parent::__construct('tramita', $request);
    }
}
```

Para que isso funcione, seus services e mappers precisam ter construtores com tipos declarados:

```php
class RequestService {
    public function __construct(
        private RequestMapper $mapper,    // Nextcloud cria e injeta
        private ProtocolService $protocol, // Nextcloud cria e injeta
    ) {}
}

class RequestMapper extends QBMapper {
    public function __construct(IDBConnection $db) { // Nextcloud injeta
        parent::__construct($db, 'tramita_requests', Request::class);
    }
}
```

O container resolve toda a cadeia de dependências automaticamente.

---

## Comandos Úteis

### occ (Nextcloud CLI)

```bash
# Habilitar/desabilitar o app
php occ app:enable tramita
php occ app:disable tramita

# Ver status das migrações
php occ migrations:status tramita

# Executar migrações manualmente
php occ migrations:migrate tramita

# Gerar nova migração
php occ migrations:generate tramita 0200

# Limpar cache
php occ maintenance:repair

# Ver informações do app
php occ app:info tramita
```

### npm (Frontend)

```bash
npm ci              # Instalar dependências (usar ci, não install)
npm run dev         # Build desenvolvimento
npm run watch       # Build + hot-reload
npm run build       # Build produção
npm run lint        # Verificar erros de lint
npm run lint:fix    # Corrigir erros de lint
```

### Composer (Backend)

```bash
composer install              # Instalar dependências
composer run lint             # Verificar sintaxe PHP
composer run cs:check         # Verificar coding standard
composer run cs:fix           # Corrigir coding standard
composer run test             # Executar testes
```

---

## Dicas para Iniciantes

### 1. Comece pelo scaffold
Crie os arquivos básicos (info.xml, Application.php, routes.php) e verifique se o app aparece no Nextcloud antes de escrever qualquer lógica.

### 2. Construa uma feature de cada vez
Não tente implementar tudo de uma vez. Faça o Process Types funcionar end-to-end (backend + frontend) antes de passar para Stages.

### 3. Use o Deck como referência
O [Nextcloud Deck](https://github.com/nextcloud/deck) é um app de Kanban oficial. É a melhor referência de código para o Tramita.

### 4. Console do navegador é seu amigo
Use F12 para ver erros JavaScript e chamadas de API. Muitos problemas são visíveis no console.

### 5. Logs do Nextcloud
```bash
tail -f /path/to/nextcloud/data/nextcloud.log | jq .
```

### 6. Não tenha medo de errar
O framework do Nextcloud é bem documentado e a comunidade é ativa. Erros fazem parte do aprendizado.

---

## Links Úteis

- [Nextcloud Developer Manual](https://docs.nextcloud.com/server/latest/developer_manual/)
- [App Development Tutorial](https://docs.nextcloud.com/server/latest/developer_manual/app_development/index.html)
- [Nextcloud Deck (referência)](https://github.com/nextcloud/deck)
- [@nextcloud/vue Components](https://nextcloud-vue-components.netlify.app/)
- [Nextcloud Community Forum](https://help.nextcloud.com/c/dev/11)
- [Nextcloud GitHub](https://github.com/nextcloud)
