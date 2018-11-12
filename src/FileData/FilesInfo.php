<?php   

    namespace FM\FileData;

    require_once 'FilesInfoInterface.php';
    require_once 'FileFunc.php';

    class FilesInfo implements FilesInfoInterface
    {
        private $fPaths;
        private $dataSet = [
            'folders' => [],
            'files' => []
        ];

        public function __construct($fPaths)
        {
            $this->fPaths = $fPaths;
            $this->generateDataSet();
        }

        public function getDataSet()
        {
            return $this->dataSet;
        }

        public function generateDataSet()
        {
            foreach ($this->fPaths as $path) {
                $fileName = basename($path);
                if ($fileName == '.' || $fileName == '..') { continue; }
                $type = is_dir($path) ? 'folders' : 'files';
                $fileСhangeDate = date("d.m.Y H:i:s", filemtime($path));
                $classNewFile = time() - filemtime($path) < TIME_NEW_FILE ? 'new-file' : '';
                $relUrl = str_replace(ROOT, '', $path);
                $size = sprintf("%u", filesize($path));
                
                $this->dataSet[$type][$fileName]['path'] = $path;
                $this->dataSet[$type][$fileName]['fileСhangeDate'] = $fileСhangeDate;
                $this->dataSet[$type][$fileName]['classNewFile'] = $classNewFile;
                
                if ($type == 'folders') {
                    $this->dataSet[$type][$fileName]['url'] = FM_REL_PATH . "{$relUrl}/";
                } elseif ($type == 'files') {
                    $this->dataSet[$type][$fileName]['url'] = FM_REL_PATH . "{$relUrl}";
                    $this->dataSet[$type][$fileName]['size'] = FileFunc::formatFileSize($size);
                    $this->dataSet[$type][$fileName]['img'] = FileFunc::chooseImg($path);
                }
            }   
        }
    }