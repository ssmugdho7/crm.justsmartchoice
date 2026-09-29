<?php

namespace TourGuide\Http\Controllers;

/**
 * TourGuideAdminControllerTrait
 *
 * Handles CRUD operations for the tour guide feature within the admin panel.
 * Provides functionality for viewing, creating, updating, and deleting tour guides.
 */
trait TourGuideAdminControllerTrait
{
    /**
     * Display the list of tour guides.
     *
     * Ensures the user has permission to view tour guides before loading the view.
     */
    public function index()
    {
        $this->ensureStaffCan("view");

        $data['title'] = tourGuideHelper()->translate('tour_guide');
        $data['tour_guides'] = $this->tourGuideReposiotry->getAll();
        return $this->loadView('admin/manage', $data);
    }

    /**
     * Display the form for creating or editing a tour guide.
     *
     * @param int $id Optional ID of the tour guide to edit. If not provided, the form is for creating a new guide.
     */
    public function form($id = '')
    {
        $id = (int)$id;

        // Check if the user has permission to create or edit based on the presence of an ID
        $this->ensureStaffCan($id ? 'edit' : 'create');

        $handler = $this->handleForm($id);
        if ($handler['is_post']) {
            $this->setAlert($handler['status'], $handler['message']);

            $url =  tourGuideHelper()->adminUrl();
            if (!$handler['success']) {
                $url = $this->uriString();
            }
            $this->redirect($url);
        }

        $data = $handler['data'];

        return $this->loadView('admin/form', $data);
    }

    /**
     * Delete a tour guide by ID.
     *
     * @param int $id ID of the tour guide to delete.
     */
    public function delete($id)
    {
        $this->ensureStaffCan('delete');

        $handler = $this->handleDelete($id);

        if ($this->isAJAX()) {

            return $this->responseJson($handler);
        }

        $this->setAlert($handler['status'], $handler['message']);
        $this->redirect(tourGuideHelper()->adminUrl());
    }

    /**
     * Clone a tour guide by ID.
     *
     * @param int $id ID of the tour guide to clone.
     */
    public function clone($id)
    {
        $this->ensureStaffCan('create');

        $handler = $this->handleClone($id);

        if ($this->isAJAX()) {

            return $this->responseJson($handler);
        }

        $this->setAlert($handler['status'], $handler['message']);
        $this->redirect(tourGuideHelper()->adminUrl());
    }

    /**
     * Translate a tour identified by the $id
     *
     * @param string $id
     * @return void
     */
    public function translate($id)
    {
        // Require edit permission
        $this->ensureStaffCan('edit');

        $handler = $this->handleTranslate($id);
        if ($this->isAJAX()) {

            return $this->responseJson($handler);
        }

        if (!empty($handler['redirect'])) {
            if (!empty($handler['message']))
                $this->setAlert($handler['status'], $handler['message']);

            $this->redirect($handler['redirect']);
        }

        $data = $handler['data'];
        $data['handler'] = $handler;

        return $this->loadView('admin/translate', $data);
    }
}