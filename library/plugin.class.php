<?php
namespace cipher;

/**
 * @package LC Plugin
 * @version 1.0
 * @copyright Copyright 2020 Luigi Cavalieri.
 * @license https://opensource.org/licenses/GPL-3.0 GPL v3.0
 *
 *
 *
 * A base class upon which can be built the plugin's main class.
 */
abstract class Plugin {
    /**
     * @since 1.0
     */
    const PLUGIN_NAME = '';

    /**
     * @since 1.0
     */
    const VERSION = '1.0';
    
    /**
     * @since 1.0
     */
    const MIN_WP_VERSION = '5.2';

	/**
	 * @since 1.0
	 * @var object
	 */
	protected static $plugin;

	/**
     * Path of the main file (plugin-name.php).
     *
     * @since 1.0
     * @var string
     */
    protected $loaderPath;

    /**
     * Name of the plugin's directory.
     *
     * @since 1.0
     * @var string
     */
    protected $dirName;
	
	/**
	 * @since 1.1
	 * @var string
	 */
	protected $dirPath;

	/**
     * URL of the plugin's directory.
     *
     * @since 1.0
     * @var string
     */
    protected $dirURL;

    /**
     * @see registerAdminNoticeActionWithMessage()
     * @since 1.0
     *
     * @var string
     */
    protected $compatibilityErrorMessage;

   /**
     * @since 1.0
     *
     * @param string $loader_path
     * @return bool
     */
    public static function launch( $loader_path ) {
        global $pagenow;

        self::$plugin             = new static();
        self::$plugin->loaderPath = $loader_path;
        self::$plugin->dirPath    = dirname( $loader_path ) . '/';
        self::$plugin->dirName    = basename( self::$plugin->dirPath );

        return self::$plugin->verifyWordPressCompatibility();
    }

    /**
     * Returns a reference to the plugin object.
     *
     * @since 1.0
     * @return object
     */
    public static function invoke() {
        return self::$plugin;
    }

    /**
     * @since 1.0
     */
    private function __construct() {}
    
    /**
     * @since 1.0
     * @return int Error code.
     */
    public function __clone() { return -1; }

    /**
     * @since 1.0
     * @return int Error code.
     */
    public function __wakeup() { return -1; }

    /**
     * @since 1.0
     *
     * @param string $relative_path
     * @return object
     */
    public function load( $relative_path ) {
        include( $this->dirPath . $relative_path );
    }
	
    /**
     * @since 1.0
     * @return string
     */
    public function version() { 
        return static::VERSION;
    }

	/**
     * @since 1.0
     * @return string
     */
    public function loaderPath() {
        return $this->loaderPath;
    }

    /**
     * @since 1.0
     * @return string
     */
    public function dirPath() {
        return $this->dirPath;
    }

    /**
     * @since 1.0
     * @return string
     */
    public function dirName() {
        return $this->dirName;
    }

    /**
     * @since 1.0
     * @return string
     */
    public function textdomain() {
        return $this->dirName;
    }

    /**
     * @since 1.0
     *
     * @param string $path Optional.
     * @return string
     */
    public function dirURL( $path = '' ) {
        if (! $this->dirURL )
            $this->dirURL = plugins_url( $this->dirName() . '/' );

        return $this->dirURL . $path;
    }
	
	/**
	 * @since 1.1
	 * @return bool
	 */
	private function verifyWordPressCompatibility() {
		global $wp_version;
        
        if ( version_compare( $wp_version, static::MIN_WP_VERSION, '>=' ) ) {
            return true;
        }

        $this->loadTextdomain();

        $plugin_version = static::PLUGIN_NAME . ' ' . static::VERSION;
        $message_format = __( 'To use %1$s you need at least WordPress %2$s. '
                            . 'Please, update your WordPress installation to '
                            . '%3$sthe latest version available%4$s.', $this->textdomain() );

        $this->registerAdminNoticeActionWithMessage( sprintf(
            $message_format,
            $plugin_version,
            static::MIN_WP_VERSION, 
            '<a href="https://wordpress.org/download/">', 
            '</a>'
        ));

        return false;
	}

	/**
     * @since 1.0
     */
	public function loadTextdomain() {
		$languages_folder_path = $this->dirName() . '/languages/';

		load_plugin_textdomain( $this->textdomain(), false, $languages_folder_path );
	}
	
	/**
     * @since 1.0
     * @param string $message
     */
    public function registerAdminNoticeActionWithMessage( $message ) {
        $this->compatibilityErrorMessage = $message;

        add_action( 'admin_notices', array( $this, 'displayAdminNotice' ) );
    }
    
    /**
     * @since 1.0
     */
    public function displayAdminNotice() {
        echo '<div class="error"><p>', $this->compatibilityErrorMessage, '</p></div>';
            
        // Hides the message "Plugin Activated" 
        // if the error is triggered during activation.
        unset( $_GET['activate'] );
    }
}
?>