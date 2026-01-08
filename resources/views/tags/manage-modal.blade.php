{{-- Modal de Gerenciamento de Tags --}}
<div id="tagManagerModal" x-data="tagManager()" x-show="showModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col"
        @click.away="closeModal()">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <h2 class="text-xl font-bold">Gerenciar Tags</h2>
                </div>
                <button @click="closeModal()" class="text-white hover:bg-white/20 rounded-full p-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            {{-- Formulário de Criação/Edição --}}
            <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-5 border border-purple-200">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span x-text="editingTag ? 'Editar Tag' : 'Nova Tag'"></span>
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nome da Tag</label>
                        <input type="text" x-model="tagForm.nome" maxlength="50" placeholder="Ex: VIP, Novo Cliente"
                            class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Escolha a Cor</label>
                        <div class="flex flex-wrap gap-3">
                            <template x-for="color in availableColors" :key="color.value">
                                <button type="button" @click="tagForm.cor = color.value" :class="{
                                        'ring-4 ring-offset-2': tagForm.cor === color.value
                                    }" :style="`background-color: ${color.hex}; border-color: ${color.hex}`"
                                    class="w-10 h-10 rounded-full border-4 transition transform hover:scale-110"
                                    :title="color.name">
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button @click="saveTag()" :disabled="saving"
                            class="flex-1 bg-purple-600 text-white px-4 py-2.5 rounded-lg font-semibold hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                            <span x-show="!saving" x-text="editingTag ? 'Atualizar' : 'Criar Tag'"></span>
                            <span x-show="saving">Salvando...</span>
                        </button>

                        <button x-show="editingTag" @click="cancelEdit()"
                            class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Lista de Tags --}}
            <div>
                <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Tags Criadas (<span x-text="tags.length"></span>)
                </h3>

                <div x-show="tags.length === 0" class="text-center py-8 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <p>Nenhuma tag criada ainda.</p>
                    <p class="text-sm">Crie sua primeira tag acima!</p>
                </div>

                <div class="space-y-2">
                    <template x-for="tag in tags" :key="tag.id">
                        <div
                            class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full" :style="`background-color: ${getColorHex(tag.cor)}`">
                                </div>
                                <span class="font-medium text-gray-900" x-text="tag.nome"></span>
                            </div>

                            <div class="flex gap-2">
                                <button @click="editTag(tag)"
                                    class="px-3 py-1.5 text-sm bg-amber-50 text-amber-700 rounded-lg hover:bg-amber-100 transition">
                                    Editar
                                </button>
                                <button @click="deleteTag(tag.id)"
                                    class="px-3 py-1.5 text-sm bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition">
                                    Excluir
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 px-6 py-4 border-t">
            <button @click="closeModal()"
                class="w-full bg-gray-600 text-white px-4 py-2.5 rounded-lg font-semibold hover:bg-gray-700 transition">
                Fechar
            </button>
        </div>
    </div>
</div>

<script>
    function tagManager() {
        return {
            showModal: false,
            tags: @json($tags ?? []),
            tagForm: {
                nome: '',
                cor: 'blue'
            },
            editingTag: null,
            saving: false,

            availableColors: [{
                name: 'Azul',
                value: 'blue',
                hex: '#3B82F6'
            },
            {
                name: 'Verde',
                value: 'green',
                hex: '#10B981'
            },
            {
                name: 'Vermelho',
                value: 'red',
                hex: '#EF4444'
            },
            {
                name: 'Amarelo',
                value: 'yellow',
                hex: '#F59E0B'
            },
            {
                name: 'Roxo',
                value: 'purple',
                hex: '#8B5CF6'
            },
            {
                name: 'Rosa',
                value: 'pink',
                hex: '#EC4899'
            },
            {
                name: 'Laranja',
                value: 'orange',
                hex: '#F97316'
            },
            {
                name: 'Cinza',
                value: 'gray',
                hex: '#6B7280'
            },
            {
                name: 'Índigo',
                value: 'indigo',
                hex: '#6366F1'
            },
            {
                name: 'Teal',
                value: 'teal',
                hex: '#14B8A6'
            },
            ],

            openModal() {
                this.showModal = true;
            },

            closeModal() {
                this.showModal = false;
                this.cancelEdit();
            },

            getColorHex(colorValue) {
                const color = this.availableColors.find(c => c.value === colorValue);
                return color ? color.hex : '#3B82F6';
            },

            async saveTag() {
                if (!this.tagForm.nome.trim()) {
                    alert('Digite um nome para a tag');
                    return;
                }

                this.saving = true;

                try {
                    const url = this.editingTag ?
                        `/tags/${this.editingTag.id}` :
                        '/tags';

                    const method = this.editingTag ? 'PATCH' : 'POST';

                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.tagForm)
                    });

                    const data = await response.json();

                    if (response.ok) {
                        if (this.editingTag) {
                            const index = this.tags.findIndex(t => t.id === this.editingTag.id);
                            if (index !== -1) {
                                this.tags[index] = data.tag;
                            }
                        } else {
                            this.tags.push(data.tag);
                        }

                        this.cancelEdit();
                        alert(data.message);
                        window.location.reload(); // Recarrega para atualizar a lista de clientes
                    } else {
                        alert(data.message || 'Erro ao salvar tag');
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    alert('Erro ao salvar tag. Tente novamente.');
                } finally {
                    this.saving = false;
                }
            },

            editTag(tag) {
                this.editingTag = tag;
                this.tagForm = {
                    nome: tag.nome,
                    cor: tag.cor
                };
            },

            cancelEdit() {
                this.editingTag = null;
                this.tagForm = {
                    nome: '',
                    cor: 'blue'
                };
            },

            async deleteTag(tagId) {
                if (!confirm('Tem certeza que deseja excluir esta tag? Ela será removida de todos os clientes.')) {
                    return;
                }

                try {
                    const response = await fetch(`/tags/${tagId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.tags = this.tags.filter(t => t.id !== tagId);
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert(data.message || 'Erro ao excluir tag');
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    alert('Erro ao excluir tag. Tente novamente.');
                }
            }
        }
    }
</script>