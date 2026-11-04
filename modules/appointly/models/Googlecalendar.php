<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Googlecalendar extends App_Model
{

    public $table;

    protected $googleReminders;

    public $unauthorized_url_settings;
    public $calendar;

    public function __construct()
    {
        parent::__construct();

        $this->load->library('appointly/googleplus');

        $this->unauthorized_url_settings = '<h3>Seems that something is wrong, please check that your Client Secret is correctly inserted in <a href="' . admin_url('settings?group=appointly-settings') . '">Setup->Settings->Appointments</a></h3>';

        $this->table = db_prefix() . 'appointly_google';

        $this->googleReminders = [
            'useDefault' => false,
            'overrides'  => [
                ['method' => 'email', 'minutes' => 24 * 60],
                ['method' => 'popup', 'minutes' => 60],
            ],
        ];
        /**
         * Init Google Calendar Service instance
         */
        $this->calendar = new Google\Service\Calendar($this->googleplus->client());
    }


    private function dedicatedCalendar()
    {
        return $this->sharedMode() ? trim((string) get_option('appointly_google_team_calendar_id')) : $this->calendarId();
    }

    public function sharedMode()
    {
        return get_option('appointly_google_team_enabled') === '1';
    }

    public function syncOwnerId()
    {
        $actor = (int) get_staff_user_id();
        if ($actor < 1) return 0;
        if (!$this->sharedMode()) return $actor;
        if (!is_admin() && !staff_can('create', 'appointments') && !staff_can('edit', 'appointments')) return 0;
        return max(0, (int) get_option('appointly_google_team_owner_id'));
    }

    public function saveTeamSettings($enabled, $ownerId)
    {
        if (!is_admin()) throw new RuntimeException('Only administrators can configure the team calendar.');
        $ownerId = filter_var($ownerId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($enabled && !$ownerId) throw new InvalidArgumentException('Choose a valid calendar owner staff ID.');
        if ($enabled) {
            $staff = $this->db->where('staffid', $ownerId)->where('active', 1)->get(db_prefix().'staff')->row();
            $calendar = get_option('appointly_google_calendar_id_staff_'.$ownerId);
            if (!$calendar && (int) get_option('appointly_google_calendar_staff_id') === $ownerId) $calendar = get_option('appointly_google_calendar_id');
            if (!$staff || !is_string($calendar) || !preg_match('/^[a-z0-9_-]+@group\.calendar\.google\.com$/iD', $calendar)) {
                throw new RuntimeException('The owner must save their separate company calendar in My Google Calendar first.');
            }
            // Switching an active team target could strand existing appointment links.
            if ($this->sharedMode() && ((int) get_option('appointly_google_team_owner_id') !== $ownerId
                || get_option('appointly_google_team_calendar_id') !== $calendar)) {
                throw new RuntimeException('Disable team sync before changing its owner or calendar. Existing events are retained.');
            }
            update_option('appointly_google_team_owner_id', (string) $ownerId);
            update_option('appointly_google_team_calendar_id', $calendar);
        }
        update_option('appointly_google_team_enabled', $enabled ? '1' : '0');
    }

    public function calendarId()
    {
        $staffId = (int) get_staff_user_id();
        if ($staffId < 1) return '';
        $personal = get_option('appointly_google_calendar_id_staff_' . $staffId);
        // Only the explicitly configured original owner inherits the old target.
        if ($personal === false || $personal === null || $personal === '') {
            $personal = (int) get_option('appointly_google_calendar_staff_id') === $staffId
                ? get_option('appointly_google_calendar_id') : '';
        }
        return is_string($personal) ? trim($personal) : '';
    }

    public function saveCalendarId($calendarId)
    {
        $staffId = (int) get_staff_user_id();
        if ($staffId < 1 || !is_string($calendarId) || strlen($calendarId) > 191
            || !preg_match('/^[a-z0-9_-]+@group\.calendar\.google\.com$/iD', trim($calendarId))) {
            throw new InvalidArgumentException('Enter the ID of a separate calendar owned by your Google account.');
        }
        $calendarId = trim($calendarId);
        if ($this->sharedMode() && (int) get_option('appointly_google_team_owner_id') === $staffId
            && $calendarId !== get_option('appointly_google_team_calendar_id')) {
            throw new RuntimeException('Disable team sync before changing its owner calendar.');
        }
        if ($calendarId !== $this->calendarId() && $this->getAccountDetails()) {
            throw new RuntimeException('Disconnect your Google account before changing its calendar. Existing Google events are retained.');
        }
        // Never accept a staff ID from the request, even for administrators.
        $key = 'appointly_google_calendar_id_staff_' . $staffId;
        if (get_option($key) !== $calendarId && !update_option($key, $calendarId, 0)) {
            throw new RuntimeException('Your Calendar settings could not be saved. Please try again.');
        }
    }

    private function dedicatedMode()
    {
        return $this->dedicatedCalendar() !== '' || trim((string) get_option('appointly_google_client_id')) !== ''
            || trim((string) get_option('appointly_google_calendar_id')) !== '';
    }

    private function syncCalendar($requested = 'primary')
    {
        $target = $this->dedicatedCalendar();
        if ($this->dedicatedMode()) {
            if ($this->syncOwnerId() < 1 || !preg_match('/^[a-z0-9_-]+@group\.calendar\.google\.com$/iD', $target)) {
                throw new RuntimeException('Configure your own private calendar before syncing appointments.');
            }
            return $target;
        }
        return $requested;
    }

    private function assertManagedEvent($event)
    {
        if (!$this->dedicatedMode()) return;
        $properties = $event->getExtendedProperties();
        $private = $properties ? $properties->getPrivate() : [];
        $staffId = (int) get_staff_user_id();
        $owner = $private['smartchoice_staff_id'] ?? null;
        if ($this->sharedMode() && ($private['smartchoice_team_calendar'] ?? '') !== hash('sha256', $this->dedicatedCalendar())) {
            throw new RuntimeException('This event does not belong to the configured team calendar.');
        }
        // The previous dedicated connection stamped only the CRM source. Keep
        // its existing events usable solely by its recorded original owner/target.
        $originalOwner = $owner === null && $staffId > 0
            && (int) get_option('appointly_google_calendar_staff_id') === $staffId
            && $this->calendarId() === trim((string) get_option('appointly_google_calendar_id'));
        if (($private['smartchoice_source'] ?? '') !== hash('sha256', base_url())
            || ((string) $owner !== (string) $staffId && !$originalOwner)) {
            throw new RuntimeException('This event was not created by this CRM and cannot be changed.');
        }
    }

    /**
     * Google OAuth Login Url
     *
     * @return void
     */
    public function loginUrl()
    {
        return $this->googleplus
            ->loginUrl();
    }


    /**
     * Google OAuth Login validation
     *
     * @return bool
     */
    public function login($code)
    {
        try {
            $login = $this->googleplus
                ->client
                ->authenticate($code);
        } catch (Throwable $e) {
            log_message('error', 'Google Calendar authorization failed; reconnect the calendar.');
            return false;
        }
        if (!is_array($login) || empty($login['access_token']) || empty($login['expires_in']) || isset($login['error'])) {
            return false;
        }

        if ($login) {

            $this->db->where('staff_id', get_staff_user_id());
            $result = $this->db->get($this->table)->row_array();

            // A new authorization must include its own refresh grant. Never pair
            // a new Google account's access token with an older account's grant.
            if (empty($login['refresh_token'])) return false;

            if ($result) {
                $this->saveNewTokenValues($login);
            } else {
                if (isset($login['refresh_token'])) {
                    $this->db->insert($this->table, [
                        'staff_id'      => get_staff_user_id(),
                        'access_token'  => sc_google_secret_encrypt($login['access_token']),
                        'refresh_token' => sc_google_secret_encrypt($login['refresh_token']),
                        'expires_in'    => time() + $login['expires_in'],
                    ]);
                } else {
                    $this->db->insert($this->table, [
                        'staff_id'     => get_staff_user_id(),
                        'access_token' => sc_google_secret_encrypt($login['access_token']),
                        'expires_in'   => time() + $login['expires_in'],
                    ]);
                }
            }

            $token = $this->googleplus
                ->client
                ->getAccessToken();

            $this->googleplus
                ->client
                ->setAccessToken($token);

            return true;
        }
        return false;
    }


    /**
     * Google get email user info
     *
     * @return array
     */
    public function getUserInfo()
    {
        return $this->googleplus->getUser();
    }


    /**
     * Google Calendar delete event
     *
     * @param $eventid
     * @return bool
     */
    public function deleteEvent($eventid)
    {
        try {
            if ($this->sharedMode() && !appointlyGoogleAuth()) return false;
            // Check if we need to refresh the token
            if ($this->googleplus->isAccessTokenExpired()) {
                $refreshToken = $this->googleplus->getTokenType('refresh_token');
                if ($refreshToken) {
                    $newToken = $this->googleplus->refreshToken($refreshToken);
                    $this->googleplus->setAccessToken($newToken);
                } else {
                    log_message('error', 'Google Calendar: No refresh token available for token refresh');
                    return false;
                }
            }

            // First check if the event exists
            try {
                $event = $this->calendar->events->get($this->syncCalendar(), $eventid);

                $this->assertManagedEvent($event);

                // Log successful retrieval
                log_message('debug', 'Google Calendar: Successfully found event ' . $eventid . ' before deletion');

                // Set sendUpdates parameter based on settings
                $params = [];
                if ($this->dedicatedMode() || get_option('appointly_disable_google_meeting_emails') == '1') {
                    $params['sendUpdates'] = 'none';
                } else {
                    $params['sendUpdates'] = 'all';
                }

                // If we get here, the event exists, so try to delete it
                $deleteResult = $this->calendar->events->delete($this->syncCalendar(), $eventid, $params);

                // Log success
                log_message('debug', 'Google Calendar: Successfully deleted event ' . $eventid);

                return true;
            } catch (Exception $innerException) {
                // If event not found
                if ($innerException->getCode() === 404) {
                    log_message('debug', 'Google Calendar: Event not found when trying to delete: ' . $eventid);
                    // Consider returning true here instead of false, since the end result is the same - event doesn't exist
                    return true;
                }

                // Log other exceptions
                log_message('error', 'Google Calendar inner exception: ' . $innerException->getMessage());
                throw $innerException;
            }
        } catch (Exception $e) {
            log_message('error', 'Google Calendar: Error deleting event: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Google Calendar get events
     *
     * @param string $calendarId
     * @param bool   $timeMin
     * @param bool   $timeMax
     * @param int    $maxResults
     * @return array
     */
    public function getEvents($calendarId = 'primary', $timeMin = false, $timeMax = false, $maxResults = 200)
    {
        // One-way mode never imports Google/personal events into CRM reports.
        if ($this->dedicatedMode()) return [];
        if (! $timeMin) {
            $timeMin = date("c", strtotime("-6 months", strtotime(date('Y-m-d') . ' 00:00:00')));
        } else {
            $timeMin = date("c", strtotime($timeMin));
        }

        if (! $timeMax) {
            $timeMax = date("c", strtotime("+6 months", strtotime(date('Y-m-d') . ' 23:59:59')));
        } else {
            $timeMax = date("c", strtotime($timeMax));
        }

        $optParams = [
            'maxResults'   => $maxResults,
            'orderBy'      => 'startTime',
            'singleEvents' => true,
            'timeMin'      => $timeMin,
            'timeMax'      => $timeMax,
            'timeZone'     => get_option('default_timezone'),
        ];

        try {
            $results = $this->calendar->events->listEvents($calendarId, $optParams);

            $data = [];

            foreach ($results->getItems() as $item) {
                // Get the correct date/time format
                $start = $item->getStart();
                $end = $item->getEnd();

                // Make sure we can safely access dateTime
                $startDateTime = isset($start->dateTime) ? $start->dateTime : (isset($start->date) ? $start->date : null);
                $endDateTime = isset($end->dateTime) ? $end->dateTime : (isset($end->date) ? $end->date : null);

                if ($startDateTime) {
                    array_push($data, [
                        'id' => $item->getId(),
                        'summary' => $item->getSummary() ?? 'Untitled Event',
                        'description' => $item->getDescription() ?? '',
                        'creator' => $item->getCreator(),
                        'start' => $startDateTime,
                        'end' => $endDateTime,
                        'htmlLink' => $item->getHtmlLink(),
                        'recurringEventId' => $item->getRecurringEventId(),
                        'status' => $item->getStatus(),
                        'eventType' => $item->getEventType(),
                        'location' => $item->getLocation()
                    ]);
                }
            }

            return $data;
        } catch (Exception $e) {
            log_message('error', 'Google Calendar: Error fetching events: ' . $e->getMessage());
            return [];
        }
    }


    /**
     * Google Calendar add new event
     *
     * @param string $calendarId
     * @param        $data
     * @return Google_Service_Calendar_Event
     */
    public function addEvent($data, $calendarId = 'primary')
    {
        $event = $this->fillGoogleCalendarEvent($data);

        // Set sendUpdates parameter in the API call based on settings
        $params = ['conferenceDataVersion' => 1];
        if ($this->dedicatedMode() || get_option('appointly_disable_google_meeting_emails') == '1') {
            $params['sendUpdates'] = 'none';
        } else {
            $params['sendUpdates'] = 'all';
        }

        return $this->calendar->events->insert($this->syncCalendar($calendarId), $event, $params);
    }


    /**
     * Google Calendar update existing event
     *
     * @param $eventid
     * @param $data
     * @return Google\Service\Calendar\Event
     */
    public function updateEvent($eventid, $data)
    {
        $event = $this->fillGoogleCalendarEvent($data);

        // Set sendUpdates parameter in the API call based on settings
        $params = [];
        if ($this->dedicatedMode() || get_option('appointly_disable_google_meeting_emails') == '1') {
            $params['sendUpdates'] = 'none';
        } else {
            $params['sendUpdates'] = 'all';
        }

        $calendarId = $this->syncCalendar();
        $this->assertManagedEvent($this->calendar->events->get($calendarId, $eventid));
        return $this->calendar->events->update($calendarId, $eventid, $event, $params);
    }

    /**
     * Manage data and fill google calendar event array
     *
     * @param $data
     * @return Google\Service\Calendar\Event
     */
    public function fillGoogleCalendarEvent($data)
    {

        $event = new Google\Service\Calendar\Event(
            [
                'summary'        => $data['summary'],
                'description'    => $data['description'],
                'location'       => ($data['location']) ? $data['location'] : '',
                'start'          => $data['start'],
                'end'            => $data['end'],
                'attendees'      => (array) $data['attendees'],
                'reminders'      => $this->googleReminders,
            ]
        );

        // Video rooms are provisioned by Jitsi; calendar sync must not create another room.

        if ($this->dedicatedMode()) {
            foreach (['start', 'end'] as $part) {
                $value = $data[$part];
                if (!empty($value['dateTime'])) {
                    $time = new DateTimeImmutable($value['dateTime'], new DateTimeZone($value['timeZone'] ?? (get_option('default_timezone') ?: 'America/New_York')));
                    $value = ['dateTime' => $time->setTimezone(new DateTimeZone('America/New_York'))->format(DateTime::RFC3339), 'timeZone' => 'America/New_York'];
                    $setter = $part === 'start' ? 'setStart' : 'setEnd';
                    $event->$setter(new Google\Service\Calendar\EventDateTime($value));
                }
            }
            $event->setVisibility('private');
            $event->setAttendees([]);
            $event->setReminders(new Google\Service\Calendar\EventReminders(['useDefault' => false, 'overrides' => []]));
            $event->setExtendedProperties(new Google\Service\Calendar\EventExtendedProperties([
                'private' => [
                    'smartchoice_source' => hash('sha256', base_url()),
                    'smartchoice_staff_id' => (string) get_staff_user_id(),
                    'smartchoice_team_calendar' => $this->sharedMode() ? hash('sha256', $this->dedicatedCalendar()) : '',
                ],
            ]));
        }
        return $event;
    }

    /**
     * Get logged in Google account details
     *
     * @return mixed
     */
    public function getAccountDetails($forSync = false)
    {
        if ((int) get_staff_user_id() < 1) return false;
        $owner = $forSync ? $this->syncOwnerId() : (int) get_staff_user_id();
        if ($owner < 1) return false;
        $this->db->select();
        $this->db->where('staff_id', $owner);
        $result = $this->db->get($this->table)->result();

        if ($result) {
            foreach ($result as $account) {
                $account->access_token = sc_google_secret_decrypt($account->access_token);
                $account->refresh_token = sc_google_secret_decrypt($account->refresh_token);
            }
            return $result;
        }
        return false;
    }


    /**
     * Google save / update new token values in database
     *
     * @param $data
     * @return void
     */
    public function saveNewTokenValues($data, $forSync = false)
    {
        if ((int) get_staff_user_id() < 1 || !is_array($data) || empty($data['access_token']) || empty($data['expires_in'])) return;
        $values = ['access_token' => sc_google_secret_encrypt($data['access_token']), 'expires_in' => time() + (int) $data['expires_in']];
        // Google may omit refresh_token on renewal; preserve the existing grant.
        if (!empty($data['refresh_token'])) $values['refresh_token'] = sc_google_secret_encrypt($data['refresh_token']);
        $owner = $forSync ? $this->syncOwnerId() : (int) get_staff_user_id();
        if ($owner < 1) return;
        $this->db->where('staff_id', $owner)->update($this->table, $values);
    }
}
