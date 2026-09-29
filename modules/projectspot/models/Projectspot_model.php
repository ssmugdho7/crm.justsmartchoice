<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Projectspot_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addProjectCategory($data)
    {
        $this->db->insert(db_prefix() . 'projectspot_categories', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function getProjectCategory($category_id)
    {
        $this->db->where('id', $category_id);
        return $this->db->get(db_prefix() . 'projectspot_categories')->row();
    }

    public function getProjectCategories($active='')
    {
        if (!empty($active)) {
            $this->db->where('is_enabled', '1');
        }
        return $this->db->get(db_prefix() . 'projectspot_categories')->result_array();
    }

    public function updateProjectCategory($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'projectspot_categories', $data);

        return $this->db->affected_rows() > 0;
    }

    public function deleteProjectCategory($category_id)
    {

        if (is_reference_in_table('category_id', db_prefix() . 'projectspot_gallery_list', $category_id)) {
            return [
                'referenced' => true,
            ];
        }

        $this->db->where('id', $category_id);
        $this->db->delete(db_prefix() . 'projectspot_categories');

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function addProjectGallery($data)
    {
        $this->db->insert(db_prefix() . 'projectspot_gallery_list', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function getProjectGalleryList($active='')
    {

        $this->db->select();
        $this->db->from(db_prefix() . 'projectspot_gallery_list');
        $this->db->order_by('created_at', 'desc');

        if (!empty($active)) {
            $this->db->where('is_enabled', '1');
        }

        $result = $this->db->get()->result_array();

        if (count($result) > 0) {
            foreach ($result as $key => $project) {
                $result[$key]['images'] = $this->getProjectGalleryListImage($project['id']);
            }
        }

        return $result;
    }

    public function getSingleProjectGalleryListWithImages($project_id)
    {

        $this->db->select();
        $this->db->from(db_prefix() . 'projectspot_gallery_list');
        $this->db->where('id', $project_id);

        $result = $this->db->get()->row();

        $result->images = $this->getProjectGalleryListImage($project_id);

        return $result;
    }

    public function getSingleProjectGalleryList($project_id)
    {
        $this->db->select();
        $this->db->from(db_prefix() . 'projectspot_gallery_list');
        $this->db->where('id', $project_id);

        return $this->db->get()->row();
    }

    public function updateProjectGallery($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'projectspot_gallery_list', $data);

        return $this->db->affected_rows() > 0;
    }

    public function deleteProjectGallery($project_id)
    {
        $this->db->where('id', $project_id);
        $this->db->delete(db_prefix() . 'projectspot_gallery_list');

        $getProjectFiles = $this->getProjectGalleryListImage($project_id);

        if (count($getProjectFiles) > 0) {
            foreach ($getProjectFiles as $file) {
                $this->deleteGalleryListImage($project_id, $file['id'], $file['image_path']);
            }

            $directory = FCPATH . 'modules/projectspot/uploads/gallery_images/' . $project_id . '/';

            if (is_dir($directory)) {
                delete_dir($directory);
            }
        }

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }


    public function addGalleryListImage($data)
    {
        $this->db->insert(db_prefix() . 'projectspot_gallery_list_images', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function getProjectGalleryListImage($project_id)
    {
        $this->db->where('project_id', $project_id);
        $this->db->order_by('image_order', 'asc');

        return $this->db->get(db_prefix() . 'projectspot_gallery_list_images')->result_array();
    }

    public function getGalleryListImage()
    {
        return $this->db->get(db_prefix() . 'projectspot_gallery_list_images')->result_array();
    }

    public function updateGalleryListImage($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'projectspot_gallery_list_images', $data);

        return $this->db->affected_rows() > 0;
    }

    public function deleteGalleryListImage($project_id, $image_id, $image_path)
    {
        $this->db->where('id', $image_id);
        $this->db->delete(db_prefix() . 'projectspot_gallery_list_images');

        $directory = FCPATH . 'modules/projectspot/uploads/gallery_images/' . $project_id . '/'. $image_path;

        if (is_file($directory)) {
            unlink($directory);
        }

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function changeCategoryStatus($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'projectspot_categories', [
            'is_enabled' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function changeProjectStatus($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'projectspot_gallery_list', [
            'is_enabled' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }
}
