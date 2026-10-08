<?php


// ----------------------
// ADMIN SETTINGS & DASHBOARD
// ----------------------
// Module Information
$lang['appointly_module_name'] = 'Appointly';
$lang['appointly_current_version'] = 'Versi modul saat ini: ';
$lang['appointly_settings_label_pointer'] = '<strong>Pengaturan -> Janji Temu</strong>';
$lang['appointly_integrations'] = 'Integrasi';
$lang['general_settings'] = 'Pengaturan Umum';
$lang['new_appointment_notification'] = 'Notifikasi Janji Temu Baru';

// Admin Settings
$lang['appointments_approve_automatically_label'] = 'Setujui otomatis janji temu yang dikirimkan dari formulir web eksternal';
$lang['appointments_reminder_time_value'] = 'Tambahkan nilai untuk menerima notifikasi email sebelum janji temu Anda (default 30 menit sebelumnya)';
$lang['appointly_default_table_filter_label'] = 'Filter tabel janji temu default';
$lang['appointment_your_settings'] = 'Pengaturan Janji Temu Anda';
$lang['appointments_buffer_hours_label'] = 'Nonaktifkan pemilih tanggal yang sudah lewat';

// Dashboard & Analytics
$lang['appointly_show_summary_in_appointments_dashboard'] = 'Tampilkan ringkasan janji temu di dasbor janji temu';
$lang['appointments_summary'] = 'Ringkasan janji temu';
$lang['appointment_appointments_dashboard'] = 'Dasbor';
$lang['appointment_want_to_go_back'] = 'Janji Temu';
$lang['appointly_analytics_dashboard'] = 'Dasbor Analitik Appointly';
$lang['appointment_history_label'] = 'Janji Temu Sebelumnya';
$lang['appointment_history_label_menu_label'] = 'Riwayat Janji Temu';
$lang['appointment_services_menu_label'] = 'Layanan';
$lang['appointment_analytics_and_reports_menu_label'] = 'Analitik & Laporan';

// Invoice Conversion
$lang['appointment_convert_to_invoice'] = 'Konversi ke Faktur';
$lang['appointment_converted_to_invoice'] = 'Janji temu berhasil dikonversi ke faktur';
$lang['appointment_convert_to_invoice_success'] = 'Janji temu berhasil dikonversi ke faktur #%s';
$lang['appointment_convert_to_invoice_failed'] = 'Gagal mengonversi janji temu ke faktur';
$lang['appointment_are_you_sure_convert_to_invoice'] = 'Apakah Anda yakin ingin mengonversi janji temu ini ke faktur?';
$lang['appointment_view_invoice'] = 'Lihat Faktur #%s';
$lang['appointment_convert_to_invoice_tooltip'] = 'Pembuatan faktur hanya tersedia untuk janji temu dengan pelanggan sebenarnya. Untuk prospek atau kontak eksternal, Anda harus mengonversinya menjadi pelanggan terlebih dahulu.';
$lang['appointment_external_contact_converted_to_client'] = 'Kontak eksternal berhasil dikonversi menjadi klien';
$lang['appointment_external_contact_convert_to_client_error'] = 'Kesalahan mengonversi kontak eksternal menjadi klien';
$lang['appointment_convert_to_customer_first'] = 'Konversi ke Pelanggan Terlebih Dahulu';
$lang['appointment_convert_lead_to_customer'] = 'Konversi Prospek ke Pelanggan';
$lang['appointment_convert_external_to_customer'] = 'Konversi ke Pelanggan';
$lang['appointment_convert_only'] = 'Konversi Saja';
$lang['appointment_convert_and_view'] = 'Konversi dan Lihat';
$lang['appointment_convert_to_invoice_only_contacts_allowed'] = 'Hanya janji temu internal dengan kontak yang dapat dikonversi ke faktur';

// Lead Conversion
$lang['appointments_convert_to_lead'] = 'Konversi Janji Temu ke Prospek';
$lang['appointments_convert_to_lead_label'] = 'Konversi ke prospek';
$lang['appointments_create_task_tooltip'] = 'Konversi ke tugas';
$lang['appointments_convert_to_lead_tooltip'] = 'Konversi ke prospek';
$lang['appointments_select_option'] = 'Pilih opsi';
$lang['appointments_contact_name_task'] = 'Janji Temu: Nama kontak';

// Reports Dashboard
$lang['appointly_reports_dashboard'] = 'Dasbor Laporan Appointly';
$lang['appointly_date_range'] = 'Rentang Tanggal';
$lang['appointly_today'] = 'Hari Ini';
$lang['appointly_this_week'] = 'Minggu Ini';
$lang['appointly_this_month'] = 'Bulan Ini';
$lang['appointly_this_year'] = 'Tahun Ini';
$lang['appointly_last_month'] = 'Bulan Lalu';
$lang['appointly_last_year'] = 'Tahun Lalu';
$lang['appointly_last_30_days'] = '30 Hari Terakhir';
$lang['appointly_custom_range'] = 'Rentang Kustom';
$lang['appointly_period_from'] = 'Periode Dari';
$lang['appointly_period_to'] = 'Periode Hingga';
$lang['appointly_date_range_required'] = 'Silakan pilih rentang tanggal yang valid';
$lang['appointly_apply'] = 'Terapkan';
$lang['appointly_total_appointments'] = 'Total Janji Temu';
$lang['appointly_completed_appointments'] = 'Janji Temu Selesai';
$lang['appointly_cancelled_appointments'] = 'Janji Temu Dibatalkan';
$lang['appointly_monthly_stats'] = 'Statistik Bulanan';
$lang['appointly_popular_services'] = 'Layanan Populer';
$lang['appointly_staff_performance'] = 'Kinerja Staf';
$lang['appointly_staff_member'] = 'Anggota Staf';
$lang['appointly_appointment_total_appointments'] = 'Total Janji Temu';
$lang['appointly_appointment_completed_appointments'] = 'Janji Temu Selesai';
$lang['appointly_appointment_cancelled_appointments'] = 'Janji Temu Dibatalkan';
$lang['appointly_appointment_completion_rate'] = 'Tingkat Penyelesaian';
$lang['appointly_filter'] = 'Filter';
$lang['appointly_no_staff_performance_data'] = 'Tidak ada data kinerja staf yang tersedia untuk periode yang dipilih';
$lang['appointly_report'] = 'Laporan';
$lang['appointly_reports_updated_for_period'] = 'Laporan diperbarui untuk periode: %s hingga %s';
$lang['appointly_no_data_for_period'] = 'Tidak ada data yang tersedia untuk periode yang dipilih';
$lang['appointly_invalid_date_format'] = 'Format tanggal tidak valid. Silakan gunakan pemilih tanggal untuk memilih tanggal';
$lang['appointly_loading_data'] = 'Memuat data...';
$lang['appointly_error_refreshing_stats'] = 'Kesalahan menyegarkan statistik. Silakan coba lagi.';
$lang['appointly_no_data_found'] = 'Tidak ada data ditemukan';

// Analytics Dashboard
$lang['total_appointments'] = 'Total Janji Temu';
$lang['completed_appointments'] = 'Janji Temu Selesai';
$lang['cancelled_appointments'] = 'Janji Temu Dibatalkan';
$lang['monthly_statistics'] = 'Statistik Bulanan';
$lang['popular_services'] = 'Layanan Populer';
$lang['staff_performance'] = 'Kinerja Staf';
$lang['staff_member'] = 'Anggota Staf';
$lang['completion_rate'] = 'Tingkat Penyelesaian';
$lang['from_date'] = 'Dari Tanggal';
$lang['to_date'] = 'Sampai Tanggal';
$lang['appointment_statistics_total'] = 'Total Janji Temu';
$lang['appointment_statistics_completed'] = 'Janji Temu Selesai';
$lang['appointment_statistics_cancelled'] = 'Janji Temu Dibatalkan';

$lang['appointment_services_to_show_in_booking_form'] = 'Pilih layanan yang akan ditampilkan di formulir pemesanan publik';
$lang['appointment_services_select_all_to_show_all'] = 'Biarkan kosong untuk menampilkan semua layanan aktif';
$lang['appointment_select_attendees'] = 'Peserta';
$lang['appointment_select_attendees_help'] = 'Pilih anggota staf tambahan yang akan menghadiri janji temu ini';
$lang['appointment_related'] = 'Terkait';

