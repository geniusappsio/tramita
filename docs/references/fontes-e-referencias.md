# Tramita - Fontes e Referências

## Documentação Oficial do Nextcloud

### Developer Manual
- **[Nextcloud Developer Manual (principal)](https://docs.nextcloud.com/server/latest/developer_manual/)** — Documentação completa para desenvolvedores de apps. Última atualização: Janeiro 2026.
- **[App Development Index](https://docs.nextcloud.com/server/latest/developer_manual/app_development/index.html)** — Índice do tutorial de desenvolvimento de apps.
- **[Tutorial Completo](https://docs.nextcloud.com/server/21/developer_manual/app_development/tutorial.html)** — Tutorial passo-a-passo para criar um app Nextcloud.
- **[Bootstrapping](https://docs.nextcloud.com/server/latest/developer_manual/app_development/bootstrap.html)** — Como inicializar um app com IBootstrap.

### Banco de Dados
- **[Database Access (QueryBuilder)](https://docs.nextcloud.com/server/latest/developer_manual/basics/storage/database.html)** — Como acessar o banco de dados usando QueryBuilder e QBMapper.
- **[Migrations](https://docs.nextcloud.com/server/stable/developer_manual/basics/storage/migrations.html)** — Como criar e gerenciar migrações de banco de dados.
- **[OCP\DB\Types](https://github.com/nextcloud/server/blob/master/lib/public/DB/Types.php)** — Tipos de dados disponíveis para colunas.

### Controllers e Rotas
- **[Controllers](https://docs.nextcloud.com/server/latest/developer_manual/basics/controllers.html)** — Como criar controllers, atributos de segurança, retorno de respostas.
- **[Dependency Injection](https://docs.nextcloud.com/server/latest/developer_manual/basics/dependency_injection.html)** — Como funciona a injeção de dependências.
- **[Configuration](https://docs.nextcloud.com/server/latest/developer_manual/basics/storage/configuration.html)** — Como usar IConfig para configurações.

### Frontend
- **[JavaScript/Frontend](https://docs.nextcloud.com/server/latest/developer_manual/basics/front-end/js.html)** — Setup de JavaScript e Vue.js para apps.
- **[@nextcloud/vue Components](https://www.npmjs.com/package/@nextcloud/vue)** — Biblioteca de componentes Vue do Nextcloud.
- **[@nextcloud/vue Storybook](https://nextcloud-vue-components.netlify.app/)** — Documentação visual dos componentes.

### Publicação
- **[App Store Publishing Rules](https://docs.nextcloud.com/server/latest/developer_manual/app_publishing_maintenance/publishing.html)** — Regras para publicação na App Store.
- **[Code Signing](https://docs.nextcloud.com/server/stable/developer_manual/app_publishing_maintenance/code_signing.html)** — Como assinar o código do app.
- **[Release Automation](https://docs.nextcloud.com/server/stable/developer_manual/app_publishing_maintenance/release_automation.html)** — Automatização de releases.
- **[Certificate Requests](https://github.com/nextcloud/app-certificate-requests)** — Repositório para solicitar certificados de assinatura.

### PHP API
- **[Nextcloud PHP API (master)](https://nextcloud-server.netlify.app/namespaces/ocp.html)** — Documentação da API PHP do Nextcloud (namespace OCP).

---

## Apps de Referência (Código-fonte)

### Nextcloud Deck (Kanban)
- **[GitHub: nextcloud/deck](https://github.com/nextcloud/deck)** — App oficial de Kanban do Nextcloud. É a principal referência para o Tramita, pois implementa:
  - Boards com colunas/cards
  - Drag-and-drop
  - Atribuição de usuários
  - Labels
  - Due dates
  - Notificações

### Nextcloud Forms
- **[GitHub: nextcloud/forms](https://github.com/nextcloud/forms)** — App oficial de formulários. Referência para:
  - Formulários dinâmicos
  - Tipos de campo
  - Respostas e submissões

### App Tutorial
- **[GitHub: nextcloud/app-tutorial](https://github.com/nextcloud/app-tutorial)** — App tutorial oficial. Exemplo simples de:
  - Estrutura de diretórios
  - Migration
  - Controller/Service/Mapper
  - Frontend Vue

---

## Ferramentas e Bibliotecas

### PHP
| Pacote | Uso | Link |
|--------|-----|------|
| nextcloud/ocp | API PHP do Nextcloud (dev) | [Packagist](https://packagist.org/packages/nextcloud/ocp) |
| phpunit/phpunit | Testes unitários | [PHPUnit](https://phpunit.de/) |
| nextcloud/coding-standard | Coding standard | [GitHub](https://github.com/nextcloud/coding-standard) |

### JavaScript/Vue
| Pacote | Uso | Link |
|--------|-----|------|
| @nextcloud/vue | Componentes UI | [npm](https://www.npmjs.com/package/@nextcloud/vue) |
| @nextcloud/axios | HTTP client com CSRF | [npm](https://www.npmjs.com/package/@nextcloud/axios) |
| @nextcloud/router | URL generation | [npm](https://www.npmjs.com/package/@nextcloud/router) |
| @nextcloud/l10n | Internacionalização | [npm](https://www.npmjs.com/package/@nextcloud/l10n) |
| @nextcloud/initial-state | Estado inicial PHP→JS | [npm](https://www.npmjs.com/package/@nextcloud/initial-state) |
| @nextcloud/dialogs | Diálogos e toasts | [npm](https://www.npmjs.com/package/@nextcloud/dialogs) |
| @nextcloud/moment | Datas | [npm](https://www.npmjs.com/package/@nextcloud/moment) |
| @nextcloud/webpack-vue-config | Config webpack | [npm](https://www.npmjs.com/package/@nextcloud/webpack-vue-config) |
| pinia | State management | [Pinia](https://pinia.vuejs.org/) |
| vue-router | Roteamento SPA | [Vue Router](https://v3.router.vuejs.org/) |
| vuedraggable | Drag-and-drop | [npm](https://www.npmjs.com/package/vuedraggable) |

---

## Assinatura Digital (Fase 3)

### GOV.BR - API de Assinatura Eletrônica
- **[Manual de Integração](https://manual-integracao-assinatura-eletronica.servicos.gov.br/pt-br/latest/iniciarintegracao.html)** — Passo-a-passo para integrar com a API de assinatura avançada do GOV.BR.
- **[ReadTheDocs](https://manual-integracao-assinatura-eletronica.readthedocs.io/en/latest/iniciarintegracao.html)** — Documentação técnica alternativa.
- **Custo**: Gratuito para órgãos públicos
- **Tipo**: Assinatura eletrônica avançada (com validade jurídica)
- **Requisito**: Credenciais solicitadas por Gestor Público via ecossistema GOV.BR

### Alternativas
- **[Autentique](https://www.autentique.com.br)** — Plataforma brasileira. Plano gratuito com 5 docs/mês.
- **[D4Sign](https://d4sign.com.br/)** — Plataforma brasileira. A partir de ~R$150/mês.

---

## Referência de Produto

### 1Doc (Inspiração)
- **[1Doc - Prefeituras](https://1doc.com.br/prefeituras)** — Sistema de gestão pública para prefeituras que serviu de inspiração para o Tramita.
- **[Funcionalidades](https://1doc.com.br/governo/sobre/funcionalidades/)** — Lista de funcionalidades do 1Doc.
- **[Processos](https://1doc.com.br/governo/sobre/funcionalidades/processos/)** — Módulo de processos e protocolo.
- **[Módulos](https://atendimento.1doc.com.br/kb/pt-br/category/M%C3%B3dulos?ticketId=&q=&kbCategoryId=109420)** — Documentação de módulos (Memorando, Ofício, Processo Administrativo, Protocolo).

**Funcionalidades principais do 1Doc**:
- Tramitação digital de processos
- Protocolo automático
- Assinatura digital (com validade jurídica)
- Portal do cidadão (Central de Atendimento)
- Relatórios e transparência
- Módulos: Memorando, Ofício, Processo Administrativo, Protocolo
- Fluxos de trabalho com etapas configuráveis
- Alertas e notificações automáticas

---

## App Store do Nextcloud

- **[Nextcloud App Store](https://apps.nextcloud.com)** — Onde o app será publicado.
- **[App Store Documentation](https://nextcloudappstore.readthedocs.io/en/latest/)** — Documentação da App Store.
- **[App Developer Guide](https://nextcloudappstore.readthedocs.io/en/latest/developer.html)** — Guia para desenvolvedores.

---

## Comunidade e Suporte

- **[Nextcloud Community Forum](https://help.nextcloud.com/)** — Fórum da comunidade Nextcloud.
- **[Development Category](https://help.nextcloud.com/c/dev/11)** — Categoria de desenvolvimento no fórum.
- **[Nextcloud GitHub](https://github.com/nextcloud)** — Organização no GitHub.
- **[Developer Portal](https://nextcloud.com/developer/)** — Portal para desenvolvedores.

---

## Informações Técnicas Pesquisadas

### Banco de Dados
- Foreign keys não são usadas em apps Nextcloud (convenção). Fonte: [Community discussion](https://help.nextcloud.com/t/failure-on-foreign-key-constraint-on-delete-cascade/190667)
- `database.xml` removido desde Nextcloud 21. Migrações PHP são obrigatórias.
- Índices grandes devem usar `AddMissingIndicesEvent` para não bloquear upgrades.

### Licenciamento
- Apps na App Store devem ser AGPL-3.0-or-later. Fonte: [Publishing Rules](https://docs.nextcloud.com/server/latest/developer_manual/app_publishing_maintenance/publishing.html)
- Modelo comercial com license key é permitido (código aberto, funcionalidade condicionada à licença).
- Apps assinados previamente devem continuar sendo assinados em releases futuros.

### Frontend
- Nextcloud usa Vue.js 2.x (não Vue 3). Setup via @nextcloud/webpack-vue-config.
- Fonte: [Vue.js setup discussion](https://help.nextcloud.com/t/vue-js-setup-for-app-development/55209)
