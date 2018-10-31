<?php
    
    namespace Filemanager\Utils\DeleteFiles;    

	require_once "../config/Conf.php";
	require_once "../Main/PathInfo.php";

	$data = [
	           'msg' => '',
	           'result' => 'error'
	        ];
	        
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		$data['msg'] .= "Incorrect method of sending data.<br>";
	} else {
    	$name = $_POST['name'];
    	$type = $_POST['type'];
    	$relativePath = getRelPath($_SERVER['HTTP_REFERER']);
    	$sep = $type == 'folder' ? SEP : '';
    	$pathFile = ROOT . $relativePath . $name . $sep;
    	
		if (!file_exists($pathFile)) {
		    $data['msg'] .= "Incorrect filetype. <br>";
		} else {
		    $type == 'folder' ? delDir($pathFile) : unlink($pathFile);
        	if (!file_exists($pathfile)) {
        		$data['result'] = 'success';
        	} else {
        	    $data['msg'] .= "Error deleting file {$name} <br>";	    
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
