<?php	
    
	function getFilesInfo($filePaths) 
	{
		$dataSet = [
			'folders' => [],
			'files' => []
		];

		foreach ($filePaths as $path) {
			$fileName = basename($path);
			if ($fileName == '.' || $fileName == '..') { continue; }
			$type = is_dir($path) ? 'folders' : 'files';
			$fileСhangeDate = date("d.m.Y H:i:s", filemtime($path));
			$classNewFile = time() - filemtime($path) < TIME_NEW_FILE_SEC ? 'new-file' : '';
			$relUrl = str_replace(ROOT, '', $path);
			$size = sprintf("%u", filesize($path));
            
            $dataSet[$type][$fileName]['path'] = $path;
			$dataSet[$type][$fileName]['fileСhangeDate'] = $fileСhangeDate;
			$dataSet[$type][$fileName]['classNewFile'] = $classNewFile;
			
			if ($type == 'folders') {
				$dataSet[$type][$fileName]['url'] = "?url={$relUrl}/";
			} elseif ($type == 'files') {
				$dataSet[$type][$fileName]['url'] = "?url={$relUrl}";
				$dataSet[$type][$fileName]['size'] = formatFileSize($size);
				$dataSet[$type][$fileName]['img'] = chooseImg($path);
			}
		}
		return $dataSet;
	}
	
	function getFileInfo($path) {
		return [
		        'fileExt' => getExtension($path),
		        'fileContent' => getFileContent($path)
		       ];
	}	

	function formatFileSize($numberOfBytes)
	{
		$amountОfInformation = [
			'Gb' => 1024 ** 3,
			'Mb' => 1024 ** 2,
			'Kb' => 1024
		];

		foreach ($amountОfInformation as $unit => $size) {
			if ($numberOfBytes >= $size) {
				return number_format($numberOfBytes / $size, 1, '.', '') . " {$unit}";
			} 
		}
		return "{$numberOfBytes} b";
	}

	function getExtension($path)
	{
	    return pathinfo($path, PATHINFO_EXTENSION);
	}

	function chooseImg($filename)
	{	
		$fileExt = getExtension($filename);

		if (file_exists(FM_PATH . "css/img/{$fileExt}.png")) {
			return "css/img/{$fileExt}.png";
		} else {
			return 'css/img/default.png';
		} 			
	}	

	function getFileContent($path) {
	    $content = [];
    	
    	if (file_exists($path) && is_readable($path)) {
        	$handler = fopen($path, "rb"); 
        	
        	if (!$handler) {
        	    return 'Error reading file';
        	} else {
        		try {
        			while (!feof($handler)) { 
        				$content[] = fgets($handler, 1024); 
        			}
        		} finally { 
        			fclose($handler);		
        		}    	    
        	}	
    	}
    	return implode('', $content);
	}
	
	function isValidName($name)
	{
	    foreach(STOP_SYMBOLS as $symbol) {
	        if (strpos($name, $symbol) !== false) {
	            return false;
	        }
	    }
	    return true;
	}	    
	    