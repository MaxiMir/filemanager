<?php

    namespace FM\FileData;

    interface PathInterFace
    {
	    public function __construct ($path);
	
	    public function isValidPath();
	
	    public static function delDir($fPath);
	
	    public function getFilesPaths($onlyDir);
	
	    public function getPathsData($currDir);
	
	    public function getTemplate();
	
	    public function generateBreadcrumbs();
	
	    public function getHeader();
	
	    public function getContentData();
    }