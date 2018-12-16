<?php

    namespace FM\Utils;

    require_once '../../vendor/autoload.php';

    use \FM\Render\HtmlMarkup;
    use \FM\FileData\FileFunc;

    class GetListDirs implements UtilsInterface
    {
        use Json;

        private $path;

        public function __construct()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->data['msg'] = 'Incorrect method of sending data';
            } else {
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
            $contentData = FileFunc::getPathsData($this->path);
            $this->data['content'] = HtmlMarkup::generate('list_dirs.twig', ['contentData' => $contentData]);
        }
    }

    $newGetListDirs = new GetListDirs();
    $newGetListDirs->echoJsonEncode();