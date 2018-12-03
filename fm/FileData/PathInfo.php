<?php

    namespace FM\FileData;

    class PathInfo implements PathInfoInterFace
    {
        private $path;
        private $isDir;
        private $isMain;

        public function __construct ($path) {
            $this->path = $path;
            $this->isDir = is_dir($path) ? true : false;
            $this->isMain = $path == ROOT ? true : false;
        }

        public function isValidPath()
        {
            return file_exists($this->path);
        }

        public function getHeader()
        {
            return $this->path == ROOT ? SEP : basename($this->path);
        }

        public function getFilesPaths()
        {
            return glob($this->path . '{,.}*', GLOB_BRACE);
        }

        public function generateBreadcrumbs()
        {
            $liHtml = [];
            $linksPath = '/';

            if ($this->isMain) {
                return [];
            }

            $dataPath = explode(SEP, str_replace(ROOT, '', $this->path));
            $folders = array_filter($dataPath, function($folder) {
                return $folder != '';
            });
            $folders = array_values($folders);
            $indLastElem = sizeof($folders) - 1;

            foreach ($folders as $fKey => $fName) {
                $linksPath .= "{$fName}/";
                if ($fKey != $indLastElem) {
                    $liHtml[] = "<li class='breadcrumb-item'><a href='/" . FM_FOLDER_NAME . "{$linksPath}'>{$fName}</a></li>";
                } else {
                    if ($this->isDir) {
                        $liHtml[] = "<li class='breadcrumb-item active' aria-current='page'>{$fName}</li>";
                    }
                }
            }

            return implode("\n", $liHtml);
        }

        public function chooseTemplate()
        {
            return $this->isDir ? 'folder.twig' : 'file.twig';
        }

        public function getContentData()
        {
            $obj = $this->isDir ? new FilesInfo($this->getFilesPaths()) : new FileInfo($this->path);
            return $obj->getDataSet();
        }
    };