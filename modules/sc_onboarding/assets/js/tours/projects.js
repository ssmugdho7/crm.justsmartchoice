/* Run-jobs tours: projects and tasks. See ../tour.js for the SCTour.register() API. Text fields are { en, es }. */
(function (T) {
    'use strict';
    if (!T) return;

    T.register('projects', {
        title: { en: 'Projects', es: 'Proyectos' },
        description: {
            en: 'Run each job from start to finish: tasks, files, time and billing.',
            es: 'Lleva cada trabajo de principio a fin: tareas, archivos, horas y cobros.'
        },
        icon: 'fa-solid fa-helmet-safety', minutes: 2, group: 'Run jobs', perm: 'projects', order: 20,
        steps: [
            { page: 'projects',
              title: { en: 'One project per job', es: 'Un proyecto por cada trabajo' },
              body: {
                  en: 'Each kitchen, bath or roof job is a project. Tasks, photos, notes, time and invoices all stay together.',
                  es: 'Cada cocina, baño o techo es un proyecto. Las tareas, fotos, notas, horas y facturas quedan juntas en un solo lugar.'
              } },
            { page: 'projects', target: '#side-menu .menu-item-projects', placement: 'right', optional: true,
              title: { en: 'Projects menu', es: 'Menú de Proyectos' },
              body: {
                  en: 'Open <b>Projects</b> to see every job you’re part of.',
                  es: 'Abre <b>Proyectos</b> para ver todos los trabajos en los que participas.'
              } },
            { page: 'projects', target: 'a.btn-primary[href$="projects/project"]', placement: 'bottom', optional: true,
              title: { en: 'Start a new job', es: 'Empieza un trabajo nuevo' },
              body: {
                  en: 'Pick the customer, set start and deadline dates, and add your crew as members.',
                  es: 'Elige al cliente, pon la fecha de inicio y la fecha límite, y agrega a tu equipo como miembros.'
              } },
            { page: 'projects', target: '.table-projects', placement: 'top', optional: true,
              title: { en: 'All your jobs', es: 'Todos tus trabajos' },
              body: {
                  en: 'Filter by status (<b>Not started</b>, <b>In progress</b>, <b>On hold</b>, <b>Finished</b>) and click a job to open it.',
                  es: 'Filtra por estado (<b>No iniciado</b>, <b>En desarrollo</b>, <b>En espera</b>, <b>Finalizado</b>) y haz clic en un trabajo para abrirlo.'
              } },
            { page: 'projects',
              title: { en: 'When you open a job…', es: 'Cuando abras un trabajo…' },
              body: {
                  en: 'When you open a job, the tabs across the top hold its tasks, files, discussions, timesheets and invoices. You can upload site photos straight from your phone.',
                  es: 'Cuando abras un trabajo, verás pestañas arriba con sus tareas, archivos, conversaciones, horas trabajadas y facturas. Puedes subir fotos de la obra directo desde tu celular.'
              } }
        ]
    });

    T.register('tasks', {
        title: { en: 'Tasks', es: 'Tareas' },
        description: {
            en: 'Assign work, track progress and never lose a to-do.',
            es: 'Asigna trabajo, sigue el avance y no se te olvide ningún pendiente.'
        },
        icon: 'fa-solid fa-list-check', minutes: 2, group: 'Run jobs', perm: 'tasks', order: 21,
        steps: [
            { page: 'tasks',
              title: { en: 'Tasks keep the crew moving', es: 'Las tareas mantienen al equipo avanzando' },
              body: {
                  en: 'Anything that needs doing, like ordering tile or booking an inspection, becomes a task with an owner and a due date.',
                  es: 'Todo lo que hay que hacer, como pedir azulejo o agendar una inspección, se vuelve una tarea con un responsable y una fecha límite.'
              } },
            { page: 'tasks', target: '#wrapper a.btn-primary.new', placement: 'bottom', optional: true,
              title: { en: 'Add a task', es: 'Agrega una tarea' },
              body: {
                  en: 'Give it a name, assign it, set a due date and link it to a project or customer.',
                  es: 'Ponle un nombre, asígnala, define la fecha límite y vincúlala a un proyecto o cliente.'
              } },
            { page: 'tasks', target: '#wrapper a[href*="tasks/switch_kanban"]', placement: 'bottom', optional: true,
              title: { en: 'List or board', es: 'Lista o tablero' },
              body: {
                  en: 'Switch between a list and a kanban board. Drag cards across columns as work moves forward.',
                  es: 'Cambia entre lista y tablero Kanban. Arrastra las tarjetas de una columna a otra conforme avanza el trabajo.'
              } },
            { page: 'tasks', target: '.table-tasks', placement: 'top', optional: true,
              title: { en: 'Your task list', es: 'Tu lista de tareas' },
              body: {
                  en: 'Filter to <b>My tasks</b> or by status. Start the timer on a task to log time against the job.',
                  es: 'Filtra por <b>Mis tareas</b> o por estado. Inicia el cronómetro en una tarea para registrar las horas del trabajo.'
              } }
        ]
    });
})(window.SCTour);
