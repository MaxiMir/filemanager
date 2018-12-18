<?php

    namespace FM\Utils;

    require_once '../../vendor/autoload.php';

    use \FM\Render\HtmlMarkup;
    use \FM\FileData\FileFunc;

    class GetListDirs implements UtilsInterface
    {
        use Json;

        private $path;
        private $currDir;
        
        public function __construct()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->data['msg'] = 'Incorrect method of sending data';
            } else {
            	$relativePath = FileFunc::getRelPath($_SERVER['HTTP_REFERER']);
            	$this->currDir = ROOT . $relativePath;
                $path = FileFunc::cleanData($_POST['path']);

                if (!is_dir($path)) {
                    $this->data['msg'] = "Error, '{$path}' is not directory!";
                } else {
                    $this->path = $path;
                    $this->run();
                }
            }
        }

        private function run()
        {
            $this->data['result'] = 'success';
            $contentData = FileFunc::getPathsData($this->path, $this->currDir);
            $this->data['content'] = HtmlMarkup::generate('list_dirs.twig', ['contentData' => ['listDirsData' => $contentData]]);
        }
    }

    $newGetListDirs = new GetListDirs();
    $newGetListDirs->echoJsonEncode();