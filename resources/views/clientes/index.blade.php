<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Meus Clientes
            </h2>
            <a href="{{ route('clientes.create') }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Novo Cliente
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-emerald-100 border border-emerald-300 text-emerald-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Estatísticas de Clientes --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div
                    class="bg-gradient-to-br from-green-50 to-green-100 p-6 shadow-md sm:rounded-lg border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase font-semibold text-green-600">Total de Clientes</p>
                            <p class="text-3xl font-bold text-green-900 mt-2">{{ $clientes->total() }}</p>
                        </div>
                        <div class="bg-green-500 p-3 rounded-full">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 shadow-md sm:rounded-lg border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase font-semibold text-blue-600">Cadastrados Hoje</p>
                            <p class="text-3xl font-bold text-blue-900 mt-2">
                                {{ $clientes->where('created_at', '>=', now()->startOfDay())->count() }}
                            </p>
                        </div>
                        <div class="bg-blue-500 p-3 rounded-full">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-emerald-50 to-emerald-100 p-6 shadow-md sm:rounded-lg border-l-4 border-emerald-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase font-semibold text-emerald-600">Com WhatsApp</p>
                            <p class="text-3xl font-bold text-emerald-900 mt-2">
                                {{ $clientes->where('whatsapp_number', '!=', null)->count() }}
                            </p>
                        </div>
                        <div class="bg-emerald-500 p-3 rounded-full">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabela de Clientes --}}
            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Lista de Clientes</h3>

                    @if ($clientes->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum cliente cadastrado</h3>
                            <p class="mt-1 text-sm text-gray-500">Comece cadastrando seu primeiro cliente.</p>
                            <div class="mt-6">
                                <a href="{{ route('clientes.create') }}"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Cadastrar Cliente
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="text-left text-xs uppercase text-gray-500">
                                        <th class="px-3 py-2">Nome</th>
                                        <th class="px-3 py-2">WhatsApp</th>
                                        <th class="px-3 py-2">Cadastrado em</th>
                                        <th class="px-3 py-2 text-right">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                                    @foreach ($clientes as $cliente)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-3 font-semibold text-gray-900">{{ $cliente->name }}</td>
                                            <td class="px-3 py-3">
                                                @if ($cliente->whatsapp_number)
                                                    <span class="font-mono text-green-700">
                                                        +{{ $cliente->whatsapp_number }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400 text-xs">Não informado</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3">
                                                <span
                                                    class="text-xs text-gray-500">{{ $cliente->created_at->format('d/m/Y H:i') }}</span>
                                            </td>
                                            <td class="px-3 py-3 text-right">
                                                <div class="flex justify-end gap-2">
                                                    <a href="{{ route('clientes.edit', $cliente) }}"
                                                        class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 border border-blue-300 rounded text-xs font-medium hover:bg-blue-200">
                                                        Editar
                                                    </a>

                                                    <form method="POST"
                                                        action="{{ route('clientes.destroy', $cliente) }}"
                                                        onsubmit="return confirm('Tem certeza que deseja excluir este cliente? Esta ação não pode ser desfeita.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 border border-red-300 rounded text-xs font-medium hover:bg-red-200">
                                                            Excluir
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Paginação --}}
                        <div class="mt-4">
                            {{ $clientes->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
