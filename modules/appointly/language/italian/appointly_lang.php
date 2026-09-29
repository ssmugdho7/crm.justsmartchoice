<?php


// ----------------------
// ADMIN SETTINGS & DASHBOARD
// ----------------------
// Module Information
$lang['appointly_module_name'] = 'Appointly';
$lang['appointly_current_version'] = 'Versione attuale del modulo: ';
$lang['appointly_settings_label_pointer'] = '<strong>Configurazione -> Impostazioni -> Appuntamenti</strong>';
$lang['appointly_integrations'] = 'Integrazioni';
$lang['general_settings'] = 'Impostazioni generali';
$lang['new_appointment_notification'] = 'Nuova notifica appuntamento';

// Admin Settings
$lang['appointments_approve_automatically_label'] = 'Approva automaticamente gli appuntamenti inviati dai moduli web esterni';
$lang['appointments_reminder_time_value'] = 'Aggiungi un valore per ricevere una notifica via email prima del tuo appuntamento (default 30 min prima)';
$lang['appointly_default_table_filter_label'] = 'Filtro predefinito tabella appuntamenti';
$lang['appointment_your_settings'] = 'Le tue impostazioni appuntamenti';
$lang['appointments_buffer_hours_label'] = 'Disabilita il selettore di date passate';

// Dashboard & Analytics
$lang['appointly_show_summary_in_appointments_dashboard'] = 'Mostra il riepilogo degli appuntamenti nel dashboard appuntamenti';
$lang['appointments_summary'] = 'Riepilogo appuntamenti';
$lang['appointment_appointments_dashboard'] = 'Dashboard';
$lang['appointment_want_to_go_back'] = 'Appuntamenti';
$lang['appointly_analytics_dashboard'] = 'Dashboard di analisi Appointly';
$lang['appointment_history_label'] = 'Appuntamenti passati';
$lang['appointment_history_label_menu_label'] = 'Cronologia appuntamenti';
$lang['appointment_services_menu_label'] = 'Servizi';
$lang['appointment_analytics_and_reports_menu_label'] = 'Analisi e rapporti';

// Invoice Conversion
$lang['appointment_convert_to_invoice'] = 'Converti in fattura';
$lang['appointment_converted_to_invoice'] = 'Appuntamento convertito in fattura con successo';
$lang['appointment_convert_to_invoice_success'] = 'Appuntamento convertito in fattura #%s con successo';
$lang['appointment_convert_to_invoice_failed'] = 'Impossibile convertire l\'appuntamento in fattura';
$lang['appointment_are_you_sure_convert_to_invoice'] = 'Sei sicuro di voler convertire questo appuntamento in una fattura?';
$lang['appointment_view_invoice'] = 'Visualizza fattura #%s';
$lang['appointment_convert_to_invoice_tooltip'] = 'La creazione di fatture è disponibile solo per appuntamenti con clienti effettivi. Per lead o contatti esterni, devi prima convertirli in clienti.';
$lang['appointment_external_contact_converted_to_client'] = 'Contatto esterno convertito in cliente con successo';
$lang['appointment_external_contact_convert_to_client_error'] = 'Errore durante la conversione del contatto esterno in cliente';
$lang['appointment_convert_to_customer_first'] = 'Converti prima in cliente';
$lang['appointment_convert_lead_to_customer'] = 'Converti lead in cliente';
$lang['appointment_convert_external_to_customer'] = 'Converti in cliente';
$lang['appointment_convert_only'] = 'Converti solo';
$lang['appointment_convert_and_view'] = 'Converti e visualizza';
$lang['appointment_convert_to_invoice_only_contacts_allowed'] = 'Solo gli appuntamenti interni con contatti possono essere convertiti in fatture';

// Lead Conversion
$lang['appointments_convert_to_lead'] = 'Converti appuntamento in lead';
$lang['appointments_convert_to_lead_label'] = 'Converti in lead';
$lang['appointments_create_task_tooltip'] = 'Converti in attività';
$lang['appointments_convert_to_lead_tooltip'] = 'Converti in lead';
$lang['appointments_select_option'] = 'Seleziona opzione';
$lang['appointments_contact_name_task'] = 'Appuntamento: Nome contatto';

// Reports Dashboard
$lang['appointly_reports_dashboard'] = 'Dashboard rapporti Appointly';
$lang['appointly_date_range'] = 'Intervallo di date';
$lang['appointly_today'] = 'Oggi';
$lang['appointly_this_week'] = 'Questa settimana';
$lang['appointly_this_month'] = 'Questo mese';
$lang['appointly_this_year'] = 'Quest\'anno';
$lang['appointly_last_month'] = 'Il mese scorso';
$lang['appointly_last_year'] = 'L\'anno scorso';
$lang['appointly_last_30_days'] = 'Ultimi 30 giorni';
$lang['appointly_custom_range'] = 'Intervallo personalizzato';
$lang['appointly_period_from'] = 'Periodo dal';
$lang['appointly_period_to'] = 'Periodo al';
$lang['appointly_date_range_required'] = 'Seleziona un intervallo di date valido';
$lang['appointly_apply'] = 'Applica';
$lang['appointly_total_appointments'] = 'Totale appuntamenti';
$lang['appointly_completed_appointments'] = 'Appuntamenti completati';
$lang['appointly_cancelled_appointments'] = 'Appuntamenti annullati';
$lang['appointly_monthly_stats'] = 'Statistiche mensili';
$lang['appointly_popular_services'] = 'Servizi popolari';
$lang['appointly_staff_performance'] = 'Performance del personale';
$lang['appointly_staff_member'] = 'Membro del personale';
$lang['appointly_appointment_total_appointments'] = 'Totale appuntamenti';
$lang['appointly_appointment_completed_appointments'] = 'Appuntamenti completati';
$lang['appointly_appointment_cancelled_appointments'] = 'Appuntamenti annullati';
$lang['appointly_appointment_completion_rate'] = 'Tasso di completamento';
$lang['appointly_filter'] = 'Filtra';
$lang['appointly_no_staff_performance_data'] = 'Nessun dato sulle prestazioni del personale disponibile per il periodo selezionato';
$lang['appointly_report'] = 'Rapporto';
$lang['appointly_reports_updated_for_period'] = 'Rapporti aggiornati per il periodo: %s al %s';
$lang['appointly_no_data_for_period'] = 'Nessun dato disponibile per il periodo selezionato';
$lang['appointly_invalid_date_format'] = 'Formato data non valido. Utilizza il selettore di date per selezionare le date';
$lang['appointly_loading_data'] = 'Caricamento dati...';
$lang['appointly_error_refreshing_stats'] = 'Errore durante l\'aggiornamento delle statistiche. Riprova.';
$lang['appointly_no_data_found'] = 'Nessun dato trovato';

// Analytics Dashboard
$lang['total_appointments'] = 'Totale appuntamenti';
$lang['completed_appointments'] = 'Appuntamenti completati';
$lang['cancelled_appointments'] = 'Appuntamenti annullati';
$lang['monthly_statistics'] = 'Statistiche mensili';
$lang['popular_services'] = 'Servizi popolari';
$lang['staff_performance'] = 'Performance del personale';
$lang['staff_member'] = 'Membro del personale';
$lang['completion_rate'] = 'Tasso di completamento';
$lang['from_date'] = 'Dalla data';
$lang['to_date'] = 'Alla data';
$lang['appointment_statistics_total'] = 'Totale appuntamenti';
$lang['appointment_statistics_completed'] = 'Appuntamenti completati';
$lang['appointment_statistics_cancelled'] = 'Appuntamenti annullati';

$lang['appointment_services_to_show_in_booking_form'] = 'Seleziona quali servizi visualizzare nel modulo di prenotazione pubblico';
$lang['appointment_services_select_all_to_show_all'] = 'Lascia vuoto per mostrare tutti i servizi attivi';
$lang['appointment_select_attendees'] = 'Partecipanti';
$lang['appointment_select_attendees_help'] = 'Seleziona i membri del personale aggiuntivi che parteciperanno a questo appuntamento';
$lang['appointment_related'] = 'Correlato';

