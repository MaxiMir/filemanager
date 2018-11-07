<?php

	require_once "../config/Conf.php";
	require_once "../Main/PathInfo.php";

	$data = [
	           'msg' => '',
	           'result' => 'error'
    ];
	        
	$code = $_POST['code'];
	$relativePath = getRelPath($_SERVER['HTTP_REFERER']);
	$pathFile = ROOT . $relativePath;

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		$data['msg'] = "Incorrect method of sending data <br>";
	} else {
        if (!is_writable($pathFile)) {
            $data['msg'] .=  'File is not writable <br>';
        } else {    
        	$handle = fopen($pathFile, 'w'); 
        	if ($handle) {
        		try {
        			fwrite($handle, $code);
        		} finally {
        			fclose($handler);
        			$data['result'] =  'success';
        		}
        	}
        }	    
	}	

	header('Content-Type: application/json');
	echo json_encode($data);	