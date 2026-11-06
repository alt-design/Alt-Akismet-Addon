<?php

namespace AltDesign\AltAkismet;

use AltDesign\AltAkismet\Events\AltFormSubmitted;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use AltDesign\AltAkismet\Support\AltToolsNav;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $vite = [
        'input' => [
            'resources/js/alt-akismet-addon.js',
            'resources/css/alt-akismet-addon.css',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function registerPermissions()
    {
        Permission::register('view alt-akismet')
            ->label('View Alt Akismet');
    }

    public function registerEvents()
    {
        Event::subscribe(AltFormSubmitted::class);
    }

    public function addToNav()
    {
        Nav::extend(function ($nav) {
            AltToolsNav::add($nav, 'spam', 'Alt Akismet', 'alt-akismet.index', 'view alt-akismet', ['filter', 'filter']);
        });
    }

    // Authenticates your Akismet API key
    function akismet_verify_key( $key ) {
        // Deal with dodgy requests
        if(!isset($_SERVER['HTTP_HOST'])) {
            return false;
        }

        return Cache::remember('alt_is_valid_akismet', 60 * 60 * 24, function() use($key) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $baseUrl = $protocol . "://" . $_SERVER['HTTP_HOST'];
            $blog = urlencode($baseUrl);
            $request = 'key='. $key .'&blog='. $blog;
            $host = $http_host = 'rest.akismet.com';
            $path = '/1.1/verify-key';
            $port = 443;
            $akismet_ua = "WordPress/4.4.1 | Akismet/3.1.7";
            $content_length = strlen( $request );
            $http_request  = "POST $path HTTP/1.0\r\n";
            $http_request .= "Host: $host\r\n";
            $http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $http_request .= "Content-Length: {$content_length}\r\n";
            $http_request .= "User-Agent: {$akismet_ua}\r\n";
            $http_request .= "\r\n";
            $http_request .= $request;
            $response = '';

            if( false != ( $fs = @fsockopen( 'ssl://' . $http_host, $port, $errno, $errstr, 10 ) ) ) {
                fwrite( $fs, $http_request );
                while( !feof( $fs ) ) {
                    $response .= fgets( $fs, 1160 ); // One TCP-IP packet
                }
                fclose( $fs );
                $response = explode( "\r\n\r\n", $response, 2 );
            }

            if ( 'valid' == $response[1] ) {
                return true;
            } else {
                return false;
            }
        });
    }

    public function bootAddon()
    {
        $this->akismet_verify_key(env("ALT_AKISMET_API_KEY"));
        $this->addToNav();
        $this->registerPermissions();
        $this->registerEvents();
    }
}