// Service Availability Settings
$lang['services_availability_updated_successfully'] = 'Disponibilità dei servizi aggiornata con successo';
$lang['services_availability_update_failed'] = 'Impossibile aggiornare la disponibilità dei servizi';
$lang['appointment_error_loading_providers'] = 'Errore durante il caricamento dei fornitori';
$lang['appointment_select_service_warning'] = 'Seleziona un servizio';
$lang['appointment_select_provider_warning'] = 'Seleziona un fornitore';
$lang['appointment_select_date_time_warning'] = 'Seleziona una data e un\'ora';
$lang['appointment_loading_providers'] = 'Caricamento fornitori...';
$lang['appointment_select_date'] = 'Seleziona data';
$lang['appointment_your_details'] = 'I tuoi dettagli';
$lang['appointment_continue'] = 'Avanti';
$lang['appointment_back'] = 'Indietro';
$lang['appointment_summary'] = 'Riepilogo appuntamento';
$lang['appointment_view_details'] = 'Visualizza dettagli';
$lang['appointment_select'] = 'Seleziona';
$lang['error_loading_data'] = 'Errore durante il caricamento dei dati';
$lang['appointment_booking_failed'] = 'Impossibile prenotare l\'appuntamento. Riprova.';
$lang['appointment_successfully_scheduled'] = 'Appuntamento programmato con successo';
$lang['appointment_successfully_scheduled_message'] = 'Grazie! Il tuo appuntamento è stato programmato con successo.';
$lang['appointment_successfully_scheduled_button'] = 'Fantastico!';
$lang['appointment_schedule_another'] = 'Programma un altro';
$lang['appointment_booking_confirmed'] = 'Grazie! Il tuo appuntamento è stato programmato con successo.';
$lang['appointment_pending_approval_message'] = 'Il tuo appuntamento è in attesa di approvazione da parte del personale. Sarai avvisato una volta confermato.';
$lang['appointment_whats_next'] = 'Cosa c\'è dopo?';
$lang['appointment_staff_review'] = 'Il nostro personale sta esaminando la tua richiesta. Attendi la conferma.';
$lang['appointment_email_confirmation'] = 'Riceverai a breve una conferma via email con tutti i dettagli dell\'appuntamento.';
$lang['appointment_prepare'] = 'Prepara tutti i documenti o i dettagli richiesti prima del tuo appuntamento.';
$lang['appointment_files'] = 'File';
$lang['appointment_externally_booked_info'] = 'Questo appuntamento è stato prenotato tramite il modulo di prenotazione esterno';
$lang['appointment_booked_from_external_booking_form'] = 'Prenotazione da';
$lang['appointment_subject_required'] = 'L\'oggetto è obbligatorio per l\'appuntamento';
$lang['appointment_attendees_required'] = 'Almeno un membro del personale deve partecipare all\'appuntamento';
$lang['appointment_name_required'] = 'Il nome è obbligatorio per gli appuntamenti esterni';
$lang['appointment_email_required'] = 'L\'email è obbligatoria per gli appuntamenti esterni';
$lang['appointment_email_invalid'] = 'Fornisci un indirizzo email valido';
$lang['appointment_contact_required'] = 'Seleziona un contatto per l\'appuntamento interno';
$lang['appointment_invalid_type'] = 'Tipo di appuntamento selezionato non valido';
$lang['appointment_type_required'] = 'Seleziona un tipo di appuntamento';
$lang['appointment_invalid_data'] = 'Dati appuntamento non validi forniti';
$lang['appointment_could_not_be_created'] = 'L\'appuntamento non è stato creato. Riprova.';
$lang['appointment_unavailable_slots'] = 'Gli slot rossi non sono disponibili a causa di appuntamenti esistenti';
$lang['appointment_book_now_description'] = 'Prenota il tuo appuntamento ora e ottieni il miglior servizio dal nostro team';
$lang['appointment_book_now_button_description'] = 'Prenota il tuo appuntamento ora e ottieni il miglior servizio dal nostro team';
$lang['appointment_feature_coming_soon'] = 'Questa funzione sarà disponibile nei prossimi aggiornamenti. Resta sintonizzato!';
$lang['appointment_description_updated'] = 'Descrizione appuntamento aggiornata con successo';
$lang['appointment_notes_updated'] = 'Note appuntamento aggiornate con successo';
$lang['appointment_notes_update_failed'] = 'Impossibile aggiornare le note dell\'appuntamento';
$lang['appointment_update_failed'] = 'Impossibile aggiornare l\'appuntamento';
$lang['appointment_description_edit_info'] = 'Clicca per modificare la descrizione. Le modifiche verranno salvate automaticamente al termine della modifica.';
$lang['appointment_viewing_notes'] = 'Visualizzazione note per appuntamento';
$lang['appointment_edit_history_notes'] = 'Modifica note';
$lang['appointment_email_missing'] = 'Indirizzo email mancante o non valido per questo appuntamento';
$lang['appointment_no_name_provided'] = 'Nessun nome fornito';
$lang['appointment_no_phone_provided'] = 'Nessun telefono fornito';
$lang['appointment_open_link'] = 'Apri link';
$lang['appointment_menu_form_link'] = 'Modulo di prenotazione';
$lang['external_form_heading'] = 'Intestazione modulo di prenotazione';
$lang['external_form_description'] = 'Descrizione modulo di prenotazione';
$lang['appointment_date_location'] = 'Dettagli sessione';
$lang['appointment_schedule_description'] = 'Completa il modulo sottostante per organizzare la tua sessione con il nostro team';
$lang['appointment_preferred_date_time'] = 'Data e ora preferite';
$lang['appointment_select_date_time'] = 'Seleziona...';
$lang['appointment_terms_description'] = 'Procedendo, confermi l\'accettazione delle nostre';
$lang['appointment_terms_link'] = 'Termini e condizioni';
$lang['appointment_accept_terms'] = 'Accetto i Termini di servizio*';
$lang['appointly_recaptcha_enabled'] = 'Abilita Recaptcha';
$lang['appointment_no_location_provided'] = 'Nessuna posizione fornita per il rendering di Google Maps';
$lang['appointment_imported_from_calendar'] = 'Importato da %s';
$lang['appointment_imported_cant_edit_notice'] = 'Questo appuntamento è stato importato da un calendario esterno e non può essere modificato.';
$lang['appointment_are_you_sure'] = 'Sei sicuro di voler eliminare questo appuntamento?';
$lang['would_you_like_to_create_new_appointment_for_lead'] = 'Vuoi creare un nuovo appuntamento per questo lead?';
$lang['would_you_like_to_create_new_appointment_for_client'] = 'Vuoi creare un nuovo appuntamento per questo cliente?';
$lang['would_you_like_to_create_new_appointment'] = 'Vuoi creare un nuovo appuntamento?';
$lang['no_appointments_found'] = 'Nessun appuntamento trovato';

// ----------------------
// EXTERNAL BOOKING FORM
// ----------------------
$lang['appointment_available_days'] = 'Disponibile';
$lang['appointment_busy_days'] = 'Occupato (ha appuntamenti)';
$lang['appointment_provider_unavailable'] = 'Fornitore non disponibile';
$lang['appointment_blocked_days'] = 'Festa aziendale/Data bloccata';
$lang['appointment_date_required'] = 'Seleziona prima una data';
$lang['appointment_select_time'] = 'Seleziona un\'ora';
$lang['appointment_book_now'] = 'Prenota ora';
$lang['appointment_submitting'] = 'Prenotazione appuntamento...';

// New appointment filter options
$lang['appointment_today'] = 'Appuntamenti di oggi';
$lang['appointment_tomorrow'] = 'Appuntamenti di domani';
$lang['appointment_this_week'] = 'Appuntamenti di questa settimana';
$lang['appointment_next_week'] = 'Appuntamenti della prossima settimana';
$lang['appointment_this_month'] = 'Appuntamenti di questo mese';
$lang['appointment_my_appointments'] = 'I miei appuntamenti';
$lang['appointment_assigned_to_me'] = 'Assegnato a me';


// ----------------------
// GENERAL/COMMON TERMS
// ----------------------
$lang['appointment_yes'] = 'Sì';
$lang['appointment_no'] = 'No';
$lang['appointment_appointments'] = 'Appuntamenti';
$lang['appointment_label'] = 'Appuntamento';
$lang['wait_text'] = 'Attendere prego...';
$lang['loading_text'] = 'Caricamento, attendere prego...';
$lang['appointment_loading'] = 'Caricamento...';
$lang['unknown_error'] = 'Errore sconosciuto';
$lang['request_failed'] = 'Richiesta fallita';
$lang['error_processing_response'] = 'Errore durante l\'elaborazione della risposta';
$lang['invalid_appointment_id'] = 'ID appuntamento non valido';
$lang['appointment_select_option'] = 'Seleziona opzione';
$lang['appointly_note'] = 'Nota';
$lang['appointment_optional'] = '<small> (opzionale) </small>';
$lang['required_field_missing'] = 'Il campo è obbligatorio';
$lang['appointly_required_field'] = 'Campo obbligatorio';
$lang['settings_updated'] = 'Impostazioni aggiornate con successo';

// Time and date terms
$lang['timezone'] = 'Fuso orario';
$lang['minutes'] = 'Minuti';
$lang['hours'] = 'Ore';
$lang['monday'] = 'Lunedì';
$lang['tuesday'] = 'Martedì';
$lang['wednesday'] = 'Mercoledì';
$lang['thursday'] = 'Giovedì';
$lang['friday'] = 'Venerdì';
$lang['saturday'] = 'Sabato';
$lang['sunday'] = 'Domenica';
$lang['today'] = 'Oggi';
$lang['this_week'] = 'Questa settimana';
$lang['this_month'] = 'Questo mese';
$lang['this_year'] = 'Quest\'anno';
$lang['date_range'] = 'Intervallo di date';
$lang['custom_range'] = 'Intervallo personalizzato';
$lang['filter'] = 'Filtra';

// ----------------------
// APPOINTMENT BASICS
// ----------------------
$lang['appointment_back_to_appointments'] = 'Appuntamenti';
$lang['appointment_create_new_appointment'] = 'Pianifica una consulenza';
$lang['appointment_select_contact'] = 'Seleziona contatto';
$lang['appointment_new_appointment'] = 'Crea appuntamento';
$lang['appointment_edit_appointment'] = 'Aggiorna appuntamento';
$lang['appointment_save_changes_btn_label'] = 'Salva modifiche';
$lang['appointment_subject'] = 'Scopo della riunione';
$lang['appointment_description'] = 'Panoramica della sessione';
$lang['appointment_overview'] = 'Panoramica appuntamento';
$lang['appointment_additional_info'] = 'Informazioni aggiuntive';
$lang['appointment_date'] = 'Data';
$lang['appointment_time'] = 'Ora preferita';
$lang['appointment_date_and_time'] = 'Data / Ora';
$lang['appointment_date_time'] = 'Data & Ora';
$lang['appointment_meeting_date'] = 'Data appuntamento';
$lang['appointment_meeting_time'] = 'Data appuntamento';
$lang['appointments_reminders_label'] = 'Promemoria';
$lang['appointment_meeting_location'] = 'Posizione';
$lang['appointment_location'] = 'Posizione';
$lang['appointment_location_address'] = 'Posizione / Indirizzo';
$lang['appointment_location_placeholder'] = 'Inserisci i dettagli della posizione';
$lang['appointment_duration'] = 'Durata appuntamento';
$lang['appointment_duration_label'] = 'Durata';
$lang['appointment_duration_help'] = 'Imposta la durata dell\'appuntamento in minuti';
$lang['appointment_notes'] = 'Note';
$lang['appointment_private_notes'] = 'Note private';
$lang['appointment_client_notes'] = 'Note appuntamento';
$lang['appointment_created_by'] = 'Creato da';
$lang['appointly_created_at'] = 'Creato il';
$lang['appointment_schedule_details'] = 'Dettagli appuntamento';
$lang['appointment_additional_settings'] = 'Impostazioni aggiuntive';

