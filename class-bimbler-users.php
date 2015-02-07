<?php
/**
 * Bimbler Users
 *
 * @package   Bimbler_Users
 * @author    Paul Perkins <paul@paulperkins.net>
 * @license   GPL-2.0+
 * @link      http://www.paulperkins.net
 * @copyright 2014 Paul Perkins
 */

/**
 * Include dependencies necessary... (none at present)
 *
 */

/**
 * Bimbler Users
 *
 * @package Bimbler_Users
 * @author  Paul Perkins <paul@paulperkins.net>
 */
class Bimbler_Users {

        /*--------------------------------------------*
         * Constructor
         *--------------------------------------------*/

        /**
         * Instance of this class.
         *
         * @since    1.0.0
         *
         * @var      object
         */
        protected static $instance = null;

        /**
         * Return an instance of this class.
         *
         * @since     1.0.0
         *
         * @return    object    A single instance of this class.
         */
        public static function get_instance() {

                // If the single instance hasn't been set, set it now.
                if ( null == self::$instance ) {
                        self::$instance = new self;
                } // end if

                return self::$instance;

        } // end get_instance

        /**
         * Initializes the plugin by setting localization, admin styles, and content filters.
         */
        private function __construct() {

        	
        	add_action ('wp_enqueue_scripts', array ($this, 'enqueue_bootstrap_scripts'));
        	 
        	
        	add_shortcode( 'bimbler_show_users', array ($this, 'show_users'));
        	        	        	         	
		} // End constructor.
		
		private $script = '<script type="text/javascript">
var responsiveHelper;
var breakpointDefinition = {
    tablet: 1024,
    phone : 480
};
var tableContainer;

	jQuery(document).ready(function($)
	{
		tableContainer = $("#table-1");
		
		var thing = tableContainer.dataTable({
			"sPaginationType": "bootstrap",
			"aLengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
			"bStateSave": true,

		    // Responsive Settings
		    bAutoWidth     : false,
		    fnPreDrawCallback: function () {
		        // Initialize the responsive datatables helper once.
		        if (!responsiveHelper) {
		            responsiveHelper = new ResponsiveDatatablesHelper(tableContainer, breakpointDefinition);
		        }
		    },
		    fnRowCallback  : function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
		        responsiveHelper.createExpandIcon(nRow);
		    },
		    fnDrawCallback : function (oSettings) {
		        responsiveHelper.respond();
		    }
		});
				
		thing.columnFilter({
			"sPlaceHolder" : "head:after"
		});
				
		$(".dataTables_wrapper select").select2({
			minimumResultsForSearch: -1
		});
				
	});
				
				
