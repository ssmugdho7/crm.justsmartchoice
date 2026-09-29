<?php

defined('BASEPATH') or exit('No direct script access allowed');

require('modules/appointly/vendor/autoload.php');

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

        $refreshToken = $this->getTokenType('refresh_token');

        if ($refreshToken) {
            // Refresh the token first
            $newToken = $this->client->refreshToken($refreshToken);
            $this->client->setAccessToken($newToken);

            // Revoke the newly refreshed token
            // We do this because sometimes the access_token is not valid due to unknown reasons
            $ci->db->where('staff_id', get_staff_user_id());
            $ci->db->delete(db_prefix() . 'appointly_google');

            return $this->client->revokeToken($this->client->getAccessToken());
        }
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
        $ci = &get_instance();
        $ci->db->select($type);
        $ci->db->where('staff_id', get_staff_user_id());

        $result = $ci->db->get(db_prefix() . 'appointly_google')->row_array();

        return isset($result[$type]) ? $result[$type] : NULL;
    }


    /**
     * Verify http or https
     */
    private function thenVerifySSL()
    {
        $httpClient = new GuzzleHttp\Client(['verify' => false,]);
        $this->client->setHttpClient($httpClient);
    }

    /**
     * Init new Google_Client
     */
    private function initGoogleClient()
    {
        $this->client = new Google\Client();
        $this->client->setApplicationName('Appointly Google Calendar API');
        $this->client->setClientId(get_option('google_client_id'));
        $this->client->setDeveloperKey(get_option('google_api_key'));
        $this->client->setClientSecret(get_option('appointly_google_client_secret'));
        $this->client->setRedirectUri(base_url() . 'appointly/google/auth/oauth');
        $this->client->addScope('https://www.googleapis.com/auth/calendar');
        $this->client->setAccessType("offline");
        $this->client->setPrompt('consent');
    }
}
