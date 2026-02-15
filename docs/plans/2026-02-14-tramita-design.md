# Tramita - Design Document

**Data**: 2026-02-14
**Autor**: Genius Apps
**Status**: Em planejamento

---

## Visão Geral

**Tramita** é um aplicativo Nextcloud de gestão de processos e tramitação de documentos para órgãos públicos municipais brasileiros, inspirado no [1Doc](https://1doc.com.br/prefeituras).

### Problema
Prefeituras brasileiras precisam de um sistema digital para tramitar processos administrativos (memorandos, ofícios, protocolos) com rastreabilidade, prazos, assinatura digital e transparência. Muitas ainda usam papel ou sistemas caros e proprietários.

### Solução
O Tramita traz gestão de processos para dentro do Nextcloud, aproveitando a infraestrutura já existente (sistema de arquivos, gerenciamento de usuários/grupos, notificações, editor de documentos). Isso reduz custos e simplifica a operação.

### Modelo de Negócio
- **Licença**: AGPL-3.0-or-later (código aberto, exigência da App Store do Nextcloud)
- **Monetização**: License key validation — o app é instalável por qualquer um via App Store, mas só funciona com licença válida da Genius Apps
- **Distribuição**: Nextcloud App Store + distribuição direta para clientes

---

## Fases do Projeto

| Fase | Escopo | Dependência | Prioridade |
|------|--------|-------------|------------|
| **MVP (Fase 1)** | Formulários dinâmicos + Kanban com etapas configuráveis + Protocolo automático + Labels + Atribuições de pessoas + Prazos + Notificações + Sistema de licença | — | Alta |
| **Fase 2** | Integração com documentos (criar/editar via Nextcloud Files, Collabora ou OnlyOffice) | MVP | Média |
| **Fase 3** | Assinatura digital via API GOV.BR (gratuita, validade jurídica plena) | Fase 2 | Média |
| **Fase 4** | Portal externo para cidadãos (formulários públicos sem login Nextcloud) | MVP | Média |
| **Fase 5** | Relatórios, dashboards e analytics (tempos de tramitação, SLAs, produtividade) | MVP | Baixa |

---

## Funcionalidades do MVP

### 1. Tipos de Processo (Process Types)
- CRUD completo para tipos de processo (Memorando, Ofício, Processo Administrativo, etc.)
- Cada tipo tem: nome, prefixo de protocolo, cor, ícone, departamento responsável
- Configurações extras via JSON (SLA, auto-assign, regras)
- Soft delete para manter histórico

### 2. Etapas Dinâmicas (Stages)
- Configuráveis por tipo de processo
- Representam as colunas do Kanban (ex: Recebido → Em Análise → Aprovação → Concluído)
- Reordenáveis via drag-and-drop
- Propriedades: cor, SLA em horas, etapas permitidas como próximo passo
- Flags: is_initial (primeira etapa), is_final (etapa terminal)

### 3. Formulários Dinâmicos (Form Templates + Fields)
- Templates de formulário associados a tipos de processo
- Campos configuráveis pelo admin: text, number, date, select, textarea, file, checkbox, radio, email, CPF, CNPJ, phone, currency, user_select
- Validação configurável (obrigatório, regex, min/max)
- Layout responsivo (full, half, third width)
- Campos condicionais (mostrar/esconder baseado em outros campos)
- Versionamento de templates

### 4. Requisições / Cards do Kanban (Requests)
- Instâncias de processo com protocolo automático
- Movimentação entre etapas via drag-and-drop no Kanban
- Propriedades: título, descrição, prioridade (urgente/normal/baixa), prazo, status
- Dados do formulário armazenados em padrão EAV (Entity-Attribute-Value)
- Soft delete e arquivamento

### 5. Protocolo Automático
- Formato: PREFIX-YYYY/NNNNNN (ex: "MEM-2026/000001")
- Geração atômica com transação + lock para garantir unicidade
- Sequencial por ano e por prefixo/departamento
- Busca por número de protocolo

### 6. Labels / Etiquetas
- Labels configuráveis com nome e cor
- Podem ser globais ou por tipo de processo
- Atribuição múltipla a requisições (many-to-many)

### 7. Atribuição de Pessoas (Assignments)
- Atribuir usuários Nextcloud a requisições
- Roles: assigned (responsável), reviewer (revisor), approver (aprovador)
- Histórico de atribuições (quem atribuiu, quando)

### 8. Comentários
- Comentários em cards com suporte a threads (parent_id)
- Menções de usuários (@usuario)
- Comentários de sistema (gerados automaticamente em transições)

### 9. Notificações
- Via sistema nativo de notificações do Nextcloud
- Tipos: atribuição, mudança de etapa, prazo próximo, comentário, menção
- Preferências configuráveis por usuário (app, email, ambos, nenhum)

### 10. Audit Trail / Histórico
- Registro completo de todas as ações (criação, edição, transições, atribuições, etc.)
- Tabela separada para transições de etapa (com duração em cada etapa)
- IP address do usuário para compliance
- Imutável (append-only)

### 11. Sistema de Licença
- Middleware que intercepta todas as requisições API
- Validação contra servidor remoto (license.geniusapps.com.br)
- Cache de 24h para evitar chamadas repetidas
- Grace period de 7 dias se servidor inacessível
- HTTP 402 quando licença inválida → frontend mostra aviso

---

## Decisões de Design

### Por que EAV para respostas de formulário?
Os campos são dinâmicos e configuráveis pelo admin. Não há como saber os nomes das colunas em tempo de design. O padrão EAV com colunas tipadas (text, int, decimal, date, json) evita problemas de casting entre bancos de dados diferentes.

### Por que sem Foreign Keys no banco?
Seguindo a convenção do Nextcloud (observada no Deck app e em discussões da comunidade), foreign keys não são usadas a nível de banco. Cascade deletes são implementados no código PHP (Service/Mapper). Isso garante compatibilidade total com SQLite, MySQL e PostgreSQL.

### Por que `deleted_at` em vez de `is_deleted`?
O `deleted_at` fornece tanto o flag de soft delete quanto o timestamp de quando foi deletado. Útil para audit trail e para funcionalidade futura de "lixeira" com limpeza automática.

### Por que `group_id` em múltiplas tabelas?
Suporte multi-departamento. Cada secretaria/departamento de uma prefeitura tem seus próprios tipos de processo, protocolos e labels. O `group_id` mapeia diretamente para grupos do Nextcloud.

### Por que tabela separada para stage_transitions?
Dados de transição de etapa são os mais consultados em um sistema de workflow. Separar do activity_log melhora a performance e facilita queries de SLA e analytics.

---

## Stakeholders

- **Genius Apps**: Desenvolvedor e mantenedor
- **Prefeituras municipais**: Usuários finais (servidores públicos)
- **Cidadãos**: Usuários do portal externo (Fase 4)
- **Nextcloud App Store**: Plataforma de distribuição
