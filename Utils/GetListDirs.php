<?php

    namespace FM\Utils;

    require_once '../../vendor/autoload.php';
    
    use \FM\Render\HtmlMarkup;
    use FM\FileData\Path;
    use \FM\FileData\FileFunc;

    class GetListDirs implements UtilsInterface
    {
        use Json;

        private $paths;
        private $currDir;

        public function __construct()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->data['msg'] = 'Incorrect method of sending data';
            } else {
                $relativePath = FileFunc::getRelPath($_SERVER['HTTP_REFERER']);
                $this->currDir = ROOT . $relativePath;
                $this->paths = FileFunc::cleanData($_POST['paths']);
                $this->run();
            }
        }

        private function run()
        {
            foreach ($this->paths as $path) {
                if (!file_exists($path)) {
                    $this->data['msg'] = "Path is not exists: {$path}";
                } else {
                	$pathObj = new Path($path);
                    $contentData = $pathObj->getPathsData($this->currDir);
                    $this->data['content'][$path] = HtmlMarkup::generate('list_dirs.twig', ['contentData' => ['listDirsData' => $contentData]]);
	                $this->data['result'] = 'success';
                }
            }
        }
    }

    $newGetListDirs = new GetListDirs();
    $newGetListDirs->echoJsonEncode();