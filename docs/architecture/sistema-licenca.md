# Tramita - Sistema de Licença

## Visão Geral

O Tramita usa um modelo de licenciamento baseado em validação de chave contra um servidor remoto. O código do app é AGPL (aberto), mas o app só funciona com uma licença válida emitida pela Genius Apps.

---

## Arquitetura

```
┌──────────────────────┐       HTTPS POST        ┌─────────────────────────────┐
│  Tramita App         │  ══════════════════>     │  Servidor de Licenças       │
│  (dentro do NC)      │                          │  license.geniusapps.com.br  │
│                      │  <══════════════════     │                             │
│  LicenseMiddleware   │   {valid, expires,       │  POST /api/v1/validate      │
│  LicenseService      │    features, plan}       │  POST /api/v1/activate      │
│  ConfigController    │                          │  POST /api/v1/deactivate    │
└──────────────────────┘                          └─────────────────────────────┘
```

---

## Fluxo de Validação

### 1. Admin insere a chave de licença
- Acessa **Settings > Tramita > Licença**
- Digita a chave no formato `GA-TRAMITA-XXXX-XXXX-XXXX-XXXX`
- Frontend chama `PUT /api/v1/config/license`

### 2. ConfigController delega para LicenseService
```
ConfigController::setLicense()
    → LicenseService::setLicenseKey($key)
        → HTTP POST para license.geniusapps.com.br/api/v1/validate
```

### 3. Dados enviados na validação
```json
{
    "license_key": "GA-TRAMITA-XXXX-XXXX-XXXX-XXXX",
    "instance_id": "oc1234567890",
    "app_version": "1.0.0",
    "server_url": "https://nextcloud.prefeitura.gov.br"
}
```

- **instance_id**: ID único da instância Nextcloud (`OCP\IConfig::getSystemValueString('instanceid')`)
- **server_url**: URL base do Nextcloud (opcional, para binding de domínio)

### 4. Resposta do servidor
```json
{
    "valid": true,
    "expires": "2027-02-14",
    "plan": "enterprise",
    "licensed_to": "Prefeitura de Exemplo",
    "max_users": 50,
    "features": ["kanban", "forms", "protocol", "notifications", "audit"]
}
```

### 5. Cache do resultado
- Resultado cacheado por **24 horas** no app config do Nextcloud
- Evita chamadas de rede em cada requisição
- Chave de cache: `tramita.license_valid_until` = timestamp atual + 86400

---

## LicenseMiddleware

O middleware intercepta **todas** as requisições API e verifica a licença:

```
Requisição HTTP
    ↓
LicenseMiddleware::beforeController()
    ↓
    É PageController ou ConfigController? → SIM → Permitir (sem verificação)
    ↓ NÃO
    LicenseService::isValid()? → SIM → Permitir
    ↓ NÃO
    Retornar HTTP 402 (Payment Required)
```

### Exceções (não verificam licença)
- **PageController**: O SPA precisa carregar para que o admin possa inserir a licença
- **ConfigController**: O endpoint de licença precisa funcionar para ativação

### Resposta HTTP 402
```json
{
    "message": "Licença inválida ou expirada",
    "licenseRequired": true
}
```

O frontend intercepta o 402 e mostra o componente `LicenseWarning.vue`.

---

## Grace Period (Período de Graça)

Se o servidor de licenças estiver inacessível:

1. O sistema verifica quando foi a última validação bem-sucedida
2. Se foi nos últimos **7 dias**, a licença continua válida
3. Após 7 dias sem conseguir validar, a licença é considerada inválida

Isso evita que o app pare de funcionar por problemas temporários de rede.

```
Servidor inacessível?
    ↓
Última validação < 7 dias atrás? → SIM → Licença válida (grace period)
    ↓ NÃO
Licença inválida
```

---

## Segurança

| Aspecto | Implementação |
|---------|---------------|
| **Armazenamento da chave** | `OCP\IConfig::setAppValue()` (encrypted at rest) |
| **Transporte** | HTTPS obrigatório |
| **Binding de instância** | instance_id impede compartilhamento de chave |
| **Anti-tampering** | Servidor pode incluir JWT/HMAC na resposta |
| **Timeout** | 10 segundos para evitar bloqueio |

---

## O Servidor de Licenças (A ser construído)

O servidor de licenças da Genius Apps precisa ser um serviço web simples que:

1. **Recebe** requisições de validação com license_key + instance_id
2. **Verifica** a chave no banco de dados
3. **Valida** binding de instância/domínio
4. **Retorna** status, expiração e features habilitadas

### Stack sugerida para o servidor
- API simples (Node.js/Express, PHP/Laravel, Python/FastAPI — qualquer um serve)
- Banco de dados (PostgreSQL ou MySQL)
- HTTPS com certificado válido
- Dashboard admin para gerenciar licenças e clientes

### Endpoints necessários
```
POST /api/v1/validate    — Validar licença
POST /api/v1/activate    — Ativar licença (primeira vez)
POST /api/v1/deactivate  — Desativar licença (mudar servidor)
GET  /api/v1/status      — Status da licença
```

**Nota**: O servidor de licenças é um projeto separado do Tramita. Pode ser construído em qualquer tecnologia.
