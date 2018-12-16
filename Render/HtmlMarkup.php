<?php
	
	namespace FM\Render;

	require ROOT . 'vendor/twig/twig/lib/Twig/Autoloader.php';

    class HtmlMarkup
    {
    	public static function generate($tmpl, $data = [])
    	{
    		$pathConsts = [
    		    'ROOT' => ROOT,
				'FM_FOLDER_NAME' => FM_FOLDER_NAME,
	 			'FM_REL_PATH' => FM_REL_PATH
    		];

			$renderData = array_merge($data, $pathConsts);

			try {
                \Twig_Autoloader::register();
				$loader = new \Twig_Loader_Filesystem(FM_PATH. 'views');
				$twig = new \Twig_Environment($loader);
				$template = $twig->loadTemplate($tmpl);
				return $template->render($renderData);
			} catch (\Exception $e) {
				die ('ERROR: ' . $e->getMessage());
			}
    	}
    }