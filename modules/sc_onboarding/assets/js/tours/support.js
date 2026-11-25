/* Support tours: tickets and knowledge base. See ../tour.js for the SCTour.register() API. Text fields are { en, es }. */
(function (T) {
    'use strict';
    if (!T) return;

    T.register('tickets', {
        title: { en: 'Support tickets', es: 'Tickets de soporte' },
        description: {
            en: 'Handle warranty calls and customer questions without losing track.',
            es: 'Atiende llamadas de garantía y preguntas de clientes sin perder el hilo.'
        },
        icon: 'fa-solid fa-life-ring', minutes: 2, group: 'Support', perm: 'tickets', order: 40,
        steps: [
            { page: 'tickets',
              title: { en: 'Tickets for every request', es: 'Un ticket para cada solicitud' },
              body: {
                  en: 'Warranty callbacks, punch-list items and customer questions each get a ticket, so nothing falls through the cracks.',
                  es: 'Las visitas de garantía, los pendientes de cierre de obra y las preguntas de clientes tienen su propio ticket, para que nada se quede sin atender.'
              } },
            { page: 'tickets', target: '#side-menu .menu-item-support', placement: 'right', optional: true,
              title: { en: 'Support menu', es: 'Menú de Soporte' },
              body: {
                  en: 'Open <b>Support</b> to see all tickets. The badge shows how many are open.',
                  es: 'Abre <b>Soporte</b> para ver todos los tickets. El número indica cuántos siguen abiertos.'
              } },
            { page: 'tickets', target: '#wrapper a.btn-primary[href$="tickets/add"]', placement: 'bottom', optional: true,
              title: { en: 'Open a ticket', es: 'Abre un ticket' },
              body: {
                  en: 'Log a call for a customer, pick the department and priority, and assign it.',
                  es: 'Registra la llamada de un cliente, elige el departamento y la prioridad, y asígnalo a alguien.'
              } },
            { page: 'tickets', target: '.table-tickets', placement: 'top', optional: true,
              title: { en: 'Work the queue', es: 'Atiende la fila de tickets' },
              body: {
                  en: 'Filter by status or department. Reply from the ticket and the customer gets an email.',
                  es: 'Filtra por estado o departamento. Responde desde el ticket y el cliente recibe un correo.'
              } }
        ]
    });

    T.register('knowledge_base', {
        title: { en: 'Knowledge base', es: 'Base de conocimiento' },
        description: {
            en: 'Write how-to articles your team and customers can look up.',
            es: 'Escribe guías que tu equipo y tus clientes puedan consultar.'
        },
        icon: 'fa-solid fa-book-open', minutes: 1, group: 'Support', perm: 'knowledge_base', order: 41,
        steps: [
            { page: 'knowledge_base',
              title: { en: 'Answers in one place', es: 'Respuestas en un solo lugar' },
              body: {
                  en: 'Care guides, warranty terms and internal how-tos. Write it once and share the link.',
                  es: 'Guías de cuidado, condiciones de garantía e instrucciones internas. Escríbelo una vez y comparte el enlace.'
              } },
            { page: 'knowledge_base', target: '#side-menu .menu-item-knowledge-base', placement: 'right', optional: true,
              title: { en: 'Knowledge base menu', es: 'Menú de Base de conocimiento' },
              body: {
                  en: 'Open <b>Knowledge Base</b> from the menu.',
                  es: 'Abre <b>Base de conocimiento</b> desde el menú.'
              } },
            { page: 'knowledge_base', target: '#wrapper a.btn-primary[href$="knowledge_base/article"]', placement: 'bottom', optional: true,
              title: { en: 'New article', es: 'Artículo nuevo' },
              body: {
                  en: 'Give it a title and group, write the content, and choose whether customers can see it.',
                  es: 'Ponle un título y un grupo, escribe el contenido y decide si los clientes pueden verlo.'
              } },
            { page: 'knowledge_base', target: '.table-articles', placement: 'top', optional: true,
              title: { en: 'Your articles', es: 'Tus artículos' },
              body: {
                  en: 'Edit, reorder or turn articles off. Groups keep them organized.',
                  es: 'Edita, reordena o desactiva artículos. Los grupos los mantienen organizados.'
              } }
        ]
    });
})(window.SCTour);
