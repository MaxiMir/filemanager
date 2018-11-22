<?php

    namespace FM\Utils;

	require_once "../config/Conf.php";

	class ChangeContent
    {
        private $code;
        private $pathFile;
        private $data = [
            'msg' => '',
            'result' => 'error'
        ];

        public function __construct()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->data['msg'] = "Incorrect method of sending data <br>";
            } else {
                $this->code = $_POST['code'];
                $path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
                $relativePath = preg_replace('/\/'. FM_FOLDER_NAME .'/', '', $path, 1);
                $query = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);
                $urlQuery = ($query != '') ? str_replace('url=', '', $query) : '';
                $this->pathFile = ROOT . $relativePath . $urlQuery;
                $this->writeFile();
            }
        }

        private function writeFile()
        {
            if (!is_writable($this->pathFile)) {
                $this->data['msg'] .=  'File is not writable <br>';
            } else {
                $handle = fopen($this->pathFile, 'w');
                if ($handle) {
                    try {
                        fwrite($handle, $this->code);
                    } finally {
                        fclose($handle);
                        $this->data['result'] =  'success';
                    }
                }
            }
        }

        public function echoJsonEncode()
        {
            header('Content-Type: application/json');
            echo json_encode($this->data);
        }
    }

    $newContent = new ChangeContent();
    $newContent->echoJsonEncode();
