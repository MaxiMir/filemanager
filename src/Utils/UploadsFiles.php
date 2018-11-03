<?php
    
	require_once "../config/Conf.php";
	require_once "../Main/PathInfo.php";
	require_once "../Main/FilesInfo.php";
	require_once "../Main/Render.php";	

	ini_set('post_max_size', '1000M'); // максимально допустимый размер данных, отправляемых POST-ом
	ini_set('upload_max_filesize', '500M'); // максимальный размер закачиваемого файла
	ini_set('max_file_uploads', "500"); 
	ini_set('max_execution_time', '3000'); // максимальное время в секундах, в течение которого скрипт должен полностью загрузиться
	

	$data = [
			  'msg' => '',
			  'result' => 'error'
			];

	if (!empty($_FILES)) {
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
	        	$filesPaths = glob($uploadDir . '{,.}*', GLOB_BRACE);
				$contentData = getFilesInfo($filesPaths);
				$data['content'] = render('table_files.twig', $contentData);					
			}
		}
	}

	header('Content-Type: application/json');
	echo json_encode($data);