// Appointment Status
$lang['appointment_status'] = 'Stato';
$lang['appointment_status_text'] = 'Stato appuntamento';
$lang['appointment_status_pending'] = 'In sospeso';
$lang['appointment_status_in-progress'] = 'In corso';
$lang['appointment_status_completed'] = 'Completato';
$lang['appointment_status_cancelled'] = 'Annullato';
$lang['appointment_status_no-show'] = 'No Show';
$lang['appointment_upcoming'] = 'Prossimo';
$lang['appointment_finished'] = 'Terminato';
$lang['appointment_ongoing'] = 'In corso';
$lang['appointment_cancelled'] = 'Annullato';
$lang['appointment_rescheduled'] = 'Riprogrammato';
$lang['appointment_no_show'] = 'No-show';
$lang['appointment_missed_label'] = 'Mancato';
$lang['appointment_pending_approval'] = 'In attesa di approvazione';
$lang['appointment_not_approved'] = 'In attesa di approvazione';
$lang['appointment_pending_cancellation'] = 'Annullamento in sospeso';
$lang['appointment_cancelled_text'] = 'Appuntamento annullato';
$lang['appointment_missed'] = 'Appuntamento mancato (Data/Ora appuntamento è passata)';
$lang['appointment_are_you_sure_mark_as_no_show'] = 'Sei sicuro di voler contrassegnare questo appuntamento come No Show?';
$lang['appointment_completed'] = 'Completato';
$lang['appointment_internal'] = 'Interno';
$lang['appointment_external'] = 'Esterno';
$lang['appointment_lead_related'] = 'Lead';
$lang['appointment_internal_staff'] = 'Personale';

// Status and Action Buttons
$lang['appointment_approve'] = 'Approva';
$lang['appointment_approved'] = 'Approvato';
$lang['appointment_mark_as_finished'] = 'Contrassegna come terminato';
$lang['appointment_mark_as_ongoing'] = 'Contrassegna come in corso';
$lang['appointment_mark_as_cancelled'] = 'Annullato';
$lang['appointment_mark_as_rescheduled'] = 'Contrassegna come riprogrammato';
$lang['appointment_mark_as_no_show'] = 'Contrassegna come no-show';
$lang['appointment_cancel'] = 'Annulla appuntamento';
$lang['appointment_description_to_cancel'] = 'Descrivi perché vuoi annullare questo appuntamento';
$lang['appointment_describe_reason_for_cancel'] = 'La descrizione è obbligatoria. Descrivi qual è il motivo dell\'annullamento dell\'appuntamento?';
$lang['appointment_request_to_cancel'] = 'Richiedi annullamento';
$lang['appointment_request_cancellation'] = 'Richiedi annullamento';
$lang['appointment_approve_cancellation'] = 'Approva annullamento';
$lang['appointment_marked_as_no_show'] = 'Contrassegnato come No Show';
$lang['appointly_are_you_sure_mark_as_no_show'] = 'Sei sicuro di voler contrassegnare questo appuntamento come No Show?';

$lang['appointment_you_have_new_appointment'] = 'Hai un nuovo appuntamento';
$lang['appointment_initiated_by'] = 'Organizzatore';
$lang['appointment_select_single_contact'] = 'Contatto';
$lang['appointment_deleted'] = 'Appuntamento eliminato con successo';
$lang['appointment_created'] = 'Nuovo appuntamento creato con successo';
$lang['appointment_updated'] = 'Appuntamento aggiornato con successo';
$lang['appointment_appointment_approved'] = 'Appuntamento approvato con successo!';
$lang['appointment_no_appointments'] = 'Non hai appuntamenti per oggi';
$lang['appointment_please_wait'] = 'Attendere prego...';
$lang['appointment_no_assigned_staff_found'] = 'Nessun partecipante assegnato trovato per questo appuntamento';
$lang['appointment_cancel_notification'] = 'Il cliente ha richiesto l\'annullamento dell\'appuntamento';
$lang['appointment_marked_as_finished'] = 'Appuntamento contrassegnato come terminato';
$lang['appointment_todays_appointments'] = 'Appuntamenti di oggi';
$lang['appointment_scheduled_at'] = 'Programmato alle:';
$lang['appointment_view_meeting'] = 'Visualizza appuntamento';
$lang['appointment_edit_meeting'] = 'Modifica appuntamento';
$lang['appointment_dismiss_meeting'] = 'Elimina appuntamento';
$lang['appointment_not_exists'] = 'Appuntamento non trovato, reindirizzamento all\'elenco appuntamenti';
$lang['appointment_marked_as_ongoing'] = 'Appuntamento contrassegnato come in corso';
$lang['appointment_general_info'] = 'Dettagli cliente';
$lang['appointment_general_details'] = 'Dettagli appuntamento';
$lang['appointment_source'] = 'Tipo';
$lang['appointment_source_external_text'] = 'Esterno (Contatto)';
$lang['appointment_source_external'] = 'Fonte (Contatto esterno)';
$lang['appointment_source_external_contact'] = 'Contatto esterno';
$lang['appointment_source_internal'] = 'Interno (Contatto)';
$lang['appointment_lead_required'] = 'Seleziona un lead per l\'appuntamento';
$lang['appointment_source_internal_client'] = 'Interno (Cliente)';
$lang['appointment_source_internal_staff'] = 'Interno (Personale)';
$lang['appointment_source_lead'] = 'Lead';
$lang['appointment_staff_only'] = 'Solo personale';
$lang['appointment_phone'] = 'Telefono';
$lang['appointment_name'] = 'Nome';
$lang['appointment_email'] = 'Email';
$lang['appointment_contact'] = 'Dettagli cliente';
$lang['appointment_sent_successfully'] = 'La tua nuova richiesta di appuntamento è stata inviata con successo, sarai avvisato via email quando il tuo appuntamento sarà approvato';
$lang['appointment_squeduled_at_text'] = 'Appuntamento programmato per iniziare alle';
$lang['appointment_staff_attendees'] = 'Partecipanti';
$lang['appointment_is_approved'] = 'Appuntamento approvato!';
$lang['appointment_public_url'] = 'URL pubblico';
$lang['appointment_is_cancelled'] = 'Appuntamento annullato!';
$lang['appointment_cancel_notes'] = 'Note di annullamento';
$lang['appointment_full_name'] = 'Nome cliente';
$lang['appointment_your_email'] = 'La tua email';
$lang['appointment_your_phone'] = 'Telefono (con prefisso internazionale)';
$lang['appointment_your_phone_example'] = '+1 69 1234 5678';
$lang['appointment_submit'] = 'Invia';
$lang['appointment_no_staff_members'] = 'Nessun membro del personale trovato, devi aggiungere un membro del personale e ricaricare questa vista per selezionare una persona per i nuovi appuntamenti inviati tramite modulo iFrame.';
$lang['appointment_cancellation_description_label'] = 'Motivo dell\'annullamento';
$lang['appointments_thank_you_cancel_request'] = 'Grazie per la tua richiesta di annullamento. La esamineremo a breve.';
$lang['appointments_already_applied_for_cancelling'] = 'Hai già richiesto di annullare questo appuntamento.';
$lang['appointment_pending_cancellations'] = 'Richieste di annullamento in sospeso';
$lang['appointment_requested_by'] = 'Richiesto da';
$lang['appointment_cancellation_approved'] = 'Richiesta di annullamento approvata con successo';
$lang['appointly_schedule_new_appointment'] = 'Pianifica appuntamento';
$lang['appointments_total_found'] = 'Totale appuntamenti';
$lang['appointments_admin_label'] = 'Amministratore';
$lang['appointments_staff_label'] = 'Personale';
$lang['appointments_no_delete_permissions'] = 'Questo appuntamento non è stato creato da te, impossibile eliminarlo';
$lang['appointment_source_external_clients_area'] = 'Fonte (Dall\'area clienti contatto esistente)';
$lang['appointments_source_external_label'] = 'Esterno';
$lang['appointments_source_internal_label'] = 'Interno';
$lang['appointments_individual_contact'] = ' (Contatto individuale)';
$lang['appointments_company_for_select'] = ' - Cliente ';
$lang['appointment_preview_url_label'] = 'Anteprima';
$lang['appointment_booking_form_services'] = 'Servizi modulo di prenotazione';
$lang['appointment_source_leads_label'] = 'Lead';
$lang['appointment_connect'] = 'Connetti';
$lang['appointment_connected'] = 'Connesso';
$lang['appointments_outlook_revoke_confirm'] = 'Disconnetti da Outlook';
$lang['appointment_selected_service'] = 'Servizio selezionato';
$lang['appointment_please_enter_your_details'] = 'Inserisci i tuoi dettagli';
$lang['appointments_request_feedback_from_client'] = 'Richiedi feedback al cliente';
$lang['appointments_request_feedback'] = 'Richiedi feedback';
$lang['appointment_feedback_label'] = 'Feedback';
$lang['appointment_view_feedback'] = 'Visualizza feedback';
$lang['appointment_feedback_label_added'] = 'Grazie per il tuo feedback!';
$lang['appointment_feedback_label_current'] = 'Il tuo feedback attuale!';
$lang['appointments_feedback_info'] = 'Gestisci i tuoi stati di feedback predefiniti';
$lang['ap_feedback_extremely_good'] = 'Estremamente buono';
$lang['ap_feedback_very_good'] = 'Molto buono';
$lang['ap_feedback_good'] = 'Buono';
$lang['ap_feedback_not_bad'] = 'Non male';
$lang['ap_feedback_bad'] = 'Cattivo';
$lang['ap_feedback_the_worst'] = 'Il peggiore';
$lang['ap_feedback_not_sure'] = 'Non sono sicuro';
$lang['appointment_feedback_title'] = 'Lascia il tuo feedback per questo appuntamento';
$lang['appointmenet_feedback_comment'] = 'I tuoi commenti e pensieri su questo appuntamento: ';
$lang['appointment_feedback_comment_textarea'] = 'Sono richieste almeno alcune parole per descrivere la tua esperienza con questo appuntamento';
$lang['appointment_feedback_comment_textarea_info'] = 'Descrivi la tua esperienza con questo appuntamento';
$lang['appointment_new_feedback_added'] = 'Hai un nuovo feedback per un appuntamento';
$lang['appointly_feedback_updated'] = 'La valutazione del feedback è stata appena aggiornata';
$lang['appointment_email_tracking'] = 'Tracciamento email (l\'email dell\'appuntamento è stata letta)';
$lang['appointment_feedback_requested_alert'] = 'Il feedback è stato richiesto con successo, sarai avvisato via email se il feedback viene fornito!';
$lang['appointment_click_to_change_rating'] = 'Clicca sulle stelle per aggiornare la tua valutazione';
$lang['appointment_staff_cant_provide_feedback'] = 'I membri del personale non possono fornire feedback per gli appuntamenti';
$lang['appointment_leave_feedback'] = 'Valuta la tua esperienza con questo appuntamento';
$lang['appointment_your_feedback'] = 'Il tuo feedback';
$lang['appointment_no_feedback_provided'] = 'Il cliente non ha ancora fornito feedback per questo appuntamento';
$lang['appointments_are_you_sure_request_feedback'] = 'Sei sicuro di voler richiedere feedback per questo appuntamento? Verrà inviata un\'email al cliente.';

