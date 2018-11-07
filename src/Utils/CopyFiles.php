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
		$data['msg'] = "Incorrect method of sending data.<br>";
	} else {
    	$fName = $_POST['fName'];
    	$newRelPath = $_POST['newRelPath'];
    	$type = $_POST['type'];
    	$overwrite = $_POST['overwrite'] == 'y' ? true : false;
    	$relativePath = getRelPath($_SERVER['HTTP_REFERER']);
    	$parentDir = ROOT . $relativePath;
    	$newParentDir = ROOT . $newRelPath;
    	$oldPathFile = $parentDir . $fName;
    	$newPathFile = $newParentDir . $fName;
        
        if ($overwrite) {
            
        } else {
            
        }
        
        if (!file_exists($oldPathFile) || !file_exists($newParentDir)) {
           $data['msg'] .= "The selected directory does not exist"; 
        }    	
        
        if (file_exists($newPathFile)) {
            if ($type == 'file') {
                $data['quest'] = "File '{$fName}' exists, overwrite?";
                $data['Finfo'] = [
                                 'fName' => $fName,
                                 'oldPathFile' => $oldPathFile,
                                 'newPathFile' => $newPathFile
                ];
            }  elseif($type == 'folder') {
                $data['quest'] = "Directory '{$fName}' exists, overwrite all files, if names match?";
                $data['Dinfo'] = [
                                 'fName' => $fName,
                                 'oldPathFile' => $oldPathFile,
                                 'newPathFile' => $newPathFile
                ];                
            }     
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

	header('Content-Type: application/json');
	echo json_encode($data);