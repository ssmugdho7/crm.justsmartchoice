/* Sell tours: proposals, estimates, invoices and payments. See ../tour.js for the SCTour.register() API.
   Text fields are { en, es }. */
(function (T) {
    'use strict';
    if (!T) return;

    T.register('proposals', {
        title: { en: 'Proposals', es: 'Propuestas' },
        description: {
            en: 'Send a polished proposal and let the customer accept it online.',
            es: 'Envía una propuesta profesional y deja que el cliente la acepte en línea.'
        },
        icon: 'fa-solid fa-file-signature', minutes: 2, group: 'Sell', perm: 'proposals', order: 10,
        steps: [
            { page: 'proposals',
              title: { en: 'Win the job with a proposal', es: 'Gana el trabajo con una propuesta' },
              body: {
                  en: 'A proposal pitches the work: scope, photos, pricing and terms. Customers can accept and sign it online.',
                  es: 'Una propuesta presenta el trabajo: alcance, fotos, precios y condiciones. El cliente puede aceptarla y firmarla en línea.'
              } },
            { page: 'proposals', target: '#side-menu .menu-item-sales', placement: 'right', optional: true,
              title: { en: 'It’s under Sales', es: 'Está dentro de Ventas' },
              body: {
                  en: 'Proposals, estimates, invoices and payments all live in the <b>Sales</b> menu.',
                  es: 'Las propuestas, presupuestos, facturas y pagos están en el menú <b>Ventas</b>.'
              } },
            { page: 'proposals', target: '.new-proposal-btn', placement: 'bottom', optional: true,
              title: { en: 'Start a new proposal', es: 'Crea una propuesta nueva' },
              body: {
                  en: 'Pick a customer or lead, add line items from your price list, then <b>Send</b>. You’ll be notified when it’s opened.',
                  es: 'Elige un cliente o cliente potencial, agrega conceptos de tu lista de precios y dale <b>Enviar</b>. Te avisaremos cuando la abra.'
              } },
            { page: 'proposals', target: '.table-proposals', placement: 'top', optional: true,
              title: { en: 'Track every proposal', es: 'Sigue cada propuesta' },
              body: {
                  en: 'See which are <b>Sent</b>, <b>Open</b>, <b>Accepted</b> or <b>Declined</b>. Accepted proposals convert to an estimate or invoice in one click.',
                  es: 'Ve cuáles están <b>Enviada</b>, <b>Abierta</b>, <b>Aceptada</b> o <b>Rechazada</b>. Una propuesta aceptada se convierte en presupuesto o factura con un clic.'
              } }
        ]
    });

    T.register('estimates', {
        title: { en: 'Estimates', es: 'Presupuestos' },
        description: {
            en: 'Price out a remodel and turn the approved estimate into an invoice.',
            es: 'Cotiza una remodelación y convierte el presupuesto aprobado en factura.'
        },
        icon: 'fa-solid fa-ruler-combined', minutes: 2, group: 'Sell', perm: 'estimates', order: 11,
        steps: [
            { page: 'estimates',
              title: { en: 'Estimates win the work', es: 'Los presupuestos consiguen el trabajo' },
              body: {
                  en: 'Send a clear, professional estimate. Once the homeowner approves it, it becomes an invoice with no retyping.',
                  es: 'Envía un presupuesto claro y profesional. Cuando el dueño de la casa lo aprueba, se convierte en factura sin volver a escribir nada.'
              } },
            { page: 'estimates', target: '.new-estimate-btn', placement: 'bottom', optional: true,
              title: { en: 'Create an estimate', es: 'Crea un presupuesto' },
              body: {
                  en: 'Choose the customer, add materials and labor as line items, set an expiry date and send it by email.',
                  es: 'Elige al cliente, agrega materiales y mano de obra como conceptos, pon una fecha de vencimiento y envíalo por correo.'
              } },
            { page: 'estimates', target: '.table-estimates', placement: 'top', optional: true,
              title: { en: 'Follow up on time', es: 'Da seguimiento a tiempo' },
              body: {
                  en: 'Filter by status to find estimates that are waiting or about to expire. A quick call often closes the deal.',
                  es: 'Filtra por estado para encontrar los presupuestos pendientes o por vencer. Una llamada rápida muchas veces cierra el trato.'
              } },
            { page: 'estimates',
              title: { en: 'Approved? Convert it', es: '¿Aprobado? Conviértelo' },
              body: {
                  en: 'Open an accepted estimate and choose <b>Convert to invoice</b>. Prices, customer and notes carry over.',
                  es: 'Abre un presupuesto aceptado y elige <b>Convertir a factura</b>. Los precios, el cliente y las notas pasan solos.'
              } }
        ]
    });

    T.register('invoices', {
        title: { en: 'Invoices', es: 'Facturas' },
        description: {
            en: 'Bill customers, take deposits and see what’s still owed.',
            es: 'Cobra a los clientes, recibe anticipos y ve cuánto te deben todavía.'
        },
        icon: 'fa-solid fa-file-invoice-dollar', minutes: 2, group: 'Sell', perm: 'invoices', order: 12,
        steps: [
            { page: 'invoices',
              title: { en: 'Get paid faster', es: 'Cobra más rápido' },
              body: {
                  en: 'Invoices go out by email with a link to pay online. Overdue reminders can go out automatically.',
                  es: 'Las facturas se envían por correo con un enlace para pagar en línea. Los recordatorios de pagos vencidos pueden salir solos.'
              } },
            { page: 'invoices', target: '.new-invoice-list', placement: 'bottom', optional: true,
              title: { en: 'New invoice', es: 'Factura nueva' },
              body: {
                  en: 'Bill a deposit, a progress payment or the final balance. Link it to the project so job costs stay together.',
                  es: 'Cobra un anticipo, un pago por avance o el saldo final. Vincúlala al proyecto para que los costos del trabajo queden juntos.'
              } },
            { page: 'invoices', target: '#invoices_total', placement: 'bottom', optional: true,
              title: { en: 'Totals at a glance', es: 'Totales de un vistazo' },
              body: {
                  en: 'Outstanding, past due and paid amounts, so you always know where cash stands.',
                  es: 'Montos pendientes, vencidos y pagados, para que siempre sepas cómo va el dinero.'
              } },
            { page: 'invoices', target: '.table-invoices', placement: 'top', optional: true,
              title: { en: 'Every invoice in one list', es: 'Todas las facturas en una lista' },
              body: {
                  en: 'Click a row to preview, record a payment, or send a reminder.',
                  es: 'Haz clic en una fila para verla, registrar un pago o enviar un recordatorio.'
              } }
        ]
    });

    T.register('payments', {
        title: { en: 'Payments', es: 'Pagos' },
        description: {
            en: 'See every payment received and match it to its invoice.',
            es: 'Ve cada pago recibido y a qué factura corresponde.'
        },
        icon: 'fa-solid fa-credit-card', minutes: 1, group: 'Sell', perm: ['payments', 'invoices'], order: 13,
        steps: [
            { page: 'payments',
              title: { en: 'Money in', es: 'Dinero que entra' },
              body: {
                  en: 'Every payment, whether online, check, cash or bank transfer, is recorded against an invoice.',
                  es: 'Cada pago, ya sea en línea, cheque, efectivo o transferencia, queda registrado en una factura.'
              } },
            // Lives inside the collapsed Sales menu: the engine expands Sales (and opens the sidebar on phones).
            { page: 'payments', target: '#side-menu .sub-menu-item-payments', placement: 'right', optional: true,
              title: { en: 'Find it under Sales', es: 'Lo encuentras en Ventas' },
              body: {
                  en: 'Open <b>Sales → Payments</b> any time to check what came in.',
                  es: 'Abre <b>Ventas → Pagos</b> cuando quieras para revisar lo que ha entrado.'
              } },
            { page: 'payments', target: '.table-payments', placement: 'top', optional: true,
              title: { en: 'Payment records', es: 'Registro de pagos' },
              body: {
                  en: 'Search by customer or invoice number. Open a payment to print or email a receipt.',
                  es: 'Busca por cliente o número de factura. Abre un pago para imprimir o enviar el recibo por correo.'
              } },
            { page: 'payments',
              title: { en: 'Recording a payment', es: 'Cómo registrar un pago' },
              body: {
                  en: 'To log a check or cash payment, open the invoice and click <b>Payment</b>. The invoice status updates automatically.',
                  es: 'Para anotar un pago en cheque o efectivo, abre la factura y haz clic en <b>Pago</b>. El estado de la factura se actualiza solo.'
              } }
        ]
    });
})(window.SCTour);
