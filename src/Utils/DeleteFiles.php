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
    	$pathFile = $_POST['pathFile'];
		$relativePath = getRelPath($_SERVER['HTTP_REFERER']);
		$parentDir = ROOT . $relativePath;
    	$isDir = is_dir($pathFile) ? true : false;
    	
		if (!file_exists($pathFile)) {
		    $data['msg'] .= "Incorrect filetype. <br>";
		} else {
		    $isDir ? delDir($pathFile) : unlink($pathFile);
        	if (file_exists($pathFile)) {
        	    $data['msg'] .= "Error deleting file <br>";
        	} else {
        		$data['result'] = 'success';
	        	$filesPaths = glob($parentDir . '{,.}*', GLOB_BRACE);
				$contentData = getFilesInfo($filesPaths);
				$data['content'] = render('table_files.twig', $contentData);   
        	}		    
		}
	}

	function delDir($dir)
	{
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST); 
        foreach ($iterator as $filename => $fileInfo) {
            if ($fileInfo->isDir()) {
                rmdir($filename);
            } else {
                unlink($filename);
            }
        }
        rmdir($dir);
	}

	header('Content-Type: application/json');
	echo json_encode($data);