// Service Availability Settings
$lang['services_availability_updated_successfully'] = 'Ketersediaan layanan berhasil diperbarui';
$lang['services_availability_update_failed'] = 'Gagal memperbarui ketersediaan layanan';
$lang['appointment_error_loading_providers'] = 'Kesalahan memuat penyedia';
$lang['appointment_select_service_warning'] = 'Silakan pilih layanan';
$lang['appointment_select_provider_warning'] = 'Silakan pilih penyedia';
$lang['appointment_select_date_time_warning'] = 'Silakan pilih tanggal dan waktu';
$lang['appointment_loading_providers'] = 'Memuat penyedia...';
$lang['appointment_select_date'] = 'Pilih Tanggal';
$lang['appointment_your_details'] = 'Detail Anda';
$lang['appointment_continue'] = 'Berikutnya';
$lang['appointment_back'] = 'Kembali';
$lang['appointment_summary'] = 'Ringkasan Janji Temu';
$lang['appointment_view_details'] = 'Lihat Detail';
$lang['appointment_select'] = 'Pilih';
$lang['error_loading_data'] = 'Kesalahan memuat data';
$lang['appointment_booking_failed'] = 'Gagal memesan janji temu. Silakan coba lagi.';
$lang['appointment_successfully_scheduled'] = 'Janji Temu Berhasil Dijadwalkan';
$lang['appointment_successfully_scheduled_message'] = 'Terima kasih! Janji temu Anda telah berhasil dijadwalkan.';
$lang['appointment_successfully_scheduled_button'] = 'Hebat!';
$lang['appointment_schedule_another'] = 'Jadwalkan Lainnya';
$lang['appointment_booking_confirmed'] = 'Terima kasih! Janji temu Anda telah berhasil dijadwalkan.';
$lang['appointment_pending_approval_message'] = 'Janji temu Anda menunggu persetujuan staf. Anda akan diberitahu setelah dikonfirmasi.';
$lang['appointment_whats_next'] = 'Apa Selanjutnya?';
$lang['appointment_staff_review'] = 'Staf kami sedang meninjau permintaan Anda. Harap tunggu konfirmasi.';
$lang['appointment_email_confirmation'] = 'Anda akan menerima konfirmasi email segera dengan semua detail janji temu.';
$lang['appointment_prepare'] = 'Harap siapkan dokumen atau detail yang diperlukan sebelum janji temu Anda.';
$lang['appointment_files'] = 'File';
$lang['appointment_externally_booked_info'] = 'Janji temu ini dipesan melalui formulir pemesanan eksternal';
$lang['appointment_booked_from_external_booking_form'] = 'Pemesanan Dari';
$lang['appointment_subject_required'] = 'Subjek diperlukan untuk janji temu';
$lang['appointment_attendees_required'] = 'Setidaknya satu anggota staf harus menghadiri janji temu';
$lang['appointment_name_required'] = 'Nama diperlukan untuk janji temu eksternal';
$lang['appointment_email_required'] = 'Email diperlukan untuk janji temu eksternal';
$lang['appointment_email_invalid'] = 'Harap berikan alamat email yang valid';
$lang['appointment_contact_required'] = 'Silakan pilih kontak untuk janji temu internal';
$lang['appointment_invalid_type'] = 'Jenis janji temu yang dipilih tidak valid';
$lang['appointment_type_required'] = 'Silakan pilih jenis janji temu';
$lang['appointment_invalid_data'] = 'Data janji temu yang diberikan tidak valid';
$lang['appointment_could_not_be_created'] = 'Janji temu tidak dapat dibuat. Silakan coba lagi.';
$lang['appointment_unavailable_slots'] = 'Slot merah tidak tersedia karena janji temu yang ada';
$lang['appointment_book_now_description'] = 'Pesan janji temu Anda sekarang dan dapatkan layanan terbaik dari tim kami';
$lang['appointment_book_now_button_description'] = 'Pesan janji temu Anda sekarang dan dapatkan layanan terbaik dari tim kami';
$lang['appointment_feature_coming_soon'] = 'Fitur ini akan tersedia di pembaruan mendatang. Tetap ikuti!';
$lang['appointment_description_updated'] = 'Deskripsi janji temu berhasil diperbarui';
$lang['appointment_notes_updated'] = 'Catatan janji temu berhasil diperbarui';
$lang['appointment_notes_update_failed'] = 'Gagal memperbarui catatan janji temu';
$lang['appointment_update_failed'] = 'Gagal memperbarui janji temu';
$lang['appointment_description_edit_info'] = 'Klik untuk mengedit deskripsi. Perubahan akan disimpan secara otomatis saat Anda selesai mengedit.';
$lang['appointment_viewing_notes'] = 'Melihat catatan untuk janji temu';
$lang['appointment_edit_history_notes'] = 'Edit Catatan';
$lang['appointment_email_missing'] = 'Alamat email hilang atau tidak valid untuk janji temu ini';
$lang['appointment_no_name_provided'] = 'Tidak ada nama yang diberikan';
$lang['appointment_no_phone_provided'] = 'Tidak ada telepon yang diberikan';
$lang['appointment_open_link'] = 'Buka Tautan';
$lang['appointment_menu_form_link'] = 'Formulir Pemesanan';
$lang['external_form_heading'] = 'Judul Formulir Pemesanan';
$lang['external_form_description'] = 'Deskripsi Formulir Pemesanan';
$lang['appointment_date_location'] = 'Detail Sesi';
$lang['appointment_schedule_description'] = 'Lengkapi formulir di bawah ini untuk mengatur sesi Anda dengan tim kami';
$lang['appointment_preferred_date_time'] = 'Tanggal & Waktu Pilihan';
$lang['appointment_select_date_time'] = 'Pilih...';
$lang['appointment_terms_description'] = 'Dengan melanjutkan, Anda mengonfirmasi penerimaan Anda terhadap';
$lang['appointment_terms_link'] = 'Syarat dan Ketentuan';
$lang['appointment_accept_terms'] = 'Saya mengakui dan menyetujui Ketentuan Layanan*';
$lang['appointly_recaptcha_enabled'] = 'Aktifkan Recaptcha';
$lang['appointment_no_location_provided'] = 'Tidak ada lokasi yang diberikan untuk merender Google Maps';
$lang['appointment_imported_from_calendar'] = 'Diimpor dari %s';
$lang['appointment_imported_cant_edit_notice'] = 'Janji temu ini diimpor dari kalender eksternal dan tidak dapat diedit.';
$lang['appointment_are_you_sure'] = 'Apakah Anda yakin ingin menghapus janji temu ini?';
$lang['would_you_like_to_create_new_appointment_for_lead'] = 'Apakah Anda ingin membuat janji temu baru untuk prospek ini?';
$lang['would_you_like_to_create_new_appointment_for_client'] = 'Apakah Anda ingin membuat janji temu baru untuk klien ini?';
$lang['would_you_like_to_create_new_appointment'] = 'Apakah Anda ingin membuat janji temu baru?';
$lang['no_appointments_found'] = 'Tidak ada janji temu ditemukan';

// ----------------------
// EXTERNAL BOOKING FORM
// ----------------------
$lang['appointment_available_days'] = 'Tersedia';
$lang['appointment_busy_days'] = 'Sibuk (ada janji temu)';
$lang['appointment_provider_unavailable'] = 'Penyedia tidak tersedia';
$lang['appointment_blocked_days'] = 'Hari Libur Perusahaan/Tanggal Diblokir';
$lang['appointment_date_required'] = 'Silakan pilih tanggal terlebih dahulu';
$lang['appointment_select_time'] = 'Pilih waktu';
$lang['appointment_book_now'] = 'Pesan Sekarang';
$lang['appointment_submitting'] = 'Memesan Janji Temu...';

// New appointment filter options
$lang['appointment_today'] = 'Janji Temu Hari Ini';
$lang['appointment_tomorrow'] = 'Janji Temu Besok';
$lang['appointment_this_week'] = 'Janji Temu Minggu Ini';
$lang['appointment_next_week'] = 'Janji Temu Minggu Depan';
$lang['appointment_this_month'] = 'Janji Temu Bulan Ini';
$lang['appointment_my_appointments'] = 'Janji Temu Saya';
$lang['appointment_assigned_to_me'] = 'Ditugaskan kepada Saya';


// ----------------------
// GENERAL/COMMON TERMS
// ----------------------
$lang['appointment_yes'] = 'Ya';
$lang['appointment_no'] = 'Tidak';
$lang['appointment_appointments'] = 'Janji Temu';
$lang['appointment_label'] = 'Janji Temu';
$lang['wait_text'] = 'Harap tunggu...';
$lang['loading_text'] = 'Memuat, harap tunggu...';
$lang['appointment_loading'] = 'Memuat...';
$lang['unknown_error'] = 'Kesalahan tidak dikenal';
$lang['request_failed'] = 'Permintaan gagal';
$lang['error_processing_response'] = 'Kesalahan memproses respons';
$lang['invalid_appointment_id'] = 'ID janji temu tidak valid';
$lang['appointment_select_option'] = 'Pilih Opsi';
$lang['appointly_note'] = 'Catatan';
$lang['appointment_optional'] = '<small> (opsional) </small>';
$lang['required_field_missing'] = 'Bidang wajib diisi';
$lang['appointly_required_field'] = 'Bidang wajib';
$lang['settings_updated'] = 'Pengaturan berhasil diperbarui';

// Time and date terms
$lang['timezone'] = 'Zona Waktu';
$lang['minutes'] = 'Menit';
$lang['hours'] = 'Jam';
$lang['monday'] = 'Senin';
$lang['tuesday'] = 'Selasa';
$lang['wednesday'] = 'Rabu';
$lang['thursday'] = 'Kamis';
$lang['friday'] = 'Jumat';
$lang['saturday'] = 'Sabtu';
$lang['sunday'] = 'Minggu';
$lang['today'] = 'Hari Ini';
$lang['this_week'] = 'Minggu Ini';
$lang['this_month'] = 'Bulan Ini';
$lang['this_year'] = 'Tahun Ini';
$lang['date_range'] = 'Rentang Tanggal';
$lang['custom_range'] = 'Rentang Kustom';
$lang['filter'] = 'Filter';

// ----------------------
// APPOINTMENT BASICS
// ----------------------
$lang['appointment_back_to_appointments'] = 'Janji Temu';
$lang['appointment_create_new_appointment'] = 'Jadwalkan Konsultasi';
$lang['appointment_select_contact'] = 'Pilih Kontak';
$lang['appointment_new_appointment'] = 'Buat Janji Temu';
$lang['appointment_edit_appointment'] = 'Perbarui Janji Temu';
$lang['appointment_save_changes_btn_label'] = 'Simpan Perubahan';
$lang['appointment_subject'] = 'Tujuan Rapat';
$lang['appointment_description'] = 'Ikhtisar Sesi';
$lang['appointment_overview'] = 'Ikhtisar Janji Temu';
$lang['appointment_additional_info'] = 'Informasi Tambahan';
$lang['appointment_date'] = 'Tanggal';
$lang['appointment_time'] = 'Waktu Pilihan';
$lang['appointment_date_and_time'] = 'Tanggal / Waktu';
$lang['appointment_date_time'] = 'Tanggal & Waktu';
$lang['appointment_meeting_date'] = 'Tanggal Janji Temu';
$lang['appointment_meeting_time'] = 'Tanggal Janji Temu';
$lang['appointments_reminders_label'] = 'Pengingat';
$lang['appointment_meeting_location'] = 'Lokasi';
$lang['appointment_location'] = 'Lokasi';
$lang['appointment_location_address'] = 'Lokasi / Alamat';
$lang['appointment_location_placeholder'] = 'Masukkan detail lokasi';
$lang['appointment_duration'] = 'Durasi Janji Temu';
$lang['appointment_duration_label'] = 'Durasi';
$lang['appointment_duration_help'] = 'Atur durasi janji temu dalam menit';
$lang['appointment_notes'] = 'Catatan';
$lang['appointment_private_notes'] = 'Catatan Pribadi';
$lang['appointment_client_notes'] = 'Catatan janji temu';
$lang['appointment_created_by'] = 'Dibuat Oleh';
$lang['appointly_created_at'] = 'Dibuat Pada';
$lang['appointment_schedule_details'] = 'Jadwal Janji Temu';
$lang['appointment_additional_settings'] = 'Pengaturan Tambahan';

