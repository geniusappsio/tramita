# Tramita - Documentação

Sistema de gestão de processos e tramitação de documentos para órgãos públicos municipais brasileiros.

**Desenvolvido por**: Genius Apps
**Plataforma**: Nextcloud App Store
**Licença**: AGPL-3.0-or-later

---

## Índice

### Planos e Design
- [Design Document](plans/2026-02-14-tramita-design.md) — Visão geral, funcionalidades, fases, decisões de design
- [Ordem de Implementação](plans/ordem-implementacao.md) — 15 etapas detalhadas com checklists de verificação

### Arquitetura
- [Stack Tecnológica](architecture/stack-tecnologica.md) — PHP, Vue.js, banco de dados, build system, ferramentas
- [Estrutura de Diretórios](architecture/estrutura-diretorios.md) — Organização completa do projeto com explicações
- [Banco de Dados](architecture/banco-de-dados.md) — Schema completo (16 tabelas), índices, migrações
- [API REST Endpoints](architecture/api-endpoints.md) — Todos os endpoints com exemplos de request/response
- [Sistema de Licença](architecture/sistema-licenca.md) — Arquitetura de validação, middleware, grace period

### Guias
- [Guia para Iniciantes](guides/guia-iniciante-nextcloud-dev.md) — Conceitos, padrões, exemplos de código, comandos úteis
- [Publicação na App Store](guides/publicacao-app-store.md) — Certificado, assinatura, regras, automação
- [Cuidados de Segurança](guides/cuidados-seguranca.md) — OWASP, LGPD, controle de acesso, validação

### Referências
- [Fontes e Referências](references/fontes-e-referencias.md) — Links oficiais, apps de referência, bibliotecas, fontes pesquisadas

---

## Quick Start

```bash
# 1. Clonar o repositório dentro da pasta apps do Nextcloud
cd /path/to/nextcloud/apps
git clone https://github.com/geniusapps/tramita.git

# 2. Instalar dependências
cd tramita
composer install
npm ci

# 3. Build do frontend
npm run dev

# 4. Habilitar o app
php occ app:enable tramita

# 5. Acessar o app
# Abra o Nextcloud no navegador e clique em "Tramita" no menu
```

---

## Resumo do Projeto

| Aspecto | Detalhe |
|---------|---------|
| **App ID** | `tramita` |
| **Namespace** | `OCA\Tramita` |
| **Backend** | PHP 8.1+ com OCP Framework |
| **Frontend** | Vue.js 2.7 + @nextcloud/vue 8 + Pinia |
| **Banco** | SQLite / MySQL 8+ / PostgreSQL 12+ |
| **Tabelas** | 16 tabelas no MVP |
| **Endpoints** | ~35 rotas REST API |
| **Nextcloud** | 28 a 31 |
| **Assinatura Digital** | GOV.BR API (Fase 3) |
| **Licença** | AGPL-3.0-or-later + license key |
