# Tramita - Schema do Banco de Dados

## Convenções

- **Prefixo de tabelas**: `tramita_` (Nextcloud adiciona `oc_` automaticamente → `oc_tramita_*`)
- **Nomes de colunas**: snake_case
- **Tipos**: OCP\DB\Types (BIGINT, STRING, TEXT, BOOLEAN, JSON, DATETIME_IMMUTABLE, etc.)
- **Foreign Keys**: Não são usadas no banco (convenção Nextcloud). Cascade deletes são feitos no PHP.
- **Soft delete**: `deleted_at` DATETIME NULL (NULL = ativo, preenchido = deletado)
- **Timestamps**: `created_at` e `updated_at` em todas as tabelas principais

---

## Diagrama de Relacionamentos

```
                        tramita_licenses (standalone)
                        tramita_notif_prefs (standalone, per-user)

 tramita_proc_types ─── 1:N ──> tramita_stages
        │                           │
        │                           └── 1:N ──> tramita_form_templates ── 1:N ──> tramita_form_fields
        │                                                                              │
        │                                                                              │
        ├── 1:N ──> tramita_protocols ──────────────────────────────┐                   │
        │                                                          │                   │
        ├── 1:N ──> tramita_labels ── 1:N ──> tramita_request_labels ──┐               │
        │                                                              │               │
        └── 1:N ──> tramita_requests ◄─────────────────────────────────┘               │
                        │                                                              │
                        ├── 1:N ──> tramita_form_responses ◄───────────────────────────┘
                        ├── 1:N ──> tramita_assignments
                        ├── 1:N ──> tramita_comments (self-ref via parent_id)
                        ├── 1:N ──> tramita_activity_log
                        ├── 1:N ──> tramita_stage_transitions
                        └── 1:N ──> tramita_notifications
```

---

## Tabelas Detalhadas

### 1. tramita_licenses — Gestão de Licença

```sql
-- Armazena a licença do app e status de validação
tramita_licenses (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    license_key     VARCHAR(512) NOT NULL,
    instance_id     VARCHAR(256) NOT NULL,           -- ID da instância Nextcloud
    status          VARCHAR(32) NOT NULL DEFAULT 'trial', -- active, expired, invalid, trial
    licensed_to     VARCHAR(256) NULL,                -- Nome da organização
    valid_until     DATETIME NULL,                    -- Data de expiração
    max_users       INTEGER NOT NULL DEFAULT 0,       -- 0 = ilimitado
    features        JSON NULL,                        -- Feature flags habilitadas
    last_check      DATETIME NULL,                    -- Última verificação remota
    created_at      DATETIME NOT NULL,
    updated_at      DATETIME NOT NULL,

    UNIQUE INDEX tramita_lic_instkey_uniq (instance_id, license_key),
    INDEX tramita_lic_status_idx (status)
)
```

### 2. tramita_proc_types — Tipos de Processo

```sql
-- Define tipos como "Memorando", "Ofício", "Processo Administrativo"
tramita_proc_types (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(256) NOT NULL,            -- "Memorando", "Ofício", etc.
    slug            VARCHAR(128) NOT NULL,            -- URL-friendly: "memorando"
    description     TEXT NULL,
    prefix          VARCHAR(16) NOT NULL,             -- Prefixo do protocolo: "MEM", "OFC"
    color           VARCHAR(7) NULL,                  -- Cor hex: "#FF5733"
    icon            VARCHAR(128) NULL,                -- Ícone
    group_id        VARCHAR(256) NOT NULL,            -- Grupo Nextcloud (departamento)
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order      INTEGER NOT NULL DEFAULT 0,
    settings        JSON NULL,                        -- Config extra (SLA, auto-assign)
    created_by      VARCHAR(64) NOT NULL,             -- User ID Nextcloud
    created_at      DATETIME NOT NULL,
    updated_at      DATETIME NOT NULL,
    deleted_at      DATETIME NULL,                    -- Soft delete

    UNIQUE INDEX tramita_ptype_slug_grp_uniq (slug, group_id),
    INDEX tramita_ptype_group_idx (group_id),
    INDEX tramita_ptype_active_idx (is_active),
    INDEX tramita_ptype_deleted_idx (deleted_at)
)
```

### 3. tramita_stages — Etapas do Workflow

