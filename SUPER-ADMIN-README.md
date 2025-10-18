# 👑 Sistema de Super Admin - Documentação Completa

## 📋 Índice
1. [Visão Geral](#visão-geral)
2. [Instalação](#instalação)
3. [Funcionalidades](#funcionalidades)
4. [Como Usar](#como-usar)
5. [Estrutura de Arquivos](#estrutura-de-arquivos)

---

## 🎯 Visão Geral

O sistema de Super Admin permite gerenciar todas as empresas cadastradas na plataforma, incluindo:

- ✅ Dashboard com estatísticas em tempo real
- ✅ Gerenciamento completo de empresas
- ✅ Controle de acesso por período (trial/pagos)
- ✅ Monitoramento de uso e requisições
- ✅ Gráficos e relatórios detalhados
- ✅ Sistema de planos (trial, mensal, trimestral, semestral, anual)

---

## 🚀 Instalação

### 1️⃣ Rodar as Migrations

```bash
php artisan migrate
```

Isso criará os seguintes campos na tabela `users`:
- `is_super_admin` - Identifica se é super admin
- `acesso_liberado_ate` - Data limite de acesso
- `acesso_ativo` - Status do acesso
- `total_requisicoes` - Total de requisições feitas
- `requisicoes_mes_atual` - Requisições do mês
- `plano` - Tipo de plano contratado
- `limite_requisicoes_mes` - Limite mensal
- `valor_pago` - Valor do último pagamento
- `data_ultimo_pagamento` - Data do pagamento
- `observacoes_admin` - Notas internas

### 2️⃣ Criar o Primeiro Super Admin

```bash
php artisan db:seed --class=SuperAdminSeeder
```

**Credenciais padrão:**
- Email: `admin@sistema.com`
- Senha: `admin123`

⚠️ **IMPORTANTE:** Altere a senha após o primeiro login!

### 3️⃣ Incluir as Rotas no web.php

Adicione ao final do arquivo `routes/web.php`:

```php
// Super Admin Routes
require __DIR__.'/super-admin-routes.php';
```

### 4️⃣ Limpar Caches

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

---

## ⚙️ Funcionalidades

### 📊 Dashboard

Acesse: `/super-admin`

O dashboard exibe:
- Total de empresas cadastradas
- Empresas ativas
- Acessos vencidos
- Total de requisições do mês
- Gráfico de empresas por plano
- Gráfico de requisições dos últimos 6 meses
- Lista das 10 empresas mais recentes

### 🏢 Gerenciamento de Empresas

Acesse: `/super-admin/empresas`

Recursos:
- **Filtros avançados:**
  - Buscar por nome, email ou WhatsApp
  - Filtrar por status (ativas, vencidas, bloqueadas)
  - Filtrar por plano

- **Ações disponíveis:**
  - Ver detalhes completos
  - Editar informações
  - Bloquear/Liberar acesso
  - Liberar trial por X dias
  - Resetar contador de requisições
  - Deletar empresa

### 📈 Detalhes da Empresa

Acesse: `/super-admin/empresas/{id}`

Visualize:
- Informações gerais
- Total de compromissos
- Compromissos confirmados/cancelados
- Total de clientes
- Mensagens dos últimos 30 dias
- Gráfico de requisições diárias
- Uso de requisições (barra de progresso)

### ✏️ Editar Empresa

Acesse: `/super-admin/empresas/{id}/editar`

Campos editáveis:
- Nome da empresa
- Email
- WhatsApp
- Plano contratado
- Status de acesso (ativo/bloqueado)
- Data de vencimento
- Limite de requisições/mês
- Valor pago
- Observações internas

---

## 💡 Como Usar

### Liberar Trial para Cliente

1. Acesse os detalhes da empresa
2. Clique em "Liberar Trial"
3. Escolha o número de dias (ex: 3 dias)
4. Confirme

O sistema automaticamente:
- Ativa o acesso
- Define a data de vencimento
- Muda o plano para "trial"
- Registra nas observações

### Bloquear Acesso de uma Empresa

1. Acesse os detalhes da empresa
2. Clique em "Bloquear Acesso"
3. Confirme

A empresa não poderá mais usar o sistema até ser desbloqueada.

### Resetar Contador de Requisições

Útil quando uma empresa esgota o limite antes do fim do mês:

1. Acesse os detalhes da empresa
2. Clique em "Resetar Requisições"
3. Confirme

O contador volta para 0.

### Monitorar Uso

No dashboard ou na listagem de empresas, você pode ver:
- Barra de progresso de uso
- Percentual usado
- Requisições atuais vs. limite

Cores da barra:
- 🟢 Verde: < 50%
- 🟡 Amarelo: 50-80%
- 🔴 Vermelho: > 80%

---

## 📁 Estrutura de Arquivos

### Backend

```
app/
├── Http/
│   ├── Controllers/
│   │   └── SuperAdminController.php
│   └── Middleware/
│       └── EnsureSuperAdmin.php
├── Models/
│   └── User.php (atualizado)

database/
├── migrations/
│   └── 2025_01_19_000001_add_super_admin_fields_to_users_table.php
└── seeders/
    └── SuperAdminSeeder.php

routes/
└── super-admin-routes.php
```

### Frontend

```
resources/
└── views/
    └── super-admin/
        ├── layouts/
        │   └── app.blade.php
        ├── empresas/
        │   ├── index.blade.php
        │   ├── detalhes.blade.php
        │   └── editar.blade.php
        └── dashboard.blade.php
```

---

## 🎨 Design

O painel usa:
- **Bootstrap 5.3** - Framework CSS
- **Font Awesome 6.4** - Ícones
- **Chart.js 4.4** - Gráficos interativos

**Tema:**
- Sidebar azul escuro
- Cards brancos com sombras suaves
- Gradientes coloridos nos ícones
- Design responsivo

---

## 🔐 Segurança

### Middleware

O acesso ao painel de super admin é protegido por 2 middlewares:
1. **auth** - Usuário deve estar autenticado
2. **super.admin** - Usuário deve ter `is_super_admin = true`

### Permissões

Apenas usuários com `is_super_admin = true` podem:
- Acessar `/super-admin/*`
- Ver todas as empresas
- Editar qualquer empresa
- Bloquear/liberar acessos
- Ver estatísticas globais

---

## 📊 Planos Disponíveis

| Plano | Descrição |
|-------|-----------|
| **trial** | Período de teste gratuito |
| **mensal** | Cobrança mensal |
| **trimestral** | Cobrança a cada 3 meses |
| **semestral** | Cobrança a cada 6 meses |
| **anual** | Cobrança anual |

---

## 🎯 Próximas Melhorias Sugeridas

- [ ] Sistema de notificações para acessos vencendo
- [ ] Relatório de receita mensal
- [ ] Export de dados em Excel/CSV
- [ ] Gráficos de crescimento mensal
- [ ] Sistema de tickets/suporte
- [ ] Integração com gateway de pagamento
- [ ] Logs de ações do super admin
- [ ] Dashboard personalizado por empresa

---

## 🆘 Suporte

Para dúvidas ou problemas:
1. Verifique os logs em `storage/logs/laravel.log`
2. Confirme que as migrations foram rodadas
3. Certifique-se de ter um usuário com `is_super_admin = true`

---

## 📝 Changelog

### Versão 1.0.0 (Janeiro 2025)
- ✅ Sistema completo de super admin
- ✅ Dashboard com estatísticas
- ✅ Gerenciamento de empresas
- ✅ Sistema de planos e trials
- ✅ Gráficos interativos
- ✅ Controle de requisições

---

**Desenvolvido para o Sistema de Agenda Digital** 🚀
