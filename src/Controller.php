<?php

// This Add-on's unique namespace
namespace WP2StaticBoilerplate;

/**
 * Controller class
 *
 * For simple Add-ons, sticking everything in here can save complexity
 * but for better organization, try to put any API clients, specialized
 * functions into their own classes under the same namespace and keep this thin
 */
class Controller {
    /**
     * This Add-on's initialization routine
     *
     * Runs on frequently, so avoid resource intensive routines in here.
     */
    public function run() : void {
        // registers this Add-on's options page
        add_action(
            'admin_menu',
            [ $this, 'addOptionsPage' ],
            15,
            1
        );

        // ensures WP2Static > Options is active menu when in Add-on's options view
        add_filter( 'parent_file', [ $this, 'setActiveParentMenu' ] );

        // calls our options save handler when POSTing from Add-on's options view
        add_action(
            'admin_post_wp2static_boilerplate_save_options',
            [ $this, 'saveOptionsFromUI' ],
            15,
            1
        );

        // registers a handler to trigger for WP2Static `deploy` phase of workflow
        add_action(
            'wp2static_deploy',
            [ $this, 'deploy' ],
            15,
            1
        );

        // function to run after deployment (ie, cache invalidation, Slack notification)
        add_action(
            'wp2static_post_deploy_trigger',
            [ 'WP2StaticBoilerplate\Boilerplate', 'post_deployment_action' ],
            15,
            1
        );

        if ( defined( 'WP_CLI' ) ) {
            \WP_CLI::add_command(
                'wp2static boilerplate',
                [ 'WP2StaticBoilerplate\CLI', 'boilerplate' ]
            );
        }
    }

    /**
     *  Get all Add-on's options
     *
     *  Used on the Add-ons options page or via WP-CLI
     *
     *  @return mixed[] All options
     */
    public static function getOptions() : array {
        global $wpdb;
        $options = [];

        $table_name = $wpdb->prefix . 'wp2static_addon_boilerplate_options';

        $rows = $wpdb->get_results( "SELECT * FROM $table_name" );

        foreach ( $rows as $row ) {
            $options[ $row->name ] = $row;
        }

        return $options;
    }

    /**
     * Seed Add-on options
     *
     * Ensures Add-on has required options initialized before usage.
     */
    public static function seedOptions() : void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_addon_boilerplate_options';

        $query_string =
            "INSERT INTO $table_name (name, value, label, description) VALUES (%s, %s, %s, %s);";

        $query = $wpdb->prepare(
            $query_string,
            'aRegularOption',
            '',
            'A regular option',
            ''
        );

        $wpdb->query( $query );

        $query = $wpdb->prepare(
            $query_string,
            'anEncryptedOption',
            '',
            'An encrypted option',
            ''
        );

