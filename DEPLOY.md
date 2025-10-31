# 🚀 Guia de Deploy para Produção

## ⚠️ PROBLEMA ATUAL
Erro: `Route [agenda.quick-messages.store] not defined`

## 🔧 SOLUÇÃO

Execute os comandos abaixo **no servidor de produção** via SSH ou painel de controle:

### 1️⃣ Entrar na pasta do projeto
```bash
cd /home/u433815487/domains/treinoeenergia.com.br
```

### 2️⃣ Verificar se está na branch correta
```bash
git branch
# Deve mostrar: * main
```

### 3️⃣ Atualizar o código
```bash
git pull origin main
```

### 4️⃣ Executar migrations (criar tabela de mensagens)
```bash
php artisan migrate --force
```

### 5️⃣ Limpar TODOS os caches
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear
```

### 6️⃣ Recriar caches (otimização)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7️⃣ Verificar se as rotas foram criadas
```bash
php artisan route:list | grep quick-messages
```

**Você deve ver 3 rotas:**
- `POST agenda/quick-messages`
- `PATCH agenda/quick-messages/{template}`
- `DELETE agenda/quick-messages/{template}`

---

## 🆘 SE AINDA DER ERRO

### Opção A: Verificar se o código foi enviado ao Git
No seu computador local:
```bash
git log --oneline -1
# Deve mostrar: dc0b53b Feat: Cria mnesagem prontas

git push origin main
```

### Opção B: Verificar arquivos no servidor
```bash
# Verificar se controller existe
ls -la app/Http/Controllers/QuickMessageTemplateController.php

# Verificar se model existe
ls -la app/Models/WhatsAppMessageTemplate.php

# Verificar se migration existe
ls -la database/migrations/*whats_app_message_templates*

# Verificar se rotas estão no arquivo
grep -n "quick-messages" routes/web.php
```

### Opção C: Recriar cache de rotas manualmente
```bash
php artisan route:clear
php artisan cache:clear
php artisan config:clear
php artisan route:cache
```

---

## 📋 CHECKLIST FINAL

- [ ] Código atualizado (`git pull`)
- [ ] Migrations executadas (`php artisan migrate`)
- [ ] Caches limpos
- [ ] Rotas aparecem em `php artisan route:list`
- [ ] Página carrega sem erro

---

## 🔍 DEBUGGING

Se o erro persistir, capture estas informações:

```bash
# Versão do Laravel
php artisan --version

# Lista de rotas (filtrar por quick-messages)
php artisan route:list | grep quick

# Verificar último commit
git log -1 --oneline

# Verificar branch atual
git status
```

E me envie os resultados!