// Appointment Status
$lang['appointment_status'] = 'Status';
$lang['appointment_status_text'] = 'Status Janji Temu';
$lang['appointment_status_pending'] = 'Tertunda';
$lang['appointment_status_in-progress'] = 'Dalam Proses';
$lang['appointment_status_completed'] = 'Selesai';
$lang['appointment_status_cancelled'] = 'Dibatalkan';
$lang['appointment_status_no-show'] = 'Tidak Hadir';
$lang['appointment_upcoming'] = 'Mendatang';
$lang['appointment_finished'] = 'Selesai';
$lang['appointment_ongoing'] = 'Sedang berlangsung';
$lang['appointment_cancelled'] = 'Dibatalkan';
$lang['appointment_rescheduled'] = 'Dijadwalkan Ulang';
$lang['appointment_no_show'] = 'Tidak hadir';
$lang['appointment_missed_label'] = 'Terlewat';
$lang['appointment_pending_approval'] = 'Menunggu persetujuan';
$lang['appointment_not_approved'] = 'Menunggu Persetujuan';
$lang['appointment_pending_cancellation'] = 'Pembatalan Tertunda';
$lang['appointment_cancelled_text'] = 'Janji Temu Dibatalkan';
$lang['appointment_missed'] = 'Janji Temu Terlewat (Tanggal/Waktu Janji Temu sudah lewat)';
$lang['appointment_are_you_sure_mark_as_no_show'] = 'Apakah Anda yakin ingin menandai janji temu ini sebagai Tidak Hadir?';
$lang['appointment_completed'] = 'Selesai';
$lang['appointment_internal'] = 'Internal';
$lang['appointment_external'] = 'Eksternal';
$lang['appointment_lead_related'] = 'Prospek';
$lang['appointment_internal_staff'] = 'Staf';

// Status and Action Buttons
$lang['appointment_approve'] = 'Setujui';
$lang['appointment_approved'] = 'Disetujui';
$lang['appointment_mark_as_finished'] = 'Tandai sebagai selesai';
$lang['appointment_mark_as_ongoing'] = 'Tandai sebagai sedang berlangsung';
$lang['appointment_mark_as_cancelled'] = 'Dibatalkan';
$lang['appointment_mark_as_rescheduled'] = 'Tandai sebagai dijadwalkan ulang';
$lang['appointment_mark_as_no_show'] = 'Tandai sebagai tidak hadir';
$lang['appointment_cancel'] = 'Batalkan Janji Temu';
$lang['appointment_description_to_cancel'] = 'Harap jelaskan mengapa Anda ingin membatalkan janji temu ini';
$lang['appointment_describe_reason_for_cancel'] = 'Deskripsi diperlukan. Harap jelaskan apa alasan pembatalan janji temu?';
$lang['appointment_request_to_cancel'] = 'Minta Pembatalan';
$lang['appointment_request_cancellation'] = 'Minta Pembatalan';
$lang['appointment_approve_cancellation'] = 'Setujui Pembatalan';
$lang['appointment_marked_as_no_show'] = 'Ditandai sebagai Tidak Hadir';
$lang['appointly_are_you_sure_mark_as_no_show'] = 'Apakah Anda yakin ingin menandai janji temu ini sebagai Tidak Hadir?';

$lang['appointment_you_have_new_appointment'] = 'Anda memiliki janji temu baru';
$lang['appointment_initiated_by'] = 'Penyelenggara';
$lang['appointment_select_single_contact'] = 'Kontak';
$lang['appointment_deleted'] = 'Janji temu berhasil dihapus';
$lang['appointment_created'] = 'Janji temu baru berhasil dibuat';
$lang['appointment_updated'] = 'Janji temu berhasil diperbarui';
$lang['appointment_appointment_approved'] = 'Janji temu berhasil disetujui!';
$lang['appointment_no_appointments'] = 'Anda tidak memiliki janji temu untuk hari ini';
$lang['appointment_please_wait'] = 'Harap tunggu...';
$lang['appointment_no_assigned_staff_found'] = 'Tidak ada peserta staf yang ditugaskan ditemukan untuk janji temu ini';
$lang['appointment_cancel_notification'] = 'Klien telah meminta pembatalan janji temu';
$lang['appointment_marked_as_finished'] = 'Janji temu ditandai sebagai selesai';
$lang['appointment_todays_appointments'] = 'Janji temu hari ini';
$lang['appointment_scheduled_at'] = 'Dijadwalkan pada:';
$lang['appointment_view_meeting'] = 'Lihat Janji Temu';
$lang['appointment_edit_meeting'] = 'Edit Janji Temu';
$lang['appointment_dismiss_meeting'] = 'Hapus Janji Temu';
$lang['appointment_not_exists'] = 'Janji temu tidak ditemukan, mengarahkan ke daftar janji temu';
$lang['appointment_marked_as_ongoing'] = 'Janji temu ditandai sebagai sedang berlangsung';
$lang['appointment_general_info'] = 'Detail Klien';
$lang['appointment_general_details'] = 'Detail Janji Temu';
$lang['appointment_source'] = 'Tipe';
$lang['appointment_source_external_text'] = 'Eksternal (Kontak)';
$lang['appointment_source_external'] = 'Sumber (Kontak Eksternal)';
$lang['appointment_source_external_contact'] = 'Kontak Eksternal';
$lang['appointment_source_internal'] = 'Internal (Kontak)';
$lang['appointment_lead_required'] = 'Silakan pilih prospek untuk janji temu';
$lang['appointment_source_internal_client'] = 'Internal (Klien)';
$lang['appointment_source_internal_staff'] = 'Internal (Staf)';
$lang['appointment_source_lead'] = 'Prospek';
$lang['appointment_staff_only'] = 'Hanya Staf';
$lang['appointment_phone'] = 'Telepon';
$lang['appointment_name'] = 'Nama';
$lang['appointment_email'] = 'Email';
$lang['appointment_contact'] = 'Detail Klien';
$lang['appointment_sent_successfully'] = 'Permintaan janji temu baru Anda berhasil dikirim, Anda akan diberitahu melalui email ketika janji temu Anda disetujui';
$lang['appointment_squeduled_at_text'] = 'Janji temu dijadwalkan mulai pada';
$lang['appointment_staff_attendees'] = 'Peserta';
$lang['appointment_is_approved'] = 'Janji temu telah disetujui!';
$lang['appointment_public_url'] = 'URL Publik';
$lang['appointment_is_cancelled'] = 'Janji temu telah dibatalkan!';
$lang['appointment_cancel_notes'] = 'Catatan Pembatalan';
$lang['appointment_full_name'] = 'Nama Klien';
$lang['appointment_your_email'] = 'Email Anda';
$lang['appointment_your_phone'] = 'Telepon (dengan kode negara)';
$lang['appointment_your_phone_example'] = '+1 69 1234 5678';
$lang['appointment_submit'] = 'Kirim';
$lang['appointment_no_staff_members'] = 'Tidak ada anggota staf yang ditemukan, Anda harus menambahkan anggota staf dan memuat ulang tampilan ini untuk memilih seseorang untuk janji temu baru yang dikirimkan melalui formulir iFrame.';
$lang['appointment_cancellation_description_label'] = 'Alasan Pembatalan';
$lang['appointments_thank_you_cancel_request'] = 'Terima kasih atas permintaan pembatalan Anda. Kami akan meninjaunya segera.';
$lang['appointments_already_applied_for_cancelling'] = 'Anda telah meminta pembatalan janji temu ini.';
$lang['appointment_pending_cancellations'] = 'Permintaan Pembatalan Tertunda';
$lang['appointment_requested_by'] = 'Diminta Oleh';
$lang['appointment_cancellation_approved'] = 'Permintaan pembatalan berhasil disetujui';
$lang['appointly_schedule_new_appointment'] = 'Jadwalkan Janji Temu';
$lang['appointments_total_found'] = 'Total janji temu';
$lang['appointments_admin_label'] = 'Administrator';
$lang['appointments_staff_label'] = 'Staf';
$lang['appointments_no_delete_permissions'] = 'Janji temu ini tidak dibuat oleh Anda, tidak dapat dihapus';
$lang['appointment_source_external_clients_area'] = 'Sumber (Dari area klien kontak yang ada)';
$lang['appointments_source_external_label'] = 'Eksternal';
$lang['appointments_source_internal_label'] = 'Internal';
$lang['appointments_individual_contact'] = ' (Kontak Individu)';
$lang['appointments_company_for_select'] = ' - Pelanggan ';
$lang['appointment_preview_url_label'] = 'Pratinjau';
$lang['appointment_booking_form_services'] = 'Layanan Formulir Pemesanan';
$lang['appointment_source_leads_label'] = 'Prospek';
$lang['appointment_connect'] = 'Hubungkan';
$lang['appointment_connected'] = 'Terhubung';
$lang['appointments_outlook_revoke_confirm'] = 'Keluar dari Outlook';
$lang['appointment_selected_service'] = 'Layanan Terpilih';
$lang['appointment_please_enter_your_details'] = 'Silakan masukkan detail Anda';
$lang['appointments_request_feedback_from_client'] = 'Minta umpan balik dari klien';
$lang['appointments_request_feedback'] = 'Minta umpan balik';
$lang['appointment_feedback_label'] = 'Umpan Balik';
$lang['appointment_view_feedback'] = 'Lihat Umpan Balik';
$lang['appointment_feedback_label_added'] = 'Terima kasih atas umpan balik Anda!';
$lang['appointment_feedback_label_current'] = 'Umpan balik Anda saat ini!';
$lang['appointments_feedback_info'] = 'Kelola status umpan balik default Anda';
$lang['ap_feedback_extremely_good'] = 'Sangat Baik';
$lang['ap_feedback_very_good'] = 'Sangat Bagus';
$lang['ap_feedback_good'] = 'Bagus';
$lang['ap_feedback_not_bad'] = 'Tidak Buruk';
$lang['ap_feedback_bad'] = 'Buruk';
$lang['ap_feedback_the_worst'] = 'Terburuk';
$lang['ap_feedback_not_sure'] = 'Tidak Yakin';
$lang['appointment_feedback_title'] = 'Tinggalkan umpan balik Anda untuk janji temu ini';
$lang['appointmenet_feedback_comment'] = 'Komentar dan pemikiran Anda tentang janji temu ini: ';
$lang['appointment_feedback_comment_textarea'] = 'Setidaknya beberapa kata diperlukan untuk menjelaskan pengalaman Anda dengan janji temu ini';
$lang['appointment_feedback_comment_textarea_info'] = 'Harap jelaskan pengalaman Anda dengan janji temu ini';
$lang['appointment_new_feedback_added'] = 'Anda memiliki umpan balik baru untuk janji temu';
$lang['appointly_feedback_updated'] = 'Peringkat umpan balik baru saja diperbarui';
$lang['appointment_email_tracking'] = 'Pelacakan email (apakah email janji temu dibaca)';
$lang['appointment_feedback_requested_alert'] = 'Umpan balik berhasil diminta, Anda akan diberitahu melalui email jika umpan balik diberikan!';
$lang['appointment_click_to_change_rating'] = 'Klik pada bintang untuk memperbarui peringkat Anda';
$lang['appointment_staff_cant_provide_feedback'] = 'Anggota staf tidak dapat memberikan umpan balik untuk janji temu';
$lang['appointment_leave_feedback'] = 'Nilai pengalaman Anda dengan janji temu ini';
$lang['appointment_your_feedback'] = 'Umpan Balik Anda';
$lang['appointment_no_feedback_provided'] = 'Klien belum memberikan umpan balik untuk janji temu ini';
$lang['appointments_are_you_sure_request_feedback'] = 'Apakah Anda yakin ingin meminta umpan balik untuk janji temu ini? Email akan dikirim ke klien.';

