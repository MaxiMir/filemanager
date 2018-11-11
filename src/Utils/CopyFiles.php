<?php
    
    use \FM\Render;  
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
    	$fName = $_POST['fName'];
    	$newRelPath = $_POST['newRelPath'];
    	$type = $_POST['type'];
    	$overwrite = $_POST['overwrite'] == 'y' ? true : false;
        $path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
        $relativePath = preg_replace('/\/src\//', '', $path, 1);
        $oldParentDir = ROOT . $relativePath;   
    	$newParentDir = ROOT . $newRelPath;
    	$oldPathFile = $parentDir . $fName;
    	$newPathFile = $newParentDir . $fName;
        
        if ($overwrite) {
            
        } else {
            if (!file_exists($oldPathFile) || !file_exists($newParentDir)) {
                $data['msg'] .= "The selected directory does not exist"; 
            }  
            
            if (file_exists($newPathFile)) {
                if ($type == 'file') {
                    $data['quest'] = "File '{$fName}' exists, overwrite?";
                }  elseif($type == 'folder') {
                    $data['quest'] = "Directory '{$fName}' exists, overwrite all files, if names match?";
                }  
                $data['info'] = [
                                 'fName' => $fName,
                                 'type' => $type,
                                 'oldPathFile' => $oldPathFile,
                                 'newPathFile' => $newPathFile
                ];  
            } else {
        		if ($data['msg'] == '') {
                    if ($type == 'file') {
                        if (!copy($parentDir . $fName, $newParentDir . $fName)) {
                             $data['msg'] .= "File {$fName} could not be copied";    
                        }    
                       
                    }
                } elseif ($type == 'folder') {
                    
                }            
            }             
        }
	}
	
	
	$copyDir = function () use ($fName, $parentDir, $oldPathFile, $newPathFile) {
	    $error = [];
	    if (!mkdir($newPathFile)) {
	        $error[] = "Failed to create directory {$fName}"; 
	    } else {
	        $files = glob($oldPathFile . '{,.}*', GLOB_BRACE);
	        foreach ($files as $file) {
	            //$res = is_file($file) ? copy($file, )
	        }
	    }
	};

	    


	header('Content-Type: application/json');
	echo json_encode($data);