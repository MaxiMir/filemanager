<?php

    namespace FM\Utils;

    require_once '../../vendor/autoload.php';

    class Search implements UtilsInterface
    {
        // поиск в текущей директории по дефолту в ROOT
        // поиск по названию
        // поиск по содержимому
        private $searchPhrase;
        private $searchPath = ROOT;
        private $searchByContent = true;
        private $searchByFName = false;

        public function __construct()
        {

        }

        private function run()
        {
            $path = '/var/www/x.ru/slim/';
            $fName = 'index.php';
            $output = shell_exec("find {$path} -name {$fName}");
            echo "<pre>$output</pre>";


            /*
                * заменяет 1 символ '?word'
                * содержащие в имени слово *word*
                * файлы с расширением jpg *.jpg
            */

            $search = 'function';
            $output = shell_exec("grep -r '{$search}' $path");
            echo "<pre>$output</pre>";

            /*
            * -i нерегистрозависимый
            */
        }


    }