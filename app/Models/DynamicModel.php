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

    public function dynamicInsert(array $data, string $table)
    {
        $query = $this->db->table($table);
        return $query->insert($data);
    }


    public function selectAllData(string $table, string $orderById, string $ascDesc)
    {
        $query = $this->db->table($table);
        $query->orderBy($orderById, $ascDesc);
        return $query->select()->get()->getResultArray();
    }




    public function dynamicInsertReturnId(array $data, string $table)
    {
        $this->db->table($table)->insert($data);
        return $this->db->insertID();
    }

    public function dynamicCheckExist(array $where, string $table)
    {
        $query = $this->db->table($table);
        $query->where($where);
        return $query->select()->get()->getResultArray();
    }

    public function dynamicDelete(array $where, string $table)
    {
        return  $this->db->table($table)->where($where)->delete();
    }

    public function dynamicUpdate(array $where, string $table, array $data)
    {
        $query = $this->db->table($table);
        $query->where($where);
        return $query->update($data, $where);
    }

    public function itemData()
    {
        $query = $this->db->table('item')
            ->select('category.category_name, item.id, item.cat_id, item.title, item.description, item.status, item.created_at')
            ->join('category', 'category.id = item.cat_id', 'left');

       return $result = $query->get()->getResultArray();
    }
}
