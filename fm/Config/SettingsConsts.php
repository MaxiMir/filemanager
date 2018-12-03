<?php

    namespace
    {
        define('SEP', DIRECTORY_SEPARATOR);
	    define('ROOT', $_SERVER['DOCUMENT_ROOT'] . SEP);
	    define('FM_FOLDER_NAME', 'fm');
	    define('FM_PATH', dirname(__DIR__) . SEP);
	    define('FM_REL_PATH', str_replace(ROOT, '/', FM_PATH));
	    define('TIME_NEW_FILE', 60);
    }




