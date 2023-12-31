<?php

namespace App\Controllers\Admin;

use App\Constant\Constants;
use App\Controllers\BaseController;
use App\Models\DynamicModel;


class Admin extends BaseController
{
    protected $parentId = null;
    protected $data;
    protected $db;
    protected $dModel;
    protected $session;


    public function __construct()
    {
        helper('form');
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
        $this->dModel = new DynamicModel();
    }
    public function index()
    {
        $this->data['slide'] = [];
        $this->data['title'] = 'test';
        return view('layout/admin/admin-home', $this->data);
    }


    /* add category data  */

    public function addCategoryData()
    {

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
                "category_name" => 'required|is_unique[category.category_name]', //you can add also condition here

            ];
            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/add-category'));
            }

            $this->db->transStart();
            $cData = array(
                'category_name' => $pData['category_name'],
                'description' => $pData['description'],
                'status' => $pData['status'],

            );

            $cId = $this->dModel->dynamicInsertReturnId($cData, 'category');
            if (!$cId) {
                $this->db->transRollback();
                $ssData = array('sfmsg' => 'Could not add category data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/add-category'));
            }

            $this->db->transCommit();
            $ssData = array('sfmsg' => 'Category data added successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/add-category'));
        }
        $this->data['formUrl'] = BASE_URL . 'admin/add-category';
        return view('layout/admin/add-category.php', $this->data);
    }

    /* showCategory */
    public function showCategory()
    {

        $dModel = new DynamicModel();
        $this->data['data'] = $dModel->selectAllData('category', 'id', 'ASC');
        return view('layout/admin/category-information', $this->data);
    }

    /* delete category */
    public function deleteCategory($id = null)
    {

        $dModel = new DynamicModel();
        $dId = $dModel->dynamicDelete(['id' => $id], 'category');
        if ($dId) {
            return redirect()->to(base_url('admin/category-info'));
        }
    }

    /* edit category */
    public function editCategoryData($id = null)
    {


        $dModel = new DynamicModel();
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        $this->data['info'] = $dModel->dynamicCheckExist(['id' => $id], 'category');
        $this->data['formUrl'] = BASE_URL . 'admin/edit-category';
        return view('layout/admin/edit-category', $this->data);
    }

    public function updateCategory()
    {
        $id = $this->request->getPost('id');

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
                "category_name" => 'required', //you can add also condition here

            ];
            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/edit-category/' . $id));
            }

            $this->db->transStart();
            $cData = array(
                'category_name' => $pData['category_name'],
                'description' => $pData['description'],
                'status' => $pData['status'],

            );

            $cId = $this->dModel->dynamicUpdate(['id' => $id], 'category', $cData);
            if (!$cId) {
                $this->db->transRollback();
                $ssData = array('sfmsg' => 'Could not update category data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/edit-category/' . $id));
            }

            $this->db->transCommit();
            $ssData = array('sfmsg' => 'Category data updated successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/category-info'));
        }
        $this->data['formUrl'] = BASE_URL . 'admin/edit-category';
        return view('layout/admin/edit-category.php', $this->data);
    }


    public function addItemData()
    {

        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        if (session()->has('sstatus')) {
            $this->data['fmsg'] = session()->get('sfmsg');
            $this->data['status'] = session()->get('sstatus');
        }
        session()->remove('sfmsg', 'sstatus');
        $this->data['category'] = $this->dModel->selectAllData('category', 'id', 'ASC');
        if ($this->request->getPost()) {

            $pData = $this->request->getPost();
            $rules = [
                "title" => 'required', //you can add also condition here

            ];
            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/add-item'));
            }

            $this->db->transStart();
            $cData = array(
                'cat_id' => $pData['cat_id'],
                'title' => $pData['title'],
                'description' => $pData['description'],
                'status' => $pData['status'],

            );

            $cId = $this->dModel->dynamicInsertReturnId($cData, 'item');
            if (!$cId) {
                $this->db->transRollback();
                $ssData = array('sfmsg' => 'Could not add item data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/add-item'));
            }

            $this->db->transCommit();
            $ssData = array('sfmsg' => 'Item data added successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/add-item'));
        }
        // dd($this->data);
        $this->data['formUrl'] = BASE_URL . 'admin/add-item';
        return view('layout/admin/add-item.php', $this->data);
    }
    public function showItem()
    {


        $this->data['data'] = $this->dModel->itemData();
        // dd($this->data);
        return view('layout/admin/item-information', $this->data);
    }
    public function deleteItem($id = null)
    {

        $dModel = new DynamicModel();
        $dId = $dModel->dynamicDelete(['id' => $id], 'item');
        if ($dId) {
            return redirect()->to(base_url('admin/item-info'));
        }
    }
    public function editItemData($id = null)
    {


        $dModel = new DynamicModel();
        $this->data['fmsg'] = '';
        $this->data['status'] = '';
        $this->data['category'] = $this->dModel->selectAllData('category', 'id', 'ASC');
        $this->data['info'] = $dModel->dynamicCheckExist(['id' => $id], 'item');
        $this->data['formUrl'] = BASE_URL . 'admin/edit-item';
        return view('layout/admin/edit-item', $this->data);
    }
    public function updateItem() {
        $id = $this->request->getPost('id');

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
                "title" => 'required',

            ];
            if (!$this->validate($rules)) {
                session()->setFlashdata('form_error', $this->validator->getErrors());
                return redirect()->to(base_url('admin/edit-category/'.$id));
            }

            $this->db->transStart();
            $cData = array(
                'cat_id' => $pData['cat_id'],
                'title' => $pData['title'],
                'description' => $pData['description'],
                'status' => $pData['status'],

            );

            $cId = $this->dModel->dynamicUpdate( ['id'=> $id] ,'item',$cData );
            if (!$cId) {
                $this->db->transRollback();
                $ssData = array('sfmsg' => 'Could not update item data!!!', 'sstatus' => 'red');
                session()->set($ssData);
                return redirect()->to(base_url('admin/edit-item/'.$id));
            }

            $this->db->transCommit();
            $ssData = array('sfmsg' => 'Item data updated successfully!!!', 'sstatus' => 'green');
            session()->set($ssData);
            return redirect()->to(base_url('admin/item-info'));
        }
        $this->data['formUrl'] = BASE_URL . 'admin/edit-item';
        return view('layout/admin/edit-item.php', $this->data);

    }
}
