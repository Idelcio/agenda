@extends('super-admin.layouts.app')

@section('title', 'Gerenciar Planos')
@section('page-title', 'Planos de Assinatura')
@section('page-subtitle', 'Gerencie os preços e descontos dos planos')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-tags text-primary"></i> Planos Disponíveis
                        </h5>
                        <span class="badge bg-info">{{ count($plans) }} planos</span>
                    </div>
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle"></i> Os preços e descontos podem ser ajustados abaixo. As
                        alterações são salvas em <code>storage/app/plans.json</code>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach ($plans as $slug => $plan)
            @php
                $finalPrice = $plan['price'];
                if (isset($plan['discount_percent']) && $plan['discount_percent'] > 0) {
                    $finalPrice = $plan['price'] * (1 - $plan['discount_percent'] / 100);
                }
                $pricePerMonth = $finalPrice / ($plan['duration_months'] ?? 1);
            @endphp
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm border-0"
                    style="transition: transform 0.3s; border-top: 4px solid #1e40af !important;">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                                style="width: 70px; height: 70px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <i class="fas fa-box-open text-white" style="font-size: 28px;"></i>
                            </div>
                            <h5 class="card-title mb-1">{{ $plan['name'] }}</h5>
                            <p class="text-muted small mb-2">{{ $plan['description'] }}</p>
                            <span class="badge bg-secondary">{{ $plan['duration_months'] }}
                                {{ $plan['duration_months'] == 1 ? 'mês' : 'meses' }}</span>
                        </div>

                        <div class="text-center mb-3 py-3 bg-light rounded">
                            @if (isset($plan['discount_percent']) && $plan['discount_percent'] > 0)
                                <div class="mb-1">
                                    <span class="text-decoration-line-through text-muted"
                                        style="font-size: 18px;">R$
                                        {{ number_format($plan['price'], 2, ',', '.') }}</span>
                                    <span class="badge bg-success ms-2">-{{ $plan['discount_percent'] }}%</span>
                                </div>
                                <div>
                                    <span class="h3 mb-0 text-primary fw-bold">
                                        R$ {{ number_format($finalPrice, 2, ',', '.') }}
                                    </span>
                                </div>
                            @else
                                <div>
                                    <span class="h3 mb-0 text-primary fw-bold">
                                        R$ {{ number_format($plan['price'], 2, ',', '.') }}
                                    </span>
                                </div>
                            @endif
                            <div class="mt-2">
                                <small class="text-muted">
                                    R$ {{ number_format($pricePerMonth, 2, ',', '.') }}/mês
                                </small>
                            </div>
                        </div>

                        <div class="d-grid">
                            <a href="{{ route('super-admin.planos.editar', $slug) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Editar Preços
                            </a>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top-0">
                        <small class="text-muted">
                            <i class="fas fa-code"></i> Slug: <code>{{ $slug }}</code>
                        </small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Informações adicionais -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info">
                <h6 class="alert-heading">
                    <i class="fas fa-lightbulb"></i> Como funciona
                </h6>
                <ul class="mb-0 small">
                    <li>Os planos base são definidos em <code>config/mercadopago.php</code></li>
                    <li>As alterações de preço e desconto são salvas em <code>storage/app/plans.json</code> e têm
                        prioridade sobre o config</li>
                    <li>O sistema utiliza o <strong>PlanService</strong> para mesclar as configurações</li>
                    <li>As alterações são aplicadas imediatamente sem necessidade de reiniciar o servidor</li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }
    </style>
@endsection
