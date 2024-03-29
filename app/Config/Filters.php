<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;
use Fluent\Cors\Filters\CorsFilter;

class Filters extends BaseConfig
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array
     */
    public $aliases = [
        'cors'          => CorsFilter::class,
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'authFilter'    => \Modules\Auth\Filters\AuthFilter::class,
        'throttle'      => \App\Filters\Throttle::class,
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     *
     * @var array
     */
    public $globals = [
        'before' => [
            'jwt' => ['except' => ['account/', 'account/login', 'account/resetPassword', 'tools/*']],  // Ready to go just waiting the right moment to un comment
            'authFilter' => ['except' => ['account/', 'account/login', 'account/resetPassword', 'tools/*']],  // Ready to go just waiting the right moment to un comment
            // 'honeypot',
            // 'csrf',
            // 'invalidchars',
        ],
        'after' => [
            'toolbar',
            'authFilter' => ['except' => ['account/logout', 'tools/*']],
            // 'honeypot',
            // 'secureheaders',
        ],
    ];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'post' => ['csrf', 'throttle']
     *
     * @var array
     */
    public $methods = [
        'post' => [
            // 'csrf' => ['except' => 'api/*'],
        ],
    ];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     *
     * @var array
     */
    public $filters = [
        'throttle' => [
            'before' => ['api/login']
        ],
        'cors'     => [
            'before' => ['*'],
            'after'  => ['*'],
        ]
    ];
}
