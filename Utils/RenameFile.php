<?php

    namespace FM\Utils;

    require_once '../../vendor/autoload.php';

    use \FM\Render\HtmlMarkup;
    use \FM\FileData\FileFunc;
    use \FM\FileData\PathInfo;

    class RenameFile implements UtilsInterface
    {
        use Json;

        private $parentDir;
        private $pathOldFile;
        private $pathNewFile;

        public function __construct()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->data['msg'] = "Incorrect method of sending data.<br>";
            } else {
                $path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
                $relativePath = preg_replace('/\/' . FM_FOLDER_NAME . '/', '', $path, 1);
                $oldName = FileFunc::cleanData($_POST['oldName']);
                $newName = FileFunc::cleanData($_POST['newName']);
                $this->parentDir = ROOT . $relativePath;
                $this->pathOldFile = $this->parentDir . $oldName;
                $this->pathNewFile = $this->parentDir . $newName;
                $isValidName = FileFunc::isValidName($newName);

                if (!is_dir($this->parentDir)) {
                    $this->data['msg'] = "Path is incorrect: '{$this->parentDir}'";
                } elseif ($oldName == $newName) {
                    $this->data['msg'] = 'File names are not individual';
                } elseif (!$isValidName) {
                    $this->data['msg'] = 'File name must be between 0 and 255 <br> and it is recommended not to use these symbols: "! @ # $ & ~ % * ( ) [ ] { } \' " \\ / : ; > < `" and space in the file name';
                } elseif (file_exists($this->pathNewFile)) {
                    $this->data['msg'] = "File with name '{$newName}' already exist";
                } else {
                    $this->run();
                }
            }
        }

        private function run()
        {
            if(!rename($this->pathOldFile, $this->pathNewFile)) {
                $this->data['msg'] = 'Failed to rename file';
            } else {
                $this->data['result'] = 'success';
                $path = new PathInfo($this->parentDir);
                $contentData = $path->getContentData();
                $this->data['content'] = HtmlMarkup::generate('table_files.twig', ['contentData' => $contentData]);
            }
        }
    }

    $newRenameFile = new RenameFile();
    $newRenameFile->echoJsonEncode();