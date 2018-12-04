<?php

    namespace FM\FileData;

    require_once 'FileInfoInterface.php';

    class FileInfo implements FileInfoInterface
    {
        private $fPath;
        private $dataSet;

        public function __construct($fPath)
        {
            $this->fPath = $fPath;
            $this->generateDataSet();
        }

        public function getDataSet()
        {
            return $this->dataSet;
        }       

        public function generateDataSet()
        {
            $this->dataSet = [
                'fileExt' => FileFunc::getExtension($this->fPath),
                'fileContent' => FileFunc::getFileContent($this->fPath)
            ];           
        }
    }
