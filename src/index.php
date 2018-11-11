<?php

    namespace FM;

    require_once __DIR__ . '/vendor/autoload.php';

    
    use \FM\Render;
    use \FM\FileData\PathInfo;
    
    require_once 'FileData/PathInfo.php';
    require_once 'FileData/FilesInfo.php';
    require_once 'FileData/FileInfo.php';   
    require_once 'FileData/FileFunc.php'; 
    require_once 'Render.php';

    $app = new \Slim\App;

    $app->get('/[{url:.*}]', function ($request, $response, $args) {

        $currPath = ROOT . $request->getAttribute('url');
        $path = new PathInfo($currPath);

        if (!$path->isvalidPath()) {
            $html = Render::generate('404.twig');
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

            $html = Render::generate($tmpl, $data);
            return $response->write($html);
        }
    });

    $app->run();
    

    /* TODO: 
    	* index.php и .htaccess в корне
    	* настроить namespace  
        * картинки без перехода на страницу + превью.
        * верстка

        //http://slimframework.ru/objects/router
    */


