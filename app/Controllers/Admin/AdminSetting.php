<?php

namespace App\Controllers\Admin;

use App\Constant\Constants;
use App\Controllers\BaseController;
use App\Models\DynamicModel;
use App\Models\SoftsolModel;
use App\Models\User;
use Tests\Support\Models\UserModel;

class AdminSetting extends BaseController
{

    protected $parentId = null;
    protected $data;
    protected $db;
    protected $dModel;
    protected $session;
    protected $thumbnail;

    public function __construct()
    {
        helper('form');
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
        $this->dModel = new DynamicModel();
        $this->thumbnail = true;
    }

    public function index()
    {
    }

    /* this method is for add softsol data
    $routes->add('admin/add-softsol-data','Admin\AdminSetting::addSoftSolData');

     */
    public function addSoftSolData()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        if (session()->has('sstatus')) {
            $this->data['fmsg'] = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }
        session()->remove('sfmsg', 'sstatus');

        if ($this->request->getPost()) {
            $pageName = $this->request->getVar('page_name');
            $pData = $this->request->getPost();
            $rules = [
                "page_name" => 'required', //you can add also condition here
                'controller_name' => 'required|is_unique[softsol_data.controller_name]',
                "page_title" => 'required',
                "meta_key_word" => 'required',
                "description" => 'required',

            ];
            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/add-softsol-data'));
            }

