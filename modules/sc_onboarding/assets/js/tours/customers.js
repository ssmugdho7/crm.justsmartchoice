/* Customers & leads tours: customers and contracts. See ../tour.js for the SCTour.register() API.
   Text fields are { en, es }. */
(function (T) {
    'use strict';
    if (!T) return;

    T.register('customers', {
        title: { en: 'Customers', es: 'Clientes' },
        description: {
            en: 'Keep every homeowner and business you work for in one place.',
            es: 'Ten en un solo lugar a cada dueño de casa y empresa para quien trabajas.'
        },
        icon: 'fa-solid fa-house-user', minutes: 2, group: 'Customers & leads', perm: 'customers', order: 30,
        steps: [
            { page: 'clients',
              title: { en: 'Your customer list', es: 'Tu lista de clientes' },
              body: {
                  en: 'Every customer has a profile with contacts, addresses, estimates, invoices, projects and notes.',
                  es: 'Cada cliente tiene un perfil con sus contactos, direcciones, presupuestos, facturas, proyectos y notas.'
              } },
            { page: 'clients', target: '#side-menu .menu-item-customers', placement: 'right', optional: true,
              title: { en: 'Customers menu', es: 'Menú de Clientes' },
              body: {
                  en: 'Open <b>Customers</b> from the menu any time.',
                  es: 'Abre <b>Clientes</b> desde el menú cuando quieras.'
              } },
            { page: 'clients', target: '#wrapper a.btn-primary[href$="clients/client"]', placement: 'bottom', optional: true,
              title: { en: 'Add a customer', es: 'Agrega un cliente' },
              body: {
                  en: 'Enter the company or homeowner name, phone and job address. You can add more contacts later.',
                  es: 'Escribe el nombre de la empresa o del dueño de la casa, el teléfono y la dirección de la obra. Después puedes agregar más contactos.'
              } },
            { page: 'clients', target: '.table-clients', placement: 'top', optional: true,
              title: { en: 'Find anyone fast', es: 'Encuentra a cualquiera rápido' },
              body: {
                  en: 'Search, filter by group, and click a name to open the full profile and job history.',
                  es: 'Busca, filtra por grupo y haz clic en un nombre para abrir su perfil completo y su historial de trabajos.'
              } }
        ]
    });

    T.register('contracts', {
        title: { en: 'Contracts', es: 'Contratos' },
        description: {
            en: 'Send contracts for e-signature and track when they expire.',
            es: 'Envía contratos para firma electrónica y controla cuándo vencen.'
        },
        icon: 'fa-solid fa-file-contract', minutes: 2, group: 'Customers & leads', perm: 'contracts', order: 31,
        steps: [
            { page: 'contracts',
              title: { en: 'Paperwork, signed online', es: 'Papeles firmados en línea' },
              body: {
                  en: 'Send a contract, let the customer sign it on their phone, and keep the signed copy on file.',
                  es: 'Envía un contrato, deja que el cliente lo firme desde su celular y guarda la copia firmada en el sistema.'
              } },
            { page: 'contracts', target: '#wrapper a.btn-primary[href$="contracts/contract"]', placement: 'bottom', optional: true,
              title: { en: 'New contract', es: 'Contrato nuevo' },
              body: {
                  en: 'Pick the customer, set the value and dates, then write or paste your terms.',
                  es: 'Elige al cliente, pon el monto y las fechas, y escribe o pega tus condiciones.'
              } },
            { page: 'contracts', target: '#contract_summary', placement: 'bottom', optional: true,
              title: { en: 'Contract summary', es: 'Resumen de contratos' },
              body: {
                  en: 'Active, expired and about-to-expire contracts, so renewals never slip.',
                  es: 'Contratos activos, vencidos y por vencer, para que ninguna renovación se te pase.'
              } },
            { page: 'contracts', target: '.table-contracts', placement: 'top', optional: true,
              title: { en: 'All contracts', es: 'Todos los contratos' },
              body: {
                  en: 'Open any contract to send it for signature, add comments, or attach files.',
                  es: 'Abre cualquier contrato para enviarlo a firma, agregar comentarios o adjuntar archivos.'
              } }
        ]
    });
})(window.SCTour);
