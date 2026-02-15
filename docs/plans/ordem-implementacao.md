# Tramita - Ordem de Implementação (MVP)

## Visão Geral

A implementação segue uma ordem que permite validar o full stack (backend + frontend) desde cedo e construir incrementalmente sobre funcionalidades já testadas.

**Tempo estimado não fornecido** — depende da experiência e disponibilidade do desenvolvedor.

---

## Etapa 1: Scaffold do App

**Objetivo**: Fazer o Nextcloud reconhecer e habilitar o app.

### Arquivos a criar:
```
tramita/
├── appinfo/info.xml
├── appinfo/routes.php
├── lib/AppInfo/Application.php
├── lib/Controller/PageController.php
├── templates/index.php
├── src/main.js
├── src/App.vue
├── package.json
├── webpack.js
├── composer.json
├── Makefile
├── .gitignore
├── LICENSE
└── img/app.svg
```

### Verificação:
- [ ] `php occ app:enable tramita` funciona sem erros
- [ ] O app aparece no menu de navegação do Nextcloud
- [ ] Clicar no app mostra uma página em branco com "Tramita" (ou "Hello World")
- [ ] `npm run dev` compila sem erros
- [ ] Console do navegador sem erros JavaScript

---

## Etapa 2: Migrações de Banco de Dados

**Objetivo**: Criar todas as 16 tabelas do MVP.

### Arquivos a criar:
```
lib/Migration/
├── Version0100Date20260214120000.php  # Core: licenses, proc_types, stages, form_templates, form_fields
├── Version0100Date20260214120100.php  # Request: protocols, requests, form_responses
└── Version0100Date20260214120200.php  # Relations: labels, request_labels, assignments, comments,
                                       #           activity_log, stage_transitions, notifications, notif_prefs
```

### Verificação:
- [ ] `php occ migrations:status tramita` mostra 3 migrações pendentes
- [ ] `php occ migrations:migrate tramita` executa sem erros
- [ ] Todas as 16 tabelas existem no banco (`oc_tramita_*`)
- [ ] Índices criados corretamente

---

## Etapa 3: Entities e Mappers

**Objetivo**: Criar as classes de acesso a dados para todas as tabelas.

### Arquivos a criar:
```
lib/Db/
├── ProcessType.php + ProcessTypeMapper.php
├── Stage.php + StageMapper.php
├── FormTemplate.php + FormTemplateMapper.php
├── FormField.php + FormFieldMapper.php
├── Request.php + RequestMapper.php
├── FormResponse.php + FormResponseMapper.php
├── Protocol.php + ProtocolMapper.php
├── Label.php + LabelMapper.php
├── RequestLabel.php + RequestLabelMapper.php
├── Assignment.php + AssignmentMapper.php
├── Comment.php + CommentMapper.php
├── ActivityLog.php + ActivityLogMapper.php
└── StageTransition.php + StageTransitionMapper.php
```

### Verificação:
- [ ] Cada Entity tem propriedades declaradas e `addType()` no construtor
- [ ] Cada Mapper estende QBMapper com tabela e Entity corretos
- [ ] Métodos básicos (find, findAll, findBy*) implementados
- [ ] `composer run lint` passa sem erros

---

## Etapa 4: Process Types (Full Stack)

**Objetivo**: Primeira feature completa — validar todo o fluxo backend → frontend.

### Backend:
```
lib/Service/ProcessTypeService.php
lib/Controller/ProcessTypeController.php
appinfo/routes.php (adicionar rotas de process-types)
```

### Frontend:
```
src/services/api.js
src/services/processTypeApi.js
src/store/processType.js
src/views/ProcessTypeList.vue
src/router.js
```

### Verificação:
- [ ] GET `/api/v1/process-types` retorna lista vazia (200)
- [ ] POST `/api/v1/process-types` cria um tipo e retorna (201)
- [ ] A página de Process Types lista os tipos criados
- [ ] Editar e deletar funcionam
- [ ] Dados persistem no banco

---

## Etapa 5: Stages (Etapas)

**Objetivo**: CRUD de etapas + reordenação.

### Backend:
```
lib/Service/StageService.php
lib/Controller/StageController.php
appinfo/routes.php (adicionar rotas de stages)
```

### Frontend:
```
src/services/stageApi.js
src/store/stage.js
src/views/StageManager.vue
```

### Verificação:
- [ ] Criar etapas associadas a um tipo de processo
- [ ] Reordenar etapas (drag-and-drop ou botões up/down)
- [ ] Marcar etapa como inicial/final
- [ ] Deletar etapa

---

## Etapa 6: Form Templates e Fields

**Objetivo**: Formulários dinâmicos configuráveis.

### Backend:
```
lib/Service/FormTemplateService.php
lib/Controller/FormTemplateController.php
lib/Controller/FormFieldController.php
appinfo/routes.php (adicionar rotas)
```

### Frontend:
```
src/services/formTemplateApi.js
src/store/formTemplate.js
src/views/FormTemplateEditor.vue
src/components/forms/FieldEditor.vue
src/components/forms/DynamicForm.vue
src/components/forms/FieldRenderer.vue
```

### Verificação:
- [ ] Admin consegue criar um template de formulário
- [ ] Admin consegue adicionar campos (text, number, date, select, CPF, etc.)
- [ ] Admin consegue reordenar campos
- [ ] Formulário renderiza corretamente no preview
- [ ] Validações funcionam (campo obrigatório, regex, etc.)

---

## Etapa 7: Requests + Protocolo

**Objetivo**: Criar requisições com protocolo automático.

### Backend:
```
lib/Service/RequestService.php
lib/Service/ProtocolService.php
lib/Controller/RequestController.php
appinfo/routes.php (adicionar rotas)
```

