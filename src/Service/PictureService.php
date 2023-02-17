<?php

namespace App\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureService
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function add(UploadedFile $picture, ?string $folder = '', ?int $width = 250, ?int $height = 250)
    {
        // on donne un nouveau nom à l'image
        $fichier = md5(uniqid(rand(), true)) . '.webp';

        // on récupère les infos de l'image
        $picture_infos = getimagesize($picture);

        if ($picture_infos === false) {
            throw new Exception('Format d\'image incorrect');
        }

        // on vérifie le format de l'image
        switch ($picture_infos['mime']) {
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
                throw new Exception('Format d\'image incorrect');
        }

        // on recadre l'image
        // on récupère les dimensions
        $image_width = $picture_infos[0];
        $image_height = $picture_infos[1];

        // on vérifie l'orientation de l'image
        // triple comparaison : inférieur, égal, puis supérieur
        switch ($image_width <=> $image_height) {
            case -1: // portrait
                $squareSize = $image_width;
                $src_x = 0;
                $src_y = ($image_height - $squareSize) / 2;
                break;
            case 0: // carré
                $squareSize = $image_width;
                $src_x = 0;
                $src_y = 0;
                break;
            case 1: // paysage
                $squareSize = $image_height;
                $src_x = ($image_width - $squareSize) / 2;
                $src_y = 0;
                break;
        }

        // on crée une nouvelle image vierge 
        // dans laquelle on va coller la découpe qu'on vient de faire
        $resized_picture = imagecreatetruecolor($width, $height);
        imagecopyresampled($resized_picture, $picture_source, 0, 0, $src_x, $src_y, $width, $height, $squareSize, $squareSize);

    }
}