// Client Area
$lang['appointly_allow_non_logged_clients_appointment'] = 'Izinkan klien yang tidak masuk untuk meminta janji temu baru melalui formulir pemesanan eksternal';
$lang['appointly_show_appointments_menu_item_in_clients_area'] = 'Tampilkan item menu permintaan janji temu di area klien (saat masuk)';
$lang['appointments_applies_for_clients'] = '(hanya berlaku untuk klien)';

// ----------------------
// TIME SLOTS & AVAILABILITY
// ----------------------
$lang['appointly_no_providers_for_service'] = 'Tidak ada penyedia yang tersedia untuk layanan ini';
$lang['appointly_no_providers_with_hours'] = 'Tidak ada penyedia dengan jam kerja yang tersedia untuk layanan ini';
$lang['appointly_select_staff'] = 'Pilih Anggota Staf';
$lang['appointment_busy_hours'] = 'Jam Sibuk';
$lang['appointment_available_hours'] = 'Jam Tersedia';
$lang['appointment_meeting_hour_is_reserved'] = 'Jam Janji Temu sudah dipesan';
$lang['appointment_requested_hour'] = 'Jam rapat yang diminta';
$lang['appointment_time_unavailable'] = 'Slot waktu ini tidak tersedia';
$lang['appointment_date_blocked'] = 'Tanggal ini diblokir oleh administrator';
$lang['appointly_available_time_slots'] = 'Slot Waktu Tersedia';
$lang['appointment_available_time_slots'] = 'Slot Waktu Tersedia';
$lang['appointment_no_slots_available'] = 'Tidak ada slot waktu yang tersedia untuk hari ini';
$lang['appointment_slot_already_booked'] = 'Slot waktu ini sudah dipesan';
$lang['appointment_unavailable_slots_shown'] = 'Slot waktu yang tidak tersedia ditampilkan dengan warna merah dan tidak dapat dipilih';
$lang['appointment_all_slots_booked'] = 'Semua slot waktu untuk hari ini sudah dipesan. Silakan coba tanggal lain.';
$lang['appointment_slot_unavailable'] = 'Slot waktu ini tidak tersedia';
$lang['appointment_checking_availability'] = 'Memeriksa ketersediaan...';
$lang['appointment_checking_time_slots'] = 'Memuat slot waktu...';
$lang['appointment_error_loading_slots'] = 'Kesalahan memuat slot waktu. Silakan coba lagi.';
$lang['appointment_not_available'] = 'Tidak tersedia';
$lang['appointment_available'] = 'Tersedia';

// Schedule and calendar
$lang['appointment_recurring'] = 'Berulang';
$lang['appointment_recurring_re_created'] = 'Janji temu berulang dibuat ulang';
$lang['appointments_all_day_event'] = 'Acara sepanjang hari';
$lang['select_blocked_days'] = 'Pilih hari';
$lang['appointments_blocked_days_on_calendar_title'] = 'Hari yang Diblokir<br><small class="text-muted">Pilih tanggal di mana janji temu tidak dapat dijadwalkan (hari libur, penutupan perusahaan, dll.).<br> Tanggal-tanggal ini tidak akan tersedia untuk pemesanan internal maupun eksternal.</small>';
$lang['appointments_dates_blocked_info_text'] = 'Tanggal yang dipilih tidak akan tersedia di kalender pemesanan. Rapat tidak dapat dijadwalkan secara internal atau eksternal pada tanggal-tanggal ini.';
$lang['appointments_blocked_days_tab_title'] = 'Hari tidak bekerja';

// Working Hours and Schedules
$lang['appointments_default_hours_label'] = 'Kelola jam janji temu default Anda';
$lang['appointly_company_schedule'] = 'Jadwal Perusahaan';
$lang['appointly_company_schedule_info'] = 'Konfigurasi jam kerja default untuk perusahaan Anda. Jam-jam ini akan digunakan untuk semua anggota staf kecuali mereka memiliki jam kerja spesifik sendiri yang ditetapkan.';
$lang['appointly_manage_company_schedule'] = 'Kelola Jadwal Perusahaan';
$lang['appointly_staff_working_hours'] = 'Jam Kerja Staf';
$lang['appointly_staff_working_hours_info'] = 'Konfigurasi jam kerja untuk anggota staf ini. Jam-jam ini akan menimpa jadwal perusahaan ketika anggota staf ini dipilih sebagai penyedia.';
$lang['appointly_view_staff_schedule'] = 'Lihat Jadwal Staf';
$lang['appointly_day'] = 'Hari';
$lang['appointly_enabled'] = 'Diaktifkan';
$lang['appointly_available'] = 'Tersedia';
$lang['appointly_start_time'] = 'Waktu Mulai';
$lang['appointly_end_time'] = 'Waktu Selesai';
$lang['appointly_use_company_schedule'] = 'Gunakan Jadwal Perusahaan';
$lang['appointly_use_company_schedule_tooltip'] = 'Centang untuk menggunakan pengaturan jadwal perusahaan untuk hari ini alih-alih pengaturan kustom.';
$lang['appointly_day_monday'] = 'Senin';
$lang['appointly_day_tuesday'] = 'Selasa';
$lang['appointly_day_wednesday'] = 'Rabu';
$lang['appointly_day_thursday'] = 'Kamis';
$lang['appointly_day_friday'] = 'Jumat';
$lang['appointly_day_saturday'] = 'Sabtu';
$lang['appointly_day_sunday'] = 'Minggu';
$lang['appointly_at_least_one_day_required'] = 'Setidaknya satu hari harus diaktifkan';
$lang['appointly_no_working_hours_found'] = 'Tidak ada jam kerja yang ditetapkan untuk penyedia ini';
$lang['appointly_closed'] = 'Tutup';
$lang['appointly_working_hours'] = 'Jam Kerja';
$lang['company_schedule_time_intervals_note'] = 'Waktu hanya dapat diatur dalam interval 15 menit (misalnya, 09:00, 09:15, 09:30, 09:45)';
$lang['working_hours_time_intervals_note'] = 'Slot waktu tersedia dalam interval 15 menit';
$lang['appointly_time_error'] = 'Waktu mulai harus lebih awal dari waktu selesai untuk';
$lang['appointly_company_schedule_sync_help'] = 'Staf dapat menggunakan opsi "Gunakan Jadwal Perusahaan" untuk menyinkronkan jam kerja mereka dengan default perusahaan.';

// Buffer settings
$lang['appointly_settings_buffer_times'] = 'Aktifkan waktu buffer antar janji temu';
$lang['appointly_settings_buffer_times_info'] = 'Waktu buffer memungkinkan periode transisi antar janji temu';
$lang['appointly_buffer_before'] = 'Buffer Sebelum (menit)';
$lang['appointly_buffer_after'] = 'Buffer Setelah (menit)';
$lang['appointly_buffer_before_help'] = 'Waktu tambahan sebelum janji temu untuk persiapan';
$lang['appointly_buffer_after_help'] = 'Waktu tambahan setelah janji temu untuk membersihkan';

// ----------------------
// SERVICES & PROVIDERS
// ----------------------
$lang['appointly_services'] = 'Layanan';
$lang['service'] = 'Layanan';
$lang['appointment_service'] = 'Layanan';
$lang['appointment_services'] = 'Layanan';
$lang['appointment_select_service'] = 'Pilih Layanan';
$lang['appointment_service_duration'] = 'Durasi';
$lang['appointment_service_price'] = 'Harga';
$lang['appointment_service_description'] = 'Deskripsi';
$lang['appointments_service_heading'] = 'Layanan';
$lang['appointments_staff_heading'] = 'Staf';
$lang['service_selection_required'] = 'Silakan pilih layanan';
$lang['appointment_service_required'] = 'Silakan pilih layanan untuk janji temu';
$lang['appointly_service_selection_label'] = 'Layanan';
$lang['appointments_selected_service'] = 'Layanan Terpilih';
$lang['no_services_available'] = 'Tidak ada layanan yang tersedia';

