# Tramita - Cuidados de Segurança

## Visão Geral

Como um sistema para órgãos públicos municipais, a segurança é crítica. Processos administrativos podem conter dados sensíveis de cidadãos (CPF, CNPJ, endereços), e o sistema deve garantir integridade, confidencialidade e rastreabilidade.

---

## OWASP Top 10 — Como o Nextcloud/Tramita se Protege

### 1. SQL Injection
**Proteção**: O QueryBuilder do Nextcloud usa **named parameters** que são automaticamente escapados.

```php
// ✅ CORRETO — Usando named parameters
$qb->select('*')
   ->from('tramita_requests')
   ->where($qb->expr()->eq('id',
       $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

// ❌ ERRADO — NUNCA faça isso
$qb->select('*')
   ->from('tramita_requests')
   ->where("id = $id");  // Vulnerável a SQL Injection!
```

**Regra**: NUNCA concatene variáveis em queries SQL. SEMPRE use `createNamedParameter()`.

### 2. Cross-Site Scripting (XSS)
**Proteção**: Vue.js escapa automaticamente todo conteúdo renderizado com `{{ }}`.

```html
<!-- ✅ SEGURO — Vue escapa automaticamente -->
<p>{{ request.title }}</p>

<!-- ❌ PERIGOSO — Só use quando absolutamente necessário -->
<p v-html="request.description"></p>
```

**Regra**: Evite `v-html`. Se precisar renderizar HTML, sanitize antes com uma biblioteca como DOMPurify.

### 3. Cross-Site Request Forgery (CSRF)
**Proteção**: O framework OCP do Nextcloud protege automaticamente com tokens CSRF em todas as requisições.

**Regra**: Use `@nextcloud/axios` em vez do axios puro. Ele inclui o token CSRF automaticamente.

```javascript
// ✅ CORRETO
import axios from '@nextcloud/axios'

// ❌ ERRADO
import axios from 'axios'
```

### 4. Broken Authentication
**Proteção**: O Nextcloud gerencia toda a autenticação. Seu app nunca precisa implementar login/logout.

**Regra**: Use `$this->userId` (injetado pelo framework) para identificar o usuário. Nunca confie em parâmetros enviados pelo cliente para identificação.

### 5. Broken Access Control
**Proteção**: Implementar no Service layer.

```php
// Verificar se o usuário tem acesso ao departamento
public function findByGroup(string $groupId, string $userId): array {
    // Verificar se o usuário pertence ao grupo
    if (!$this->groupManager->isInGroup($userId, $groupId)) {
        throw new ForbiddenException('Acesso negado');
    }
    return $this->mapper->findByGroup($groupId);
}
```

**Regra**: Sempre verifique permissões no backend, nunca confie apenas no frontend.

### 6. Security Misconfiguration
**Proteção**: Seguir as configurações padrão do Nextcloud.

**Regras**:
- Não desabilite verificações de segurança
- Não use `#[NoCSRFRequired]` sem motivo
- Não use `#[PublicPage]` sem proteção adequada
- Mantenha dependências atualizadas

### 7. Sensitive Data Exposure
**Proteção**: Cuidado com o que é retornado nas APIs.

```php
// ✅ CORRETO — jsonSerialize() controlado
public function jsonSerialize(): array {
    return [
        'id' => $this->getId(),
        'title' => $this->getTitle(),
        // NÃO inclua dados sensíveis desnecessários
    ];
}
```

**Regras**:
- Nunca retorne a chave de licença completa nas APIs
- Mascare CPF/CNPJ quando exibir em listagens (ex: `***.***.***-12`)
- Use `is_confidential` para restringir acesso a processos sensíveis
- Logs não devem conter dados pessoais

---

## Controle de Acesso

### Níveis de Acesso

| Nível | Quem | O que pode fazer |
|-------|------|------------------|
| **Admin** | Administradores Nextcloud | Tudo: criar tipos, etapas, formulários, gerenciar licença |
| **Gerente** | Responsáveis por departamento | Gerenciar processos do departamento, atribuir pessoas |
| **Usuário** | Servidores públicos | Criar requisições, comentar, mover cards atribuídos |
| **Leitor** | Membros do grupo | Visualizar processos do departamento |

