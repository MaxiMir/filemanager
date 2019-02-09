<?php
	
	namespace FM\Utils;
	
	session_start();
	
	require_once '../../vendor/autoload.php';
	
	use FM\FileData\FileFunc;
	
	class CopyFiles implements UtilsInterface
	{
		use Json;
		
		private $name;
		private $oldPath;
		private $newParentPath;
		private $newPath;
		private $isDir;
		private $isOverwrite;
		
		public function __construct()
		{
			if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
				$this->data['msg'] = "Incorrect method of sending data.<br>";
			} else {
				$postData = FileFunc::cleanData($_POST);
				$this->name = $postData['name'];
				$this->isOverwrite = $postData['overwrite'] == 'Y' ? true : false;
				
				if ($this->isOverwrite) {
					if ($this->name !== $_SESSION['copyFilesData']['name']) {
						$this->data['msg'] = 'Error, unknown file';
					} else {
						$this->isDir = $_SESSION['copyFilesData']['isDir'];
						$this->oldPath = $_SESSION['copyFilesData']['oldPath'];
						$this->newParentPath = $_SESSION['copyFilesData']['newParentPath'];
						$this->run();
					}
				} else {
					$relativePath = FileFunc::getRelPath($_SERVER['HTTP_REFERER']);
					$this->name = $postData['name'];
					$this->oldPath = ROOT . $relativePath . $this->name;
					$this->newParentPath = $postData['newParentDir'];
					$this->newPath = $this->newParentPath . $this->name;
					$this->isDir = ($postData['type'] == 'folder') ? true : false;
					$isRunnable = !file_exists($this->newPath)|| file_exists($this->newPath) && $this->isOverwrite;
					
					if ($isRunnable) {
						$this->run();
					} elseif (!file_exists($this->oldPath) || !file_exists($this->newParentPath)) {
						$this->data['msg'] = "Invalid file path";
					} else {
						if ($this->isDir) {
							$this->data['notification'] = "Directory '{$this->name}' exists, overwrite all files, if names match?";
						} else {
							$this->data['notification'] = "File '{$this->name}' exists in:<br> '{$this->newPath}', overwrite?";
						}
						
						$_SESSION['copyFilesData'] = [
							'name' => $this->name,
							'isDir' => $this->isDir,
							'oldPath' => $this->oldPath,
							'newParentPath' => $this->newParentPath
						];
					}
				}
			}
		}
		
		private function run()
		{
			if (!$this->isDir) {
				if (!copy($this->oldPath, $this->newPath)) {
					$this->data['msg'] = "Error copying file: <br> '{$this->name}'";
				} else {
					$this->data['result'] = 'success';
				}
			} else {
				if (!$this->isOverwrite) {
					if (!mkdir($this->newPath)) {
						$this->data['msg'] = "Error creating folder: <br> '{$this->name}'";
					}
				} else {
					$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->oldPath, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
					
					foreach ($iterator as $filename => $fileInfo) {
						if ($fileInfo->isFile()) {
							$copiedPath = str_replace($this->oldPath, $this->newPath, $filename);
							
							if (!copy($filename, $copiedPath)) {
								$this->data['msg'] = "Error copying file: <br> '{$this->name}'";
								return;
							}
						} else {
							$creatingPath = str_replace($this->oldPath, $this->newPath, $filename);
							
							if (!mkdir($this->newPath)) {
								$this->data['msg'] = "Error creating folder: <br> '{$creatingPath}'";
								return;
							}
						}
					}
					
					$this->data['result'] = 'success';
				}
			}
		}
		
		public function __destruct()
		{
			if ($this->isOverwrite) {
				session_unset();
				setcookie(session_name(), session_id(), time() - 3600);
				session_destroy();
			}
		}
	}
	
	$newFile = new CopyFiles();
	$newFile->echoJsonEncode();
	
