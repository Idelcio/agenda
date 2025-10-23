import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import ptBrLocale from '@fullcalendar/core/locales/pt-br';

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('fullcalendar');

    if (!calendarEl) return;

    const appointmentModal = document.getElementById('appointment-modal');
    const modalElements = appointmentModal ? {
        wrapper: appointmentModal,
        title: appointmentModal.querySelector('[data-modal-title]'),
        status: appointmentModal.querySelector('[data-modal-status]'),
        datetime: appointmentModal.querySelector('[data-modal-datetime]'),
        description: appointmentModal.querySelector('[data-modal-description]'),
        whatsapp: appointmentModal.querySelector('[data-modal-whatsapp]'),
        editLink: appointmentModal.querySelector('[data-modal-edit]'),
        closers: appointmentModal.querySelectorAll('[data-modal-close]')
    } : null;

    const statusLabels = {
        pendente: 'Pendente',
        confirmado: 'Confirmado',
        cancelado: 'Cancelado',
        concluido: 'Concluido'
    };

    const statusBadgeStyles = {
        pendente: 'bg-amber-100 text-amber-700 border-amber-200',
        confirmado: 'bg-emerald-100 text-emerald-700 border-emerald-200',
        cancelado: 'bg-rose-100 text-rose-700 border-rose-200',
        concluido: 'bg-slate-200 text-slate-700 border-slate-300'
    };

    const dateFormatter = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'long' });
    const dateTimeFormatter = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'long', timeStyle: 'short' });

    const closeAppointmentModal = () => {
        if (!modalElements) {
            return;
        }

        modalElements.wrapper.classList.add('hidden');
        modalElements.wrapper.setAttribute('aria-hidden', 'true');
    };

    if (modalElements) {
        modalElements.wrapper.setAttribute('role', 'dialog');
        modalElements.wrapper.setAttribute('aria-modal', 'true');
        modalElements.wrapper.setAttribute('aria-hidden', 'true');

        modalElements.closers.forEach(trigger => {
            trigger.addEventListener('click', closeAppointmentModal);
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !modalElements.wrapper.classList.contains('hidden')) {
                closeAppointmentModal();
            }
        });
    }

    const openAppointmentModal = (event) => {
        if (!modalElements) {
            window.location.href = `/agenda/${event.id}/edit`;
            return;
        }

        const statusKey = event.extendedProps.status || 'pendente';
        const badge = modalElements.status;
        if (badge) {
            const baseClasses = 'inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-semibold';
            badge.className = `${baseClasses} ${statusBadgeStyles[statusKey] || statusBadgeStyles.pendente}`;
            badge.textContent = statusLabels[statusKey] || statusLabels.pendente;
        }

        if (modalElements.title) {
            modalElements.title.textContent = event.title || 'Compromisso';
        }

        if (modalElements.datetime) {
            let text = '';
            if (event.allDay && event.start) {
                text = `Dia inteiro em ${dateFormatter.format(event.start)}`;
            } else if (event.start) {
                const startText = dateTimeFormatter.format(event.start);
                const endText = event.end ? dateTimeFormatter.format(event.end) : '';
                text = endText ? `${startText} até ${endText}` : startText;
            } else {
                text = 'Horário não informado.';
            }
            modalElements.datetime.textContent = text;
        }

        if (modalElements.description) {
            const description = event.extendedProps.description;
            modalElements.description.textContent = description && description.trim().length
                ? description.trim()
                : 'Nenhuma descrição registrada.';
        }

        if (modalElements.whatsapp) {
            modalElements.whatsapp.textContent = event.extendedProps.whatsapp
                ? 'Lembretes automáticos via WhatsApp ativados.'
                : 'Lembretes via WhatsApp não estão ativados.';
        }

        if (modalElements.editLink) {
            modalElements.editLink.setAttribute('href', `/agenda/${event.id}/edit`);
        }

        modalElements.wrapper.classList.remove('hidden');
        modalElements.wrapper.setAttribute('aria-hidden', 'false');
    };

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
                'pendente': { bg: '#fef3c7', border: '#f59e0b' },
                'confirmado': { bg: '#d1fae5', border: '#10b981' },
                'cancelado': { bg: '#fee2e2', border: '#ef4444' },
                'concluido': { bg: '#e2e8f0', border: '#64748b' }
            };

            const color = statusColors[status] || statusColors['pendente'];
            const textColor = '#111827';
            const descriptionColor = '#475569';

            info.el.style.backgroundColor = color.bg;
            info.el.style.borderColor = color.border;
            info.el.style.borderLeft = `4px solid ${color.border}`;
            info.el.style.setProperty('color', textColor, 'important');

            const textNodes = info.el.querySelectorAll('.fc-event-title, .fc-event-time, .fc-list-event-title, .fc-event-main-frame, .fc-event-main');
            textNodes.forEach(node => node.style.setProperty('color', textColor, 'important'));

            const description = info.event.extendedProps.description;
            if (description) {
                const descriptionText = description.trim();
                if (descriptionText.length) {
                    const mainContainer = info.el.querySelector('.fc-event-main');
                    if (mainContainer && !mainContainer.querySelector('.fc-event-description')) {
                        const descriptionEl = document.createElement('div');
                        descriptionEl.className = 'fc-event-description';
                        descriptionEl.textContent = descriptionText;
                        descriptionEl.style.color = descriptionColor;
                        descriptionEl.style.display = 'block';
                        descriptionEl.style.width = '100%';
                        descriptionEl.style.whiteSpace = 'nowrap';
                        descriptionEl.style.overflow = 'hidden';
                        descriptionEl.style.textOverflow = 'ellipsis';
                        mainContainer.appendChild(descriptionEl);
                    }

                    const listContainer = info.el.querySelector('.fc-list-event-title');
                    if (listContainer && !listContainer.querySelector('.fc-event-description')) {
                        const descriptionEl = document.createElement('div');
                        descriptionEl.className = 'fc-event-description';
                        descriptionEl.textContent = descriptionText;
                        descriptionEl.style.color = descriptionColor;
                        descriptionEl.style.display = 'block';
                        descriptionEl.style.width = '100%';
                        descriptionEl.style.whiteSpace = 'nowrap';
                        descriptionEl.style.overflow = 'hidden';
                        descriptionEl.style.textOverflow = 'ellipsis';
                        listContainer.appendChild(descriptionEl);
                    }
                }
            }
        },

        // Clicar em evento
        eventClick: function(info) {
            if (info.jsEvent) {
                info.jsEvent.preventDefault();
                info.jsEvent.stopPropagation();
            }

            openAppointmentModal(info.event);
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
