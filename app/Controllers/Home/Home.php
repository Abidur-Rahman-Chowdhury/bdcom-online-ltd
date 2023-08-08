<?php

namespace App\Controllers\Home;

use App\Constant\Constants;
use App\Controllers\BaseController;
use App\Models\DynamicModel;
use App\Models\SoftsolModel;

class Home extends BaseController
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
        return view('layout/admin/admin-home',$this->data);
    }

   
}
