<?php

/**
 * System checker initializes each of the system check classes, loads them, runs them, and prints them.
 */
class swp_system_checker
{
    public static $custom_checks = array();
    public static $has_error = FALSE;

    public static function full_system_check()
    {
    	swp_system_checker::load_all_checks();
    	swp_system_checker::run_all_checks();
    	swp_system_checker::print_all_errors();

    }

    public static function load_all_checks()
    {
    	swp_system_checker::$custom_checks[ 'swp_php_check' ]  = new swp_php_check();
    	swp_system_checker::$custom_checks[ 'swp_curl_check' ] = new swp_curl_check();
    	swp_system_checker::$custom_checks[ 'swp_wp_check' ]   = new swp_wp_check();
    }

    public static function run_all_checks()
    {
		foreach( swp_system_checker::$custom_checks as $custom_check )
		{
			if( method_exists( $custom_check, 'run' ) ):
				if( !$custom_check->run() && !$custom_check->check_passed ):
					swp_system_checker::$has_error = true;
				endif;
			endif;
		}
    }

    public static function print_all_errors()
    {
    	if( !isset( swp_system_checker::$has_error ) || empty( swp_system_checker::$has_error ) ):
    		return FALSE;
    	endif;

    	foreach( swp_system_checker::$custom_checks as $custom_check )
    	{
    		if( $custom_check->check_passed ):
    			continue;
    		endif;

    		echo '<div class="sw-red-notice">';
    		echo '<br><p style="font-weight:bold">A problem has been found:</p>' . $custom_check->whats_wrong;
    		echo '<br><p style="font-weight:bold">Here is how to resolve the problem:</p>' . $custom_check->how_to_fix;
    		echo '</div>';
    	}
    }
}

/**
 * Parent class of each subclass
 */
abstract class swp_custom_check
{
	public $name = "";
	public $whats_wrong = "";
	public $how_to_fix= "";
	public $check_passed = null;
	public $additional_message = null;

	/**
	 * Force children to have an executable run method.
	 */
	abstract public function run();
}

/**
 * Checks for PHP compatibilty
 */
class swp_php_check extends swp_custom_check
{
	public function __construct()
	{
		$this->name = 'PHP Check';
	}

	public function run()
	{
		//if( version_compare( PHP_VERSION, '5.2.0' ) < 0 ): // FOR TESTING

		if( version_compare( PHP_VERSION, '5.2.0' ) >= 0 ):
			$this->check_passed = true;
		else:
			$this->check_passed = false;
			$this->whats_wrong = 'Your server is currently using PHP version 5.2 (or whatever version it is). In order for our plugin to fetch share counts properly, you must be using PHP 5.3 or newer.';
			$this->how_to_fix = 'To fix this, simply contact your hosting provider and ask them to update your server to the latest stable version of PHP.  If possible, test your website on a newer version before updating to avoid errors that could cause problems on a live site.';
		endif;

		return $this->check_passed;
	}
}

/**
 * Checks for CURL compatibilty
 */
class swp_curl_check extends swp_custom_check
{
	public function __construct()
	{
		$this->name = 'Curl Check';
	}

	public function run()
	{
		//if( !function_exists( 'curl_version' ) ): // FOR TESTING

		if( function_exists( 'curl_version' ) ):
			$this->check_passed = true;
		else:
			$this->check_passed = false;
			$this->whats_wrong = 'Your server has cURL disabled. In order for our plugin to fetch share counts, you must have cURL enabled on your server.';
			$this->how_to_fix = 'To fix this, simply contact your hosting provider and ask them to activate cURL on your server.';
		endif;

		return $this->check_passed;
	}
}

/**
 * Checks for Wordpress compatibilty
 */
class swp_wp_check extends swp_custom_check
{
	public function __construct()
	{
		$this->name = 'Wordpress Check';
	}

	public function run()
	{
		global $wp_version;
		//if( $wp_version >= '6.5.2' ): // FOR TESTING

		if( $wp_version >= '4.5.2' ):
			$this->check_passed = true;
		else:
			$this->check_passed = false;
			$this->whats_wrong = 'Your website is currently using WordPress version ' . $wp_version;
			$this->how_to_fix = 'This version is very out of date. For security and compatibility reasons, you should immediately update WordPress to the latest version.';
		endif;

		return $this->check_passed;
	}
}
?>
