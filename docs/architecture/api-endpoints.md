# Tramita - API REST Endpoints

## Visão Geral

Todos os endpoints seguem o padrão REST e são prefixados com `/apps/tramita/api/v1/`.

A autenticação é automática via sessão do Nextcloud (cookies) ou via Basic Auth para clientes externos.

O middleware de licença intercepta todas as requisições (exceto PageController e ConfigController).

---

## Endpoints

### Page (SPA Entry Point)

| Método | Endpoint | Controller | Descrição |
|--------|----------|------------|-----------|
| GET | `/` | page#index | Serve o SPA Vue.js |
| GET | `/{path}` | page#index (catchall) | Catch-all para rotas do Vue Router |

### Process Types (Tipos de Processo)

| Método | Endpoint | Controller | Acesso | Descrição |
|--------|----------|------------|--------|-----------|
| GET | `/api/v1/process-types` | processType#index | Todos | Lista tipos de processo ativos |
| GET | `/api/v1/process-types/{id}` | processType#show | Todos | Detalhes de um tipo |
| POST | `/api/v1/process-types` | processType#create | Admin | Criar novo tipo |
| PUT | `/api/v1/process-types/{id}` | processType#update | Admin | Atualizar tipo |
| DELETE | `/api/v1/process-types/{id}` | processType#destroy | Admin | Soft delete tipo |

**Exemplo de Request (POST)**:
```json
{
    "name": "Memorando",
    "slug": "memorando",
    "prefix": "MEM",
    "description": "Comunicação interna entre setores",
    "color": "#2196F3",
    "group_id": "secretaria-admin"
}
```

**Exemplo de Response**:
```json
{
    "id": 1,
    "name": "Memorando",
    "slug": "memorando",
    "prefix": "MEM",
    "description": "Comunicação interna entre setores",
    "color": "#2196F3",
    "groupId": "secretaria-admin",
    "isActive": true,
    "sortOrder": 0,
    "createdAt": "2026-02-14T12:00:00Z",
    "updatedAt": "2026-02-14T12:00:00Z"
}
```

### Stages (Etapas do Workflow)

| Método | Endpoint | Controller | Acesso | Descrição |
|--------|----------|------------|--------|-----------|
| GET | `/api/v1/process-types/{ptId}/stages` | stage#index | Todos | Lista etapas do tipo |
| GET | `/api/v1/stages/{id}` | stage#show | Todos | Detalhes de uma etapa |
| POST | `/api/v1/process-types/{ptId}/stages` | stage#create | Admin | Criar nova etapa |
| PUT | `/api/v1/stages/{id}` | stage#update | Admin | Atualizar etapa |
| DELETE | `/api/v1/stages/{id}` | stage#destroy | Admin | Soft delete etapa |
| PUT | `/api/v1/process-types/{ptId}/stages/reorder` | stage#reorder | Admin | Reordenar etapas |

**Exemplo de Request (POST)**:
```json
{
    "name": "Em Análise",
    "slug": "em-analise",
    "color": "#FF9800",
    "sortOrder": 1,
    "isInitial": false,
    "isFinal": false,
    "slaHours": 48,
    "allowedNext": [3, 4]
}
```

**Reorder Request (PUT)**:
```json
{
    "order": [1, 3, 2, 4, 5]
}
```

### Form Templates (Templates de Formulário)

| Método | Endpoint | Controller | Acesso | Descrição |
|--------|----------|------------|--------|-----------|
| GET | `/api/v1/process-types/{ptId}/form-templates` | formTemplate#index | Todos | Lista templates |
| GET | `/api/v1/form-templates/{id}` | formTemplate#show | Todos | Detalhes com campos |
| POST | `/api/v1/process-types/{ptId}/form-templates` | formTemplate#create | Admin | Criar template |
| PUT | `/api/v1/form-templates/{id}` | formTemplate#update | Admin | Atualizar template |
| DELETE | `/api/v1/form-templates/{id}` | formTemplate#destroy | Admin | Soft delete |

### Form Fields (Campos de Formulário)

| Método | Endpoint | Controller | Acesso | Descrição |
|--------|----------|------------|--------|-----------|
| GET | `/api/v1/form-templates/{ftId}/fields` | formField#index | Todos | Lista campos |
| POST | `/api/v1/form-templates/{ftId}/fields` | formField#create | Admin | Criar campo |
| PUT | `/api/v1/form-fields/{id}` | formField#update | Admin | Atualizar campo |
| DELETE | `/api/v1/form-fields/{id}` | formField#destroy | Admin | Deletar campo |
| PUT | `/api/v1/form-templates/{ftId}/fields/reorder` | formField#reorder | Admin | Reordenar |

**Exemplo de Request (POST)**:
```json
{
    "name": "cpf_requerente",
    "label": "CPF do Requerente",
    "fieldType": "cpf",
    "placeholder": "000.000.000-00",
    "isRequired": true,
    "sortOrder": 2,
    "width": "half",
    "validation": {
        "regex": "^\\d{3}\\.\\d{3}\\.\\d{3}-\\d{2}$",
        "message": "CPF inválido"
    }
}
```

### Requests (Requisições / Cards do Kanban)

