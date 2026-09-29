<?php


// ----------------------
// ADMIN SETTINGS & DASHBOARD
// ----------------------
// Module Information
$lang['appointly_module_name'] = 'Appointly';
$lang['appointly_current_version'] = 'Mevcut modül sürümü: ';
$lang['appointly_settings_label_pointer'] = '<strong>Kurulum -> Ayarlar -> Randevular</strong>';
$lang['appointly_integrations'] = 'Entegrasyonlar';
$lang['general_settings'] = 'Genel Ayarlar';
$lang['new_appointment_notification'] = 'Yeni Randevu Bildirimi';

// Admin Settings
$lang['appointments_approve_automatically_label'] = 'Harici web formlarından gönderilen randevuları otomatik olarak onayla';
$lang['appointments_reminder_time_value'] = 'Randevunuzdan önce e-posta bildirimi almak için değer ekleyin (varsayılan 30 dakika önce)';
$lang['appointly_default_table_filter_label'] = 'Varsayılan randevu tablosu filtresi';
$lang['appointment_your_settings'] = 'Randevu Ayarlarınız';
$lang['appointments_buffer_hours_label'] = 'Geçmiş tarih seçicisini devre dışı bırak';

// Dashboard & Analytics
$lang['appointly_show_summary_in_appointments_dashboard'] = 'Randevu özetini randevu panosunda göster';
$lang['appointments_summary'] = 'Randevu özeti';
$lang['appointment_appointments_dashboard'] = 'Kontrol Paneli';
$lang['appointment_want_to_go_back'] = 'Randevular';
$lang['appointly_analytics_dashboard'] = 'Appointly Analiz Paneli';
$lang['appointment_history_label'] = 'Geçmiş Randevular';
$lang['appointment_history_label_menu_label'] = 'Randevu Geçmişi';
$lang['appointment_services_menu_label'] = 'Hizmetler';
$lang['appointment_analytics_and_reports_menu_label'] = 'Analizler ve Raporlar';

// Invoice Conversion
$lang['appointment_convert_to_invoice'] = 'Faturaya Dönüştür';
$lang['appointment_converted_to_invoice'] = 'Randevu başarıyla faturaya dönüştürüldü';
$lang['appointment_convert_to_invoice_success'] = 'Randevu başarıyla #%s faturaya dönüştürüldü';
$lang['appointment_convert_to_invoice_failed'] = 'Randevuyu faturaya dönüştürme başarısız oldu';
$lang['appointment_are_you_sure_convert_to_invoice'] = 'Bu randevuyu faturaya dönüştürmek istediğinizden emin misiniz?';
$lang['appointment_view_invoice'] = 'Faturayı Görüntüle #%s';
$lang['appointment_convert_to_invoice_tooltip'] = 'Fatura oluşturma yalnızca gerçek müşterilerle yapılan randevular için geçerlidir. Potansiyel müşteriler veya harici kişiler için, önce onları müşteriye dönüştürmeniz gerekir.';
$lang['appointment_external_contact_converted_to_client'] = 'Harici kişi başarıyla müşteriye dönüştürüldü';
$lang['appointment_external_contact_convert_to_client_error'] = 'Harici kişiyi müşteriye dönüştürürken hata oluştu';
$lang['appointment_convert_to_customer_first'] = 'Önce Müşteriye Dönüştür';
$lang['appointment_convert_lead_to_customer'] = 'Potansiyel Müşteriyi Müşteriye Dönüştür';
$lang['appointment_convert_external_to_customer'] = 'Müşteriye Dönüştür';
$lang['appointment_convert_only'] = 'Sadece Dönüştür';
$lang['appointment_convert_and_view'] = 'Dönüştür ve Görüntüle';
$lang['appointment_convert_to_invoice_only_contacts_allowed'] = 'Yalnızca kişilerle yapılan dahili randevular faturaya dönüştürülebilir';

// Lead Conversion
$lang['appointments_convert_to_lead'] = 'Randevuyu Potansiyel Müşteriye Dönüştür';
$lang['appointments_convert_to_lead_label'] = 'Potansiyel müşteriye dönüştür';
$lang['appointments_create_task_tooltip'] = 'Göreve dönüştür';
$lang['appointments_convert_to_lead_tooltip'] = 'Potansiyel müşteriye dönüştür';
$lang['appointments_select_option'] = 'Seçenek seç';
$lang['appointments_contact_name_task'] = 'Randevu: Kişi adı';

// Reports Dashboard
$lang['appointly_reports_dashboard'] = 'Appointly Raporlar Paneli';
$lang['appointly_date_range'] = 'Tarih Aralğı';
$lang['appointly_today'] = 'Bugün';
$lang['appointly_this_week'] = 'Bu Hafta';
$lang['appointly_this_month'] = 'Bu Ay';
$lang['appointly_this_year'] = 'Bu Yıl';
$lang['appointly_last_month'] = 'Geçen Ay';
$lang['appointly_last_year'] = 'Geçen Yıl';
$lang['appointly_last_30_days'] = 'Son 30 Gün';
$lang['appointly_custom_range'] = 'Özel Aralık';
$lang['appointly_period_from'] = 'Dönemden';
$lang['appointly_period_to'] = 'Döneme';
$lang['appointly_date_range_required'] = 'Lütfen geçerli bir tarih aralığı seçin';
$lang['appointly_apply'] = 'Uygula';
$lang['appointly_total_appointments'] = 'Toplam Randevu';
$lang['appointly_completed_appointments'] = 'Tamamlanan Randevular';
$lang['appointly_cancelled_appointments'] = 'İptal Edilen Randevular';
$lang['appointly_monthly_stats'] = 'Aylık İstatistikler';
$lang['appointly_popular_services'] = 'Popüler Hizmetler';
$lang['appointly_staff_performance'] = 'Personel Performansı';
$lang['appointly_staff_member'] = 'Personel Üyesi';
$lang['appointly_appointment_total_appointments'] = 'Toplam Randevu';
$lang['appointly_appointment_completed_appointments'] = 'Tamamlanan Randevular';
$lang['appointly_appointment_cancelled_appointments'] = 'İptal Edilen Randevular';
$lang['appointly_appointment_completion_rate'] = 'Tamamlanma Oranı';
$lang['appointly_filter'] = 'Filtrele';
$lang['appointly_no_staff_performance_data'] = 'Seçilen dönem için personel performans verisi mevcut değil';
$lang['appointly_report'] = 'Rapor';
$lang['appointly_reports_updated_for_period'] = 'Raporlar şu dönem için güncellendi: %s - %s';
$lang['appointly_no_data_for_period'] = 'Seçilen dönem için veri mevcut değil';
$lang['appointly_invalid_date_format'] = 'Geçersiz tarih formatı. Lütfen tarih seçiciyi kullanarak tarihleri seçin';
$lang['appointly_loading_data'] = 'Veriler yükleniyor...';
$lang['appointly_error_refreshing_stats'] = 'İstatistikler yenilenirken hata oluştu. Lütfen tekrar deneyin.';
$lang['appointly_no_data_found'] = 'Veri bulunamadı';

// Analytics Dashboard
$lang['total_appointments'] = 'Toplam Randevu';
$lang['completed_appointments'] = 'Tamamlanan Randevular';
$lang['cancelled_appointments'] = 'İptal Edilen Randevular';
$lang['monthly_statistics'] = 'Aylık İstatistikler';
$lang['popular_services'] = 'Popüler Hizmetler';
$lang['staff_performance'] = 'Personel Performansı';
$lang['staff_member'] = 'Personel Üyesi';
$lang['completion_rate'] = 'Tamamlanma Oranı';
$lang['from_date'] = 'Başlangıç Tarihi';
$lang['to_date'] = 'Bitiş Tarihi';
$lang['appointment_statistics_total'] = 'Toplam Randevu';
$lang['appointment_statistics_completed'] = 'Tamamlanan Randevular';
$lang['appointment_statistics_cancelled'] = 'İptal Edilen Randevular';

$lang['appointment_services_to_show_in_booking_form'] = 'Genel rezervasyon formunda hangi hizmetlerin gösterileceğini seçin';
$lang['appointment_services_select_all_to_show_all'] = 'Tüm aktif hizmetleri göstermek için boş bırakın';
$lang['appointment_select_attendees'] = 'Katılımcılar';
$lang['appointment_select_attendees_help'] = 'Bu randevuya katılacak ek personel üyelerini seçin';
$lang['appointment_related'] = 'İlgili';