// Service Creation and Management
$lang['appointly_new_service'] = 'Layanan Baru';
$lang['appointly_edit_service'] = 'Edit Layanan';
$lang['appointly_service_add_success'] = 'Layanan berhasil ditambahkan';
$lang['appointly_service_edit_success'] = 'Layanan berhasil diperbarui';
$lang['appointly_service_delete_success'] = 'Layanan berhasil dihapus';
$lang['appointly_service_delete_error'] = 'Tidak dapat menghapus layanan';
$lang['error_adding_service'] = 'Tidak dapat menambahkan layanan';
$lang['error_updating_service'] = 'Tidak dapat memperbarui layanan';
$lang['service_delete_error_active'] = 'Layanan aktif dan tidak dapat dihapus atau dinonaktifkan.';
$lang['appointly_service_in_use_warning'] = 'Layanan ini sedang digunakan dalam satu atau lebih janji temu dan tidak dapat dihapus atau dinonaktifkan.';

// Service Properties
$lang['service_availability_days'] = 'Hari Tersedia';
$lang['service_hours_start'] = 'Jam Kerja Mulai';
$lang['service_hours_end'] = 'Jam Kerja Selesai';
$lang['appointly_service_name'] = 'Nama';
$lang['appointly_service_name_label'] = 'Nama Layanan';
$lang['appointly_service_duration'] = 'Durasi';
$lang['appointly_service_price'] = 'Harga';
$lang['appointly_service_color'] = 'Warna';
$lang['appointly_service_description'] = 'Deskripsi';
$lang['appointly_service_active'] = 'Aktif';
$lang['appointly_service_details'] = 'Detail Layanan';
$lang['appointly_service_back_to_list'] = 'Kembali ke Daftar Layanan';
$lang['appointly_duration_validation'] = 'Durasi harus dalam interval 15 menit (15, 30, 45, dll) dan maksimal 480 menit';
$lang['appointly_price_validation'] = 'Harga tidak boleh negatif';
$lang['appointly_duration_minutes'] = 'menit';
$lang['appointly_service_staff'] = 'Ditugaskan ke Penyedia/Staf';
// Service Validation
$lang['service_name_required'] = 'Nama layanan wajib diisi';
$lang['service_duration_required'] = 'Durasi layanan wajib diisi';
$lang['service_duration_numeric'] = 'Durasi harus berupa angka';
$lang['service_duration_greater'] = 'Durasi harus lebih besar dari 0';
$lang['service_price_required'] = 'Harga layanan wajib diisi';
$lang['service_price_greater_equal'] = 'Harga harus 0 atau lebih besar';
$lang['service_days_required'] = 'Silakan pilih setidaknya satu hari yang tersedia';
$lang['service_hours_required'] = 'Jam layanan wajib diisi';
$lang['service_hours_invalid'] = 'Format waktu tidak valid';
$lang['service_hours_start_end'] = 'Waktu selesai harus setelah waktu mulai';
$lang['appointly_staff_required'] = 'Silakan pilih anggota staf';
$lang['appointly_working_hours_required'] = '%s jam kerja wajib diisi.';
$lang['appointly_working_hours_invalid'] = 'Waktu selesai %s harus setelah waktu mulai.';
$lang['appointly_working_hours_at_least_one'] = 'Setidaknya satu hari harus diaktifkan.';

// Service Table Headers
$lang['service_th_name'] = 'Nama';
$lang['service_th_duration'] = 'Durasi (menit)';
$lang['service_th_price'] = 'Harga';
$lang['service_th_availability'] = 'Ketersediaan';
$lang['service_th_status'] = 'Status';
$lang['service_th_options'] = 'Opsi';

// Service Status
$lang['service_status_active'] = 'Aktif';
$lang['service_status_inactive'] = 'Tidak Aktif';
$lang['service_status_changed_success'] = 'Status layanan berhasil diperbarui';
$lang['service_status_changed_error'] = 'Tidak dapat memperbarui status layanan';
$lang['service_toggle_active'] = 'Alihkan status aktif';
$lang['error_updating_status'] = 'Kesalahan memperbarui status. Silakan coba lagi.';
$lang['service_status_updated'] = 'Status layanan berhasil diperbarui';
$lang['service_status_update_failed'] = 'Tidak dapat memperbarui status layanan';

// Providers
$lang['appointment_provider'] = 'Penyedia';
$lang['appointly_provider'] = 'Penyedia';
$lang['service_provider_loading'] = 'Memuat penyedia yang tersedia...';
$lang['service_no_providers'] = 'Tidak ada penyedia yang tersedia untuk layanan ini';
$lang['service_provider_select'] = 'Pilih Penyedia';
$lang['appointment_select_provider'] = 'Pilih Penyedia';
$lang['appointly_select_provider'] = 'Pilih Penyedia';
$lang['appointly_no_staff'] = 'Tidak ada anggota staf yang tersedia';
$lang['appointly_error_loading_schedule'] = 'Kesalahan memuat jadwal penyedia';
$lang['appointment_no_provider_assigned'] = 'Tidak ada penyedia yang ditugaskan';
$lang['appointly_meeting_location'] = 'Lokasi Rapat';

// Multiple Providers
$lang['appointly_settings_multi_providers'] = 'Aktifkan beberapa penyedia per layanan';
$lang['appointly_settings_multi_providers_info'] = 'Izinkan layanan disediakan oleh beberapa anggota staf';
$lang['appointly_primary_provider'] = 'Penyedia Utama';
$lang['appointly_add_provider'] = 'Tambahkan Penyedia';
$lang['appointly_remove_provider'] = 'Hapus Penyedia';
$lang['appointly_service_providers'] = 'Penyedia Layanan';
$lang['appointly_confirm_provider_removal'] = 'Apakah Anda yakin ingin menghapus penyedia ini?';
$lang['appointly_assigned_providers'] = 'Penyedia yang Ditugaskan';
$lang['appointly_service_primary_provider'] = 'Penyedia Utama';
$lang['appointment_external_provider'] = 'Penyedia Eksternal';

// ----------------------
// NOTIFICATIONS & REMINDERS
// ----------------------
$lang['appointment_modal_notification_info'] = 'Harap centang kotak jika Anda ingin peserta dan kontak yang dipilih menerima pengingat, mis. jika diatur 30 menit sebelum janji temu dimulai. Perhatikan bahwa fitur ini memerlukan konfigurasi cron job.';
$lang['appointment_sms_notification_text'] = 'Kirim Notifikasi SMS';
$lang['appointment_email_notification_text'] = 'Kirim Notifikasi Email';
$lang['appointment_send_notification'] = 'Kirim notifikasi sekarang';
$lang['appointment_notified'] = 'Pengingat Janji Temu';
$lang['appointment_notified_by_sms'] = 'Notifikasi pengingat melalui SMS dipicu';
$lang['appointment_notified_by_email'] = 'Notifikasi pengingat melalui Email dipicu';
$lang['appointment_send_early_reminders_label'] = 'Kirim Pengingat Awal';
$lang['appointly_are_you_early_reminders'] = 'Apakah Anda yakin ingin mengirim pengingat janji temu awal?';
$lang['appointly_reminders_sent'] = 'Pengingat janji temu telah dikirim ke semua peserta dan kontak';
$lang['appointment_manually_send_reminders_info'] = 'Kirim pengingat notifikasi secara manual ke semua peserta';
$lang['appointment_early_reminders_notice_label'] = 'Janji temu tidak Dibatalkan atau Selesai untuk mengirim Pengingat Awal';
$lang['appointment_email_read_at'] = 'Dibaca pada: ';
$lang['appointment_email_not_read'] = 'Belum Dibaca';
$lang['appoontment_sms_notification'] = 'Notifikasi SMS';
$lang['appoontment_email_notification'] = 'Notifikasi Email';

// ----------------------
// CALENDAR INTEGRATIONS
// ----------------------
// Google Calendar
$lang['appointly_calendar_integrations'] = 'Integrasi Kalender';
$lang['appointment_add_to_google_calendar'] = 'Tambahkan ke Google Kalender';
$lang['appointments_google_already_signed'] = 'Anda sudah masuk ke Akun Google Anda.';
$lang['appointments_added_to_google_calendar'] = 'Ditambahkan ke Google Kalender';
$lang['appointments_sign_in_google'] = 'Masuk dengan Google';
$lang['appointments_google_revoke_confirm'] = 'Keluar dari Google';
$lang['appointments_google_revoke'] = 'Cabut sesi Google Kalender saat ini dan hapus semua izin yang diberikan ke akun Google Anda.';
$lang['appointments_google_calendar_client_id'] = 'ID Klien API Google Kalender <strong>(diambil dari Pengaturan->Google->ID KLIEN API)</strong>';
$lang['appointments_google_calendar_settings'] = 'Pengaturan API Google Kalender';
$lang['appointments_google_calendar_client_secret'] = 'Rahasia Klien API Google Kalender';
$lang['appointments_redirect_url'] = 'URI pengalihan Otorisasi Google';
$lang['appointly_show_google_appointments_from'] = 'Filter janji temu berdasarkan rentang tanggal:';
$lang['appointments_delete_from_google_label'] = 'Sebelum janji temu dihapus, hapus juga janji temu yang dibuat di Google Kalender Anda <small>(Hanya berlaku jika Google Kalender diaktifkan dan disinkronkan)</small>';
$lang['appointment_add_to_google_calendar_external'] = 'Sertakan janji temu eksternal ini ke Google Kalender Anda (Centang dan Klik Simpan)';
$lang['appointment_open_google_calendar'] = 'Buka di Google Kalender';
$lang['appointment_google_not_added_yet'] = 'Sepertinya janji temu ini belum ditambahkan ke Google Kalender anggota staf mana pun. Apakah Anda ingin menambahkan janji temu ini ke Google Kalender Anda?';
$lang['appointment_add_to_calendar'] = 'Tambahkan ke Kalender';
$lang['appointment_view_in_calendar'] = 'Lihat di Google Kalender';
$lang['appointment_calendar_adding_to_google'] = 'Menambahkan ke Google Kalender...';
$lang['appointment_error_adding_to_calendar'] = 'Tidak dapat menambahkan ke kalender. Silakan coba lagi.';
$lang['event_not_found_in_google'] = 'Acara mungkin tidak ada di Google Kalender';
$lang['appointments_delete_from_google_calendar'] = 'Hapus dari Google Kalender';
$lang['appointments_synced_from_google'] = 'Disinkronkan Dari Google';
$lang['appointments_googlesync_show_in_table_label'] = 'Jika integrasi Google Kalender aktif, tampilkan semua janji temu Google Kalender dalam tampilan tabel default.';
$lang['appointly_google_synced_title'] = ' Disinkronkan Google';
$lang['appointment_hide_google_calendar'] = 'Tampilkan Tampilan Default';
$lang['appointment_google_calendar_synced'] = 'Google Kalender Disinkronkan';
$lang['appointment_googlesync_only_today'] = 'Hari Ini';
$lang['appointment_googlesync_only_last_month'] = 'Bulan Lalu';
$lang['appointment_googlesync_only_last_three_months'] = '3 Bulan Terakhir';
$lang['appointment_googlesync_only_last_six_months'] = '6 Bulan Terakhir';
$lang['appointment_googlesync_only_last_year'] = 'Tahun Lalu';
$lang['appointment_googlesync_show_all'] = 'Semua';
$lang['appointly_not_including_two_way_synced_appointments'] = 'Tidak termasuk janji temu yang disinkronkan dua arah';
$lang['appointment_external_calendar_event'] = 'Acara Kalender Eksternal';

