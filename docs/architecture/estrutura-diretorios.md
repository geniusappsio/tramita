# Tramita - Estrutura de Diretórios

## Estrutura Completa

```
tramita/
│
├── appinfo/                               # CONFIGURAÇÃO DO APP (obrigatório)
│   ├── info.xml                           # Metadados: nome, versão, dependências, autor
│   └── routes.php                         # Definição de todas as rotas REST API
│
├── lib/                                   # BACKEND PHP (namespace: OCA\Tramita)
│   │
│   ├── AppInfo/
│   │   └── Application.php                # Bootstrap do app (IBootstrap)
│   │                                      # Registra DI, middleware, notificações
│   │
│   ├── Controller/                        # Camada HTTP (recebe requests, retorna responses)
│   │   ├── PageController.php             # Serve o index.php (SPA Vue)
│   │   ├── ProcessTypeController.php      # CRUD tipos de processo
│   │   ├── StageController.php            # CRUD etapas do workflow
│   │   ├── FormTemplateController.php     # CRUD templates de formulário
│   │   ├── FormFieldController.php        # CRUD campos de formulário
│   │   ├── RequestController.php          # CRUD requisições (cards do kanban)
│   │   ├── CardController.php             # Operações de card (assign, label, deadline)
│   │   ├── LabelController.php            # CRUD labels/etiquetas
│   │   └── ConfigController.php           # Settings admin + ativação de licença
│   │
│   ├── Service/                           # Camada de LÓGICA DE NEGÓCIO
│   │   ├── ProcessTypeService.php         # Regras de negócio para tipos de processo
│   │   ├── StageService.php               # Regras para etapas (validar transições)
│   │   ├── FormTemplateService.php        # Regras para templates de formulário
│   │   ├── RequestService.php             # Regras para requisições (criar, mover, etc.)
│   │   ├── ProtocolService.php            # Geração atômica de protocolo (transação + lock)
│   │   ├── LabelService.php               # Regras para labels
│   │   ├── LicenseService.php             # Validação de licença contra servidor remoto
│   │   └── NotificationService.php        # Orquestração de notificações
│   │
│   ├── Db/                                # Camada de ACESSO A DADOS (ORM)
│   │   ├── ProcessType.php                # Entity (define campos/tipos)
│   │   ├── ProcessTypeMapper.php          # Mapper (queries ao banco)
│   │   ├── Stage.php
│   │   ├── StageMapper.php
│   │   ├── FormTemplate.php
│   │   ├── FormTemplateMapper.php
│   │   ├── FormField.php
│   │   ├── FormFieldMapper.php
│   │   ├── Request.php
│   │   ├── RequestMapper.php
│   │   ├── FormResponse.php
│   │   ├── FormResponseMapper.php
│   │   ├── Protocol.php
│   │   ├── ProtocolMapper.php
│   │   ├── Label.php
│   │   ├── LabelMapper.php
│   │   ├── RequestLabel.php
│   │   ├── RequestLabelMapper.php
│   │   ├── Assignment.php
│   │   ├── AssignmentMapper.php
│   │   ├── Comment.php
│   │   ├── CommentMapper.php
│   │   ├── ActivityLog.php
│   │   ├── ActivityLogMapper.php
│   │   ├── StageTransition.php
│   │   └── StageTransitionMapper.php
│   │
│   ├── Migration/                         # MIGRAÇÕES DE BANCO DE DADOS
│   │   ├── Version0100Date20260214120000.php  # Core: licenses, proc_types, stages, forms
│   │   ├── Version0100Date20260214120100.php  # Requests: protocols, requests, form_responses
│   │   └── Version0100Date20260214120200.php  # Relations: labels, assignments, comments, etc.
│   │
│   ├── Middleware/
│   │   └── LicenseMiddleware.php          # Intercepta requests, valida licença
│   │
│   ├── Notification/
│   │   ├── Notifier.php                   # Implementa INotifier (formata notificações)
│   │   └── NotificationHelper.php         # Helper para criar/despachar notificações
│   │
│   ├── Settings/
│   │   ├── AdminSettings.php              # Página de settings admin
│   │   └── AdminSection.php               # Seção no menu de settings
│   │
│   └── Exception/
│       ├── InvalidLicenseException.php    # Licença inválida
│       ├── NotFoundException.php           # Recurso não encontrado
│       └── ValidationException.php        # Erro de validação
│
├── src/                                   # FRONTEND VUE.JS (código-fonte)
│   │
│   ├── main.js                            # Entry point (monta Vue app)
│   ├── App.vue                            # Componente raiz
│   ├── router.js                          # Configuração Vue Router
│   │
│   ├── views/                             # PÁGINAS (uma por rota)
│   │   ├── Dashboard.vue                  # Visão geral com resumos
│   │   ├── KanbanBoard.vue                # Board kanban principal
│   │   ├── RequestForm.vue                # Formulário criar/editar requisição
│   │   ├── RequestDetail.vue              # Detalhes completos do card
│   │   ├── AdminSettings.vue              # Configurações admin
│   │   ├── ProcessTypeList.vue            # Lista/gerencia tipos de processo
│   │   ├── StageManager.vue               # Gerencia etapas de um tipo
│   │   └── FormTemplateEditor.vue         # Editor visual de formulários
│   │
│   ├── components/                        # COMPONENTES REUTILIZÁVEIS
│   │   ├── kanban/
│   │   │   ├── KanbanColumn.vue           # Uma coluna (etapa) do kanban
│   │   │   ├── KanbanCard.vue             # Um card (requisição) no kanban
│   │   │   └── KanbanCardModal.vue        # Modal de detalhes/edição do card
│   │   │
│   │   ├── forms/
│   │   │   ├── DynamicForm.vue            # Renderiza formulário dinâmico
│   │   │   ├── FieldRenderer.vue          # Renderiza um campo individual
│   │   │   └── FieldEditor.vue            # Admin: edita propriedades de campo
│   │   │
│   │   └── common/
│   │       ├── ProtocolBadge.vue           # Badge com número de protocolo
│   │       ├── AssigneeSelector.vue        # Seletor de usuários Nextcloud
│   │       ├── LabelSelector.vue           # Seletor de labels/etiquetas
│   │       ├── DeadlinePicker.vue          # Seletor de data de prazo
│   │       └── LicenseWarning.vue          # Aviso quando licença inválida
│   │
│   ├── store/                             # PINIA STORES (estado global)
│   │   ├── processType.js                 # Estado dos tipos de processo
│   │   ├── stage.js                       # Estado das etapas
│   │   ├── request.js                     # Estado das requisições
│   │   ├── formTemplate.js                # Estado dos templates de formulário
│   │   ├── label.js                       # Estado dos labels
│   │   └── config.js                      # Estado de configuração/licença
│   │
│   └── services/                          # CLIENTES API (chamadas HTTP)
│       ├── api.js                         # Instância axios base + interceptors
│       ├── processTypeApi.js              # Chamadas API tipos de processo
│       ├── stageApi.js                    # Chamadas API etapas
│       ├── requestApi.js                  # Chamadas API requisições
│       ├── formTemplateApi.js             # Chamadas API templates
│       ├── labelApi.js                    # Chamadas API labels
│       └── configApi.js                   # Chamadas API config/licença
│
├── templates/
│   └── index.php                          # Template PHP que carrega o SPA Vue
│
├── css/
│   └── tramita.scss                       # Estilos globais do app
│
├── img/
│   ├── app.svg                            # Ícone do app (barra de navegação)
│   └── app-dark.svg                       # Ícone para dark mode
│
├── js/                                    # BUILD OUTPUT (gerado pelo webpack, gitignored)
│
├── l10n/                                  # TRADUÇÕES
│   ├── pt_BR.js / pt_BR.json             # Português do Brasil
│   └── en.js / en.json                   # Inglês
│
├── tests/                                 # TESTES
│   ├── Unit/
│   │   ├── Service/                       # Testes unitários de services
│   │   └── Db/                            # Testes unitários de mappers
│   └── Integration/
│       └── Controller/                    # Testes de integração de controllers
│
├── composer.json                          # Dependências PHP
├── package.json                           # Dependências JavaScript
├── webpack.js                             # Configuração Webpack
├── Makefile                               # Automação de build
├── .eslintrc.js                           # Config ESLint (JS)
├── .php-cs-fixer.dist.php                 # Config PHP CS Fixer
├── .gitignore                             # Arquivos ignorados pelo git
├── LICENSE                                # AGPL-3.0-or-later
└── CHANGELOG.md                           # Histórico de mudanças
```

