<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Meus Clientes
            </h2>
            <div class="flex gap-3">
                <button onclick="openTagModal()"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-900 active:bg-black focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Gerenciar Tags
                </button>
                <a href="{{ route('clientes.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Novo Cliente
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-emerald-100 border border-emerald-300 text-emerald-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Botão Histórico / Voltar com estilo Agendoo --}}
            <div class="flex justify-start">
                <a href="{{ route('agenda.index') }}"
                    class="inline-flex items-center mb-4 px-5 py-2.5 bg-white text-indigo-600 border border-indigo-200 rounded-lg shadow hover:bg-indigo-50 hover:text-indigo-700 transition-all duration-200 font-semibold text-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Voltar
                </a>
            </div>

            {{-- Filtros --}}
            <div x-data="clienteFilters()" class="bg-white shadow rounded-lg p-4">
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar por nome</label>
                        <input type="text" x-model="searchTerm" @input="filterClientes"
                            placeholder="Digite o nome do cliente..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    </div>

                    <div class="min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Tag</label>
                        <select x-model="selectedTag" @change="filterClientes"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                            <option value="">Todas as tags</option>
                            @foreach ($tags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button @click="clearFilters"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition">
                        Limpar Filtros
                    </button>
                </div>
            </div>



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
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Lista de Clientes</h3>
                    </div>

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
                                        <th class="px-3 py-2">Tags</th>
                                        <th class="px-3 py-2">Cadastrado em</th>
                                        <th class="px-3 py-2 text-right">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                                    @foreach ($clientes as $cliente)
                                        <tr class="hover:bg-gray-50" data-cliente-id="{{ $cliente->id }}"
                                            data-cliente-name="{{ strtolower($cliente->name) }}"
                                            data-cliente-tags="{{ $cliente->clienteTags->pluck('id')->implode(',') }}">
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
                                            <td class="px-3 py-3 relative">
                                                <div x-data="clienteTags({{ $cliente->id }}, @js($cliente->clienteTags))"
                                                    class="flex flex-wrap gap-1 items-center">
                                                    <template x-for="tag in clienteTags" :key="tag.id">
                                                        <span
                                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium text-white"
                                                            :style="`background-color: ${getColorHex(tag.cor)}`">
                                                            <span x-text="tag.nome"></span>
                                                            <button @click="removeTag(tag.id)"
                                                                class="hover:bg-white/20 rounded-full p-0.5">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                            </button>
                                                        </span>
                                                    </template>

                                                    {{-- Botão para adicionar tag rapidamente --}}
                                                    <div class="relative inline-block" x-data="{ open: false }">
                                                        <button @click="open = !open" type="button"
                                                            class="inline-flex items-center px-2 py-1 bg-purple-50 text-purple-600 border border-purple-200 rounded-full text-xs font-medium hover:bg-purple-100 transition">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M12 4v16m8-8H4" />
                                                            </svg>
                                                            Tag
                                                        </button>

                                                        <div x-show="open" @click.away="open = false" x-cloak
                                                            x-transition:enter="transition ease-out duration-100"
                                                            x-transition:enter-start="transform opacity-0 scale-95"
                                                            x-transition:enter-end="transform opacity-100 scale-100"
                                                            x-transition:leave="transition ease-in duration-75"
                                                            x-transition:leave-start="transform opacity-100 scale-100"
                                                            x-transition:leave-end="transform opacity-0 scale-95"
                                                            class="absolute left-0 z-[9999] mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-1 max-h-64 overflow-y-auto"
                                                            style="position: absolute;">
                                                            @if($tags->isEmpty())
                                                                <div class="px-3 py-2 text-xs text-gray-500 text-center">
                                                                    Nenhuma tag criada ainda
                                                                </div>
                                                            @else
                                                                @foreach ($tags as $tag)
                                                                    <button @click="addTag({{ $tag->id }}); open = false" type="button"
                                                                        class="w-full text-left px-3 py-2 hover:bg-purple-50 flex items-center gap-2 transition">
                                                                        <span class="w-4 h-4 rounded-full flex-shrink-0"
                                                                            style="background-color: {{ $tag->cor === 'blue' ? '#3B82F6' : ($tag->cor === 'green' ? '#10B981' : ($tag->cor === 'red' ? '#EF4444' : ($tag->cor === 'yellow' ? '#F59E0B' : ($tag->cor === 'purple' ? '#8B5CF6' : ($tag->cor === 'pink' ? '#EC4899' : ($tag->cor === 'orange' ? '#F97316' : ($tag->cor === 'gray' ? '#6B7280' : ($tag->cor === 'indigo' ? '#6366F1' : '#14B8A6')))))))) }}"></span>
                                                                        <span class="text-sm text-gray-700">{{ $tag->nome }}</span>
                                                                    </button>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
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

                                                    <form method="POST" action="{{ route('clientes.destroy', $cliente) }}"
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

    {{-- Tag Management Modal --}}
    @include('tags.manage-modal')

    <script>
        // Color mapping helper
        const colorMap = {
            'blue': '#3B82F6',
            'green': '#10B981',
            'red': '#EF4444',
            'yellow': '#F59E0B',
            'purple': '#8B5CF6',
            'pink': '#EC4899',
            'orange': '#F97316',
            'gray': '#6B7280',
            'indigo': '#6366F1',
            'teal': '#14B8A6'
        };

        // Cliente tags component for each row
        function clienteTags(clienteId, initialTags) {
            return {
                clienteId: clienteId,
                clienteTags: initialTags || [],

                getColorHex(colorValue) {
                    return colorMap[colorValue] || '#3B82F6';
                },

                async addTag(tagId) {
                    try {
                        const response = await fetch('/tags/attach', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                cliente_id: this.clienteId,
                                tag_id: tagId
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            // Adiciona a tag à lista se não existir
                            if (!this.clienteTags.find(t => t.id === data.tag.id)) {
                                this.clienteTags.push(data.tag);
                                this.updateRowTagsAttribute();
                            }
                        } else {
                            alert(data.message || 'Erro ao adicionar tag');
                        }
                    } catch (error) {
                        console.error('Erro:', error);
                        alert('Erro ao adicionar tag. Tente novamente.');
                    }
                },

                async removeTag(tagId) {
                    if (!confirm('Deseja remover esta tag do cliente?')) {
                        return;
                    }

                    try {
                        const response = await fetch('/tags/detach', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                cliente_id: this.clienteId,
                                tag_id: tagId
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            // Remove a tag da lista
                            this.clienteTags = this.clienteTags.filter(t => t.id !== tagId);
                            this.updateRowTagsAttribute();
                        } else {
                            alert(data.message || 'Erro ao remover tag');
                        }
                    } catch (error) {
                        console.error('Erro:', error);
                        alert('Erro ao remover tag. Tente novamente.');
                    }
                },

                updateRowTagsAttribute() {
                    // Atualiza o atributo data-cliente-tags para que os filtros funcionem corretamente
                    const row = document.querySelector(`tr[data-cliente-id="${this.clienteId}"]`);
                    if (row) {
                        const tagIds = this.clienteTags.map(t => t.id).join(',');
                        row.setAttribute('data-cliente-tags', tagIds);
                    }
                }
            }
        }

        // Cliente filters component
        function clienteFilters() {
            return {
                searchTerm: '',
                selectedTag: '',

                filterClientes() {
                    const rows = document.querySelectorAll('tbody tr[data-cliente-id]');

                    rows.forEach(row => {
                        const clienteName = row.getAttribute('data-cliente-name') || '';
                        const matchesSearch = !this.searchTerm || clienteName.includes(this.searchTerm.toLowerCase());

                        let matchesTag = true;
                        if (this.selectedTag) {
                            const clienteTags = row.getAttribute('data-cliente-tags') || '';
                            const tagIds = clienteTags ? clienteTags.split(',') : [];
                            matchesTag = tagIds.includes(this.selectedTag);
                        }

                        row.style.display = (matchesSearch && matchesTag) ? '' : 'none';
                    });
                },

                clearFilters() {
                    this.searchTerm = '';
                    this.selectedTag = '';
                    this.filterClientes();
                }
            }
        }

        // Helper function to open tag modal
        function openTagModal() {
            const modalElement = document.getElementById('tagManagerModal');
            if (modalElement) {
                const alpineData = Alpine.$data(modalElement);
                if (alpineData && alpineData.openModal) {
                    alpineData.openModal();
                }
            }
        }
    </script>
</x-app-layout>