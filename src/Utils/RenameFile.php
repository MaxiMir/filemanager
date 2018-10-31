<?php

    namespace Filemanager\Utils\RenameFile;

	require_once "../config/Conf.php";
	require_once "../Main/PathInfo.php";
	require_once "../Main/FilesInfo.php";
	require_once "../Main/Render.php";

	$data = ['msg' => '', 'result' => 'error'];
	$relativePath = getRelPath($_SERVER['HTTP_REFERER']);
	$parentDir = ROOT . $relativePath;

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		$data['msg'] = "Incorrect method of sending data.<br>";
	} else {
    	$oldName = $_POST['oldName'];
    	$newName = $_POST['newName'];
    	$type = $_POST['type'];
    	$template = $type == 'folder' ? 'table_folders.twig' : 'table_files.twig';
    	$sep = $type == 'folder' ? SEP : '';
    	$pathOldFile = $parentDir . $oldName . $sep;
    	$pathNewFile = $parentDir . $newName . $sep;
    	
		if ($newName == '') {
			$data['msg'] .= "File name is empty <br>";
		} elseif ($oldName == $newName) {
		    $data['msg'] .= "File names are not individual <br>";
		} elseif (strlen($newName) > 255) {
			$data['msg'] .= "File name is too long <br>";
		} elseif (strpos($newName, '/') !== false) {
			$data['msg'] .= "File name not consist '/' <br>";
		}

		if ($relativePath == '' || !is_dir(ROOT . $relativePath)) {
			$data['msg'] .= "Path is incorrect <br>";
		}

		if (file_exists($pathNewFile)) {
			$data['msg'] .= "File with name '{$newName}' already exist";
		}

		if ($data['msg'] == '') {
		    if(!rename($pathOldFile, $pathNewFile)) {
		        $data['msg'] = 'Failed to rename file';
		    } else {
		        $data['result'] = 'success';
				$contentData = getFilesInfo([$pathNewFile]);
				//$data['htmlFiles'] = render($template, $contentData);	
		    }
		}	
	}

	header('Content-Type: application/json');
	echo json_encode($data);