### Frontend:
```
src/services/requestApi.js
src/store/request.js
src/views/RequestForm.vue
src/views/RequestDetail.vue
src/components/common/ProtocolBadge.vue
```

### Verificação:
- [ ] Criar uma requisição gera protocolo automático (ex: "MEM-2026/000001")
- [ ] Formulário dinâmico é renderizado e preenchido
- [ ] Dados do formulário são salvos no banco
- [ ] Detalhes da requisição mostram todos os campos
- [ ] Busca por protocolo funciona
- [ ] Protocolos são sequenciais e únicos

---

## Etapa 8: Kanban Board

**Objetivo**: O coração visual do app — board kanban com drag-and-drop.

### Frontend:
```
src/views/KanbanBoard.vue
src/components/kanban/KanbanColumn.vue
src/components/kanban/KanbanCard.vue
src/components/kanban/KanbanCardModal.vue
```

### Backend:
```
appinfo/routes.php (rotas de move e reorder)
lib/Controller/RequestController.php (métodos move, reorder)
lib/Service/RequestService.php (lógica de transição de etapa)
```

### Verificação:
- [ ] Board mostra colunas por etapa
- [ ] Cards aparecem na coluna correta
- [ ] Drag-and-drop move card entre etapas
- [ ] Reordenação dentro da coluna funciona
- [ ] Card mostra: protocolo, título, prioridade, prazo, avatares

---

## Etapa 9: Labels

**Objetivo**: Etiquetas coloridas para categorizar requisições.

### Backend:
```
lib/Service/LabelService.php
lib/Controller/LabelController.php
appinfo/routes.php (rotas de labels)
```

### Frontend:
```
src/services/labelApi.js
src/store/label.js
src/components/common/LabelSelector.vue
```

### Verificação:
- [ ] Criar labels com nome e cor
- [ ] Atribuir labels a requisições
- [ ] Labels aparecem nos cards do kanban
- [ ] Remover labels funciona

---

## Etapa 10: Assignments (Atribuições)

**Objetivo**: Atribuir pessoas a requisições.

### Backend:
```
lib/Controller/CardController.php (métodos assign/unassign)
```

### Frontend:
```
src/components/common/AssigneeSelector.vue
src/components/common/DeadlinePicker.vue
```

### Verificação:
- [ ] Selecionar usuários Nextcloud e atribuir a um card
- [ ] Avatares dos atribuídos aparecem no card do kanban
- [ ] Definir prazo funciona
- [ ] Remover atribuição funciona

---

## Etapa 11: Comments

**Objetivo**: Comentários em cards com suporte a threads.

### Backend:
```
lib/Controller/CommentController.php (ou endpoint dentro de RequestController)
```

### Frontend:
```
src/components/common/CommentSection.vue
```

### Verificação:
- [ ] Adicionar comentários em um card
- [ ] Responder a comentários (threads)
- [ ] Comentários aparecem ordenados por data
- [ ] Menções (@usuario) são reconhecidas

---

## Etapa 12: Notificações

**Objetivo**: Notificações via sistema nativo do Nextcloud.

### Backend:
```
lib/Notification/Notifier.php
lib/Notification/NotificationHelper.php
lib/Service/NotificationService.php
lib/AppInfo/Application.php (registrar Notifier)
```

### Verificação:
- [ ] Atribuição gera notificação para o atribuído
- [ ] Mudança de etapa gera notificação
- [ ] Comentário/menção gera notificação
- [ ] Notificações aparecem no sino do Nextcloud
- [ ] Clicar na notificação leva ao card

---

## Etapa 13: Sistema de Licença

**Objetivo**: Middleware de validação de licença.

### Backend:
```
lib/Middleware/LicenseMiddleware.php
lib/Service/LicenseService.php
lib/Controller/ConfigController.php
lib/Settings/AdminSettings.php
lib/Settings/AdminSection.php
lib/Exception/InvalidLicenseException.php
lib/AppInfo/Application.php (registrar middleware e settings)
```

### Frontend:
```
src/views/AdminSettings.vue
src/components/common/LicenseWarning.vue
src/services/configApi.js
src/store/config.js
```

### Verificação:
- [ ] Sem licença, APIs retornam 402
- [ ] Page e Config continuam acessíveis (para inserir licença)
- [ ] Admin Settings mostra campo para chave de licença
- [ ] Inserir chave válida permite uso do app
- [ ] Frontend mostra aviso quando licença inválida
- [ ] Cache de 24h funciona (não chama servidor a cada request)

---

## Etapa 14: Activity Log / Audit Trail

**Objetivo**: Registro automático de todas as ações.

### Backend:
```
lib/Service/ActivityLogService.php
(integrar em todos os Services existentes)
```

### Verificação:
- [ ] Criação de requisição registrada
- [ ] Mudanças de etapa registradas com from/to
- [ ] Atribuições registradas
- [ ] Comentários registrados
- [ ] Histórico mostra timeline completa do card

---

## Etapa 15: Testes e Polish

**Objetivo**: Garantir qualidade e preparar para release.

### Testes:
```
tests/Unit/Service/ProcessTypeServiceTest.php
tests/Unit/Service/ProtocolServiceTest.php
tests/Unit/Db/RequestMapperTest.php
tests/Integration/Controller/ProcessTypeControllerTest.php
```

### Verificação:
- [ ] Testes unitários passam
- [ ] Testes de integração passam
- [ ] `make lint` sem erros
- [ ] `npm run build` sem erros
- [ ] App funciona em SQLite, MySQL e PostgreSQL
- [ ] App funciona em Nextcloud 28, 29, 30, 31
- [ ] CHANGELOG.md atualizado
- [ ] Screenshots para info.xml
