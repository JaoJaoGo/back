<?php

namespace Tests;

use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        // Para testes, remover middlewares que causam problemas de session
        // Isso é uma solução temporária apenas para ambiente de testes
        if (app()->environment('testing')) {
            $app['router']->middlewareGroup('api', []);
        }

        return $app;
    }
}
