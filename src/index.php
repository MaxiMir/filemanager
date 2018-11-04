<?php

    namespace Filemanager;

 	require_once 'vendor/autoload.php';
	require_once 'Main/PathInfo.php';
	require_once 'Main/FilesInfo.php';
	require_once 'Main/Render.php';
	
	$currPath = getCurrPath();
	$filesPaths = glob($currPath . '{,.}*', GLOB_BRACE);
	$tmpl = chooseTemplate($currPath);
	$header = getHeader($currPath);
	$breadcrumbs = generateBreadcrumbs($currPath, $template);
	$contentData = $tmpl == 'file.twig' ? getFileInfo($currPath) : getFilesInfo($filesPaths);

	print render($tmpl, $contentData, $header, $breadcrumbs);
	
	
/* TODO:   
    * картинки без перехода на страницу + превью.
    * анимация загрузки страницы
    * верстка
    * роутинг
    * 404 c сохранением урла
*/

echo "<pre>";
//print_r(getTreePaths());