// Service Availability Settings
$lang['services_availability_updated_successfully'] = 'Hizmet kullanılabilirliği başarıyla güncellendi';
$lang['services_availability_update_failed'] = 'Hizmet kullanılabilirliği güncellenemedi';
$lang['appointment_error_loading_providers'] = 'Sağlayıcılar yüklenirken hata oluştu';
$lang['appointment_select_service_warning'] = 'Lütfen bir hizmet seçin';
$lang['appointment_select_provider_warning'] = 'Lütfen bir sağlayıcı seçin';
$lang['appointment_select_date_time_warning'] = 'Lütfen bir tarih ve saat seçin';
$lang['appointment_loading_providers'] = 'Sağlayıcılar yükleniyor...';
$lang['appointment_select_date'] = 'Tarih Seç';
$lang['appointment_your_details'] = 'Detaylarınız';
$lang['appointment_continue'] = 'İleri';
$lang['appointment_back'] = 'Geri';
$lang['appointment_summary'] = 'Randevu Özeti';
$lang['appointment_view_details'] = 'Detayları Görüntüle';
$lang['appointment_select'] = 'Seç';
$lang['error_loading_data'] = 'Veriler yüklenirken hata oluştu';
$lang['appointment_booking_failed'] = 'Randevu rezervasyonu başarısız oldu. Lütfen tekrar deneyin.';
$lang['appointment_successfully_scheduled'] = 'Randevu Başarıyla Planlandı';
$lang['appointment_successfully_scheduled_message'] = 'Teşekkür ederiz! Randevunuz başarıyla planlandı.';
$lang['appointment_successfully_scheduled_button'] = 'Harika!';
$lang['appointment_schedule_another'] = 'Başka Bir Randevu Planla';
$lang['appointment_booking_confirmed'] = 'Teşekkür ederiz! Randevunuz başarıyla planlandı.';
$lang['appointment_pending_approval_message'] = 'Randevunuz personel onayını bekliyor. Onaylandığında size bildirilecektir.';
$lang['appointment_whats_next'] = 'Sırada Ne Var?';
$lang['appointment_staff_review'] = 'Personelimiz isteğinizi inceliyor. Lütfen onay bekleyin.';
$lang['appointment_email_confirmation'] = 'Tüm randevu detaylarını içeren bir e-posta onayı kısa süre içinde alacaksınız.';
$lang['appointment_prepare'] = 'Lütfen randevunuzdan önce gerekli tüm belge veya detayları hazırlayın.';
$lang['appointment_files'] = 'Dosyalar';
$lang['appointment_externally_booked_info'] = 'Bu randevu harici rezervasyon formu aracılığıyla rezerve edildi';
$lang['appointment_booked_from_external_booking_form'] = 'Rezervasyon Kaynağı';
$lang['appointment_subject_required'] = 'Randevu için konu zorunludur';
$lang['appointment_attendees_required'] = 'Randevuya en az bir personel üyesi katılmalıdır';
$lang['appointment_name_required'] = 'Harici randevular için isim zorunludur';
$lang['appointment_email_required'] = 'Harici randevular için e-posta zorunludur';
$lang['appointment_email_invalid'] = 'Lütfen geçerli bir e-posta adresi girin';
$lang['appointment_contact_required'] = 'Dahili randevu için lütfen bir kişi seçin';
$lang['appointment_invalid_type'] = 'Geçersiz randevu türü seçildi';
$lang['appointment_type_required'] = 'Lütfen bir randevu türü seçin';
$lang['appointment_invalid_data'] = 'Geçersiz randevu verileri sağlandı';
$lang['appointment_could_not_be_created'] = 'Randevu oluşturulamadı. Lütfen tekrar deneyin.';
$lang['appointment_unavailable_slots'] = 'Kırmızı slotlar mevcut randevular nedeniyle kullanılamaz';
$lang['appointment_book_now_description'] = 'Randevunuzu şimdi ayırtın ve ekibimizden en iyi hizmeti alın';
$lang['appointment_book_now_button_description'] = 'Randevunuzu şimdi ayırtın ve ekibimizden en iyi hizmeti alın';
$lang['appointment_feature_coming_soon'] = 'Bu özellik gelecek güncellemelerde mevcut olacak. Takipte kalın!';
$lang['appointment_description_updated'] = 'Randevu açıklaması başarıyla güncellendi';
$lang['appointment_notes_updated'] = 'Randevu notları başarıyla güncellendi';
$lang['appointment_notes_update_failed'] = 'Randevu notları güncellenemedi';
$lang['appointment_update_failed'] = 'Randevu güncellenemedi';
$lang['appointment_description_edit_info'] = 'Açıklamayı düzenlemek için tıklayın. Düzenlemeyi bitirdiğinizde değişiklikler otomatik olarak kaydedilecektir.';
$lang['appointment_viewing_notes'] = 'Randevu notları görüntüleniyor';
$lang['appointment_edit_history_notes'] = 'Notları Düzenle';
$lang['appointment_email_missing'] = 'Bu randevu için e-posta adresi eksik veya geçersiz';
$lang['appointment_no_name_provided'] = 'İsim belirtilmedi';
$lang['appointment_no_phone_provided'] = 'Telefon belirtilmedi';
$lang['appointment_open_link'] = 'Bağlantıyı Aç';
$lang['appointment_menu_form_link'] = 'Rezervasyon Formu';
$lang['external_form_heading'] = 'Rezervasyon Formu Başlığı';
$lang['external_form_description'] = 'Rezervasyon Formu Açıklaması';
$lang['appointment_date_location'] = 'Oturum Detayları';
$lang['appointment_schedule_description'] = 'Ekibimizle görüşmenizi ayarlamak için aşağıdaki formu doldurun';
$lang['appointment_preferred_date_time'] = 'Tercih Edilen Tarih ve Saat';
$lang['appointment_select_date_time'] = 'Seç...';
$lang['appointment_terms_description'] = 'Devam ederek, şunları kabul ettiğinizi onaylarsınız:';
$lang['appointment_terms_link'] = 'Şartlar ve Koşullar';
$lang['appointment_accept_terms'] = 'Hizmet Şartlarını kabul ediyorum ve onaylıyorum*';
$lang['appointly_recaptcha_enabled'] = 'Recaptcha\'yı Etkinleştir';
$lang['appointment_no_location_provided'] = 'Google Haritalar\'ı oluşturmak için konum belirtilmedi';
$lang['appointment_imported_from_calendar'] = '%s adresinden içe aktarıldı';
$lang['appointment_imported_cant_edit_notice'] = 'Bu randevu harici bir takvimden içe aktarıldı ve düzenlenemez.';
$lang['appointment_are_you_sure'] = 'Bu randevuyu silmek istediğinizden emin misiniz?';
$lang['would_you_like_to_create_new_appointment_for_lead'] = 'Bu potansiyel müşteri için yeni bir randevu oluşturmak ister misiniz?';
$lang['would_you_like_to_create_new_appointment_for_client'] = 'Bu müşteri için yeni bir randevu oluşturmak ister misiniz?';
$lang['would_you_like_to_create_new_appointment'] = 'Yeni bir randevu oluşturmak ister misiniz?';
$lang['no_appointments_found'] = 'Randevu bulunamadı';

// ----------------------
// EXTERNAL BOOKING FORM
// ----------------------
$lang['appointment_available_days'] = 'Mevcut';
$lang['appointment_busy_days'] = 'Meşgul (randevuları var)';
$lang['appointment_provider_unavailable'] = 'Sağlayıcı müsait değil';
$lang['appointment_blocked_days'] = 'Şirket Tatili/Engellenen Tarih';
$lang['appointment_date_required'] = 'Lütfen önce bir tarih seçin';
$lang['appointment_select_time'] = 'Bir saat seçin';
$lang['appointment_book_now'] = 'Şimdi Rezervasyon Yap';
$lang['appointment_submitting'] = 'Randevu Rezerve Ediliyor...';

// New appointment filter options
$lang['appointment_today'] = 'Bugünkü Randevular';
$lang['appointment_tomorrow'] = 'Yarınki Randevular';
$lang['appointment_this_week'] = 'Bu Haftaki Randevular';
$lang['appointment_next_week'] = 'Gelecek Haftaki Randevular';
$lang['appointment_this_month'] = 'Bu Ayki Randevular';
$lang['appointment_my_appointments'] = 'Randevularım';
$lang['appointment_assigned_to_me'] = 'Bana Atananlar';


// ----------------------
// GENERAL/COMMON TERMS
// ----------------------
$lang['appointment_yes'] = 'Evet';
$lang['appointment_no'] = 'Hayır';
$lang['appointment_appointments'] = 'Randevular';
$lang['appointment_label'] = 'Randevu';
$lang['wait_text'] = 'Lütfen bekleyin...';
$lang['loading_text'] = 'Yükleniyor, lütfen bekleyin...';
$lang['appointment_loading'] = 'Yükleniyor...';
$lang['unknown_error'] = 'Bilinmeyen hata';
$lang['request_failed'] = 'İstek başarısız oldu';
$lang['error_processing_response'] = 'Yanıt işlenirken hata oluştu';
$lang['invalid_appointment_id'] = 'Geçersiz randevu kimliği';
$lang['appointment_select_option'] = 'Seçenek Seç';
$lang['appointly_note'] = 'Not';
$lang['appointment_optional'] = '<small> (isteğe bağlı) </small>';
$lang['required_field_missing'] = 'Alan zorunludur';
$lang['appointly_required_field'] = 'Zorunlu alan';
$lang['settings_updated'] = 'Ayarlar başarıyla güncellendi';

// Time and date terms
$lang['timezone'] = 'Saat Dilimi';
$lang['minutes'] = 'Dakika';
$lang['hours'] = 'Saat';
$lang['monday'] = 'Pazartesi';
$lang['tuesday'] = 'Salı';
$lang['wednesday'] = 'Çarşamba';
$lang['thursday'] = 'Perşembe';
$lang['friday'] = 'Cuma';
$lang['saturday'] = 'Cumartesi';
$lang['sunday'] = 'Pazar';
$lang['today'] = 'Bugün';
$lang['this_week'] = 'Bu Hafta';
$lang['this_month'] = 'Bu Ay';
$lang['this_year'] = 'Bu Yıl';
$lang['date_range'] = 'Tarih Aralığı';
$lang['custom_range'] = 'Özel Aralık';
$lang['filter'] = 'Filtrele';

// ----------------------
// APPOINTMENT BASICS
// ----------------------
$lang['appointment_back_to_appointments'] = 'Randevular';
$lang['appointment_create_new_appointment'] = 'Danışma Planla';
$lang['appointment_select_contact'] = 'Kişi Seç';
$lang['appointment_new_appointment'] = 'Randevu Oluştur';
$lang['appointment_edit_appointment'] = 'Randevuyu Güncelle';
$lang['appointment_save_changes_btn_label'] = 'Değişiklikleri Kaydet';
$lang['appointment_subject'] = 'Toplantı Amacı';
$lang['appointment_description'] = 'Oturum Özeti';
$lang['appointment_overview'] = 'Randevu Özeti';
$lang['appointment_additional_info'] = 'Ek Bilgiler';
$lang['appointment_date'] = 'Tarih';
$lang['appointment_time'] = 'Tercih Edilen Saat';
$lang['appointment_date_and_time'] = 'Tarih / Saat';
$lang['appointment_date_time'] = 'Tarih ve Saat';
$lang['appointment_meeting_date'] = 'Randevu Tarihi';
$lang['appointment_meeting_time'] = 'Randevu Tarihi';
$lang['appointments_reminders_label'] = 'Hatırlatıcılar';
$lang['appointment_meeting_location'] = 'Konum';
$lang['appointment_location'] = 'Konum';
$lang['appointment_location_address'] = 'Konum / Adres';
$lang['appointment_location_placeholder'] = 'Konum detaylarını girin';
$lang['appointment_duration'] = 'Randevu Süresi';
$lang['appointment_duration_label'] = 'Süre';
$lang['appointment_duration_help'] = 'Randevu süresini dakika cinsinden ayarlayın';
$lang['appointment_notes'] = 'Notlar';
$lang['appointment_private_notes'] = 'Özel Notlar';
$lang['appointment_client_notes'] = 'Randevu notları';
$lang['appointment_created_by'] = 'Oluşturan';
$lang['appointly_created_at'] = 'Oluşturulma Tarihi';
$lang['appointment_schedule_details'] = 'Randevu Planı Detayları';
$lang['appointment_additional_settings'] = 'Ek Ayarlar';