| Método | Endpoint | Controller | Acesso | Descrição |
|--------|----------|------------|--------|-----------|
| GET | `/api/v1/process-types/{ptId}/requests` | request#index | Todos | Lista requisições (com filtros) |
| GET | `/api/v1/requests/{id}` | request#show | Todos | Detalhes completos |
| POST | `/api/v1/process-types/{ptId}/requests` | request#create | Todos | Criar requisição |
| PUT | `/api/v1/requests/{id}` | request#update | Responsável/Admin | Atualizar |
| DELETE | `/api/v1/requests/{id}` | request#destroy | Admin | Soft delete |
| PUT | `/api/v1/requests/{id}/move` | request#move | Responsável/Admin | Mover para outra etapa |
| GET | `/api/v1/requests/search` | request#search | Todos | Buscar (protocolo, título, etc.) |
| GET | `/api/v1/requests/protocol/{protocolNumber}` | request#byProtocol | Todos | Buscar por protocolo |
| GET | `/api/v1/requests/{id}/history` | request#history | Todos | Audit trail do request |

**Exemplo de Request (POST)**:
```json
{
    "title": "Solicitação de Alvará Comercial",
    "description": "Pedido de emissão de alvará para comércio varejista",
    "priority": 2,
    "dueDate": "2026-03-14T23:59:59Z",
    "fields": {
        "nome_empresa": "Loja ABC",
        "cnpj": "12.345.678/0001-99",
        "endereco": "Rua Principal, 100",
        "tipo_atividade": "comercio_varejista"
    }
}
```

**Exemplo de Response**:
```json
{
    "id": 42,
    "protocolNumber": "ALV-2026/000042",
    "processTypeId": 3,
    "currentStageId": 1,
    "title": "Solicitação de Alvará Comercial",
    "priority": 2,
    "status": "open",
    "dueDate": "2026-03-14T23:59:59Z",
    "requesterId": "admin",
    "requesterName": "Administrador",
    "groupId": "secretaria-financas",
    "createdAt": "2026-02-14T12:30:00Z",
    "labels": [],
    "assignments": [],
    "fieldValues": { ... }
}
```

**Move Request (PUT)**:
```json
{
    "stageId": 3,
    "comment": "Análise concluída, encaminhando para aprovação"
}
```

**Search Query Parameters (GET)**:
```
/api/v1/requests/search?q=alvara&status=open&priority=1&from=2026-01-01&to=2026-12-31&page=1&limit=20
```

### Card Operations (Operações de Card)

| Método | Endpoint | Controller | Acesso | Descrição |
|--------|----------|------------|--------|-----------|
| POST | `/api/v1/requests/{id}/assign` | card#assign | Responsável/Admin | Atribuir pessoa |
| DELETE | `/api/v1/requests/{id}/assign/{userId}` | card#unassign | Responsável/Admin | Remover atribuição |
| POST | `/api/v1/requests/{id}/labels` | card#addLabel | Responsável/Admin | Adicionar label |
| DELETE | `/api/v1/requests/{id}/labels/{labelId}` | card#removeLabel | Responsável/Admin | Remover label |
| PUT | `/api/v1/requests/{id}/deadline` | card#setDeadline | Responsável/Admin | Definir prazo |
| PUT | `/api/v1/requests/{id}/reorder` | card#reorder | Todos | Reordenar no kanban |

**Assign Request (POST)**:
```json
{
    "userId": "joao.silva",
    "role": "assigned"
}
```

### Labels (Etiquetas)

| Método | Endpoint | Controller | Acesso | Descrição |
|--------|----------|------------|--------|-----------|
| GET | `/api/v1/labels` | label#index | Todos | Lista labels |
| POST | `/api/v1/labels` | label#create | Admin | Criar label |
| PUT | `/api/v1/labels/{id}` | label#update | Admin | Atualizar label |
| DELETE | `/api/v1/labels/{id}` | label#destroy | Admin | Deletar label |

### Config / License (Configuração)

| Método | Endpoint | Controller | Acesso | Descrição |
|--------|----------|------------|--------|-----------|
| GET | `/api/v1/config` | config#get | Admin | Obter configuração |
| PUT | `/api/v1/config/license` | config#setLicense | Admin | Ativar/atualizar licença |
| PUT | `/api/v1/config/{key}` | config#update | Admin | Atualizar configuração |

**Set License (PUT)**:
```json
{
    "licenseKey": "GA-TRAMITA-XXXX-XXXX-XXXX-XXXX"
}
```

**Response**:
```json
{
    "valid": true,
    "licensedTo": "Prefeitura de Exemplo",
    "validUntil": "2027-02-14",
    "plan": "enterprise",
    "features": ["kanban", "forms", "protocol", "notifications"]
}
```

---

## Códigos de Resposta

| Código | Significado |
|--------|-------------|
| 200 | Sucesso |
| 201 | Criado com sucesso |
| 204 | Deletado com sucesso |
| 400 | Erro de validação (dados inválidos) |
| 401 | Não autenticado |
| 402 | Licença inválida ou expirada |
| 403 | Sem permissão |
| 404 | Recurso não encontrado |
| 409 | Conflito (ex: slug duplicado) |
| 500 | Erro interno do servidor |

---

## Filtros e Paginação

Endpoints de listagem suportam query parameters:

```
?page=1&limit=20               # Paginação
?status=open                    # Filtro por status
?priority=1                     # Filtro por prioridade
?assignee=joao.silva            # Filtro por responsável
?label=urgente                  # Filtro por label
?from=2026-01-01&to=2026-12-31 # Filtro por data
?sort=created_at&order=desc     # Ordenação
```
