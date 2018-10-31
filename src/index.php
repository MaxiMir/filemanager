<?php

    namespace Filemanager;

 	require_once 'vendor/autoload.php';
	require_once 'Main/PathInfo.php';
	require_once 'Main/FilesInfo.php';
	require_once 'Main/Render.php';
	
	$currPath = getCurrPath();
	$filesPaths = glob($currPath . '{,.}*', GLOB_BRACE);
	$template = chooseTemplate($currPath);
	$header = getHeader($currPath);
	$breadcrumbs = generateBreadcrumbs($currPath, $template);
	
	if ($template == 'file.twig') {
		$contentData = getFileInfo($currPath);
	} else {
		$contentData = getFilesInfo($filesPaths);
	}

	print render($template, $contentData, $header, $breadcrumbs);
    	
#  Загрузка новых файлов в текущую директорию
# Файлы и папки с одинаковыми названиями
# ? правильная структура и подключение файлов из /actions
# анимация загрузки
# вставка новых файлов
# верстка
# роутинг
# 404 c сохранением урла