// Appointment Status
$lang['appointment_status'] = 'Durum';
$lang['appointment_status_text'] = 'Randevu Durumu';
$lang['appointment_status_pending'] = 'Beklemede';
$lang['appointment_status_in-progress'] = 'Devam Ediyor';
$lang['appointment_status_completed'] = 'Tamamlandı';
$lang['appointment_status_cancelled'] = 'İptal Edildi';
$lang['appointment_status_no-show'] = 'Gelmedi';
$lang['appointment_upcoming'] = 'Yaklaşan';
$lang['appointment_finished'] = 'Bitti';
$lang['appointment_ongoing'] = 'Devam ediyor';
$lang['appointment_cancelled'] = 'İptal edildi';
$lang['appointment_rescheduled'] = 'Yeniden Planlandı';
$lang['appointment_no_show'] = 'Gelmedi';
$lang['appointment_missed_label'] = 'Kaçırıldı';
$lang['appointment_pending_approval'] = 'Onay Bekliyor';
$lang['appointment_not_approved'] = 'Onay Bekliyor';
$lang['appointment_pending_cancellation'] = 'İptal Bekliyor';
$lang['appointment_cancelled_text'] = 'Randevu İptal Edildi';
$lang['appointment_missed'] = 'Randevu Kaçırıldı (Randevu Tarihi/Saati geçmişte kaldı)';
$lang['appointment_are_you_sure_mark_as_no_show'] = 'Bu randevuyu gelmedi olarak işaretlemek istediğinizden emin misiniz?';
$lang['appointment_completed'] = 'Tamamlandı';
$lang['appointment_internal'] = 'Dahili';
$lang['appointment_external'] = 'Harici';
$lang['appointment_lead_related'] = 'Potansiyel Müşteri';
$lang['appointment_internal_staff'] = 'Personel';

// Status and Action Buttons
$lang['appointment_approve'] = 'Onayla';
$lang['appointment_approved'] = 'Onaylandı';
$lang['appointment_mark_as_finished'] = 'Tamamlandı olarak işaretle';
$lang['appointment_mark_as_ongoing'] = 'Devam ediyor olarak işaretle';
$lang['appointment_mark_as_cancelled'] = 'İptal edildi';
$lang['appointment_mark_as_rescheduled'] = 'Yeniden planlandı olarak işaretle';
$lang['appointment_mark_as_no_show'] = 'Gelmedi olarak işaretle';
$lang['appointment_cancel'] = 'Randevuyu İptal Et';
$lang['appointment_description_to_cancel'] = 'Lütfen bu randevuyu neden iptal etmek istediğinizi açıklayın';
$lang['appointment_describe_reason_for_cancel'] = 'Açıklama zorunludur. Lütfen randevuyu iptal etme nedeninizi açıklayın?';
$lang['appointment_request_to_cancel'] = 'İptal Talebi';
$lang['appointment_request_cancellation'] = 'İptal Talebi';
$lang['appointment_approve_cancellation'] = 'İptali Onayla';
$lang['appointment_marked_as_no_show'] = 'Gelmedi olarak işaretlendi';
$lang['appointly_are_you_sure_mark_as_no_show'] = 'Bu randevuyu gelmedi olarak işaretlemek istediğinizden emin misiniz?';

$lang['appointment_you_have_new_appointment'] = 'Yeni bir randevunuz var';
$lang['appointment_initiated_by'] = 'Düzenleyen';
$lang['appointment_select_single_contact'] = 'Kişi';
$lang['appointment_deleted'] = 'Randevu başarıyla silindi';
$lang['appointment_created'] = 'Yeni randevu başarıyla oluşturuldu';
$lang['appointment_updated'] = 'Randevu başarıyla güncellendi';
$lang['appointment_appointment_approved'] = 'Randevu başarıyla onaylandı!';
$lang['appointment_no_appointments'] = 'Bugün için randevunuz yok';
$lang['appointment_please_wait'] = 'Lütfen bekleyin...';
$lang['appointment_no_assigned_staff_found'] = 'Bu randevu için atanmış personel bulunamadı';
$lang['appointment_cancel_notification'] = 'Müşteri randevu iptali talep etti';
$lang['appointment_marked_as_finished'] = 'Randevu tamamlandı olarak işaretlendi';
$lang['appointment_todays_appointments'] = 'Bugünkü randevular';
$lang['appointment_scheduled_at'] = 'Planlanan saat:';
$lang['appointment_view_meeting'] = 'Randevuyu Görüntüle';
$lang['appointment_edit_meeting'] = 'Randevuyu Düzenle';
$lang['appointment_dismiss_meeting'] = 'Randevuyu Sil';
$lang['appointment_not_exists'] = 'Randevu bulunamadı, randevu listesine yönlendiriliyor';
$lang['appointment_marked_as_ongoing'] = 'Randevu devam ediyor olarak işaretlendi';
$lang['appointment_general_info'] = 'Müşteri Detayları';
$lang['appointment_general_details'] = 'Randevu Detayları';
$lang['appointment_source'] = 'Tip';
$lang['appointment_source_external_text'] = 'Harici (Kişi)';
$lang['appointment_source_external'] = 'Kaynak (Harici Kişi)';
$lang['appointment_source_external_contact'] = 'Harici Kişi';
$lang['appointment_source_internal'] = 'Dahili (Kişi)';
$lang['appointment_lead_required'] = 'Randevu için lütfen bir potansiyel müşteri seçin';
$lang['appointment_source_internal_client'] = 'Dahili (Müşteri)';
$lang['appointment_source_internal_staff'] = 'Dahili (Personel)';
$lang['appointment_source_lead'] = 'Potansiyel Müşteri';
$lang['appointment_staff_only'] = 'Sadece Personel';
$lang['appointment_phone'] = 'Telefon';
$lang['appointment_name'] = 'İsim';
$lang['appointment_email'] = 'E-posta';
$lang['appointment_contact'] = 'Müşteri Detayları';
$lang['appointment_sent_successfully'] = 'Yeni randevu talebiniz başarıyla gönderildi, randevunuz onaylandığında e-posta ile bilgilendirileceksiniz';
$lang['appointment_squeduled_at_text'] = 'Randevu şu saatte başlayacak şekilde planlandı:';
$lang['appointment_staff_attendees'] = 'Katılımcılar';
$lang['appointment_is_approved'] = 'Randevu onaylandı!';
$lang['appointment_public_url'] = 'Herkese Açık URL';
$lang['appointment_is_cancelled'] = 'Randevu iptal edildi!';
$lang['appointment_cancel_notes'] = 'İptal Notları';
$lang['appointment_full_name'] = 'Müşteri Adı';
$lang['appointment_your_email'] = 'E-postanız';
$lang['appointment_your_phone'] = 'Telefon (ülke kodu ile)';
$lang['appointment_your_phone_example'] = '+1 69 1234 5678';
$lang['appointment_submit'] = 'Gönder';
$lang['appointment_no_staff_members'] = 'Personel üyesi bulunamadı, iFrame formu aracılığıyla gönderilen yeni randevular için bir kişi seçmek üzere personel üyesi eklemeli ve bu görünümü yeniden yüklemelisiniz.';
$lang['appointment_cancellation_description_label'] = 'İptal Nedeni';
$lang['appointments_thank_you_cancel_request'] = 'İptal talebiniz için teşekkür ederiz. Kısa süre içinde inceleyeceğiz.';
$lang['appointments_already_applied_for_cancelling'] = 'Bu randevuyu iptal etmek için zaten başvurdunuz.';
$lang['appointment_pending_cancellations'] = 'Bekleyen İptal Talepleri';
$lang['appointment_requested_by'] = 'Talep Eden';
$lang['appointment_cancellation_approved'] = 'İptal talebi başarıyla onaylandı';
$lang['appointly_schedule_new_appointment'] = 'Randevu Planla';
$lang['appointments_total_found'] = 'Toplam randevu';
$lang['appointments_admin_label'] = 'Yönetici';
$lang['appointments_staff_label'] = 'Personel';
$lang['appointments_no_delete_permissions'] = 'Bu randevu sizin tarafınızdan oluşturulmadı, silinemez';
$lang['appointment_source_external_clients_area'] = 'Kaynak (Müşteri alanından mevcut kişi)';
$lang['appointments_source_external_label'] = 'Harici';
$lang['appointments_source_internal_label'] = 'Dahili';
$lang['appointments_individual_contact'] = ' (Bireysel Kişi)';
$lang['appointments_company_for_select'] = ' - Müşteri ';
$lang['appointment_preview_url_label'] = 'Önizleme';
$lang['appointment_booking_form_services'] = 'Rezervasyon Formu Hizmetleri';
$lang['appointment_source_leads_label'] = 'Potansiyel Müşteriler';
$lang['appointment_connect'] = 'Bağlan';
$lang['appointment_connected'] = 'Bağlandı';
$lang['appointments_outlook_revoke_confirm'] = 'Outlook\'tan Çıkış Yap';
$lang['appointment_selected_service'] = 'Seçilen Hizmet';
$lang['appointment_please_enter_your_details'] = 'Lütfen detaylarınızı girin';
$lang['appointments_request_feedback_from_client'] = 'Müşteriden geri bildirim iste';
$lang['appointments_request_feedback'] = 'Geri bildirim iste';
$lang['appointment_feedback_label'] = 'Geri Bildirim';
$lang['appointment_view_feedback'] = 'Geri Bildirimi Görüntüle';
$lang['appointment_feedback_label_added'] = 'Geri bildiriminiz için teşekkür ederiz!';
$lang['appointment_feedback_label_current'] = 'Mevcut geri bildiriminiz!';
$lang['appointments_feedback_info'] = 'Varsayılan geri bildirim durumlarınızı yönetin';
$lang['ap_feedback_extremely_good'] = 'Son Derece İyi';
$lang['ap_feedback_very_good'] = 'Çok İyi';
$lang['ap_feedback_good'] = 'İyi';
$lang['ap_feedback_not_bad'] = 'Fena Değil';
$lang['ap_feedback_bad'] = 'Kötü';
$lang['ap_feedback_the_worst'] = 'En Kötü';
$lang['ap_feedback_not_sure'] = 'Emin Değil';
$lang['appointment_feedback_title'] = 'Bu randevu için geri bildiriminizi bırakın';
$lang['appointmenet_feedback_comment'] = 'Bu randevu hakkındaki yorumlarınız ve düşünceleriniz: ';
$lang['appointment_feedback_comment_textarea'] = 'Bu randevuyla ilgili deneyiminizi açıklamak için en az birkaç kelime gereklidir';
$lang['appointment_feedback_comment_textarea_info'] = 'Lütfen bu randevuyla ilgili deneyiminizi açıklayın';
$lang['appointment_new_feedback_added'] = 'Bir randevu için yeni bir geri bildiriminiz var';
$lang['appointly_feedback_updated'] = 'Geri bildirim derecelendirmesi az önce güncellendi';
$lang['appointment_email_tracking'] = 'E-posta takibi (randevu e-postası okundu mu)';
$lang['appointment_feedback_requested_alert'] = 'Geri bildirim başarıyla talep edildi, geri bildirim verildiğinde e-posta ile bilgilendirileceksiniz!';
$lang['appointment_click_to_change_rating'] = 'Derecelendirmenizi güncellemek için yıldızlara tıklayın';
$lang['appointment_staff_cant_provide_feedback'] = 'Personel üyeleri randevular için geri bildirim sağlayamaz';
$lang['appointment_leave_feedback'] = 'Bu randevuyla ilgili deneyiminizi derecelendirin';
$lang['appointment_your_feedback'] = 'Geri Bildiriminiz';
$lang['appointment_no_feedback_provided'] = 'Müşteri bu randevu için henüz geri bildirim sağlamadı';
$lang['appointments_are_you_sure_request_feedback'] = 'Bu randevu için geri bildirim talep etmek istediğinizden emin misiniz? Müşteriye bir e-posta gönderilecektir.';

