<?php

namespace Front\Model\Utils\ArrayValidator;

class ArrayValidator {

    private $serverEnc = "utf-8";
    private $sourceEnc = "utf-8";

    public function setServerEncoding($encoding){
        $this->serverEnc = $encoding;
    }

    public function setSourceEncoding($encoding){
        $this->sourceEnc = $encoding;
    }

    public function validate($template, &$data, $dec_point = ","){

        foreach ($template as $key => $item){

            $item = explode(",", $item);

            foreach ($item as $it){

                switch ($it){

                    case "is_numeric":
                        if(isset($data[$key])) {
                            if (!is_numeric($data[$key])) {
                                throw new \Exception("Parametr: " . $key . " má špatnou hodnotu");
                            }
                        }
                        break;
                    case "required":
                        if(!isset($data[$key])) {
                            throw new \Exception("Není vyplněn povinný parametr: " . $key . "");
                        }
                        break;
                    case "price":
                        $data[$key] = number_format($data[$key],2,$dec_point,"");
                        break;
                    case "float":
                        $data[$key] = number_format($data[$key],2,$dec_point,"");
                        break;
                    case "no_spaces":
                        $data[$key] = preg_replace('/\s+/', '', $data[$key]);
                        break;
                    case "encode":
                        if(isset($data[$key])){
                            $data[$key] = iconv($this->sourceEnc, $this->serverEnc, $data[$key]);
                        }
                    break;
                    default:
                        break;
                }
            }
        }

        foreach ($data as $key => $item){
            if(!array_key_exists($key, $template)){
                unset($data[$key]);
            }
        }

    }
}

?>
