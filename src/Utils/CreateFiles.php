<?php

    namespace FM\Utils;
    
    use FM\Render;
    use FM\FileData\FileFunc;
    use FM\FileData\PathInfo;

    require "../config/Conf.php";
    require "CreateFilesInterface.php";
    require '../FileData/FileFunc.php';
    require '../FileData/PathInfo.php';
	require '../Render.php';

    class CreateFiles implements CreateFilesInterface
    {
        private $name;
        private $type ;
        private $isDir;
        private $path;
        private $relativePath;
        private $parentDir;
        private $pathNewFile;
        private $isValidName;
        private $data = [
            'msg' => '',
            'result' => 'error'
        ];

        public function __construct()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->data['msg'] = "Incorrect method of sending data <br>";
            } else {
                $this->name = $_POST['name'];
                $this->type = $_POST['type'];
                $this->isDir = $this->type == 'folder' ? true : false;
                $this->relativePath = FileFunc::getRelPath($_SERVER['HTTP_REFERER']);
                $this->parentDir = ROOT . $this->relativePath;
                $this->pathNewFile = $this->parentDir . $this->name;
                $this->isValidName = FileFunc::isValidName($this->name);
                $this->createFile();
            }
        }

        public function createFile()
        {
            if ($this->name === '') {
                $this->data['msg'] .= "File name is empty <br>";
            } elseif (strlen($this->name) > 255) {
                $this->data['msg'] .= "File name is too long <br>";
            } elseif (!$this->isValidName) {
                $this->data['msg'] .= "It is recommended not to use these symbols: '! @ # $ & ~ % * ( ) [ ] { } ' \" \\ / : ; > < `' and space in the file name <br>";
            }

            if (file_exists($this->pathNewFile)) {
                $this->data['msg'] .= "File with name '{$this->name}' already exist <br>";
            }

            if (!is_dir($this->parentDir)) {
                $this->data['msg'] .= "Path is incorrect <br> {$this->relativePath}  <br>";
            }

            if ($this->data['msg'] == '') {
                $resOper = $this->isDir ? mkdir($this->pathNewFile) : touch($this->pathNewFile);

                if (!$resOper) {
                    $this->data['msg'] .= "Could not create file '{$this->name}' <br>";
                } else {
                    $this->data['result'] = "success";
                    $path = new PathInfo($this->parentDir);
                    $contentData = $path->getContentData();
                    $this->data['content'] = Render::generate('table_files.twig', ['contentData' => $contentData]);
                }
            }
        }

        public function echoJsonEncode()
        {
            header('Content-Type: application/json');
            echo json_encode($this->data);
        }
    }

    $newFile = new CreateFiles();
    $newFile->echoJsonEncode();