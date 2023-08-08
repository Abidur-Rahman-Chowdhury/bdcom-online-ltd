<?php

namespace App\Models;
use CodeIgniter\Model;

class CheckModel extends Model
{
    protected $db;

    function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public Function getMerchantRoute($routeName){
        $query = $this->db->table('client')
                        ->where('route_name',$routeName)
                        ->where('is_active',1)
                        ->where("expired_at >".time());
        return $query->select()->get()->getRowArray();
    }
}