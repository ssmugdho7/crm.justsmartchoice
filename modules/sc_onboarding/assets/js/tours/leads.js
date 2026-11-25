/* Leads tour. See ../tour.js for the SCTour.register() API. Text fields are { en, es }. */
(function (T) {
    'use strict';
    if (!T) return;

    T.register('leads', {
        title: { en: 'Leads', es: 'Clientes potenciales' },
        description: {
            en: 'Capture new inquiries and move them toward a signed job.',
            es: 'Registra cada consulta nueva y llévala hasta un trabajo firmado.'
        },
        icon: 'fa-solid fa-bullseye', minutes: 2, group: 'Customers & leads', perm: 'leads', order: 32,
        steps: [
            { page: 'leads',
              title: { en: 'Every inquiry is a lead', es: 'Cada consulta es un cliente potencial' },
              body: {
                  en: 'Calls, web forms and referrals land here. Work them until they become paying customers.',
                  es: 'Las llamadas, formularios web y recomendaciones llegan aquí. Dales seguimiento hasta que se vuelvan clientes que pagan.'
              } },
            { page: 'leads', target: '#side-menu .menu-item-leads', placement: 'right', optional: true,
              title: { en: 'Leads menu', es: 'Menú de Clientes potenciales' },
              body: {
                  en: 'Open <b>Leads</b> to see your pipeline.',
                  es: 'Abre <b>Clientes potenciales</b> para ver todas tus oportunidades.'
              } },
            { page: 'leads', target: '#wrapper a.btn-primary[onclick^="init_lead"]', placement: 'bottom', optional: true,
              title: { en: 'Add a lead', es: 'Agrega un cliente potencial' },
              body: {
                  en: 'Enter the name, phone, source and what they need. Assign it to a salesperson right away.',
                  es: 'Escribe el nombre, teléfono, de dónde llegó y qué necesita. Asígnalo a un vendedor de inmediato.'
              } },
            { page: 'leads', target: '#wrapper a[href*="leads/switch_kanban"]', placement: 'bottom', optional: true,
              title: { en: 'Board or list', es: 'Tablero o lista' },
              body: {
                  en: 'The board shows leads as cards by status. Drag a card when the lead moves forward. Switch to a list to sort and filter.',
                  es: 'El tablero muestra cada cliente potencial como una tarjeta según su estado. Arrastra la tarjeta cuando avance. Cambia a lista para ordenar y filtrar.'
              } },
            { page: 'leads',
              title: { en: 'Close the deal', es: 'Cierra el trato' },
              body: {
                  en: 'Open a lead to log calls and notes, send a proposal, then <b>Convert to customer</b> when they say yes.',
                  es: 'Abre un cliente potencial para anotar llamadas y notas, envía una propuesta y, cuando diga que sí, usa <b>Convertir en cliente</b>.'
              } }
        ]
    });
})(window.SCTour);
