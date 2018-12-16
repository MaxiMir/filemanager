<?php

    namespace FM\FileData;

    interface FileInfoInterface extends FilesInfoInterface
    {
        public function getExtension();
        public function getFileContent();
    }