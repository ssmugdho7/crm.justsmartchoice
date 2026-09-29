<?php

if (! function_exists('checkGoogleTwoWaySyncResults')) {
    /**
     * @param  array  $filters
     * @param $rResult
     * @param $output
     * @param  array  $result
     * @return array
     * @throws Exception
     */
    function checkGoogleTwoWaySyncResults(array $filters, $rResult, $output, array $result): array
    {
        $CI = get_instance();
        $googleData = [];
        $filteredResults = [];

        // Check if we're filtering for Google Calendar synced events
        $isGoogleCalendarFilter = !empty($CI->input->post('google_calendar_synced')) ||
            ($CI->input->post('custom_view') === 'google_calendar_synced');

        // Store the filter state in session for client-side access
        $CI->session->set_userdata('appointly_is_google_filter', $isGoogleCalendarFilter);

        // Get Google Calendar data regardless of filter
        if (appointlyGoogleAuth() && get_option('appointments_googlesync_show_in_table')) {
            $googleData = appointlyGetGoogleCalendarData();
        }

        if ($isGoogleCalendarFilter) {
            // For Google Calendar filter, we need to apply the same deduplication logic
            // Extract Google event IDs from database appointments to avoid duplicates
            $dbGoogleEventIds = [];
            foreach ($rResult as $appointment) {
                if (!empty($appointment['google_event_id'])) {
                    $dbGoogleEventIds[] = $appointment['google_event_id'];
                }
            }

            // Filter Google Calendar events to exclude those already in database
            $filteredGoogleData = array_filter($googleData, static function ($event) use ($dbGoogleEventIds) {
                // Keep only events that don't have matching google_event_id in database
                return !isset($event['id']) || ! in_array($event['id'], $dbGoogleEventIds);
            });
            $rResult = array_values($filteredGoogleData);

            // Apply search filtering
            $searchTerm = strtolower($CI->input->post('search')['value'] ?? '');
            if (trim($searchTerm) !== '') {
                $rResult = array_filter($rResult, static function ($row) use ($searchTerm) {
                    return stripos((string) $row['subject'], $searchTerm) !== false
                           || stripos((string) $row['description'], $searchTerm) !== false
                           || stripos((string) $row['creator_firstname'], $searchTerm) !== false;
                });
            }

            // Apply sorting
            $sortColumnIndex = $CI->input->post('order')[0]['column'] ?? -1;
            $sortDirection = $CI->input->post('order')[0]['dir'] ?? 'asc';

            $columnMap = [
                1 => 'subject',
                2 => 'date',
                3 => 'created_by',
                4 => 'description',
            ];

            $sortColumn = $columnMap[$sortColumnIndex] ?? 'date';

            usort($rResult, static function ($a, $b) use ($sortColumn, $sortDirection) {
                $dateA = isset($a['date']) ? strtotime($a['date']) : 0;
                $dateB = isset($b['date']) ? strtotime($b['date']) : 0;

                if ($sortColumn === 'date') {
                    $compareResult = $dateA - $dateB;
                } else {
                    $compareResult = strcasecmp(
                        (string)($a[$sortColumn] ?? ''),
                        (string)($b[$sortColumn] ?? '')
                    );
                    if ($compareResult === 0) {
                        $compareResult = $dateA - $dateB;
                    }
                }

                return ($sortDirection === 'asc') ? $compareResult : -$compareResult;
            });

            // Apply pagination
            $start = (int) $CI->input->post('start');
            $length = (int) $CI->input->post('length');

            $totalRecords = count($rResult);
            $rResult = array_slice($rResult, $start, $length);

            // Ensure proper DataTables format for AJAX response
            $output = [
                'draw' => (int) $CI->input->post('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => array_values($rResult)
            ];

            return [$CI, $googleData, $rResult, $output, $rResult];
        }

        // Normal merge behavior for non-Google-filter view
        // If appointments_googlesync_show_in_table option is enabled, merge Google Calendar appointments
        if (!empty($googleData)) {
            // Extract Google event IDs from database appointments
            $dbGoogleEventIds = [];
            foreach ($rResult as $appointment) {
                if (!empty($appointment['google_event_id'])) {
                    $dbGoogleEventIds[] = $appointment['google_event_id'];
                }
            }
            // Filter out Google events that already exist in the database
            $filteredGoogleData = array_filter($googleData, function ($event) use ($dbGoogleEventIds) {
                // Keep only events that don't have matching google_event_id in database
                return !isset($event['id']) || ! in_array($event['id'], $dbGoogleEventIds);
            });

            $mergedData = array_merge($rResult, $filteredGoogleData);

            $uniqueMeetings = [];
            foreach ($mergedData as $meeting) {
                if (!isset($meeting['date'])) continue;

                // Create a more robust unique key that includes google_event_id if available
                if (!empty($meeting['google_event_id'])) {
                    $uniqueKey = 'google_' . $meeting['google_event_id'];
                } elseif (!empty($meeting['id']) && isset($meeting['source']) && $meeting['source'] === 'google') {
                    $uniqueKey = 'google_' . $meeting['id'];
                } else {
                    $uniqueKey = md5($meeting['subject'] . $meeting['date']);
                }

                if (!isset($uniqueMeetings[$uniqueKey])) {
                    $uniqueMeetings[$uniqueKey] = $meeting;
                }
            }

            $filteredResults = array_values($uniqueMeetings);
        }

        return [$CI, $googleData, $filteredResults, $output, $rResult];
    }
}


if (! function_exists('appointlyGetGoogleCalendarData')) {
    /**
     * Fetch Google Calendar Two-way Sync data
     *
     * @return array
     */
    function appointlyGetGoogleCalendarData()
    {
        $data = [];
        $CI = &get_instance();

        try {
            $CI->load->model('appointly/googlecalendar');
            $events = $CI->googlecalendar->getEvents();

            if (!$events) {
                log_message('error', 'Google Calendar: No events found or not authenticated');
                return [];
            }

            foreach ($events as $item) {
                // Get event ID without instance suffix for recurring events
                $eventId = explode('_', $item['id'])[0];

                // Format Google Calendar link
                $calendarLink = $item['htmlLink'];
                if (empty($calendarLink)) {
                    $calendarLink = 'https://calendar.google.com/calendar/u/0/r/eventedit/' . $eventId;
                }

                $data[] = [
                    'id' => $eventId,
                    'subject' => $item['summary'],
                    'description' => $item['description'],
                    'status' => $item['status'] ?? 'in-progress',
                    'creator_firstname' => $item['creator']->email ?? null,
                    'creator_lastname' => null,
                    'phone' => '',
                    'contact_email' => '',
                    'google_calendar_link' => $calendarLink,
                    'google_added_by_id' => null,
                    'outlook_calendar_link' => null,
                    'outlook_added_by_id' => null,
                    'feedback' => '',
                    'contact_id' => 0,
                    'created_by' => null,
                    'custom_status' => null,
                    'name' => null,
                    'from_2way_sync' => true,
                    'date' => !empty($item['start'])
                        ? date('Y-m-d H:i:s', strtotime($item['start']))
                        : null,
                    'start_hour' => !empty($item['start'])
                        ? date('H:i', strtotime($item['start']))
                        : '',
                    'source' => 'google',
                    'eventType' => $item['eventType'] ?? null,
                    'location' => $item['location'] ?? null,
                    'recurringEventId' => $item['recurringEventId'] ?? null
                ];
            }
        } catch (Exception $e) {
            log_message('error', 'Google Calendar Data Error: ' . $e->getMessage());
            return [];
        }

        return $data;
    }
}
