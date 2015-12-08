<?php
    /**
     * Created by PhpStorm.
     * User: gauthier
     * Date: 08/12/2015
     * Time: 12:16
     */
    
    namespace ObjectivePHP\Application\Workflow\Filter;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Exception;

    /**
     * Class RouteFilter
     *
     * @package ObjectivePHP\Application\Workflow
     */
    class RouteFilter extends AbstractFilter
    {
        /**
         * @return bool
         */
        public function filter(ApplicationInterface $app) : bool
        {
            // check route filter
            if ($this->getFilter() != '*')
            {

                $request = $app->getRequest();

                if (!$request)
                {
                    throw new Exception('Cannot run RouteFilter: no request has been set');
                }

                $route = $request->getRoute();

                if (!$route)
                {
                    throw new Exception('Cannot run RouteFilter: no route has been set');
                }


                if (!$app->getRouteMatcher()->match($this->getFilter(), $route))
                {
                    return false;
                }
            }

            return true;
        }


    }