```sql
-- Colunas do Kanban: "Recebido" → "Em Análise" → "Aprovação" → "Concluído"
tramita_stages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    proc_type_id    BIGINT UNSIGNED NOT NULL,         -- FK → tramita_proc_types
    name            VARCHAR(256) NOT NULL,
    slug            VARCHAR(128) NOT NULL,
    description     TEXT NULL,
    color           VARCHAR(7) NULL,
    sort_order      INTEGER NOT NULL DEFAULT 0,       -- Ordem no workflow
    is_initial      BOOLEAN NOT NULL DEFAULT FALSE,   -- Etapa inicial?
    is_final        BOOLEAN NOT NULL DEFAULT FALSE,   -- Etapa terminal?
    allowed_next    JSON NULL,                        -- IDs de próximas etapas permitidas
    auto_assign     JSON NULL,                        -- Regras de auto-atribuição
    sla_hours       INTEGER NULL,                     -- SLA em horas
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      DATETIME NOT NULL,
    updated_at      DATETIME NOT NULL,
    deleted_at      DATETIME NULL,

    UNIQUE INDEX tramita_stage_slug_pt_uniq (proc_type_id, slug),
    INDEX tramita_stage_ptype_idx (proc_type_id),
    INDEX tramita_stage_order_idx (proc_type_id, sort_order),
    INDEX tramita_stage_deleted_idx (deleted_at)
)
```

### 4. tramita_form_templates — Templates de Formulário

```sql
-- Templates associados a tipos de processo e opcionalmente a etapas
tramita_form_templates (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    proc_type_id    BIGINT UNSIGNED NOT NULL,         -- FK → tramita_proc_types
    stage_id        BIGINT UNSIGNED NULL,             -- FK → tramita_stages (NULL = todas)
    name            VARCHAR(256) NOT NULL,
    description     TEXT NULL,
    version         INTEGER NOT NULL DEFAULT 1,       -- Versionamento
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    is_required     BOOLEAN NOT NULL DEFAULT FALSE,
    sort_order      INTEGER NOT NULL DEFAULT 0,
    settings        JSON NULL,                        -- Layout, lógica condicional
    created_by      VARCHAR(64) NOT NULL,
    created_at      DATETIME NOT NULL,
    updated_at      DATETIME NOT NULL,
    deleted_at      DATETIME NULL,

    INDEX tramita_ftpl_ptype_idx (proc_type_id),
    INDEX tramita_ftpl_stage_idx (stage_id),
    INDEX tramita_ftpl_deleted_idx (deleted_at)
)
```

### 5. tramita_form_fields — Campos de Formulário

```sql
-- Definição dos campos dinâmicos de cada template
tramita_form_fields (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_id     BIGINT UNSIGNED NOT NULL,         -- FK → tramita_form_templates
    name            VARCHAR(128) NOT NULL,            -- Nome interno (snake_case)
    label           VARCHAR(256) NOT NULL,            -- Label de exibição
    field_type      VARCHAR(32) NOT NULL,             -- text, number, date, select, textarea,
                                                      -- file, checkbox, radio, email, cpf,
                                                      -- cnpj, phone, currency, user_select
    placeholder     VARCHAR(256) NULL,
    help_text       TEXT NULL,
    default_value   TEXT NULL,
    is_required     BOOLEAN NOT NULL DEFAULT FALSE,
    is_readonly     BOOLEAN NOT NULL DEFAULT FALSE,
    is_hidden       BOOLEAN NOT NULL DEFAULT FALSE,
    validation      JSON NULL,                        -- {min, max, regex, message}
    options         JSON NULL,                        -- [{value: "a", label: "Opção A"}, ...]
    sort_order      INTEGER NOT NULL DEFAULT 0,
    width           VARCHAR(16) NULL DEFAULT 'full',  -- full, half, third
    conditional     JSON NULL,                        -- Condições de visibilidade
    created_at      DATETIME NOT NULL,
    updated_at      DATETIME NOT NULL,
    deleted_at      DATETIME NULL,

    UNIQUE INDEX tramita_ffield_name_tpl_uniq (template_id, name),
    INDEX tramita_ffield_tpl_idx (template_id),
    INDEX tramita_ffield_order_idx (template_id, sort_order),
    INDEX tramita_ffield_deleted_idx (deleted_at)
)
```

### 6. tramita_protocols — Registro de Protocolos

```sql
-- Numeração sequencial: MEM-2026/000001
tramita_protocols (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    year            INTEGER NOT NULL,                 -- 2026
    sequence        BIGINT NOT NULL,                  -- Número sequencial no ano
    prefix          VARCHAR(16) NOT NULL,             -- "MEM", "OFC"
    full_number     VARCHAR(64) NOT NULL,             -- "MEM-2026/000001"
    proc_type_id    BIGINT UNSIGNED NOT NULL,         -- FK → tramita_proc_types
    request_id      BIGINT UNSIGNED NULL,             -- FK → tramita_requests
    group_id        VARCHAR(256) NOT NULL,            -- Departamento emissor
    created_at      DATETIME NOT NULL,

    UNIQUE INDEX tramita_proto_full_uniq (full_number),
    UNIQUE INDEX tramita_proto_seq_uniq (year, prefix, sequence, group_id),
    INDEX tramita_proto_year_idx (year, prefix),
    INDEX tramita_proto_req_idx (request_id),
    INDEX tramita_proto_ptype_idx (proc_type_id)
)
```

