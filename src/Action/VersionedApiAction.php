<?php

namespace ObjectivePHP\Application\Action;

abstract class VersionedApiAction extends SubRoutingAction
{

    use HttpAction;

    protected $defaultVersion = '1.0';

    protected $versionParameter = 'version';

    public function route()
    {

        $version = $this->getApplication()->getRequest()->getParameters()->get($this->versionParameter) ?: $this->defaultVersion;

        return $version;
    }

    /**
     * Return a list of
     *
     * @return array
     */
    public function listAvailableVersions()
    {
        return $this->getMiddlewareStack()->keys()->toArray();
    }


}