// Client Area
$lang['appointly_allow_non_logged_clients_appointment'] = 'Consenti ai clienti non loggati di richiedere nuovi appuntamenti tramite il modulo di prenotazione esterno';
$lang['appointly_show_appointments_menu_item_in_clients_area'] = 'Mostra la voce di menu della richiesta di appuntamento nell\'area clienti (quando loggato)';
$lang['appointments_applies_for_clients'] = '(si applica solo ai clienti)';

// ----------------------
// TIME SLOTS & AVAILABILITY
// ----------------------
$lang['appointly_no_providers_for_service'] = 'Nessun fornitore disponibile per questo servizio';
$lang['appointly_no_providers_with_hours'] = 'Nessun fornitore con orari di lavoro disponibili per questo servizio';
$lang['appointly_select_staff'] = 'Seleziona membro del personale';
$lang['appointment_busy_hours'] = 'Ore occupate';
$lang['appointment_available_hours'] = 'Ore disponibili';
$lang['appointment_meeting_hour_is_reserved'] = 'L\'ora dell\'appuntamento è già riservata';
$lang['appointment_requested_hour'] = 'Ora della riunione richiesta';
$lang['appointment_time_unavailable'] = 'Questo slot orario non è disponibile';
$lang['appointment_date_blocked'] = 'Questa data è bloccata dall\'amministratore';
$lang['appointly_available_time_slots'] = 'Slot orari disponibili';
$lang['appointment_available_time_slots'] = 'Slot orari disponibili';
$lang['appointment_no_slots_available'] = 'Nessun slot orario disponibile per questo giorno';
$lang['appointment_slot_already_booked'] = 'Questo slot orario è già prenotato';
$lang['appointment_unavailable_slots_shown'] = 'Gli slot orari non disponibili sono mostrati in rosso e non possono essere selezionati';
$lang['appointment_all_slots_booked'] = 'Tutti gli slot orari per questo giorno sono prenotati. Prova un\'altra data.';
$lang['appointment_slot_unavailable'] = 'Questo slot orario non è disponibile';
$lang['appointment_checking_availability'] = 'Verifica disponibilità...';
$lang['appointment_checking_time_slots'] = 'Caricamento slot orari...';
$lang['appointment_error_loading_slots'] = 'Errore durante il caricamento degli slot orari. Riprova.';
$lang['appointment_not_available'] = 'Non disponibile';
$lang['appointment_available'] = 'Disponibile';

// Schedule and calendar
$lang['appointment_recurring'] = 'Ricorrente';
$lang['appointment_recurring_re_created'] = 'Appuntamento ricorrente ricreato';
$lang['appointments_all_day_event'] = 'Evento di un\'intera giornata';
$lang['select_blocked_days'] = 'Seleziona giorni';
$lang['appointments_blocked_days_on_calendar_title'] = 'Giorni bloccati<br><small class="text-muted">Seleziona le date in cui gli appuntamenti non possono essere programmati (festività, chiusure aziendali, ecc.).<br> Queste date non saranno disponibili per la prenotazione interna ed esterna.</small>';
$lang['appointments_dates_blocked_info_text'] = 'Le date selezionate non saranno disponibili nel calendario di prenotazione. Le riunioni non possono essere programmate internamente o esternamente in queste date.';
$lang['appointments_blocked_days_tab_title'] = 'Giorni non lavorativi';

// Working Hours and Schedules
$lang['appointments_default_hours_label'] = 'Gestisci i tuoi orari di appuntamento predefiniti';
$lang['appointly_company_schedule'] = 'Programma aziendale';
$lang['appointly_company_schedule_info'] = 'Configura gli orari di lavoro predefiniti per la tua azienda. Questi orari verranno utilizzati per tutti i membri del personale a meno che non abbiano i propri orari di lavoro specifici impostati.';
$lang['appointly_manage_company_schedule'] = 'Gestisci programma aziendale';
$lang['appointly_staff_working_hours'] = 'Orari di lavoro del personale';
$lang['appointly_staff_working_hours_info'] = 'Configura gli orari di lavoro per questo membro del personale. Questi orari sovrascriveranno il programma aziendale quando questo membro del personale viene selezionato come fornitore.';
$lang['appointly_view_staff_schedule'] = 'Visualizza programma del personale';
$lang['appointly_day'] = 'Giorno';
$lang['appointly_enabled'] = 'Abilitato';
$lang['appointly_available'] = 'Disponibile';
$lang['appointly_start_time'] = 'Ora di inizio';
$lang['appointly_end_time'] = 'Ora di fine';
$lang['appointly_use_company_schedule'] = 'Usa programma aziendale';
$lang['appointly_use_company_schedule_tooltip'] = 'Seleziona per utilizzare le impostazioni del programma aziendale per questo giorno invece delle impostazioni personalizzate.';
$lang['appointly_day_monday'] = 'Lunedì';
$lang['appointly_day_tuesday'] = 'Martedì';
$lang['appointly_day_wednesday'] = 'Mercoledì';
$lang['appointly_day_thursday'] = 'Giovedì';
$lang['appointly_day_friday'] = 'Venerdì';
$lang['appointly_day_saturday'] = 'Sabato';
$lang['appointly_day_sunday'] = 'Domenica';
$lang['appointly_at_least_one_day_required'] = 'Almeno un giorno deve essere abilitato';
$lang['appointly_no_working_hours_found'] = 'Nessun orario di lavoro impostato per questo fornitore';
$lang['appointly_closed'] = 'Chiuso';
$lang['appointly_working_hours'] = 'Orari di lavoro';
$lang['company_schedule_time_intervals_note'] = 'Gli orari possono essere impostati solo a intervalli di 15 minuti (es. 09:00, 09:15, 09:30, 09:45)';
$lang['working_hours_time_intervals_note'] = 'Gli slot orari sono disponibili a intervalli di 15 minuti';
$lang['appointly_time_error'] = 'L\'ora di inizio deve essere precedente all\'ora di fine per';
$lang['appointly_company_schedule_sync_help'] = 'Il personale può utilizzare l\'opzione "Usa programma aziendale" per sincronizzare i propri orari con i valori predefiniti dell\'azienda.';

// Buffer settings
$lang['appointly_settings_buffer_times'] = 'Abilita tempi di buffer tra gli appuntamenti';
$lang['appointly_settings_buffer_times_info'] = 'I tempi di buffer consentono periodi di transizione tra gli appuntamenti';
$lang['appointly_buffer_before'] = 'Buffer prima (minuti)';
$lang['appointly_buffer_after'] = 'Buffer dopo (minuti)';
$lang['appointly_buffer_before_help'] = 'Tempo aggiuntivo prima dell\'appuntamento per prepararsi';
$lang['appointly_buffer_after_help'] = 'Tempo aggiuntivo dopo l\'appuntamento per riordinare';

// ----------------------
// SERVICES & PROVIDERS
// ----------------------
$lang['appointly_services'] = 'Servizi';
$lang['service'] = 'Servizio';
$lang['appointment_service'] = 'Servizio';
$lang['appointment_services'] = 'Servizi';
$lang['appointment_select_service'] = 'Seleziona servizio';
$lang['appointment_service_duration'] = 'Durata';
$lang['appointment_service_price'] = 'Prezzo';
$lang['appointment_service_description'] = 'Descrizione';
$lang['appointments_service_heading'] = 'Servizio';
$lang['appointments_staff_heading'] = 'Personale';
$lang['service_selection_required'] = 'Seleziona un servizio';
$lang['appointment_service_required'] = 'Seleziona un servizio per l\'appuntamento';
$lang['appointly_service_selection_label'] = 'Servizio';
$lang['appointments_selected_service'] = 'Servizio selezionato';
$lang['no_services_available'] = 'Nessun servizio disponibile';

// Service Creation and Management
$lang['appointly_new_service'] = 'Nuovo servizio';
$lang['appointly_edit_service'] = 'Modifica servizio';
$lang['appointly_service_add_success'] = 'Servizio aggiunto con successo';
$lang['appointly_service_edit_success'] = 'Servizio aggiornato con successo';
$lang['appointly_service_delete_success'] = 'Servizio eliminato con successo';
$lang['appointly_service_delete_error'] = 'Impossibile eliminare il servizio';
$lang['error_adding_service'] = 'Impossibile aggiungere il servizio';
$lang['error_updating_service'] = 'Impossibile aggiornare il servizio';
$lang['service_delete_error_active'] = 'Il servizio è attivo e non può essere eliminato o disabilitato.';
$lang['appointly_service_in_use_warning'] = 'Questo servizio è attualmente in uso in uno o più appuntamenti e non può essere eliminato o disabilitato.';

