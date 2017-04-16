<?php

if (! function_exists('version')) {
    /**
     * 设置版本号到api的url中
     *
     * @param string $version 例如 v1
     *
     * @return \Zyh\ApiGateway\Routing\UrlGenerator
     */
    function version($version)
    {
        return app('api.url')->version($version);
    }
}

if (! function_exists('callservice')) {
    /**
     * 调用微服务
     *
     * @param string $version 例如 v1
     *
     * @return \Zyh\ApiGateway\Routing\UrlGenerator
     */
    function callservice($version)
    {
        return app('api.url')->version($version);
    }

    /**
     * 在服务中调用服务
     *
     * @param string $version 服务的版本号
     * @param string $service 服务名称 例如：foundation.getVerifyCode
     * @param array $params 服务需要的参数
     * @return array
     */
    function callService($version, $service, $params = array())
    {
        $services   = explode('.', $service);

        //todo 控制服务的调用，远程 亦或 本地
        //目前全本地

        $className  = 'App\Http\Services\\'.ucfirst($version).'\\'.ucfirst($services[0]).'\\Controllers\\'.ucfirst($services[1]);

        $serviceObj = new $className($params);
        try {
            if ($serviceObj->paramsValidate()) {//校验请求参数
                return $serviceObj->run();
            }
        } catch(ServiceException $se) {
            //empty
        }

        return $serviceObj->getError();
    }
}