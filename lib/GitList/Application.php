<?php

namespace GitList;

use Silex\Application as SilexApplication;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use GitList\Provider\GitServiceProvider;
use GitList\Provider\RepositoryUtilServiceProvider;
use GitList\Provider\ViewUtilServiceProvider;

/**
 * GitList application.
 */
class Application extends SilexApplication
{
    /**
     * Constructor initialize services.
     *
     * @param Config $config
     * @param string $root   Base path of the application files (views, cache)
     */
    public function __construct(Config $config, $root = null)
    {
        parent::__construct();

        $app = $this;
        $root = realpath($root);

        $this['debug'] = $config->get('app', 'debug');
        $this['filetypes'] = $config->getSection('filetypes');
        $this['cache.archives'] = $root . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'archives';

        // Register services
        $this->register(new TwigServiceProvider(), array(
            'twig.path'       => $root . DIRECTORY_SEPARATOR . 'views',
            'twig.options'    => array('cache' => $root . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'views'),
        ));
        $this->register(new GitServiceProvider(), array(
            'git.client'      => $config->get('git', 'client'),
            'git.repos'       => $config->get('git', 'repositories'),
            'git.hidden'      => $config->get('git', 'hidden') ? $config->get('git', 'hidden') : array(),
        ));
		$this->register(new DoctrineServiceProvider(), array(
			'db.options'      => array(
				'driver'      => 'pdo_sqlite',
				'path'        => $root . DIRECTORY_SEPARATOR . 'db/app.db',
			),
		));
		// initialize database if not yet done
		try {
			$test = $app['db']->fetchAssoc('SELECT * FROM gitlist');
		} catch(\PDOException $e) {
			if($e->getCode() == 'HY000') {
				$app['db']->executeQuery('CREATE TABLE gitlist (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, value TEXT)');
				$app['db']->executeQuery('CREATE TABLE tags (id INTEGER PRIMARY KEY AUTOINCREMENT, repo TEXT, commitid TEXT, name TEXT)');
			}
		}
        $this->register(new ViewUtilServiceProvider());
        $this->register(new RepositoryUtilServiceProvider());
        $this->register(new UrlGeneratorServiceProvider());

        $this['twig'] = $this->share($this->extend('twig', function($twig, $app) {
            $twig->addFilter('md5', new \Twig_Filter_Function('md5'));

            return $twig;
        }));

        // Handle errors
        $this->error(function (\Exception $e, $code) use ($app) {
            return $app['twig']->render('error.twig', array(
                'message' => $e->getMessage(),
            ));
        });
    }
}