// Service Properties
$lang['service_availability_days'] = 'Giorni disponibili';
$lang['service_hours_start'] = 'Orario di lavoro inizio';
$lang['service_hours_end'] = 'Orario di lavoro fine';
$lang['appointly_service_name'] = 'Nome';
$lang['appointly_service_name_label'] = 'Nome servizio';
$lang['appointly_service_duration'] = 'Durata';
$lang['appointly_service_price'] = 'Prezzo';
$lang['appointly_service_color'] = 'Colore';
$lang['appointly_service_description'] = 'Descrizione';
$lang['appointly_service_active'] = 'Attivo';
$lang['appointly_service_details'] = 'Dettagli servizio';
$lang['appointly_service_back_to_list'] = 'Torna all\'elenco servizi';
$lang['appointly_duration_validation'] = 'La durata deve essere a intervalli di 15 minuti (15, 30, 45, ecc.) e massimo 480 minuti';
$lang['appointly_price_validation'] = 'Il prezzo non può essere negativo';
$lang['appointly_duration_minutes'] = 'minuti';
$lang['appointly_service_staff'] = 'Assegnato a fornitore/personale';
// Service Validation
$lang['service_name_required'] = 'Il nome del servizio è obbligatorio';
$lang['service_duration_required'] = 'La durata del servizio è obbligatoria';
$lang['service_duration_numeric'] = 'La durata deve essere un numero';
$lang['service_duration_greater'] = 'La durata deve essere maggiore di 0';
$lang['service_price_required'] = 'Il prezzo del servizio è obbligatorio';
$lang['service_price_greater_equal'] = 'Il prezzo deve essere 0 o maggiore';
$lang['service_days_required'] = 'Seleziona almeno un giorno disponibile';
$lang['service_hours_required'] = 'Gli orari di servizio sono obbligatori';
$lang['service_hours_invalid'] = 'Formato ora non valido';
$lang['service_hours_start_end'] = 'L\'ora di fine deve essere successiva all\'ora di inizio';
$lang['appointly_staff_required'] = 'Seleziona un membro del personale';
$lang['appointly_working_hours_required'] = '%s ore di lavoro sono obbligatorie.';
$lang['appointly_working_hours_invalid'] = 'L\'ora di fine di %s deve essere successiva all\'ora di inizio.';
$lang['appointly_working_hours_at_least_one'] = 'Almeno un giorno deve essere abilitato.';

// Service Table Headers
$lang['service_th_name'] = 'Nome';
$lang['service_th_duration'] = 'Durata (minuti)';
$lang['service_th_price'] = 'Prezzo';
$lang['service_th_availability'] = 'Disponibilità';
$lang['service_th_status'] = 'Stato';
$lang['service_th_options'] = 'Opzioni';

// Service Status
$lang['service_status_active'] = 'Attivo';
$lang['service_status_inactive'] = 'Inattivo';
$lang['service_status_changed_success'] = 'Stato del servizio aggiornato con successo';
$lang['service_status_changed_error'] = 'Impossibile aggiornare lo stato del servizio';
$lang['service_toggle_active'] = 'Attiva/disattiva stato attivo';
$lang['error_updating_status'] = 'Errore durante l\'aggiornamento dello stato. Riprova.';
$lang['service_status_updated'] = 'Stato del servizio aggiornato con successo';
$lang['service_status_update_failed'] = 'Impossibile aggiornare lo stato del servizio';

// Providers
$lang['appointment_provider'] = 'Fornitore';
$lang['appointly_provider'] = 'Fornitore';
$lang['service_provider_loading'] = 'Caricamento fornitori disponibili...';
$lang['service_no_providers'] = 'Nessun fornitore disponibile per questo servizio';
$lang['service_provider_select'] = 'Seleziona fornitore';
$lang['appointment_select_provider'] = 'Seleziona fornitore';
$lang['appointly_select_provider'] = 'Seleziona fornitore';
$lang['appointly_no_staff'] = 'Nessun membro del personale disponibile';
$lang['appointly_error_loading_schedule'] = 'Errore durante il caricamento del programma del fornitore';
$lang['appointment_no_provider_assigned'] = 'Nessun fornitore assegnato';
$lang['appointly_meeting_location'] = 'Luogo della riunione';

// Multiple Providers
$lang['appointly_settings_multi_providers'] = 'Abilita più fornitori per servizio';
$lang['appointly_settings_multi_providers_info'] = 'Consenti che i servizi siano forniti da più membri del personale';
$lang['appointly_primary_provider'] = 'Fornitore principale';
$lang['appointly_add_provider'] = 'Aggiungi fornitore';
$lang['appointly_remove_provider'] = 'Rimuovi fornitore';
$lang['appointly_service_providers'] = 'Fornitori di servizi';
$lang['appointly_confirm_provider_removal'] = 'Sei sicuro di voler rimuovere questo fornitore?';
$lang['appointly_assigned_providers'] = 'Fornitori assegnati';
$lang['appointly_service_primary_provider'] = 'Fornitore principale';
$lang['appointment_external_provider'] = 'Fornitore esterno';

// ----------------------
// NOTIFICATIONS & REMINDERS
// ----------------------
$lang['appointment_modal_notification_info'] = 'Contrassegna le caselle di controllo se desideri che i partecipanti e il contatto selezionati ricevano promemoria, ad esempio se impostato 30 minuti prima dell\'inizio dell\'appuntamento. Tieni presente che questa funzione richiede la configurazione di un cron job.';
$lang['appointment_sms_notification_text'] = 'Invia notifiche SMS';
$lang['appointment_email_notification_text'] = 'Invia notifiche email';
$lang['appointment_send_notification'] = 'Invia notifica ora';
$lang['appointment_notified'] = 'Promemoria appuntamenti';
$lang['appointment_notified_by_sms'] = 'Notifica promemoria via SMS attivata';
$lang['appointment_notified_by_email'] = 'Notifica promemoria via Email attivata';
$lang['appointment_send_early_reminders_label'] = 'Invia promemoria anticipati';
$lang['appointly_are_you_early_reminders'] = 'Sei sicuro di voler inviare promemoria anticipati per l\'appuntamento?';
$lang['appointly_reminders_sent'] = 'I promemoria dell\'appuntamento sono stati inviati a tutti i partecipanti e al contatto';
$lang['appointment_manually_send_reminders_info'] = 'Invia manualmente promemoria di notifica a tutti i partecipanti';
$lang['appointment_early_reminders_notice_label'] = 'L\'appuntamento non è annullato né terminato per inviare promemoria anticipati';
$lang['appointment_email_read_at'] = 'Letto il: ';
$lang['appointment_email_not_read'] = 'Non letto';
$lang['appoontment_sms_notification'] = 'Notifica SMS';
$lang['appoontment_email_notification'] = 'Notifica Email';

// ----------------------
// CALENDAR INTEGRATIONS
// ----------------------
// Google Calendar
$lang['appointly_calendar_integrations'] = 'Integrazioni calendario';
$lang['appointment_add_to_google_calendar'] = 'Aggiungi a Google Calendar';
$lang['appointments_google_already_signed'] = 'Sei già connesso al tuo account Google.';
$lang['appointments_added_to_google_calendar'] = 'Aggiunto a Google Calendar';
$lang['appointments_sign_in_google'] = 'Accedi con Google';
$lang['appointments_google_revoke_confirm'] = 'Esci da Google';
$lang['appointments_google_revoke'] = 'Revoca la sessione corrente di Google Calendar e rimuovi tutte le autorizzazioni concesse al tuo account Google.';
$lang['appointments_google_calendar_client_id'] = 'ID client API Google Calendar <strong>(recuperato da Impostazioni->Google->ID CLIENT API)</strong>';
$lang['appointments_google_calendar_settings'] = 'Impostazioni API Google Calendar';
$lang['appointments_google_calendar_client_secret'] = 'Segreto client API Google Calendar';
$lang['appointments_redirect_url'] = 'URI di reindirizzamento autorizzazione Google';
$lang['appointly_show_google_appointments_from'] = 'Filtra appuntamenti per intervallo di date:';
$lang['appointments_delete_from_google_label'] = 'Prima che l\'appuntamento venga eliminato, elimina anche l\'appuntamento creato nel tuo Google Calendar <small>(Si applica solo se Google Calendar è abilitato e sincronizzato)</small>';
$lang['appointment_add_to_google_calendar_external'] = 'Includi questo appuntamento esterno nel tuo Google Calendar (Contrassegna e clicca Salva)';
$lang['appointment_open_google_calendar'] = 'Apri in Google Calendar';
$lang['appointment_google_not_added_yet'] = 'Sembra che questo appuntamento non sia ancora stato aggiunto al Google Calendar di nessun membro del personale. Vuoi aggiungere questo appuntamento al tuo Google Calendar?';
$lang['appointment_add_to_calendar'] = 'Aggiungi al calendario';
$lang['appointment_view_in_calendar'] = 'Visualizza in Google Calendar';
$lang['appointment_calendar_adding_to_google'] = 'Aggiunta a Google Calendar...';
$lang['appointment_error_adding_to_calendar'] = 'Impossibile aggiungere al calendario. Riprova.';
$lang['event_not_found_in_google'] = 'L\'evento potrebbe non esistere in Google Calendar';
$lang['appointments_delete_from_google_calendar'] = 'Elimina da Google Calendar';
$lang['appointments_synced_from_google'] = 'Sincronizzato da Google';
$lang['appointments_googlesync_show_in_table_label'] = 'Se l\'integrazione di Google Calendar è attiva, visualizza tutti gli appuntamenti di Google Calendar nella vista tabella predefinita.';
$lang['appointly_google_synced_title'] = ' Sincronizzato Google';
$lang['appointment_hide_google_calendar'] = 'Mostra vista predefinita';
$lang['appointment_google_calendar_synced'] = 'Google Calendar sincronizzato';
$lang['appointment_googlesync_only_today'] = 'Oggi';
$lang['appointment_googlesync_only_last_month'] = 'Il mese scorso';
$lang['appointment_googlesync_only_last_three_months'] = 'Ultimi 3 mesi';
$lang['appointment_googlesync_only_last_six_months'] = 'Ultimi 6 mesi';
$lang['appointment_googlesync_only_last_year'] = 'L\'anno scorso';
$lang['appointment_googlesync_show_all'] = 'Tutti';
$lang['appointly_not_including_two_way_synced_appointments'] = 'Non include appuntamenti sincronizzati bidirezionalmente';
$lang['appointment_external_calendar_event'] = 'Evento calendario esterno';