        $wpdb->query( $query );
    }

    /**
     * Save option
     *
     * Saves an option. Called when POSTing via UI or saving with WP-CLI
     *
     * @param mixed $value option value to save
     */
    public static function saveOption( string $name, $value ) : void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_addon_boilerplate_options';

        $wpdb->update(
            $table_name,
            [ 'value' => $value ],
            [ 'name' => $name ]
        );
    }

    /**
     * Render the Add-ons options page
     *
     * Add-ons don't get their own submenu within WP2Static, but if they have
     * configurable options, it's expected to register an options page with WP2Static Core
     * which will link to them from the Add-ons page
     */
    public static function renderBoilerplatePage() : void {
        // template variables for the Add-on's options page
        $view = [];
        // nonce used to validate any POSTing from the Add-ons options page
        $view['nonce_action'] = 'wp2static-boilerplate-options';

        // get some SiteInfo from WP2Static core plugin
        $view['uploads_path'] = \WP2Static\SiteInfo::getPath( 'uploads' );
        $view['uploads_url'] = \WP2Static\SiteInfo::getUrl( 'uploads' );
        // get all of this Add-ons options from database
        $view['options'] = self::getOptions();
        // load options page template from disk
        require_once __DIR__ . '/../views/options-page.php';
    }

    /**
     * Handler for the `wp2static_deploy` action if this Add-on
     * is to perform anything on the `deploy` phase of WP2Static workflow
     */
    public function deploy( string $processed_site_path ) : void {
        \WP2Static\WsLog::l( 'Boilerplate Addon deploying' );

        $boilerplate_deployer = new Boilerplate();
        $boilerplate_deployer->upload_files( $processed_site_path );
    }

    public static function activate_for_single_site() : void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_addon_boilerplate_options';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            value VARCHAR(255) NOT NULL,
            label VARCHAR(255) NULL,
            description VARCHAR(255) NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // keep Add-ons options schema up to date
        dbDelta( $sql );

        $options = self::getOptions();

        /**
         * Check that Add-ons required options have been initialized
         * or seed default options
        */
        if ( ! isset( $options['aRegularOption'] ) ) {
            self::seedOptions();
        }

        do_action(
            'wp2static_register_addon', // hook fired from WP2Static
            'wp2static-addon-boilerplate', // this plugin's slug
            'deploy', // type of add-on we're registering
            'Boilerplate Demonstration', // Add-on name
            'https://github.com/WP2Static/wp2static-addon-boilerplate', // docs URL
            'Plugin to serve as developer reference' // description
        );
    }

    public static function deactivate_for_single_site() : void {
    }

    public static function deactivate( bool $network_wide = null ) : void {
        if ( $network_wide ) {
            global $wpdb;

            $query = 'SELECT blog_id FROM %s WHERE site_id = %d;';

            $site_ids = $wpdb->get_col(
                sprintf(
                    $query,
                    $wpdb->blogs,
                    $wpdb->siteid
                )
            );

            foreach ( $site_ids as $site_id ) {
                switch_to_blog( $site_id );
                self::deactivate_for_single_site();
            }

            restore_current_blog();
        } else {
            self::deactivate_for_single_site();
        }
    }

    public static function activate( bool $network_wide = null ) : void {
        if ( $network_wide ) {
            global $wpdb;

            $query = 'SELECT blog_id FROM %s WHERE site_id = %d;';

            $site_ids = $wpdb->get_col(
                sprintf(
                    $query,
                    $wpdb->blogs,
                    $wpdb->siteid
                )
            );

            foreach ( $site_ids as $site_id ) {
                switch_to_blog( $site_id );
                self::activate_for_single_site();
            }

            restore_current_blog();
        } else {
            self::activate_for_single_site();
        }
    }

    public static function saveOptionsFromUI() : void {
        check_admin_referer( 'wp2static-boilerplate-options' );

        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_addon_boilerplate_options';

        $an_encrypted_option =
            $_POST['anEncryptedOption'] ?
            \WP2Static\CoreOptions::encrypt_decrypt(
                'encrypt',
                sanitize_text_field( $_POST['anEncryptedOption'] )
            ) : '';

        $wpdb->update(
            $table_name,
            [ 'value' => $an_encrypted_option ],
            [ 'name' => 'anEncryptedOption' ]
        );

        $wpdb->update(
            $table_name,
            [ 'value' => sanitize_text_field( $_POST['aRegularOption'] ) ],
            [ 'name' => 'aRegularOption' ]
        );

        wp_safe_redirect( admin_url( 'admin.php?page=wp2static-addon-boilerplate' ) );
        exit;
    }

    /**
     * Get option value
     *
     * @return string option value
     */
    public static function getValue( string $name ) : string {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_addon_boilerplate_options';

        $sql = $wpdb->prepare(
            "SELECT value FROM $table_name WHERE" . ' name = %s LIMIT 1',
            $name
        );

        $option_value = $wpdb->get_var( $sql );

        if ( ! is_string( $option_value ) ) {
            return '';
        }

        return $option_value;
    }

    /**
     * Register Add-on options page
     *
     * Will be linked to from WP2Static > Add-ons page
     */
    public function addOptionsPage() : void {
        add_submenu_page(
            'null', // don't add within WP2Static menu directly
            'Boilerplate Deployment Options', // page name / title
            'Boilerplate Deployment Options', // page name / title
            'manage_options', // user level required to access page
            'wp2static-addon-boilerplate', // page slug
            [ $this, 'renderBoilerplatePage' ] // function to render page
        );
    }

    /**
     * Set WP2Static > Options menu to active when viewing this
     * add-on's options page.
     */
    public function setActiveParentMenu() : void {
            global $plugin_page;

        if ( 'wp2static-addon-boilerplate' === $plugin_page ) {
            // phpcs:ignore
            $plugin_page = 'wp2static';
        }
    }
}

