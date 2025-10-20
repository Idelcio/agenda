# Sistema de Assinatura com Mercado Pago

Sistema completo de assinatura com pagamento via Mercado Pago integrado ao Laravel.

## Funcionalidades Implementadas

- ✅ Criação de assinaturas (Mensal, Trimestral, Semestral e Anual)
- ✅ Pagamento via Checkout Pro do Mercado Pago (cartão de crédito)
- ✅ Webhook para receber notificações de pagamento
- ✅ Ativação automática de assinatura após pagamento aprovado
- ✅ Middleware para bloquear acesso sem assinatura ativa
- ✅ Histórico de assinaturas e pagamentos
- ✅ Cancelamento de assinatura

## Estrutura Criada

### Migrations
- `create_subscriptions_table` - Tabela de assinaturas
- `create_payments_table` - Tabela de pagamentos

### Models
- `Subscription` - Gerencia assinaturas com métodos úteis
- `Payment` - Gerencia pagamentos

### Services
- `MercadoPagoService` - Toda lógica de integração com Mercado Pago

### Controllers
- `SubscriptionController` - Endpoints de gerenciamento de assinatura
- `WebhookController` - Recebe notificações do Mercado Pago

### Middleware
- `CheckSubscription` - Bloqueia acesso sem assinatura ativa

## Planos Disponíveis

| Plano | Duração | Preço Total | Preço Mensal Efetivo | Desconto |
|-------|---------|-------------|---------------------|----------|
| Mensal | 1 mês | R$ 59,90 | R$ 59,90 | - |
| Trimestral | 3 meses | R$ 159,90 | R$ 53,30 | ~11% |
| Semestral | 6 meses | R$ 299,90 | R$ 49,98 | ~17% |
| Anual | 12 meses | R$ 549,90 | R$ 45,82 | ~24% |

Os valores podem ser alterados em: `config/mercadopago.php`

## API Endpoints

### 1. Listar Planos Disponíveis
```http
GET /api/subscription/plans
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "success": true,
  "plans": {
    "monthly": {
      "name": "Plano Mensal",
      "description": "Acesso completo por 1 mês",
      "price": 49.90,
      "duration_months": 1
    },
    ...
  }
}
```

### 2. Criar Nova Assinatura (Gera Link de Pagamento)
```http
POST /api/subscription/create
Authorization: Bearer {token}
Content-Type: application/json

{
  "plan_type": "monthly"
}
```

**Valores válidos para `plan_type`:**
- `monthly` - Plano Mensal
- `quarterly` - Plano Trimestral
- `semiannual` - Plano Semestral
- `annual` - Plano Anual

**Resposta:**
```json
{
  "success": true,
  "subscription_id": 1,
  "checkout_url": "https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=XXX",
  "sandbox_url": "https://sandbox.mercadopago.com.br/checkout/v1/redirect?pref_id=XXX"
}
```

**O que fazer com a resposta:**
1. Redirecione o usuário para a `checkout_url` (produção) ou `sandbox_url` (testes)
2. O usuário será levado para a página de pagamento do Mercado Pago
3. Após o pagamento, o usuário será redirecionado para as URLs configuradas
4. O webhook será chamado automaticamente e a assinatura será ativada

### 3. Consultar Assinatura Atual
```http
GET /api/subscription/current
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "success": true,
  "subscription": {
    "id": 1,
    "plan_type": "monthly",
    "amount": "49.90",
    "status": "active",
    "starts_at": "2025-10-19 12:00:00",
    "expires_at": "2025-11-19 12:00:00",
    "is_active": true
  }
}
```

### 4. Histórico de Assinaturas
```http
GET /api/subscription/history
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "success": true,
  "subscriptions": [
    {
      "id": 1,
      "plan_type": "monthly",
      "amount": "49.90",
      "status": "active",
      "starts_at": "2025-10-19 12:00:00",
      "expires_at": "2025-11-19 12:00:00",
      "created_at": "2025-10-19 11:30:00",
      "payments": [
        {
          "id": 1,
          "status": "approved",
          "amount": "49.90",
          "payment_method": "credit_card",
          "approved_at": "2025-10-19 12:00:00"
        }
      ]
    }
  ]
}
```

### 5. Cancelar Assinatura
```http
POST /api/subscription/cancel
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "success": true,
  "message": "Assinatura cancelada com sucesso"
}
```

### 6. Webhook do Mercado Pago
```http
POST /api/webhook/mercadopago
```

**Este endpoint é chamado automaticamente pelo Mercado Pago.**
Não requer autenticação. O sistema processa automaticamente:
- Pagamentos aprovados → Ativa assinatura
- Pagamentos rejeitados → Cancela assinatura
- Outros status → Registra no log

## Fluxo Completo de Uso

### 1. Após Registro do Usuário
```javascript
// Frontend - Após usuário se registrar
// Redirecionar para tela de escolha de plano
window.location.href = '/choose-plan';
```

### 2. Escolher Plano e Criar Assinatura
```javascript
// Frontend - Usuário escolhe um plano
const response = await fetch('/api/subscription/create', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    plan_type: 'monthly' // ou 'quarterly', 'semiannual', 'annual'
  })
});

const data = await response.json();

if (data.success) {
  // Redirecionar para página de pagamento do Mercado Pago
  window.location.href = data.checkout_url;
}
```

### 3. Usuário Paga no Mercado Pago
- O usuário será redirecionado para a página do Mercado Pago
- Preenche os dados do cartão e confirma o pagamento
- Mercado Pago processa o pagamento

### 4. Webhook é Chamado Automaticamente
- Mercado Pago chama `/api/webhook/mercadopago`
- Sistema busca os dados do pagamento
- Se aprovado, ativa a assinatura do usuário
- Usuário já pode usar o sistema

