<?php   

    namespace FM\FileData;

    class FilesInfo implements FilesInfoInterface
    {
        private $fPaths;

        public function __construct($fPaths)
        {
            $this->fPaths = $fPaths;
        }

        public function getDataSet()
        {
            $dataSet = [
                'folders' => [],
                'files' => []
            ];

            foreach ($this->fPaths as $path) {
                $fileName = basename($path);
                if ($fileName == '.' || $fileName == '..') { continue; }
                $type = is_dir($path) ? 'folders' : 'files';
                $fileChangeDate = date("d.m.Y H:i:s", filemtime($path));
                $classNewFile = time() - filemtime($path) < TIME_NEW_FILE ? 'new-file' : '';
                $relUrl = FileFunc::getRelUrl($path);

                $dataSet[$type][$fileName]['path'] = $path;
                $dataSet[$type][$fileName]['fileChangeDate'] = $fileChangeDate;
                $dataSet[$type][$fileName]['classNewFile'] = $classNewFile;
                $dataSet[$type][$fileName]['url'] = FM_REL_PATH . "{$relUrl}";

                if ($type == 'folders') {
                    $size = FileFunc::getDirSize($path);
                } elseif ($type == 'files') {
                    $size = sprintf("%u", filesize($path));
                    $dataSet[$type][$fileName]['img'] = FileFunc::chooseImg($path);
                }

                $dataSet[$type][$fileName]['size'] = FileFunc::formatFileSize($size);
            }

            return $dataSet;
        }
    }