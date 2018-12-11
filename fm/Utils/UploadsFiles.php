<?php

	namespace FM\Utils;

    require_once '../../vendor/autoload.php';
    
    use FM\Render\HtmlMarkup;
    use FM\FileData\FileFunc;
    use FM\FileData\PathInfo;

	class UploadsFiles implements UtilsInterface
	{
	    use Json;

		private $parentDir;
		
		public function __construct()
		{
			ini_set('post_max_size', '1000M'); // максимально допустимый размер данных, отправляемых POST-ом
			ini_set('upload_max_filesize', '500M'); // максимальный размер закачиваемого файла
			ini_set('max_file_uploads', "500");
			ini_set('max_execution_time', '3000'); // максимальное время в секундах, в течение которого скрипт должен полностью загрузиться
			
			if (!empty($_FILES)) {
				$relativePath = FileFunc::getRelPath($_SERVER['HTTP_REFERER']);
				$this->parentDir = ROOT . $relativePath;
                if (!is_dir($this->parentDir)) {
                    $this->data['msg'] = "Path is incorrect: <br>'{$this->parentDir}'";
                } else {
                    $this->run();
                }
			}
		}
		
		private function run()
		{
            foreach($_FILES as $file) {
                if (!move_uploaded_file($file['tmp_name'], $this->parentDir . basename($file['name']))) {
                    $this->data['msg'] = 'An error occurred while loading files';
                }
            }
				
            if ($this->data['msg'] == '') {
                $this->data['result'] = 'success';
                $path = new PathInfo($this->parentDir);
                $contentData = $path->getContentData();
                $this->data['content'] = HtmlMarkup::generate('table_files.twig', ['contentData' => $contentData]);
            }
		}
	}
	
	$newUploadFiles = new UploadsFiles();
	$newUploadFiles->echoJsonEncode();