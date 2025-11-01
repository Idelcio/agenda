# Arquivos para Upload Manual - Deploy

## Última Atualização do Commit
**Commit:** 2e3854f - Feat: Adiciona seleção de período para geração de PDF
**Data:** 2025-11-01

---

## Arquivos Modificados (Último Commit)

### 1. app/Http/Controllers/AppointmentController.php
**Caminho completo:** `app/Http/Controllers/AppointmentController.php`

**Mudanças:**
- Método `gerarPdfSemanal()` agora aceita parâmetro `periodo` (dia/semana/mes)
- Switch para definir início/fim baseado no período
- Nomes de arquivo dinâmicos por tipo de período

---

### 2. resources/views/agenda/index.blade.php
**Caminho completo:** `resources/views/agenda/index.blade.php`

**Mudanças:**
- Botão "Gerar PDF Semanal" substituído por dropdown com Alpine.js
- Três opções: PDF do Dia, Semana e Mês
- Menu dropdown com animações

---

### 3. resources/views/agenda/pdf/semanal.blade.php
**Caminho completo:** `resources/views/agenda/pdf/semanal.blade.php`

**Mudanças:**
- Título do PDF agora é dinâmico (AGENDA DIÁRIA/SEMANAL/MENSAL)

---

## Commits Anteriores Importantes

### Commit 88339fd - Navegação do calendário
**Arquivo:** `resources/js/calendar.js`
- Permite navegar entre meses no calendário
- **IMPORTANTE:** Precisa rodar `npm run build` para gerar `public/build/assets/app-*.js`

---

### Commit 8f1923d, c6a1e5d, 6ccf996, 054be30, 19b6325 - Fixes diversos
**Arquivo:** `resources/views/agenda/index.blade.php`
- Correção de filtros de período (dia/semana/mês)
- Correção de bugs JavaScript
- Correção de ortografia ("Mês")

---

## LISTA COMPLETA DE ARQUIVOS PARA UPLOAD

### PHP (Backend)
```
app/Http/Controllers/AppointmentController.php
```

### Blade (Views)
```
resources/views/agenda/index.blade.php
resources/views/agenda/pdf/semanal.blade.php
```

### JavaScript (precisa compilar)
```
resources/js/calendar.js
```

### Assets Compilados (após npm run build)
```
public/build/assets/app-[hash].js
public/build/assets/app-[hash].css
public/build/manifest.json
```

---

## PROCEDIMENTO DE UPLOAD MANUAL

### Passo 1: Compilar Assets
No ambiente local:
```bash
npm run build
```

### Passo 2: Upload via FTP/SFTP
Faça upload dos seguintes arquivos para **AMBOS** os servidores:

#### Backend (PHP)
- `app/Http/Controllers/AppointmentController.php`

#### Views (Blade)
- `resources/views/agenda/index.blade.php`
- `resources/views/agenda/pdf/semanal.blade.php`

#### JavaScript (opcional, se você editar JS no servidor)
- `resources/js/calendar.js`

#### Assets Compilados (IMPORTANTE)
- `public/build/assets/app-*.js` (arquivo com hash no nome)
- `public/build/assets/app-*.css` (arquivo com hash no nome)
- `public/build/manifest.json`

**NOTA:** Os arquivos em `public/build/assets/` têm nomes com hash (ex: app-abc123.js).
Você pode enviar toda a pasta `public/build/` para garantir.

### Passo 3: Limpar Cache no Servidor
Após upload, execute **EM CADA SERVIDOR**:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Ou se tiver acesso apenas via painel:
- Acesse qualquer rota do sistema que force o recarregamento
- Delete manualmente os arquivos em `bootstrap/cache/` se tiver acesso FTP

---

## VERIFICAÇÃO PÓS-DEPLOY

### Testar no navegador:
1. Acesse `/agenda`
2. Verifique se o botão "Gerar PDF" aparece
3. Clique e veja se abre menu dropdown com 3 opções
4. Teste gerar PDF do Dia, Semana e Mês
5. Teste navegar entre meses no calendário
6. Teste os filtros Dia/Semana/Mês

---

## TROUBLESHOOTING

### Se o dropdown não aparecer:
- Verifique se o arquivo `public/build/manifest.json` foi atualizado
- Limpe cache do navegador (Ctrl+F5)
- Verifique se Alpine.js está carregando (F12 > Console)

### Se os PDFs não gerarem corretamente:
- Verifique permissões da pasta `storage/`
- Execute `php artisan storage:link`
- Verifique logs em `storage/logs/laravel.log`

### Se o calendário não navegar entre meses:
- Verifique se `public/build/assets/app-*.js` foi atualizado
- Limpe cache do navegador
- Verifique console do navegador (F12) por erros JavaScript

---

## OPÇÃO ALTERNATIVA: USAR GIT NO SERVIDOR

Se os servidores têm acesso Git:

```bash
# No servidor
cd /caminho/do/projeto
git pull origin main
npm run build
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Muito mais simples e seguro!
