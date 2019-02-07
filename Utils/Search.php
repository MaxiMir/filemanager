<?php

    namespace FM\Utils;

    require_once '../../vendor/autoload.php';

    use FM\Render\HtmlMarkup;
    use FM\FileData\FileInfo;
    use FM\FileData\FileFunc;

    class Search implements UtilsInterface
    {
        use Json;

        private $searchPhrase;
        private $searchPath;
        private $contentData = [];
        private $options = [
            'searchInCurrDir' => false,
            'searchByName' => false,
            'contentSearch' => false,
            'caseInsensitiveSearch' => false
        ];

        public function __construct()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->data['msg'] = "Incorrect method of sending data.<br>";
            } else {
                $this->searchPhrase = $_POST['searchPhrase'];
                $newOptions = json_decode($_POST['searchOptions'], true);
                $this->options = array_merge($this->options, $newOptions);
                $this->searchPath = !$this->options['searchInCurrDir'] ? ROOT : ROOT . FileFunc::getRelPath($_SERVER['HTTP_REFERER']);

                $this->run();
            }
        }

        private function run()
        {
            $caseInsensitiveSearch = $this->options['caseInsensitiveSearch'];
            $searchPath = $this->searchPath;
            $searchPhrase = $this->searchPhrase;

            if ($this->options['searchByName']) {
                $opRegister = $caseInsensitiveSearch ? '-iname' : '-name';

                $filesData = shell_exec("find {$searchPath} {$opRegister} '{$searchPhrase}'");
                $this->contentData['searchFiles'] = array_reduce(explode(ROOT, $filesData), function ($acc, $relPath) {
                    if (!empty($relPath)) {
                        $fullPath = ROOT . trim($relPath);
                        $acc[$relPath]['href'] = '/' . FM_FOLDER_NAME . '/' . FileFunc::getRelUrl($fullPath);
                        $acc[$relPath]['src'] = is_dir($fullPath) ? FM_REL_PATH . 'css/img/folder.png' : FileInfo::chooseImg($fullPath);
                    }
                    return $acc;
                }, []);
            }

            if ($this->options['contentSearch']) {
                $opRegister = $caseInsensitiveSearch ? '-i' : '';

                $filesData = shell_exec("grep -I -r {$opRegister} '{$searchPhrase}' {$searchPath}");
                $this->contentData['contentSearch'] = array_reduce(explode(ROOT, $filesData), function ($acc, $dataPath) use ($searchPhrase) {
                    if (!empty($dataPath)) {
                        $relPath = strstr($dataPath, ':', true);
                        $fullPath = ROOT . $relPath;

                        if (empty($acc[$relPath]['href']) && empty($acc[$relPath]['src'])) {
                            $acc[$relPath]['href'] = '/'. FM_FOLDER_NAME . '/' . FileFunc::getRelUrl($fullPath) . "#find=" . base64_encode($searchPhrase);
                            $acc[$relPath]['src'] = FileInfo::chooseImg($fullPath);
                        }
                        $text = trim(str_replace("{$relPath}:", '', $dataPath));
                        $acc[$relPath]['text'][] = $text;
                    }
                    return $acc;
                }, []);
            }

            $this->data['content'] = HtmlMarkup::generate('search.twig', ['contentData' => $this->contentData]);
            $this->data['result'] = 'success';
        }
    }


    $newSearch = new Search();
    $newSearch->echoJsonEncode();