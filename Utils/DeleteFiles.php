<?php

    namespace FM\Utils;

    require_once '../../vendor/autoload.php';

    use \FM\Render\HtmlMarkup;
    use \FM\FileData\Path;

    class DeleteFiles implements UtilsInterface
    {
        use Json;

        private $pathFiles;
        private $parentDir;

        public function __construct()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->data['msg'] = 'Incorrect method of sending data';
            } else {
                $path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
                $relativePath = preg_replace('/\/' . FM_FOLDER_NAME . '/', '', $path, 1);
                $this->parentDir = ROOT . $relativePath;
                $this->pathFiles = array_map(function($item) {
                    return  $this->parentDir . htmlspecialchars(trim($item));
                }, $_POST['checkedFName']);

                if (empty($this->pathFiles)) {
                    $this->data['msg'] = "Incorrect file paths" . implode(', ', $_POST);
                } else {
                    $this->run();
                }
            }
        }

        private function run()
        {
            foreach ($this->pathFiles as $path) {
                if (file_exists($path)) {
                    is_dir($path) ? Path::delDir($path) : unlink($path);
                }
            }

            $notDeletedFiles = array_filter($this->pathFiles, function ($path) {
                return file_exists($path);
            });

            if (!empty($notDeletedFiles)) {
                $this->data['msg'] = 'Error deleting files: ' . implode(', ', $notDeletedFiles);
            } else {
                $this->data['result'] = 'success';
                $path = new Path($this->parentDir);
                $contentData = $path->getContentData();
                $this->data['content'] = HtmlMarkup::generate('table_files.twig', ['contentData' => $contentData]);
            }
        }
    }

    $newDeleteFiles = new DeleteFiles();
    $newDeleteFiles->echoJsonEncode();