// Client Area
$lang['appointly_allow_non_logged_clients_appointment'] = 'Giriş yapmamış müşterilerin harici rezervasyon formu aracılığıyla yeni randevu talep etmesine izin ver';
$lang['appointly_show_appointments_menu_item_in_clients_area'] = 'Müşteri alanında (giriş yapıldığında) randevu talep menü öğesini göster';
$lang['appointments_applies_for_clients'] = '(yalnızca müşteriler için geçerlidir)';

// ----------------------
// TIME SLOTS & AVAILABILITY
// ----------------------
$lang['appointly_no_providers_for_service'] = 'Bu hizmet için sağlayıcı bulunamadı';
$lang['appointly_no_providers_with_hours'] = 'Bu hizmet için uygun çalışma saatleri olan sağlayıcı bulunamadı';
$lang['appointly_select_staff'] = 'Personel Üyesi Seç';
$lang['appointment_busy_hours'] = 'Meşgul Saatler';
$lang['appointment_available_hours'] = 'Müsait Saatler';
$lang['appointment_meeting_hour_is_reserved'] = 'Randevu Saati zaten rezerve edildi';
$lang['appointment_requested_hour'] = 'Talep edilen toplantı saati';
$lang['appointment_time_unavailable'] = 'Bu zaman dilimi kullanılamıyor';
$lang['appointment_date_blocked'] = 'Bu tarih yönetici tarafından engellendi';
$lang['appointly_available_time_slots'] = 'Müsait Zaman Dilimleri';
$lang['appointment_available_time_slots'] = 'Müsait Zaman Dilimleri';
$lang['appointment_no_slots_available'] = 'Bu gün için müsait zaman dilimi yok';
$lang['appointment_slot_already_booked'] = 'Bu zaman dilimi zaten rezerve edildi';
$lang['appointment_unavailable_slots_shown'] = 'Müsait olmayan zaman dilimleri kırmızı renkte gösterilir ve seçilemez';
$lang['appointment_all_slots_booked'] = 'Bu gün için tüm zaman dilimleri rezerve edildi. Lütfen başka bir tarih deneyin.';
$lang['appointment_slot_unavailable'] = 'Bu zaman dilimi kullanılamıyor';
$lang['appointment_checking_availability'] = 'Müsaitlik kontrol ediliyor...';
$lang['appointment_checking_time_slots'] = 'Zaman dilimleri yükleniyor...';
$lang['appointment_error_loading_slots'] = 'Zaman dilimleri yüklenirken hata oluştu. Lütfen tekrar deneyin.';
$lang['appointment_not_available'] = 'Müsait değil';
$lang['appointment_available'] = 'Müsait';

// Schedule and calendar
$lang['appointment_recurring'] = 'Tekrarlayan';
$lang['appointment_recurring_re_created'] = 'Tekrarlayan randevu yeniden oluşturuldu';
$lang['appointments_all_day_event'] = 'Tüm gün etkinliği';
$lang['select_blocked_days'] = 'Günleri seç';
$lang['appointments_blocked_days_on_calendar_title'] = 'Engellenen Günler<br><small class="text-muted">Randevuların planlanamayacağı tarihleri seçin (tatiller, şirket kapanışları vb.).<br> Bu tarihler hem dahili hem de harici rezervasyonlar için kullanılamayacaktır.</small>';
$lang['appointments_dates_blocked_info_text'] = 'Seçilen tarihler rezervasyon takviminde kullanılamayacaktır. Bu tarihlerde dahili veya harici olarak toplantı planlanamaz.';
$lang['appointments_blocked_days_tab_title'] = 'Çalışma dışı günler';

// Working Hours and Schedules
$lang['appointments_default_hours_label'] = 'Varsayılan randevu saatlerinizi yönetin';
$lang['appointly_company_schedule'] = 'Şirket Takvimi';
$lang['appointly_company_schedule_info'] = 'Şirketinizin varsayılan çalışma saatlerini yapılandırın. Bu saatler, kendi özel çalışma saatlerini ayarlamadıkları sürece tüm personel üyeleri için kullanılacaktır.';
$lang['appointly_manage_company_schedule'] = 'Şirket Takvimini Yönet';
$lang['appointly_staff_working_hours'] = 'Personel Çalışma Saatleri';
$lang['appointly_staff_working_hours_info'] = 'Bu personel üyesi için çalışma saatlerini yapılandırın. Bu saatler, bu personel üyesi sağlayıcı olarak seçildiğinde şirket takvimini geçersiz kılacaktır.';
$lang['appointly_view_staff_schedule'] = 'Personel Takvimini Görüntüle';
$lang['appointly_day'] = 'Gün';
$lang['appointly_enabled'] = 'Etkin';
$lang['appointly_available'] = 'Müsait';
$lang['appointly_start_time'] = 'Başlangıç Saati';
$lang['appointly_end_time'] = 'Bitiş Saati';
$lang['appointly_use_company_schedule'] = 'Şirket Takvimini Kullan';
$lang['appointly_use_company_schedule_tooltip'] = 'Özel ayarlar yerine bu gün için şirket takvimi ayarlarını kullanmak için işaretleyin.';
$lang['appointly_day_monday'] = 'Pazartesi';
$lang['appointly_day_tuesday'] = 'Salı';
$lang['appointly_day_wednesday'] = 'Çarşamba';
$lang['appointly_day_thursday'] = 'Perşembe';
$lang['appointly_day_friday'] = 'Cuma';
$lang['appointly_day_saturday'] = 'Cumartesi';
$lang['appointly_day_sunday'] = 'Pazar';
$lang['appointly_at_least_one_day_required'] = 'En az bir gün etkinleştirilmelidir';
$lang['appointly_no_working_hours_found'] = 'Bu sağlayıcı için çalışma saati ayarlanmadı';
$lang['appointly_closed'] = 'Kapalı';
$lang['appointly_working_hours'] = 'Çalışma Saatleri';
$lang['company_schedule_time_intervals_note'] = 'Saatler yalnızca 15 dakikalık aralıklarla ayarlanabilir (örn. 09:00, 09:15, 09:30, 09:45)';
$lang['working_hours_time_intervals_note'] = 'Zaman dilimleri 15 dakikalık aralıklarla mevcuttur';
$lang['appointly_time_error'] = 'Başlangıç saati bitiş saatinden önce olmalıdır:';
$lang['appointly_company_schedule_sync_help'] = 'Personel, saatlerini şirket varsayılanlarıyla senkronize etmek için "Şirket Takvimini Kullan" seçeneğini kullanabilir.';

// Buffer settings
$lang['appointly_settings_buffer_times'] = 'Randevular arasında tampon sürelerini etkinleştir';
$lang['appointly_settings_buffer_times_info'] = 'Tampon süreleri, randevular arasında geçiş sürelerine izin verir';
$lang['appointly_buffer_before'] = 'Önce Tampon (dakika)';
$lang['appointly_buffer_after'] = 'Sonra Tampon (dakika)';
$lang['appointly_buffer_before_help'] = 'Randevudan önce hazırlanmak için ek süre';
$lang['appointly_buffer_after_help'] = 'Randevudan sonra temizlik için ek süre';

// ----------------------
// SERVICES & PROVIDERS
// ----------------------
$lang['appointly_services'] = 'Hizmetler';
$lang['service'] = 'Hizmet';
$lang['appointment_service'] = 'Hizmet';
$lang['appointment_services'] = 'Hizmetler';
$lang['appointment_select_service'] = 'Hizmet Seç';
$lang['appointment_service_duration'] = 'Süre';
$lang['appointment_service_price'] = 'Fiyat';
$lang['appointment_service_description'] = 'Açıklama';
$lang['appointments_service_heading'] = 'Hizmet';
$lang['appointments_staff_heading'] = 'Personel';
$lang['service_selection_required'] = 'Lütfen bir hizmet seçin';
$lang['appointment_service_required'] = 'Randevu için lütfen bir hizmet seçin';
$lang['appointly_service_selection_label'] = 'Hizmet';
$lang['appointments_selected_service'] = 'Seçilen Hizmet';
$lang['no_services_available'] = 'Hizmet bulunamadı';

// Service Creation and Management
$lang['appointly_new_service'] = 'Yeni Hizmet';
$lang['appointly_edit_service'] = 'Hizmeti Düzenle';
$lang['appointly_service_add_success'] = 'Hizmet başarıyla eklendi';
$lang['appointly_service_edit_success'] = 'Hizmet başarıyla güncellendi';
$lang['appointly_service_delete_success'] = 'Hizmet başarıyla silindi';
$lang['appointly_service_delete_error'] = 'Hizmet silinemedi';
$lang['error_adding_service'] = 'Hizmet eklenemedi';
$lang['error_updating_service'] = 'Hizmet güncellenemedi';
$lang['service_delete_error_active'] = 'Hizmet aktif ve silinemez veya devre dışı bırakılamaz.';
$lang['appointly_service_in_use_warning'] = 'Bu hizmet şu anda bir veya daha fazla randevuda kullanılıyor ve silinemez veya devre dışı bırakılamaz.';

