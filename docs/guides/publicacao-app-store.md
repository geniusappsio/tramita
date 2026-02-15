# Guia de Publicação na App Store do Nextcloud

## Pré-requisitos

Antes de publicar na App Store, você precisa:

1. Um repositório público no GitHub/GitLab com o código do app
2. O app funcionando corretamente em pelo menos uma versão do Nextcloud
3. Um certificado de assinatura de código
4. O `info.xml` completo e válido

---

## Passo 1: Gerar Certificado de Assinatura

### 1.1 Gerar chave privada e CSR

```bash
# Gerar chave privada RSA 4096-bit
openssl req -nodes -newkey rsa:4096 -keyout tramita.key -out tramita.csr \
    -subj "/CN=tramita"
```

**IMPORTANTE**:
- O CN (Common Name) deve ser **exatamente** o ID do app (`tramita`)
- Guarde o arquivo `tramita.key` em local seguro — nunca compartilhe!
- O ID do app deve conter apenas letras minúsculas e underscores

### 1.2 Solicitar assinatura

1. Acesse [github.com/nextcloud/app-certificate-requests](https://github.com/nextcloud/app-certificate-requests)
2. Crie um Pull Request com o conteúdo do arquivo `tramita.csr`
3. No PR, inclua o link do repositório público do app
4. Configure seu perfil do GitHub para mostrar seu email

A equipe do Nextcloud vai analisar e, se aprovado, fornecer o certificado assinado.

### 1.3 Receber o certificado

Após aprovação, você receberá o arquivo `tramita.crt`. Guarde junto com `tramita.key`.

---

## Passo 2: Assinar o Release

### 2.1 Preparar o pacote

```bash
# Build de produção
npm run build
composer install --no-dev

# Criar pacote .tar.gz (via Makefile)
make appstore
```

O Makefile gera `build/tramita.tar.gz` excluindo arquivos de desenvolvimento.

### 2.2 Assinar o pacote

```bash
# Assinar o tar.gz
openssl dgst -sha512 -sign tramita.key build/tramita.tar.gz | openssl base64 > build/tramita.tar.gz.sig
```

---

## Passo 3: Registrar o App na App Store

### 3.1 Criar conta

1. Acesse [apps.nextcloud.com](https://apps.nextcloud.com)
2. Faça login com sua conta GitHub

### 3.2 Registrar o app

Você pode registrar via interface web ou via API REST:

**Via API (recomendado)**:
```bash
curl -X POST https://apps.nextcloud.com/api/v1/apps/releases \
    -H "Authorization: Token YOUR_API_TOKEN" \
    -H "Content-Type: application/json" \
    -d '{
        "download": "https://github.com/geniusapps/tramita/releases/download/v1.0.0/tramita.tar.gz",
        "signature": "'$(cat build/tramita.tar.gz.sig)'",
        "nightly": false
    }'
```

**Via interface web**:
1. Acesse apps.nextcloud.com
2. Clique em "Upload App"
3. Forneça o link de download e a assinatura

### 3.3 Informações extraídas automaticamente

A App Store extrai do `info.xml`:
- Nome, descrição, autor
- Versão e dependências
- Categoria e screenshots
- Links de bugs, repositório, website

**Princípio DRY**: Você NÃO precisa re-inserir essas informações na App Store.

---

## Passo 4: Regras da App Store

### Obrigatórias

| Regra | Detalhe |
|-------|---------|
| **Licença AGPL** | O app DEVE ser licenciado sob AGPL-3.0-or-later |
| **Sem "Nextcloud" no nome** | O nome do app não pode conter "Nextcloud" |
| **info.xml válido** | Deve validar contra o XML Schema oficial |
| **Assinatura de código** | Apps Featured DEVEM ser assinados |
| **Sem backdoors** | O Nextcloud faz auditorias de segurança |

### Recomendadas

| Recomendação | Detalhe |
|-------------|---------|
| Screenshots | Incluir screenshots no info.xml para melhor visibilidade |
| Descrição multilíngue | Pelo menos português e inglês |
| Documentação | Links para docs de usuário, admin e desenvolvedor |
| Changelog | Manter um CHANGELOG.md atualizado |
| Testes | Ter testes automatizados aumenta a confiança |

### Proibidas

| Proibição | Detalhe |
|-----------|---------|
| Código malicioso | Nenhum tipo de backdoor, spyware ou minerador |
| Dados sem consentimento | Não coletar dados sem informar o usuário |
| Dependências inseguras | Não incluir bibliotecas com vulnerabilidades conhecidas |

---

## Passo 5: Release Automation (Opcional)

### GitHub Actions

Automatize o build e publicação com GitHub Actions:

```yaml
# .github/workflows/release.yml
name: Release
on:
  release:
    types: [published]

jobs:
  release:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: 20

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.1

      - name: Build
        run: |
          npm ci
          npm run build
          composer install --no-dev

      - name: Package
        run: make appstore

      - name: Sign
        run: |
          echo "${{ secrets.APP_PRIVATE_KEY }}" > tramita.key
          openssl dgst -sha512 -sign tramita.key build/tramita.tar.gz | openssl base64 > build/tramita.tar.gz.sig

      - name: Upload to release
        uses: softprops/action-gh-release@v1
        with:
          files: |
            build/tramita.tar.gz
            build/tramita.tar.gz.sig

      - name: Publish to App Store
        run: |
          curl -X POST https://apps.nextcloud.com/api/v1/apps/releases \
            -H "Authorization: Token ${{ secrets.APPSTORE_TOKEN }}" \
            -H "Content-Type: application/json" \
            -d "{
              \"download\": \"${{ github.event.release.assets[0].browser_download_url }}\",
              \"signature\": \"$(cat build/tramita.tar.gz.sig)\",
              \"nightly\": false
            }"
```

### Secrets necessários
- `APP_PRIVATE_KEY`: Conteúdo do arquivo `tramita.key`
- `APPSTORE_TOKEN`: Token de API da App Store

---

## Passo 6: Manutenção

### Atualizar o app
1. Incremente a versão no `info.xml`
2. Crie uma nova migração se houver mudanças no banco
3. Build, assine e publique novo release
4. A App Store publica automaticamente para instalações que verificam atualizações

### Compatibilidade com novas versões do Nextcloud
- A cada nova major version do Nextcloud, teste o app
- Atualize `max-version` no info.xml se compatível
- Faça ajustes se APIs deprecated foram removidas

### Responder a auditorias de segurança
- O Nextcloud pode auditar seu app a qualquer momento
- Corrija vulnerabilidades reportadas prontamente
- Apps com vulnerabilidades não corrigidas podem ser removidos da App Store

---

## Fontes

- [App Store Developer Guide](https://nextcloudappstore.readthedocs.io/en/latest/developer.html)
- [Publishing Rules](https://docs.nextcloud.com/server/latest/developer_manual/app_publishing_maintenance/publishing.html)
- [Code Signing](https://docs.nextcloud.com/server/stable/developer_manual/app_publishing_maintenance/code_signing.html)
- [Release Automation](https://docs.nextcloud.com/server/stable/developer_manual/app_publishing_maintenance/release_automation.html)
- [Certificate Requests](https://github.com/nextcloud/app-certificate-requests)