---

## Explicação das Camadas

### `appinfo/` — Configuração do App
Arquivos obrigatórios que o Nextcloud lê para reconhecer e configurar o app.

- **info.xml**: "Cartão de identidade" do app. Define nome, versão, autor, dependências, menu de navegação, settings, etc. Validado contra um XML Schema oficial.
- **routes.php**: Mapeia URLs para ações de controllers. Cada rota define: nome do controller, URL pattern, método HTTP (GET/POST/PUT/DELETE).

### `lib/Controller/` — Camada HTTP
Controllers são **finos**. Eles apenas:
1. Recebem a requisição HTTP
2. Extraem parâmetros
3. Delegam para o Service
4. Retornam a resposta (JSONResponse)

**Nunca** coloque lógica de negócio no controller.

### `lib/Service/` — Camada de Lógica de Negócio
Services contêm **toda a lógica de negócio**:
- Validações
- Regras de transição de etapa
- Geração de protocolo
- Criação de notificações
- Verificação de permissões

### `lib/Db/` — Camada de Acesso a Dados
Entities e Mappers seguem o padrão do framework OCP:
- **Entity**: Objeto que representa uma linha do banco. Define propriedades e tipos.
- **Mapper**: Estende `QBMapper`. Contém métodos de query (find, findAll, insert, update, delete).

### `src/` — Frontend Vue.js
Código-fonte do frontend. É compilado pelo Webpack para a pasta `js/`.

- **views/**: Componentes de página (um por rota do Vue Router)
- **components/**: Componentes reutilizáveis organizados por domínio
- **store/**: Pinia stores para gerenciamento de estado
- **services/**: Funções que fazem chamadas HTTP à API

### `templates/` — PHP Templates
O Nextcloud usa templates PHP para renderizar HTML. O app Tramita tem apenas um: `index.php`, que carrega o bundle JavaScript do Vue.

### `lib/Migration/` — Migrações de Banco
Classes PHP que definem a evolução do schema do banco de dados. Executadas automaticamente quando o app é habilitado ou atualizado.

**Regra de ouro**: Nunca alterar uma migração após ela ter sido executada em produção. Sempre criar uma nova migração.
