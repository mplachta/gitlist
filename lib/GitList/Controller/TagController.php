<?php

namespace GitList\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Response;

class TagController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $route = $app['controllers_factory'];

        $route->post('/add', function() use ($app) {
			$commitid = $app['request']->get('commitid');
			$name     = $app['request']->get('name');
			$repo     = $app['request']->get('repo');
			
			if(strlen($commitid) != 7 || strlen($name) == 0 || strlen($repo) == 0) {
				throw new Exception('Missing parameters');
			}
			
			$test = $app['db']->fetchAssoc('SELECT * FROM tags WHERE repo = ? AND commitid = ? AND name = ?', array($repo, $commitid, $name));
			if(!$test) {
				$app['db']->executeQuery('INSERT INTO tags (repo, commitid, name) VALUES(?, ?, ?)', array($repo, $commitid, $name));
			}
			
			return $app->redirect('/'.$repo . '/commit/' . $commitid);
        })->bind('addtag');
		
		$route->get('/delete/{id}', function($id) use($app) {
			$test = $app['db']->fetchAssoc('SELECT * FROM tags WHERE id = ?', array($id));
			if(!$test) {
				return new Response('Not found', 404);
			}
			
			$commitid = $test['commitid'];
			$repo     = $test['repo'];
			
			$app['db']->executeQuery('DELETE FROM tags WHERE id = ?', array($id));
			return $app->redirect('/'.$repo . '/commit/' . $commitid);
		})->assert('id', '[0-9]+')
		  ->bind('deletetag');

        return $route;
    }
}