// Video Meeting
$lang['appointment_google_meet_info'] = 'Janji temu ini ditambahkan ke Google Kalender, Anda dapat menggunakan Video Meeting untuk bertemu online dengan klien Anda';
$lang['appointment_google_meet_info_2'] = 'Janji temu ini ditambahkan ke Google Kalender';
$lang['appointment_google_client_meet_info'] = 'Terhubung melalui Video Meeting';
$lang['appointment_connect_via_google_meet'] = 'Saya ingin terhubung melalui Video Meeting';
$lang['appointment_meet_message'] = 'Halo<br><br>Silakan ikuti URL ini untuk terhubung dengan saya melalui Video Meeting: ';
$lang['appointment_meeting_request_sent'] = 'Pesan permintaan rapat Anda berhasil dikirim';
$lang['appointment_leave_a_comment'] = 'Apakah Anda ingin meninggalkan komentar';
$lang['appointment_google_meet_connect_message'] = 'Kirim email ke peserta dan minta untuk terhubung melalui Video Meeting';
$lang['appointment_google_meet_modal_custom_label'] = 'Undang staf dan klien ke Video Meeting melalui Email';
$lang['appointment_google_meet'] = 'Video Meeting';
$lang['appointment_google_calendar'] = 'Google Kalender';

// Enhanced Video Meeting Settings
$lang['appointment_google_meet_enhanced_settings'] = 'Pengaturan Video Meeting yang Ditingkatkan';
$lang['appointly_auto_enable_google_meet'] = 'Aktifkan Video Meeting secara otomatis untuk semua janji temu';
$lang['appointly_auto_enable_google_meet_help'] = 'Saat diaktifkan, semua janji temu baru akan secara otomatis menyertakan tautan Video Meeting saat disinkronkan ke Google Kalender';
$lang['appointly_google_meet_default_settings'] = 'Pengaturan Video Meeting Default';
$lang['appointly_google_meet_enable_recording'] = 'Aktifkan perekaman secara default';
$lang['appointly_google_meet_enable_waiting_room'] = 'Aktifkan ruang tunggu secara default';
$lang['appointly_google_meet_reminder_minutes'] = 'Waktu pengingat default sebelum rapat';
$lang['appointly_google_meet_reminder_help'] = 'Atur waktu pengingat default untuk janji temu Video Meeting';
$lang['appointly_disable_google_meeting_emails'] = 'Nonaktifkan notifikasi email Google Kalender';
$lang['appointly_disable_google_meeting_emails_help'] = 'Saat diaktifkan, Google tidak akan mengirim notifikasi email otomatis untuk acara kalender';
$lang['appointly_minutes'] = 'menit';
$lang['appointly_hour'] = 'jam';
$lang['appointly_hours'] = 'jam';
$lang['appointly_day'] = 'hari';

// Enhanced Video Meeting Features
$lang['appointment_google_meet_join_before_start'] = 'Gabung Video Meeting';
$lang['appointment_google_meet_copy_link'] = 'Salin Tautan Video Meeting';
$lang['appointment_google_meet_link_copied'] = 'Tautan Video Meeting disalin ke papan klip';
$lang['appointment_google_meet_test_connection'] = 'Uji Koneksi Video Meeting';
$lang['appointment_google_meet_connection_success'] = 'Uji koneksi Video Meeting berhasil';
$lang['appointment_google_meet_connection_failed'] = 'Uji koneksi Video Meeting gagal';
$lang['appointment_google_meet_quick_join'] = 'Gabung Rapat Cepat';
$lang['appointment_google_meet_meeting_details'] = 'Detail Rapat';
$lang['appointment_google_meet_dial_in'] = 'Informasi Panggilan';
$lang['appointment_google_meet_share_screen'] = 'Berbagi Layar Tersedia';
$lang['appointment_google_meet_record_meeting'] = 'Perekaman Tersedia';
$lang['appointment_google_meet_waiting_room_enabled'] = 'Ruang Tunggu Diaktifkan';
$lang['appointment_google_meet_testing_connection'] = 'Menguji koneksi Video Meeting...';
$lang['appointment_google_meet_copy_failed'] = 'Gagal menyalin tautan. Harap salin secara manual.';
$lang['appointment_google_meet_email_message'] = 'Pesan Email';
$lang['appointment_google_meet_send_invitation'] = 'Kirim Undangan Video Meeting';
$lang['appointment_google_meet_message_required'] = 'Harap masukkan pesan untuk dikirim';
$lang['sending'] = 'Mengirim...';
$lang['appointment_email_send_failed'] = 'Gagal mengirim email. Harap periksa pengaturan email Anda.';
$lang['appointment_google_meet_send_to'] = 'Kirim Ke';
$lang['appointment_google_meet_primary_recipient'] = 'Penerima Utama';
$lang['appointment_google_meet_also_notify_attendees'] = 'Juga beritahu peserta staf';
$lang['appointment_no_email_provided'] = 'Tidak ada alamat email yang diberikan';
$lang['appointment_google_meet_hd_video_audio'] = 'Video & Audio HD';
$lang['appointment_google_meet_always_enabled'] = 'Selalu diaktifkan';
$lang['appointment_google_meet_recording_enabled'] = 'Perekaman Diaktifkan';
$lang['appointment_google_meet_recording_disabled'] = 'Perekaman Dinonaktifkan';
$lang['appointment_google_meet_waiting_room_enabled_status'] = 'Status Ruang Tunggu Diaktifkan';
$lang['appointment_google_meet_waiting_room_disabled'] = 'Ruang Tunggu Dinonaktifkan';
$lang['appointment_google_meet_quick_actions'] = 'Tindakan Cepat';
$lang['appointment_google_meet_join_meeting'] = 'Gabung Video Meeting';
$lang['appointment_google_meet_send_invitation_btn'] = 'Kirim Undangan';

// Google Maps
$lang['appointment_google_maps'] = 'Buka di Google Maps';
$lang['appointly_google_maps_not_shown'] = 'Google Maps tidak akan ditampilkan.';
$lang['appointly_google_api_key_notset'] = 'Kunci API Google tidak diatur, harap atur kunci API Google di pengaturan janji temu';
$lang['appointly_message_will_hide'] = 'Pesan ini akan disembunyikan dalam 5 detik';

