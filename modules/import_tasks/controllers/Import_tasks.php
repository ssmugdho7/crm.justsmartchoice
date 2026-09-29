<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Import_tasks extends AdminController
{
    private static $TASKS_IMPORT_FILE_LOCATION = FCPATH . 'uploads/import_tasks/';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('import_tasks_model');
        $this->load->model('tasks_model');
    }

    public function index()
    {
        $this->importedTasks();
    }

    public function import()
    {
        $this->load->library('ImportTasks', [], 'import');
        $dbFields = $this->db->list_fields(db_prefix() . 'tasks');
        array_push($dbFields, 'attachments');
        array_push($dbFields, 'tags');
        array_push($dbFields, 'checklist_items');
        array_push($dbFields, 'cf_estymacja');
        $this->import->setDatabaseFields($dbFields);
        if ($this->input->post('download_sample') === 'true') {
            $this->import->downloadSample();
        }
        if (
            $this->input->post()
            && isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != ''
        ) {
            $importTasks = [
                'filename' => $_FILES['file_csv']['name']
            ];
            $importId = $this->import_tasks_model->store($importTasks);
            $uploadFilePath = $this->uploadImportFile($importId);
            $this->import->setSimulation($this->input->post('simulate'))
                ->setTemporaryFileLocation($uploadFilePath)
                ->setFilename($_FILES['file_csv']['name'])
                ->perform();
            $data['total_rows_post'] = $this->import->totalRows();
            $importedTasks = $this->import->totalImported();
            if (!$this->import->isSimulation()) {
                set_alert('success', _l('import_total_imported', $importedTasks));
            }
            $this->import_tasks_model->setTasksCount($importId, $importedTasks);
            redirect(admin_url('import_tasks'));
        }
        $data['totalRows'] = $this->import->totalRows();
        $this->load->view('import', $data);
    }

    private function uploadImportFile($importId)
    {
        $uploadPath = self::$TASKS_IMPORT_FILE_LOCATION . $importId;
        if (!is_dir(self::$TASKS_IMPORT_FILE_LOCATION)) {
            mkdir(self::$TASKS_IMPORT_FILE_LOCATION);
        }
        _maybe_create_upload_path($uploadPath);
        $uploadFilePath = $uploadPath . '/' . $_FILES['file_csv']['name'];
        move_uploaded_file($_FILES['file_csv']['tmp_name'], $uploadFilePath);
        return $uploadFilePath;
    }

    public function import_tasks()
    {
        $this->importedTasks();
    }

    private function importedTasks()
    {
        $data['importedTasksHistory'] = $this->import_tasks_model->getAll();
        $this->load->view('imported_tasks', $data);
    }

    public function add()
    {
        if (!is_admin()) {
            access_denied(_l('only_admin'));
        }
        if ($this->input->post()) {
            $id = $this->import_tasks_model->store($this->input->post());
            if ($id) {
                set_alert('success', _l('added_successfully', _l('Tasks imported')));
            }
        }
        redirect(admin_url('import_tasks'));
    }

    public function delete($id)
    {
        if (!is_admin()) {
            access_denied(_l('only_admin'));
        }
        $result = $this->import_tasks_model->delete($id);
        if (
            $result
            && is_dir(self::$TASKS_IMPORT_FILE_LOCATION . $id)
            && $this->deleteDirectory(self::$TASKS_IMPORT_FILE_LOCATION . $id)
        ) {
            set_alert('success', _l('Tasks import file removed successfully'));
        } else {
            set_alert('warning', _l('Failed to delete tasks import file'));
        }
        redirect(admin_url('import_tasks'));
    }

    private function deleteDirectory($directory)
    {
        if (!file_exists($directory)) {
            return true;
        }
        if (!is_dir($directory)) {
            return unlink($directory);
        }
        foreach (scandir($directory) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!$this->deleteDirectory($directory . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return rmdir($directory);
    }
}
