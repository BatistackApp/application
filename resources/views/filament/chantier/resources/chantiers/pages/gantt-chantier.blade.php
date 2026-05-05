<x-filament-panels::page>
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">

        {{-- Légende --}}
        <div class="flex items-center gap-6 mb-4 text-xs">
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm bg-[#94a3b8] inline-block"></span> À faire
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm bg-[#3b82f6] inline-block"></span> En cours
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm bg-[#22c55e] inline-block"></span> Terminé
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm bg-[#ef4444] inline-block"></span> Bloqué
            </span>
        </div>

        <div id="gantt-container"></div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.css">

        <script>
            const tasks = @json($this->getGanttData());

            if (tasks.length === 0) {
                document.getElementById('gantt-container').innerHTML =
                    '<p class="text-center text-gray-400 py-12">Aucune tâche planifiée sur ce chantier.</p>';
            } else {
                const gantt = new Gantt('#gantt-container', tasks, {
                    view_mode: 'Week',
                    date_format: 'YYYY-MM-DD',
                    language: 'fr',
                    custom_popup_html: function(task) {
                        return `
                        <div class="details-container p-3 text-sm">
                            <h5 class="font-bold mb-1">${task.name}</h5>
                            <p class="text-gray-500">Avancement : <strong>${task.progress}%</strong></p>
                            ${task.assignee ? `<p class="text-gray-500">Responsable : ${task.assignee}</p>` : ''}
                            <p class="text-gray-500">Du ${task.start} au ${task.end}</p>
                        </div>
                    `;
                    },
                    on_click: function(task) {},
                    on_date_change: function(task, start, end) {
                        @this.call('updateTaskDates', task.id, start, end);
                    },
                    on_progress_change: function(task, progress) {
                        @this.call('updateTaskProgress', task.id, progress);
                    },
                });

                // Couleurs personnalisées par statut
                tasks.forEach(task => {
                    const bar = document.querySelector(`.bar-wrapper[data-id="${task.id}"] .bar`);
                    if (bar) bar.style.fill = task.color;
                });
            }
        </script>
    @endpush
</x-filament-panels::page>