// Outlook Calendar
$lang['appointment_login_to_outlook'] = 'Masuk dengan Outlook';
$lang['appointment_logout_from_outlook'] = 'Keluar dari Outlook';
$lang['appointment_open_outlook_calendar'] = 'Buka di Kalender Outlook';
$lang['appointments_outlook_revoke'] = 'Cabut sesi Kalender Outlook saat ini dan hapus semua izin yang diberikan ke akun outlook Anda.';
$lang['appointment_redirect_url_logout'] = 'URI Pengalihan dan Otorisasi Outlook';
$lang['appointment_outlook_api_label'] = 'API Kalender Outlook';
$lang['appointment_outlook_client_id'] = 'ID Aplikasi (klien)';
$lang['appointment_outlook_calendar'] = 'Kalender Outlook';
$lang['appointment_outlook_calendar_info'] = 'Janji temu ini ditambahkan ke Kalender Outlook';
$lang['appointment_add_to_outlook'] = 'Tambahkan ke Kalender Outlook';
$lang['appointment_outlook_not_added_yet'] = 'Belum ditambahkan ke Kalender Outlook';
$lang['appointment_is_added_to_outlook'] = 'Ditambahkan ke Kalender Outlook';
$lang['appointment_calendar_adding_to_outlook'] = 'Menambahkan ke Kalender Outlook...';
$lang['appointment_added_to_outlook'] = 'Acara berhasil ditambahkan ke Kalender Outlook';
$lang['appointment_added_to_outlook_but_not_saved'] = 'Acara ditambahkan ke Outlook tetapi gagal disimpan di database';
$lang['appointment_outlook_event_saved'] = 'Detail acara Outlook disimpan';
$lang['appointment_outlook_event_save_failed'] = 'Gagal menyimpan detail acara Outlook';
$lang['appointment_outlook_error'] = 'Terjadi kesalahan saat menambahkan ke Outlook';
$lang['appointment_outlook_auth_error'] = 'Kesalahan autentikasi Outlook';
$lang['appointment_invalid_date'] = 'Tanggal janji temu tidak valid';
$lang['appointment_sign_in_to_outlook'] = 'Masuk ke Outlook';
$lang['appointment_sign_out_from_outlook'] = 'Keluar dari Outlook';
$lang['appointments_outlook_view_in_calendar'] = 'Lihat di Kalender Outlook';
$lang['appointment_outlook_calendar_title'] = 'Kalender Outlook';
$lang['appointment_outlook_sync_status'] = 'Status Sinkronisasi Outlook';
$lang['appointment_outlook_last_synced'] = 'Terakhir disinkronkan: %s';
$lang['appointment_outlook_sync_error'] = 'Sinkronisasi terakhir gagal: %s';
$lang['appointment_outlook_event_deleted'] = 'Acara Outlook berhasil dihapus';
$lang['appointment_outlook_event_delete_failed'] = 'Gagal menghapus acara Outlook';
$lang['appointments_table_calendar'] = 'Ditambahkan ke Kalender';
$lang['appointment_not_added_to_calendars_yet'] = 'Belum ditambahkan ke kalender mana pun.';
$lang['permission_approve'] = 'Setujui';
$lang['permission_view_reports'] = 'Lihat Laporan';
$lang['appointly_missing_required_fields'] = 'Bidang wajib diisi hilang';
$lang['appointly_service_not_found'] = 'Layanan tidak ditemukan';
$lang['appointly_error_getting_time_slots'] = 'Kesalahan mendapatkan slot waktu yang tersedia. Silakan coba lagi.';
$lang['appointly_invalid_working_hours'] = 'Konfigurasi jam kerja tidak valid';
$lang['appointly_service_price_invalid'] = 'Harga layanan harus berupa angka yang valid (0 atau lebih besar)';
$lang['appointly_service_duration_invalid'] = 'Durasi layanan harus berupa angka positif';
$lang['appointly_service_providers_required'] = 'Setidaknya satu penyedia layanan harus ditugaskan';
$lang['appointly_available_time_slots'] = 'Slot Waktu Tersedia';
$lang['appointment_loading'] = 'Memuat...';
$lang['appointly_error_loading_providers'] = 'Kesalahan memuat penyedia';
$lang['appointly_no_data_available'] = 'Tidak ada data yang tersedia';
$lang['appointly_please_try_again'] = 'Silakan coba lagi';
$lang['appointly_installation_complete'] = 'Instalasi berhasil diselesaikan';
$lang['appointly_database_updated'] = 'Database berhasil diperbarui';
$lang['appointly_menu_reset'] = 'Menu berhasil diatur ulang';
$lang['appointly_default_service_created'] = 'Layanan default berhasil dibuat';
$lang['appointly_working_hours_configured'] = 'Jam kerja berhasil dikonfigurasi';
$lang['appointment_select_service_provider_first'] = 'Silakan pilih layanan dan penyedia terlebih dahulu';
$lang['appointment_are_you_sure_to_cancel'] = 'Apakah Anda yakin ingin membatalkan janji temu ini?';
$lang['appointment_are_you_sure_to_mark_as_ongoing'] = 'Apakah Anda yakin ingin menandai janji temu ini sebagai sedang berlangsung?';
$lang['appointment_error_occurred'] = 'Terjadi kesalahan. Silakan coba lagi.';
$lang['appointment_closed'] = 'Pemesanan janji temu saat ini ditutup';
$lang['appointment_time_required'] = 'Silakan pilih slot waktu';
$lang['appointment_no_providers'] = 'Tidak ada penyedia yang tersedia untuk layanan ini';
$lang['appointment_provider_not_available'] = 'Penyedia tidak tersedia pada hari ini';
$lang['appointment_minutes'] = 'menit';
$lang['appointment_unavailable'] = 'Tidak Tersedia';
$lang['is_required'] = 'wajib diisi';
$lang['appointment_schedule_info'] = 'Jadwal Janji Temu';
$lang['appointment_form_info'] = 'Informasi Janji Temu';
$lang['appointment_marked_as_approved'] = 'Janji temu ditandai sebagai disetujui';
$lang['appointment_cancellation_approval_failed'] = 'Gagal menyetujui pembatalan janji temu';
$lang['appointment_send_an_sms'] = 'Kirim SMS';
$lang['appointment_call_number'] = 'Panggil';
$lang['appointment_actions'] = 'Tindakan';
$lang['appointment_staff_cannot_provide_feedback'] = 'Anggota staf tidak dapat memberikan umpan balik untuk janji temu';
$lang['appointment_thank_you_for_feedback'] = 'Terima kasih atas umpan balik Anda!';
$lang['appointment_feedback_comment_required'] = 'Komentar umpan balik wajib diisi';

// Reschedule functionality client side
$lang['appointment_reschedule'] = 'Jadwalkan Ulang';
$lang['appointment_reschedule_reason'] = 'Alasan Penjadwalan Ulang';
$lang['appointment_reschedule_reason_placeholder'] = 'Harap jelaskan mengapa Anda perlu menjadwalkan ulang janji temu ini...';
$lang['appointment_reschedule_reason_required'] = 'Alasan penjadwalan ulang wajib diisi';
$lang['appointment_request_reschedule'] = 'Minta Penjadwalan Ulang';
$lang['appointment_new_date'] = 'Tanggal Baru';
$lang['appointment_new_time'] = 'Waktu Baru';
$lang['appointment_reschedule_request_submitted'] = 'Permintaan penjadwalan ulang Anda telah dikirimkan dan akan ditinjau oleh staf kami.';
$lang['appointment_cannot_be_rescheduled'] = 'Janji temu ini tidak dapat dijadwalkan ulang karena statusnya saat ini.';

$lang['appointment_processing'] = 'Memproses...';
$lang['appointment_select_date_first'] = 'Silakan pilih tanggal terlebih dahulu';
$lang['appointment_please_select_date_time'] = 'Silakan pilih tanggal dan waktu';
$lang['appointment_current_details'] = 'Detail Saat Ini';
$lang['appointment_loading_available_times'] = 'Memuat waktu yang tersedia...';
$lang['appointment_no_available_slots'] = 'Tidak ada slot yang tersedia';
$lang['appointment_no_available_times'] = 'Tidak ada waktu yang tersedia';

$lang['appointment_reschedule_requested'] = 'Penjadwalan Ulang Diminta';
$lang['appointment_reschedule_pending_review'] = 'Permintaan penjadwalan ulang Anda sedang menunggu tinjauan oleh staf kami.';
$lang['appointment_reschedule_pending_notice'] = 'Permintaan Penjadwalan Ulang Tertunda';
$lang['appointment_requested_date'] = 'Tanggal yang Diminta';
$lang['appointment_requested_time'] = 'Waktu yang Diminta';
$lang['appointment_current_date'] = 'Tanggal Saat Ini';
$lang['appointment_approve_reschedule'] = 'Setujui Penjadwalan Ulang';
$lang['appointment_reject_reschedule'] = 'Tolak Penjadwalan Ulang';
$lang['appointment_pending_reschedules'] = 'Penjadwalan Ulang Tertunda';
$lang['appointment_reschedule_approved'] = 'Penjadwalan Ulang Disetujui';
$lang['appointment_reschedule_rejected'] = 'Penjadwalan Ulang Ditolak';
$lang['appointment_cancellation_requested'] = 'Pembatalan Diminta';
$lang['appointment_cancellation_pending_review'] = 'Permintaan pembatalan Anda sedang menunggu tinjauan oleh staf kami.';
$lang['appointment_cancellation_reason'] = 'Alasan Pembatalan';
$lang['appointment_cancellation_notes'] = 'Catatan Pembatalan';
$lang['appointment_cancellation_notes_placeholder'] = 'Harap jelaskan mengapa Anda ingin membatalkan janji temu ini...';
$lang['appointment_cancellation_notes_required'] = 'Catatan pembatalan wajib diisi';
$lang['appointment_cancellation_request_submitted'] = 'Permintaan pembatalan Anda telah dikirimkan dan akan ditinjau oleh staf kami.';
$lang['appointment_confirm_approve_reschedule'] = 'Apakah Anda yakin ingin menyetujui permintaan penjadwalan ulang ini? Ini akan memperbarui janji temu ke tanggal dan waktu baru.';
$lang['appointment_reschedule_denial_reason'] = 'Harap berikan alasan untuk menolak permintaan penjadwalan ulang ini (ini akan dikirim ke klien):';
$lang['appointment_reschedule_approved_successfully'] = 'Permintaan penjadwalan ulang telah disetujui dan janji temu telah diperbarui.';
$lang['appointment_reschedule_denied_successfully'] = 'Permintaan penjadwalan ulang telah ditolak dan klien telah diberitahu.';
$lang['appointment_reschedule_approval_failed'] = 'Gagal menyetujui permintaan penjadwalan ulang. Silakan coba lagi.';
$lang['appointment_reschedule_denial_failed'] = 'Gagal menolak permintaan penjadwalan ulang. Silakan coba lagi.';
$lang['appointment_view_details'] = 'Lihat Detail';
$lang['appointment_client_information'] = 'Informasi Klien';
$lang['appointment_provider_information'] = 'Informasi Penyedia';
$lang['appointment_reschedule_action_required'] = 'Tindakan Diperlukan';
$lang['appointment_reschedule_instructions'] = 'Ketika klien meminta penjadwalan ulang, mereka akan muncul di sini untuk persetujuan Anda. Menyetujui akan secara otomatis memperbarui janji temu dan memberi tahu klien melalui email.';
$lang['appointment_no_pending_reschedules'] = 'Tidak ada permintaan penjadwalan ulang yang tertunda ditemukan.';
$lang['appointment_reschedule_request_details'] = 'Detail Permintaan Penjadwalan Ulang';
$lang['appointment_reschedule_date_required'] = 'Tanggal baru wajib diisi';
$lang['appointment_reschedule_time_required'] = 'Waktu baru wajib diisi';
$lang['appointment_reschedule_future_datetime'] = 'Silakan pilih tanggal dan waktu di masa mendatang';
$lang['appointment_reschedule_request_subject'] = 'Permintaan Penjadwalan Ulang Janji Temu';
$lang['appointment_reschedule_approved_subject'] = 'Penjadwalan Ulang Janji Temu Disetujui';
$lang['appointment_reschedule_denied_subject'] = 'Penjadwalan Ulang Janji Temu Ditolak';
$lang['appointment_book_again'] = 'Pesan Lagi';
$lang['appointment_cancelled_title'] = 'Janji temu ini telah dibatalkan';
$lang['appointment_book_new_appointment'] = 'Apakah Anda ingin memesan janji temu baru?';
$lang['appointment_book_new'] = 'Pesan Janji Temu Baru';
$lang['appointment_deny_reschedule'] = 'Tolak Penjadwalan Ulang';
$lang['appointment_reschedule_denial_reason_prompt'] = 'Harap berikan alasan untuk menolak permintaan penjadwalan ulang ini:';
$lang['appointment_reschedule_denial_reason_required'] = 'Alasan penolakan wajib diisi';
$lang['appointment_confirm_approve_cancellation'] = 'Apakah Anda yakin ingin menyetujui permintaan pembatalan ini? Ini akan membatalkan janji temu secara permanen.';
$lang['appointment_cancelled_book_again_message'] = 'Janji temu ini telah dibatalkan. Apakah Anda ingin memesan janji temu baru?';
$lang['appointment_no_show_book_again_message'] = 'Janji temu ini tidak hadir. Apakah Anda ingin memesan janji temu baru?';

