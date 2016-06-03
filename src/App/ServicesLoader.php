<?php

namespace App;

use Silex\Application;

class ServicesLoader
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function bindServicesIntoContainer()
    {
        $this->app['bookmarks.service'] = $this->app->share(function () {
            return new Services\BookmarksService($this->app["db"]);
        });        
        
        $this->app['comments.service'] = $this->app->share(function () {
            return new Services\CommentsService($this->app["db"]);
        });
    }
}

