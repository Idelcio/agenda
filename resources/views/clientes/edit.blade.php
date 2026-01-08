<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('clientes.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Cliente
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center mb-6">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Editar Informações do Cliente</h3>
                            <p class="text-sm text-gray-600">Atualize as informações do cliente {{ $cliente->name }}.
                            </p>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded">
                            <strong>Ops!</strong>
                            <p class="mt-2 text-sm">Verifique os campos destacados e tente novamente.</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('clientes.update', $cliente) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="name" value="Nome completo *" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name', $cliente->name)" required autofocus
                                placeholder="Digite o nome completo do cliente" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="whatsapp_number" value="WhatsApp *" />
                            <x-text-input id="whatsapp_number" name="whatsapp_number" type="text"
                                class="mt-1 block w-full" :value="old('whatsapp_number', '+' . $cliente->whatsapp_number)" required placeholder="+5511999999999" />
                            <p class="mt-1 text-xs text-gray-500">
                                Digite com o código do país (+55) + DDD + número. Exemplo: +5511999999999
                            </p>
                            <x-input-error class="mt-2" :messages="$errors->get('whatsapp_number')" />
                        </div>

                        {{-- Gerenciamento de Tags --}}
                        <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-5 border border-purple-200"
                            x-data="clienteTagsEdit({{ $cliente->id }}, @js($cliente->clienteTags))">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <label class="font-semibold text-gray-900">Tags do Cliente</label>
                                </div>
                                <button type="button" onclick="openTagModal()"
                                    class="inline-flex items-center px-3 py-1.5 bg-gray-800 text-white rounded-lg text-xs font-medium hover:bg-gray-900 transition shadow-sm">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Nova Tag
                                </button>
                            </div>

                            {{-- Tags atuais do cliente --}}
                            <div class="mb-3">
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="tag in clienteTags" :key="tag.id">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium text-white shadow-sm"
                                            :style="`background-color: ${getColorHex(tag.cor)}`">
                                            <span x-text="tag.nome"></span>
                                            <button @click="removeTag(tag.id)" type="button"
                                                class="hover:bg-white/20 rounded-full p-0.5 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template x-if="clienteTags.length === 0">
                                        <span class="text-sm text-gray-500 italic">Nenhuma tag adicionada ainda</span>
                                    </template>
                                </div>
                            </div>

                            {{-- Adicionar nova tag --}}
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" type="button"
                                    class="inline-flex items-center px-4 py-2 bg-white text-purple-600 border border-purple-300 rounded-lg text-sm font-medium hover:bg-purple-50 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Adicionar Tag
                                </button>

                                <div x-show="open" @click.away="open = false" x-cloak
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute left-0 z-50 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 py-1 max-h-72 overflow-y-auto">
                                    @if($tags->isEmpty())
                                        <div class="px-4 py-3 text-sm text-gray-500 text-center">
                                            <p>Nenhuma tag criada ainda.</p>
                                            <button type="button" @click="open = false; openTagModal()"
                                                class="mt-2 text-purple-600 hover:text-purple-700 font-medium">
                                                Criar primeira tag
                                            </button>
                                        </div>
                                    @else
                                        @foreach ($tags as $tag)
                                            <button @click="addTag(@js($tag)); open = false" type="button"
                                                class="w-full text-left px-4 py-2.5 flex items-center gap-3 transition"
                                                :class="isTagSelected({{ $tag->id }}) ? 'opacity-50 cursor-not-allowed bg-gray-100' : 'hover:bg-purple-50 cursor-pointer'"
                                                :disabled="isTagSelected({{ $tag->id }})">
                                                <span class="w-5 h-5 rounded-full flex-shrink-0 shadow-sm"
                                                    style="background-color: {{ $tag->cor === 'blue' ? '#3B82F6' : ($tag->cor === 'green' ? '#10B981' : ($tag->cor === 'red' ? '#EF4444' : ($tag->cor === 'yellow' ? '#F59E0B' : ($tag->cor === 'purple' ? '#8B5CF6' : ($tag->cor === 'pink' ? '#EC4899' : ($tag->cor === 'orange' ? '#F97316' : ($tag->cor === 'gray' ? '#6B7280' : ($tag->cor === 'indigo' ? '#6366F1' : '#14B8A6')))))))) }}">
                                                </span>
                                                <span class="text-sm text-gray-700 font-medium">{{ $tag->nome }}</span>
                                                <span x-show="isTagSelected({{ $tag->id }})"
                                                    class="ml-auto text-xs text-green-600 font-semibold">✓ Adicionada</span>
                                            </button>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <p class="mt-3 text-xs text-gray-600">
                                💡 Use tags para organizar e filtrar seus clientes mais facilmente
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-4 border-t">
                            <a href="{{ route('clientes.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancelar
                            </a>

                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Salvar Alterações
                            </button>
                        </div>
                    </form>
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

        // Cliente tags component for edit page
        function clienteTagsEdit(clienteId, initialTags) {
            return {
                clienteId: clienteId,
                clienteTags: initialTags || [],

                getColorHex(colorValue) {
                    return colorMap[colorValue] || '#3B82F6';
                },

                isTagSelected(tagId) {
                    return this.clienteTags.some(t => t.id === tagId);
                },

                async addTag(tag) {
                    // Verifica se a tag já está adicionada
                    if (this.isTagSelected(tag.id)) {
                        return;
                    }

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
                                tag_id: tag.id
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            // Adiciona a tag à lista
                            this.clienteTags.push(tag);
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
                        } else {
                            alert(data.message || 'Erro ao remover tag');
                        }
                    } catch (error) {
                        console.error('Erro:', error);
                        alert('Erro ao remover tag. Tente novamente.');
                    }
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