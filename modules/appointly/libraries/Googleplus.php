<?php

defined('BASEPATH') or exit('No direct script access allowed');

require('modules/appointly/vendor/autoload.php');
require_once APPPATH . 'helpers/google_integration_security_helper.php';

class Googleplus
{
    public $client;

    /**
     * Googleplus constructor.
     */
    public function __construct()
    {
        $this->initGoogleClient();
        $this->thenVerifySSL();
    }

    /**
     * Google Client instance
     *
     */
    public function client()
    {
        return $this->client;
    }


    /**
     * Generate login url
     *
     * @return mixed
     */
    public function loginUrl()
    {
        return $this->client->createAuthUrl();
    }


    /**
     * Get authenticated user
     *
     * @return mixed
     */
    public function getAuthenticate()
    {
        return $this->client->authenticate();
    }


    /**
     * Get the current access token
     *
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->client->getAccessToken();
    }


    /**
     * Set new access token
     *
     * @param $token
     *
     * @return mixed
     */
    public function setAccessToken($token)
    {
        return $this->client->setAccessToken($token);
    }

    /**
     * Refresh the token
     *
     * @param $token
     *
     * @return mixed
     */
    public function refreshToken($token)
    {
        return $this->client->refreshToken($token);
    }

    /**
     * Check if acess token is expired
     *
     * @return mixed
     */
    public function isAccessTokenExpired()
    {
        return $this->client->isAccessTokenExpired();
    }

    /**
     * Revoke token from google API
     *
     * @return mixed
     */
    public function revokeToken()
    {
        $ci = &get_instance();

        if ((int) get_staff_user_id() < 1) return false;
        $token = $this->getTokenType('refresh_token') ?: $this->getTokenType('access_token');
        if (!$token) return true;
        // Revoke this staff user's grant directly; do not refresh or touch anyone else's row.
        if (!$this->client->revokeToken($token)) return false;
        return $ci->db->where('staff_id', get_staff_user_id())->delete(db_prefix() . 'appointly_google');
    }


    /**
     * Get a token type from database
     *
     * @param $type
     *
     * @param string $type The type of token to retrieve.
     *
     * @return mixed The token value, or NULL if not found.
     */
    public function getTokenType($type)
    {
        if ((int) get_staff_user_id() < 1 || !in_array($type, ['access_token', 'refresh_token'], true)) return null;
        $ci = &get_instance();
        $ci->db->select($type);
        $ci->db->where('staff_id', get_staff_user_id());

        $result = $ci->db->get(db_prefix() . 'appointly_google')->row_array();

        return isset($result[$type]) ? sc_google_secret_decrypt($result[$type]) : NULL;
    }


    /**
     * Verify http or https
     */
    private function thenVerifySSL()
    {
        $httpClient = new GuzzleHttp\Client(['verify' => true,]);
        $this->client->setHttpClient($httpClient);
    }

    /**
     * Init new Google_Client
     */
    private function initGoogleClient()
    {
        $this->client = new Google\Client();
        $this->client->setApplicationName('Appointly Google Calendar API');
        $this->client->setClientId((get_option('appointly_google_client_id') ?: get_option('google_client_id')));
        $this->client->setDeveloperKey(get_option('google_api_key'));
        $this->client->setClientSecret(sc_google_secret_decrypt(get_option('appointly_google_client_secret')));
        $this->client->setRedirectUri(base_url() . 'appointly/google/auth/oauth');
        // A dedicated connection only needs event access to calendars the owner owns.
        $this->client->addScope(get_option('appointly_google_client_id')
            ? 'https://www.googleapis.com/auth/calendar.events.owned'
            : 'https://www.googleapis.com/auth/calendar');
        $this->client->setAccessType("offline");
        $this->client->setPrompt('consent');
    }
}
