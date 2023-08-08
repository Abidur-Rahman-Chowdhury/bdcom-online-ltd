<?php

namespace App\Models;
use CodeIgniter\Model;
use App\Constant\Constants;
use PHPUnit\TextUI\XmlConfiguration\Constant;

class DynamicModel extends Model
{
    protected $db;
    

    function __construct()
    {
        $this->db = \Config\Database::connect();
        
    }

    public Function dynamicInsert(array $data, string $table){
        $query = $this->db->table($table);
        return $query->insert($data);
    }


    public Function selectAllData(string $table, string $orderById, string $ascDesc){
        $query = $this->db->table($table);
        $query->orderBy($orderById, $ascDesc);
        return $query->select()->get()->getResultArray();
    }
 

    

    public Function dynamicInsertReturnId(array $data, string $table){
        $this->db->table($table)->insert($data);
        return $this->db->insertID();
    }

    public Function dynamicCheckExist(array $where, string $table){
        $query = $this->db->table($table);
        $query->where($where);
        return $query->select()->get()->getResultArray();
    }

    public function dynamicDelete(array $where, string $table) {
           return  $this->db->table($table)->where($where)->delete();
    }

    public Function dynamicUpdate(array $where, string $table, array $data){
        $query = $this->db->table($table);
        $query->where($where);
        return $query->update($data, $where);
    }

    


 


  




}