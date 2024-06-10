<?php

namespace Intra\Model\Utils\ImageManager;

use Nette;

class ImagesEditor {

    private $destination;
    private $newDestination;

    public function destination() {
        return $this->destination;
    }

    public function newDestination() {
        return $this->newDestination;
    }

    function __construct() {

    }

    public function setInputDir($path) {
        $this->destination = $path;
    }

    public function setOutputDir($path) {
        $this->newDestination = $path;
    }

    public function isValidIMG($source) {

        if (gettype($source) == "resource") {

            if (get_resource_type($source) == "gd") {
                return true;
            }
        }
        return false;
    }

    /**
     * Load image from files
     * @param type $imgNameEx
     * @return Image
     * @throws Exception
     */
    public function loadImage($imgNameEx) {

        if ($this->destination != '' && $this->newDestination != '') {

            if ($imgNameEx != '') {

                $image = $this->createImageFrom($imgNameEx);
                $image = new Image($this->destination . "/" . $imgNameEx, $this, $image);

                return $image;
            } else {
                throw new Exception("Není uveden obrázek");
            }
        } else {
            throw new Exception("Není uvedená cesta zdroje, nebo cesta cíle!");
        }
    }

    private function createImageFrom($imgName) {

        $image = false;

        switch (pathinfo($this->destination . "/" . $imgName, PATHINFO_EXTENSION)) {
            case "jpg":
            case "jpeg":
                $image = @imagecreatefromjpeg($this->destination . "/" . $imgName);
                break;
            case "png":
                $image = @imagecreatefrompng($this->destination . "/" . $imgName);
                break;
            case "gif":
                $image = @imagecreatefromgif($this->destination . "/" . $imgName);
        }

        if (!$this->isValidIMG($image)) {
            //throw new Exception("Načtení obrázku se nepovedlo...");
        }

        return $image;
    }

    public function createImage($type = "truecolor", $width, $height) {

        $image = false;

        switch ($type) {
            case "truecolor":
                if (is_numeric($width) && is_numeric($height)) {
                    $image = imagecreatetruecolor($width, $height);
                } else {
                    throw new Exception("Rozměry obrázku musí být číslo!");
                }
                break;
        }

        if (!$this->isValidIMG($image)) {
            //throw new Exception("Načtení obrázku se nepovedlo...");
        }

        return $image;
    }

    public function saveImage(Image $img, $newName, $del = TRUE) {

        if ($this->destination == $this->newDestination) {
            $pom = "_";
        } else {
            $pom = "";
        }

        if ($newName) {
            $name = $newName;
        } else {
            $name = $img->name();
        }

        $image = null;

        switch ($img->extension()) {
            case "jpg":
            case "jpeg":
                imagejpeg($img->GDImage(), $img->newPath() . "/" . $name . $pom . "." . $img->extension());
                break;
            case "png":
                imagepng($img->GDImage(), $img->newPath() . "/" . $name . $pom . "." . $img->extension(), 6);
                break;
            case "gif":
                imagegif($img->GDImage(), $img->newPath() . "/" . $name . $pom . "." . $img->extension());
        }

        if ($this->destination == $this->newDestination) {

            $picc = $this->newDestination . "/" . $name . $pom . "." . $img->extension();
            $pic = $this->newDestination . "/" . $name . "." . $img->extension();

            if ($img->extension() == $img->oldExtension()) {
                if (file_exists($pic))
                    unlink($pic);
            }

            copy($picc, $pic);
            unlink($picc);

            if ($del && $img->extension() != $img->oldExtension()) {
                unlink($this->newDestination . "/" . $name . "." . $img->oldExtension());
            }
        }
    }

}
