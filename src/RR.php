<?php


namespace AST;

use ReflectionFunction;


class RR
{

    const
        DELIMITER = ':',
        ROOT_DIR = '\AST';

    protected $currentUri;

    protected $incomingUri;

    /**
     * @var ReflectionFunction
     */
    protected $reflectCallback;

    protected
        $action = array(),
        $segments = array();

    /**
     * RR constructor.
     * @param string $pattern
     * @param $function
     */
    public function __construct(string $pattern, $function, $method)
    {
        $this->incomingUri = $pattern;

        $this->currentUri['path']       = $this->resolvedRequestUri();
        $this->currentUri['method']     = $_SERVER['REQUEST_METHOD'];

        $this->segments = explode('/', $_SERVER['REQUEST_URI']);

        $this->buildAction($pattern, $function);

    }

    /**
     * @return mixed|string
     */
    public function run( )
    {

        try {

            if ($this->functionIsNotCallable()) {
                return $this->runController();

            }

            //return $this->runCallable();

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dispatcher()
    {
        return new ControllerDispatch();
    }

    protected function dispatch($controller, $method, $params)
    {

        return call_user_func_array(
            array(new $controller(), $method),
            array($params)
        );
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function runController()
    {
        if ($this->matchPattern()) {
            return $this->dispatcher()->dispatch(
                $this, $this->getController(), $this->getControllerMethod()
            );
        }

        return null;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getController()
    {
        if (! $this->makeSureClassExists()) {
            throw new \Exception(
                sprintf(
                    "'%s' cannot solved. Make sure controller exists.",
                    $this->directoryFinder())
            );
        }

        $controller = $this->directoryFinder();

        return new $controller();
    }

    protected function getControllerMethod()
    {
        return $this->functionFinder();
    }

//    protected function runCallable()
//    {
//        $callable = $this->action['callback'];
//
//        return $callable(...$this->catchParameters());
//    }

    protected function matchPattern(): bool
    {
        return in_array($this->incomingUri, $this->currentUri);
    }

//    /**
//     * @return mixed
//     * @throws \Exception
//     */
//    protected function catchRouteFunction()
//    {
//        return $this->getFunction();
//    }

//    /**
//     * @return array
//     * @throws \ReflectionException
//     */
//    protected function catchParameters()
//    {
//        $params = [];
//        $args = [];
//
//        $this->makeCallbackReflected();
//
//        $this->matchArgs($args, $params);
//
//        return empty($args) ? ['parameters' => $params] : $args ;
//
//    }

    protected function runRoute($fn, $args)
    {
        call_user_func_array(
            $fn,
            $args
        );
    }

    protected function buildAction(string $pattern, $function)
    {
        if ($this->matchPattern()) {
            $this->action = [
                'url' => $pattern,
                'callback' => $function
            ];
        }
    }

    protected function resolvedRequestUri()
    {
        $uri = ($_SERVER['REQUEST_URI']);
        $uri = (empty($uri))
             ? '/'
             : $uri;

        return urldecode(
            parse_url($uri, PHP_URL_PATH)
        );
    }

    protected function getCurrentUrl()
    {
        return $this->currentUri['path'];
    }

    protected function getSegment(int $nth)
    {
        return $this->segments[$nth];
    }

    protected function getSegments()
    {
        return $this->segments;
    }

//    /**
//     * @throws \Exception
//     */
//    protected function getFunction()
//    {
//        $fn = null;
//
//        if (is_callable($this->getCallback())) {
//            $fn = $this->getCallback();
//        }
//        else if ($this->functionIsNotCallable()) {
//            $fn = RouteControllerAbstraction::getControllerFunction($this->getCallback());
//        }
//
//        return $fn;
//    }

    protected function &getCallback()
    {
        return $this->action['callback'];
    }

    private function functionIsNotCallable(): bool
    {
        return ! is_callable($this->getCallback());
    }

//    /**
//     * @throws \ReflectionException|\Exception
//     */
//    private function makeCallbackReflected()
//    {
//        if (is_array($this->getFunction())) {
//            $this->reflectCallback = new \ReflectionMethod(
//                $this->getFunction()[0], $this->getFunction()[1]
//            );
//        }
//        else {
//            $this->reflectCallback = new \ReflectionFunction(
//                $this->getFunction()
//            );
//        }
//
//    }

    private function callbackParameters()
    {
        return first($this->reflectCallback->getParameters());
    }

    private function findParametersFromQuery(array &$params)
    {
        $queryString = $this->currentUri['query'];
        parse_str($queryString, $params);
    }

    private function matchArgs(array &$args, array &$params)
    {
        $this->matchReflectionArgs($args, $params);

        $this->matchGetArgs($args);
    }

    private function matchReflectionArgs(&$args, $params)
    {

        if (array_key_exists('query', $this->currentUri)) {

            $this->findParametersFromQuery($params);

            $exposedArgs = $this->callbackParameters();

            if (! empty($exposedArgs)) {
                $argName = $exposedArgs->getName();

                // Kullanıcının girdiği parametre adı ile query parametresinde adı uyuşan varsa,
                // direkt o parametreye query parametresinin değeri atanıyor, değilse
                // genel olarak query parametrelerinin hepsi dönüyor.

                // Alternatif olarak bu durumda, $_GET[$argName] kullanılabilirdi.


                if (array_key_exists($argName, $params)) {
                    $argValue = $params[$argName] ?? null;

                    $args = [
                        $argName => $argValue
                    ];
                }

            }

        }

    }

    private function matchGetArgs(array &$args)
    {
        if (! empty($_GET)) {
            $exposedArgs = $this->callbackParameters();

            if (! empty($exposedArgs)) {
                $argName = $exposedArgs->getName();

                $argValue = $_GET[$argName] ?? null;

                $args = array_filter(array($argName => $argValue));
            }
        }
    }

    protected function makeSureClassExists(): bool
    {
        $fullClassDirectory = $this->directoryFinder();

        return class_exists($fullClassDirectory);
    }

    protected function functionFinder(): string
    {
        $function = strstr(
            $this->getCallback(),
            self::DELIMITER
        ); // :(functionName)
        $function = str_replace(
            self::DELIMITER,
            '',
            $function); // (functionName)

        return $function;
    }

    protected function directoryFinder()
    {
        $directory = self::ROOT_DIR . '\\' . strstr(
            $this->getCallback(),
            self::DELIMITER,
            true);

        $directory = ltrim($directory, '\\');

        return $directory;
    }
}