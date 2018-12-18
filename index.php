<?php

    require_once '../vendor/autoload.php';

    use \Slim\App;
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;
    use FM\FileData\PathInfo;
    use FM\Render\HtmlMarkup;

    $configuration = [
        'settings' => [
            'displayErrorDetails' => true,
        ]
    ];
    
    $app = new App($configuration);

    $app->get('/[{url:.*}]', function (Request $request, Response $response, $args) {
        $queryParam = $request->getQueryParam('url', '');
        $currPath = ROOT . $queryParam;
        $path = new PathInfo($currPath);

        if (!$path->isvalidPath()) {
            $html = HtmlMarkup::generate('404.twig');
            return $response->withStatus(404)
                ->withHeader('Content-Type', 'text/html')
                ->write($html);
        } else {
            $tmpl = $path->chooseTemplate();
            $header = $path->getHeader();
            $breadcrumbs = $path->generateBreadcrumbs();
            $contentData = $path->getContentData();

            $data = [
                'tmpl' => $tmpl,
                'header' => $header,
                'breadcrumbs' => $breadcrumbs,
                'contentData' => $contentData
            ];

            $html = HtmlMarkup::generate($tmpl, $data);
            return $response->write($html);
        }
    });

    $app->run();


    /* TODO:
        * Synchronous XMLHttpRequest - замена решения
        * авторизация
        * ООП в utils
        * увеличение шрифтов в редакторе, просмотр страниц, сохранение для пользователя
        * картинки без перехода на страницу + превью.
        * верстка

        -> https://github.com/slimphp/Twig-View
        -> http://slimframework.ru/objects/router
    */