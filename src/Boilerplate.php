<?php

// This Add-on's unique namespace
namespace WP2StaticBoilerplate;

// Iterating for Add-ons processing or deploying files
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Boilerplate
 *
 * Heavy lifting of Add-on preferred in here than Controller
 */
class Boilerplate {

    /**
     * Encrypted option example
     *
     * @var string $an_encrypted_option
     */
    private $an_encrypted_option;
    /**
     * Encrypted option example
     *
     * @var string $a_regular_option
     */
    private $a_regular_option;

    public function __construct() {
        $this->an_encrypted_option = \WP2Static\CoreOptions::encrypt_decrypt(
            'decrypt',
            Controller::getValue( 'an_encrypted_option' )
        );
        $this->a_regular_option = Controller::getValue( 'boilerplateStorageZoneName' );

        $notice = 'Boilerplate class has been instantiated with' .
            "a regular option: $this->a_regular_option " .
            "and an encrypted option: $this->an_encrypted_option ";

        // log to WP2Static > Logs
        \WP2Static\WsLog::l( $notice );
    }

    /**
     * Upload processed StaticSite files
     *
     * This could be via a 3rd party API, local copy, ZIP, etc.
     * For deployment options without their own/good PHP library
     * a requests library, like Guzzle may be used (refer Netlify
     * or BunnyCDN deployment Add-ons for examples).
     */
    public function upload_files( string $processed_site_path ) : void {
        $notice = 'Boilerplate Add-on is simulating uploading files';
        // log to WP2Static > Logs
        \WP2Static\WsLog::l( $notice );

        if ( ! is_dir( $processed_site_path ) ) {
            return;
        }

        // iterate each file in ProcessedSite
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $processed_site_path,
                RecursiveDirectoryIterator::SKIP_DOTS
            )
        );

        foreach ( $iterator as $filename => $file_object ) {
            $base_name = basename( $filename );
            if ( $base_name != '.' && $base_name != '..' ) {
                $real_filepath = realpath( $filename );

                $cache_key = str_replace( $processed_site_path, '', $filename );

                if ( \WP2Static\DeployCache::fileisCached( $cache_key ) ) {
                    continue;
                }

                if ( ! $real_filepath ) {
                    $err = 'Trying to deploy unknown file to Boilerplate: ' . $filename;
                    // log to WP2Static > Logs
                    \WP2Static\WsLog::l( $err );
                    // skip this file in iteration
                    continue;
                }

                // Standardise all paths to use / (Windows support)
                // TODO: apply WP method of get_safe_path or such
                $filename = str_replace( '\\', '/', $filename );

                if ( ! is_string( $filename ) ) {
                    continue;
                }

                $remote_path = ltrim( $cache_key, '/' );

                // Note: Do your per-file (or batch) transfers here
                // stimulate successful and failed file transfers
                $result = rand( 0, 1 ) == 1;

                if ( $result ) {
                    // Add file path to DeployCache on successful transfer
                    \WP2Static\DeployCache::addFile( $cache_key );
                } else {
                    $err = "Failed to deploy file $cache_key";
                    // log to WP2Static > Logs
                    \WP2Static\WsLog::l( $err );
                }
            }
        }
    }

    /**
     * Post-procesing action
     *
     * Suited for cache invalidation, Slack notification, etc.
     */
    public static function post_deployment_action( string $enabled_deployer ) : void {
        if ( $enabled_deployer !== 'wp2static-addon-boilerplate' ) {
            return;
        }
        $notice = 'Boilerplate Add-on is simulating post deployment action';
        \WP2Static\WsLog::l( $notice );
    }
}
