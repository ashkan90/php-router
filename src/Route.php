<?php


namespace Xav;

use ReflectionFunction;


class Route
{

    static
        $DELIMITER = ':',
        $ROOT_DIR = '\Xav';

    protected $currentUri;

    /**
     * @var Container;
     */
    protected $container;

    protected
        $incomingUri,
        $incomingMethod;

    /**
     * @var ReflectionFunction
     */
    protected $reflectCallback;

    protected
        $action = array(),
        $segments = array();


    public function __construct($pattern, $function, $method)
    {
        $this->incomingUri = $pattern;
        $this->incomingMethod = $method;

        $this->currentUri['path']       = $this->resolvedRequestUri();
        $this->currentUri['method']     = $_SERVER['REQUEST_METHOD'];

        $this->segments = explode('/', $_SERVER['REQUEST_URI']);

        $this->buildAction($pattern, $function);

    }

    /**
     * @return mixed|string
     */
    public function run()
    {
        try {
            if ($this->functionIsNotCallable()) {
                return $this->runController();

            }

            return $this->runCallable();

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return mixed
     * @throws \ReflectionException
     */
    protected function runCallable()
    {
        return $this->reflect(
            $this->getCallback(),
            $this->transformReflector(
                $this->reflector()
            )
        );
    }


    protected function reflect($closure, $parameters)
    {
        $queryParameters = [];

        // Reflect edilen closure'un parametrelerinde,
        // ServerRequest tipinde değişken var mı yok mu diye kontrol ediyor.
        // Eğer varsa o parametreye ServerRequest tipinde bir dönüş oluyor.
        if (array_filter($parameters)) {
            array_walk($parameters, function($item) use ($closure){
                if ($item == ServerRequest::class) {
                    return $closure($this->request());
                }

                return null;
            });
        }

        // Gelen url nin sorgu olarak parametresi varsa parametre değerlerini oradan alıyor.
        // örn: ?name=emirhan&surname=ataman === true
        if ($this->hasParameterAsQuery()) {
            $this->findParametersFromQuery($queryParameters);
        }

        // Eğer route dan gelen parametre; örn: 'test/:id/:val' var ise
        // bu parametrelerin değerlerini url den topluyor.
        if ($this->hasParameterAsString()) {
            $queryParameters = $this->mapParameters($parameters);
        }
        dd($queryParameters);

        if (count($parameters) != count($queryParameters)) {
            foreach ($queryParameters as $item) {
                $queryParameters[] = $item;
            }
        } else {

            $queryParameters = array_combine(array_keys($parameters), $queryParameters);
        }




        return $closure(...array_values($queryParameters));
    }

    /**
     * @return ReflectionFunction
     * @throws \ReflectionException
     */
    protected function reflector()
    {
        return new \ReflectionFunction($this->getCallback());
    }

    /**
     * @param \ReflectionFunctionAbstract $function
     * @return array
     * @throws \ReflectionException
     */
    protected function transformReflector(\ReflectionFunctionAbstract $function)
    {
        $parameters = array();

        foreach ($function->getParameters() as $parameter) {
            $parameters[$parameter->getName()] = $parameter->isDefaultValueAvailable()
                ? $parameter->getDefaultValue()
                : $parameter->getClass()
                    ? $parameter->getClass()->name
                    : null;
        }

        return $parameters;
    }

    /**
     * @return ServerRequest
     */
    protected function request()
    {
        return new ServerRequest($_REQUEST, array(
            'method' => $this->incomingMethod,
            'url' => $this->incomingUri
        ));
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

        return $this->container->make($controller);
    }

    protected function getControllerMethod()
    {
        return $this->functionFinder();
    }

    protected function mapParameters($_keys)
    {
        $keys = str_replace(':', '', $this->incomingUri);
        $keys = array_filter(explode('/', $keys));


        $keys = array_intersect($keys, array_keys($_keys));
        $values = array_filter($this->getSegments());

        $values = array_in_keys($values, array_keys($keys));



//        $parameters = array_filter(array_combine($keys, $values));
//
//        dd($parameters);
        return $values;
    }

    protected function hasParameterAsString(): bool
    {
        return str_word_count($this->incomingUri, null, ':') > 0;
    }

    protected function hasParameterAsQuery(): bool
    {
        return isset($_SERVER['QUERY_STRING']);
    }

    protected function matchPattern(): bool
    {

//        $this->mapParameters();
//        dd($this->incomingUri, $this->currentUri);
        return in_array($this->incomingUri, $this->currentUri);
    }

    public function dispatcher()
    {
        return new ControllerDispatch();
    }

    protected function buildAction(string $pattern, $function)
    {
        $this->container == null ? new Container($this) : $this->container;

        $x = array_filter(explode('/', $this->incomingUri));
        if ($this->matchPattern() || count($this->getSegments()) == count($x)) {
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

    /**
     * @param int $nth
     * @return mixed
     */
    protected function getSegment(int $nth)
    {
        return $this->segments[$nth];
    }

    protected function getSegments($multiple = null)
    {
        $segments = null;
        if (is_numeric($multiple)) {
            $segments = $this->getSegment($multiple);
        } else if (is_array($multiple)) {
            foreach ($this->segments as $segment) {
                $segments[] = $segment;
            }
        }


        $segments = $this->segments;


        return array_filter($segments);
    }

    protected function &getCallback()
    {
        return $this->action['callback'];
    }

    private function functionIsNotCallable(): bool
    {
        return ! is_callable($this->getCallback());
    }

    private function callbackParameters()
    {
        return first($this->reflectCallback->getParameters());
    }

    private function findParametersFromQuery(array &$params)
    {
        $queryString = $_SERVER['QUERY_STRING'];
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
            self::$DELIMITER
        ); // :(functionName)
        $function = str_replace(
            self::$DELIMITER,
            '',
            $function); // (functionName)

        return $function;
    }

    protected function directoryFinder()
    {
        $directory = self::$ROOT_DIR . '\\' . strstr(
            $this->getCallback(),
            self::$DELIMITER,
            true);

        $directory = ltrim($directory, '\\');

        return $directory;
    }
}