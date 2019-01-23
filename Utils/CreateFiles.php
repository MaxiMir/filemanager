<?php

    namespace FM\Utils;

    require_once '../../vendor/autoload.php';

    use \FM\Render\HtmlMarkup;
    use \FM\FileData\FileFunc;
    use \FM\FileData\PathInfo;


    class CreateFiles implements UtilsInterface
    {
        use Json;

        private $name;
        private $parentDir;
        private $pathNewFile;
        private $isDir;

        public function __construct()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->data['msg'] = 'Incorrect method of sending data';
            } else {
                $postData = FileFunc::cleanData($_POST);
                $relativePath = FileFunc::getRelPath($_SERVER['HTTP_REFERER']);
                $type = $postData['type'];
                $this->name = $postData['name'];
                $this->parentDir = ROOT . $relativePath;
                $this->pathNewFile = $this->parentDir . $this->name;
                $this->isDir = $type == 'folder' ? true : false;
                $isValidName = FileFunc::isValidName($this->name);

                if (!is_dir($this->parentDir)) {
                    $this->data['msg'] = "Path is incorrect:<br> {$relativePath}";
                } elseif (!$isValidName) {
                    $this->data['msg'] = 'File name must be between 0 and 255 <br> and it is recommended not to use these symbols: "! @ # $ & ~ % * ( ) [ ] { } \' " \\ / : ; > < `" and space in the file name';
                } elseif (file_exists($this->pathNewFile)) {
                    $this->data['msg'] = "File with name '{$this->name}' already exist";
                } else {
                    $this->run();
                }

            }
        }

        private function run()
        {
            $resOper = $this->isDir ? mkdir($this->pathNewFile) : touch($this->pathNewFile);

            if (!$resOper) {
                $this->data['msg'] = "Could not create file '{$this->name}'";
            } else {
                $this->data['result'] = 'success';
                $path = new PathInfo($this->parentDir);
                $contentData = $path->getContentData();
                $this->data['content'] = HtmlMarkup::generate('table_files.twig', ['contentData' => $contentData]);
            }
        }
    }

    $newFile = new CreateFiles();
    $newFile->echoJsonEncode();