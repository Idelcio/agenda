# 🚀 Deploy do Super Admin - Passo a Passo

## 📦 Arquivos para Upload

Faça upload dos seguintes arquivos para o servidor:

### 1. Backend

```
app/Http/Controllers/SuperAdminController.php
app/Http/Middleware/EnsureSuperAdmin.php
app/Http/Kernel.php (ATUALIZADO)
app/Console/Commands/SetSuperAdmin.php

database/migrations/2025_01_19_000001_add_super_admin_fields_to_users_table.php
database/seeders/SuperAdminSeeder.php
database/seeders/SetIdelcioAsSuperAdmin.php

routes/web.php (ATUALIZADO)
routes/super-admin-routes.php
```

### 2. Frontend

```
resources/views/super-admin/layouts/app.blade.php
resources/views/super-admin/dashboard.blade.php
resources/views/super-admin/empresas/index.blade.php
resources/views/super-admin/empresas/detalhes.blade.php
resources/views/super-admin/empresas/editar.blade.php
```

---

## ⚙️ Comandos para Rodar no Servidor

Conecte-se ao servidor via SSH e rode os seguintes comandos:

### 1. Entrar no diretório do projeto

```bash
cd /home/u433815487/domains/treinoeenergia.com.br
```

### 2. Rodar a migration (criar campos no banco)

```bash
php artisan migrate --force
```

### 3. Tornar o Idelcio super admin

```bash
php artisan db:seed --class=SetIdelcioAsSuperAdmin
```

**OU use o comando:**

```bash
php artisan user:set-super-admin idelcioforest@gmail.com
```

### 4. Limpar todos os caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 5. Recriar o cache de rotas (produção)

```bash
php artisan route:cache
php artisan config:cache
```

---

## ✅ Verificar se funcionou

1. Acesse: `https://treinoeenergia.com.br/super-admin`

2. Faça login com:
   - Email: `idelcioforest@gmail.com`
   - Senha: (sua senha atual)

3. Você deve ver o dashboard do super admin!

---

## 🐛 Troubleshooting

### Erro 404

Se der erro 404, rode:

```bash
php artisan route:clear
php artisan route:cache
php artisan route:list | grep super-admin
```

O último comando deve mostrar as rotas do super admin.

### Erro 500

Veja os logs:

```bash
tail -n 100 storage/logs/laravel.log
```

### Erro de Migration

Se a migration já foi rodada antes:

```bash
php artisan migrate:status
```

Se aparecer como já rodada, pule o passo 2.

### Verificar se o usuário é super admin

```bash
php artisan tinker
```

Dentro do tinker:

```php
$user = User::where('email', 'idelcioforest@gmail.com')->first();
$user->is_super_admin; // deve retornar true
exit
```

---

## 📝 Verificar Permissões

Certifique-se que as permissões estão corretas:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

(Ajuste o usuário conforme seu servidor)

---

## 🎯 Pós-Deploy

Após fazer login como super admin:

1. ✅ Verifique o dashboard
2. ✅ Acesse a lista de empresas
3. ✅ Teste editar uma empresa
4. ✅ Teste liberar um trial de 3 dias
5. ✅ Verifique os gráficos

---

## 🔐 Segurança

**IMPORTANTE:** Após o deploy, certifique-se de:

- [ ] Usar HTTPS (certificado SSL)
- [ ] Não compartilhar o acesso de super admin
- [ ] Fazer backup do banco de dados antes de mudanças críticas
- [ ] Revisar os logs regularmente

---

## 📞 Suporte

Se algo der errado:

1. Verifique os logs: `storage/logs/laravel.log`
2. Verifique se todos os arquivos foram enviados
3. Confirme que a migration foi rodada
4. Teste com `php artisan route:list`

---

**Boa sorte com o deploy! 🚀**