// Google Meet
$lang['appointment_google_meet_info'] = 'Questo appuntamento è aggiunto a Google Calendar, puoi usare Google Meet per incontrare i tuoi clienti online';
$lang['appointment_google_meet_info_2'] = 'Questo appuntamento è aggiunto a Google Calendar';
$lang['appointment_google_client_meet_info'] = 'Connettiti tramite Google Meet';
$lang['appointment_connect_via_google_meet'] = 'Desidero connettermi tramite Google Meet';
$lang['appointment_meet_message'] = 'Ciao<br><br>Segui questo URL per connetterti con me tramite Google Meet: ';
$lang['appointment_meeting_request_sent'] = 'Il tuo messaggio di richiesta riunione è stato inviato con successo';
$lang['appointment_leave_a_comment'] = 'Vuoi lasciare un commento';
$lang['appointment_google_meet_connect_message'] = 'Invia email ai partecipanti e chiedi di connettersi tramite Google Meet';
$lang['appointment_google_meet_modal_custom_label'] = 'Invita personale e clienti a Google Meet via Email';
$lang['appointment_google_meet'] = 'Google Meet';
$lang['appointment_google_calendar'] = 'Google Calendar';

// Enhanced Google Meet Settings
$lang['appointment_google_meet_enhanced_settings'] = 'Impostazioni Google Meet avanzate';
$lang['appointly_auto_enable_google_meet'] = 'Abilita automaticamente Google Meet per tutti gli appuntamenti';
$lang['appointly_auto_enable_google_meet_help'] = 'Quando abilitato, tutti i nuovi appuntamenti includeranno automaticamente i link di Google Meet quando sincronizzati con Google Calendar';
$lang['appointly_google_meet_default_settings'] = 'Impostazioni predefinite di Google Meet';
$lang['appointly_google_meet_enable_recording'] = 'Abilita la registrazione per impostazione predefinita';
$lang['appointly_google_meet_enable_waiting_room'] = 'Abilita la sala d\'attesa per impostazione predefinita';
$lang['appointly_google_meet_reminder_minutes'] = 'Tempo di promemoria predefinito prima della riunione';
$lang['appointly_google_meet_reminder_help'] = 'Imposta il tempo di promemoria predefinito per gli appuntamenti di Google Meet';
$lang['appointly_disable_google_meeting_emails'] = 'Disabilita le notifiche email di Google Calendar';
$lang['appointly_disable_google_meeting_emails_help'] = 'Quando abilitato, Google non invierà notifiche email automatiche per gli eventi del calendario';
$lang['appointly_minutes'] = 'minuti';
$lang['appointly_hour'] = 'ora';
$lang['appointly_hours'] = 'ore';
$lang['appointly_day'] = 'giorno';

// Enhanced Google Meet Features
$lang['appointment_google_meet_join_before_start'] = 'Partecipa a Google Meet';
$lang['appointment_google_meet_copy_link'] = 'Copia link Google Meet';
$lang['appointment_google_meet_link_copied'] = 'Link Google Meet copiato negli appunti';
$lang['appointment_google_meet_test_connection'] = 'Test connessione Google Meet';
$lang['appointment_google_meet_connection_success'] = 'Test connessione Google Meet riuscito';
$lang['appointment_google_meet_connection_failed'] = 'Test connessione Google Meet fallito';
$lang['appointment_google_meet_quick_join'] = 'Partecipa rapidamente alla riunione';
$lang['appointment_google_meet_meeting_details'] = 'Dettagli riunione';
$lang['appointment_google_meet_dial_in'] = 'Informazioni di accesso telefonico';
$lang['appointment_google_meet_share_screen'] = 'Condivisione schermo disponibile';
$lang['appointment_google_meet_record_meeting'] = 'Registrazione disponibile';
$lang['appointment_google_meet_waiting_room_enabled'] = 'Sala d\'attesa abilitata';
$lang['appointment_google_meet_testing_connection'] = 'Test connessione Google Meet...';
$lang['appointment_google_meet_copy_failed'] = 'Copia fallita. Copia manualmente.';
$lang['appointment_google_meet_email_message'] = 'Messaggio email';
$lang['appointment_google_meet_send_invitation'] = 'Invia invito Google Meet';
$lang['appointment_google_meet_message_required'] = 'Inserisci un messaggio da inviare';
$lang['sending'] = 'Invio...';
$lang['appointment_email_send_failed'] = 'Invio email fallito. Controlla le tue impostazioni email.';
$lang['appointment_google_meet_send_to'] = 'Invia a';
$lang['appointment_google_meet_primary_recipient'] = 'Destinatario principale';
$lang['appointment_google_meet_also_notify_attendees'] = 'Notifica anche i partecipanti del personale';
$lang['appointment_no_email_provided'] = 'Nessun indirizzo email fornito';
$lang['appointment_google_meet_hd_video_audio'] = 'Video e audio HD';
$lang['appointment_google_meet_always_enabled'] = 'Sempre abilitato';
$lang['appointment_google_meet_recording_enabled'] = 'Registrazione abilitata';
$lang['appointment_google_meet_recording_disabled'] = 'Registrazione disabilitata';
$lang['appointment_google_meet_waiting_room_enabled_status'] = 'Stato sala d\'attesa abilitata';
$lang['appointment_google_meet_waiting_room_disabled'] = 'Sala d\'attesa disabilitata';
$lang['appointment_google_meet_quick_actions'] = 'Azioni rapide';
$lang['appointment_google_meet_join_meeting'] = 'Partecipa a Google Meet';
$lang['appointment_google_meet_send_invitation_btn'] = 'Invia invito';

// Google Maps
$lang['appointment_google_maps'] = 'Apri in Google Maps';
$lang['appointly_google_maps_not_shown'] = 'Google Maps non verrà mostrato.';
$lang['appointly_google_api_key_notset'] = 'La chiave API di Google non è impostata, imposta la chiave API di Google nelle impostazioni dell\'appuntamento';
$lang['appointly_message_will_hide'] = 'Questo messaggio verrà nascosto in 5 secondi';

// Outlook Calendar
$lang['appointment_login_to_outlook'] = 'Accedi a Outlook';
$lang['appointment_logout_from_outlook'] = 'Esci da Outlook';
$lang['appointment_open_outlook_calendar'] = 'Apri in Outlook Calendar';
$lang['appointments_outlook_revoke'] = 'Revoca la sessione corrente di Outlook Calendar e rimuovi tutte le autorizzazioni concesse al tuo account Outlook.';
$lang['appointment_redirect_url_logout'] = 'URI di reindirizzamento e autorizzazione Outlook';
$lang['appointment_outlook_api_label'] = 'API Outlook Calendar';
$lang['appointment_outlook_client_id'] = 'ID applicazione (client)';
$lang['appointment_outlook_calendar'] = 'Outlook Calendar';
$lang['appointment_outlook_calendar_info'] = 'Questo appuntamento è aggiunto a Outlook Calendar';
$lang['appointment_add_to_outlook'] = 'Aggiungi a Outlook Calendar';
$lang['appointment_outlook_not_added_yet'] = 'Non ancora aggiunto a Outlook Calendar';
$lang['appointment_is_added_to_outlook'] = 'Aggiunto a Outlook Calendar';
$lang['appointment_calendar_adding_to_outlook'] = 'Aggiunta a Outlook Calendar...';
$lang['appointment_added_to_outlook'] = 'Evento aggiunto a Outlook Calendar con successo';
$lang['appointment_added_to_outlook_but_not_saved'] = 'Evento aggiunto a Outlook ma non salvato nel database';
$lang['appointment_outlook_event_saved'] = 'Dettagli evento Outlook salvati';
$lang['appointment_outlook_event_save_failed'] = 'Impossibile salvare i dettagli dell\'evento Outlook';
$lang['appointment_outlook_error'] = 'Errore durante l\'aggiunta a Outlook';
$lang['appointment_outlook_auth_error'] = 'Errore di autenticazione Outlook';
$lang['appointment_invalid_date'] = 'Data appuntamento non valida';
$lang['appointment_sign_in_to_outlook'] = 'Accedi a Outlook';
$lang['appointment_sign_out_from_outlook'] = 'Esci da Outlook';
$lang['appointments_outlook_view_in_calendar'] = 'Visualizza in Outlook Calendar';
$lang['appointment_outlook_calendar_title'] = 'Outlook Calendar';
$lang['appointment_outlook_sync_status'] = 'Stato sincronizzazione Outlook';
$lang['appointment_outlook_last_synced'] = 'Ultima sincronizzazione: %s';
$lang['appointment_outlook_sync_error'] = 'Ultima sincronizzazione fallita: %s';
$lang['appointment_outlook_event_deleted'] = 'Evento Outlook eliminato con successo';
$lang['appointment_outlook_event_delete_failed'] = 'Impossibile eliminare l\'evento Outlook';
$lang['appointments_table_calendar'] = 'Aggiunto ai calendari';
$lang['appointment_not_added_to_calendars_yet'] = 'Non ancora aggiunto a nessun calendario.';
$lang['permission_approve'] = 'Approva';
$lang['permission_view_reports'] = 'Visualizza rapporti';
$lang['appointly_missing_required_fields'] = 'Campi obbligatori mancanti';
$lang['appointly_service_not_found'] = 'Servizio non trovato';
$lang['appointly_error_getting_time_slots'] = 'Errore durante il recupero degli slot orari disponibili. Riprova.';
$lang['appointly_invalid_working_hours'] = 'Configurazione orari di lavoro non valida';
$lang['appointly_service_price_invalid'] = 'Il prezzo del servizio deve essere un numero valido (0 o maggiore)';
$lang['appointly_service_duration_invalid'] = 'La durata del servizio deve essere un numero positivo';
$lang['appointly_service_providers_required'] = 'Almeno un fornitore di servizi deve essere assegnato';
$lang['appointly_available_time_slots'] = 'Slot orari disponibili';
$lang['appointment_loading'] = 'Caricamento...';
$lang['appointly_error_loading_providers'] = 'Errore durante il caricamento dei fornitori';
$lang['appointly_no_data_available'] = 'Nessun dato disponibile';
$lang['appointly_please_try_again'] = 'Riprova';
$lang['appointly_installation_complete'] = 'Installazione completata con successo';
$lang['appointly_database_updated'] = 'Database aggiornato con successo';
$lang['appointly_menu_reset'] = 'Menu ripristinato con successo';
$lang['appointly_default_service_created'] = 'Servizio predefinito creato con successo';
$lang['appointly_working_hours_configured'] = 'Orari di lavoro configurati con successo';
$lang['appointment_select_service_provider_first'] = 'Seleziona prima servizio e fornitore';
$lang['appointment_are_you_sure_to_cancel'] = 'Sei sicuro di voler annullare questo appuntamento?';
$lang['appointment_are_you_sure_to_mark_as_ongoing'] = 'Sei sicuro di voler contrassegnare questo appuntamento come in corso?';
$lang['appointment_error_occurred'] = 'Si è verificato un errore. Riprova.';
$lang['appointment_closed'] = 'La prenotazione degli appuntamenti è attualmente chiusa';
$lang['appointment_time_required'] = 'Seleziona uno slot orario';
$lang['appointment_no_providers'] = 'Nessun fornitore disponibile per questo servizio';
$lang['appointment_provider_not_available'] = 'Fornitore non disponibile in questo giorno';
$lang['appointment_minutes'] = 'minuti';
$lang['appointment_unavailable'] = 'Non disponibile';
$lang['is_required'] = 'è obbligatorio';
$lang['appointment_schedule_info'] = 'Informazioni appuntamento';
$lang['appointment_form_info'] = 'Informazioni appuntamento';
$lang['appointment_marked_as_approved'] = 'Appuntamento contrassegnato come approvato';
$lang['appointment_cancellation_approval_failed'] = 'Impossibile approvare l\'annullamento dell\'appuntamento';
$lang['appointment_send_an_sms'] = 'Invia SMS';
$lang['appointment_call_number'] = 'Chiama';
$lang['appointment_actions'] = 'Azioni';
$lang['appointment_staff_cannot_provide_feedback'] = 'I membri del personale non possono fornire feedback per gli appuntamenti';
$lang['appointment_thank_you_for_feedback'] = 'Grazie per il tuo feedback!';
$lang['appointment_feedback_comment_required'] = 'Il commento di feedback è obbligatorio';

