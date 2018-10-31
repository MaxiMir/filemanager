<?php

	//namespace Filemanager\Main\Render;
	
    require FM_PATH. 'vendor/Twig/Autoloader.php';
    
	function render($tmpl, $contentData, $header = null, $breadcrumbs = null)
	{
		try {
			Twig_Autoloader::register();			
			$loader = new Twig_Loader_Filesystem(FM_PATH. 'views');
			$twig = new Twig_Environment($loader);
			$template = $twig->loadTemplate($tmpl);
			
			if ($tmpl == '404.php') {
				return $template->render([]);			    
			} elseif ($tmpl == 'table_files.twig' || $tmpl == 'table_folders.twig') {
				return $template->render(['contentData' => $contentData]);
			} else {
				return $template->render([
											'header' => $header,
											'breadcrumbs' => $breadcrumbs,
											'contentData' => $contentData
										]);
			}
		} catch (Exception $e) {
			die ('ERROR: ' . $e->getMessage());
		}
	}



