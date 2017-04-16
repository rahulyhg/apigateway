<?php

namespace Zyh\ApiGateway\Service;

use ErrorException;

trait Helpers
{
    /**
     * 同步阻塞式调用服务
     *
     * @param string $version 版本号 例如：v1
     * @param string $service 服务 例如：usre.getInfo
     * @param array  $params 服务需要的参数
     * @return array
     */
    protected function syncCallService($version, $service, $params = array())
    {
        $services = explode('.', $service);
        $service  = implode('/', $services);


        //todo 控制服务的调用，远程 亦或 本地
        //目前全本地

        $dispatcher = app('Zyh\MicroService\Dispatcher');

        return $dispatcher->version($version)->with($params)->post($service);

/*        $classBuild = config('apigateway.service.local.classBuild');
        $className  = sprintf($classBuild, ucfirst($version), ucfirst($services[0]), ucfirst($services[1]));
        $serviceObj = new $className($params);
        try {
            if ($serviceObj->paramsValidate()) {//校验请求参数
                return $serviceObj->run();
            }
        } catch(Exception $se) {
            //empty
        }

        return $serviceObj->getError();*/
    }

    /**
     * 异步非阻塞式调用服务
     * @param int   $limit
     * @param int   $expires
     * @param array $options
     *
     * @return void
     */
    protected function asyncCallService()
    {
        //todo
    }

}