// Reschedule functionality client side
$lang['appointment_reschedule'] = 'Riprogramma';
$lang['appointment_reschedule_reason'] = 'Motivo della riprogrammazione';
$lang['appointment_reschedule_reason_placeholder'] = 'Spiega perché devi riprogrammare questo appuntamento...';
$lang['appointment_reschedule_reason_required'] = 'Il motivo della riprogrammazione è obbligatorio';
$lang['appointment_request_reschedule'] = 'Richiedi riprogrammazione';
$lang['appointment_new_date'] = 'Nuova data';
$lang['appointment_new_time'] = 'Nuova ora';
$lang['appointment_reschedule_request_submitted'] = 'La tua richiesta di riprogrammazione è stata inviata e sarà esaminata dal nostro personale.';
$lang['appointment_cannot_be_rescheduled'] = 'Questo appuntamento non può essere riprogrammato a causa del suo stato attuale.';

$lang['appointment_processing'] = 'Elaborazione...';
$lang['appointment_select_date_first'] = 'Seleziona prima una data';
$lang['appointment_please_select_date_time'] = 'Seleziona una data e un\'ora';
$lang['appointment_current_details'] = 'Dettagli attuali';
$lang['appointment_loading_available_times'] = 'Caricamento orari disponibili...';
$lang['appointment_no_available_slots'] = 'Nessun slot disponibile';
$lang['appointment_no_available_times'] = 'Nessun orario disponibile';

$lang['appointment_reschedule_requested'] = 'Riprogrammazione richiesta';
$lang['appointment_reschedule_pending_review'] = 'La tua richiesta di riprogrammazione è in attesa di revisione da parte del nostro personale.';
$lang['appointment_reschedule_pending_notice'] = 'Richieste di riprogrammazione in sospeso';
$lang['appointment_requested_date'] = 'Data richiesta';
$lang['appointment_requested_time'] = 'Ora richiesta';
$lang['appointment_current_date'] = 'Data attuale';
$lang['appointment_approve_reschedule'] = 'Approva riprogrammazione';
$lang['appointment_reject_reschedule'] = 'Rifiuta riprogrammazione';
$lang['appointment_pending_reschedules'] = 'Riprogrammazioni in sospeso';
$lang['appointment_reschedule_approved'] = 'Riprogrammazione approvata';
$lang['appointment_reschedule_rejected'] = 'Riprogrammazione rifiutata';
$lang['appointment_cancellation_requested'] = 'Annullamento richiesto';
$lang['appointment_cancellation_pending_review'] = 'La tua richiesta di annullamento è in attesa di revisione da parte del nostro personale.';
$lang['appointment_cancellation_reason'] = 'Motivo dell\'annullamento';
$lang['appointment_cancellation_notes'] = 'Note di annullamento';
$lang['appointment_cancellation_notes_placeholder'] = 'Spiega perché vuoi annullare questo appuntamento...';
$lang['appointment_cancellation_notes_required'] = 'Le note di annullamento sono obbligatorie';
$lang['appointment_cancellation_request_submitted'] = 'La tua richiesta di annullamento è stata inviata e sarà esaminata dal nostro personale.';
$lang['appointment_confirm_approve_reschedule'] = 'Sei sicuro di voler approvare questa richiesta di riprogrammazione? Questo aggiornerà l\'appuntamento alla nuova data e ora.';
$lang['appointment_reschedule_denial_reason'] = 'Fornisci un motivo per negare questa richiesta di riprogrammazione (verrà inviato al cliente):';
$lang['appointment_reschedule_approved_successfully'] = 'Richiesta di riprogrammazione approvata e appuntamento aggiornato.';
$lang['appointment_reschedule_denied_successfully'] = 'Richiesta di riprogrammazione negata e cliente avvisato.';
$lang['appointment_reschedule_approval_failed'] = 'Impossibile approvare la richiesta di riprogrammazione. Riprova.';
$lang['appointment_reschedule_denial_failed'] = 'Impossibile negare la richiesta di riprogrammazione. Riprova.';
$lang['appointment_view_details'] = 'Visualizza dettagli';
$lang['appointment_client_information'] = 'Informazioni cliente';
$lang['appointment_provider_information'] = 'Informazioni fornitore';
$lang['appointment_reschedule_action_required'] = 'Azione richiesta';
$lang['appointment_reschedule_instructions'] = 'Quando i clienti richiedono riprogrammazioni, queste appaiono qui per la tua approvazione. L\'approvazione aggiornerà automaticamente l\'appuntamento e avviserà il cliente via email.';
$lang['appointment_no_pending_reschedules'] = 'Nessuna richiesta di riprogrammazione in sospeso trovata.';
$lang['appointment_reschedule_request_details'] = 'Dettagli richiesta di riprogrammazione';
$lang['appointment_reschedule_date_required'] = 'Nuova data obbligatoria';
$lang['appointment_reschedule_time_required'] = 'Nuova ora obbligatoria';
$lang['appointment_reschedule_future_datetime'] = 'Seleziona una data e un\'ora future';
$lang['appointment_reschedule_request_subject'] = 'Richiesta di riprogrammazione appuntamento';
$lang['appointment_reschedule_approved_subject'] = 'Riprogrammazione appuntamento approvata';
$lang['appointment_reschedule_denied_subject'] = 'Riprogrammazione appuntamento negata';
$lang['appointment_book_again'] = 'Prenota di nuovo';
$lang['appointment_cancelled_title'] = 'Questo appuntamento è stato annullato';
$lang['appointment_book_new_appointment'] = 'Vuoi prenotare un nuovo appuntamento?';
$lang['appointment_book_new'] = 'Prenota nuovo appuntamento';
$lang['appointment_deny_reschedule'] = 'Nega riprogrammazione';
$lang['appointment_reschedule_denial_reason_prompt'] = 'Fornisci un motivo per negare questa richiesta di riprogrammazione:';
$lang['appointment_reschedule_denial_reason_required'] = 'Un motivo per il rifiuto è obbligatorio';
$lang['appointment_confirm_approve_cancellation'] = 'Sei sicuro di voler approvare questa richiesta di annullamento? Questo annullerà l\'appuntamento in modo permanente.';
$lang['appointment_cancelled_book_again_message'] = 'Questo appuntamento è stato annullato. Vuoi prenotare un nuovo appuntamento?';
$lang['appointment_no_show_book_again_message'] = 'Questo appuntamento è stato un no-show. Vuoi prenotare un nuovo appuntamento?';