### 7. tramita_requests — Requisições (Cards do Kanban)

```sql
-- Instâncias de processo — os "cards" do kanban
tramita_requests (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    protocol_id     BIGINT UNSIGNED NULL,             -- FK → tramita_protocols
    proc_type_id    BIGINT UNSIGNED NOT NULL,         -- FK → tramita_proc_types
    current_stage_id BIGINT UNSIGNED NOT NULL,        -- FK → tramita_stages
    title           VARCHAR(512) NOT NULL,
    description     TEXT NULL,
    priority        SMALLINT NOT NULL DEFAULT 2,      -- 1=urgente, 2=normal, 3=baixa
    status          VARCHAR(32) NOT NULL DEFAULT 'open', -- open, in_progress, paused,
                                                         -- completed, cancelled, archived
    due_date        DATETIME NULL,                    -- Prazo
    completed_at    DATETIME NULL,
    requester_id    VARCHAR(64) NOT NULL,             -- User ID Nextcloud
    requester_name  VARCHAR(256) NULL,                -- Denormalizado para exibição
    group_id        VARCHAR(256) NOT NULL,            -- Departamento
    sort_order      INTEGER NOT NULL DEFAULT 0,       -- Ordem dentro da coluna kanban
    metadata        JSON NULL,                        -- Dados extensíveis
    is_confidential BOOLEAN NOT NULL DEFAULT FALSE,
    created_at      DATETIME NOT NULL,
    updated_at      DATETIME NOT NULL,
    deleted_at      DATETIME NULL,

    INDEX tramita_req_ptype_idx (proc_type_id),
    INDEX tramita_req_stage_idx (current_stage_id),
    INDEX tramita_req_proto_idx (protocol_id),
    INDEX tramita_req_status_idx (status),
    INDEX tramita_req_requester_idx (requester_id),
    INDEX tramita_req_group_idx (group_id),
    INDEX tramita_req_priority_idx (priority, status),
    INDEX tramita_req_due_idx (due_date),
    INDEX tramita_req_deleted_idx (deleted_at),
    INDEX tramita_req_sort_idx (current_stage_id, sort_order),
    INDEX tramita_req_created_idx (created_at)
)
```

### 8. tramita_form_responses — Valores dos Campos (EAV)

```sql
-- Armazena respostas dos formulários dinâmicos
-- Padrão EAV (Entity-Attribute-Value) com colunas tipadas
tramita_form_responses (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id      BIGINT UNSIGNED NOT NULL,         -- FK → tramita_requests
    template_id     BIGINT UNSIGNED NOT NULL,         -- FK → tramita_form_templates
    field_id        BIGINT UNSIGNED NOT NULL,         -- FK → tramita_form_fields
    value_text      TEXT NULL,                        -- Valores texto/string
    value_int       BIGINT NULL,                      -- Valores inteiro/boolean
    value_decimal   DECIMAL(15,4) NULL,               -- Valores monetários/decimais
    value_date      DATETIME NULL,                    -- Valores data/datetime
    value_json      JSON NULL,                        -- Valores complexos (multi-select, files)
    submitted_by    VARCHAR(64) NOT NULL,
    created_at      DATETIME NOT NULL,
    updated_at      DATETIME NOT NULL,

    UNIQUE INDEX tramita_fresp_rft_uniq (request_id, template_id, field_id),
    INDEX tramita_fresp_req_idx (request_id),
    INDEX tramita_fresp_tpl_idx (template_id),
    INDEX tramita_fresp_field_idx (field_id)
)
```

### 9. tramita_labels — Labels/Etiquetas

```sql
tramita_labels (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(128) NOT NULL,
    color           VARCHAR(7) NOT NULL DEFAULT '#808080',
    group_id        VARCHAR(256) NOT NULL,            -- Escopo: departamento
    proc_type_id    BIGINT UNSIGNED NULL,             -- NULL = disponível para todos os tipos
    sort_order      INTEGER NOT NULL DEFAULT 0,
    created_by      VARCHAR(64) NOT NULL,
    created_at      DATETIME NOT NULL,
    updated_at      DATETIME NOT NULL,
    deleted_at      DATETIME NULL,

    INDEX tramita_label_group_idx (group_id),
    INDEX tramita_label_ptype_idx (proc_type_id),
    INDEX tramita_label_deleted_idx (deleted_at)
)
```

