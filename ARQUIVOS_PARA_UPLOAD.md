# Arquivos para Upload Manual - Deploy

## Última Atualização do Commit
**Commit:** b4ae93a - Feat: Adiciona status e descrição dos compromissos no PDF
**Data:** 2025-11-01

---

## RESUMO DAS ALTERAÇÕES DESTA SESSÃO

### Funcionalidades Implementadas:
1. **Seleção de período para PDF** - Dia, Semana, Mês
2. **Mês Anterior** - Opção para gerar PDF do mês passado
3. **Período Personalizado** - Modal para escolher data início/fim
4. **Status no PDF** - Badges coloridos (Pendente/Concluído/Cancelado)
5. **Descrição no PDF** - Mostra descrição do compromisso quando disponível

---

## ⚠️ ARQUIVOS QUE VOCÊ PRECISA FAZER UPLOAD

### Arquivos Obrigatórios (TODOS):

1. **app/Http/Controllers/AppointmentController.php**
   - Lógica do backend para PDF (período, mês anterior, personalizado)

2. **resources/views/agenda/index.blade.php**
   - Dropdown com 5 opções de PDF + Modal personalizado

3. **resources/views/agenda/pdf/semanal.blade.php**
   - Template do PDF com status, título e descrição

4. **public/build/manifest.json**
   - Manifesto dos assets compilados (após npm run build)

---

## Detalhes das Mudanças

### 1. app/Http/Controllers/AppointmentController.php
**O que mudou:**
- Aceita `periodo`: dia, semana, mes, personalizado
- Aceita `mes_offset`: -1 (mês anterior), 0 (atual), +1 (próximo)
- Aceita `data_inicio` e `data_fim` para período personalizado
- Gera nomes de arquivo dinâmicos

### 2. resources/views/agenda/index.blade.php
**O que mudou:**
- Dropdown com 5 opções:
  * PDF do Dia Atual
  * PDF da Semana Atual
  * PDF do Mês Atual
  * PDF do Mês Anterior (novo!)
  * Período Personalizado... (novo!)
- Modal Alpine.js para escolher datas personalizadas

### 3. resources/views/agenda/pdf/semanal.blade.php
**O que mudou:**
- Exibe status com badge colorido (Pendente/Concluído/Cancelado)
- Mostra título do compromisso quando disponível
- Inclui descrição em itálico
- Bordas e cores diferentes por status

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

## 📋 LISTA RESUMIDA - ARQUIVOS PARA UPLOAD

### ✅ Arquivos OBRIGATÓRIOS (4 arquivos):

```
app/Http/Controllers/AppointmentController.php
resources/views/agenda/index.blade.php
resources/views/agenda/pdf/semanal.blade.php
public/build/manifest.json
```

### ⚠️ IMPORTANTE:
Você **NÃO** precisa fazer upload dos arquivos JavaScript compilados desta vez, pois não houve alteração no `resources/js/calendar.js` nesta sessão. O `manifest.json` é suficiente.

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
