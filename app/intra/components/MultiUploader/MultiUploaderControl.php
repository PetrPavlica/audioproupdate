<?php

namespace Intra\Components\MultiUploader;

use Nette;
use Nette\Application\UI\Control;
use Intra\Components\MultiUploader\Model\Uploader;

class MultiUploaderControl extends Control {

    protected $limit; //Maximum Limit of files. {null, Number}
    protected $maxSize; //Maximum Size of files {null, Number(in MB's)}
    protected $extensions; //Whitelist for file extension. {null, Array(ex: array('jpg', 'png'))}
    protected $required; //Minimum one file is required for upload {Boolean}
    protected $uploadDir; //'../../../../../uploads/', //Upload directory {String}
    protected $title; //array('name'), //New file name {null, String, Array}
    protected $removeFiles; //Enable file exclusion {Boolean(extra for jQuery.filer), String($_POST field name containing json data with file names)}
    protected $replace; //Replace the file if it already exists  {Boolean}
    protected $perms;  //Uploaded file permisions {null, Number}
    protected $onCheck; //A callback function name to be called by checking a file for errors (must return an array) | ($file) | Callback
    protected $onError; //A callback function name to be called if an error occured (must return an array) | ($errors, $file) | Callback
    protected $onSuccess; //A callback function name to be called if all files were successfully uploaded | ($files, $metas) | Callback
    protected $onUpload;  //A callback function name to be called if all files were successfully uploaded (must return an array) | ($file) | Callback
    protected $onComplete; //A callback function name to be called when upload is complete | ($file) | Callback
    protected $onRemove; //A callback function name to be called by removing files (must return an array) | ($removed_files) | Callback

    public function __construct() {
        parent::__construct();
        $this->limit = 10;
        $this->maxSize = 30;
        $this->required = false;
        $this->title = array('name');
        $this->removeFiles = true;
        $this->replace = false;
        $this->extensions = ['jpg', 'jpeg', 'png', 'gif'];
    }

    // /** @var Translator */ TODO
    // public $trans;

    public function render() {
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/element.latte');

        $template->uploadDir = $this->uploadDir;
        $template->limit = $this->limit;
        $template->maxSize = $this->maxSize;
        $template->extensions = $this->extensions;
        $template->required = $this->required;
        $template->uploadDir = $this->uploadDir;
        $template->title = $this->title;
        $template->removeFiles = $this->removeFiles;
        $template->replace = $this->replace;
        $template->perms = $this->perms;
        $template->onCheck = $this->onCheck;
        $template->onError = $this->onError;
        $template->onSuccess = $this->onSuccess;
        $template->onUpload = $this->onUpload;
        $template->onComplete = $this->onComplete;
        $template->onRemove = $this->onRemove;

        // render template
        $template->render();
    }

    public function handleRemove() {
        if (isset($_POST['file'])) {
            if (!is_array($_POST['file'])) {
                $file = $this->uploadDir . $_POST['file'];
                if (file_exists($file)) {
                    unlink($file);
                }
            } else {
                foreach ($_POST['file'] as $p) {
                    $file = $this->uploadDir . $p;
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
            }
        }
    }

    public function handleSend() {
        $uploader = new Uploader();
        $data = $uploader->upload($_FILES['files'], array(
            'limit' => $this->limit,
            'maxSize' => $this->maxSize,
            'extensions' => $this->extensions,
            'required' => $this->required,
            'uploadDir' => $this->uploadDir,
            'title' => $this->title,
            'removeFiles' => $this->removeFiles,
            'replace' => $this->replace,
            'perms' => $this->perms,
            'onCheck' => $this->onCheck,
            'onError' => $this->onError,
            'onSuccess' => $this->onSuccess,
            'onUpload' => $this->onUpload,
            'onComplete' => $this->onComplete,
            'onRemove' => $this->onRemove,
        ));
        if ($data['hasErrors']) {
            $errors = $data['errors'];
            echo json_encode($errors);
            exit;
        }

        if ($data['isComplete']) {
            $files = $data['data'];
            if (isset($files['metas'][0]['name']))
                echo json_encode($files['metas'][0]['name']);
            else
                echo json_encode('Cannot upload');
            exit;
        }
    }

    /**
     * Maximum Limit of files. {null, Number}
     * @param type $limit
     */
    public function setLimit($limit) {
        $this->limit = $limit;
    }

    /**
     * Maximum Size of files {null, Number(in MB's)}
     * @param type $maxSize
     */
    public function setMaxSize($maxSize) {
        $this->maxSize = $maxSize;
    }

    /**
     * Whitelist for file extension. {null, Array(ex: array('jpg', 'png'))}
     * @param type $extensions
     */
    public function setExtensions($extensions) {
        $this->extensions = $extensions;
    }

    /**
     * Minimum one file is required for upload {Boolean}
     * @param type $required
     */
    public function setRequired($required) {
        $this->required = $required;
    }

    /**
     * Upload directory {String} - in default use BasePath + path from config
     * @param type $uploadDir
     */
    public function setUploadDir($uploadDir) {
        $this->uploadDir = $uploadDir;
    }

    /**
     * New file name {null, String, Array}
     * @param type $uploadDir
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * Enable file exclusion {Boolean(extra for jQuery.filer), String($_POST field name containing json data with file names)}
     * @param type $removeFiles
     */
    public function setRemoveFiles($removeFiles) {
        $this->removeFiles = $removeFiles;
    }

    /**
     * Replace the file if it already exists  {Boolean}
     * @param type $replace
     */
    public function setReplace($replace) {
        $this->replace = $replace;
    }

    /**
     * Uploaded file permisions {null, Number}
     * @param type $perms
     */
    public function setPerms($perms) {
        $this->perms = $perms;
    }

    /**
     * A callback function name to be called by checking a file for errors (must return an array) | ($file) | Callback
     * @param type $onCheck
     */
    public function setOnCheck($onCheck) {
        $this->onCheck = $onCheck;
    }

    /**
     * A callback function name to be called if an error occured (must return an array) | ($errors, $file) | Callback
     * @param type $onError
     */
    public function setOnError($onError) {
        $this->onError = $onError;
    }

    /**
     * A callback function name to be called if all files were successfully uploaded | ($files, $metas) | Callback
     * @param type $onSuccess
     */
    public function setOnSuccess($onSuccess) {
        $this->onSuccess = $onSuccess;
    }

    /**
     * A callback function name to be called if all files were successfully uploaded (must return an array) | ($file) | Callback
     * @param type $onUpload
     */
    public function setOnUpload($onUpload) {
        $this->onUpload = $onUpload;
    }

    /**
     * A callback function name to be called when upload is complete | ($file) | Callback
     * @param type $onComplete
     */
    public function setOnComplete($onComplete) {
        $this->onComplete = $onComplete;
    }

    /**
     * A callback function name to be called by removing files (must return an array) | ($removed_files) | Callback
     * @param type $onRemove
     */
    public function setOnRemove($onRemove) {
        $this->onRemove = $onRemove;
    }

}
