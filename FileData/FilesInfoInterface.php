<?php

    namespace FM\FileData;

    interface FilesInfoInterface
    {
        public function __construct($fPath);
        public function getDataSet();
    }