### 5. Verificar se Usuário Tem Assinatura
```javascript
// Frontend - Ao fazer login
const response = await fetch('/api/subscription/current', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const data = await response.json();

if (!data.success) {
  // Não tem assinatura ativa, redirecionar para escolher plano
  window.location.href = '/choose-plan';
} else {
  // Tem assinatura ativa, pode usar o sistema
  window.location.href = '/dashboard';
}
```

## Proteger Rotas com Middleware

Para proteger rotas que só usuários com assinatura podem acessar:

### No arquivo `routes/api.php`:
```php
// Rotas que REQUEREM assinatura ativa
Route::middleware(['auth:sanctum', 'subscription'])->group(function () {
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    // ... outras rotas protegidas
});
```

**Resposta quando não tem assinatura:**
```json
{
  "success": false,
  "message": "Você precisa ter uma assinatura ativa para acessar este recurso",
  "requires_subscription": true
}
```

## Configuração do Webhook no Mercado Pago

1. Acesse: https://www.mercadopago.com.br/developers/panel
2. Vá em: **Suas integrações** → Sua aplicação → **Webhooks**
3. Configure o webhook:
   - **URL de produção:** `https://treinoeenergia.com.br/webhook/mercadopago`
   - **Eventos:** Marque `payment`

**IMPORTANTE:** A URL do webhook DEVE ser pública (acessível pela internet).

## Testar Webhooks Localmente

Para testar webhooks em ambiente local, use o ngrok:

```bash
# Instalar ngrok (se não tiver)
# https://ngrok.com/download

# Expor seu servidor local
ngrok http 8000

# Você receberá uma URL como: https://abc123.ngrok.io
# Configure no Mercado Pago: https://abc123.ngrok.io/api/webhook/mercadopago
```

## Cartões de Teste (Ambiente Sandbox)

Para testar pagamentos use esses cartões:

| Bandeira | Número | CVV | Validade | Resultado |
|----------|--------|-----|----------|-----------|
| Mastercard | 5031 4332 1540 6351 | 123 | 11/25 | Aprovado |
| Visa | 4235 6477 2802 5682 | 123 | 11/25 | Aprovado |
| Mastercard | 5031 7557 3453 0604 | 123 | 11/25 | Recusado |

**Nome do titular:** APRO (aprovado) ou OTHE (recusado)
**CPF:** Qualquer CPF válido

## Logs e Debug

Todos os eventos do webhook são registrados em:
```
storage/logs/laravel.log
```

Para ver os logs:
```bash
tail -f storage/logs/laravel.log
```

## Verificar Assinatura em Qualquer Lugar

```php
// Em qualquer controller ou service
use App\Services\MercadoPagoService;

$mercadoPagoService = app(MercadoPagoService::class);

// Verificar se usuário tem assinatura ativa
if ($mercadoPagoService->hasActiveSubscription($userId)) {
    // Usuário pode acessar
} else {
    // Bloquear acesso
}

// Ou usando o Model User
$user = Auth::user();
if ($user->hasActiveSubscription()) {
    // Usuário pode acessar
}
```

## Alterar Preços dos Planos

Edite o arquivo: `config/mercadopago.php`

```php
'plans' => [
    'monthly' => [
        'name' => 'Plano Mensal',
        'description' => 'Acesso completo por 1 mês',
        'price' => 49.90, // ALTERE AQUI
        'duration_months' => 1,
    ],
    // ...
],
```

## Status de Assinatura

| Status | Descrição |
|--------|-----------|
| `pending` | Aguardando pagamento |
| `active` | Assinatura ativa e válida |
| `cancelled` | Assinatura cancelada pelo usuário |
| `expired` | Assinatura expirada (venceu) |

## Status de Pagamento

| Status | Descrição |
|--------|-----------|
| `pending` | Aguardando pagamento |
| `approved` | Pagamento aprovado |
| `authorized` | Pagamento autorizado |
| `in_process` | Em processamento |
| `in_mediation` | Em mediação |
| `rejected` | Pagamento rejeitado |
| `cancelled` | Pagamento cancelado |
| `refunded` | Pagamento estornado |
| `charged_back` | Chargeback |

## Troubleshooting

### Webhook não está sendo chamado
1. Verifique se a URL está correta no painel do Mercado Pago
2. Certifique-se que a URL é HTTPS
3. Teste a URL: `curl -X POST https://seusite.com/api/webhook/mercadopago`

### Assinatura não ativa após pagamento
1. Verifique os logs: `tail -f storage/logs/laravel.log`
2. Veja se o webhook foi chamado
3. Verifique se o Access Token está correto

### Erro ao criar preference
1. Verifique se o Access Token está correto
2. Veja os logs para mais detalhes
3. Confirme que você tem saldo em conta de teste (ambiente sandbox)

## Segurança

- ✅ Webhook não requer autenticação (Mercado Pago não envia tokens)
- ✅ Todos os logs são registrados para auditoria
- ✅ Credenciais armazenadas em variáveis de ambiente
- ✅ Middleware verifica assinatura em tempo real

## Próximos Passos

1. **Frontend:** Criar páginas para:
   - Escolha de plano após registro
   - Página de sucesso/erro após pagamento
   - Dashboard de assinatura do usuário

2. **Email:** Enviar emails quando:
   - Pagamento for aprovado
   - Assinatura estiver próxima do vencimento
   - Assinatura expirar

3. **Renovação:** Implementar renovação automática

4. **Testes:** Testar todos os cenários com cartões de teste

## Suporte

- Documentação Mercado Pago: https://www.mercadopago.com.br/developers
- Painel Mercado Pago: https://www.mercadopago.com.br/developers/panel
- Postman Collection: https://www.mercadopago.com.br/developers/pt/docs/checkout-pro/integrate-preferences
