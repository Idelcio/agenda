# 📦 Arquivos para Deploy - Usuários Filhos e Envio em Massa

## 🆕 NOVAS FUNCIONALIDADES IMPLEMENTADAS

### 1. **Usuários Filhos (Sub-usuários)**
Permite que o Super Admin crie usuários filhos para cada empresa. Esses usuários têm login próprio mas acessam a mesma agenda da empresa pai.

### 2. **Envio em Massa de WhatsApp**
Permite enviar mensagens para múltiplos clientes de uma vez, com intervalo de 5 segundos entre cada envio para evitar bloqueios.

---

## 📂 ARQUIVOS NOVOS (Criar no servidor)

### Migrations:
```
database/migrations/2025_11_10_194524_add_usuario_pai_id_to_users_table.php
database/migrations/2025_11_10_194933_create_mass_messages_table.php
```

### Models:
```
app/Models/MassMessage.php
app/Models/MassMessageItem.php
```

### Jobs:
```
app/Jobs/SendMassMessageJob.php
```

---

## 📝 ARQUIVOS MODIFICADOS (Substituir no servidor)

### Controllers:
```
app/Http/Controllers/SuperAdminController.php
app/Http/Controllers/ClienteController.php
app/Http/Controllers/AppointmentController.php
```

### Middleware:
```
app/Http/Middleware/EnsureWhatsAppSetupCompleted.php
```

### Services:
```
app/Services/WhatsAppService.php
```

### Models:
```
app/Models/User.php
```

### Views:
```
resources/views/super-admin/empresas/detalhes.blade.php
resources/views/clientes/index.blade.php
resources/views/agenda/index.blade.php
```

### Routes:
```
routes/super-admin-routes.php
routes/web.php
```

---

## 🔧 COMANDOS PARA EXECUTAR NO SERVIDOR

### 1. Rodar as migrations:
```bash
php artisan migrate
```

### 2. Limpar cache (recomendado):
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 3. Configurar Queue (se ainda não configurado):

**Opção A - Usando Supervisor (recomendado para produção):**

Criar arquivo `/etc/supervisor/conf.d/laravel-worker.conf`:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /caminho/completo/para/seu/projeto/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=seu-usuario
numprocs=1
redirect_stderr=true
stdout_logfile=/caminho/completo/para/seu/projeto/storage/logs/worker.log
stopwaitsecs=3600
```

Após criar o arquivo:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

**Opção B - Usando Cron (alternativa simples):**

Adicionar ao crontab (`crontab -e`):
```cron
* * * * * cd /caminho/completo/para/seu/projeto && php artisan schedule:run >> /dev/null 2>&1
* * * * * cd /caminho/completo/para/seu/projeto && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

### 4. Configurar .env (se necessário):

Adicionar/verificar as seguintes variáveis:
```env
QUEUE_CONNECTION=database
```

