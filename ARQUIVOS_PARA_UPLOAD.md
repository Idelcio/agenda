# 📦 Arquivos para Upload Manual

## 🎯 PRIORIDADE MÁXIMA (Correção de timezone)

### ✅ Obrigatório - Corrige horário dos lembretes:
- **app/Models/Appointment.php** ⚠️ CRÍTICO

---

## 📋 FUNCIONALIDADES COMPLETAS (Mensagens Prontas + Correções)

Se o servidor ainda não tem as mensagens prontas, envie também:

### 🆕 Novos arquivos (criar no servidor):
1. **app/Http/Controllers/QuickMessageTemplateController.php**
2. **app/Http/Requests/StoreWhatsAppMessageTemplateRequest.php**
3. **app/Models/WhatsAppMessageTemplate.php**
4. **database/migrations/2025_10_27_085252_create_whats_app_message_templates_table.php**

### 📝 Arquivos modificados (substituir):
5. **app/Http/Controllers/AppointmentController.php**
6. **app/Models/User.php**
7. **app/Services/WhatsAppService.php**
8. **resources/views/agenda/index.blade.php**
9. **resources/views/agenda/partials/form.blade.php**
10. **routes/web.php**

---

## 🚀 Comandos pós-upload (OBRIGATÓRIO)

Após fazer upload dos arquivos, execute no servidor:

```bash
# 1. Rodar migration (se enviou novos arquivos)
php artisan migrate --force

# 2. Limpar TODOS os caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# 3. Recriar cache de rotas
php artisan route:cache

# 4. Verificar se rotas foram criadas
php artisan route:list | grep quick-messages
```

---

## ✅ Verificação final

### Teste 1: Timezone corrigido
```bash
grep -A 3 "scopeDueForReminder" app/Models/Appointment.php
```

Deve mostrar:
```php
public function scopeDueForReminder($query)
{
    // Pega a hora atual no timezone da aplicação e converte para UTC
    $nowUtc = now()->setTimezone('UTC');
```

### Teste 2: Mensagens prontas funcionando
```bash
php artisan route:list | grep quick-messages
```

Deve mostrar 3 rotas:
- POST agenda/quick-messages
- PATCH agenda/quick-messages/{template}
- DELETE agenda/quick-messages/{template}

---

## 📊 Resumo

| Situação | Arquivos necessários |
|----------|---------------------|
| **Só corrigir timezone** | Apenas `app/Models/Appointment.php` |
| **Funcionalidade completa** | Todos os 10 arquivos listados acima |

---

## ⚠️ IMPORTANTE

Sempre faça backup antes de substituir arquivos:
```bash
cp app/Models/Appointment.php app/Models/Appointment.php.backup
```
