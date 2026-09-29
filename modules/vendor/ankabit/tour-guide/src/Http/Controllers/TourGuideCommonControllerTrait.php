<?php

namespace TourGuide\Http\Controllers;

use TourGuide\TourGuideHook;
use TourGuide\TourGuideUtils;

trait TourGuideCommonControllerTrait
{
    /**
     * Manage data statistic reporting.
     * Might only support POST requests.
     *
     * @param string $action
     * @return
     */
    function user($action)
    {
        $handler = $this->handleUserActions($action);
        return $this->responseJson($handler);
    }

    /**
     * Handle tour guide setup backend actions
     *
     * @param string $id The tour guide id
     * @param string $action The action being performed
     * @return void
     */
    public function setup($id, $action = 'start')
    {
        $id = (int)$id;
        $tourSetupId =  tourGuideHelper()->getSetupSessionId();
        $hasFullAccess = $this->staffCan('edit');

        // Require edit permission
        if (!$hasFullAccess) {

            // Allow saving/stop from any where for active session tour id
            $hasPartialAccess = ($action == 'save' || $action == 'stop') && !empty($tourSetupId);
            if (!$hasPartialAccess)
                $this->ensureStaffCan("edit");
        }

        $portal = TourGuideUtils::sanitizeData($_GET['portal'] ?? '');

        TourGuideHook::applyFilter("before_handle_setup", $action, $portal);

        // Run handler
        $handler = $this->handleSetup($id, $action);

        TourGuideHook::applyFilter("after_handle_setup", $action, $portal);

        if ($this->isAJAX()) {

            return $this->responseJson($handler);
        }

        if (!empty($handler['message']))
            $this->setAlert($handler['status'], $handler['message']);

        $defaultReturnUrl = tourGuideHelper()->adminUrl();
        $returnUrl = TourGuideHook::applyFilter("after_handle_setup_return_url", [
            'url' => $defaultReturnUrl,
            'hasFullAccess' => $hasFullAccess,
            'action' => $action,
            'portal' => $portal
        ]);

        $this->redirect(empty($returnUrl) ? $defaultReturnUrl : $returnUrl);
    }
}