Se preferir usar Redis (mais performático):
```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## ✅ CHECKLIST DE DEPLOY

- [ ] Fazer backup do banco de dados
- [ ] Fazer backup dos arquivos atuais
- [ ] Enviar os arquivos novos via FTP/SFTP
- [ ] Substituir os arquivos modificados
- [ ] Executar `php artisan migrate`
- [ ] Executar `php artisan config:clear`
- [ ] Executar `php artisan route:clear`
- [ ] Executar `php artisan view:clear`
- [ ] Configurar supervisor ou cron para queue
- [ ] Verificar se `.env` tem `QUEUE_CONNECTION` configurado
- [ ] Testar criação de usuário filho no Super Admin
- [ ] Testar envio em massa na página de clientes

---

## 🧪 COMO TESTAR APÓS DEPLOY

### Testar Usuários Filhos:
1. Fazer login como Super Admin
2. Ir em "Empresas" > Selecionar uma empresa
3. Scroll até a seção "Usuários Filhos"
4. Clicar em "Adicionar Usuário"
5. Preencher: nome, email, senha
6. Salvar
7. Fazer logout
8. Tentar fazer login com o email e senha do usuário filho
9. Verificar se acessa a mesma agenda da empresa pai

### Testar Envio em Massa:
1. Fazer login como empresa (não Super Admin)
2. Ir em "Clientes"
3. Marcar checkbox de 2-3 clientes (que tenham WhatsApp)
4. Clicar no botão "Enviar para X Cliente(s)"
5. Digitar uma mensagem de teste
6. Clicar em "Enviar Mensagens"
7. Verificar mensagem de sucesso
8. **IMPORTANTE:** Verificar os logs para confirmar envios:
   ```bash
   tail -f storage/logs/laravel.log
   ```
9. Os clientes devem receber as mensagens com intervalo de 5 segundos entre cada uma

---

## 🚨 TROUBLESHOOTING

### Erro: "Queue connection not configured"
**Solução:** Adicionar `QUEUE_CONNECTION=database` no `.env` e rodar `php artisan config:clear`

### Erro: "SendMassMessageJob not found"
**Solução:** Verificar se o arquivo `app/Jobs/SendMassMessageJob.php` foi enviado corretamente

### Mensagens não estão sendo enviadas:
**Problema:** Queue worker não está rodando
**Solução:**
- Verificar se supervisor está ativo: `sudo supervisorctl status`
- OU rodar manualmente: `php artisan queue:work`
- Verificar logs: `tail -f storage/logs/laravel.log`

### Erro: "Column usuario_pai_id not found"
**Solução:** Rodar as migrations: `php artisan migrate`

### Erro: "Table mass_messages not found"
**Solução:** Rodar as migrations: `php artisan migrate`

---

## 📊 ESTRUTURA DO BANCO DE DADOS (Novos campos/tabelas)

### Tabela `users` (campo adicionado):
- `usuario_pai_id` (bigint, nullable) - ID do usuário pai (para sub-usuários)

### Tabela `mass_messages` (nova):
- `id` (bigint, auto)
- `user_id` (bigint) - Empresa que enviou
- `mensagem` (text) - Conteúdo
- `total_destinatarios` (int) - Total de clientes
- `enviados` (int) - Quantos foram enviados
- `falhas` (int) - Quantos falharam
- `status` (enum: pendente/processando/concluido/erro)
- `iniciado_em` (timestamp, nullable)
- `concluido_em` (timestamp, nullable)
- `created_at`, `updated_at`

### Tabela `mass_message_items` (nova):
- `id` (bigint, auto)
- `mass_message_id` (bigint) - FK para mass_messages
- `cliente_id` (bigint) - FK para users
- `telefone` (string) - WhatsApp do cliente
- `status` (enum: pendente/enviado/erro)
- `erro_mensagem` (text, nullable)
- `enviado_em` (timestamp, nullable)
- `created_at`, `updated_at`

---

## 💡 OBSERVAÇÕES IMPORTANTES

1. **Queue Worker é ESSENCIAL:** Sem ele, o envio em massa não funciona. As mensagens ficam pendentes.

2. **Intervalo de 5 segundos:** Configurado no Job para evitar bloqueio do WhatsApp. NÃO ALTERAR.

3. **Usuários Filhos:**
   - Herdam todas as configurações da empresa pai (plano, acesso, credenciais WhatsApp)
   - Podem fazer login com suas próprias credenciais
   - **SÃO** do tipo 'empresa' (não são clientes, são funcionários/colaboradores)
   - **IMPORTANTE:** Já nascem com `apibrasil_setup_completed = true` e NÃO são redirecionados para tela de setup
   - Usam automaticamente as credenciais WhatsApp do pai (sem precisar configurar)
   - **Permissões:**
     - ✅ Podem criar compromissos
     - ✅ Podem editar compromissos
     - ❌ **NÃO** podem deletar compromissos
     - ✅ Podem criar clientes
     - ✅ Podem editar clientes
     - ❌ **NÃO** podem deletar clientes
     - ✅ Podem enviar mensagens individuais
     - ✅ Podem enviar mensagens em massa

4. **Envio em Massa:**
   - Só envia para clientes que TÊM WhatsApp cadastrado
   - Clientes sem WhatsApp são ignorados automaticamente
   - Checkbox não aparece para clientes sem WhatsApp
   - Limite de 1000 caracteres por mensagem

5. **Segurança:**
   - Super Admin: Apenas super admin pode criar usuários filhos
   - Empresas: Apenas veem seus próprios clientes para envio em massa
   - Isolamento: Empresa A não pode enviar para clientes da Empresa B

---

**Data da Implementação:** 10/11/2025
**Versão do Sistema:** 1.1
**Stack:** Laravel 11 + MySQL + Queue (Database/Redis)