            $this->db->transStart();
            $cData = array(
                'page_name' => $pData['page_name'],
                'controller_name' => $pData['controller_name'],
                'page_title' => $pData['page_title'],
                'meta_key_word' => $pData['meta_key_word'],
                'description' => $pData['description'],
            );
            $cId = $this->dModel->dynamicInsertReturnId($cData, 'softsol_data');
            if (!$cId) {
                $this->db->transRollback();
                $ssData = array('sfmsg' => 'Could not add softsol data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/add-softsol-data'));
            }
            for ($i = 1; $i <= $pData['totRow']; $i++) {
                if (isset($_FILES["image_{$i}"])) {
                    if (empty($_FILES["image_{$i}"]['tmp_name'])) {
                        continue;
                    }
                    if ($pageName === 'slide') {
                        $folderPath = "softsol-image/slide/";
                        $targetHeight = Constants::SLIDE_HEIGHT;
                        $targetWidth = Constants::SLIDE_WIDTH;
                    } elseif ($pageName === 'banner') {
                        $folderPath = "softsol-image/page-image/";
                        $targetHeight = Constants::BANNER_HEIGHT;
                        $targetWidth = Constants::BANNER_WIDTH;
                    } elseif ($pageName === 'home') {
                        $folderPath = "softsol-image/page-image/";
                        $targetHeight = Constants::CONTENT_HEIGHT;
                        $targetWidth = Constants::CONTENT_WIDTH;
                    } else {
                        $folderPath = "softsol-image/page-image/";
                        $targetHeight = Constants::PAGE_HEIGHT;
                        $targetWidth = Constants::PAGE_WIDTH;
                    }

                    $imageName = $this->dModel->imageUpload($_FILES["image_{$i}"], 'bizc/' . $folderPath, $targetWidth, $targetHeight, $this->thumbnail);
                    $img = $folderPath . $imageName;
                    $imgData = array(
                        'page_name' => $pData['page_name'],
                        'page_id' => $cId,
                        'file_name' => $img,
                        'image_title' => null,
                        'thumbnail' => $this->thumbnail ? $imageName : null,
                    );
                    $imgId = $this->dModel->dynamicInsertReturnId($imgData, 'images');
                    if (!$imgId) {
                        $this->db->transRollback();
                        $ssData = array('sfmsg' => 'Image could not be uploaded!!!', 'sstatus' => 'red');
                        session()->set($ssData);
                        return redirect()->to(base_url('admin/add-softsol-data'));
                    }
                }
            }
            $this->db->transCommit();
            $ssData = array('sfmsg' => 'Softsol data added successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/add-softsol-data'));
        }
        $this->data['formUrl'] = BASE_URL . 'admin/add-softsol-data';
        return view('layout/admin/add-softsol-data', $this->data);
    }

    /* this method is for update softsol data
    $routes->post('admin/edit-softsol-data', 'Admin\AdminSetting::updateSoftSolData');

     */
    public function updateSoftSolData()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        $id = $this->request->getVar('id');

        if (session()->has('sstatus')) {
            $this->data['fmsg'] = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }

        session()->remove('sfmsg', 'sstatus');
        $softsolData = $this->dModel->dynamicCheckExist(['id' => $id], 'softsol_data');

        if (!$softsolData) {
            $ssData = array('sfmsg' => 'Softsol data not found!!!', 'sstatus' => 'red');
            session()->set($ssData);
            return redirect()->to(base_url('admin/edit-softsol-data/' . $id));
        }

        if ($this->request->getPost()) {
            $controllerName = $this->request->getVar('controller_name');
            $pData = $this->request->getPost();
            $rules = [
                "page_name" => 'required',
                'controller_name' => 'required',
                "page_title" => 'required',
                "meta_key_word" => 'required',
                "description" => 'required',
            ];

            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/edit-softsol-data/' . $id));
            }

            $cData = array(
                'page_name' => $pData['page_name'],
                'controller_name' => $pData['controller_name'],
                'page_title' => $pData['page_title'],
                'meta_key_word' => $pData['meta_key_word'],
                'description' => $pData['description'],
            );

            $updated = $this->dModel->dynamicUpdate(['id' => $id], 'softsol_data', $cData);
            if (!$updated) {
                $ssData = array('sfmsg' => 'Could not update softsol data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/edit-softsol-data/' . $id));
            }

            for ($i = 1; $i <= $pData['totRow']; $i++) {
                if (isset($_FILES["image_{$i}"])) {
                    if (empty($_FILES["image_{$i}"]['tmp_name'])) {
                        continue;
                    }

                    $pageName = $pData['page_name'];
                    if ($pageName === 'slide') {
                        $folderPath = "softsol-image/slide/";
                        $targetHeight = Constants::SLIDE_HEIGHT;
                        $targetWidth = Constants::SLIDE_WIDTH;
                    } elseif ($pageName === 'banner') {
                        $folderPath = "softsol-image/page-image/";
                        $targetHeight = Constants::BANNER_HEIGHT;
                        $targetWidth = Constants::BANNER_WIDTH;
                    } elseif ($pageName === 'home') {
                        $folderPath = "softsol-image/page-image/";
                        $targetHeight = Constants::CONTENT_HEIGHT;
                        $targetWidth = Constants::CONTENT_WIDTH;
                    } else {
                        $folderPath = "softsol-image/page-image/";
                        $targetHeight = Constants::PAGE_HEIGHT;
                        $targetWidth = Constants::PAGE_WIDTH;
                    }

                    $imageName = $this->dModel->imageUpload($_FILES["image_{$i}"], 'bizc/' . $folderPath, $targetWidth, $targetHeight, $this->thumbnail);
                    $img = $folderPath . $imageName;
                    $imgData = array(
                        'page_name' => $pData['page_name'],
                        'page_id' => $id, // Update the image for the specific page ID
                        'file_name' => $img,
                        'image_title' => null,
                        'thumbnail' => $this->thumbnail ? $imageName : null,
                    );
                    $imgId = $this->dModel->dynamicInsertReturnId($imgData, 'images');
                    if (!$imgId) {
                        $ssData = array('sfmsg' => 'Image could not be uploaded!!!', 'sstatus' => 'red');
                        session()->set($ssData);
                        return redirect()->to(base_url('admin/edit-softsol-data/' . $id));
                    }
                }
            }

            $ssData = array('sfmsg' => 'Softsol data updated successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/softsol-data-list/' . $controllerName));
        }

        $this->data['softsolData'] = $softsolData;
        $this->data['formUrl'] = BASE_URL . 'admin/edit-softsol-data';
        return view('layout/admin/edit-form-data.php', $this->data);
    }

    /* Add Project Data  */
    public function addProjectData()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        if (session()->has('sstatus')) {
            $this->data['fmsg'] = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }
        session()->remove('sfmsg', 'sstatus');

        $this->data['pCategory'] = $this->dModel->selectAllData('project_category', 'project_category', 'ASC');
        if ($this->request->getPost()) {
            $pData = $this->request->getPost();
            $rules = [
                "category_id" => 'required', //you can add also condition here
                'project_name' => 'required|is_unique[softsol_data.controller_name]',
                "title" => 'required',
                "description" => 'required',

            ];
            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/add-project-data'));
            }

            $this->db->transStart();
            $cData = array(
                'category_id' => $pData['category_id'],
                'project_name' => $pData['project_name'],
                'title' => $pData['title'],
                'description' => $pData['description'],
                'project_link' => $pData['project_link'],
            );
            $cId = $this->dModel->dynamicInsertReturnId($cData, 'project_info');

            if (!$cId) {
                $this->db->transRollback();
                $ssData = array('sfmsg' => 'Could not add project data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/add-project-data'));
            }

            for ($i = 1; $i <= $pData['totRow']; $i++) {
                if (isset($_FILES["image_{$i}"])) {
                    if (empty($_FILES["image_{$i}"]['tmp_name'])) {
                        continue;
                    }

                    $folderPath = "softsol-image/project/";
                    $targetHeight = Constants::PROJECT_HEIGHT;
                    $targetWidth = Constants::PROJECT_WIDTH;

                    $imageName = $this->dModel->imageUpload($_FILES["image_{$i}"], 'bizc/' . $folderPath, $targetWidth, $targetHeight, $this->thumbnail);
                    $img = $folderPath . $imageName;
                    $imgData = array(
                        'page_name' => Constants::PROJECT_PAGE_NAME,
                        'page_id' => $cId,
                        'file_name' => $img,
                        'image_title' => null,
                        'thumbnail' => $this->thumbnail ? $imageName : null,
                    );
                    $imgId = $this->dModel->dynamicInsertReturnId($imgData, 'images');
                    if (!$imgId) {
                        $this->db->transRollback();
                        $ssData = array('sfmsg' => 'Image could not be uploaded!!!', 'sstatus' => 'red');
                        session()->set($ssData);
                        return redirect()->to(base_url('admin/add-softsol-data'));
                    }
                }
            }

            $this->db->transCommit();
            $ssData = array('sfmsg' => 'Project data added successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/add-project-data'));
        }
        $this->data['formUrl'] = BASE_URL . 'admin/add-project-data';
        return view('layout/admin/add-project-data.php', $this->data);
    }

    /* this is for active deactive softsol data images */
    public function actDecSoftsolData($controllerName = null, $id = null, $actDec = null)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $dynamic_model = new DynamicModel();
        $dynamic_model->dynamicUpdate(['id' => $id], 'softsol_data', ['is_active' => $actDec]);
        $dynamic_model->dynamicUpdate(['page_id' => $id], 'images', ['is_active' => $actDec]);
        $controllerName === 'slide' ? 'slide' : $controllerName;
        return redirect()->to('admin/softsol-data-list/' . $controllerName);
    }

    /* this is for active deactive project images */
    public function actDecProjectData($id = null, $actDec = null)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('admin/login');
        }
        $dynamic_model = new DynamicModel();
        $dynamic_model->dynamicUpdate(['id' => $id], 'project_info', ['is_active' => $actDec]);
        $dynamic_model->dynamicUpdate(['page_id' => $id], 'images', ['is_active' => $actDec]);
        return redirect()->to('admin/project-data-list');
    }

    /* this for update project data  */

    public function updateProjectData()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        $id = $this->request->getVar('id');
        if (session()->has('sstatus')) {
            $this->data['fmsg'] = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }

        session()->remove('sfmsg', 'sstatus');

        $projectData = $this->dModel->dynamicCheckExist(['id' => $id], 'project_info');

        if (!$projectData) {
            $ssData = array('sfmsg' => 'Project data not found!!!', 'sstatus' => 'red');
            session()->set($ssData);
            return redirect()->to(base_url('admin/edit-project-data/' . $id));
        }

        // Load project categories for dropdown
        $this->data['pCategory'] = $this->dModel->selectAllData('project_category', 'project_category', 'ASC');

        if ($this->request->getPost()) {
            $pData = $this->request->getPost();
            $rules = [
                "category_id" => 'required',
                'project_name' => 'required', // Make sure to exclude the current project's ID from the unique check
                "title" => 'required',
                "description" => 'required',
            ];

            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/edit-project-data/' . $id));
            }

            // Update project data
            $cData = array(
                'category_id' => $pData['category_id'],
                'project_name' => $pData['project_name'],
                'title' => $pData['title'],
                'description' => $pData['description'],
                'project_link' => $pData['project_link'],
            );

            $updated = $this->dModel->dynamicUpdate(['id' => $id], 'project_info', $cData);

            if (!$updated) {
                $ssData = array('sfmsg' => 'Could not update project data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/edit-project-data/' . $id));
            }

            // Update project images (if any)
            for ($i = 1; $i <= $pData['totRow']; $i++) {
                if (isset($_FILES["image_{$i}"])) {
                    if (empty($_FILES["image_{$i}"]['tmp_name'])) {
                        continue;
                    }
                    // Add your image upload logic here
                    $folderPath = "softsol-image/project/";
                    $targetHeight = Constants::PROJECT_HEIGHT;
                    $targetWidth = Constants::PROJECT_WIDTH;

                    $imageName = $this->dModel->imageUpload($_FILES["image_{$i}"], 'bizc/' . $folderPath, $targetWidth, $targetHeight, $this->thumbnail);
                    $img = $folderPath . $imageName;
                    $imgData = array(
                        'page_name' => Constants::PROJECT_PAGE_NAME,
                        'page_id' => $id, // Update the image for the specific project ID
                        'file_name' => $img,
                        'image_title' => null,
                        'thumbnail' => $this->thumbnail ? $imageName : null,
                    );
                    $imgId = $this->dModel->dynamicInsertReturnId($imgData, 'images');

                    if (!$imgId) {
                        $ssData = array('sfmsg' => 'Image could not be uploaded!!!', 'sstatus' => 'red');
                        session()->set($ssData);
                        return redirect()->to(base_url('admin/edit-project-data/' . $id));
                    }
                }
            }

            $ssData = array('sfmsg' => 'Project data updated successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/project-data-list'));
        }

        $this->data['projectData'] = $projectData;
        $this->data['formUrl'] = BASE_URL . 'admin/edit-project-data';
        return view('layout/admin/edit-project-data.php', $this->data);
    }



    /* add cateory */

    public function addCategoryData()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        if (session()->has('sstatus')) {
            $this->data['fmsg'] = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }
        session()->remove('sfmsg', 'sstatus');

        if ($this->request->getPost()) {

            $pData = $this->request->getPost();
            $rules = [
                "project_category" => 'required', //you can add also condition here

            ];
            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/add-category-data'));
            }

            $this->db->transStart();
            $cData = array(
                'project_category' => $pData['project_category'],

            );
            $cId = $this->dModel->dynamicInsertReturnId($cData, 'project_category');
            if (!$cId) {
                $this->db->transRollback();
                $ssData = array('sfmsg' => 'Could not add category data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/add-category-data'));
            }

            $this->db->transCommit();
            $ssData = array('sfmsg' => 'Category data added successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/add-category-data'));
        }
        $this->data['formUrl'] = BASE_URL . 'admin/add-category-data';
        return view('layout/admin/add-category-data.php', $this->data);
    }

    /* active dective  category*/
    public function actDecCategoryData($id = null, $actDec = null)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $dynamic_model = new DynamicModel();
        $dynamic_model->dynamicUpdate(['id' => $id], 'project_category', ['is_active' => $actDec]);
        return redirect()->to('admin/category-data-list');
    }



    public function updateCategoryData()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        $id = $this->request->getVar('id');
        if (session()->has('sstatus')) {
            $this->data['fmsg'] = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }
        session()->remove('sfmsg', 'sstatus');
        $projectData = $this->dModel->dynamicCheckExist(['id' => $id], 'project_category');
        if (!$projectData) {
            $ssData = array('sfmsg' => 'Project data not found!!!', 'sstatus' => 'red');
            session()->set($ssData);
            return redirect()->to(base_url('admin/edit-category-data/' . $id));
        }

        // Load project categories for dropdown

        if ($this->request->getPost()) {
            $pData = $this->request->getPost();
            $rules = [
                "project_category" => 'required',
            ];

            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/edit-project-data/' . $id));
            }

            // Update project data
            $cData = array(
                'project_category' => $pData['project_category'],
            );

            $updated = $this->dModel->dynamicUpdate(['id' => $id], 'project_category', $cData);

            if (!$updated) {
                $ssData = array('sfmsg' => 'Could not update category data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/edit-category-data/' . $id));
            }

            $ssData = array('sfmsg' => 'Category data updated successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/category-data-list'));
        }

        $this->data['projectData'] = $projectData;
        $this->data['formUrl'] = BASE_URL . 'admin/edit-category-data';
        return view('layout/admin/edit-category-data.php', $this->data);
    }


    /* and contact data  */
    public function addContactData()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }

        $dModel = new DynamicModel();
        $softsolModel = new SoftsolModel();
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        if (session()->has('sstatus')) {
            $this->data['fmsg'] = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }
        session()->remove('sfmsg', 'sstatus');


        if ($this->request->getPost()) {
            $cData = $this->request->getPost();
            $rules = [
                "title" => 'required', //you can add also condition here
                'details' => 'required',
                "phone" => 'required',
                "email" => 'required',
                "website" => 'required',
                // "desc" => 'required',

            ];
            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/add-contact-data'));
            }
            $cData = array(
                'title' => $cData['title'],
                'details' => $cData['details'],
                'phone' => $cData['phone'],
                'email' => $cData['email'],
                'website' => $cData['website'],
                // 'desc' => $cData['desc'],

            );
            $cId = $dModel->dynamicInsertReturnId($cData, 'contact_info');

            if (!$cId) {
                $ssData = array('sfmsg' => 'Contact information could not send!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/add-contact-data'));
            }

            $ssData = array('sfmsg' => 'Contact Information created Successfully', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/add-contact-data'));
        }
        $this->data['formUrl'] = BASE_URL . 'admin/add-contact-data';
        return view('layout/admin/add-contact-data.php', $this->data);
    }


    /* update contact information */
    public function updateContactInformation()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        $dModel = new DynamicModel();
        $id = $this->request->getVar('id');

        if (session()->has('sstatus')) {
            $this->data['fmsg'] = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }

        session()->remove('sfmsg', 'sstatus');

        $designationData = $this->dModel->dynamicCheckExist(['id' => $id], 'contact_info');


        if (!$designationData) {
            $ssData = array('sfmsg' => 'Contact data not found!!!', 'sstatus' => 'red');
            session()->set($ssData);
            return redirect()->to(base_url('admin/edit-contact-info/' . $id));
        }

        // Load project categories for dropdown

        if ($this->request->getPost()) {
            $pData = $this->request->getPost();
            $rules = [
                "title" => 'required',
                "details" => 'required',
                "phone" => 'required',
                "email" => 'required',
                "website" => 'required',
                // "desc" => 'required',
            ];

            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/edit-contact-info/' . $id));
            }

            // Update project data
            $cData = array(
                "title" => $pData['title'],
                "details" => $pData['details'],
                "phone" =>  $pData['phone'],
                "email" =>  $pData['email'],
                "website" => $pData['website'],
                // "desc" => $pData['desc'],
            );

            $updated = $this->dModel->dynamicUpdate(['id' => $id], 'contact_info', $cData);

            if (!$updated) {
                $ssData = array('sfmsg' => 'Could not update contact information data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/edit-contact-info/' . $id));
            }

            $ssData = array('sfmsg' => 'Contact Data data updated successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/contact-info'));
        }

        $this->data['contactData'] =  $designationData;
        $this->data['formUrl'] = BASE_URL . 'admin/edit-contact-info';
        return view('layout/admin/edit-contact-information.php', $this->data);
    }


    /* add department data */
    public function addDepartmentData()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        if (session()->has('sstatus')) {
            $this->data['fmsg'] = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }
        session()->remove('sfmsg', 'sstatus');

        if ($this->request->getPost()) {

            $pData = $this->request->getPost();
            $rules = [
                "department_name" => 'required|is_unique[office_department.department_name]', //you can add also condition here
                "description" => 'required',
            ];
            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/add-department-data'));
            }
            $cData = array(
                'department_name' => $pData['department_name'],
                'description' => $pData['description'],

            );
            $cId = $this->dModel->dynamicInsertReturnId($cData, 'office_department');
            if (!$cId) {
                $ssData = array('sfmsg' => 'Could not add department data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/add-department-data'));
            }
            $ssData = array('sfmsg' => 'Department data added successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/add-department-data'));
        }
        $this->data['formUrl'] = BASE_URL . 'admin/add-department-data';
        return view('layout/admin/add-department-data.php', $this->data);
    }

    /* update contact information */
    public function updateDepartmentInformation()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        $dModel = new DynamicModel();
        $id = $this->request->getVar('id');
        if (session()->has('sstatus')) {
            $this->data['fmsg'] = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }
        session()->remove('sfmsg', 'sstatus');

        $departmentData = $this->dModel->dynamicCheckExist(['id' => $id], 'office_department');
        if (!$departmentData) {
            $ssData = array('sfmsg' => 'Contact data not found!!!', 'sstatus' => 'red');
            session()->set($ssData);
            return redirect()->to(base_url('admin/edit-department-data/' . $id));
        }

        if ($this->request->getPost()) {
            $pData = $this->request->getPost();
            $rules = [
                "department_name" => 'required', //you can add also condition here
                "description" => 'required',
            ];

            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/edit-department-data/' . $id));
            }

            // Update department data
            $cData = array(
                "department_name" => $pData['department_name'],
                "description" => $pData['description'],
            );
            $updated = $this->dModel->dynamicUpdate(['id' => $id], 'office_department', $cData);
            if (!$updated) {
                $ssData = array('sfmsg' => 'Could not update department information data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/edit-department-data/' . $id));
            }
            $ssData = array('sfmsg' => 'Department Data data updated successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/department-info'));
        }
        $this->data['departmentData'] =  $departmentData;
        $this->data['formUrl'] = BASE_URL . 'admin/edit-department-data';
    }


    /* add designation data  */
    public function addDesignationData()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        if (session()->has('sstatus')) {
            $this->data['fmsg'] = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }
        session()->remove('sfmsg', 'sstatus');

        if ($this->request->getPost()) {

            $pData = $this->request->getPost();
            $rules = [
                "designation" => 'required|is_unique[office_designation.designation]', //you can add also condition here
                "sort_order" => 'required',
                "details" => 'required',

            ];
            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/add-designation-data'));
            }
            $cData = array(
                'designation' => $pData['designation'],
                'sort_order' => $pData['sort_order'],
                'details' => $pData['details'],

            );
            $cId = $this->dModel->dynamicInsertReturnId($cData, 'office_designation');
            if (!$cId) {
                $ssData = array('sfmsg' => 'Could not add designation data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/add-designation-data'));
            }
            $ssData = array('sfmsg' => 'Designation data added successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/add-designation-data'));
        }
        $this->data['formUrl'] = BASE_URL . 'admin/add-designation-data';
        return view('layout/admin/add-designation-data.php', $this->data);
    }

    /* update designation data  */

    public function updateDesignationInformation()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        $dModel = new DynamicModel();
        $id = $this->request->getVar('id');
        if (session()->has('sstatus')) {
            $this->data['fmsg'] = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }
        session()->remove('sfmsg', 'sstatus');

        $designationData = $this->dModel->dynamicCheckExist(['id' => $id], 'office_designation');
        if (!$designationData) {
            $ssData = array('sfmsg' => 'Designation data not found!!!', 'sstatus' => 'red');
            session()->set($ssData);
            return redirect()->to(base_url('admin/edit-designation-data/' . $id));
        }

        if ($this->request->getPost()) {
            $pData = $this->request->getPost();
            $rules = [
                "designation" => 'required', //you can add also condition here
                "sort_order" => 'required',
                "details" => 'required',
            ];

            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/edit-designation-data/' . $id));
            }

            // Update department data
            $cData = array(
                "designation" => $pData['designation'],
                "sort_order" => $pData['sort_order'],
                "details" => $pData['details'],
            );
            $updated = $this->dModel->dynamicUpdate(['id' => $id], 'office_designation', $cData);
            if (!$updated) {
                $ssData = array('sfmsg' => 'Could not update designation information data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/edit-designation-data/' . $id));
            }
            $ssData = array('sfmsg' => 'Designation Data data updated successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/designation-info'));
        }
        $this->data['designationData'] =  $designationData;
        $this->data['formUrl'] = BASE_URL . 'admin/edit-designation-data';
        return view('layout/admin/edit-designation-data.php', $this->data);
    }


    public function addEmployeeInformation()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        if (session()->has('sstatus')) {
            $this->data['fmsg'] = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }
        session()->remove('sfmsg', 'sstatus');

        $this->data['department'] = $this->dModel->selectAllData('office_department', 'department_name', 'ASC');
        $this->data['designation'] = $this->dModel->selectAllData('office_designation', 'designation', 'ASC');
        if ($this->request->getPost()) {
            $pData = $this->request->getPost();
            $rules = [
                "department_id" => 'required', //you can add also condition here
                'designation_id' => 'required',
                "employee_name" => 'required',
                "employee_info" => 'required',
                "phone" => 'required',
                "email" => 'required',
                "address" => 'required',
                "join_date" => 'required',
                "status" => 'required',

            ];
            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/add-employee-data'));
            }

            $this->db->transStart();
            $cData = array(
                'department_id' => $pData['department_id'],
                'designation_id' => $pData['designation_id'],
                'employee_name' => $pData['employee_name'],
                'employee_info' => $pData['employee_info'],
                'phone' => $pData['phone'],
                'email' => $pData['email'],
                'address' => $pData['address'],
                'join_date' => $pData['join_date'],
                'others' => $pData['others'],
                'status' => $pData['status'],

            );
            $cId = $this->dModel->dynamicInsertReturnId($cData, 'office_employee');

            $empHistory = array(
                'emp_id' =>  $cId,
                'department_id' => $pData['department_id'],
                'designation_id' => $pData['designation_id'],
                'start_date' => $pData['join_date'],
                'end_date' => null,
                'note' => $pData['others'],

            );

            $eId = $this->dModel->dynamicInsertReturnId($empHistory, 'office_emp_history');
            for ($i = 1; $i <= $pData['totRow']; $i++) {
                if (isset($_FILES["image_{$i}"])) {
                    if (empty($_FILES["image_{$i}"]['tmp_name'])) {
                        continue;
                    }

                    $folderPath = "softsol-image/employee/";
                    $targetHeight = Constants::EMPLOYEE_HEIGHT;
                    $targetWidth = Constants::EMPLOYEE_WIDTH;

                    $imageName = $this->dModel->imageUpload($_FILES["image_{$i}"], 'bizc/' . $folderPath, $targetWidth, $targetHeight, $this->thumbnail);
                    $img = $folderPath . $imageName;
                    $imgData = array(
                        'page_name' => Constants::EMPLOYEE_PAGE_NAME,
                        'page_id' => $cId,
                        'file_name' => $img,
                        'image_title' => null,
                        'thumbnail' => $this->thumbnail ? $imageName : null,
                    );
                    $imgId = $this->dModel->dynamicInsertReturnId($imgData, 'images');
                    if (!$imgId) {
                        $this->db->transRollback();
                        $ssData = array('sfmsg' => 'Image could not be uploaded!!!', 'sstatus' => 'red');
                        session()->set($ssData);
                        return redirect()->to(base_url('admin/add-employee-data'));
                    }
                }
            }
            if (!$cId) {
                $this->db->transRollback();
                $ssData = array('sfmsg' => 'Could not add employee data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/add-employee-data'));
            }

            /* image for loop will be here */

            $this->db->transCommit();
            $ssData = array('sfmsg' => 'Employee data added successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/add-employee-data'));
        }


        $this->data['formUrl'] = BASE_URL . 'admin/add-employee-data';

        return view('layout/admin/add-employee-data.php', $this->data);
    }


    public  function updateEmployeeDetails()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        $dModel = new DynamicModel();
        $id = $this->request->getVar('id');
        if (session()->has('sstatus')) {
            $this->data['fmsg'] = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }
        session()->remove('sfmsg', 'sstatus');

        $employeData = $this->dModel->dynamicCheckExist(['id' => $id], 'office_employee');

        if (!$employeData) {
            $ssData = array('sfmsg' => 'Employee data not found!!!', 'sstatus' => 'red');
            session()->set($ssData);
            return redirect()->to(base_url('admin/edit-employee-data/' . $id));
        }

        if ($this->request->getPost()) {
            $pData = $this->request->getPost();
            $rules = [
                "department_id" => 'required',
                'designation_id' => 'required',
                "employee_name" => 'required',
                "employee_info" => 'required',
                "phone" => 'required',
                "email" => 'required',
                "address" => 'required',
                "join_date" => 'required',
                "status" => 'required',

            ];

            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/edit-employee-data/' . $id));
            }

            // Update department data
            $cData = array(
                'department_id' => $pData['department_id'],
                'designation_id' => $pData['designation_id'],
                'employee_name' => $pData['employee_name'],
                'employee_info' => $pData['employee_info'],
                'phone' => $pData['phone'],
                'email' => $pData['email'],
                'address' => $pData['address'],
                'join_date' => $pData['join_date'],
                'others' => $pData['others'],
                'status' => $pData['status'],
            );
            if ($cData['department_id'] != $employeData[0]['department_id'] || $cData['designation_id'] != $employeData[0]['designation_id']) {
                $currentDateTime = new \DateTime();
                $formattedDate = $currentDateTime->format('Y-m-d');
                $dModel->dynamicUpdate(['emp_id' => $id], 'office_emp_history', ['end_date' => $formattedDate]);

                $empHistory = array(
                    'emp_id' => $id,
                    'department_id' => $pData['department_id'],
                    'designation_id' => $pData['designation_id'],
                    'start_date' => $pData['join_date'],
                    'end_date' => null,
                    'note' => $pData['others'],
                );
                $empHistoryID = $dModel->dynamicInsertReturnId($empHistory, 'office_emp_history');
            }

            if ($pData['status'] == 2 || $pData['status'] == 3) {
                $currentDateTime = new \DateTime();
                $formattedDate = $currentDateTime->format('Y-m-d');
                $dModel->dynamicUpdate(['emp_id' => $id], 'office_emp_history', ['end_date' => $formattedDate]);
            }
            $updated = $this->dModel->dynamicUpdate(['id' => $id], 'office_employee', $cData);
            if (!$updated) {
                $ssData = array('sfmsg' => 'Could not update employee information data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/edit-employee-data/' . $id));
            }
            for ($i = 1; $i <= $pData['totRow']; $i++) {
                if (isset($_FILES["image_{$i}"])) {
                    if (empty($_FILES["image_{$i}"]['tmp_name'])) {
                        continue;
                    }

                    // Add your image upload logic here

                    $folderPath = "softsol-image/employee/";
                    $targetHeight = Constants::EMPLOYEE_HEIGHT;
                    $targetWidth = Constants::EMPLOYEE_WIDTH;

                    $imageName = $this->dModel->imageUpload($_FILES["image_{$i}"], 'bizc/' . $folderPath, $targetWidth, $targetHeight, $this->thumbnail);
                    $img = $folderPath . $imageName;
                    $imgData = array(
                        'page_name' => Constants::EMPLOYEE_PAGE_NAME,
                        'page_id' => $id, // Update the image for the specific project ID
                        'file_name' => $img,
                        'image_title' => null,
                        'thumbnail' => $this->thumbnail ? $imageName : null,
                    );
                    $imgId = $this->dModel->dynamicInsertReturnId($imgData, 'images');

                    if (!$imgId) {
                        $ssData = array('sfmsg' => 'Image could not be uploaded!!!', 'sstatus' => 'red');
                        session()->set($ssData);
                        return redirect()->to(base_url('admin/edit-employee-data/' . $id));
                    }
                }
            }
            $ssData = array('sfmsg' => 'Employee  Data data updated successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/employee-info'));
        }
        $this->data['employeData'] =   $employeData;
        dd($this->data);
        $this->data['formUrl'] = BASE_URL . 'admin/edit-employee-data';
        return view('layout/admin/edit-employee-data.php', $this->data);
    }


    public function register()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        if (session()->has('sstatus')) {
            $this->data['fmsg']           = session()->get('sfmsg');
            $this->data['status']         = session()->get('sstatus');
        }
        session()->remove('sfmsg', 'sstatus');

        if ($this->request->getPost()) {
            $userData = $this->request->getPost();
            // dd($userData['password']);
            $password =  $this->request->getVar('password');
            // dd($password);
            $password = password_hash($password, PASSWORD_BCRYPT);
            $rules = [
                "full_name" => 'required', //you can add also condition here
                "email" => 'required|valid_email|is_unique[users.email]',
                "password" => 'required',
            ];
            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('register'));
            }


            $this->db->transStart();
            $cData = array(
                'full_name' => $userData['full_name'],
                'email'     => $userData['email'],
                'password'  => $password,

            );
            $cId = $this->dModel->dynamicInsertReturnId($cData, 'users');
            if (!$cId) {
                $this->db->transRollback();
                $ssData = array('sfmsg' => 'Could not add users data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('resgister'));
            }
            $this->db->transCommit();
            $ssData = array('sfmsg' => 'Users data added successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('register'));
        }

        $this->data['formUrl'] = BASE_URL . 'register';
        return view('layout/admin/register/register', $this->data);
    }

    public function login()
    {

        $this->data['fmsg'] = '';
        $this->data['status'] = '';

        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin'));
        }

        $this->data['formUrl'] = BASE_URL . 'admin/login';
        return view('layout/admin/login/login', $this->data);
    }

    public function processLogin()
    {
        $this->data['fmsg'] = '';
        $this->data['status'] = '';

        $userModel = new User();

        if (session()->has('sstatus')) {
            $this->data['fmsg']  = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }
        session()->remove('sfmsg', 'sstatus');

        if ($this->request->getPost()) {
            $loginData = $this->request->getPost();
            $rules = [
                "email" => 'required|valid_email|is_not_unique[users.email]',
                "password" => 'required',
            ];
            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/login'));
            }
            $email = $loginData['email'];
            $password = $loginData['password'];


            $result = $userModel->where('email', $email)->first();
            // dd($result['password']);

            //    dd($password, $result['password']);

            if ($result) {
                if (password_verify($password, $result['password'])) {
                    $userData = [
                        'id' => $result['id'],
                        'email' => $result['email'],
                        'isLoggedIn' => true,
                    ];
                    session()->set($userData);
                    return redirect()->to(base_url('admin'));
                } else {
                    return redirect()->back()->with('error', 'Invalid username or password');
                }
            }
        }
    }

    public function addStatesData()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $softsolModel = new SoftsolModel();
        $this->data['fmsg'] = '';
        $this->data['status'] = '';

        if (session()->has('sstatus')) {
            $this->data['fmsg']  = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }
        session()->remove('sfmsg', 'sstatus');
        $this->data['states'] = $softsolModel->selectStatesByParentId('state', 'name', 'ASC', 0);

        if ($this->request->getPost()) {
            $statesData = $this->request->getPost();


            $rules = [
                "name" => 'required|is_unique[state.name]',
                "sort" => 'required',
            ];
            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/add-states'));
            }

            $this->db->transStart();
            $cData = [
                'parent_id' => $statesData['parent_id'] ?? null,
                'name'     => $statesData['name'],
                'reference' => $statesData['reference'],
                'sort'  => $statesData['sort']

            ];
            $cId = $this->dModel->dynamicInsertReturnId($cData, 'state');
            if (!$cId) {
                $this->db->transRollback();
                $ssData = array('sfmsg' => 'Could not add states data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/add-states'));
            }
            $this->db->transCommit();
            $ssData = array('sfmsg' => 'States data added successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/add-states'));
        }



        // dd($this->data);

        $this->data['formUrl'] = BASE_URL . '/admin/add-states';
        $this->data['states'] = $this->statesTree();
        return view('layout/admin/add-states-data', $this->data);
    }


    public function createStatesTree( $currentId = null,$parent_id = 0, $sub_mark = '', &$options = '')
    {

        $softsolModel = new SoftsolModel();
        $result = $softsolModel->selectStatesByParentId('state', 'name', 'ASC', $parent_id);
        $arraylen = count($result);

        // dd($result);

        if ($arraylen > 0) {
            for ($i = 0; $i <  $arraylen; $i++) {
                $row = $result[$i];
                if($this->parentId == $row['id']) {
                    $options .= '<option selected value="' . $row['id'] . '">' . $sub_mark . $row['name'] . '</option>';

                } else {

                    $options .= '<option value="' . $row['id'] . '">' . $sub_mark . $row['name'] . '</option>';
                }
                $this->createStatesTree($currentId,$row['id'], $sub_mark . '---', $options);
            }
        }
        return $options;
    }
    public function statesTree($id =null)
    {
        return  $this->createStatesTree($id);
    }


    /* edit states data */

    public function editStatesData($id = null){

        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        $this->data['statesData'] = $this->dModel->dynamicCheckExist(['id' => $id], 'state');
        $this->parentId =  $this->data['statesData'][0]['parent_id'];
        $this->data['states'] = $this->statesTree($id);
        
        $this->data['formUrl'] = BASE_URL . '/admin/edit-states-data';
        return view('layout/admin/edit-states-data', $this->data);


        
    }

    public function updateStatesData() {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        if (session()->has('sstatus')) {
            $this->data['fmsg'] = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }
        session()->remove('sfmsg', 'sstatus');
        $id = $this->request->getVar('id');
         $statesData = $this->dModel->dynamicCheckExist(['id' => $id], 'state');
        if (!$statesData) {
            $ssData = array('sfmsg' => 'States data not found!!!', 'sstatus' => 'red');
            session()->set($ssData);
            return redirect()->to(base_url('admin/edit-states-data/' . $id));
        }

        if($this->request->getPost()) {
            $sData = $this->request->getPost();
            $rules = [
                "name" => 'required',
                "sort" => 'required',
            ];
            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/edit-states-data/' . $id));
            }

            if($statesData[0]['parent_id'] !== $sData['parent_id']) {
                $updateStates = [
                    'parent_id' =>  $sData['parent_id'],
                    'name'     => $sData['name'],
                    'reference' => $sData['reference'],
                    'sort'  => $sData['sort']
                ];

            } else {
                $updateStates = [
                    'name'     => $sData['name'],
                    'reference' => $sData['reference'],
                    'sort'  => $sData['sort']
                ];
            }
       
            $updated = $this->dModel->dynamicUpdate(['id' => $id], 'state', $updateStates);
            if (!$updated) {
                $ssData = array('sfmsg' => 'Could not update states information data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/edit-states-data/' . $id));
            }
            $ssData = array('sfmsg' => 'States  Data data updated successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/states-info'));
        }
        $this->data['statesData'] =   $statesData;

        
        $this->data['formUrl'] = BASE_URL . 'admin/edit-states-data';
        return view('layout/admin/edit-states-data.php', $this->data);

    }
}
