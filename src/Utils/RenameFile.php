<?php

    use \FM\Render; 
    use \FM\FileData\FileFunc;
    use \FM\FileData\PathInfo; 
        
    require_once "../config/Conf.php";
    require_once '../FileData/FileFunc.php';
    require_once '../FileData/PathInfo.php';    
	require_once '../Render.php';

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
		$path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
		$relativePath = preg_replace('/\/'. FM_FOLDER_NAME .'/', '', $path, 1);
		$parentDir = ROOT . $relativePath;    
    	$pathOldFile = $parentDir . $oldName;
    	$pathNewFile = $parentDir . $newName;
		$isValidName = FileFunc::isValidName($newName);

		if ($newName == '') {
			$data['msg'] .= "File name is empty <br>";
		} elseif ($oldName == $newName) {
		    $data['msg'] .= "File names are not individual <br>";
		} elseif (strlen($newName) > 255) {
			$data['msg'] .= "File name is too long <br>";
		} elseif (!$isValidName) {
			$data['msg'] .= "It is recommended not to use these symbols: '! @ # $ & ~ % * ( ) [ ] { } ' \" \\ / : ; > < `' and space in the file name <br>";
		}

		if (!is_dir($parentDir)) {
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
	        	$path = new PathInfo($parentDir);
	        	$contentData = $path->getContentData();
				$data['content'] = Render::generate('table_files.twig', ['contentData' => $contentData]); 				
		    }
		}	
	}

	header('Content-Type: application/json');
	echo json_encode($data);