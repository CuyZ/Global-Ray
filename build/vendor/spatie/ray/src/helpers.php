<?php

namespace RayGlobalScoped;

use RayGlobalScoped\Illuminate\Contracts\Container\BindingResolutionException;
use RayGlobalScoped\Spatie\CraftRay\Ray as CraftRay;
use RayGlobalScoped\Spatie\LaravelRay\Ray as LaravelRay;
use RayGlobalScoped\Spatie\Ray\Ray;
use RayGlobalScoped\Spatie\Ray\Settings\SettingsFactory;
use RayGlobalScoped\Spatie\RayBundle\Ray as SymfonyRay;
use RayGlobalScoped\Spatie\WordPressRay\Ray as WordPressRay;
use RayGlobalScoped\Spatie\YiiRay\Ray as YiiRay;
if (!\function_exists('RayGlobalScoped\\ray')) {
    /**
     * @param mixed ...$args
     *
     * @return \Spatie\Ray\Ray|LaravelRay|WordPressRay|YiiRay|SymfonyRay
     */
    function ray(...$args)
    {
        if (\class_exists(\RayGlobalScoped\Spatie\LaravelRay\Ray::class)) {
            try {
                return \RayGlobalScoped\app(\RayGlobalScoped\Spatie\LaravelRay\Ray::class)->send(...$args);
            } catch (\RayGlobalScoped\Illuminate\Contracts\Container\BindingResolutionException $exception) {
                // this  exception can occur when requiring spatie/ray in an Orchestra powered
                // testsuite without spatie/laravel-ray's service provider being registered
                // in `getPackageProviders` of the base test suite
            }
        }
        if (\class_exists(\RayGlobalScoped\Spatie\CraftRay\Ray::class)) {
            return \RayGlobalScoped\Yii::$container->get(\RayGlobalScoped\Spatie\CraftRay\Ray::class)->send(...$args);
        }
        if (\class_exists(\RayGlobalScoped\Spatie\YiiRay\Ray::class)) {
            return \RayGlobalScoped\Yii::$container->get(\RayGlobalScoped\Spatie\YiiRay\Ray::class)->send(...$args);
        }
        $rayClass = \RayGlobalScoped\Spatie\Ray\Ray::class;
        if (\class_exists(\RayGlobalScoped\Spatie\WordPressRay\Ray::class)) {
            $rayClass = \RayGlobalScoped\Spatie\WordPressRay\Ray::class;
        }
        if (\class_exists(\RayGlobalScoped\Spatie\RayBundle\Ray::class)) {
            $rayClass = \RayGlobalScoped\Spatie\RayBundle\Ray::class;
        }
        $settings = \RayGlobalScoped\Spatie\Ray\Settings\SettingsFactory::createFromConfigFile();
        return (new $rayClass($settings))->send(...$args);
    }
}
if (!\function_exists('RayGlobalScoped\\rd')) {
    function rd(...$args)
    {
        \RayGlobalScoped\ray(...$args)->die();
    }
}
