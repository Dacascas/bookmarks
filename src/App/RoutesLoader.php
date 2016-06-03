<?php

namespace App;

use Silex\Application;

class RoutesLoader
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->instantiateControllers();
    }

    private function instantiateControllers()
    {
        $this->app['bookmarks.controller'] = $this->app->share(function () {
            return new Controllers\BookmarksController($this->app['bookmarks.service']);
        });

        $this->app['comments.controller'] = $this->app->share(function () {
            return new Controllers\CommentsController($this->app['comments.service'], $this->app['bookmarks.service']);
        });
    }

    public function bindRoutesToControllers()
    {
        $api = $this->app["controllers_factory"];

        $api->get('/bookmarks', "bookmarks.controller:getAll");
        $api->post('/bookmarks', "bookmarks.controller:save");
        $api->get('/bookmarks/{url}', "bookmarks.controller:getElement");
        
        $api->post('/comments', "comments.controller:save");
        $api->put('/comments/{comment_id}', "comments.controller:update");
        $api->delete('/comments/{comment_id}', "comments.controller:delete");

        $this->app->mount($this->app["api.endpoint"].'/'.$this->app["api.version"], $api);
    }
}

