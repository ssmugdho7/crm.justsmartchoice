/* Basics tour: getting around Smart Choice CRM. See ../tour.js for the SCTour.register() API.
   Every text field is { en, es }; the engine picks the staff member's language (SCOnb.lang). */
(function (T) {
    'use strict';
    if (!T) return;

    T.register('basics', {
        title: { en: 'Getting around', es: 'Cómo moverte en el CRM' },
        description: {
            en: 'A quick look at the menu, search, quick create and where to find help.',
            es: 'Un vistazo rápido al menú, la búsqueda, la creación rápida y dónde encontrar ayuda.'
        },
        icon: 'fa-solid fa-compass', minutes: 2, group: 'Basics', order: 0,
        steps: [
            { page: '',
              title: { en: 'Welcome to Smart Choice CRM', es: 'Te damos la bienvenida a Smart Choice CRM' },
              body: {
                  en: 'This is where you run the whole business: leads, estimates, jobs, invoices and your crew. This tour takes about two minutes.',
                  es: 'Aquí manejas todo el negocio: clientes potenciales, presupuestos, trabajos, facturas y tu equipo. Este recorrido toma unos dos minutos.'
              } },
            // On phones the menu is behind the ☰ button; the engine opens it. If it still can't be found the step is skipped.
            { page: '', target: '#side-menu', placement: 'right', optional: true,
              title: { en: 'The main menu', es: 'El menú principal' },
              body: {
                  en: 'Everything lives in the main menu: <b>Customers</b>, <b>Sales</b>, <b>Projects</b>, <b>Tasks</b>, <b>Leads</b> and <b>Support</b>. You only see the areas your role allows.',
                  es: 'Todo está en el menú principal: <b>Clientes</b>, <b>Ventas</b>, <b>Proyectos</b>, <b>Tareas</b>, <b>Clientes potenciales</b> y <b>Soporte</b>. Solo ves las áreas que tu rol permite.'
              } },
            { page: '', target: '#search_input', placement: 'bottom', optional: true,
              title: { en: 'Search anything', es: 'Busca lo que sea' },
              body: {
                  en: 'Use the search box at the top to find a customer name, phone number, invoice or project and jump straight to it. Faster than clicking through menus.',
                  es: 'Usa la caja de búsqueda de arriba para encontrar un cliente, un teléfono, una factura o un proyecto y abrirlo al instante. Es más rápido que buscar en los menús.'
              } },
            // The whole round button (the link around the + icon), not just the icon.
            { page: '', target: '#header li.icon > a[data-toggle="dropdown"]:has(.fa-plus)', placement: 'bottom', optional: true, padding: 6,
              title: { en: 'Quick create', es: 'Creación rápida' },
              body: {
                  en: 'The <b>+</b> button adds a new lead, customer, estimate, task or project from any page.',
                  es: 'El botón <b>+</b> agrega un cliente potencial, cliente, presupuesto, tarea o proyecto desde cualquier página.'
              } },
            { page: '', target: '#header .header-notifications', placement: 'bottom', optional: true,
              title: { en: 'Notifications', es: 'Notificaciones' },
              body: {
                  en: 'The bell lights up when someone assigns you a task, mentions you, or a customer accepts an estimate.',
                  es: 'La campana se ilumina cuando alguien te asigna una tarea, te menciona o un cliente acepta un presupuesto.'
              } },
            { page: '', target: '[data-container="top-12"]', placement: 'top', optional: true,
              title: { en: 'Your dashboard', es: 'Tu panel de inicio' },
              body: {
                  en: 'Widgets show what needs attention today: open tasks, overdue invoices, project progress. Drag widgets to rearrange them.',
                  es: 'Los recuadros muestran lo que necesita atención hoy: tareas abiertas, facturas vencidas y el avance de los proyectos. Arrástralos para acomodarlos a tu gusto.'
              } },
            { page: '', target: '#side-menu .menu-item-sc-help', placement: 'right', optional: true,
              title: { en: 'Help & Tours', es: 'Ayuda y recorridos' },
              body: {
                  en: 'Open <b>Help &amp; Tours</b> in the menu any time to replay this tour or take one for Sales, Projects, Leads and more.',
                  es: 'Abre <b>Ayuda y recorridos</b> en el menú cuando quieras para repetir este recorrido o tomar otro de Ventas, Proyectos, Clientes potenciales y más.'
              } }
        ]
    });
})(window.SCTour);
