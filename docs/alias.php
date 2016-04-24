<?php

class abc {
	protected $aliases = [];
	public function registerCoreContainerAliases() {
		$aliases = array(
			'app'                  => ['Illuminate\Foundation\Application', 'Illuminate\Contracts\Container\Container', 'Illuminate\Contracts\Foundation\Application'],
			'artisan'              => ['Illuminate\Console\Application', 'Illuminate\Contracts\Console\Application'],
			'auth'                 => 'Illuminate\Auth\AuthManager',
			'auth.driver'          => ['Illuminate\Auth\Guard', 'Illuminate\Contracts\Auth\Guard'],
			'auth.password.tokens' => 'Illuminate\Auth\Passwords\TokenRepositoryInterface',
			'blade.compiler'       => 'Illuminate\View\Compilers\BladeCompiler',
			'cache'                => ['Illuminate\Cache\CacheManager', 'Illuminate\Contracts\Cache\Factory'],
			'cache.store'          => ['Illuminate\Cache\Repository', 'Illuminate\Contracts\Cache\Repository'],
			'config'               => ['Illuminate\Config\Repository', 'Illuminate\Contracts\Config\Repository'],
			'cookie'               => ['Illuminate\Cookie\CookieJar', 'Illuminate\Contracts\Cookie\Factory', 'Illuminate\Contracts\Cookie\QueueingFactory'],
			'encrypter'            => ['Illuminate\Encryption\Encrypter', 'Illuminate\Contracts\Encryption\Encrypter'],
			'db'                   => 'Illuminate\Database\DatabaseManager',
			'events'               => ['Illuminate\Events\Dispatcher', 'Illuminate\Contracts\Events\Dispatcher'],
			'files'                => 'Illuminate\Filesystem\Filesystem',
			'filesystem'           => 'Illuminate\Contracts\Filesystem\Factory',
			'filesystem.disk'      => 'Illuminate\Contracts\Filesystem\Filesystem',
			'filesystem.cloud'     => 'Illuminate\Contracts\Filesystem\Cloud',
			'hash'                 => 'Illuminate\Contracts\Hashing\Hasher',
			'translator'           => ['Illuminate\Translation\Translator', 'Symfony\Component\Translation\TranslatorInterface'],
			'log'                  => ['Illuminate\Log\Writer', 'Illuminate\Contracts\Logging\Log', 'Psr\Log\LoggerInterface'],
			'mailer'               => ['Illuminate\Mail\Mailer', 'Illuminate\Contracts\Mail\Mailer', 'Illuminate\Contracts\Mail\MailQueue'],
			'paginator'            => 'Illuminate\Pagination\Factory',
			'auth.password'        => ['Illuminate\Auth\Passwords\PasswordBroker', 'Illuminate\Contracts\Auth\PasswordBroker'],
			'queue'                => ['Illuminate\Queue\QueueManager', 'Illuminate\Contracts\Queue\Factory', 'Illuminate\Contracts\Queue\Monitor'],
			'queue.connection'     => 'Illuminate\Contracts\Queue\Queue',
			'redirect'             => 'Illuminate\Routing\Redirector',
			'redis'                => ['Illuminate\Redis\Database', 'Illuminate\Contracts\Redis\Database'],
			'request'              => 'Illuminate\Http\Request',
			'router'               => ['Illuminate\Routing\Router', 'Illuminate\Contracts\Routing\Registrar'],
			'session'              => 'Illuminate\Session\SessionManager',
			'session.store'        => ['Illuminate\Session\Store', 'Symfony\Component\HttpFoundation\Session\SessionInterface'],
			'url'                  => ['Illuminate\Routing\UrlGenerator', 'Illuminate\Contracts\Routing\UrlGenerator'],
			'validator'            => ['Illuminate\Validation\Factory', 'Illuminate\Contracts\Validation\Factory'],
			'view'                 => ['Illuminate\View\Factory', 'Illuminate\Contracts\View\Factory'],
		);

		foreach ($aliases as $key => $aliases) {
			foreach ((array) $aliases as $alias) {
				$this->alias($key, $alias);
			}
		}
	}

	public function alias($abstract, $alias) {
		$this->aliases[$alias] = $abstract;
	}

	public function getaliases() {
		return $this->aliases;
	}

}

