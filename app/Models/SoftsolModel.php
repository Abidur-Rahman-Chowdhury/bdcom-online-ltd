<?php
namespace App\Models;

use CodeIgniter\Model;

class SoftsolModel extends Model
{

    protected $db;
    protected $table = 'softsol_data';
    protected $primaryKey = 'id';
    protected $allowedFields = ['page_name', 'controller_name', 'page_title', 'meta_key_word', 'description'];

    public function __construct()
    {
        $this->db = \Config\Database::connect();

    }

    public function insertSoftsolData($data)
    {
        $this->insert($data);
        return $this->insertID();
    }

    public function insertImages($data)
    {

        $builder = $this->db->table('images');
        $builder->insertBatch($data);
    }

    public function updateSoftsolData($pageId, $data)
    {
        $this->update($pageId, $data);
    }
    public function getAllAboutusData()
    {

        $query = $this->db->table($this->table)->get();
        $aboutus = $query->getResultArray();

        foreach ($aboutus as &$about) {
            $about['images'] = $this->getImagesByPageName($about['page_name']);
        }

        return $aboutus;
    }

    public function getImagesByPageName($pageName)
    {

        $query = $this->db->table('images')->where('page_name', $pageName)->get();
        return $query->getResultArray();
    }

    public function getSoftsolDataByControllerNames($controllerNames)
    {

        $query = $this->db->table($this->table)->whereIn('controller_name', $controllerNames)->get();
        $aboutus = $query->getResultArray();

        foreach ($aboutus as &$about) {
            $about['images'] = $this->getImagesByPageId($about['id']);
        }

        return $aboutus;
    }

    public function getImagesByPageId($pageId)
    {

        $query = $this->db->table('images')->where('page_id', $pageId)->get();
        return $query->getResultArray();
    }

    public function getPortfolioInfo()
    {

        $query = $this->db->table('project_info pi')
            ->join('project_category pc', 'pc.id = pi.category_id');
        return $query->select('pi.*, pc.project_category')->get()->getResultArray();
    }

    public function selectStatesByParentId(string $table, string $orderById, string $ascDesc, int $parentId)
    {
        $query = $this->db->table($table)->where('parent_id', $parentId);
        $query->orderBy($orderById, $ascDesc);
        return $query->select()->get()->getResultArray();
    }

    public function selectAllStates()
    {
        $query = $this->db->table('state')
            ->join('state as state2', 'state2.id = state.parent_id', 'LEFT');

        return $query->select('state.*, state2.name as parent_name')->get()->getResultArray();
    }

    public function deleteStatesById($id)
    {
       return  $this->db->table('state')->where('id', $id)->delete();
    }

//   public  function imageUpload($FILES, $folderPath = '', $targetWidth=200, $targetHeight=200){

//         $file = $FILES['image']['tmp_name'];

//         $sourceProperties = getimagesize($file);

//         if(empty($folderPath)){
//             $folderPath = "admin-template/upload/";
//         }
//         $ext = pathinfo($FILES['image']['name'], PATHINFO_EXTENSION);
//         $fileNewName = 'image'.'-'.time().'.'.$ext;
//         $imageType = $sourceProperties[2];
//         switch ($imageType) {

//             case IMAGETYPE_PNG:
//                 $imageResourceId = imagecreatefrompng($file);
//                 $targetLayer = $this->imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1], $targetWidth, $targetHeight);
//                 imagepng($targetLayer,$folderPath. $fileNewName);
//                 return $fileNewName;

//             case IMAGETYPE_GIF:
//                 $imageResourceId = imagecreatefromgif($file);
//                 $targetLayer = $this->imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1], $targetWidth, $targetHeight);
//                 imagegif($targetLayer,$folderPath. $fileNewName);
//                 return $fileNewName;

//             case IMAGETYPE_JPEG:
//                 $imageResourceId = imagecreatefromjpeg($file);
//                 $targetLayer = $this->imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1], $targetWidth, $targetHeight);
//                 imagejpeg($targetLayer,$folderPath. $fileNewName);
//                 return $fileNewName;

//             default:
//             return "Invalid Image type.";
//         }
//     }

//     function imageResize($imageResourceId,$width,$height, $targetWidth, $targetHeight) {
//         $targetLayer=imagecreatetruecolor($targetWidth,$targetHeight);
//         imagecopyresampled($targetLayer,$imageResourceId,0,0,0,0,$targetWidth,$targetHeight, $width,$height);
//         return $targetLayer;
//     }
}
