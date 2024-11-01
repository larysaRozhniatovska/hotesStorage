<?php

namespace app\lib;

class Router
{
    /**
     * Init application
     * @return void
     */
    public  function init() : void
    {
        $action = 'index';
        if (isset($_GET['action'])) {
            $action = filter_input(INPUT_GET, 'action');
        }
        $controller = new Controller();
        if (!method_exists($controller, $action)) {
            self::notFound();
        }
        $controller->$action();
    }
    /**
     * Generate 404 status
     * @return never
     */
    public static function notFound() : never
    {
        http_response_code(404);
        //TODO if need especially view
        exit();
    }

    /**
     * generate url by action
     * @param string $action
     * @return string
     */
    public static function url(string $action = 'note') : string
    {
        return '/?action=' . $action;
    }

    /**
     * redirect to specify url
     * @param string $url
     * @return never
     */
    public static function redirect(string $url) : never
    {
        header('Location: ' . $url);
        exit();
    }
}