### 10. tramita_request_labels — Junction Request ↔ Label

```sql
tramita_request_labels (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id      BIGINT UNSIGNED NOT NULL,
    label_id        BIGINT UNSIGNED NOT NULL,
    created_at      DATETIME NOT NULL,

    UNIQUE INDEX tramita_rlabel_req_lbl_uniq (request_id, label_id),
    INDEX tramita_rlabel_req_idx (request_id),
    INDEX tramita_rlabel_lbl_idx (label_id)
)
```

### 11. tramita_assignments — Atribuições de Pessoas

```sql
tramita_assignments (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id      BIGINT UNSIGNED NOT NULL,
    user_id         VARCHAR(64) NOT NULL,             -- User ID Nextcloud
    role            VARCHAR(32) NOT NULL DEFAULT 'assigned', -- assigned, reviewer, approver
    assigned_by     VARCHAR(64) NOT NULL,
    assigned_at     DATETIME NOT NULL,
    unassigned_at   DATETIME NULL,                    -- Soft-unassign (manter histórico)
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,

    UNIQUE INDEX tramita_assign_req_usr_uniq (request_id, user_id, role),
    INDEX tramita_assign_req_idx (request_id),
    INDEX tramita_assign_user_idx (user_id),
    INDEX tramita_assign_active_idx (is_active)
)
```

### 12. tramita_comments — Comentários

```sql
tramita_comments (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id      BIGINT UNSIGNED NOT NULL,
    parent_id       BIGINT UNSIGNED NULL,             -- Self-ref para threads
    user_id         VARCHAR(64) NOT NULL,
    content         TEXT NOT NULL,
    is_system       BOOLEAN NOT NULL DEFAULT FALSE,   -- Mensagem de sistema?
    mentions        JSON NULL,                        -- Array de user_ids mencionados
    created_at      DATETIME NOT NULL,
    updated_at      DATETIME NOT NULL,
    deleted_at      DATETIME NULL,

    INDEX tramita_comment_req_idx (request_id),
    INDEX tramita_comment_user_idx (user_id),
    INDEX tramita_comment_parent_idx (parent_id),
    INDEX tramita_comment_created_idx (created_at),
    INDEX tramita_comment_deleted_idx (deleted_at)
)
```

### 13. tramita_activity_log — Audit Trail

```sql
-- Registro IMUTÁVEL de todas as ações (append-only)
tramita_activity_log (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id      BIGINT UNSIGNED NULL,             -- NULL para eventos de sistema
    user_id         VARCHAR(64) NOT NULL,
    action          VARCHAR(64) NOT NULL,             -- created, stage_changed, assigned,
                                                      -- comment_added, field_updated,
                                                      -- label_added, label_removed,
                                                      -- priority_changed, status_changed,
                                                      -- deleted, restored
    entity_type     VARCHAR(64) NOT NULL,             -- request, comment, assignment, label
    entity_id       BIGINT UNSIGNED NULL,
    old_value       TEXT NULL,                        -- Valor anterior (JSON para complexo)
    new_value       TEXT NULL,                        -- Novo valor
    details         JSON NULL,                        -- Contexto adicional
    ip_address      VARCHAR(45) NULL,                 -- IPv4/IPv6
    created_at      DATETIME NOT NULL,

    INDEX tramita_actlog_req_idx (request_id),
    INDEX tramita_actlog_user_idx (user_id),
    INDEX tramita_actlog_action_idx (action),
    INDEX tramita_actlog_entity_idx (entity_type, entity_id),
    INDEX tramita_actlog_created_idx (created_at),
    INDEX tramita_actlog_req_created_idx (request_id, created_at)
)
```

### 14. tramita_stage_transitions — Transições de Etapa

```sql
-- Histórico de movimentação entre etapas (separado do activity_log para performance)
tramita_stage_transitions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id      BIGINT UNSIGNED NOT NULL,
    from_stage_id   BIGINT UNSIGNED NULL,             -- NULL para criação (etapa inicial)
    to_stage_id     BIGINT UNSIGNED NOT NULL,
    user_id         VARCHAR(64) NOT NULL,
    comment         TEXT NULL,                        -- Motivo da transição
    duration_secs   BIGINT NULL,                      -- Tempo na etapa anterior
    created_at      DATETIME NOT NULL,

    INDEX tramita_strans_req_idx (request_id),
    INDEX tramita_strans_from_idx (from_stage_id),
    INDEX tramita_strans_to_idx (to_stage_id),
    INDEX tramita_strans_user_idx (user_id),
    INDEX tramita_strans_created_idx (created_at)
)
```

