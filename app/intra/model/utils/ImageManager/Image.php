<?php

namespace Intra\Model\Utils\ImageManager;

use Nette;

class Image extends \Nette\Object {

    private $name;
    private $extension;
    private $oldExtension;
    private $newPath;
    private $oldPath;
    private $origWidth;
    private $origHeight;
    private $width;
    private $height;
    private $type;
    private $GDImage;
    private $imagesEP;

    function __construct($pic, ImagesEditor $im, $GD) {

        $this->imagesEP = $im;

        if ($this->imagesEP->isValidIMG($GD)) {
            $this->GDImage = $GD;
        } else {
            throw new \Exception("Obrázek, se kterým se má pracovat, musí být otevřený v GD knihovně!");
        }

        $this->name = pathinfo($pic, PATHINFO_FILENAME);
        $this->extension = pathinfo($pic, PATHINFO_EXTENSION);
        $this->oldExtension = $this->extension;

        list($width, $height) = getimagesize($pic);

        $this->width = $width;
        $this->height = $height;

        $this->origWidth = $width;
        $this->origHeight = $height;

        $this->type = exif_imagetype($pic);
        $this->newPath = $this->imagesEP->newDestination();
        $this->oldPath = $this->imagesEP->destination();
    }

    public function isJPG() {

        if ($this->type == 2) {
            return true;
        }

        return false;
    }

    public function isPNG() {

        if ($this->type == 3) {
            return true;
        }

        return false;
    }

    public function isGIF() {

        if ($this->type == 1 || $this->type == 0) {
            return true;
        }

        return false;
    }

    public function name() {
        return $this->name;
    }

    public function extension() {
        return $this->extension;
    }

    public function oldExtension() {
        return $this->oldExtension;
    }

    public function newPath() {
        return $this->newPath;
    }

    public function GDImage() {
        return $this->GDImage;
    }

    public function width() {
        return $this->width;
    }

    public function height() {
        return $this->height;
    }

    public function origWidth() {
        return $this->origWidth;
    }

    public function origHeight() {
        return $this->origHeight;
    }

    public function rotate($degrees) {

        if (isset($degrees)) {
            $rotate = imagerotate($this->GDImage, $degrees, 0);
            $this->GDImage = $rotate;
        } else {
            throw new \Exception("Není zadán úhel otočení obrázku!!!");
        }
    }

    public function autoRotate() {
        if ($this->isJPG()) {
            try {
                $exif = @exif_read_data($this->oldPath . "/" . $this->name . "." . $this->extension);
            } catch (\Exception $exp) {
                return;
            }

            if (isset($exif['THUMBNAIL']['Orientation'])) {
                $orientation = $exif['THUMBNAIL']['Orientation'];

                switch ($orientation) {
                    case 3:
                        $rotate = imagerotate($this->GDImage, 180, 0);
                        break;
                    case 6:
                        $rotate = imagerotate($this->GDImage, -90, 0);
                        break;
                    case 8:
                        $rotate = imagerotate($this->GDImage, 90, 0);
                        break;
                    default:
                        $rotate = $this->GDImage;
                        break;
                }

                $this->GDImage = $rotate;
            }
        }
    }

    private function resampleImg($width, $height, $x = 0, $y = 0) {

        if ($x > 0 || $y > 0) {
            $widthO = $width;
            $heightO = $height;
        } else {
            $widthO = $this->width;
            $heightO = $this->height;
        }

        $newImg = $this->imagesEP->createImage("truecolor", $width, $height);

        if ($this->isPNG()) {

            imagealphablending($newImg, false);
            imagesavealpha($newImg, true);
        } elseif ($this->isGIF()) {

            $transparent = imageColorTransparent($this->GDImage);

            if ($transparent != -1) {
                $transparent_color = imageColorsForIndex($this->GDImage, $transparent);
                $transparent_new = imageColorAllocate($newImg, $transparent_color["red"], $transparent_color["green"], $transparent_color["blue"]);
                $transparent_new_index = imageColorTransparent($newImg, $transparent_new);
                imageFill($newImg, 0, 0, $transparent_new_index);
            }

            imageCopyResized($newImg, $this->GDImage, 0, 0, $x, $y, $width, $height, $widthO, $heightO);
        }

        if (!$this->isGIF()) {
            imagecopyresampled($newImg, $this->GDImage, 0, 0, $x, $y, $width, $height, $widthO, $heightO);
        }

        $this->GDImage = $newImg;
        $this->width = $width;
        $this->height = $height;
    }

    public function cropImage($x, $y, $width, $height) {
        $this->resampleImg($width, $height, $x, $y);
    }

    private function imageResize($new_width = 0, $new_height = 0) {

        if ($new_height != 0) {
            $prc = (100 * $new_height) / $this->height;
            $height = $new_height;
            $width = ($this->width * $prc) / 100;
        } else {
            $prc = (100 * $new_width) / $this->width;
            $width = $new_width;
            $height = ($this->height * $prc) / 100;
        }

        $this->resampleImg($width, $height);
    }

