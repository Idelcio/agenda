<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Conexão Oficial WhatsApp (Meta)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Sucesso!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            {{-- Card Principal: Status e Conexão --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <div class="flex flex-col md:flex-row gap-8 items-start">
                        
                        {{-- Lado Esquerdo: Status --}}
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Conecte sua conta WhatsApp Business</h3>
                            <p class="text-gray-600 mb-6">
                                Utilize a infraestrutura oficial da Meta (Facebook) para garantir estabilidade máxima, segurança e evitar bloqueios. 
                                Ideal para empresas que precisam de confiabilidade profissional.
                            </p>

                            <div class="flex items-center gap-4 mb-6">
                                <div class="flex items-center gap-2 px-4 py-2 rounded-full {{ $isConnected ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    <span class="relative flex h-3 w-3">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $isConnected ? 'bg-green-400 opacity-75' : 'hidden' }}"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 {{ $isConnected ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                    </span>
                                    <span class="font-semibold">{{ $isConnected ? 'Conectado e Ativo' : 'Não conectado' }}</span>
                                </div>
                                @if($isConnected)
                                    <span class="text-sm text-gray-500">ID: {{ $user->meta_phone_id }}</span>
                                @endif
                            </div>

                            @if(!$isConnected)
                                <a href="{{ route('meta.oauth.redirect') }}" 
                                   class="inline-flex items-center px-6 py-3 bg-[#1877F2] border border-transparent rounded-lg font-semibold text-white hover:bg-[#166fe5] focus:bg-[#1565c0] active:bg-[#1565c0] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                                    <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                    Conectar com Facebook
                                </a>
                                <p class="mt-2 text-xs text-gray-500">
                                    * Ao conectar, você autoriza o nosso sistema a enviar mensagens em nome da sua empresa.
                                </p>
                            @else
                                <form action="{{ route('meta.oauth.disconnect') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium underline">
                                        Desconectar conta
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- Lado Direito: Ilustração ou Info --}}
                        <div class="hidden md:block w-1/3 bg-gray-50 p-6 rounded-xl border border-gray-100">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                Vantagens da API Oficial
                            </h4>
                            <ul class="space-y-3 text-sm text-gray-600">
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    <span>Não precisa de celular ligado ou bateria.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    <span>Conexão extremamente estável (Nuvem).</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    <span>Risco de banimento quase zero quando usado corretamente.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    <span>Envio de botões e listas interativas nativas.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>


            {{-- Bloco de Transparência de Preços --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-blue-500">
                <div class="p-8">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="bg-blue-100 p-2 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Entendendo os Custos do Facebook</h3>
                            <p class="text-sm text-gray-500">O modelo de cobrança é baseado em <strong>Conversas</strong>, não por mensagem individual.</p>
                        </div>
                    </div>

                    {{-- Explicação Visual da Janela de 24h --}}
                    <div class="bg-gradient-to-br from-gray-50 to-blue-50 rounded-xl p-6 mb-8 border border-blue-100">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Como funciona a "Janela de 24 Horas"?
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Cenário 1: Cliente Chamou --}}
                            <div class="relative bg-white p-5 rounded-lg shadow-sm border border-gray-100">
                                <div class="absolute -top-3 -left-3 bg-green-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold shadow-md">1</div>
                                <h5 class="font-bold text-gray-800 mb-2 ml-4">Cliente chamou você</h5>
                                <p class="text-sm text-gray-600 mb-3">
                                    Quando um cliente envia uma mensagem (dúvida, pedido), abre-se uma <strong>Janela de Serviço</strong> gratuita.
                                </p>
                                <div class="bg-green-50 text-green-800 text-xs p-3 rounded border border-green-100 flex items-center gap-2">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span>Você tem <strong>24 horas</strong> para responder livremente sem custo (nas primeiras 1.000 conversas/mês).</span>
                                </div>
                            </div>

                            {{-- Cenário 2: Você chamou --}}
                            <div class="relative bg-white p-5 rounded-lg shadow-sm border border-gray-100">
                                <div class="absolute -top-3 -left-3 bg-blue-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold shadow-md">2</div>
                                <h5 class="font-bold text-gray-800 mb-2 ml-4">Você chamou o cliente</h5>
                                <p class="text-sm text-gray-600 mb-3">
                                    Se passaram mais de 24h desde a última mensagem do cliente, a janela fechou. Para reabrir, você paga uma taxa única.
                                </p>
                                <div class="bg-blue-50 text-blue-800 text-xs p-3 rounded border border-blue-100 flex items-center gap-2">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                                    <span>Ao enviar um <strong>Template</strong> pago, uma nova janela de 24h se abre para conversar à vontade sobre aquele tema.</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-xs text-gray-500 text-center italic">
                            Resumindo: Você só paga para iniciar conversas novas ou retomar conversas antigas (mais de 24h paradas).
                        </div>
                    </div>

                    <h4 class="font-bold text-gray-800 mb-4 text-sm uppercase tracking-wide">Tabela de Preços Estimados (Brasil)</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        @foreach($pricing as $type => $data)
                            <div class="border rounded-xl p-4 {{ $data['bg'] }} border-opacity-50 border-gray-200 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-bold {{ $data['color'] }} uppercase text-xs tracking-wider">{{ $data['title'] }}</h4>
                                    {{-- Ícones simples baseados no tipo --}}
                                    @if($type == 'marketing')
                                        <svg class="w-5 h-5 {{ $data['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                                    @elseif($type == 'utility')
                                        <svg class="w-5 h-5 {{ $data['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    @elseif($type == 'authentication')
                                        <svg class="w-5 h-5 {{ $data['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                    @else
                                        <svg class="w-5 h-5 {{ $data['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <p class="text-2xl font-extrabold text-gray-800">{{ $data['price'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $data['unit'] }}</p>
                                </div>

                                <p class="text-xs text-gray-600 leading-relaxed min-h-[40px]">
                                    {{ $data['description'] }}
                                </p>

                                @if(isset($data['note']))
                                    <p class="mt-2 text-[10px] text-gray-400 italic">{{ $data['note'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Explicação "Open Bar" para Leigos --}}
                    <div class="mt-6 bg-green-50 rounded-xl p-5 border border-green-100 flex flex-col md:flex-row items-center gap-6">
                        <div class="hidden md:block">
                            <div class="bg-white p-3 rounded-full shadow-md">
                                <span class="text-4xl">🎟️</span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-green-800 text-lg mb-2">Entendeu? É como um "Ingresso de Parque"!</h4>
                            <p class="text-green-900 text-sm leading-relaxed">
                                Quando você paga os <strong>R$ 0,35</strong> (exemplo), você compra um "passe livre" válido por <strong>24 horas</strong> para conversar com aquele cliente.
                            </p>
                            <ul class="mt-3 space-y-1 text-sm text-green-800 font-medium">
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    Dentro dessas 24h, você pode enviar 1, 10 ou 1.000 mensagens sem pagar nada a mais.
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    Se a conversa acabar e você chamar de novo no dia seguinte (após fechar a janela), aí sim paga um novo ingresso.
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-6 flex items-start gap-2 text-xs text-gray-500 bg-gray-50 p-3 rounded border border-gray-100">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <p>
                            <strong>Importante:</strong> Esses valores são cobrados diretamente na sua fatura do Facebook Business. 
                            Nós cobramos apenas a assinatura do sistema. As primeiras 1.000 conversas de serviço (iniciadas pelo cliente) são gratuitas mensalmente. 
                            Conversas de marketing e utilidade são pagas desde a primeira mensagem.
                        </p>
                    </div>
                </div>
            </div>

            {{-- FAQ / Ajuda --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Perguntas Frequentes</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium text-gray-900 text-sm mb-1">Como funciona o pagamento?</h4>
                        <p class="text-sm text-gray-600">Você cadastra um cartão de crédito no painel de Negócios da Meta. O Facebook cobra automaticamente conforme o uso.</p>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 text-sm mb-1">Posso usar meu número atual?</h4>
                        <p class="text-sm text-gray-600">Sim, mas ele deixará de funcionar no WhatsApp do celular. Um número na API Oficial funciona exclusivamente na nuvem.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