$abc = new abc();
$abc->registerCoreContainerAliases();
var_export($abc->getaliases());
/**
array (
  'Illuminate\\Foundation\\Application' => 'app',
  'Illuminate\\Contracts\\Container\\Container' => 'app',
  'Illuminate\\Contracts\\Foundation\\Application' => 'app',
  'Illuminate\\Console\\Application' => 'artisan',
  'Illuminate\\Contracts\\Console\\Application' => 'artisan',
  'Illuminate\\Auth\\AuthManager' => 'auth',
  'Illuminate\\Auth\\Guard' => 'auth.driver',
  'Illuminate\\Contracts\\Auth\\Guard' => 'auth.driver',
  'Illuminate\\Auth\\Passwords\\TokenRepositoryInterface' => 'auth.password.tokens',
  'Illuminate\\View\\Compilers\\BladeCompiler' => 'blade.compiler',
  'Illuminate\\Cache\\CacheManager' => 'cache',
  'Illuminate\\Contracts\\Cache\\Factory' => 'cache',
  'Illuminate\\Cache\\Repository' => 'cache.store',
  'Illuminate\\Contracts\\Cache\\Repository' => 'cache.store',
  'Illuminate\\Config\\Repository' => 'config',
  'Illuminate\\Contracts\\Config\\Repository' => 'config',
  'Illuminate\\Cookie\\CookieJar' => 'cookie',
  'Illuminate\\Contracts\\Cookie\\Factory' => 'cookie',
  'Illuminate\\Contracts\\Cookie\\QueueingFactory' => 'cookie',
  'Illuminate\\Encryption\\Encrypter' => 'encrypter',
  'Illuminate\\Contracts\\Encryption\\Encrypter' => 'encrypter',
  'Illuminate\\Database\\DatabaseManager' => 'db',
  'Illuminate\\Events\\Dispatcher' => 'events',
  'Illuminate\\Contracts\\Events\\Dispatcher' => 'events',
  'Illuminate\\Filesystem\\Filesystem' => 'files',
  'Illuminate\\Contracts\\Filesystem\\Factory' => 'filesystem',
  'Illuminate\\Contracts\\Filesystem\\Filesystem' => 'filesystem.disk',
  'Illuminate\\Contracts\\Filesystem\\Cloud' => 'filesystem.cloud',
  'Illuminate\\Contracts\\Hashing\\Hasher' => 'hash',
  'Illuminate\\Translation\\Translator' => 'translator',
  'Symfony\\Component\\Translation\\TranslatorInterface' => 'translator',
  'Illuminate\\Log\\Writer' => 'log',
  'Illuminate\\Contracts\\Logging\\Log' => 'log',
  'Psr\\Log\\LoggerInterface' => 'log',
  'Illuminate\\Mail\\Mailer' => 'mailer',
  'Illuminate\\Contracts\\Mail\\Mailer' => 'mailer',
  'Illuminate\\Contracts\\Mail\\MailQueue' => 'mailer',
  'Illuminate\\Pagination\\Factory' => 'paginator',
  'Illuminate\\Auth\\Passwords\\PasswordBroker' => 'auth.password',
  'Illuminate\\Contracts\\Auth\\PasswordBroker' => 'auth.password',
  'Illuminate\\Queue\\QueueManager' => 'queue',
  'Illuminate\\Contracts\\Queue\\Factory' => 'queue',
  'Illuminate\\Contracts\\Queue\\Monitor' => 'queue',
  'Illuminate\\Contracts\\Queue\\Queue' => 'queue.connection',
  'Illuminate\\Routing\\Redirector' => 'redirect',
  'Illuminate\\Redis\\Database' => 'redis',
  'Illuminate\\Contracts\\Redis\\Database' => 'redis',
  'Illuminate\\Http\\Request' => 'request',
  'Illuminate\\Routing\\Router' => 'router',
  'Illuminate\\Contracts\\Routing\\Registrar' => 'router',
  'Illuminate\\Session\\SessionManager' => 'session',
  'Illuminate\\Session\\Store' => 'session.store',
  'Symfony\\Component\\HttpFoundation\\Session\\SessionInterface' => 'session.store',
  'Illuminate\\Routing\\UrlGenerator' => 'url',
  'Illuminate\\Contracts\\Routing\\UrlGenerator' => 'url',
  'Illuminate\\Validation\\Factory' => 'validator',
  'Illuminate\\Contracts\\Validation\\Factory' => 'validator',
  'Illuminate\\View\\Factory' => 'view',
  'Illuminate\\Contracts\\View\\Factory' => 'view',
)
*/
