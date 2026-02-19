# Instalação em Ambiente Docker

Guia passo a passo para instalar o Tramita em uma instância Nextcloud rodando em Docker.

## Pré-requisitos

- Docker e Docker Compose instalados no servidor
- Container Nextcloud rodando (imagem `nextcloud:latest` ou similar)
- Acesso root/sudo ao servidor host

## Passo 1 — Clonar o repositório

No **servidor host**, acesse o volume de apps do Nextcloud e clone o repositório:

```bash
cd /var/lib/docker/volumes/nextcloud_data/_data/apps
git clone https://github.com/geniusappsio/tramita.git
```

> **Nota**: O caminho do volume pode variar dependendo da sua configuração Docker. Verifique com `docker inspect nextcloud` se necessário.

Se o `git` solicitar autenticação mesmo o repositório sendo público, use:

```bash
GIT_TERMINAL_PROMPT=0 git clone https://github.com/geniusappsio/tramita.git
```

## Passo 2 — Instalar dependências PHP

O container padrão do Nextcloud não inclui o Composer. Acesse o container e instale:

```bash
# Acessar o container
docker exec -it nextcloud bash

# Navegar até o app
cd /var/www/html/apps/tramita

# Instalar o Composer
curl -sS https://getcomposer.org/installer | php

# Instalar dependências PHP (sem dev)
php composer.phar install --no-dev
```

> O `--no-dev` pula dependências de desenvolvimento (phpunit, coding-standard) que não são necessárias em produção.

## Passo 3 — Build do frontend

### Opção A: Build dentro do container

Instale o Node.js dentro do container:

```bash
# Ainda dentro do container nextcloud
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs

# Verificar instalação
node -v    # Deve mostrar v20.x
npm -v     # Deve mostrar v10.x

# Instalar dependências e compilar
cd /var/www/html/apps/tramita
npm ci
npm run build
```

> **Atenção**: O Node.js instalado dessa forma será perdido ao recriar o container. Para deploys recorrentes, prefira a Opção B.

### Opção B: Build local e cópia (recomendado para produção)

No seu **computador de desenvolvimento**:

```bash
cd ~/Projects/nextcloud/apps/tramita
npm ci
npm run build
```

Copie a pasta `js/` gerada para o servidor:

```bash
scp -r js/ root@seu-servidor:/var/lib/docker/volumes/nextcloud_data/_data/apps/tramita/
```

## Passo 4 — Ajustar permissões

O Nextcloud espera que os arquivos do app pertençam ao usuário `www-data`:

```bash
# No servidor host
chown -R www-data:www-data /var/lib/docker/volumes/nextcloud_data/_data/apps/tramita

# OU dentro do container
docker exec -it nextcloud chown -R www-data:www-data /var/www/html/apps/tramita
```

## Passo 5 — Habilitar o app

O comando `occ` deve ser executado como usuário `www-data` (uid 33) e com o **caminho completo** `/var/www/html/occ`:

```bash
# De fora do container (recomendado)
docker exec -u www-data nextcloud php /var/www/html/occ app:enable tramita
```

Se já estiver dentro do container:

```bash
su -s /bin/bash www-data -c "php /var/www/html/occ app:enable tramita"
```

> **Importante**: Não use `php occ` a partir da pasta do app — o `occ` fica na raiz do Nextcloud (`/var/www/html/occ`), não na pasta do app. Executar como `root` também causará erro; sempre use o usuário `www-data`.

## Passo 6 — Atualizar cache do Nextcloud

Após habilitar o app, execute o repair para registrar assets e migrations:

```bash
docker exec -u www-data nextcloud php /var/www/html/occ maintenance:repair
```

## Passo 7 — Verificar

1. Acesse o Nextcloud no navegador
2. O item **Tramita** deve aparecer no menu de navegação
3. Clique para acessar o app

## Atualizando o app

Para atualizar o Tramita em deploys futuros:

```bash
# No servidor host
cd /var/lib/docker/volumes/nextcloud_data/_data/apps/tramita
git pull origin main

# Dentro do container
docker exec -it nextcloud bash
cd /var/www/html/apps/tramita
php composer.phar install --no-dev
```

E refaça o build do frontend (Passo 3, Opção A ou B).

Depois execute as migrações de banco e atualize o cache:

```bash
docker exec -u www-data nextcloud php /var/www/html/occ upgrade
docker exec -u www-data nextcloud php /var/www/html/occ maintenance:repair
```

## Solução de problemas

### "Permission denied" ao clonar via SSH

O container provavelmente não tem chave SSH configurada. Use HTTPS:

```bash
git clone https://github.com/geniusappsio/tramita.git
```

### App não aparece no menu

Verifique se o app está habilitado:

```bash
docker exec -u www-data nextcloud php occ app:list | grep tramita
```

Se não estiver listado, verifique os logs:

```bash
docker exec -u www-data nextcloud php occ log:tail
```

### Erro "Could not load JavaScript"

A pasta `js/` está ausente ou vazia. Refaça o Passo 3.

### Erro de permissão ao habilitar

Ajuste as permissões conforme o Passo 4.

### "Console has to be executed with the user that owns config.php"

O `occ` precisa ser executado como `www-data`, não como `root`:

```bash
# Errado
docker exec -it nextcloud php /var/www/html/occ app:enable tramita

# Correto (note o -u www-data)
docker exec -u www-data nextcloud php /var/www/html/occ app:enable tramita
```

### "Could not open input file: occ"

Você está executando `php occ` a partir de uma pasta que não é a raiz do Nextcloud. Use o caminho completo:

```bash
# Errado (dentro de /var/www/html/apps/tramita)
php occ app:enable tramita

# Correto
php /var/www/html/occ app:enable tramita
```