// Service Properties
$lang['service_availability_days'] = 'Müsait Günler';
$lang['service_hours_start'] = 'Çalışma Saatleri Başlangıç';
$lang['service_hours_end'] = 'Çalışma Saatleri Bitiş';
$lang['appointly_service_name'] = 'Ad';
$lang['appointly_service_name_label'] = 'Hizmet Adı';
$lang['appointly_service_duration'] = 'Süre';
$lang['appointly_service_price'] = 'Fiyat';
$lang['appointly_service_color'] = 'Renk';
$lang['appointly_service_description'] = 'Açıklama';
$lang['appointly_service_active'] = 'Aktif';
$lang['appointly_service_details'] = 'Hizmet Detayları';
$lang['appointly_service_back_to_list'] = 'Hizmet Listesine Geri Dön';
$lang['appointly_duration_validation'] = 'Süre 15 dakikalık aralıklarla (15, 30, 45 vb.) ve maksimum 480 dakika olmalıdır';
$lang['appointly_price_validation'] = 'Fiyat negatif olamaz';
$lang['appointly_duration_minutes'] = 'dakika';
$lang['appointly_service_staff'] = 'Sağlayıcıya/Personele Atandı';
// Service Validation
$lang['service_name_required'] = 'Hizmet adı zorunludur';
$lang['service_duration_required'] = 'Hizmet süresi zorunludur';
$lang['service_duration_numeric'] = 'Süre bir sayı olmalıdır';
$lang['service_duration_greater'] = 'Süre 0\'dan büyük olmalıdır';
$lang['service_price_required'] = 'Hizmet fiyatı zorunludur';
$lang['service_price_greater_equal'] = 'Fiyat 0 veya daha büyük olmalıdır';
$lang['service_days_required'] = 'Lütfen en az bir müsait gün seçin';
$lang['service_hours_required'] = 'Hizmet saatleri zorunludur';
$lang['service_hours_invalid'] = 'Geçersiz saat formatı';
$lang['service_hours_start_end'] = 'Bitiş saati başlangıç saatinden sonra olmalıdır';
$lang['appointly_staff_required'] = 'Lütfen bir personel üyesi seçin';
$lang['appointly_working_hours_required'] = '%s çalışma saatleri zorunludur.';
$lang['appointly_working_hours_invalid'] = '%s bitiş saati başlangıç saatinden sonra olmalıdır.';
$lang['appointly_working_hours_at_least_one'] = 'En az bir gün etkinleştirilmelidir.';

// Service Table Headers
$lang['service_th_name'] = 'Ad';
$lang['service_th_duration'] = 'Süre (dakika)';
$lang['service_th_price'] = 'Fiyat';
$lang['service_th_availability'] = 'Müsaitlik';
$lang['service_th_status'] = 'Durum';
$lang['service_th_options'] = 'Seçenekler';

// Service Status
$lang['service_status_active'] = 'Aktif';
$lang['service_status_inactive'] = 'Pasif';
$lang['service_status_changed_success'] = 'Hizmet durumu başarıyla güncellendi';
$lang['service_status_changed_error'] = 'Hizmet durumu güncellenemedi';
$lang['service_toggle_active'] = 'Aktif durumu değiştir';
$lang['error_updating_status'] = 'Durum güncellenirken hata oluştu. Lütfen tekrar deneyin.';
$lang['service_status_updated'] = 'Hizmet durumu başarıyla güncellendi';
$lang['service_status_update_failed'] = 'Hizmet durumu güncellenemedi';

// Providers
$lang['appointment_provider'] = 'Sağlayıcı';
$lang['appointly_provider'] = 'Sağlayıcı';
$lang['service_provider_loading'] = 'Müsait sağlayıcılar yükleniyor...';
$lang['service_no_providers'] = 'Bu hizmet için sağlayıcı bulunamadı';
$lang['service_provider_select'] = 'Sağlayıcı Seç';
$lang['appointment_select_provider'] = 'Sağlayıcı Seç';
$lang['appointly_select_provider'] = 'Sağlayıcı Seç';
$lang['appointly_no_staff'] = 'Müsait personel üyesi yok';
$lang['appointly_error_loading_schedule'] = 'Sağlayıcı takvimi yüklenirken hata oluştu';
$lang['appointment_no_provider_assigned'] = 'Sağlayıcı atanmadı';
$lang['appointly_meeting_location'] = 'Toplantı Konumu';

// Multiple Providers
$lang['appointly_settings_multi_providers'] = 'Hizmet başına birden fazla sağlayıcıyı etkinleştir';
$lang['appointly_settings_multi_providers_info'] = 'Hizmetlerin birden fazla personel üyesi tarafından sağlanmasına izin ver';
$lang['appointly_primary_provider'] = 'Birincil Sağlayıcı';
$lang['appointly_add_provider'] = 'Sağlayıcı Ekle';
$lang['appointly_remove_provider'] = 'Sağlayıcıyı Kaldır';
$lang['appointly_service_providers'] = 'Hizmet Sağlayıcıları';
$lang['appointly_confirm_provider_removal'] = 'Bu sağlayıcıyı kaldırmak istediğinizden emin misiniz?';
$lang['appointly_assigned_providers'] = 'Atanan Sağlayıcılar';
$lang['appointly_service_primary_provider'] = 'Birincil Sağlayıcı';
$lang['appointment_external_provider'] = 'Harici Sağlayıcı';

// ----------------------
// NOTIFICATIONS & REMINDERS
// ----------------------
$lang['appointment_modal_notification_info'] = 'Seçilen katılımcıların ve kişilerin hatırlatıcı almasını istiyorsanız lütfen onay kutularını işaretleyin, örn. randevu başlangıcından 30 dakika önce ayarlanmışsa. Bu özelliğin cron işinin yapılandırılmasını gerektirdiğini unutmayın.';
$lang['appointment_sms_notification_text'] = 'SMS Bildirimleri Gönder';
$lang['appointment_email_notification_text'] = 'E-posta Bildirimleri Gönder';
$lang['appointment_send_notification'] = 'Şimdi bildirim gönder';
$lang['appointment_notified'] = 'Randevu Hatırlatıcıları';
$lang['appointment_notified_by_sms'] = 'SMS ile hatırlatma bildirimi tetiklendi';
$lang['appointment_notified_by_email'] = 'E-posta ile hatırlatma bildirimi tetiklendi';
$lang['appointment_send_early_reminders_label'] = 'Erken Hatırlatıcılar Gönder';
$lang['appointly_are_you_early_reminders'] = 'Erken randevu hatırlatıcıları göndermek istediğinizden emin misiniz?';
$lang['appointly_reminders_sent'] = 'Randevu hatırlatıcıları tüm katılımcılara ve kişiye gönderildi';
$lang['appointment_manually_send_reminders_info'] = 'Tüm katılımcılara manuel olarak bildirim hatırlatıcıları gönder';
$lang['appointment_early_reminders_notice_label'] = 'Erken Hatırlatıcılar göndermek için Randevu İptal Edilmedi veya Tamamlanmadı';
$lang['appointment_email_read_at'] = 'Okunma tarihi: ';
$lang['appointment_email_not_read'] = 'Okunmadı';
$lang['appoontment_sms_notification'] = 'SMS Bildirimi';
$lang['appoontment_email_notification'] = 'E-posta Bildirimi';

// ----------------------
// CALENDAR INTEGRATIONS
// ----------------------
// Google Calendar
$lang['appointly_calendar_integrations'] = 'Takvim Entegrasyonları';
$lang['appointment_add_to_google_calendar'] = 'Google Takvim\'e Ekle';
$lang['appointments_google_already_signed'] = 'Zaten Google Hesabınızda oturum açtınız.';
$lang['appointments_added_to_google_calendar'] = 'Google Takvim\'e Eklendi';
$lang['appointments_sign_in_google'] = 'Google ile Oturum Aç';
$lang['appointments_google_revoke_confirm'] = 'Google\'dan Çıkış Yap';
$lang['appointments_google_revoke'] = 'Mevcut Google Takvim oturumunu iptal et ve Google hesabınıza verilen tüm izinleri kaldır.';
$lang['appointments_google_calendar_client_id'] = 'Google Takvim API İstemci Kimliği <strong>(Ayarlar->Google->API İSTEMCİ KİMLİĞİ\'nden alındı)</strong>';
$lang['appointments_google_calendar_settings'] = 'Google Takvim API Ayarları';
$lang['appointments_google_calendar_client_secret'] = 'Google Takvim API İstemci Sırrı';
$lang['appointments_redirect_url'] = 'Google Yetkilendirme yönlendirme URI\'si';
$lang['appointly_show_google_appointments_from'] = 'Randevuları tarih aralığına göre filtrele:';
$lang['appointments_delete_from_google_label'] = 'Randevu silinmeden önce Google Takviminizde oluşturulan randevuyu da silin <small>(Yalnızca Google Takvim etkin ve senkronize ise geçerlidir)</small>';
$lang['appointment_add_to_google_calendar_external'] = 'Bu harici randevuyu Google Takviminize ekleyin (İşaretleyin ve Kaydet\'e tıklayın)';
$lang['appointment_open_google_calendar'] = 'Google Takvim\'de Aç';
$lang['appointment_google_not_added_yet'] = 'Görünüşe göre bu randevu henüz hiçbir personel üyesinin Google Takvimine eklenmedi. Bu randevuyu Google Takviminize eklemek ister misiniz?';
$lang['appointment_add_to_calendar'] = 'Takvime Ekle';
$lang['appointment_view_in_calendar'] = 'Google Takvim\'de Görüntüle';
$lang['appointment_calendar_adding_to_google'] = 'Google Takvim\'e ekleniyor...';
$lang['appointment_error_adding_to_calendar'] = 'Takvime eklenemedi. Lütfen tekrar deneyin.';
$lang['event_not_found_in_google'] = 'Etkinlik Google Takvim\'de bulunamayabilir';
$lang['appointments_delete_from_google_calendar'] = 'Google Takvim\'den Sil';
$lang['appointments_synced_from_google'] = 'Google\'dan Senkronize Edildi';
$lang['appointments_googlesync_show_in_table_label'] = 'Google Takvim entegrasyonu aktifse, tüm Google Takvim randevularını varsayılan tablo görünümünde göster.';
$lang['appointly_google_synced_title'] = ' Google Senkronize Edildi';
$lang['appointment_hide_google_calendar'] = 'Varsayılan Görünümü Göster';
$lang['appointment_google_calendar_synced'] = 'Google Takvim Senkronize Edildi';
$lang['appointment_googlesync_only_today'] = 'Bugün';
$lang['appointment_googlesync_only_last_month'] = 'Geçen Ay';
$lang['appointment_googlesync_only_last_three_months'] = 'Son 3 Ay';
$lang['appointment_googlesync_only_last_six_months'] = 'Son 6 Ay';
$lang['appointment_googlesync_only_last_year'] = 'Geçen Yıl';
$lang['appointment_googlesync_show_all'] = 'Tümü';
$lang['appointly_not_including_two_way_synced_appointments'] = 'İki yönlü senkronize randevular dahil değildir';
$lang['appointment_external_calendar_event'] = 'Harici Takvim Etkinliği';

