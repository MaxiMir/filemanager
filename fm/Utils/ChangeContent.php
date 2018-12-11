<?php

    namespace FM\Utils;

    require_once '../../vendor/autoload.php';

    use FM\FileData\FileFunc;

	class ChangeContent implements UtilsInterface
    {
        use Json;

        private $pathFile;
        private $code;

        public function __construct()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->data['msg'] = 'Incorrect method of sending data';
            } else {
                $query = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);
                $urlQuery = ($query != '') ? str_replace('url=', '', $query) : '';
                $path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
                $relativePath = preg_replace('/\/'. FM_FOLDER_NAME .'/', '', $path, 1);
                $this->pathFile = ROOT . $relativePath . $urlQuery;
                $this->code = FileFunc::cleanData($_POST['code']);
                if (!is_writable($this->pathFile)) {
                    $this->data['msg'] = 'File is not writable';
                } else {
                    $this->run();
                }
            }
        }

        private function run()
        {
            $handle = fopen($this->pathFile, 'w');
            if ($handle) {
                try {
                    fwrite($handle, $this->code);
                } finally {
                    fclose($handle);
                    $this->data['result'] = 'success';
                }
            }
        }
    }

    $newChangeContent = new ChangeContent();
    $newChangeContent->echoJsonEncode();
