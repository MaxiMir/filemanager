<?php

	require_once "../config/Conf.php";
	require_once "../Main/PathInfo.php";
	require_once "../Main/FilesInfo.php";
	require_once "../Main/Render.php";

	$data = [
			  'msg' => '',
			  'result' => 'error'
	];

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		$data['msg'] = "Incorrect method of sending data.<br>";
	} else {
    	$oldName = $_POST['oldName'];
    	$newName = $_POST['newName'];
    	$type = $_POST['type'];
    	$relativePath = getRelPath($_SERVER['HTTP_REFERER']);
    	$parentDir = ROOT . $relativePath;	    
    	$pathOldFile = $parentDir . $oldName;
    	$pathNewFile = $parentDir . $newName;
    	
		if ($newName == '') {
			$data['msg'] .= "File name is empty <br>";
		} elseif ($oldName == $newName) {
		    $data['msg'] .= "File names are not individual <br>";
		} elseif (strlen($newName) > 255) {
			$data['msg'] .= "File name is too long <br>";
		} elseif (!isValidName($newName)) {
			$data['msg'] .= "It is recommended not to use these symbols: '! @ # $ & ~ % * ( ) [ ] { } ' \" \\ / : ; > < `' and space in the file name <br>";
		}

		if ($relativePath == '' || !is_dir($parentDir)) {
			$data['msg'] .= "Path is incorrect: '{$parentDir}' <br>";
		}

		if (file_exists($pathNewFile)) {
			$data['msg'] .= "File with name '{$newName}' already exist";
		}

		if ($data['msg'] == '') {
		    if(!rename($pathOldFile, $pathNewFile)) {
		        $data['msg'] = 'Failed to rename file';
		    } else {
		        $data['result'] = 'success';
	        	$filesPaths = glob($parentDir . '{,.}*', GLOB_BRACE);
				$contentData = getFilesInfo($filesPaths);
				$data['content'] = render('table_files.twig', $contentData);				
		    }
		}	
	}

	header('Content-Type: application/json');
	echo json_encode($data);