// Calendar Integration Removal
$lang['appointment_remove_google_integration'] = 'Rimuovi integrazione Google Calendar';
$lang['appointment_remove_outlook_integration'] = 'Rimuovi integrazione Outlook Calendar';
$lang['appointment_confirm_remove_google_integration'] = 'Sei sicuro di voler rimuovere l\'integrazione di Google Calendar da questo appuntamento? Questo eliminerà anche l\'evento da Google Calendar se possibile.';
$lang['appointment_confirm_remove_outlook_integration'] = 'Sei sicuro di voler rimuovere l\'integrazione di Outlook Calendar da questo appuntamento? Questo eliminerà anche l\'evento da Outlook Calendar se possibile.';
$lang['appointment_google_integration_removed'] = 'Integrazione Google Calendar rimossa dall\'appuntamento';
$lang['appointment_google_integration_removed_and_deleted'] = 'Integrazione Google Calendar rimossa ed evento eliminato da Google Calendar';
$lang['appointment_google_removal_failed'] = 'Impossibile rimuovere l\'integrazione di Google Calendar';
$lang['appointment_outlook_integration_removed'] = 'Integrazione Outlook Calendar rimossa dall\'appuntamento';
$lang['appointment_outlook_integration_removed_and_deleted'] = 'Integrazione Outlook Calendar rimossa ed evento eliminato da Outlook Calendar';
$lang['appointment_outlook_removal_failed'] = 'Impossibile rimuovere l\'integrazione di Outlook Calendar';
$lang['appointment_missing_required_fields'] = 'Campi obbligatori mancanti';
$lang['appointment_not_found'] = 'Appuntamento non trovato';
$lang['appointment_outlook_not_authenticated_warning'] = 'Attenzione: Non sei attualmente autenticato con Outlook. L\'integrazione verrà rimossa solo localmente, ma l\'evento rimarrà nel tuo calendario Outlook.';
$lang['appointment_outlook_not_available_warning'] = 'Attenzione: L\'integrazione di Outlook non è disponibile. L\'integrazione verrà rimossa solo localmente.';
$lang['appointment_outlook_integration_removed_local_only'] = 'Integrazione calendario Outlook rimossa localmente. Nota: L\'evento potrebbe esistere ancora nel tuo calendario Outlook.';

// Dashboard Widgets
$lang['appointly_upcoming_appointments'] = 'Prossimi appuntamenti';
$lang['appointly_no_upcoming_appointments'] = 'Nessun prossimo appuntamento';
$lang['appointly_no_appointments_in_range'] = 'Nessun appuntamento programmato in %s';
$lang['appointly_next_7_days'] = 'Prossimi 7 giorni';
$lang['appointly_next_14_days'] = 'Prossimi 14 giorni';
$lang['appointly_next_30_days'] = 'Prossimi 30 giorni';
$lang['appointly_next_4_weeks'] = 'Prossime 4 settimane';
$lang['appointly_view_all_appointments'] = 'Visualizza tutti gli appuntamenti';
$lang['appointly_dashboard_widgets_settings'] = 'Impostazioni widget dashboard';
$lang['appointly_today_widget_enabled'] = 'Mostra widget appuntamenti di oggi sul dashboard';
$lang['appointly_upcoming_widget_enabled'] = 'Mostra widget prossimi appuntamenti sul dashboard';
$lang['appointly_upcoming_widget_range'] = 'Intervallo di date widget prossimi appuntamenti';
$lang['appointly_today'] = 'Oggi';
$lang['appointly_tomorrow'] = 'Domani';
$lang['days'] = 'giorni';

$lang['appointly_invoice_default_vat'] = 'IVA/Percentuale fiscale predefinita';
$lang['appointly_invoice_vat_help'] = 'Percentuale fiscale predefinita da applicare alle fatture generate automaticamente (imposta a 0 per nessuna imposta)';
$lang['appointly_invoice_tax_type_help'] = 'Scegli come applicare le tasse alle fatture degli appuntamenti';
$lang['appointly_invoice_tax_type_label'] = 'Metodo di applicazione delle tasse';
$lang['appointly_tax_type_none'] = 'Nessuna tassa';
$lang['appointly_tax_type_custom'] = 'Percentuale personalizzata';
$lang['appointly_tax_type_system'] = 'Usa aliquote fiscali CRM';
$lang['appointly_default_vat_label'] = 'Percentuale fiscale personalizzata';
$lang['appointly_default_vat_help'] = 'Percentuale fiscale personalizzata da applicare alle fatture (imposta a 0 per nessuna imposta)';
$lang['appointly_system_tax_label'] = 'Seleziona aliquota fiscale';
$lang['appointly_system_tax_help'] = 'Scegli tra le aliquote fiscali configurate nel CRM';

// Enhanced Invoice Settings - Tab Names
$lang['appointly_invoice_settings'] = 'Fattura & Tasse';
$lang['appointly_tax_settings'] = 'Configurazione tasse';
$lang['appointly_tax_settings_help'] = 'Configura come le tasse vengono applicate alle fatture create dagli appuntamenti. Puoi usare il sistema fiscale del CRM o impostare una percentuale personalizzata.';
$lang['appointly_tax_type_label'] = 'Metodo di applicazione delle tasse';
$lang['appointly_tax_type_help'] = 'Scegli come applicare le tasse alle fatture degli appuntamenti';

// Form Field Labels
$lang['appointly_enable'] = 'Abilita';
$lang['appointly_disable'] = 'Disabilita';
$lang['appointly_yes'] = 'Sì';
$lang['appointly_no'] = 'No';

// Client Dashboard Language Strings
$lang['appointment_client_dashboard_description'] = 'Gestisci i tuoi appuntamenti, visualizza la cronologia e prenota nuovi appuntamenti.';
$lang['appointment_book_new'] = 'Prenota nuovo appuntamento';
$lang['appointment_all'] = 'Tutti';
$lang['appointment_total_appointments'] = 'Totale appuntamenti';
$lang['appointment_completed_appointments'] = 'Completati';
$lang['appointment_upcoming_appointments'] = 'Prossimi';
$lang['appointment_cancelled_appointments'] = 'Annullati';
$lang['appointment_no_appointments_found'] = 'Nessun appuntamento trovato';
$lang['appointment_no_appointments_match_filter'] = 'Nessun appuntamento corrisponde al filtro attuale';
$lang['appointment_date_and_time'] = 'Data & Ora';
$lang['appointment_details'] = 'Dettagli appuntamento';
$lang['appointment_book_again'] = 'Prenota di nuovo';
$lang['view_invoice'] = 'Visualizza fattura';

// Cancel and Reschedule
$lang['appointment_cancel_reason'] = 'Motivo dell\'annullamento';
$lang['appointment_cancel_reason_placeholder'] = 'Fornisci un motivo per annullare questo appuntamento...';
$lang['appointment_reschedule_reason'] = 'Motivo della riprogrammazione';
$lang['appointment_reschedule_reason_placeholder'] = 'Spiega perché devi riprogrammare questo appuntamento...';
$lang['appointment_new_date'] = 'Nuova data';
$lang['appointment_new_time'] = 'Nuova ora';
$lang['appointment_request_reschedule'] = 'Richiedi riprogrammazione';
$lang['appointment_cancel_request_sent'] = 'Richiesta di annullamento inviata al personale per approvazione.';
$lang['appointment_cancel_request_failed'] = 'Impossibile inviare la richiesta di annullamento. Riprova.';
$lang['appointment_reschedule_request_sent'] = 'Richiesta di riprogrammazione inviata al personale per approvazione.';

// ICS Calendar Export
$lang['appointment_add_to_calendar'] = 'Aggiungi al Calendario';
$lang['appointment_download_ics'] = 'Scarica file .ics';
$lang['appointment_ics_description'] = 'Scarica questo appuntamento come file calendario (.ics) per importarlo in qualsiasi applicazione calendario (Apple Calendar, Google Calendar, Outlook, ecc.)';
$lang['appointment_calendar_file'] = 'File Calendario';
$lang['appointment_ics_tooltip'] = 'Scarica un file calendario standard che funziona con tutte le applicazioni calendario';
$lang['appointment_ics_file_downloaded'] = 'File calendario scaricato con successo';
$lang['appointment_ics_download_error'] = 'Errore durante il download del file calendario';
$lang['appointment_reschedule_request_failed'] = 'Impossibile inviare la richiesta di riprogrammazione. Riprova.';
$lang['appointment_client_dashboard'] = 'Dashboard cliente';
$lang['appointment_available_times'] = 'Orari disponibili';
$lang['appointment_error_loading_times'] = 'Errore durante il caricamento degli orari';
$lang['appointment_cancel_request_submitted'] = 'Richiesta di annullamento inviata con successo';
$lang['appointment_date_time_required'] = 'Data e ora sono obbligatorie';
$lang['appointment_reschedule_pending'] = 'Richiesta di riprogrammazione in attesa di approvazione';
$lang['appointly_invoice_payment_mode_changed'] = 'Modalità di pagamento fattura modificata, salva le impostazioni per applicare le modifiche';
$lang['payment_received_for_appointment'] = 'Pagamento ricevuto per:';
$lang['appointly_create_invoice_when_completed'] = 'Crea fattura quando l\'appuntamento è completato?';
$lang['invoice_created_for_appointment'] = 'Fattura #%s creata per questo appuntamento.';
$lang['appointment_email_sent_success'] = 'Email inviata con successo';
$lang['appointment_email_sent_failed'] = 'Invio email fallito';
$lang['customer_permission_appointments'] = 'Appuntamenti';
$lang['appointment_contact_relationship'] = 'Contatto & Informazioni Relazione';
$lang['appointment_download_ics_tooltip'] = 'Scarica il file del calendario (.ics) per aggiungere questo appuntamento alla tua applicazione calendario';
$lang['appointment_security_verification'] = 'Verifica sicurezza';
$lang['appointment_view_on_map'] = 'Visualizza sulla mappa';
$lang['appointment_no_notes_available'] = 'Nessun note disponibile per questo appuntamento';
$lang['appointment_session_overview'] = 'Panoramica sessione';
$lang['appointly_show_staff_email_booking_form'] = 'Mostra indirizzi email del personale nel modulo di prenotazione';
$lang['appointly_no_available_time_slots'] = 'Nessun slot orario disponibile';
$lang['appointment_status_changed_successfully'] = 'Stato appuntamento modificato con successo';
$lang['appointment_status_change_failed'] = 'Impossibile modificare lo stato dell\'appuntamento';
