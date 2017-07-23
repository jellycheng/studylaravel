<?php namespace App\Http\Middleware;

use Closure;

class Cors {


    public function handle($request, Closure $next)
    {
        echo 'cors' . PHP_EOL;
        $actions = $request->route()->getAction();
        /**
        array (
        'middleware' => 'cors',
        'uses' => 'App\\Http\\Controllers\\UserController@showProfile3',
        'permissions' => '菜单ID:123',
        'controller' => 'App\\Http\\Controllers\\UserController@showProfile3',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => NULL,
        'where' =>array (),
        )
         */
        var_export($actions);

        echo $request->server->get('REQUEST_METHOD', 'GET') . PHP_EOL; //获取请求方法
        echo $request->getMethod();//获取请求方法, GET,POST,OPTIONS
        echo $request->getRealMethod() . PHP_EOL; //获取请求方法, GET,POST,OPTIONS

        //var_export($request->server);

        if (!empty($request->server->get('HTTP_ORIGIN', ''))) {
            $wrap_header = array();

            $wrap_header['origin'] = 'Access-Control-Allow-Origin: ' . $request->server->get('HTTP_ORIGIN', '*');
            $wrap_header['cred'] = 'Access-Control-Allow-Credentials:true';
            $wrap_header['allow_methods'] = 'Access-Control-Allow-Methods: POST,GET,OPTIONS';
            $wrap_header['allow_header'] = 'Access-Control-Allow-Headers: accept, origin, withcredentials, content-type,urlEncodeCharset, Accept-Charset, sid, t_sid, token,APP-SOURCE,APP-V,APP-TYPE,APP-CHANNEL';

            if($request->getRealMethod() == 'OPTIONS') {
                $wrap_header['cache'] = "Access-Control-Max-Age: 86400";
            }
            if (is_array($wrap_header)) {
                foreach ($wrap_header as $key => $header_line) {
                    @header($header_line);
                }
            }
            if($request->getRealMethod() == 'OPTIONS'){
                exit;
            }

        }

        return $next($request);
    }

}
