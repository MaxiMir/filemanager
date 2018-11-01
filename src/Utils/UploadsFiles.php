<?php
    
	require_once "../config/Conf.php";
	require_once "../Main/PathInfo.php";
	require_once "../Main/FilesInfo.php";
	require_once "../Main/Render.php";	

	ini_set('post_max_size', '500M');
	ini_set('upload_max_filesize', '400M');
	ini_set('max_execution_time', '3000');
	ini_set('max_input_time', '6000');

	$data = [
			  'msg' => '',
			  'result' => 'error'
			];

	if (empty($_FILES)) {
		$data['msg'] .= "Files not found  <br>";
	} else {
    	$relativePath = getRelPath($_SERVER['HTTP_REFERER']);
	    $uploadDir = ROOT . $relativePath;
	    $pathNewFiles = [];
	    
		if (!is_dir($uploadDir)) {
			$data['msg'] .= "Path is incorrect: '{$parentDir}' <br>";
		} else {
			foreach($_FILES as $file) {
				if (!move_uploaded_file($file['tmp_name'], $uploadDir . basename($file['name']))) {
					$data['msg'] = 'An error occurred while loading files';
				}
			}
			
			if ($data['msg'] == '') {
				$data['result'] = 'success';
				$data['msg'] = 'Download successful';
	        	$filesPaths = glob($uploadDir . '{,.}*', GLOB_BRACE);
				$contentData = getFilesInfo($filesPaths);
				$data['html'] = render('table_files.twig', $contentData);					
			}
		}
	}

	header('Content-Type: application/json');
	echo json_encode($data);