    public function resize($width = 0, $height = 0, $method = "cropp") {

        /* method: is used when both width and height are defined.
          supplement - will create transparent background with defined dimensions, if is height or width of image larger
          than defined width or height, it will change larger dimension to defined dimension and then position
          the picture to the center od background.
          cropp       - will change larger dimension the defined dimension and then will crop image with second dimension
          from the middle of picture. (looses data)
         */

        if ($width != 0 && $height != 0) {

            $widthO = $this->width;
            $heightO = $this->height;

            if (($widthO / $heightO) == ($width / $height)) {
                $this->imageResize($width);
            } else {

                if (($widthO == $heightO) && ($widthO > $width)) {
                    if ($width > $height) {
                        $this->imageResize(0, $height);
                    } else {
                        $this->imageResize($width);
                    }
                } else if (($widthO > $heightO) && ($widthO > $width)) {

                    //$ratioKoef = 0.2;

                    if($widthO/$heightO < $width/$height){ // If target ratio - original ratio is lower than ratioKoef, cropp image width and not height

                        if ($heightO > $height) {
                            $this->imageResize($height);
                        }

                    } else {

                        if ($method == "cropp") {
                            if ($heightO > $height) {
                                $this->imageResize(0, $height);
                            }
                        } else {
                            if ($heightO > $height) {
                                $this->imageResize($width);
                            }
                        }

                    }

                } else {

                    if ($widthO > $width) {
                        $this->imageResize(0, $height);
                    } else {

                        if ($heightO > $height) {
                            $this->imageResize(0, $height);
                        }
                    }
                }

                $widthO = $this->width;
                $heightO = $this->height;


                switch ($method) {
                    case "cropp":

                        if ($widthO > $heightO) {
                            if ($widthO > $width) {
                                $x = ($widthO / 2) - ($width / 2);
                                $y = 0;
                                $this->cropImage($x, $y, $width, $heightO);
                            }

                            if ($heightO > $height) {
                                $x = 0;
                                $y = ($heightO / 2) - ($height / 2);
                                $this->cropImage($x, $y, $widthO, $height);
                            }
                        } else {

                            if ($heightO > $height) {
                                $x = 0;
                                $y = ($heightO / 2) - ($height / 2);
                                $this->cropImage($x, $y, $widthO, $height);
                            }

                            if ($widthO > $width) {

                                $x = ($widthO / 2) - ($width / 2);
                                $y = 0;
                                $this->cropImage($x, $y, $width, $heightO);
                            }
                        }

                        if (($widthO < $width) || ($heightO < $height)) {
                            $this->transparentBackground($width, $height);
                        }

                        break;

                    case "supplement":
                        $this->transparentBackground($width, $height);
                        break;

                    default:
                        throw new \Exception("Špatná vlastnost u metody u změně velikosti obrázku");
                        break;
                }
            }
        } else {

            if ($width > 0) {
                $this->imageResize($width);
            } elseif ($height > 0) {
                $this->imageResize(0, $height);
            } else {
                $widthO = $this->width;
                $heightO = $this->height;

                if ($width >= $widthO || $height >= $heightO) {
                    if ($widthO >= $heightO) {
                        $this->imageResize(1280);
                    } else {
                        $this->imageResize(0, 720);
                    }
                }
            }
        }
    }

    public function transparentBackground($width, $height) {

        if ($this->isJPG()) {
            $this->type = 3;
            $this->extension = "png";
        }

        $resultPic = $this->imagesEP->createImage("truecolor", $width, $height);
        $color = imagecolorallocate($resultPic, 255, 255, 255);
        imagefill($resultPic, 0, 0, $color);

        $x = ($width / 2) - ($this->width / 2);
        $y = ($height / 2) - ($this->height / 2);

        imagecopy($resultPic, $this->GDImage, $x, $y, 0, 0, $this->width(), $this->height());
        //imagecopyresampled($resultPic, $this->GDImage, 0, 0, $x, $y, $width, $height, $this->width(), $this->width());
        //imagecopymerge($resultPic, $this->GDImage, $x, $y, 0, 0, $this->width, $this->height,100);

        if ($this->isPNG()) {
            imagealphablending($this->GDImage, false);
            imagesavealpha($this->GDImage, true);
        }

        $this->GDImage = $resultPic;
    }

    public function show() {

        switch (strtolower($this->extension)) {
            case "jpg":
            case "jpeg":
                header("Content-Type: image/jpeg");
                imagejpeg($this->GDImage);
                break;
            case "png":
                header("Content-Type: image/png");
                imagepng($this->GDImage);
                break;
            case "gif":
                header("Content-Type: image/gif");
                imagegif($this->GDImage);
        }
    }

    public function save($newName = null, $del = TRUE) {
        $this->imagesEP->SaveImage($this, $newName, $del);
    }

}