// Google Meet
$lang['appointment_google_meet_info'] = 'Bu randevu Google Takvim\'e eklendi, müşterilerinizle çevrimiçi görüşmek için Google Meet\'i kullanabilirsiniz';
$lang['appointment_google_meet_info_2'] = 'Bu randevu Google Takvim\'e eklendi';
$lang['appointment_google_client_meet_info'] = 'Google Meet aracılığıyla bağlan';
$lang['appointment_connect_via_google_meet'] = 'Google Meet aracılığıyla bağlanmak istiyorum';
$lang['appointment_meet_message'] = 'Merhaba<br><br>Benimle Google Meet aracılığıyla bağlanmak için lütfen bu URL\'yi takip edin: ';
$lang['appointment_meeting_request_sent'] = 'Toplantı isteği mesajınız başarıyla gönderildi';
$lang['appointment_leave_a_comment'] = 'Yorum bırakmak ister misiniz?';
$lang['appointment_google_meet_connect_message'] = 'Katılımcılara e-posta gönderin ve Google Meet aracılığıyla bağlanmalarını isteyin';
$lang['appointment_google_meet_modal_custom_label'] = 'Personeli ve müşterileri E-posta ile Google Meet\'e davet et';
$lang['appointment_google_meet'] = 'Google Meet';
$lang['appointment_google_calendar'] = 'Google Takvim';

// Enhanced Google Meet Settings
$lang['appointment_google_meet_enhanced_settings'] = 'Gelişmiş Google Meet Ayarları';
$lang['appointly_auto_enable_google_meet'] = 'Tüm randevular için Google Meet\'i otomatik olarak etkinleştir';
$lang['appointly_auto_enable_google_meet_help'] = 'Etkinleştirildiğinde, tüm yeni randevular Google Takvim ile senkronize edildiğinde otomatik olarak Google Meet bağlantıları içerecektir';
$lang['appointly_google_meet_default_settings'] = 'Varsayılan Google Meet Ayarları';
$lang['appointly_google_meet_enable_recording'] = 'Varsayılan olarak kaydı etkinleştir';
$lang['appointly_google_meet_enable_waiting_room'] = 'Varsayılan olarak bekleme odasını etkinleştir';
$lang['appointly_google_meet_reminder_minutes'] = 'Toplantıdan önceki varsayılan hatırlatma süresi';
$lang['appointly_google_meet_reminder_help'] = 'Google Meet randevuları için varsayılan hatırlatma süresini ayarlayın';
$lang['appointly_disable_google_meeting_emails'] = 'Google Takvim e-posta bildirimlerini devre dışı bırak';
$lang['appointly_disable_google_meeting_emails_help'] = 'Etkinleştirildiğinde, Google takvim etkinlikleri için otomatik e-posta bildirimleri göndermeyecektir';
$lang['appointly_minutes'] = 'dakika';
$lang['appointly_hour'] = 'saat';
$lang['appointly_hours'] = 'saat';
$lang['appointly_day'] = 'gün';

// Enhanced Google Meet Features
$lang['appointment_google_meet_join_before_start'] = 'Google Meet\'e Katıl';
$lang['appointment_google_meet_copy_link'] = 'Google Meet Bağlantısını Kopyala';
$lang['appointment_google_meet_link_copied'] = 'Google Meet bağlantısı panoya kopyalandı';
$lang['appointment_google_meet_test_connection'] = 'Google Meet Bağlantısını Test Et';
$lang['appointment_google_meet_connection_success'] = 'Google Meet bağlantı testi başarılı';
$lang['appointment_google_meet_connection_failed'] = 'Google Meet bağlantı testi başarısız oldu';
$lang['appointment_google_meet_quick_join'] = 'Hızlı Toplantıya Katıl';
$lang['appointment_google_meet_meeting_details'] = 'Toplantı Detayları';
$lang['appointment_google_meet_dial_in'] = 'Çevirmeli Bilgiler';
$lang['appointment_google_meet_share_screen'] = 'Ekran Paylaşımı Mevcut';
$lang['appointment_google_meet_record_meeting'] = 'Kayıt Mevcut';
$lang['appointment_google_meet_waiting_room_enabled'] = 'Bekleme Odası Etkin';
$lang['appointment_google_meet_testing_connection'] = 'Google Meet bağlantısı test ediliyor...';
$lang['appointment_google_meet_copy_failed'] = 'Bağlantı kopyalanamadı. Lütfen manuel olarak kopyalayın.';
$lang['appointment_google_meet_email_message'] = 'E-posta Mesajı';
$lang['appointment_google_meet_send_invitation'] = 'Google Meet Davetiyesi Gönder';
$lang['appointment_google_meet_message_required'] = 'Lütfen gönderilecek bir mesaj girin';
$lang['sending'] = 'Gönderiliyor...';
$lang['appointment_email_send_failed'] = 'E-posta gönderilemedi. Lütfen e-posta ayarlarınızı kontrol edin.';
$lang['appointment_google_meet_send_to'] = 'Gönderilen';
$lang['appointment_google_meet_primary_recipient'] = 'Birincil Alıcı';
$lang['appointment_google_meet_also_notify_attendees'] = 'Personel katılımcılarını da bilgilendir';
$lang['appointment_no_email_provided'] = 'E-posta adresi belirtilmedi';
$lang['appointment_google_meet_hd_video_audio'] = 'HD Video ve Ses';
$lang['appointment_google_meet_always_enabled'] = 'Her zaman etkin';
$lang['appointment_google_meet_recording_enabled'] = 'Kayıt Etkin';
$lang['appointment_google_meet_recording_disabled'] = 'Kayıt Devre Dışı';
$lang['appointment_google_meet_waiting_room_enabled_status'] = 'Bekleme Odası Etkin Durumda';
$lang['appointment_google_meet_waiting_room_disabled'] = 'Bekleme Odası Devre Dışı';
$lang['appointment_google_meet_quick_actions'] = 'Hızlı Eylemler';
$lang['appointment_google_meet_join_meeting'] = 'Google Meet\'e Katıl';
$lang['appointment_google_meet_send_invitation_btn'] = 'Davetiye Gönder';

// Google Maps
$lang['appointment_google_maps'] = 'Google Haritalar\'da Aç';
$lang['appointly_google_maps_not_shown'] = 'Google Haritalar gösterilmeyecektir.';
$lang['appointly_google_api_key_notset'] = 'Google API Anahtarı ayarlanmadı, lütfen randevu ayarlarında Google API Anahtarını ayarlayın';
$lang['appointly_message_will_hide'] = 'Bu mesaj 5 saniye içinde gizlenecektir';

