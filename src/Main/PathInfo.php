<?php

	function isMainPage() 
	{
		return $_SERVER['REQUEST_URI'] == '/'.FM_FOLDER.'/';
	}


	function getCurrPath() 
	{	
		if (isMainPage()) { 
		    return ROOT . SEP;
		}
		return isset($_GET['url']) ? ROOT . $_GET['url'] : $_SERVER['REQUEST_URI'];
	}


	function getRelPath($url)
	{
	    $queryStr = parse_url($url, PHP_URL_QUERY);
	    return is_null($queryStr) ? SEP : explode('=', $queryStr)[1];
	}


	function isvalidPath($currPath)
	{
		return file_exists($currPath);
	}


	function getHeader($currPath)
	{
		if (isMainPage()) { 
		    return SEP; 
		}
		return basename($currPath);
	}


	function generateBreadcrumbs($currPath, $template)
	{
		$liHtml = [];
		$linksPath = '?url=/';

		$dataPath = explode(SEP, str_replace(ROOT, '', $currPath));
		$folders = array_filter($dataPath, function($folder) {
			return $folder != '';
		});
		$folders = array_values($folders);
		$indLastElem = sizeof($folders) - 1;

		foreach ($folders as $fKey => $fName) {
			$linksPath .= "{$fName}/";
			if ($fKey != $indLastElem) {
				$liHtml[] = "<li class='breadcrumb-item'><a href='{$linksPath}'>{$fName}</a></li>";
			} else {
				if ($template != 'file.twig') {
					$liHtml[] = "<li class='breadcrumb-item active' aria-current='page'>{$fName}</li>";
				}
			}
		}
		return implode("\n", $liHtml);
	}

	function chooseTemplate($currPath)
	{
		if (!isvalidPath($currPath)) {
	  		return '404.php';
		}	

		if (isMainPage()) { 
			return 'main.twig'; 
		}
		return is_dir($currPath) ? 'folder.twig' : 'file.twig';
	}


	function getTreePaths($dir = ROOT)
	{
		$treePath = [];

	    if ($dirHandle = opendir($dir)) {
	        while($fName = readdir($dirHandle)) {
	        	$pathFile = $dir . SEP . $fName;
	            if ($fName == '.' || $fName== '..') { 
	            	continue; 
	            }
	            if (is_dir($pathFile)) {
	                $treePath[$fName] = ['path' => $pathFile];
	                $treePath[$fName] = ['childDirs' => getTreePaths($pathFile)];
	            }
	        }
	        closedir($dirHandle);
	    }
	    return $treePath;
	}