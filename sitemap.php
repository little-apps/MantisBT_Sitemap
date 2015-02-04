<?php
/**
 * MantisBT_Sitemap - Generates a sitemap for MantisBT
 * @package MantisBT_Sitemap
 * @link https://github.com/PHPMailer/PHPMailer/ The MantisBT_Sitemap GitHub project
 * @author Little Apps (https://www.little-apps.com)
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License v3
*/

require_once( dirname(__FILE__) . '/core.php' );

class MantisBT_Sitemap {
	private $sitemap = array(
		//array( 'loc' => BASE_URI.'/login_page.php', 'changefreq' => 'monthly', 'priority' => 0.9, 'lastmod' => '2015-2-3' )
	);
	
	private $base_uri;
	private $user_ids = array();
	
	public static function display_sitemap() {
		$sitemap = new MantisBT_Sitemap();
		
		$sitemap->build_sitemap();
		$sitemap->output();
	}
	
	public function __construct() {
		$this->base_uri = rtrim( config_get( 'path' ), '/' );
		
		// If authenticated user -> include needed files
		if (auth_is_user_authenticated()) {
			require_once( dirname(__FILE__) . '/core/current_user_api.php' );
			require_once( dirname(__FILE__). '/core/print_api.php' );
			require_once( dirname(__FILE__). '/core/string_api.php' );
		}
	}
	
	public function build_sitemap() {
		if (!empty($this->sitemap))
			$this->sitemap = array();
		
		if (!empty($this->user_ids))
			$this->user_ids = array();
		
		if (!auth_is_user_authenticated()) {
			// The login page is the only page that anonymous users have access to
			$this->add_to_sitemap('/login_page.php', 'monthly', 1);
		} else {

			// Add default home page
			$this->add_to_sitemap(config_get( 'default_home_page' ), 'weekly', 1);
			
			// Add other main pages
			$this->add_to_sitemap('changelog_page.php');
			$this->add_to_sitemap('roadmap_page.php');
			$this->add_to_sitemap('account_page.php');
			
			// Add bug reports
			$this->add_bug_reports();
			
			// Add users
			$this->add_users();
			
			// Add projects
			$this->add_projects();
		}
	}
	
	public function output($echo = true) {
		$xml = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8' ?>\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" />');
		
		if (!empty($this->sitemap)) {
			foreach ($this->sitemap as $url_info) {
				$url = $xml->addChild('url');
				
				$url->addChild('loc', $url_info['loc']);
				
				if (isset($url_info['changefreq']))
					$url->addChild('changefreq', $url_info['changefreq']);
				
				if (isset($url_info['priority']))
					$url->addChild('priority', $url_info['priority']);
				
				if (isset($url_info['lastmod']))
					$url->addChild('lastmod', $url_info['lastmod']);
			}
		}
		
		if ($echo)
			echo $xml->asXML();
		else
			return $xml->asXML();
	}
	
	private function add_bug_reports() {
		$t_page = 1;
		$t_total = -1;
		$rows = filter_get_bug_rows($t_page, $t_total, $p_page_count, $p_bug_count, null, ALL_PROJECTS);

		foreach ($rows as $t_bug) {
			$link = string_get_bug_view_url( $t_bug->id );

			$last_updated = $t_bug->last_updated;
			
			$priority = 0.5;
			
			if (isset($t_bug->handler_id))
				$this->add_user_id($t_bug->handler_id);
				
			if ($t_bug->priority == 10)
				// none
				$priority = 0.3;
			else if ($t_bug->priority == 20)
				// low
				$priority = 0.4;
			else if ($t_bug->priority == 30)
				// normal
				$priority = 0.5;
			else if ($t_bug->priority == 40)
				// high
				$priority = 0.6;
			else if ($t_bug->priority == 50)
				// urgent
				$priority = 0.7;
			else if ($t_bug->priority == 60)
				// immediate
				$priority = 0.8;
				
			$this->add_to_sitemap($link, 'daily', $priority, $last_updated);
		}
	}
	
	private function add_users() {
		if (empty($this->user_ids))
			return;
		
		foreach ($this->user_ids as $user_id) {
			$link = string_sanitize_url( 'view_user_page.php?id=' . $user_id, true );
			
			$link = str_replace($this->base_uri, '', $link);
			
			$this->add_to_sitemap($link, 'monthly', 0.4);
		}
	}
	
	private function add_projects() {
		$t_project_ids = current_user_get_accessible_projects();
		
		if (!empty($t_project_ids)) {
			foreach ($t_project_ids as $project_id) {
				$link = 'view_all_bug_page.php?project_id='.$project_id;
				
				$this->add_to_sitemap($link, 'daily');
			}
		}
	}
	
	private function add_user_id($user_id) {
		if (user_exists($user_id) && user_get_field($user_id, 'enabled') && !in_array($user_id, $this->user_ids))
			$this->user_ids[] = $user_id;
	}
	
	private function add_to_sitemap($relative_uri, $changefreq = 'monthly', $priority = false, $lastmod = false) {
        $info = array();

        if (parse_url($relative_uri, PHP_URL_SCHEME) != '') {
			// URI is absolute
			throw new Exception('URI is absolute');
		}

        $relative_uri = ltrim($relative_uri, "\\/");

        $info['loc'] = $this->base_uri.'/'.$relative_uri;

        if ($changefreq) {
			if (!in_array($changefreq, array('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never')))
					throw new Exception('Change frequency is invalid');

			$info['changefreq'] = $changefreq;
        }

        if ($priority) {
			if (!is_numeric($priority))
				throw new Exception('Priority is not a number');

			$priority = (float)$priority;

			if (!is_float($priority))
				throw new Exception('Priority is not a float');

			if ($priority < 0 || $priority > 1)
				throw new Exception('Priority must be between 0 and 1');

			$info['priority'] = $priority;
        }

        if ($lastmod) {
			if (!is_numeric($lastmod))
				throw new Exception('Last modified must be a number representing a unix timestamp');

			$lastmod = intval($lastmod);

			$info['lastmod'] = date('Y-m-d', $lastmod);
        }

        $this->sitemap[] = $info;

        return true;
	}
}

header('Content-Type: text/xml');
MantisBT_Sitemap::display_sitemap();
