<?php

    namespace FM\FileData;

    interface PathInfoInterFace
    {
        public function isValidPath();
        public function getHeader();
        public function getFilesPaths();
        public function generateBreadcrumbs();
        public function chooseTemplate();
        public function getContentData();
    }