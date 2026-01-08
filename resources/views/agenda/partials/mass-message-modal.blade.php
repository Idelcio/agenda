<div x-ref="massMessageModal"
     x-cloak
     class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur">
    <div class="w-full max-w-lg max-h-[90vh] flex flex-col rounded-3xl bg-white shadow-2xl overflow-hidden overflow-y-auto">
        <form x-ref="massMessageForm"
              action="{{ route('clientes.send-mass-message') }}"
              method="POST"
              class="flex flex-col h-full">
            @csrf
            <input type="hidden" name="client_ids" x-bind:value="JSON.stringify(selectedClients)">

            <header class="flex-shrink-0 flex items-center justify-between rounded-t-3xl bg-green-600 px-6 py-3 text-white">
                <div class="flex items-center gap-2 font-semibold">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                    </svg>
                    <span>Envio em Massa via WhatsApp</span>
                </div>
                <button type="button"
                        @click="closeMassModal()"
                        class="rounded-full bg-white/20 p-2 text-white transition hover:bg-white/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </header>

            <div class="flex-1 overflow-y-auto space-y-3 px-6 py-3 pb-6" style="overflow-y: auto; -webkit-overflow-scrolling: touch;">
                {{-- Alerta de Segurança --}}
                <div class="rounded-lg border-2 border-amber-400 bg-amber-50 p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="space-y-2">
                            <h4 class="font-bold text-amber-900 text-sm">⚠️ ATENÇÃO: Cuidados com Envio em Massa</h4>
                            <ul class="text-xs text-amber-800 space-y-1.5 list-disc list-inside">
                                <li><strong>Risco de bloqueio:</strong> Envios em massa podem fazer o WhatsApp bloquear seu número permanentemente</li>
                                <li><strong>Números novos:</strong> Se o número for novo, ele DEVE ser validado antes de usar envio em massa</li>
                                <li><strong>Recomendação:</strong> Use apenas com números validados há mais de 6 meses</li>
                                <li><strong>Boas práticas:</strong> Evite enviar para mais de 50 contatos por vez e respeite o intervalo entre envios</li>
                            </ul>
                            <p class="text-xs font-semibold text-amber-900 mt-2">
                                💡 Para números novos, comece enviando mensagens individuais e espere respostas antes de usar envio em massa.
                            </p>
                            <p class="text-xs font-bold text-red-700 mt-3 pt-2 border-t border-amber-300">
                                ⚠️ NÃO NOS RESPONSABILIZAMOS SE SEU NÚMERO FOR BLOQUEADO PELO WHATSAPP. Use por sua conta e risco.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="text-sm text-gray-600">
                    <span x-text="selectedClients.length"></span> cliente(s) selecionado(s) &bull; Intervalo de 5s entre envios
                </div>
                <p class="text-xs text-gray-500">
                    Envio em massa aceita apenas mensagens de texto. Anexos nao sao suportados aqui.
                </p>

                <div>
                    <label for="mass_title" class="block text-sm font-semibold text-gray-700 mb-2">Titulo da campanha</label>
                    <input type="text"
                           id="mass_title"
                           name="titulo"
                           required
                           maxlength="150"
                           placeholder="Ex.: Marketing das quintas-feiras"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                </div>

                <div>
                    <label for="mass_message" class="block text-sm font-semibold text-gray-700 mb-2">
                        Mensagem
                    </label>
                    <textarea id="mass_message"
                              name="mensagem"
                              rows="4"
                              maxlength="1000"
                              required
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                              placeholder="Digite a mensagem que deseja enviar"></textarea>
                    <p class="mt-1 text-xs text-gray-500">Campo obrigatorio. Maximo de 1000 caracteres.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Quando enviar? <span class="text-red-600">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <label class="cursor-pointer rounded-lg border px-3 py-2 transition"
                               :class="agendamento === 'imediato' ? 'border-green-500 bg-green-50 text-green-800' : 'border-slate-200 text-slate-600'">
                            <input type="radio" class="sr-only" name="agendamento_tipo" value="imediato" x-model="agendamento" required>
                            <p class="text-sm font-semibold">Enviar agora</p>
                            <p class="text-xs">Comeca imediatamente</p>
                        </label>
                        <label class="cursor-pointer rounded-lg border px-3 py-2 transition"
                               :class="agendamento === 'agendado' ? 'border-green-500 bg-green-50 text-green-800' : 'border-slate-200 text-slate-600'">
                            <input type="radio" class="sr-only" name="agendamento_tipo" value="agendado" x-model="agendamento" required>
                            <p class="text-sm font-semibold">Agendar envio</p>
                            <p class="text-xs">Escolha dia e hora</p>
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Selecione uma das opcoes acima</p>
                    <div class="mt-3" x-show="agendamento === 'agendado'" x-cloak>
                        <label for="mass_schedule" class="block text-sm font-semibold text-gray-700 mb-2">Data e hora do disparo</label>
                        <input type="datetime-local"
                               id="mass_schedule"
                               name="scheduled_for"
                               min="{{ now()->format('Y-m-d\\TH:i') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        <p class="mt-1 text-xs text-gray-500">Use horario local no formato 24h.</p>
                    </div>
                </div>
            </div>

            <footer class="flex-shrink-0 rounded-b-3xl bg-slate-50 px-6 py-3 border-t border-slate-200">
                <div class="flex gap-3 justify-end">
                    <button type="button"
                            @click="closeMassModal()"
                            class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-2xl bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-green-600/30 transition hover:bg-green-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="agendamento === 'agendado' ? 'Agendar envio' : 'Enviar agora'">Enviar agora</span>
                    </button>
                </div>
            </footer>
        </form>
    </div>
</div>

