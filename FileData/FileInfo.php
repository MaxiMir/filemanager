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