// Calendar Integration Removal
$lang['appointment_remove_google_integration'] = 'Hapus Integrasi Google Kalender';
$lang['appointment_remove_outlook_integration'] = 'Hapus Integrasi Kalender Outlook';
$lang['appointment_confirm_remove_google_integration'] = 'Apakah Anda yakin ingin menghapus integrasi Google Kalender dari janji temu ini? Ini juga akan menghapus acara dari Google Kalender jika memungkinkan.';
$lang['appointment_confirm_remove_outlook_integration'] = 'Apakah Anda yakin ingin menghapus integrasi Kalender Outlook dari janji temu ini? Ini juga akan menghapus acara dari Kalender Outlook jika memungkinkan.';
$lang['appointment_google_integration_removed'] = 'Integrasi Google Kalender dihapus dari janji temu';
$lang['appointment_google_integration_removed_and_deleted'] = 'Integrasi Google Kalender dihapus dan acara dihapus dari Google Kalender';
$lang['appointment_google_removal_failed'] = 'Gagal menghapus integrasi Google Kalender';
$lang['appointment_outlook_integration_removed'] = 'Integrasi Kalender Outlook dihapus dari janji temu';
$lang['appointment_outlook_integration_removed_and_deleted'] = 'Integrasi Kalender Outlook dihapus dan acara dihapus dari Kalender Outlook';
$lang['appointment_outlook_removal_failed'] = 'Gagal menghapus integrasi Kalender Outlook';
$lang['appointment_missing_required_fields'] = 'Bidang wajib diisi hilang';
$lang['appointment_not_found'] = 'Janji temu tidak ditemukan';
$lang['appointment_outlook_not_authenticated_warning'] = 'Peringatan: Anda saat ini tidak diautentikasi dengan Outlook. Integrasi hanya akan dihapus secara lokal, tetapi acara akan tetap ada di kalender Outlook Anda.';
$lang['appointment_outlook_not_available_warning'] = 'Peringatan: Integrasi Outlook tidak tersedia. Integrasi hanya akan dihapus secara lokal.';
$lang['appointment_outlook_integration_removed_local_only'] = 'Integrasi kalender Outlook dihapus secara lokal. Catatan: Acara mungkin masih ada di kalender Outlook Anda.';

// Dashboard Widgets
$lang['appointly_upcoming_appointments'] = 'Janji Temu Mendatang';
$lang['appointly_no_upcoming_appointments'] = 'Tidak Ada Janji Temu Mendatang';
$lang['appointly_no_appointments_in_range'] = 'Tidak ada janji temu yang dijadwalkan dalam %s';
$lang['appointly_next_7_days'] = '7 Hari Berikutnya';
$lang['appointly_next_14_days'] = '14 Hari Berikutnya';
$lang['appointly_next_30_days'] = '30 Hari Berikutnya';
$lang['appointly_next_4_weeks'] = '4 Minggu Berikutnya';
$lang['appointly_view_all_appointments'] = 'Lihat Semua Janji Temu';
$lang['appointly_dashboard_widgets_settings'] = 'Pengaturan Widget Dasbor';
$lang['appointly_today_widget_enabled'] = 'Tampilkan widget janji temu hari ini di dasbor';
$lang['appointly_upcoming_widget_enabled'] = 'Tampilkan widget janji temu mendatang di dasbor';
$lang['appointly_upcoming_widget_range'] = 'Rentang tanggal widget janji temu mendatang';
$lang['appointly_today'] = 'Hari Ini';
$lang['appointly_tomorrow'] = 'Besok';
$lang['days'] = 'hari';

$lang['appointly_invoice_default_vat'] = 'PPN/Persentase Pajak Default';
$lang['appointly_invoice_vat_help'] = 'Persentase pajak default yang akan diterapkan pada faktur yang dibuat secara otomatis (atur ke 0 untuk tanpa pajak)';
$lang['appointly_invoice_tax_type_help'] = 'Pilih bagaimana Anda ingin menerapkan pajak pada faktur janji temu';
$lang['appointly_invoice_tax_type_label'] = 'Metode Penerapan Pajak';
$lang['appointly_tax_type_none'] = 'Tanpa Pajak';
$lang['appointly_tax_type_custom'] = 'Persentase Kustom';
$lang['appointly_tax_type_system'] = 'Gunakan Tarif Pajak CRM';
$lang['appointly_default_vat_label'] = 'Persentase Pajak Kustom';
$lang['appointly_default_vat_help'] = 'Persentase pajak kustom yang akan diterapkan pada faktur (atur ke 0 untuk tanpa pajak)';
$lang['appointly_system_tax_label'] = 'Pilih Tarif Pajak';
$lang['appointly_system_tax_help'] = 'Pilih dari tarif pajak yang Anda konfigurasi di CRM';

// Enhanced Invoice Settings - Tab Names
$lang['appointly_invoice_settings'] = 'Faktur & Pajak';
$lang['appointly_tax_settings'] = 'Konfigurasi Pajak';
$lang['appointly_tax_settings_help'] = 'Konfigurasi bagaimana pajak diterapkan pada faktur yang dibuat dari janji temu. Anda dapat menggunakan sistem pajak CRM atau menetapkan persentase kustom.';
$lang['appointly_tax_type_label'] = 'Metode Penerapan Pajak';
$lang['appointly_tax_type_help'] = 'Pilih bagaimana Anda ingin menerapkan pajak pada faktur janji temu';

// Form Field Labels
$lang['appointly_enable'] = 'Aktifkan';
$lang['appointly_disable'] = 'Nonaktifkan';
$lang['appointly_yes'] = 'Ya';
$lang['appointly_no'] = 'Tidak';

// Client Dashboard Language Strings
$lang['appointment_client_dashboard_description'] = 'Kelola janji temu Anda, lihat riwayat, dan pesan janji temu baru.';
$lang['appointment_book_new'] = 'Pesan Janji Temu Baru';
$lang['appointment_all'] = 'Semua';
$lang['appointment_total_appointments'] = 'Total Janji Temu';
$lang['appointment_completed_appointments'] = 'Selesai';
$lang['appointment_upcoming_appointments'] = 'Mendatang';
$lang['appointment_cancelled_appointments'] = 'Dibatalkan';
$lang['appointment_no_appointments_found'] = 'Tidak ada janji temu ditemukan';
$lang['appointment_no_appointments_match_filter'] = 'Tidak ada janji temu yang cocok dengan filter saat ini';
$lang['appointment_date_and_time'] = 'Tanggal & Waktu';
$lang['appointment_details'] = 'Detail Janji Temu';
$lang['appointment_book_again'] = 'Pesan Lagi';
$lang['view_invoice'] = 'Lihat Faktur';

// Cancel and Reschedule
$lang['appointment_cancel_reason'] = 'Alasan Pembatalan';
$lang['appointment_cancel_reason_placeholder'] = 'Harap berikan alasan untuk membatalkan janji temu ini...';
$lang['appointment_reschedule_reason'] = 'Alasan Penjadwalan Ulang';
$lang['appointment_reschedule_reason_placeholder'] = 'Harap jelaskan mengapa Anda perlu menjadwalkan ulang janji temu ini...';
$lang['appointment_new_date'] = 'Tanggal Baru';
$lang['appointment_new_time'] = 'Waktu Baru';
$lang['appointment_request_reschedule'] = 'Minta Penjadwalan Ulang';
$lang['appointment_cancel_request_sent'] = 'Permintaan pembatalan telah dikirim ke staf untuk persetujuan.';
$lang['appointment_cancel_request_failed'] = 'Gagal mengirim permintaan pembatalan. Silakan coba lagi.';
$lang['appointment_reschedule_request_sent'] = 'Permintaan penjadwalan ulang telah dikirim ke staf untuk persetujuan.';
$lang['appointment_reschedule_request_failed'] = 'Gagal mengirim permintaan penjadwalan ulang. Silakan coba lagi.';
$lang['appointment_client_dashboard'] = 'Dasbor Klien';
$lang['appointment_available_times'] = 'Waktu Tersedia';
$lang['appointment_error_loading_times'] = 'Kesalahan memuat waktu';
$lang['appointment_cancel_request_submitted'] = 'Permintaan pembatalan berhasil dikirim';
$lang['appointment_date_time_required'] = 'Tanggal dan waktu wajib diisi';
$lang['appointment_reschedule_pending'] = 'Permintaan penjadwalan ulang menunggu persetujuan';
$lang['appointly_invoice_payment_mode_changed'] = 'Mode pembayaran faktur diubah, harap simpan pengaturan untuk menerapkan perubahan';
$lang['payment_received_for_appointment'] = 'Pembayaran diterima untuk:';
$lang['appointly_create_invoice_when_completed'] = 'Buat faktur saat janji temu selesai?';
$lang['invoice_created_for_appointment'] = 'Faktur #%s telah dibuat untuk janji temu ini.';
$lang['appointment_email_sent_success'] = 'Email berhasil dikirim';
$lang['appointment_email_sent_failed'] = 'Gagal mengirim email';
$lang['customer_permission_appointments'] = 'Janji Temu';
$lang['appointment_contact_relationship'] = 'Kontak & Informasi Hubungan';
$lang['appointment_security_verification'] = 'Verifikasi Keamanan';
$lang['appointment_view_on_map'] = 'Lihat di Peta';
$lang['appointment_no_notes_available'] = 'Tidak ada catatan yang tersedia untuk janji temu ini';
$lang['appointment_session_overview'] = 'Ringkasan Sesi';
$lang['appointly_show_staff_email_booking_form'] = 'Tampilkan alamat email staf di formulir pemesanan';
$lang['appointly_no_available_time_slots'] = 'Slot waktu tidak tersedia';
$lang['appointment_status_changed_successfully'] = 'Status janji temu berhasil diubah';
$lang['appointment_status_change_failed'] = 'Gagal mengubah status janji temu';
