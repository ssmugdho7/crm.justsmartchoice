/* Team & admin tours: staff and settings. See ../tour.js for the SCTour.register() API. Text fields are { en, es }. */
(function (T) {
    'use strict';
    if (!T) return;

    T.register('staff', {
        title: { en: 'Staff', es: 'Equipo' },
        description: {
            en: 'Add team members and control what each person can access.',
            es: 'Agrega miembros del equipo y controla a qué puede entrar cada persona.'
        },
        icon: 'fa-solid fa-users-gear', minutes: 2, group: 'Team & admin', perm: 'staff', order: 50,
        steps: [
            { page: 'staff',
              title: { en: 'Your team', es: 'Tu equipo' },
              body: {
                  en: 'Office staff, project managers and field crew each get their own login, so you always know who did what.',
                  es: 'El personal de oficina, los encargados de proyecto y la cuadrilla de campo tienen cada uno su propio usuario, así siempre sabes quién hizo qué.'
              } },
            { page: 'staff', target: '#wrapper a.btn-primary[href$="staff/member"]', placement: 'bottom', optional: true,
              title: { en: 'Add a staff member', es: 'Agrega un miembro del equipo' },
              body: {
                  en: 'Enter their name and email, pick a role, then fine-tune permissions if needed.',
                  es: 'Escribe su nombre y correo, elige un rol y, si hace falta, ajusta los permisos.'
              } },
            { page: 'staff', target: '.table-staff', placement: 'top', optional: true,
              title: { en: 'Everyone in one list', es: 'Todos en una sola lista' },
              body: {
                  en: 'Click a name to edit their profile, role and permissions, or deactivate someone who has left.',
                  es: 'Haz clic en un nombre para editar su perfil, rol y permisos, o desactiva a alguien que ya no trabaja contigo.'
              } },
            { page: 'staff',
              title: { en: 'Roles keep it simple', es: 'Los roles lo hacen fácil' },
              body: {
                  en: 'Give everyone in the same job the same role. Example: field crew can see their projects and tasks but not invoices.',
                  es: 'Dale el mismo rol a quienes hacen el mismo trabajo. Por ejemplo: la cuadrilla de campo ve sus proyectos y tareas, pero no las facturas.'
              } }
        ]
    });

    T.register('settings', {
        title: { en: 'Settings', es: 'Ajustes' },
        description: {
            en: 'Company details, email, payments and other system options.',
            es: 'Datos de la empresa, correo, pagos y otras opciones del sistema.'
        },
        icon: 'fa-solid fa-gear', minutes: 2, group: 'Team & admin', perm: 'admin', order: 51,
        steps: [
            { page: 'settings',
              title: { en: 'System settings', es: 'Ajustes del sistema' },
              body: {
                  en: 'Admins set up the company profile, email sending, payment gateways and more here.',
                  es: 'Aquí los administradores configuran el perfil de la empresa, el envío de correos, las pasarelas de pago y más.'
              } },
            { page: 'settings', target: '.settings-group-general', placement: 'right', optional: true,
              title: { en: 'Start with General', es: 'Empieza por General' },
              body: {
                  en: 'Company name, logo and contact details appear on every estimate and invoice.',
                  es: 'El nombre, logo y datos de contacto de la empresa aparecen en cada presupuesto y factura.'
              } },
            { page: 'settings', target: '.settings-group-email', placement: 'right', optional: true,
              title: { en: 'Email', es: 'Correo' },
              body: {
                  en: 'Connect your mail server so estimates, invoices and reminders come from your company address.',
                  es: 'Conecta tu servidor de correo para que los presupuestos, facturas y recordatorios salgan desde el correo de tu empresa.'
              } },
            { page: 'settings', target: '.settings-group-payment_gateways', placement: 'right', optional: true,
              title: { en: 'Online payments', es: 'Pagos en línea' },
              body: {
                  en: 'Turn on a payment gateway so customers can pay invoices by card.',
                  es: 'Activa una pasarela de pago para que los clientes puedan pagar sus facturas con tarjeta.'
              } },
            { page: 'settings', target: '#setup-menu-item', placement: 'right', optional: true,
              title: { en: 'More setup', es: 'Más opciones en Configuración' },
              body: {
                  en: 'The <b>Setup</b> menu holds staff roles, custom fields, email templates and modules.',
                  es: 'El menú <b>Configuración</b> tiene los roles del equipo, campos personalizados, plantillas de correo y módulos.'
              } }
        ]
    });
})(window.SCTour);
