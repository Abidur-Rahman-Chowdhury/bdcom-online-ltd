<?php

namespace App\Models;
use CodeIgniter\Model;

class HotelModel extends Model
{
    protected $db;

    function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public Function getSlide($clientId){
        $query = $this->db->table('slide')
                        ->where('is_active',1)
                        ->where('client_id',$clientId);
        return $query->select()->get()->getResultArray();
    }
}