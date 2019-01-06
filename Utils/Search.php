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
            $files = glob($dir."/*.html"); // Получаем все html-файлы из директории
            $results = array(); // Создаём массив для результатов поиска
            for ($i = 0; $i < count($files); $i++) {
                /* Перебираем все полученные файлы */
                $str = strip_tags(file_get_contents($files[$i])); // Помещаем содержимое файлов в переменную, предварительно убрав все html-теги
                $count = substr_count($str, $search); // Ищем количество вхождений искомой строки в файл
                if ($count) $results[$files[$i]] = $count; // Если хотя бы 1 вхождение найдено, то добавляем файл с количеством вхождений в массив результатов
            }
            return $results; // Возвращаем результат
        }
        }
    }