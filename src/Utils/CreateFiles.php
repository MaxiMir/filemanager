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
		$data['msg'] .= "Incorrect method of sending data.<br>";
	} else {
		$name = $_POST['name'];
		$type = $_POST['type'];
		$isDir = $type == 'folder' ? true : false;
		$path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
		$relativePath = preg_replace('/\/src\//', '', $path, 1);
		$parentDir = ROOT . $relativePath;
		$pathNewFile = $parentDir . $name;
		$isValidName = FileFunc::isValidName($name);
		
		if ($name == '') {
			$data['msg'] .= "File name is empty <br>";
		} elseif (strlen($name) > 255) {
			$data['msg'] .= "File name is too long <br>";
		} elseif (!$isValidName) {
			$data['msg'] .= "It is recommended not to use these symbols: '! @ # $ & ~ % * ( ) [ ] { } ' \" \\ / : ; > < `' and space in the file name <br>";
		}

		if (file_exists($pathNewFile)) {
			$data['msg'] .= "File with name '{$name}' already exist <br>";
		}
		
		if ($relativePath == '' || !is_dir($parentDir)) {
			$data['msg'] .= "Path is incorrect <br> {$relativePath}  <br>";
		}

		if ($data['msg'] == '') {
		    $resOper = $isDir ? mkdir($pathNewFile) : touch($pathNewFile);
		    
		    if(!$resOper) {
		        $data['msg'] .= "Could not create file '{$name}' <br>";    
		    } else {
	        	$data['result'] = "success";
	        	$path = new PathInfo($parentDir);
	        	$contentData = $path->getContentData();
				$data['content'] = Render::generate('table_files.twig', ['contentData' => $contentData]);
		    }	
	    }
	}
	
	header('Content-Type: application/json');
	echo json_encode($data);
