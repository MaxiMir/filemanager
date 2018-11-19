<?php

    namespace FM\FileData;

    class FileFunc
    {
        public function getRelUrl($path)
        {
            $relPath = str_replace(ROOT, '', $path);
            return preg_match('/.htaccess|index.php/', $relPath) ? "?url={$relPath}" : $relPath;          
        }

        public static function formatFileSize($numberOfBytes)
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

        public static function getExtension($fPath)
        {
            return pathinfo($fPath, PATHINFO_EXTENSION);
        }   

        public static function chooseImg($fPath)
        {   
            $fileExt = self::getExtension($fPath);

            if (file_exists(FM_PATH . "css/img/{$fileExt}.png")) {
                return FM_REL_PATH . "css/img/{$fileExt}.png";
            } else {
                return FM_REL_PATH . 'css/img/default.png';
            }           
        }   

        public static function getFileContent($fPath) {
            $content = [];
            
            if (file_exists($fPath) && is_readable($fPath)) {
                $handler = fopen($fPath, "rb"); 
               
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
        
        public static function isValidName($fName)
        {
            $stop_symbols = ['!', '@', '#', '$', '&', '~', '%', '*', '(', ')', '[', ']', '{', '}', '\'', '"', '\\', '/', ':', ';', '>', '<', '`', ' '];
            
            foreach($stop_symbols as $symbol) {
                if (strpos($fName, $symbol) !== false) {
                    return false;
                }
            }
            return true;
        }

        public static function delDir($fPath)
        {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($fPath, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST); 

            foreach ($iterator as $filename => $fileInfo) {
                if ($fileInfo->isDir()) {
                    rmdir($filename);
                } else {
                    unlink($filename);
                }
            }

            rmdir($fPath);
        }
    }