### Implementação via Atributos PHP

```php
// Apenas admins (padrão, sem atributo especial)
public function createProcessType(): JSONResponse { ... }

// Qualquer usuário logado
#[NoAdminRequired]
public function listRequests(): JSONResponse { ... }

// Acesso público (sem login) — para portal externo futuro
#[PublicPage]
#[NoCSRFRequired]
public function submitExternalRequest(): JSONResponse { ... }
```

### Verificação por Grupo/Departamento

O Nextcloud tem o `OCP\IGroupManager` para verificar pertencimento a grupos:

```php
use OCP\IGroupManager;

class RequestService {
    public function __construct(
        private IGroupManager $groupManager,
    ) {}

    public function canAccessGroup(string $userId, string $groupId): bool {
        return $this->groupManager->isInGroup($userId, $groupId);
    }
}
```

---

## Proteção de Dados (LGPD)

Como o Tramita lida com dados de cidadãos brasileiros, a LGPD (Lei Geral de Proteção de Dados) se aplica.

### Dados pessoais no Tramita

| Dado | Tabela | Classificação |
|------|--------|---------------|
| CPF | tramita_form_responses | Dado pessoal sensível |
| CNPJ | tramita_form_responses | Dado pessoal |
| Nome | tramita_requests (requester_name) | Dado pessoal |
| Email | tramita_form_responses | Dado pessoal |
| Telefone | tramita_form_responses | Dado pessoal |
| Endereço | tramita_form_responses | Dado pessoal |

### Medidas de proteção

1. **Minimização**: Só colete dados necessários para o processo
2. **Armazenamento seguro**: Dados ficam no banco do Nextcloud (que deve usar criptografia em disco)
3. **Acesso controlado**: Verificação de grupo/departamento em cada consulta
4. **Audit trail**: Todas as ações são registradas em `tramita_activity_log`
5. **Soft delete**: Dados não são deletados permanentemente imediatamente
6. **Confidencialidade**: Flag `is_confidential` para restringir visualização

### Futuro (a considerar)

- Implementar criptografia de campos sensíveis (CPF, CNPJ)
- Implementar exportação de dados do cidadão (direito de acesso LGPD)
- Implementar exclusão definitiva de dados pessoais (direito ao esquecimento)
- Definir política de retenção de dados

---

## Validação de Entrada

### Backend (PHP)

Sempre valide dados no backend, mesmo que o frontend já valide:

```php
class RequestService {
    public function create(array $data, string $userId): Request {
        // Validar campos obrigatórios
        if (empty($data['title'])) {
            throw new ValidationException('Título é obrigatório');
        }

        // Validar tamanho
        if (strlen($data['title']) > 512) {
            throw new ValidationException('Título muito longo');
        }

        // Validar prioridade
        if (!in_array($data['priority'], [1, 2, 3])) {
            throw new ValidationException('Prioridade inválida');
        }

        // Sanitizar
        $data['title'] = trim($data['title']);
        $data['description'] = trim($data['description'] ?? '');

        // ... criar request
    }
}
```

### Frontend (Vue.js)

Validação no frontend é para UX (feedback rápido), não para segurança:

```javascript
// Validação de CPF (frontend — para UX)
function validateCPF(cpf) {
    cpf = cpf.replace(/\D/g, '')
    if (cpf.length !== 11) return false
    // ... algoritmo de validação
    return true
}
```

---

## Checklist de Segurança

Antes de cada release, verificar:

- [ ] Todas as queries usam QueryBuilder com named parameters
- [ ] Nenhum uso de `v-html` sem sanitização
- [ ] `@nextcloud/axios` usado em todas as chamadas HTTP
- [ ] Verificação de permissões em todos os endpoints de escrita
- [ ] Dados sensíveis mascarados em respostas de API
- [ ] Logs não contêm dados pessoais
- [ ] Dependências atualizadas (npm audit, composer audit)
- [ ] Nenhum endpoint público sem proteção adequada
- [ ] Licença key não exposta no frontend
- [ ] Testes de segurança básicos executados
