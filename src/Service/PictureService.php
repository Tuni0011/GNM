<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PictureService
{
    private $params;
    private $filesystem;

    public function __construct(ParameterBagInterface $params,Filesystem $filesystem )
    {
        $this->params = $params;
        $this->filesystem = $filesystem;
    }

    public function add(UploadedFile $picture, ?string $folder= '' , ?int $width = 250, ?int $height =250)
    {

        $fichier= md5(uniqid(rand(), true)). '.webp';

        $picture_infos = getimagesize($picture);
         
        if($picture_infos === false){
            throw new \Exception('format incorect ');
        }
        switch($picture_infos['mime']){
            case 'image/png':
                $picture_source = imagecreatefrompng($picture);
                break;
            case 'image/jpeg':
                $picture_source = imagecreatefromjpeg($picture);
                break;
            case 'image/webp':
                $picture_source = imagecreatefromwebp($picture);
                break;
            default:
                throw new \Exception('format incorect ');
        }
        $imageWidth = $picture_infos[0];
        $imageHeight = $picture_infos[1];
        switch ($imageWidth <=> $imageHeight) {
            case -1:
                $squareSize = $imageWidth;
                $src_x = 0;
                $src_y = ($imageHeight - $imageWidth) / 2;
                break;
            case 0:
                $squareSize = $imageWidth;
                $src_x = 0;
                $src_y = 0;
                break;
            case 1:
                $squareSize = $imageHeight;
                $src_x = ($imageWidth - $imageHeight) / 2;
                $src_y = 0;
                break;
        }


        $resized_picture = imagecreatetruecolor($width, $height);
        imagecopyresampled($resized_picture, $picture_source, 0, 0, $src_x, $src_y, $width, $height, $squareSize, $squareSize);

        $path= $this->params->get('image_directory').$folder;
        if(!file_exists($path . '/mini/')){
            mkdir($path . '/mini/', 0755, true);
        }
         imagewebp($resized_picture,$path . '/mini/' . $width .'x' .
         $height . '-'. $fichier);
         $picture->move($path . '/'. $fichier);

        return $fichier;


        
    }

    public function delete($fichier, $folder = '', ?int $width = 250, ?int $height = 250)
    {
        if ($fichier !== 'default.webp') {
            $path = $this->params->get('image_directory') . $folder;
            $mini = $path . '/mini/' . $width . 'x' . $height . '-' . $fichier;
            $original = $path . '/' . $fichier;

            $success = false;

            if ($this->filesystem->exists($mini)) {
                $this->filesystem->remove($mini);
                $success = true;
            }

            if ($this->filesystem->exists($original)) {
                $this->filesystem->remove($original);
                $success = true;
            }

            return $success;
        }

        return false;
    }
}

