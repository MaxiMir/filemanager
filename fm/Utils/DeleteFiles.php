<?php

    namespace FM\Utils;

    require_once '../../vendor/autoload.php';

    use \FM\Render\HtmlMarkup;
    use \FM\FileData\FileFunc;
    use \FM\FileData\PathInfo;

    class DeleteFiles implements UtilsInterface
    {
        use Json;

        private $pathFile;
        private $isDir;
        private $parentDir;

        public function __construct()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->data['msg'] = 'Incorrect method of sending data';
            } else {
                $path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
                $relativePath = preg_replace('/\/' . FM_FOLDER_NAME . '/', '', $path, 1);
                $this->pathFile = FileFunc::cleanData($_POST['pathFile']);
                $this->isDir = is_dir($this->pathFile) ? true : false;
                $this->parentDir = ROOT . $relativePath;

                if (!file_exists($this->pathFile)) {
                    $this->data['msg'] = 'Incorrect file';
                } else {
                    $this->run();
                }
            }
        }

        private function run()
        {
            $this->isDir ? FileFunc::delDir($this->pathFile) : unlink($this->pathFile);
            if (file_exists($this->pathFile)) {
                $this->data['msg'] = 'Error deleting file';
            } else {
                $this->data['result'] = 'success';
                $path = new PathInfo($this->parentDir);
                $contentData = $path->getContentData();
                $this->data['content'] = HtmlMarkup::generate('table_files.twig', ['contentData' => $contentData]);
            }
        }
    }

    $newDeleteFiles = new DeleteFiles();
    $newDeleteFiles->echoJsonEncode();
