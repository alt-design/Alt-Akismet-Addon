<?php

namespace AltDesign\AltAkismet;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;

use Statamic\Providers\AddonServiceProvider;

use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use AltDesign\AltAkismet\Events\AltFormSubmitted;

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
            $nav->content('Alt Akismet')
                ->section('Tools')
                ->can('view alt-akismet')
                ->route('alt-akismet.index')
                ->icon('<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="20" height="20" viewBox="0 0 24 24"><g id="Custom_Size_1"> <path d="M-36.609-78.623l-.284.477C-37.746-76.665-38.456-76-39.167-76c-.853,0-1.137-1.146-1.137-4.968,0-3.773.379-4.919,1.942-6.066l.663-.477a3.611,3.611,0,0,0,1.09-1.146Zm8.527,3.031c0-.013,0-.024,0-.037l0-5.334v-6.83c0-3.916-1.184-6.161-3.979-7.594A14.253,14.253,0,0,0-38.5-96.772c-5.068,0-9.663,2.532-9.663,5.349,0,1.863,1.705,3.2,4.074,3.2,2.463,0,4.264-1.385,4.264-3.248s-1.658-3.152-4.027-3.152a4.971,4.971,0,0,0-1.374.143,9.537,9.537,0,0,1,4.974-1.528c2.652,0,3.837,1.385,3.837,4.442,0,2.914-1,3.964-4.832,5.206l-1.184.382c-4.642,1.48-6.442,3.248-6.442,6.161,0,3.2,2.842,5.684,6.395,5.684,2.842,0,4.311-.907,6.064-3.964a5.322,5.322,0,0,0,5.543,3.964C-28.028-74.017-28.082-75.2-28.082-75.592Z" transform="translate(50.095 97.449)" fill="none" stroke-linecap="round" stroke="currentcolor" stroke-linejoin="round" stroke-width="1"/> </g> </svg>');
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
