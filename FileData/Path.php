<?php

    namespace FM\FileData;

    class Path implements PathInfoInterFace
    {
        private $path;
        private $isDir;
		private $isMain;

        public function __construct ($path)
        {
            $this->path = $path;
            $this->isDir = is_dir($path) ? true : false;
            $this->isMain = $path == ROOT ? true : false;
        }

        public function isValidPath()
        {
            return file_exists($this->path);
        }

		public static function delDir($fPath)
		{
			$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($fPath, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
		
			foreach ($iterator as $filename => $fileInfo) {
			    if ($fileInfo->isDir()) {
			        rmdir($filename);
				} else {
			        unlink($filename);
			    }
			}

            rmdir($fPath);
       }

		public function getFilesPaths($onlyDir = false)
		{
			$flag = $onlyDir ? GLOB_ONLYDIR : GLOB_BRACE;
			$pattern = $onlyDir ? "{$this->path}*" : "{$this->path}{,.}*";
			return glob($pattern, $flag);
		}
	
	    public function getPathsData($currDir = null)
	    {
		    $paths = $this->getFilesPaths(true);
		
		    return array_reduce($paths, function ($acc, $path) use ($currDir) {
			    $fName = basename($path);
			    $relPath = str_replace(ROOT, "/".FM_FOLDER_NAME."/", $path);
			    $isEmptyDir = empty(glob("{$path}/*", GLOB_ONLYDIR)) ? 'Y' : 'N';
			    $acc[$fName] = [
				    "path" => "{$path}/",
				    "relPath" => "{$relPath}/",
				    "isEmptyDir" => $isEmptyDir,
			    ];
			    return $acc;
		    }, []);
	    }

        public function getTemplate()
        {
            return $this->isDir ? 'folder.twig' : 'file.twig';
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
	
	    public function getHeader()
	    {
		    return $this->path == ROOT ? SEP : basename($this->path);
	    }

        public function getContentData()
        {
            $isDir = $this->isDir;

            $obj = $isDir ? new FilesInfo($this->getFilesPaths()) : new FileInfo($this->path);
            $contentData = $obj->getDataSet();

            if ($isDir) {
            	$rootInfo = new self(ROOT);
                $contentData['listDirsData'] = $rootInfo->getPathsData();
            }

            return $contentData;
        }
    };