// Outlook Calendar
$lang['appointment_login_to_outlook'] = 'Outlook\'a Giriş Yap';
$lang['appointment_logout_from_outlook'] = 'Outlook\'tan Çıkış Yap';
$lang['appointment_open_outlook_calendar'] = 'Outlook Takvim\'de Aç';
$lang['appointments_outlook_revoke'] = 'Mevcut Outlook Takvim oturumunu iptal et ve Outlook hesabınıza verilen tüm izinleri kaldır.';
$lang['appointment_redirect_url_logout'] = 'Outlook Yetkilendirme ve Yönlendirme URI\'si';
$lang['appointment_outlook_api_label'] = 'Outlook Takvim API\'si';
$lang['appointment_outlook_client_id'] = 'Uygulama (istemci) Kimliği';
$lang['appointment_outlook_calendar'] = 'Outlook Takvim';
$lang['appointment_outlook_calendar_info'] = 'Bu randevu Outlook Takvim\'e eklendi';
$lang['appointment_add_to_outlook'] = 'Outlook Takvim\'e Ekle';
$lang['appointment_outlook_not_added_yet'] = 'Henüz Outlook Takvim\'e eklenmedi';
$lang['appointment_is_added_to_outlook'] = 'Outlook Takvim\'e Eklendi';
$lang['appointment_calendar_adding_to_outlook'] = 'Outlook Takvim\'e ekleniyor...';
$lang['appointment_added_to_outlook'] = 'Etkinlik Outlook Takvim\'e başarıyla eklendi';
$lang['appointment_added_to_outlook_but_not_saved'] = 'Etkinlik Outlook\'a eklendi ancak veritabanına kaydedilemedi';
$lang['appointment_outlook_event_saved'] = 'Outlook etkinlik detayları kaydedildi';
$lang['appointment_outlook_event_save_failed'] = 'Outlook etkinlik detayları kaydedilemedi';
$lang['appointment_outlook_error'] = 'Outlook\'a eklenirken hata oluştu';
$lang['appointment_outlook_auth_error'] = 'Outlook kimlik doğrulama hatası';
$lang['appointment_invalid_date'] = 'Geçersiz randevu tarihi';
$lang['appointment_sign_in_to_outlook'] = 'Outlook\'a giriş yap';
$lang['appointment_sign_out_from_outlook'] = 'Outlook\'tan çıkış yap';
$lang['appointments_outlook_view_in_calendar'] = 'Outlook Takvim\'de Görüntüle';
$lang['appointment_outlook_calendar_title'] = 'Outlook Takvim';
$lang['appointment_outlook_sync_status'] = 'Outlook Senkronizasyon Durumu';
$lang['appointment_outlook_last_synced'] = 'Son senkronizasyon: %s';
$lang['appointment_outlook_sync_error'] = 'Son senkronizasyon başarısız oldu: %s';
$lang['appointment_outlook_event_deleted'] = 'Outlook etkinliği başarıyla silindi';
$lang['appointment_outlook_event_delete_failed'] = 'Outlook etkinliği silinemedi';
$lang['appointments_table_calendar'] = 'Takvimlere Eklendi';
$lang['appointment_not_added_to_calendars_yet'] = 'Henüz hiçbir takvime eklenmedi.';
$lang['permission_approve'] = 'Onayla';
$lang['permission_view_reports'] = 'Raporları Görüntüle';
$lang['appointly_missing_required_fields'] = 'Gerekli alanlar eksik';
$lang['appointly_service_not_found'] = 'Hizmet bulunamadı';
$lang['appointly_error_getting_time_slots'] = 'Müsait zaman dilimleri alınırken hata oluştu. Lütfen tekrar deneyin.';
$lang['appointly_invalid_working_hours'] = 'Geçersiz çalışma saatleri yapılandırması';
$lang['appointly_service_price_invalid'] = 'Hizmet fiyatı geçerli bir sayı olmalıdır (0 veya daha büyük)';
$lang['appointly_service_duration_invalid'] = 'Hizmet süresi pozitif bir sayı olmalıdır';
$lang['appointly_service_providers_required'] = 'En az bir hizmet sağlayıcı atanmalıdır';
$lang['appointly_available_time_slots'] = 'Müsait Zaman Dilimleri';
$lang['appointment_loading'] = 'Yükleniyor...';
$lang['appointly_error_loading_providers'] = 'Sağlayıcılar yüklenirken hata oluştu';
$lang['appointly_no_data_available'] = 'Veri mevcut değil';
$lang['appointly_please_try_again'] = 'Lütfen tekrar deneyin';
$lang['appointly_installation_complete'] = 'Kurulum başarıyla tamamlandı';
$lang['appointly_database_updated'] = 'Veritabanı başarıyla güncellendi';
$lang['appointly_menu_reset'] = 'Menü başarıyla sıfırlandı';
$lang['appointly_default_service_created'] = 'Varsayılan hizmet başarıyla oluşturuldu';
$lang['appointly_working_hours_configured'] = 'Çalışma saatleri başarıyla yapılandırıldı';
$lang['appointment_select_service_provider_first'] = 'Lütfen önce hizmet ve sağlayıcıyı seçin';
$lang['appointment_are_you_sure_to_cancel'] = 'Bu randevuyu iptal etmek istediğinizden emin misiniz?';
$lang['appointment_are_you_sure_to_mark_as_ongoing'] = 'Bu randevuyu devam ediyor olarak işaretlemek istediğinizden emin misiniz?';
$lang['appointment_error_occurred'] = 'Bir hata oluştu. Lütfen tekrar deneyin.';
$lang['appointment_closed'] = 'Randevu rezervasyonu şu anda kapalı';
$lang['appointment_time_required'] = 'Lütfen bir zaman dilimi seçin';
$lang['appointment_no_providers'] = 'Bu hizmet için sağlayıcı bulunamadı';
$lang['appointment_provider_not_available'] = 'Sağlayıcı bu gün müsait değil';
$lang['appointment_minutes'] = 'dakika';
$lang['appointment_unavailable'] = 'Müsait Değil';
$lang['is_required'] = 'zorunludur';
$lang['appointment_schedule_info'] = 'Randevu Planı';
$lang['appointment_form_info'] = 'Randevu Bilgileri';
$lang['appointment_marked_as_approved'] = 'Randevu onaylandı olarak işaretlendi';
$lang['appointment_cancellation_approval_failed'] = 'Randevu iptali onayı başarısız oldu';
$lang['appointment_send_an_sms'] = 'SMS Gönder';
$lang['appointment_call_number'] = 'Ara';
$lang['appointment_actions'] = 'Eylemler';
$lang['appointment_staff_cannot_provide_feedback'] = 'Personel üyeleri randevular için geri bildirim sağlayamaz';
$lang['appointment_thank_you_for_feedback'] = 'Geri bildiriminiz için teşekkür ederiz!';
$lang['appointment_feedback_comment_required'] = 'Geri bildirim yorumu zorunludur';

// Reschedule functionality client side
$lang['appointment_reschedule'] = 'Yeniden Planla';
$lang['appointment_reschedule_reason'] = 'Yeniden Planlama Nedeni';
$lang['appointment_reschedule_reason_placeholder'] = 'Lütfen bu randevuyu neden yeniden planlamanız gerektiğini açıklayın...';
$lang['appointment_reschedule_reason_required'] = 'Yeniden planlama nedeni zorunludur';
$lang['appointment_request_reschedule'] = 'Yeniden Planlama Talebi';
$lang['appointment_new_date'] = 'Yeni Tarih';
$lang['appointment_new_time'] = 'Yeni Saat';
$lang['appointment_reschedule_request_submitted'] = 'Yeniden planlama talebiniz gönderildi ve personelimiz tarafından incelenecektir.';
$lang['appointment_cannot_be_rescheduled'] = 'Bu randevu mevcut durumu nedeniyle yeniden planlanamaz.';

$lang['appointment_processing'] = 'İşleniyor...';
$lang['appointment_select_date_first'] = 'Lütfen önce bir tarih seçin';
$lang['appointment_please_select_date_time'] = 'Lütfen bir tarih ve saat seçin';
$lang['appointment_current_details'] = 'Mevcut Detaylar';
$lang['appointment_loading_available_times'] = 'Müsait saatler yükleniyor...';
$lang['appointment_no_available_slots'] = 'Müsait slot yok';
$lang['appointment_no_available_times'] = 'Müsait saat yok';

$lang['appointment_reschedule_requested'] = 'Yeniden Planlama Talep Edildi';
$lang['appointment_reschedule_pending_review'] = 'Yeniden planlama talebiniz personelimiz tarafından incelenmeyi bekliyor.';
$lang['appointment_reschedule_pending_notice'] = 'Bekleyen Yeniden Planlama Talepleri';
$lang['appointment_requested_date'] = 'Talep Edilen Tarih';
$lang['appointment_requested_time'] = 'Talep Edilen Saat';
$lang['appointment_current_date'] = 'Mevcut Tarih';
$lang['appointment_approve_reschedule'] = 'Yeniden Planlamayı Onayla';
$lang['appointment_reject_reschedule'] = 'Yeniden Planlamayı Reddet';
$lang['appointment_pending_reschedules'] = 'Bekleyen Yeniden Planlamalar';
$lang['appointment_reschedule_approved'] = 'Yeniden Planlama Onaylandı';
$lang['appointment_reschedule_rejected'] = 'Yeniden Planlama Reddedildi';
$lang['appointment_cancellation_requested'] = 'İptal Talep Edildi';
$lang['appointment_cancellation_pending_review'] = 'İptal talebiniz personelimiz tarafından incelenmeyi bekliyor.';
$lang['appointment_cancellation_reason'] = 'İptal Nedeni';
$lang['appointment_cancellation_notes'] = 'İptal Notları';
$lang['appointment_cancellation_notes_placeholder'] = 'Lütfen bu randevuyu neden iptal etmek istediğinizi açıklayın...';
$lang['appointment_cancellation_notes_required'] = 'İptal notları zorunludur';
$lang['appointment_cancellation_request_submitted'] = 'İptal talebiniz gönderildi ve personelimiz tarafından incelenecektir.';
$lang['appointment_confirm_approve_reschedule'] = 'Bu yeniden planlama talebini onaylamak istediğinizden emin misiniz? Bu, randevuyu yeni tarih ve saate güncelleyecektir.';
$lang['appointment_reschedule_denial_reason'] = 'Lütfen bu yeniden planlama talebini reddetme nedenini belirtin (bu müşteriye gönderilecektir):';
$lang['appointment_reschedule_approved_successfully'] = 'Yeniden planlama talebi onaylandı ve randevu güncellendi.';
$lang['appointment_reschedule_denied_successfully'] = 'Yeniden planlama talebi reddedildi ve müşteri bilgilendirildi.';
$lang['appointment_reschedule_approval_failed'] = 'Yeniden planlama talebi onaylanamadı. Lütfen tekrar deneyin.';
$lang['appointment_reschedule_denial_failed'] = 'Yeniden planlama talebi reddedilemedi. Lütfen tekrar deneyin.';
$lang['appointment_view_details'] = 'Detayları Görüntüle';
$lang['appointment_client_information'] = 'Müşteri Bilgileri';
$lang['appointment_provider_information'] = 'Sağlayıcı Bilgileri';
$lang['appointment_reschedule_action_required'] = 'Eylem Gerekli';
$lang['appointment_reschedule_instructions'] = 'Müşteriler yeniden planlama talep ettiğinde, onayınız için burada görünürler. Onaylamak, randevuyu otomatik olarak güncelleyecek ve müşteriyi e-posta ile bilgilendirecektir.';
$lang['appointment_no_pending_reschedules'] = 'Bekleyen yeniden planlama talebi bulunamadı.';
$lang['appointment_reschedule_request_details'] = 'Yeniden Planlama Talebi Detayları';
$lang['appointment_reschedule_date_required'] = 'Yeni tarih zorunludur';
$lang['appointment_reschedule_time_required'] = 'Yeni saat zorunludur';
$lang['appointment_reschedule_future_datetime'] = 'Lütfen gelecekteki bir tarih ve saat seçin';
$lang['appointment_reschedule_request_subject'] = 'Randevu Yeniden Planlama Talebi';
$lang['appointment_reschedule_approved_subject'] = 'Randevu Yeniden Planlama Onaylandı';
$lang['appointment_reschedule_denied_subject'] = 'Randevu Yeniden Planlama Reddedildi';
$lang['appointment_book_again'] = 'Tekrar Rezervasyon Yap';
$lang['appointment_cancelled_title'] = 'Bu randevu iptal edildi';
$lang['appointment_book_new_appointment'] = 'Yeni bir randevu almak ister misiniz?';
$lang['appointment_book_new'] = 'Yeni Randevu Al';
$lang['appointment_deny_reschedule'] = 'Yeniden Planlamayı Reddet';
$lang['appointment_reschedule_denial_reason_prompt'] = 'Lütfen bu yeniden planlama talebini reddetme nedenini belirtin:';
$lang['appointment_reschedule_denial_reason_required'] = 'Reddetme nedeni zorunludur';
$lang['appointment_confirm_approve_cancellation'] = 'Bu iptal talebini onaylamak istediğinizden emin misiniz? Bu, randevuyu kalıcı olarak iptal edecektir.';
$lang['appointment_cancelled_book_again_message'] = 'Bu randevu iptal edildi. Yeni bir randevu almak ister misiniz?';
$lang['appointment_no_show_book_again_message'] = 'Bu randevu gelmedi olarak işaretlendi. Yeni bir randevu almak ister misiniz?';

