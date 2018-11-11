<?php

	require_once "../config/Conf.php";

	$data = [
		'msg' => '',
		'result' => 'error'
    ];
	        
	$code = $_POST['code'];
	$path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
	$relativePath = preg_replace('/\/src\//', '', $path, 1);
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
        			fclose($handle);
        			$data['result'] =  'success';
        		}
        	}
        }	    
	}	

	header('Content-Type: application/json');
	echo json_encode($data);	