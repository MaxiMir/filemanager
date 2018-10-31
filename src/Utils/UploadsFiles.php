<?php

    namespace Filemanager\Utils\UploadsFiles;
    
	require_once "../config/Conf.php";
	require_once "../Actions/PathInfo.php";
	require_once "../Actions/FilesInfo.php";
	require_once "../Actions/Render.php";	

	ini_set('post_max_size', '500M');
	ini_set('upload_max_filesize', '400M');
	ini_set('max_execution_time', '3000');
	ini_set('max_input_time', '6000');

	$data = ['msg' => '', 'result' => 'error'];
	$relativePath = getRelPath($_SERVER['HTTP_REFERER']);
	$uploadDir = ROOT . $relativePath;
	$pathNewFiles = [];

	if (empty($_FILES)) {
		$data['msg'] .= "Files not found  <br>";
	} else {
		if (!is_dir($uploadDir)) {
			$data['msg'] .= "Path is incorrect <br>";
		}

		if ($data['msg'] == '') {
			foreach($_FILES as $file) {
				if (!move_uploaded_file($file['tmp_name'], $uploadDir . basename($file['name']))) {
					$data['msg'] = 'An error occurred while loading files';
				} else {
					$data['result'] = 'success';
					$data['msg'] = 'Download successful';
					$pathNewFiles[] = $uploadDir . basename($file['name']);	
				}
			}

			if (!empty($pathNewFiles)) {
				$contentData = getFilesInfo($pathNewFiles);
				$data['htmlFiles'] = render('table_files.twig', $contentData);
			}
		}
	}

	header('Content-Type: application/json');
	echo json_encode($data);