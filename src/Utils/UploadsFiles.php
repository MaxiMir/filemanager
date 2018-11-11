<?php
    
    use \FM\Render;
    use \FM\FileData\PathInfo;    
    
    require_once "../config/Conf.php";
    require_once '../FileData/FileFunc.php';
    require_once '../FileData/PathInfo.php';    
	require_once '../Render.php';

	ini_set('post_max_size', '1000M'); // максимально допустимый размер данных, отправляемых POST-ом
	ini_set('upload_max_filesize', '500M'); // максимальный размер закачиваемого файла
	ini_set('max_file_uploads', "500"); 
	ini_set('max_execution_time', '3000'); // максимальное время в секундах, в течение которого скрипт должен полностью загрузиться
	

	$data = [
		'msg' => '',
		'result' => 'error'
	];

	if (!empty($_FILES)) {
		$pathNewFiles = [];
		$path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
		$relativePath = str_replace('src/', '', $path);
		$parentDir = ROOT . $relativePath;
 
		if (!is_dir($parentDir)) {
			$data['msg'] .= "Path is incorrect: <br>'{$parentDir}' <br>";
		} else {
			foreach($_FILES as $file) {
				if (!move_uploaded_file($file['tmp_name'], $parentDir . basename($file['name']))) {
					$data['msg'] = "An error occurred while loading files <br>";
				}
			}
			
			if ($data['msg'] == '') {
				$data['result'] = 'success';		
	        	$path = new PathInfo($parentDir);
	        	$contentData = $path->getContentData();
				$data['content'] = Render::generate('table_files.twig', ['contentData' => $contentData]);					
			}
		}
	}

	header('Content-Type: application/json');
	echo json_encode($data);