// Calendar Integration Removal
$lang['appointment_remove_google_integration'] = 'Google Takvim Entegrasyonunu Kaldır';
$lang['appointment_remove_outlook_integration'] = 'Outlook Takvim Entegrasyonunu Kaldır';
$lang['appointment_confirm_remove_google_integration'] = 'Bu randevudan Google Takvim entegrasyonunu kaldırmak istediğinizden emin misiniz? Bu, mümkünse etkinliği Google Takvim\'den de silecektir.';
$lang['appointment_confirm_remove_outlook_integration'] = 'Bu randevudan Outlook Takvim entegrasyonunu kaldırmak istediğinizden emin misiniz? Bu, mümkünse etkinliği Outlook Takvim\'den de silecektir.';
$lang['appointment_google_integration_removed'] = 'Google Takvim entegrasyonu randevudan kaldırıldı';
$lang['appointment_google_integration_removed_and_deleted'] = 'Google Takvim entegrasyonu kaldırıldı ve etkinlik Google Takvim\'den silindi';
$lang['appointment_google_removal_failed'] = 'Google Takvim entegrasyonu kaldırılamadı';
$lang['appointment_outlook_integration_removed'] = 'Outlook Takvim entegrasyonu randevudan kaldırıldı';
$lang['appointment_outlook_integration_removed_and_deleted'] = 'Outlook Takvim entegrasyonu kaldırıldı ve etkinlik Outlook Takvim\'den silindi';
$lang['appointment_outlook_removal_failed'] = 'Outlook Takvim entegrasyonu kaldırılamadı';
$lang['appointment_missing_required_fields'] = 'Gerekli alanlar eksik';
$lang['appointment_not_found'] = 'Randevu bulunamadı';
$lang['appointment_outlook_not_authenticated_warning'] = 'Uyarı: Şu anda Outlook ile kimlik doğrulaması yapmadınız. Entegrasyon yalnızca yerel olarak kaldırılacak, ancak etkinlik Outlook takviminizde kalacaktır.';
$lang['appointment_outlook_not_available_warning'] = 'Uyarı: Outlook entegrasyonu mevcut değil. Entegrasyon yalnızca yerel olarak kaldırılacaktır.';
$lang['appointment_outlook_integration_removed_local_only'] = 'Outlook takvim entegrasyonu yerel olarak kaldırıldı. Not: Etkinlik Outlook takviminizde hala mevcut olabilir.';

// Dashboard Widgets
$lang['appointly_upcoming_appointments'] = 'Yaklaşan Randevular';
$lang['appointly_no_upcoming_appointments'] = 'Yaklaşan Randevu Yok';
$lang['appointly_no_appointments_in_range'] = '%s aralığında planlanmış randevu yok';
$lang['appointly_next_7_days'] = 'Sonraki 7 Gün';
$lang['appointly_next_14_days'] = 'Sonraki 14 Gün';
$lang['appointly_next_30_days'] = 'Sonraki 30 Gün';
$lang['appointly_next_4_weeks'] = 'Sonraki 4 Hafta';
$lang['appointly_view_all_appointments'] = 'Tüm Randevuları Görüntüle';
$lang['appointly_dashboard_widgets_settings'] = 'Kontrol Paneli Widget Ayarları';
$lang['appointly_today_widget_enabled'] = 'Bugünkü randevular widget\'ını kontrol panelinde göster';
$lang['appointly_upcoming_widget_enabled'] = 'Yaklaşan randevular widget\'ını kontrol panelinde göster';
$lang['appointly_upcoming_widget_range'] = 'Yaklaşan randevular widget tarih aralığı';
$lang['appointly_today'] = 'Bugün';
$lang['appointly_tomorrow'] = 'Yarın';
$lang['days'] = 'gün';

$lang['appointly_invoice_default_vat'] = 'Varsayılan KDV/Vergi Yüzdesi';
$lang['appointly_invoice_vat_help'] = 'Otomatik oluşturulan faturalara uygulanacak varsayılan vergi yüzdesi (vergi yoksa 0 olarak ayarla)';
$lang['appointly_invoice_tax_type_help'] = 'Randevu faturalarına vergileri nasıl uygulamak istediğinizi seçin';
$lang['appointly_invoice_tax_type_label'] = 'Vergi Uygulama Yöntemi';
$lang['appointly_tax_type_none'] = 'Vergi Yok';
$lang['appointly_tax_type_custom'] = 'Özel Yüzde';
$lang['appointly_tax_type_system'] = 'CRM Vergi Oranlarını Kullan';
$lang['appointly_default_vat_label'] = 'Özel Vergi Yüzdesi';
$lang['appointly_default_vat_help'] = 'Faturalara uygulanacak özel vergi yüzdesi (vergi yoksa 0 olarak ayarla)';
$lang['appointly_system_tax_label'] = 'Vergi Oranı Seç';
$lang['appointly_system_tax_help'] = 'CRM\'de yapılandırılmış vergi oranlarınızdan seçin';

// Enhanced Invoice Settings - Tab Names
$lang['appointly_invoice_settings'] = 'Fatura ve Vergiler';
$lang['appointly_tax_settings'] = 'Vergi Yapılandırması';
$lang['appointly_tax_settings_help'] = 'Randevulardan oluşturulan faturalara vergilerin nasıl uygulanacağını yapılandırın. CRM\'in vergi sistemini kullanabilir veya özel bir yüzde belirleyebilirsiniz.';
$lang['appointly_tax_type_label'] = 'Vergi Uygulama Yöntemi';
$lang['appointly_tax_type_help'] = 'Randevu faturalarına vergileri nasıl uygulamak istediğinizi seçin';

// Form Field Labels
$lang['appointly_enable'] = 'Etkinleştir';
$lang['appointly_disable'] = 'Devre Dışı Bırak';
$lang['appointly_yes'] = 'Evet';
$lang['appointly_no'] = 'Hayır';

// Client Dashboard Language Strings
$lang['appointment_client_dashboard_description'] = 'Randevularınızı yönetin, geçmişi görüntüleyin ve yeni randevular alın.';
$lang['appointment_book_new'] = 'Yeni Randevu Al';
$lang['appointment_all'] = 'Tümü';
$lang['appointment_total_appointments'] = 'Toplam Randevu';
$lang['appointment_completed_appointments'] = 'Tamamlandı';
$lang['appointment_upcoming_appointments'] = 'Yaklaşan';
$lang['appointment_cancelled_appointments'] = 'İptal Edildi';
$lang['appointment_no_appointments_found'] = 'Randevu bulunamadı';
$lang['appointment_no_appointments_match_filter'] = 'Mevcut filtreyle eşleşen randevu yok';
$lang['appointment_date_and_time'] = 'Tarih ve Saat';
$lang['appointment_details'] = 'Randevu Detayları';
$lang['appointment_book_again'] = 'Tekrar Rezervasyon Yap';
$lang['view_invoice'] = 'Faturayı Görüntüle';

// Cancel and Reschedule
$lang['appointment_cancel_reason'] = 'İptal Nedeni';
$lang['appointment_cancel_reason_placeholder'] = 'Lütfen bu randevuyu neden iptal etmek istediğinizi açıklayın...';
$lang['appointment_reschedule_reason'] = 'Yeniden Planlama Nedeni';
$lang['appointment_reschedule_reason_placeholder'] = 'Lütfen bu randevuyu neden yeniden planlamanız gerektiğini açıklayın...';
$lang['appointment_new_date'] = 'Yeni Tarih';
$lang['appointment_new_time'] = 'Yeni Saat';
$lang['appointment_request_reschedule'] = 'Yeniden Planlama Talebi';
$lang['appointment_cancel_request_sent'] = 'İptal talebi onay için personele gönderildi.';
$lang['appointment_cancel_request_failed'] = 'İptal talebi gönderilemedi. Lütfen tekrar deneyin.';
$lang['appointment_reschedule_request_sent'] = 'Yeniden planlama talebi onay için personele gönderildi.';
$lang['appointment_reschedule_request_failed'] = 'Yeniden planlama talebi gönderilemedi. Lütfen tekrar deneyin.';
$lang['appointment_client_dashboard'] = 'Müşteri Kontrol Paneli';
$lang['appointment_available_times'] = 'Müsait Saatler';
$lang['appointment_error_loading_times'] = 'Saatler yüklenirken hata oluştu';
$lang['appointment_cancel_request_submitted'] = 'İptal talebi başarıyla gönderildi';
$lang['appointment_date_time_required'] = 'Tarih ve saat zorunludur';
$lang['appointment_reschedule_pending'] = 'Yeniden planlama talebi onay bekliyor';
$lang['appointly_invoice_payment_mode_changed'] = 'Fatura ödeme modu değiştirildi, değişiklikleri uygulamak için lütfen ayarları kaydedin';
$lang['payment_received_for_appointment'] = 'Şunun için ödeme alındı:';
$lang['appointly_create_invoice_when_completed'] = 'Randevu tamamlandığında fatura oluşturulsun mu?';
$lang['invoice_created_for_appointment'] = 'Bu randevu için #%s fatura oluşturuldu.';
$lang['appointment_email_sent_success'] = 'E-posta başarıyla gönderildi';
$lang['appointment_email_sent_failed'] = 'E-posta gönderilemedi';
$lang['appointment_contact_relationship'] = 'İletişim & İlişki Bilgileri';
$lang['customer_permission_appointments'] = 'Randevular';
$lang['appointment_security_verification'] = 'Güvenlik Doğrulama';
$lang['appointment_view_on_map'] = 'Haritada Görüntüle';
$lang['appointment_no_notes_available'] = 'Bu randevu için not mevcut değil';
$lang['appointment_session_overview'] = 'Oturum Genel Bakış';
$lang['appointly_show_staff_email_booking_form'] = 'Randevu formunda personel e-posta adreslerini göster';
$lang['appointly_no_available_time_slots'] = 'Müsait zaman dilimi yok';
$lang['appointment_status_changed_successfully'] = 'Randevu durumu başarıyla değiştirildi';
$lang['appointment_status_change_failed'] = 'Randevu durumu değiştirilemedi';
