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



    public function getSlideImages(){
        $query = $this->db->table('softsol_data')
            ->select('images.file_name, softsol_data.page_title, softsol_data.description')
            ->join('images', 'softsol_data.id = images.page_id')
            ->where('softsol_data.page_name', 'slide')
            ->where('images.is_active', 1)
            ->get();
    
        return $query->getResultArray();
    }

    public Function dynamicUpdate(array $where, string $table, array $data){
        $query = $this->db->table($table);
        $query->where($where);
        return $query->update($data, $where);
    }

    public function getSoftsolDataByControllerNames($controllerNames)
    {
        $db = db_connect();
        $query = $db->table($this->table)->whereIn('controller_name', $controllerNames)->get();
        $aboutus = $query->getResultArray();

        foreach ($aboutus as &$about) {
            $about['images'] = $this->getImagesByPageId($about['id']);
        }

        return $aboutus;
    }


    public function imageUpload($FILES, $folderPath = '', $targetWidth=200, $targetHeight=200, $thumbnail = false){
        $file = $FILES['tmp_name']; 
        $sourceProperties = getimagesize($file);
        
        if(empty($folderPath)){
            $folderPath = "test-img/";  
        }
        
        $thumbnailPath = 'bizc/softsol-image/thumbnail/';
        $ext = pathinfo($FILES['name'], PATHINFO_EXTENSION);
        $fileNewName = 'softsolbd'.'-'.time().uniqid().'.'.$ext;
        $imageType = $sourceProperties[2];
        switch ($imageType) {

            case IMAGETYPE_PNG:
                $imageResourceId = imagecreatefrompng($file); 
                $targetLayer = $this->imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1], $targetWidth, $targetHeight);
                imagepng($targetLayer,$folderPath. $fileNewName);
                if($thumbnail){
                    $targetLayer = $this->thumbnail($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
                    imagepng($targetLayer,$thumbnailPath. $fileNewName);
                }
                return $fileNewName;

            case IMAGETYPE_GIF:
                $imageResourceId = imagecreatefromgif($file); 
                $targetLayer = $this->imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1], $targetWidth, $targetHeight);
                imagegif($targetLayer,$folderPath. $fileNewName);
                if($thumbnail){
                    $targetLayer = $this->thumbnail($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
                    imagepng($targetLayer,$thumbnailPath. $fileNewName);
                }
                return $fileNewName;

            case IMAGETYPE_JPEG:
                $imageResourceId = imagecreatefromjpeg($file); 
                $targetLayer = $this->imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1], $targetWidth, $targetHeight);
                imagejpeg($targetLayer,$folderPath. $fileNewName);
                if($thumbnail){
                    $targetLayer = $this->thumbnail($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
                    imagepng($targetLayer,$thumbnailPath. $fileNewName);
                }
                return $fileNewName;

            default:
            return "Invalid Image type.";
        }
    }


    function imageResize($imageResourceId,$width,$height, $targetWidth, $targetHeight) {
        $targetLayer=imagecreatetruecolor($targetWidth,$targetHeight);
        imagecopyresampled($targetLayer,$imageResourceId,0,0,0,0,$targetWidth,$targetHeight, $width,$height);
        return $targetLayer;
    }


    function thumbnail($imageResourceId,$width,$height) {
        $targetWidth = Constants::THUMBNAIL_WEIGHT;
        $targetHeight = Constants::THUMBNAIL_HEIGHT;
        $targetLayer=imagecreatetruecolor($targetWidth,$targetHeight);
        imagecopyresampled($targetLayer,$imageResourceId,0,0,0,0,$targetWidth,$targetHeight, $width,$height);
        return $targetLayer;
    }

}