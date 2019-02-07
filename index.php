<?php

    require_once '../vendor/autoload.php';

    use \Slim\App;
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;
    use FM\FileData\Path;
    use FM\Render\HtmlMarkup;

    $configuration = [
        'settings' => [
            'displayErrorDetails' => true
        ]
    ];
    
    $app = new App($configuration);

    $app->get('/[{url:.*}]', function (Request $request, Response $response, $args) {
        $queryParam = $request->getQueryParam('url', '');
        $currPath = ROOT . $queryParam;
        $path = new Path($currPath);

        if (!$path->isvalidPath()) {
            $htmlMarkup = HtmlMarkup::generate('404.twig');
            return $response->withStatus(404)
                ->withHeader('Content-Type', 'text/html')
                ->write($htmlMarkup);
        } else {
            $tmpl = $path->getTemplate();
            $header = $path->getHeader();
            $breadcrumbs = $path->generateBreadcrumbs();
            $contentData = $path->getContentData();

            $data = [
                'tmpl' => $tmpl,
                'header' => $header,
                'breadcrumbs' => $breadcrumbs,
                'contentData' => $contentData
            ];

           $htmlMarkup = HtmlMarkup::generate($tmpl, $data);
           return $response->write($htmlMarkup);
        }
    });

    $app->run();

    /* TODO:
        * при наведении на левое меню скролл
        * авторизация
        * copy, move
        * увеличение шрифтов в редакторе, сохранение для пользователя параметров
        * картинки без перехода на страницу + превью.
        * верстка
        * минификация css, js
        -> http://slimframework.ru/objects/router
    */
