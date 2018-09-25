<?php
/**
 * Created by PhpStorm.
 * User: tito.duarte
 * Date: 25.09.2018
 * Time: 19:17
 */

namespace Reliv\ServeStatic;


class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'middleware_pipeline' => $this->getMiddlewarePipeline(),
        ];
    }


    public function getDependencies() : array
    {
        return [
            'invokables' => [
                ServeStaticMiddlewarePipe::class => ServeStaticMiddlewarePipe::class
            ],
            'factories'  => [],
        ];
    }


    public function getMiddlewarePipeline()
    {
        return [
            [
                'middleware' => ServeStaticMiddlewarePipe::class,
                'priority' => 1000
            ],
        ];
    }


}