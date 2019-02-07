<?php

    namespace FM\FileData;

    class FileInfo implements FileInfoInterface
    {
        private $fPath;

        public function __construct($fPath)
        {
            $this->fPath = $fPath;
        }

        public function getExtension()
        {
            return pathinfo($this->fPath, PATHINFO_EXTENSION);
        }

        public static function chooseImg($fPath)
        {
            $fileExt = trim(pathinfo($fPath, PATHINFO_EXTENSION));

            if (file_exists(FM_PATH . "css/img/{$fileExt}.png")) {
                return FM_REL_PATH . "css/img/{$fileExt}.png";
            } else {
                return FM_REL_PATH . 'css/img/default.png';
            }
        }

        public function getFileContent()
        {
            $content = [];

            if (is_readable($this->fPath)) {
                $handler = fopen($this->fPath, "rb");

                if (!$handler) {
                    return 'Error reading file';
                } else {
                    try {
                        while (!feof($handler)) {
                            $content[] = fgets($handler, 1024);
                        }
                    } finally {
                        fclose($handler);
                    }
                }
            }

            return implode('', $content);
        }

        public function getDataSet()
        {
            $ext = $this->getExtension();
            $content = $this->getFileContent();

            return [
                'fileExt' => $ext,
                'fileContent' => $content
            ];
        }
    }
