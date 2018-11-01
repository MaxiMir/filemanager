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
		$data['msg'] .= "Incorrect method of sending data.<br>";
	} else {
		$name = $_POST['name'];
		$type = $_POST['type'];
		$isDir = $type == 'folder' ? true : false;
		$sep = $isDir ? SEP : '';
		$relativePath = getRelPath($_SERVER['HTTP_REFERER']);
		$parentDir = ROOT . $relativePath;
		$pathNewFile = $parentDir . $name . $sep;
		
		if ($name == '') {
			$data['msg'] .= "File name is empty <br>";
		} elseif (strlen($name) > 255) {
			$data['msg'] .= "File name is too long <br>";
		} elseif (strpos($name, '/') !== false) {
			$data['msg'] .= "File name not consist '/' <br>";
		}

		if (file_exists($pathNewFile)) {
			$data['msg'] .= "File with name '{$name}' already exist";
		}
		
		if ($relativePath == '' || !is_dir($parentDir)) {
			$data['msg'] .= "Path is incorrect <br>";
		}

		if ($data['msg'] == '') {
		    $resOper = $isDir ? mkdir($pathNewFile) : touch($pathNewFile);
		    
		    if(!$resOper) {
		        $data['msg'] .= "Could not create file '{$name}'";    
		    } else {
	        	$data['result'] = "success";
	        	$filesPaths = glob($parentDir . '{,.}*', GLOB_BRACE);
				$contentData = getFilesInfo($filesPaths);
				$data['html'] = render('table_files.twig', $contentData);
		    }	
	    }
	}
	
	header('Content-Type: application/json');
	echo json_encode($data);
