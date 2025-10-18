import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import ptBrLocale from '@fullcalendar/core/locales/pt-br';

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('fullcalendar');

    if (!calendarEl) return;

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        locale: ptBrLocale,
        initialView: getInitialView(),
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia',
            list: 'Lista'
        },
        views: {
            dayGridMonth: {
                titleFormat: { year: 'numeric', month: 'long' }
            },
            timeGridWeek: {
                titleFormat: { year: 'numeric', month: 'short', day: 'numeric' }
            },
            timeGridDay: {
                titleFormat: { year: 'numeric', month: 'long', day: 'numeric' }
            }
        },
        navLinks: true,
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        slotDuration: '00:30:00',
        height: 'auto',
        expandRows: true,

        // Carregar eventos do backend
        events: async function(info, successCallback, failureCallback) {
            try {
                const response = await fetch('/agenda/eventos');
                const data = await response.json();
                successCallback(data);
            } catch (error) {
                console.error('Erro ao carregar eventos:', error);
                failureCallback(error);
            }
        },

        // Cores por status
        eventDidMount: function(info) {
            const status = info.event.extendedProps.status || 'pendente';
            const statusColors = {
                'pendente': { bg: '#fef3c7', border: '#f59e0b', text: '#92400e' },
                'confirmado': { bg: '#d1fae5', border: '#10b981', text: '#065f46' },
                'cancelado': { bg: '#fee2e2', border: '#ef4444', text: '#991b1b' },
                'concluido': { bg: '#e2e8f0', border: '#64748b', text: '#1e293b' }
            };

            const color = statusColors[status] || statusColors['pendente'];
            info.el.style.backgroundColor = color.bg;
            info.el.style.borderColor = color.border;
            info.el.style.color = color.text;
            info.el.style.borderLeft = `4px solid ${color.border}`;
        },

        // Clicar em evento
        eventClick: function(info) {
            const appointmentId = info.event.id;
            if (confirm(`Deseja editar "${info.event.title}"?`)) {
                window.location.href = `/agenda/${appointmentId}/edit`;
            }
        },

        // Selecionar intervalo para criar
        select: function(info) {
            const now = new Date();
            if (info.start < now && info.start.toDateString() !== now.toDateString()) {
                alert('Não é possível criar agendamentos em datas passadas.');
                calendar.unselect();
                return;
            }

            // Scroll para o formulário de criar
            const formSection = document.querySelector('.bg-white.shadow.sm\\:rounded-lg.border-t-4.border-indigo-500');
            if (formSection) {
                formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });

                // Preencher automaticamente data/hora no campo datetime-local
                const inicioInput = document.querySelector('input[name="inicio"]');

                if (inicioInput) {
                    const year = info.start.getFullYear();
                    const month = String(info.start.getMonth() + 1).padStart(2, '0');
                    const day = String(info.start.getDate()).padStart(2, '0');
                    const hours = String(info.start.getHours()).padStart(2, '0');
                    const minutes = String(info.start.getMinutes()).padStart(2, '0');

                    // Formato datetime-local: YYYY-MM-DDTHH:mm
                    inicioInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;

                    // Adiciona efeito visual para chamar atenção
                    inicioInput.classList.add('ring-2', 'ring-purple-500', 'ring-offset-2');
                    setTimeout(() => {
                        inicioInput.classList.remove('ring-2', 'ring-purple-500', 'ring-offset-2');
                    }, 2000);
                }

                // Se tiver fim definido, preenche também
                if (info.end && !info.allDay) {
                    const fimInput = document.querySelector('input[name="fim"]');
                    if (fimInput) {
                        const endYear = info.end.getFullYear();
                        const endMonth = String(info.end.getMonth() + 1).padStart(2, '0');
                        const endDay = String(info.end.getDate()).padStart(2, '0');
                        const endHours = String(info.end.getHours()).padStart(2, '0');
                        const endMinutes = String(info.end.getMinutes()).padStart(2, '0');

                        fimInput.value = `${endYear}-${endMonth}-${endDay}T${endHours}:${endMinutes}`;
                    }
                }
            }

            calendar.unselect();
        },

        // Arrastar evento
        eventDrop: async function(info) {
            const appointmentId = info.event.id;
            const newStart = info.event.start;

            try {
                const response = await fetch(`/agenda/${appointmentId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        inicio: newStart.toISOString(),
                        _method: 'PUT'
                    })
                });

                if (!response.ok) {
                    info.revert();
                    alert('Erro ao atualizar agendamento.');
                }
            } catch (error) {
                console.error('Erro:', error);
                info.revert();
                alert('Erro ao atualizar agendamento.');
            }
        },

        // Redimensionar evento
        eventResize: async function(info) {
            const appointmentId = info.event.id;
            const newEnd = info.event.end;

            try {
                const response = await fetch(`/agenda/${appointmentId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        fim: newEnd.toISOString(),
                        _method: 'PUT'
                    })
                });

                if (!response.ok) {
                    info.revert();
                    alert('Erro ao atualizar agendamento.');
                }
            } catch (error) {
                console.error('Erro:', error);
                info.revert();
                alert('Erro ao atualizar agendamento.');
            }
        },

        // Responsivo
        windowResize: function() {
            calendar.changeView(getInitialView());
        }
    });

    calendar.render();

    // Salvar preferência de visualização
    calendar.on('viewChange', function() {
        localStorage.setItem('calendarView', calendar.view.type);
    });
});

// Determinar visualização inicial baseada no tamanho da tela e preferência salva
function getInitialView() {
    const savedView = localStorage.getItem('calendarView');
    const width = window.innerWidth;

    if (savedView && width > 768) {
        return savedView;
    }

    if (width < 768) {
        return 'listMonth'; // Mobile: lista
    } else if (width < 1024) {
        return 'timeGridDay'; // Tablet: dia
    } else {
        return 'dayGridMonth'; // Desktop: mês
    }
}
