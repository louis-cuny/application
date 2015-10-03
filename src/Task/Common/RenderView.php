<?php

    namespace ObjectivePHP\Application\Task\Common;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\Application\View\Helper\Vars;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Primitives\Collection\Collection;

    /**
     * Class RenderView
     *
     * @package ObjectivePHP\Application\Task\Common
     */
    class RenderView
    {

        /**
         * @var string
         */
        protected $locationDirective = 'views.locations';

        /**
         * @var ApplicationInterface
         */
        protected $application;

        /**
         * @param WorkflowEvent $event
         *
         * @throws Exception
         */
        public function __invoke(WorkflowEvent $event)
        {

            $this->setApplication($application = $event->getApplication());

            $viewName = $this->getViewName($event);


            if(!$viewName)
            {
                return;
            }

            $context = $this->getContext($event);

            $output = $this->render($viewName, $context);

            $this->getApplication()->getResponse()->getBody()->rewind();
            $this->getApplication()->getResponse()->getBody()->write($output);
        }

        /**
         * @param WorkflowEvent $event
         *
         * @return mixed|null
         * @throws \ObjectivePHP\Events\Exception
         */
        protected function getViewName(WorkflowEvent $event)
        {
            return $event->getResults()['view-resolver'];
        }

        /**
         * @param WorkflowEvent $event
         *
         * @return mixed
         */
        protected function getContext(WorkflowEvent $event)
        {

            $viewVars = $this->getApplication()->getWorkflow()->getStep('run')->getEarlierEvent('execute')
                             ->getResults()['action'];

            // default to empty array
            if (is_null($viewVars))
            {
                $viewVars = [];
            }

            // inject config
            $context['config'] = $this->getApplication()->getConfig();

            foreach ($viewVars as $reference => $value)
            {
                Vars::set($reference, $value);
            }

            return $context;
        }

        /**
         * @return ApplicationInterface
         */
        public function getApplication()
        {
            return $this->application;
        }

        /**
         * @param ApplicationInterface $application
         *
         * @return $this
         */
        public function setApplication($application)
        {
            $this->application = $application;

            return $this;
        }

        /**
         * @param       $viewName
         * @param array $context
         *
         * @return string
         * @throws Exception
         */
        public function render($viewName, $context = [])
        {
            $viewPath = $this->resolveViewPath($viewName);

            // make view and layout path available to the rest of the application
            if ($this instanceof RenderLayout)
            {
                Vars::set('layout.path', $viewPath);
            }
            else
            {
                Vars::set('view.path', $viewPath);
            }

            if (is_null($viewPath))
            {
                throw new Exception(sprintf('Unable to resolve view "%s" to a file path', $viewName));
            }

            if (!file_exists($viewPath))
            {
                throw new Exception(sprintf('Resolved view script "%s" does not exist', $viewPath));
            }

            if ($context)
            {
                extract($context);
                unset($context);
            }

            ob_start();
            include $viewPath;
            $view = ob_get_clean();

            return $view;
        }

        /**
         * @param $viewName
         *
         * @return callable
         */
        protected function resolveViewPath($viewName)
        {

            return $viewName . '.phtml';
        }

        /**
         * @return array
         */
        protected function getViewsLocations()
        {
            $config = $this->getApplication()->getConfig();

            if ($config->hasDirective($this->locationDirective))
            {
                $viewLocations = array_reverse(Collection::cast($config->get($this->locationDirective))->toArray());
            }
            else return [];

            $locations = [];
            foreach ($viewLocations as $paths)
            {
                $locations[] = $paths;
            }

            return $locations;

        }

    }