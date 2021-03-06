<?php

    namespace FM\FileData;

    class FileFunc
    {
        public static function getRelUrl($fpath)
        {
            $relPath = str_replace(ROOT, '', $fpath);
            $relPath .= is_dir($fpath) ? "/" : "";
            return preg_match('/.htaccess|index.php|style.css|composer.json|composer.lock|README.md/', $relPath) ? "?url={$relPath}" : $relPath;
        }

        public static function getDirSize($fpath)
        {
            $size = 0;

            $iterator = new \RecursiveIteratorIterator(
               new \RecursiveDirectoryIterator($fpath, \FilesystemIterator::SKIP_DOTS),
               \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $filename => $fileInfo) {
                if ($fileInfo->isFile()) {
                    $size += filesize($filename);
                }
            }

            return $size;
        }

        public static function formatFileSize($numberOfBytes)
        {
            $amountOfInformation = [
                'Gb' => 1024 ** 3,
                'Mb' => 1024 ** 2,
                'Kb' => 1024
            ];

            foreach ($amountOfInformation as $unit => $size) {
                if ($numberOfBytes >= $size) {
                    return number_format($numberOfBytes / $size, 1, '.', '') . " {$unit}";
                }
            }
            return "{$numberOfBytes} b";
        }

        public static function getRelPath($url)
        {
            $path = parse_url($url, PHP_URL_PATH);
            return preg_replace('/\/'. FM_FOLDER_NAME .'\//', '', $path, 1);
        }

        public static function isValidName($fName)
        {
            $stop_symbols = ['!', '@', '#', '$', '&', '~', '%', '*', '(', ')', '[', ']', '{', '}', '\'', '"', '\\', '/', ':', ';', '>', '<', '`', ' '];
            $fNameLenght = strlen($fName);

            if ($fNameLenght == 0 && $fNameLenght > 255) {
                return false;
            }

            foreach($stop_symbols as $symbol) {
                if (strpos($fName, $symbol) !== false) {
                    return false;
                }
            }

            return true;
        }

        public static function cleanData($data)
        {
            if (is_array($data)) {
                return array_map(function ($item) {
                    return htmlspecialchars(trim($item));
                }, $data);
            }

            return htmlspecialchars(trim($data));
        }
        
    }