### 15. tramita_notifications — Histórico de Notificações

```sql
tramita_notifications (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         VARCHAR(64) NOT NULL,
    request_id      BIGINT UNSIGNED NULL,
    type            VARCHAR(64) NOT NULL,             -- stage_change, assignment, comment,
                                                      -- mention, due_date, sla_warning
    title           VARCHAR(512) NOT NULL,
    message         TEXT NULL,
    link            VARCHAR(1024) NULL,               -- Deep link
    is_read         BOOLEAN NOT NULL DEFAULT FALSE,
    read_at         DATETIME NULL,
    created_at      DATETIME NOT NULL,

    INDEX tramita_notif_user_idx (user_id),
    INDEX tramita_notif_req_idx (request_id),
    INDEX tramita_notif_read_idx (user_id, is_read),
    INDEX tramita_notif_type_idx (type),
    INDEX tramita_notif_created_idx (created_at)
)
```

### 16. tramita_notif_prefs — Preferências de Notificação

```sql
tramita_notif_prefs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         VARCHAR(64) NOT NULL,
    event_type      VARCHAR(64) NOT NULL,             -- Mesmo tipo de tramita_notifications
    channel         VARCHAR(32) NOT NULL DEFAULT 'app', -- app, email, both, none
    is_enabled      BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      DATETIME NOT NULL,
    updated_at      DATETIME NOT NULL,

    UNIQUE INDEX tramita_npref_user_evt_uniq (user_id, event_type),
    INDEX tramita_npref_user_idx (user_id)
)
```

---

## Resumo das Tabelas

| # | Tabela | Propósito | Volume Estimado |
|---|--------|-----------|----------------|
| 1 | tramita_licenses | Licença do app | 1-5 |
| 2 | tramita_proc_types | Tipos de processo | 10-50 |
| 3 | tramita_stages | Etapas do workflow | 30-200 |
| 4 | tramita_form_templates | Templates de formulário | 20-100 |
| 5 | tramita_form_fields | Campos de formulário | 100-1.000 |
| 6 | tramita_protocols | Protocolos | 10K-1M+ |
| 7 | tramita_requests | Requisições (cards) | 10K-1M+ |
| 8 | tramita_form_responses | Respostas de formulário | 100K-10M+ |
| 9 | tramita_labels | Labels/etiquetas | 20-100 |
| 10 | tramita_request_labels | Junction request↔label | 10K-500K |
| 11 | tramita_assignments | Atribuições de pessoas | 10K-500K |
| 12 | tramita_comments | Comentários | 10K-1M+ |
| 13 | tramita_activity_log | Audit trail | 100K-10M+ |
| 14 | tramita_stage_transitions | Transições de etapa | 50K-5M+ |
| 15 | tramita_notifications | Notificações | 100K-5M+ |
| 16 | tramita_notif_prefs | Preferências notificação | 100-10K |

---

## Estratégia de Índices

| Query Pattern | Índice | Motivo |
|--------------|--------|--------|
| Kanban: cards por etapa | `tramita_req_sort_idx (current_stage_id, sort_order)` | Recuperação ordenada |
| Filtro por status | `tramita_req_status_idx (status)` | Dashboard |
| Tarefas do usuário | `tramita_assign_user_idx (user_id)` | "Minhas tarefas" |
| Busca por protocolo | `tramita_proto_full_uniq (full_number)` | Lookup único |
| Geração de protocolo | `tramita_proto_seq_uniq (year, prefix, sequence, group_id)` | Próximo sequencial |
| Timeline do card | `tramita_actlog_req_created_idx (request_id, created_at)` | Histórico ordenado |
| Notificações não lidas | `tramita_notif_read_idx (user_id, is_read)` | Badge counter |
| Registros soft-deleted | `*_deleted_idx (deleted_at)` | Excluir deletados |
| Cards vencidos | `tramita_req_due_idx (due_date)` | Monitoramento SLA |

---

## Migrações

As migrações são divididas em 3 arquivos para manutenibilidade:

| Migração | Tabelas | Arquivo |
|----------|---------|---------|
| Migration 1 | licenses, proc_types, stages, form_templates, form_fields | `Version0100Date20260214120000.php` |
| Migration 2 | protocols, requests, form_responses | `Version0100Date20260214120100.php` |
| Migration 3 | labels, request_labels, assignments, comments, activity_log, stage_transitions, notifications, notif_prefs | `Version0100Date20260214120200.php` |
