@extends('super-admin.layouts.app')

@section('title', 'Editar Plano')
@section('page-title', 'Editar Plano')
@section('page-subtitle', $plan['name'])

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Card de Informações do Plano -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Informações do Plano
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nome:</strong> {{ $plan['name'] }}</p>
                            <p><strong>Descrição:</strong> {{ $plan['description'] }}</p>
                            <p><strong>Slug:</strong> <code>{{ $plan['slug'] }}</code></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Duração:</strong> {{ $plan['duration_months'] }}
                                {{ $plan['duration_months'] == 1 ? 'mês' : 'meses' }}</p>
                            <p><strong>Preço Atual:</strong>
                                <span class="text-primary fw-bold">R$
                                    {{ number_format($plan['price'], 2, ',', '.') }}</span>
                            </p>
                            <p><strong>Desconto Atual:</strong>
                                <span class="badge bg-success">{{ $plan['discount_percent'] ?? 0 }}%</span>
                            </p>
                        </div>
                    </div>

                    @php
                        $finalPrice = $plan['price'];
                        if (isset($plan['discount_percent']) && $plan['discount_percent'] > 0) {
                            $finalPrice = $plan['price'] * (1 - $plan['discount_percent'] / 100);
                        }
                        $pricePerMonth = $finalPrice / $plan['duration_months'];
                    @endphp

                    <div class="alert alert-info mt-3 mb-0">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <strong>Preço Base</strong><br>
                                <span class="h5">R$ {{ number_format($plan['price'], 2, ',', '.') }}</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Preço Final</strong><br>
                                <span class="h5 text-primary">R$ {{ number_format($finalPrice, 2, ',', '.') }}</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Por Mês</strong><br>
                                <span class="h5 text-success">R$ {{ number_format($pricePerMonth, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulário de Edição -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit"></i> Editar Valores
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('super-admin.planos.atualizar', $plan['slug']) }}" method="POST"
                        id="editPlanForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">
                                    <i class="fas fa-dollar-sign"></i> Preço (R$) *
                                </label>
                                <input type="number" class="form-control @error('price') is-invalid @enderror"
                                    id="price" name="price" step="0.01" min="0"
                                    value="{{ old('price', $plan['price']) }}" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Digite o valor em reais (ex: 59.90)
                                </small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="discount_percent" class="form-label">
                                    <i class="fas fa-percent"></i> Desconto (%) *
                                </label>
                                <input type="number" class="form-control @error('discount_percent') is-invalid @enderror"
                                    id="discount_percent" name="discount_percent" min="0" max="100"
                                    value="{{ old('discount_percent', $plan['discount_percent'] ?? 0) }}" required>
                                @error('discount_percent')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Desconto em porcentagem (0 a 100)
                                </small>
                            </div>
                        </div>

                        <!-- Preview do Cálculo -->
                        <div class="alert alert-light border" id="pricePreview">
                            <h6 class="mb-3"><i class="fas fa-calculator"></i> Preview do Cálculo</h6>
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <small class="text-muted">Preço Base</small><br>
                                    <strong id="preview-base">R$ {{ number_format($plan['price'], 2, ',', '.') }}</strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Desconto</small><br>
                                    <strong id="preview-discount"
                                        class="text-danger">-{{ $plan['discount_percent'] ?? 0 }}%</strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Preço Final</small><br>
                                    <strong id="preview-final"
                                        class="text-primary">R$ {{ number_format($finalPrice, 2, ',', '.') }}</strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Por Mês</small><br>
                                    <strong id="preview-month"
                                        class="text-success">R$ {{ number_format($pricePerMonth, 2, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between pt-3">
                            <a href="{{ route('super-admin.planos') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Informações Importantes -->
            <div class="alert alert-warning mt-4">
                <h6 class="alert-heading">
                    <i class="fas fa-exclamation-triangle"></i> Atenção
                </h6>
                <ul class="mb-0 small">
                    <li>As alterações afetam apenas novos pagamentos</li>
                    <li>Assinaturas ativas mantêm o valor original</li>
                    <li>Os valores são salvos em <code>storage/app/plans.json</code></li>
                    <li>Use sempre valores positivos e realistas</li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Preview em tempo real dos cálculos
        const priceInput = document.getElementById('price');
        const discountInput = document.getElementById('discount_percent');
        const durationMonths = {{ $plan['duration_months'] }};

        function updatePreview() {
            const price = parseFloat(priceInput.value) || 0;
            const discount = parseFloat(discountInput.value) || 0;
            const finalPrice = price * (1 - discount / 100);
            const pricePerMonth = finalPrice / durationMonths;

            document.getElementById('preview-base').textContent =
                'R$ ' + price.toFixed(2).replace('.', ',');
            document.getElementById('preview-discount').textContent =
                '-' + discount.toFixed(0) + '%';
            document.getElementById('preview-final').textContent =
                'R$ ' + finalPrice.toFixed(2).replace('.', ',');
            document.getElementById('preview-month').textContent =
                'R$ ' + pricePerMonth.toFixed(2).replace('.', ',');
        }

        priceInput.addEventListener('input', updatePreview);
        discountInput.addEventListener('input', updatePreview);

        // Validação do formulário
        document.getElementById('editPlanForm').addEventListener('submit', function(e) {
            const price = parseFloat(priceInput.value);
            const discount = parseFloat(discountInput.value);

            if (price <= 0) {
                e.preventDefault();
                alert('O preço deve ser maior que zero!');
                priceInput.focus();
                return false;
            }

            if (discount < 0 || discount > 100) {
                e.preventDefault();
                alert('O desconto deve estar entre 0 e 100!');
                discountInput.focus();
                return false;
            }
        });
    </script>
@endsection
