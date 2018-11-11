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
		$data['msg'] .= "Incorrect method of sending data <br>";
	} else {
    	$pathFile = $_POST['pathFile'];
    	$isDir = is_dir($pathFile) ? true : false;
		$path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
		$relativePath = preg_replace('/\/src\//', '', $path, 1);
		$parentDir = ROOT . $relativePath;
    	
		if (!file_exists($pathFile)) {
		    $data['msg'] .= "Incorrect filetype <br>";
		} else {
		    $isDir ? FileFunc::delDir($pathFile) : unlink($pathFile);
        	if (file_exists($pathFile)) {
        	    $data['msg'] .= "Error deleting file <br>";
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
