<?php

namespace TourGuide\Http;

use TourGuide\TourGuideRepository;
use TourGuide\TourGuideTranslator;
use TourGuide\TourGuideUtils;

trait TourGuideServiceTrait
{
    /** @var TourGuideReposiotry $tourGuideReposiotry */
    protected $tourGuideReposiotry;
    protected $responseTransformer;

    public function initTrait($responseTransformer = null)
    {
        if ($responseTransformer)
            $this->responseTransformer = $responseTransformer;

        if (!$this->tourGuideReposiotry)
            $this->tourGuideReposiotry = new TourGuideRepository();
    }

    /**
     * Display the form for creating or editing a tour guide.
     *
     * @param int $id Optional ID of the tour guide to edit. If not provided, the form is for creating a new guide.
     */
    public function handleForm($id = '')
    {
        $id = (int)$id;
        $success = false;
        $message = tourGuideHelper()->translate('tour_guide_error_completing_action');

        $data = [];
        $data['title'] = tourGuideHelper()->translate('tour_guide');

        try {
            // If editing, retrieve the tour guide data
            if ($id) {

                $data['tour_guide'] = $this->tourGuideReposiotry->get($id);

                if (empty($data['tour_guide'])) {

                    throw new \Exception(tourGuideHelper()->translate("tour_guide_not_found"), 1);
                }
            }

            // Handle form submission
            $postData = TourGuideUtils::getPostData(null, true);
            if (!empty($postData)) {

                $post_id = (int)($postData['id'] ?? '');

                if ($post_id == $id) {

                    $form = TourGuideUtils::getFormData(); // return data from tour namespace only
                    $form['settings'] = json_encode($form['settings']);
                    $form['id'] = $id;

                    $resp = $this->tourGuideReposiotry->save($form);
                    if (empty($id) && $resp) {
                        $data['tour_guide'] = $this->tourGuideReposiotry->get($resp);
                    }
                    $success = true;
                    $message = tourGuideHelper()->translate($id ? 'tour_guide_updated_successfully' : 'tour_guide_added_successfully', tourGuideHelper()->translate('tour_guide'));
                }
            }
        } catch (\Throwable $th) {
            $message = $th->getMessage();
        }

        return $this->response([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Delete a tour guide by ID.
     *
     * @param int $id ID of the tour guide to delete.
     */
    public function handleDelete($id)
    {
        $message = tourGuideHelper()->translate('tour_guide_error_completing_action');
        $success = false;

        try {
            $success = $this->tourGuideReposiotry->delete($id);
            if ($success) {
                $message =  tourGuideHelper()->translate('tour_guide_deleted', tourGuideHelper()->translate('tour_guide'));
            }
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            $success = false;
        }

        return $this->response([
            'success' => $success,
            'message' => $message
        ]);
    }

    /**
     * Clone a tour guide by ID
     *
     * @param int $id
     * @return array
     */
    public function handleClone($id)
    {

        $message = tourGuideHelper()->translate('tour_guide_error_completing_action');
        $success = false;

        try {
            $success = $this->tourGuideReposiotry->clone($id);
            if ($success) {
                $message =  tourGuideHelper()->translate('tour_guide_added_successfully', tourGuideHelper()->translate('tour_guide'));
            }
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            $success = false;
        }

        return $this->response([
            'success' => $success,
            'message' => $message
        ]);
    }

    /**
     * Handle tour setup actions
     *
     * @param int $id
     * @param string $action
     * @return array
     */
    public function handleSetup($id, $action = 'start')
    {
        $message = '';
        $success = true;

        try {
            // Get the tour
            $tour_guide = $this->tourGuideReposiotry->get($id);
            if (empty($tour_guide)) {

                throw new \Exception(tourGuideHelper()->translate("tour_guide_not_found"), 1);
            }

            // Save setup for the tour
            $postData = TourGuideUtils::getPostData(null, true);
            if ($action == 'save' && !empty($postData)) {

                $message = tourGuideHelper()->translate('tour_guide_updated_successfully', tourGuideHelper()->translate('tour_guide'));

                $steps = $postData['steps'] ?? [];
                $steps = json_encode($steps);

                $form = [
                    'steps' => $steps,
                    'id' => $id
                ];

                $this->tourGuideReposiotry->save($form);
                $success = true;
            }


            // Stop setup mode
            else if ($action == 'stop') {

                tourGuideHelper()->setSetupSessionId(false);
            } else {

                // Start setup mode
                tourGuideHelper()->setSetupSessionId($id);
            }
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            $success = false;
        }

        return $this->response([
            'success' => $success, 'message' => $message, 'tour_guide' => $tour_guide
        ]);
    }

    /**
     * Method to manage the translation of a tour
     *
     * @param int $id
     * @return array
     */
    public function handleTranslate($id)
    {
        $message = '';
        $success = true;
        $redirect = '';
        $data = [];

        try {

            $tour_guide = $this->tourGuideReposiotry->get($id);
            if (empty($tour_guide)) {

                throw new \Exception(tourGuideHelper()->translate("tour_guide_not_found"), 404);
            }

            $postData = TourGuideUtils::getPostData(null, true);
            // Example usage of the TranslationAPI class
            if (!empty($postData)) {

                // Save the translation
                if (isset($postData['steps_translations'])) {
                    $form = [
                        'steps_translations' => json_encode($postData['steps_translations'] ?? []),
                        'id' => $id
                    ];

                    $success = $this->tourGuideReposiotry->save($form);
                    $message = tourGuideHelper()->translate('updated_successfully', tourGuideHelper()->translate('tour_guide_steps_translations'));
                    return $this->response([
                        'message' => $message,
                        'success' => $success,
                        'redirect' => tourGuideHelper()->adminUrl()
                    ]);
                }

                // Translate
                $translation = '';
                $text = $postData['text'] ?? '';
                $sourceLang = $postData['source_lang'] ?? '';
                $targetLang = $postData['target_lang'] ?? '';
                if (!empty($text) && !empty($sourceLang) && !empty($targetLang)) {
                    $cache = tourGuideHelper()->session('translation')[$sourceLang . '_' . $targetLang]["$text"] ?? '';
                    if (!empty($cache)) $translation = $cache;

                    if (empty($translation)) {
                        $api = new TourGuideTranslator();
                        $translation = $api->translate($text, $sourceLang, $targetLang);

                        $cache = tourGuideHelper()->session('translation') ?? [];
                        $cache[$sourceLang . '_' . $targetLang]["$text"] = $translation;
                        tourGuideHelper()->session('translation', $cache);
                    }
                }

                return $this->response([
                    'message' => '',
                    'success' => true,
                    'translation' => $translation,
                    'redirect' => tourGuideHelper()->adminUrl()
                ]);
            }


            $supported_languages = tourGuideHelper()->supportedLanguages();
            $steps = json_decode($tour_guide->steps ?? '') ?? [];
            $steps_translations = json_decode($tour_guide->steps_translations ?? '', true) ?? [];

            if (empty($steps)) {
                throw new \Exception(tourGuideHelper()->translate("tour_guide_steps_empty"), 404);
            }

            $lang_compare_index = 'code';
            $default_language = tourGuideHelper()->systemLocale();

            $data = [
                'tour_guide' => $tour_guide,
                'steps' => $steps,
                'steps_translations' => $steps_translations,
                'lang_compare_index' => $lang_compare_index,
                'default_language' => $default_language,
                'supported_languages' => $supported_languages
            ];
        } catch (\Throwable $th) {
            if ($th->getCode() == 404)
                $redirect  = tourGuideHelper()->adminUrl();

            $message = $th->getMessage();
            $success = false;
        }

        return $this->response(['data' => $data, 'success' => $success, 'message' => $message, 'redirect' => $redirect]);
    }

    /**
     * Manage the user context and progress actions
     *
     * @param string $action
     * @return array
     */
    public function handleUserActions($action)
    {
        $success = true;
        $message = '';

        try {
            $postData = TourGuideUtils::getPostData(null, true);
            $userId = $postData['id'] ?? '';
            $tourId = $postData['tour'] ?? '';

            if (empty($postData) || empty($userId) || empty($tourId))
                throw new \Exception(tourGuideHelper()->translate('tour_guide_empty_data'), 1);

            if (strtolower($userId) !== 'guest') {

                $userMetadata = $this->tourGuideReposiotry->getUserMetadata($userId);

                if ($action == 'last_view') {
                    $data = ['last_view' => ['step' => $postData['step'], 'tour' => $tourId]];
                    $userMetadata =  $this->tourGuideReposiotry->updateUserMetadata($userId, $data);
                }

                if ($action == 'finish') {
                    $finishedTours = (array)($userMetadata['finised_tours'] ?? []);
                    $finishedTours[] = $tourId;
                    $data = ['finished_tours' => $finishedTours];
                    $userMetadata = $this->tourGuideReposiotry->updateUserMetadata($userId, $data);
                }
            }
        } catch (\Throwable $th) {
            $success = false;
            $message = $th->getMessage();
        }
        return $this->response([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * Handle the settings of the whole package
     *
     * @return array
     */
    public function handleSettings()
    {
        $success = true;
        $message = '';

        try {
            $postData = TourGuideUtils::getPostData(null, true);

            if (empty($postData))
                throw new \Exception(tourGuideHelper()->translate('tour_guide_empty_data'), 1);

            $success = $this->tourGuideReposiotry->updateSettings($postData);
            if ($success)
                $message = trim(tourGuideHelper()->translate('tour_guide_updated_successfully', ''));
        } catch (\Throwable $th) {
            $success = false;
            $message = $th->getMessage();
        }

        return $this->response([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * Transforms and formats a response data array.
     * 
     * This function applies a callable response transformer (if provided) to the input data.
     * It ensures the response contains the required keys: 'status', 'message', 'data', and 'is_post'.
     * The function is used to structure a standardized response format.
     * 
     * @param array $data - The data to be transformed and returned.
     * @return array - The formatted response data.
     */
    private function response($data)
    {
        // Apply the response transformer if one is set and callable
        if (is_callable($this->responseTransformer)) {
            $data = call_user_func($this->responseTransformer, $data);
        }

        // Ensure the response contains a status, defaulting to 'success'
        if (!isset($data['status'])) {
            $data['status'] = 'success';
        }

        // Ensure the response contains a message, defaulting to an empty string
        if (!isset($data['message'])) {
            $data['message'] = '';
        }

        // Ensure the response contains a 'data' field, defaulting to an empty array
        if (!isset($data['data'])) {
            $data['data'] = [];
        }

        // Check if the request is a POST request and include this information in the response
        if (!isset($data['is_post'])) {
            $data['is_post'] = TourGuideUtils::isPostRequest();
        }

        return $data; // Return the formatted response data
    }

    /**
     * Outputs a JSON response and terminates the script.
     * 
     * This function applies a callable response transformer (if provided) to the input data.
     * It ensures the response contains the required keys: 'status' and 'message'.
     * The HTTP status code is set based on the success or failure of the response.
     * The function outputs a JSON-encoded response and terminates the script with the appropriate HTTP headers.
     * 
     * @param array $data - The data to be transformed and returned as JSON.
     * @return void - The function exits the script after sending the response.
     */
    private function responseJson($data)
    {
        // Apply the response transformer if one is set and callable
        if (is_callable($this->responseTransformer)) {
            $data = call_user_func($this->responseTransformer, $data);
        }

        // Ensure the response contains a status, defaulting to 'success'
        if (!isset($data['status'])) {
            $data['status'] = 'success';
        }

        // Ensure the response contains a message, defaulting to an empty string
        if (!isset($data['message'])) {
            $data['message'] = '';
        }

        // Set the HTTP response headers for JSON output
        header('Content-Type: application/json');

        // Determine the appropriate HTTP status code based on the response status
        $code = $data['response_code'] ?? ($data['success'] ? 200 : 500);

        // Determine the server protocol (e.g., HTTP/1.1, HTTP/2) and send the appropriate response headers
        $server_protocol = isset($_SERVER['SERVER_PROTOCOL']) && in_array($_SERVER['SERVER_PROTOCOL'], ['HTTP/1.0', 'HTTP/1.1', 'HTTP/2'], true)
            ? $_SERVER['SERVER_PROTOCOL']
            : 'HTTP/1.1';

        header($server_protocol . ' ' . $code, true, $code);

        // Output the JSON-encoded response and terminate the script
        echo json_encode($data);
        exit();
    }
}