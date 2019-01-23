<?php

namespace FM\Utils;

/*
require_once '../../vendor/autoload.php';

use FM\Render\HtmlMarkup;
use FM\FileData\FileFunc;
use FM\FileData\PathInfo;


class CopyFiles implements UtilsInterface
{
    use Json;

    private $name;
    private $parentDir;
    private $pathNewFile;
    private $isDir;

    public function __construct()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->data['msg'] = "Incorrect method of sending data.<br>";
        } else {
            $postData = array_map(function ($data) {
                return FileFunc::getRelPath($data);
            }, $_POST);
            $fName = $postData['fName'];
            $newRelPath = $postData['newRelPath'];
            $type = $postData['type'];
            $overwrite = $postData['overwrite'] == 'Y' ? true : false;
            $path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
            $relativePath = preg_replace('/\/'. FM_FOLDER_NAME .'/', '', $path, 1);
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


    }
}
}
*/



*/
        

