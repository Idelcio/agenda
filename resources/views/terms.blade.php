<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso e Política de Privacidade</title>
    <link rel="icon" type="image/png" href="{{ asset('logo2.png') }}">
    <link rel="alternate icon" type="image/png" href="{{ asset('logo2.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Termos de Uso e Política de Privacidade</h1>
                <p class="text-sm text-gray-600">Última atualização: {{ date('d/m/Y') }}</p>
            </div>

            <div class="space-y-8">
                <!-- Termos de Uso -->
                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b-2 border-blue-500 pb-2">1. Termos de Uso</h2>

                    <div class="space-y-4 text-gray-700">
                        <h3 class="text-xl font-semibold text-gray-800 mt-4">1.1 Aceitação dos Termos</h3>
                        <p>Ao acessar e usar este sistema de agendamento, você concorda com estes Termos de Uso e nossa Política de Privacidade. Se você não concordar com qualquer parte destes termos, não deverá utilizar nossos serviços.</p>

                        <h3 class="text-xl font-semibold text-gray-800 mt-4">1.2 Descrição do Serviço</h3>
                        <p>Nosso sistema oferece uma plataforma de agendamento automatizado integrada ao WhatsApp, permitindo que você:</p>
                        <ul class="list-disc list-inside ml-4 space-y-2">
                            <li>Gerencie agendamentos de forma automatizada</li>
                            <li>Envie lembretes via WhatsApp para seus clientes</li>
                            <li>Organize sua agenda de forma eficiente</li>
                            <li>Cadastre e gerencie informações de clientes</li>
                        </ul>

                        <h3 class="text-xl font-semibold text-gray-800 mt-4">1.3 Uso Permitido</h3>
                        <p>Você concorda em utilizar o serviço exclusivamente para:</p>
                        <ul class="list-disc list-inside ml-4 space-y-2">
                            <li>Gerenciamento de agendamentos e compromissos profissionais</li>
                            <li>Comunicação legítima com seus clientes via chatbot</li>
                            <li>Envio de lembretes e notificações relacionadas aos serviços agendados</li>
                        </ul>

                        <h3 class="text-xl font-semibold text-gray-800 mt-4">1.4 Uso Proibido</h3>
                        <p>É expressamente proibido utilizar o sistema para:</p>
                        <ul class="list-disc list-inside ml-4 space-y-2">
                            <li>Envio de spam ou mensagens não solicitadas</li>
                            <li>Práticas ilegais ou não autorizadas</li>
                            <li>Compartilhar conteúdo ofensivo, difamatório ou ilegal</li>
                            <li>Violar direitos de terceiros</li>
                            <li>Tentar acessar áreas restritas do sistema</li>
                        </ul>

                        <h3 class="text-xl font-semibold text-gray-800 mt-4">1.5 Responsabilidades do Usuário</h3>
                        <p>Você é responsável por:</p>
                        <ul class="list-disc list-inside ml-4 space-y-2">
                            <li>Manter a confidencialidade de suas credenciais de acesso</li>
                            <li>Todas as atividades realizadas em sua conta</li>
                            <li>Garantir que as informações fornecidas sejam verdadeiras e atualizadas</li>
                            <li>Obter consentimento de seus clientes para envio de mensagens</li>
                        </ul>
                    </div>
                </section>

                <!-- Política de Privacidade e LGPD -->
                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b-2 border-blue-500 pb-2">2. Política de Privacidade (LGPD)</h2>

                    <div class="space-y-4 text-gray-700">
                        <h3 class="text-xl font-semibold text-gray-800 mt-4">2.1 Compromisso com a Privacidade</h3>
                        <p>Esta Política de Privacidade foi elaborada em conformidade com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018 - LGPD). Levamos sua privacidade a sério e nos comprometemos a proteger seus dados pessoais.</p>

                        <h3 class="text-xl font-semibold text-gray-800 mt-4">2.2 Dados Coletados</h3>
                        <p>Para o funcionamento do sistema de agendamento via chatbot, coletamos apenas os seguintes dados:</p>
                        <ul class="list-disc list-inside ml-4 space-y-2">
                            <li><strong>Dados de cadastro:</strong> Nome, e-mail e número de WhatsApp</li>
                            <li><strong>Dados de agendamento:</strong> Informações sobre compromissos, datas e horários</li>
                            <li><strong>Dados de clientes:</strong> Nome e número de WhatsApp dos seus clientes (quando você os cadastra)</li>
                        </ul>

                        <h3 class="text-xl font-semibold text-gray-800 mt-4">2.3 Dados que NÃO Coletamos</h3>
                        <p class="font-semibold">Declaramos expressamente que NÃO coletamos:</p>
                        <ul class="list-disc list-inside ml-4 space-y-2">
                            <li>CPF (Cadastro de Pessoa Física)</li>
                            <li>CNPJ (Cadastro Nacional de Pessoa Jurídica)</li>
                            <li>Dados bancários ou financeiros</li>
                            <li>Dados sensíveis (origem racial, opiniões políticas, religiosas, etc.)</li>
                            <li>Dados de saúde</li>
                            <li>Dados de localização em tempo real</li>
                        </ul>

                        <h3 class="text-xl font-semibold text-gray-800 mt-4">2.4 Finalidade do Uso dos Dados</h3>
                        <p>Utilizamos seus dados exclusivamente para:</p>
                        <ul class="list-disc list-inside ml-4 space-y-2">
                            <li>Possibilitar seu acesso e autenticação no sistema</li>
                            <li>Gerenciar seus agendamentos</li>
                            <li>Enviar notificações e lembretes via WhatsApp</li>
                            <li>Melhorar a experiência de uso do sistema</li>
                            <li>Funcionalidade do chatbot de agendamento</li>
                        </ul>

                        <h3 class="text-xl font-semibold text-gray-800 mt-4">2.5 Compartilhamento de Dados</h3>
                        <p>Seus dados NÃO são vendidos, alugados ou compartilhados com terceiros para fins comerciais. O único compartilhamento realizado é:</p>
                        <ul class="list-disc list-inside ml-4 space-y-2">
                            <li>Com a API de WhatsApp (API Brasil ou similar) exclusivamente para envio de mensagens autorizadas</li>
                            <li>Quando exigido por lei ou ordem judicial</li>
                        </ul>

                        <h3 class="text-xl font-semibold text-gray-800 mt-4">2.6 Segurança dos Dados</h3>
                        <p>Implementamos medidas de segurança técnicas e administrativas para proteger seus dados contra acesso não autorizado, perda, alteração ou divulgação, incluindo:</p>
                        <ul class="list-disc list-inside ml-4 space-y-2">
                            <li>Criptografia de senhas</li>
                            <li>Acesso restrito aos dados</li>
                            <li>Monitoramento de segurança</li>
                            <li>Backups regulares</li>
                        </ul>

                        <h3 class="text-xl font-semibold text-gray-800 mt-4">2.7 Seus Direitos (LGPD)</h3>
                        <p>De acordo com a LGPD, você tem direito a:</p>
                        <ul class="list-disc list-inside ml-4 space-y-2">
                            <li>Confirmar a existência de tratamento de seus dados</li>
                            <li>Acessar seus dados pessoais</li>
                            <li>Corrigir dados incompletos, inexatos ou desatualizados</li>
                            <li>Solicitar a anonimização, bloqueio ou eliminação de dados desnecessários</li>
                            <li>Solicitar a portabilidade de seus dados</li>
                            <li>Revogar o consentimento</li>
                            <li>Solicitar a exclusão de dados tratados com seu consentimento</li>
                        </ul>

                        <h3 class="text-xl font-semibold text-gray-800 mt-4">2.8 Retenção de Dados</h3>
                        <p>Mantemos seus dados pessoais apenas pelo tempo necessário para as finalidades descritas nesta política, ou conforme exigido por lei. Quando você solicitar a exclusão de sua conta, seus dados serão removidos de nossos sistemas.</p>

                        <h3 class="text-xl font-semibold text-gray-800 mt-4">2.9 Cookies e Tecnologias Similares</h3>
                        <p>Utilizamos cookies essenciais para manter sua sessão ativa e garantir o funcionamento adequado do sistema. Não utilizamos cookies de rastreamento ou publicidade.</p>
                    </div>
                </section>

                <!-- Informações da Empresa -->
                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b-2 border-blue-500 pb-2">3. Informações da Empresa</h2>

                    <div class="space-y-2 text-gray-700">
                        <p><strong>Razão Social:</strong> Forest</p>
                        <p><strong>CNPJ:</strong> 36.370.873/0001-61</p>
                        <p><strong>Contato:</strong> (51) 98487-1703</p>
                    </div>
                </section>

                <!-- Alterações -->
                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b-2 border-blue-500 pb-2">4. Alterações nestes Termos</h2>

                    <div class="text-gray-700">
                        <p>Reservamo-nos o direito de modificar estes Termos de Uso e Política de Privacidade a qualquer momento. As alterações entrarão em vigor imediatamente após sua publicação no sistema. O uso continuado do serviço após as alterações constitui sua aceitação dos novos termos.</p>
                    </div>
                </section>

                <!-- Contato -->
                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b-2 border-blue-500 pb-2">5. Contato</h2>

                    <div class="text-gray-700">
                        <p>Para exercer seus direitos sob a LGPD, esclarecer dúvidas sobre estes termos ou reportar problemas, entre em contato:</p>
                        <ul class="list-none ml-4 mt-2 space-y-2">
                            <li><strong>WhatsApp:</strong> (51) 98487-1703</li>
                            <li><strong>CNPJ:</strong> 36.370.873/0001-61</li>
                        </ul>
                    </div>
                </section>

                <!-- Legislação -->
                <section>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b-2 border-blue-500 pb-2">6. Lei Aplicável</h2>

                    <div class="text-gray-700">
                        <p>Estes termos são regidos pelas leis da República Federativa do Brasil, especialmente pela Lei nº 13.709/2018 (LGPD), pelo Marco Civil da Internet (Lei nº 12.965/2014) e pelo Código de Defesa do Consumidor (Lei nº 8.078/1990).</p>
                    </div>
                </section>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                    Voltar para o Cadastro
                </a>
                <span class="mx-2 text-gray-400">|</span>
                <a href="{{ url('/') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                    Voltar para Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
