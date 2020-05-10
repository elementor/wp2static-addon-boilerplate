<?php

namespace WP2StaticBoilerplate;

use WP_CLI;

/**
 * WP2StaticBoilerplate WP-CLI commands
 *
 * Registers WP-CLI commands for WP2StaticBoilerplate under main wp2static cmd
 *
 * Usage: wp wp2static boilerplate options set aRegularOption 'Some value'
 */
class CLI {

    /**
     * Boilerplate add-on commands
     *
     * @param string[] $args CLI args
     * @param string[] $assoc_args CLI args
     */
    public function boilerplate(
        array $args,
        array $assoc_args
    ) : void {
        $action = isset( $args[0] ) ? $args[0] : null;
        $arg = isset( $args[1] ) ? $args[1] : null;

        if ( empty( $action ) ) {
            WP_CLI::error( 'Missing required argument: <options>' );
        }

        if ( $action === 'options' ) {
            if ( empty( $arg ) ) {
                WP_CLI::error( 'Missing required argument: <get|set|list>' );
            }

            $option_name = isset( $args[2] ) ? $args[2] : null;

            if ( $arg === 'get' ) {
                if ( empty( $option_name ) ) {
                    WP_CLI::error( 'Missing required argument: <option-name>' );
                    return;
                }

                // decrypt encrypted values
                if ( $option_name === 'anEncryptedOption' ) {
                    $option_value = \WP2Static\CoreOptions::encrypt_decrypt(
                        'decrypt',
                        Controller::getValue( $option_name )
                    );
                } else {
                    $option_value = Controller::getValue( $option_name );
                }

                WP_CLI::line( $option_value );
            }

            if ( $arg === 'set' ) {
                if ( empty( $option_name ) ) {
                    WP_CLI::error( 'Missing required argument: <option-name>' );
                    return;
                }

                $option_value = isset( $args[3] ) ? $args[3] : null;

                if ( empty( $option_value ) ) {
                    $option_value = '';
                }

                // decrypt an encrypted option
                if ( $option_name === 'anEncryptedOption' ) {
                    $option_value = \WP2Static\CoreOptions::encrypt_decrypt(
                        'encrypt',
                        $option_value
                    );
                }

                Controller::saveOption( $option_name, $option_value );
            }

            if ( $arg === 'list' ) {
                $options = Controller::getOptions();

                // decrypt encrypted values
                $options['anEncryptedOption']->value = \WP2Static\CoreOptions::encrypt_decrypt(
                    'decrypt',
                    $options['anEncryptedOption']->value
                );

                WP_CLI\Utils\format_items(
                    'table',
                    $options,
                    [ 'name', 'value' ]
                );
            }
        }
    }

    /**
     * Print multilines of input text via WP-CLI
     *
     * Helper to display multiline prompts on the CLI
     */
    public function multilinePrint( string $string ) : void {
        $msg = trim( str_replace( [ "\r", "\n" ], '', $string ) );

        $msg = preg_replace( '!\s+!', ' ', $msg );

        WP_CLI::line( PHP_EOL . $msg . PHP_EOL );
    }
}