</script>';
		
	private $script_bot = '	<script src="/wp-content/plugins/bimbler-users/assets/js/jquery.dataTables.min.js"></script>
	<script src="/wp-content/plugins/bimbler-users/assets/js/datatables/TableTools.min.js"></script>
	<script src="/wp-content/plugins/bimbler-users/assets/js/dataTables.bootstrap.js"></script>
	<script src="/wp-content/plugins/bimbler-users/assets/js/datatables/jquery.dataTables.columnFilter.js"></script>
	<script src="/wp-content/plugins/bimbler-users/assets/js/datatables/lodash.min.js"></script>
	<script src="/wp-content/plugins/bimbler-users/assets/js/datatables/responsive/js/datatables.responsive.js"></script>';
		
	function enqueue_bootstrap_scripts () {
		wp_register_style( 'style-datatables', plugins_url('data-tables.css', __FILE__) );
		wp_enqueue_style( 'style-datatables' );
		
		wp_register_style( 'style-entypo', plugins_url('entypo.css', __FILE__) );
		wp_enqueue_style( 'style-entypo' );
		
		
		// Load the freewall JS files.
		//wp_register_script ('bimbler-freewall-script', plugin_dir_url( __FILE__ ) . 'js/freewall.js', array( 'jquery' ) );
		//wp_register_script ('bimbler-freewall-index-script', plugin_dir_url( __FILE__ ) . 'js/freewall-index.js', array( 'jquery' ) );
		
		// Just in case.
		//wp_enqueue_script ('jquery');
		
		//wp_enqueue_script( 'bimbler-freewall-script');
		//wp_enqueue_script( 'bimbler-freewall-index-script');
		
	}
		
	function get_users () {
		global $wpdb;
			
		//$table_name = $wpdb->base_prefix . $rsvp_db_table;
			
		$sql =  'SELECT u.id as uid, ';
		$sql .= ' u.user_registered as reg_date ';
		$sql .= " FROM {$wpdb->users} u, ";
		$sql .= " {$wpdb->usermeta} m1 ";
		$sql .= ' WHERE u.id = m1.user_id ';
		$sql .= ' AND m1.meta_key = \'wp_capabilities\' ';
		$sql .= ' AND m1.meta_value NOT LIKE \'%unverified%\' ';
		$sql .= ' ORDER BY u.user_registered DESC';
		
		$users = $wpdb->get_results ($sql);
		
		if (!isset ($users)) {
			//echo 'Cannot get user list.';
			echo $wpdb->print_error ();
		}
		
		return $users;
	}
	
	function get_last_login ($user_object)
	{
		$meta = 'wp-last-login';
		$time_str = 'j M g:ia';
		$time_str = 'Y-m-d H:i:s';
		$timezone = 'Australia/Brisbane';
	
		date_default_timezone_set($timezone);
	
		//$timestamp = strtotime($stored_time);
		//$local_time = $timestamp + date('Z');
	
	
		$time = '';
	
		$meta_time = get_user_meta ($user_object->ID, $meta);
	
		if (!isset ($meta_time[0])) {
			$time = '-';
		} else {
			$time = date ($time_str, $meta_time[0]);
		}
	
		return $time;
	}
	
	function get_meetup_id ($user) {
	
		$meta = get_user_meta($user, 'meetup_id', true);
	
		if (!$meta) {
			return '';
		}
	
		return '*';
	}
	
	function get_login_ip ($user) {
	
		$meta = get_user_meta($user->ID, 'bimbler-login-ip', true);
	
		if (!$meta) {
			return '-';
		}
	
		return $meta;
	}
	
	/*
	 * 
	 *
	 */
	function show_users($atts) {
		
		//global $post;
		
/*		$a = shortcode_atts (array (
								'ahead' 	=> 7,
								'send_mail' => 'Y',
							), $atts);
		
		if (!isset ($a)) {
			error_log ('send_reminder called with no interval set.');
			return;
		} */
		
		$content = '';
		
		$content .= '<table class="table table-bordered table-striped datatable" id="table-1">';
		
		$content .= '	<thead>';
		$content .= '	<tr class="replace-inputs">';
		$content .= '		<th data-hide="phone">User ID</th>';
		$content .= '		<th>User Login</th>';
		$content .= '		<th data-hide="phone">User Name</th>';
		$content .= '		<th data-hide="phone,tablet">Nick Name</th>';
		$content .= '		<th>Email</th>';
		$content .= '		<th>Joined</th>';
		$content .= '		<th>Last Login</th>';
		$content .= '		<th data-hide="phone,tablet">Login IP</th>';
		$content .= '	</tr>';
		$content .= '	<tr>';
		$content .= '		<th>User ID</th>';
		$content .= '		<th>User Login</th>';
		$content .= '		<th data-hide="phone">User Name</th>';
		$content .= '		<th data-hide="phone,tablet">Nick Name</th>';
		$content .= '		<th>Email</th>';
		$content .= '		<th>Joined</th>';
		$content .= '		<th>Last Login</th>';
		$content .= '		<th data-hide="phone,tablet">Login IP</th>';
		$content .= '	</tr>';
		$content .= '	</thead>';
		$content .= '	<tbody>';
		
		$odd = true;
		
		if ( !is_super_admin() ){
			$content = '<div class="bimbler-alert-box notice"><span>Notice: </span>You must be an admin user to view this page.</div>';
		
			return $content;
		}
		
		
		$users = $this->get_users();
		
		if (!isset ($users)) {
			return "Error";
		}
		
		
		
		//$content .= '<p>Users: ' . count($users) . '</p>';
		
		foreach ( $users as $user) {
			$user_info   = get_userdata ($user->uid);
			$username = $user_info->user_login;
			$user_email = $user_info->user_email;
			$user_person = $user_info->user_firstname . ' ' . $user_info->user_lastname;
			//$user_person = $user_info->nicename;
			$registered = $user->reg_date;
			$avatar = get_avatar ($user->uid, $size='150');
			$legacy = $this->get_meetup_id($user->uid);
			
			$last_login = $this->get_last_login($user_info);
			$last_ip = $this->get_login_ip($user_info);
				

			$content .= '<tr class="';
			if ($odd) {
				$content .= ' odd';
			} else {
				$content .= ' even';
			}

			$content .= ' grade A">';
			
			
			
			$content .= '<td>' . $user->uid . '</td>';
			$content .= '<td><a href="/profile/' . $user_info->user_nicename .'" title="View ' . $username . '\'s profile">' . $username . '</a> ' .  $legacy . '</td>';
			//$content .= '<td>' . $username . '</td>';
			$content .= '<td>' . $user_person . '</td>';
			$content .= '<td>' . $user_info->nickname . '</td>';
			$content .= '<td>' . $user_email . '</td>';
			$content .= '<td>' . $registered . '</td>';
			$content .= '<td class="center">' . $last_login . '</td>';
			$content .= '<td class="center">' . $last_ip . '</td>';
				
			$content .= '</tr>';
			
					
			/*<tr class="odd gradeX">
			<td>Trident</td>
			<td>Internet Explorer 4.0</td>
			<td>Win 95+</td>
			<td class="center">4</td>
			<td class="center">X</td>
			</tr>
			<tr class="even gradeC">
			<td>Trident</td>
			<td>Internet Explorer 5.0</td>
			<td>Win 95+</td>
			<td class="center">5</td>
			<td class="center">C</td>
			</tr>
			<tr class="odd gradeA">
			<td>Trident</td>
			<td>Internet Explorer 5.5</td>
			<td>Win 95+</td>
			<td class="center">5.5</td>
			<td class="center">A</td>
			</tr> */

			if ($odd) {
				$odd = false;
			} else {
				$odd = true;
			}
		}
		
		$content .= '	</tbody>';
		$content .= '</table>';
		
		$content .= $this->script;
		$content .= $this->script_bot;
		
		return $content;
				
	}
					
} // End class
