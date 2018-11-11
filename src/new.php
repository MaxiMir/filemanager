<?

###################### SLIM ###################### 

// file: composer.json

{
	"require": {
		"slim/slim": "2.*"
	}
}

$composer init // install
$ php copmposer.phar install

$app = new\Slim\Slim();
$app->get('/hello/:name', function($name) {
	echo 'Hello, $name';
});

$app->run();


// file: index.php

require_once "vendor/autoload.php";

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim([
						'mode' => 'development', // по умолч. Определяется в момент создания класса
						'debug' => TRUE // режим откладки, по умолчанию включен и исп. свой класс ERROR Exception для перехвата и отображению ошибок
						'templates.path' => 'templates', // путь до каталога с шаблонами
						'cookies.encrypt' => TRUE, // влючение режима шифрования значений, которые записываются в куки
						'cookies.lifetime'=>  '20 minutes', // время жизни кук
						'cookies.path' => '/', // устанавливает подмножество страниц, для которых действительны значения файлов cookies
						'cookies.domain' => 'slim.ru', // -//- для каких доменов
						'cookies.secure' => FALSE, // Если true, то информация по кукам пересылается только по https с использованием SSL сертификата. По-умолч. false.
						'cookies.httponly' => TRUE, // куки будут доступны для различных клиентских языков веб програмирования (напр., JS)
						'cookies.cipher' => 'cipher',
						'cookies.cipher_mode' => 'mode',
						'cookies.secret_key' => 'key',
						'host' => 'localhost',
						'user' => 'user',
						'pass' => 'pass',
						'db' => 'dbname'
]); // в массиве при необходимости передаем наши настройки. 1 ваирант

$app->config('db'); // возвращает значение настройки
$app->config('db' => 'dbname'); // изменяет значение настройки
$app->config([ // изменение/создание настроек. 2 вариант
 			  'host' => 'localhost',
 			  'user' => 'user',
			  'pass' => 'pass',
			  'db' => 'dbname'
]); 

$app->configureMode('development', function() use ($app) {  // привязываем конкретные настройки mode
	$app->config([
					'debug' => TRUE
	]);				
});

$app->configureMode('test', function() use ($app) {  // привязываем конкретные настройки mode. Вызывается после установки/изменения режима mode
	$app->config([
					'debug' => FALSE
	]);	
});


getDefaultSettings(); // возвращает массив настроек по-умолчанию

$app->get('hello', function() { // index.php?hello или index.php/hello
	$app = \Slim\Slim::getInstance(); // возвращает ранее созданный объект данного класса или можно использовать use($app)
	echo 'world';
});

$app->post('/add', function () {} {
	print_r($_POST);
});

$app->map('/create', function() {
	echo 'STRING!';
})->via('GET', 'POST')->name('create');  // POST & GET. name - задаем имя роутера

$app->get('article/:name+', function ($name = 1) use ($app)) { // если параметр необязательный, необходимо обернуть в () - (:name). Значение по умолчанию
	print_r($name); // + создает массив из параметров
}

// несколько необязательных параметров: (/:id(/:name))

$app->run(); // запускаем фреймворк

// file: .htaccess

RewriteEngine On // подключаем модуль перенаправления сервера Apache
RewriteCond %{REQUEST_FILENAME} !-f // условие перенаправления (если не файл)
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L] // правило перенаправления: домен + index.php + добавленный запроc. Флаг QSA - добавление запроса, L - последнее перенаправление


// маршрутизация - процесс получения части URI и разложение его на параметры для определения того, какой контроллер и какое его действие должны выполниться.
// роутер - метод, в котором определен шаблон части URI и функция обработчик, код которой будет выполнен при совпадении текущего URI c описанным шаблоном.

URI - Uniform Resource Identifier - единообразный индентификатор ресурса = http://slim.ru/article/id/2-title.php
URL - Uniform Resource Locator - единообразный указатель ресурса = http://slim.ru
URN - Iniform Resource Name - единообразный указатель имени = /article/id/2-title.php



######################## .htaccess ########################

# .htaccess - файл дополнительной конфигурации web сервера

# - комментарий
AddDefaultCharset utf-8

# - запрет листинга каталогов
Options -Indexes // разрешить вместо "-" - "+"" 

# открытие файлов без указания расширения (category.php -> category)
Options +MultiViews 

# Переопределение индексного файла
DirectoryIndex file_php.php

# Видоизменяет листинг каталога
IndexOptions FancyIndexing
IndexOptions FancyIndexing ScanHTMLTitles // добавляет колонку title файлам html

# Исключение из листинга определенных файлов
IndexIgnore *.rar *.zip *.txt // если поставить * - все файлы

# Выполнение кода PHP в не .php файлах
// файл httpd.conf:
AddType application/x-httpd-php .php .php5 .phtml // файлы которые обрабатываются интерпритатором php, перед отдачей клиенту

AddType application/x-httpd-php .htm .css

// file: style.css:

<?php header("Content-Type: text/css"); ?>
<?php $bg ="#333"; $color = '#fff'; ?>

body {
	background: <?=$bg?>
	color: <?=$color?>
}

# Страницы ошибок

ErrorDocument 403 /errors/page403.html
ErrotDocument 404 /errors/page404.html

# Порядок работы директив Allow и Deny
Order Deny,Allow // Deny, Allow - запрет доступа всем, кроме ...
Order Allow, Deny // Allow, Deny - разрешаем всем кроме ...

Deny from all // запрет всем => 403 ошибка
Allow from 127.0.0.1, 127.0.0.2 // разрешена только с этих IP. Можно указывать часть IP адреса или их диапазон
// Стоит учесть тот факт, что содержимое файла будет все-равно доступно другим скриптам.

# Ограничение доступа к файлам
<Files "rar.rar"> // к конкретному файлу
    Deny from all 
    Allow from 127.0.0.1
</Files>    


// ? - любой 1 символ, * - любые символы