<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="h2 text-gradient">My Calendar</h2>
        </div>
    </x-slot>

    <div class="card" style="padding: 1.5rem 2rem; max-width: 950px; margin: 0 auto;">
        <div id="calendar"></div>
    </div>

    <!-- FullCalendar CDN -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var eventsData = {!! json_encode($events) !!};

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 650,
                contentHeight: 580,
                aspectRatio: 1.45,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: eventsData,
                eventTimeFormat: {
                    hour: 'numeric',
                    minute: '2-digit',
                    omitZeroMinute: false,
                    meridiem: 'short'
                },
                eventClick: function(info) {
                    if (info.event.url) {
                        window.location.href = info.event.url;
                        info.jsEvent.preventDefault(); // Prevent default link opening if any issues
                    }
                },
                eventContent: function(arg) {
                    let titleEl = document.createElement('div');
                    if (arg.timeText) {
                        titleEl.innerHTML = `<span style="font-weight: 700; opacity: 0.9; margin-right: 6px; white-space: nowrap;">${arg.timeText}</span>${arg.event.title}`;
                    } else {
                        titleEl.innerHTML = arg.event.title;
                    }
                    titleEl.style.whiteSpace = 'normal';
                    titleEl.style.overflow = 'hidden';
                    titleEl.style.fontSize = '0.8rem';
                    titleEl.style.lineHeight = '1.2';
                    return { domNodes: [titleEl] };
                }
            });

            calendar.render();
        });
    </script>

    <style>
        /* Custom FullCalendar Styles to match Eventry aesthetic */
        .fc {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
        }
        .fc .fc-toolbar-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
        }
        .fc .fc-button-primary {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
            text-transform: capitalize;
            font-weight: 500;
            border-radius: var(--radius-full) !important;
            transition: all 0.2s ease;
            box-shadow: none !important;
        }
        .fc .fc-button-primary:hover {
            background-color: #0036a0 !important; /* slightly darker primary */
            border-color: #0036a0 !important;
        }
        .fc .fc-button-primary:not(:disabled).fc-button-active, 
        .fc .fc-button-primary:not(:disabled):active {
            background-color: #002776 !important;
            border-color: #002776 !important;
        }
        .fc .fc-button-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .fc-theme-standard td, .fc-theme-standard th {
            border-color: var(--border);
        }
        .fc-theme-standard .fc-scrollgrid {
            border-color: var(--border);
            border-radius: var(--radius-md) !important;
            overflow: hidden;
        }
        .fc .fc-col-header-cell-cushion {
            color: var(--text-med);
            font-weight: 600;
            padding: 6px 0;
            font-size: 0.85rem;
            text-decoration: none;
        }
        .fc .fc-daygrid-day-number {
            color: var(--text-high);
            font-weight: 500;
            padding: 4px 8px;
            text-decoration: none;
            font-size: 0.85rem;
        }
        .fc-daygrid-event {
            border-radius: 4px;
            padding: 2px 4px;
            margin-top: 2px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: none;
        }
        .fc-daygrid-event:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
            opacity: 0.9;
        }
        /* Prominent today highlight */
        .fc .fc-daygrid-day.fc-day-today {
            background-color: transparent !important;
        }
        .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 4px;
            padding: 0;
            font-weight: bold;
        }
    </style>
</x-app-layout>
