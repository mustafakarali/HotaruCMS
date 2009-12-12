<?php
/**
 * The engine, powers everything :-)
 *
 * PHP version 5
 *
 * LICENSE: Hotaru CMS is free software: you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 3 of 
 * the License, or (at your option) any later version. 
 *
 * Hotaru CMS is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
 * FITNESS FOR A PARTICULAR PURPOSE. 
 *
 * You should have received a copy of the GNU General Public License along 
 * with Hotaru CMS. If not, see http://www.gnu.org/licenses/.
 * 
 * @category  Content Management System
 * @package   HotaruCMS
 * @author    Nick Ramsay <admin@hotarucms.org>
 * @copyright Copyright (c) 2009, Hotaru CMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://www.hotarucms.org/
 */
class Hotaru
{
    protected $version              = "1.0";    // Hotaru CMS version
    protected $db;                              // database object
    protected $cage;                            // Inspekt object
    protected $currentUser;                     // UserBase object
    protected $isDebug              = false;    // show db queries and page loading time
    protected $isAdmin              = false;    // flag to tell if we are in Admin or not
    protected $title                = '';       // the page title
    protected $lang                 = array();  // stores language file content
    protected $folder               = '';       // plugin folder name
    protected $pluginSettings       = array();  // contains all plugin settings
    protected $pluginBasics         = array();  // contains basic plugin details
    protected $sidebars             = true;    // enable or disable the sidebars
    protected $token                = '';       // token for CSRF
    
    public $message                 = '';       // message to display
    public $messageType             = 'green';  // green or red, color of message box
    public $messages                = array();  // for multiple messages
    
    public $vars            = array();  // multi-purpose
    
    /**
     * CONSTRUCTOR - Initialize
     */
    public function __construct()
    {
        // initialize Hotaru
        if (!isset($start)) { 
            require_once(LIBS . 'Initialize.php');
            $init = new Initialize();
            $this->db       = $init->db;
            $this->cage     = $init->cage;
            $this->isDebug  = $init->isDebug;
            $this->currentUser = new UserAuth();
        }
    }
    
    
/* *************************************************************
 *
 *  HOTARU FUNCTIONS
 *
 * *********************************************************** */


    /**
     * START - the top of "Hotaru", i.e. the page-building process
     */
    public function start($entrance = 'main')
    {
        // include "main" language pack
        $lang = new Language();
        $this->lang = $lang->includeLanguagePack($this->lang, 'main');

        switch ($entrance) {
            case 'admin':
                $this->isAdmin = true;
                $this->lang = $lang->includeLanguagePack($this->lang, 'admin');
                require_once(LIBS . 'AdminAuth.php');       // include Admin class
                $admin = new AdminAuth();                   // new Admin object
                $page = $admin->adminInit($this);       // initialize Admin & get desired page
                $this->checkCookie();                   // check cookie reads user details
                $this->checkAccess();                   // site closed if no access permitted
                $this->checkCssJs();                    // check if we need to merge css/js
                $this->adminPages($page);               // Direct to desired Admin page
                break;
            default:
                $this->checkCookie();                   // log in user if cookie
                $this->checkAccess();                   // site closed if no access permitted
                $this->checkCssJs();                    // check if we need to merge css/js
                $this->pluginHook('start');             // used to do stuff before output
                $this->displayTemplate('index');        // displays the index page
        }
        
        exit;
    }
    
    
/* *************************************************************
 *
 *  ACCESS MODIFIERS
 *
 * *********************************************************** */
 
 
    /**
     * Access modifier to set protected properties
     */
    public function __set($var, $val)
    {
        $this->$var = $val;  
    }
    
    
    /**
     * Access modifier to get protected properties
     */
    public function __get($var)
    {
        return $this->$var;
    }


/* *************************************************************
 *
 *  PAGE HANDLING FUNCTIONS
 *
 * *********************************************************** */
 
 
    /**
     * Includes a template to display
     *
     * @param string $page page name
     * @param string $plugin optional plugin name
     * @param bool $include_once true or false
     */
    public function displayTemplate($page = '', $plugin = '', $include_once = true)
    {
        $pageHandling = new PageHandling();
        $pageHandling->displayTemplate($this, $page, $plugin, $include_once);
    }
    
    
    /**
     * Gets the current page name
     */
    public function getPageName()
    {
        $pageHandling = new PageHandling();
        return $pageHandling->getPageName($this->cage);
    }
    
    
    /**
     * Generate either default or friendly urls
     *
     * @param array $parameters an array of pairs, e.g. 'page' => 'about' 
     * @param string $head either 'index' or 'admin'
     * @return string
     */
    public function url($parameters = array(), $head = 'index')
    {
        $pageHandling = new PageHandling();
        return $pageHandling->url($this, $parameters, $head);
    }
    

 /* *************************************************************
 *
 *  USERAUTH FUNCTIONS
 *
 * *********************************************************** */
 
 
    /**
     * check cookie and log in
     *
     * @return bool
     */
    public function checkCookie()
    {
        $this->currentUser->checkCookie($this);
    }

    /* With the exception of above, user functions need to be called 
       directly in order to retain the user object being used. E.g.
       
       $user = new UserAuth();
       $user->getUserBasic($hotaru);
       $user->updateUserBasic($hotaru);
    */


 /* *************************************************************
 *
 *  USERINFO FUNCTIONS
 *
 * *********************************************************** */
    
    
     /**
     * Checks if the user has an 'admin' role
     *
     * @return bool
     */
    public function isAdmin($username)
    {
        require_once(LIBS . 'UserInfo.php');
        $userInfo = new UserInfo();
        return $userInfo->isAdmin($this->db, $username);
    }
    
    
    /**
     * Check if a user exists
     *
     * @param int $userid 
     * @param string $username
     * @return int
     *
     * Notes: Returns 'no' if a user doesn't exist, else field under which found
     */
    public function userExists($id = 0, $username = '', $email = '')
    {
        require_once(LIBS . 'UserInfo.php');
        $userInfo = new UserInfo();
        return $userInfo->userExists($this->db, $id, $username, $email);
    }
    
    
 /* *************************************************************
 *
 *  PLUGIN FUNCTIONS
 *
 * *********************************************************** */
 
 
    /**
     * Look for and run actions at a given plugin hook
     *
     * @param string $hook name of the plugin hook
     * @param bool $perform false to check existence, true to actually run
     * @param string $folder name of plugin folder
     * @param array $parameters mixed values passed from plugin hook
     * @return array | bool
     */
    public function pluginHook($hook = '', $folder = '', $parameters = array(), $exclude = array())
    {
        $plugins = new PluginFunctions();
        $plugins->pluginHook($this, $hook, $folder, $parameters, $exclude);
    }
    
    
    /**
     * Get number of active plugins
     *
     * @return int|false
     */
    public function numActivePlugins()
    {
        $plugins = new PluginFunctions();
        return $plugins->numActivePlugins($this->db);
    }
    
    
    /**
     * Get version number of plugin if active
     *
     * @param string $folder plugin folder name
     * @return string|false
     */
    public function isActive($folder = '')
    {
        $plugins = new PluginFunctions();
        return $plugins->isActive($this, $folder);
    }
    
    
    /**
     * Store basic plugin info in memory. We use the hotaru 
     * object because it's persistent during a page load
     */
    public function getPluginBasics()
    {
        $plugins = new PluginFunctions();
        $this->pluginBasics = $plugins->getPluginBasics($this->db);
    }
    
    
    /**
     * Determines if a plugin is enabled or not
     *
     * @param string $folder plugin folder name
     * @return string
     */
    public function getPluginStatus($folder = '')
    {
        $plugins = new PluginFunctions();
        return $plugins->getPluginStatus($this, $folder);
    }
    
 
 /* *************************************************************
 *
 *  INCLUDE CSS & JAVASCRIPT FUNCTIONS
 *
 * *********************************************************** */
 
 
    /**
     * Check if we need to combine CSS and JavaScript files
     */
     public function checkCssJs()
     {
        if (!$this->cage->get->keyExists('combine')) { return false; }
 
        $type = $this->cage->get->testAlpha('type');
        $version = $this->cage->get->testInt('version');
        $this->combineIncludes($type, $version, $admin);
     }
     
     
    /**
     * Combine Included CSS & JSS files
     *
     * @param string $type either 'css' or 'js'
     * @param int version number or echo output to cache file
     * @param bool $admin
     * @link http://www.ejeliot.com/blog/72 Based on work by Ed Eliot
     */
     public function combineIncludes($type = 'css', $version = 0, $admin = false)
     {
        $includes = new IncludeCssJs();         // test and merge css and javascript files
        $includes->combineIncludes($this, $type, $version, $admin);
     }
     
     
     /**
     * Included combined files
     *
     * @param int $version_js 
     * @param int $version_css 
     * @param bool $admin
     */
     public function includeCombined($version_js = 0, $version_css = 0, $admin = false)
     {
        $includes = new IncludeCssJs();         // test and merge css and javascript files
        $includes->includeCombined($version_js, $version_css, $admin);
     }
     
     
 /* *************************************************************
 *
 *  MESSAGE FUNCTIONS (success/error messages)
 *
 * *********************************************************** */
 
 
    /**
     * Display a SINGLE success or failure message
     *
     * @param string $msg
     * @param string $msg_type ('green' or 'red')
     */
    public function showMessage($msg = '', $msg_type = 'green')
    {
        require_once(LIBS . 'Messages.php');
        $messages = new Messages();
        $messages->showMessage($this, $msg, $msg_type);
    }
    
    
    /**
     * Displays ALL success or failure messages
     */
    public function showMessages()
    {
        require_once(LIBS . 'Messages.php');
        $messages = new Messages();
        $messages->showMessages($this);
    }
    
    
 /* *************************************************************
 *
 *  INCLUDE ANNOUNCEMENT FUNCTIONS
 *
 * *********************************************************** */
 
 
    /**
     * Displays an announcement at the top of the screen
     */
    public function checkAnnouncements() 
    {
        require_once(LIBS . 'Announcements.php');
        $announce = new Announcements();
        if ($this->isAdmin) {
            return $announce->checkAdminAnnouncements($this);
        } else {
            return $announce->checkAnnouncements($this);
        }
    }
    
    
 /* *************************************************************
 *
 *  INCLUDE DEBUG FUNCTIONS
 *
 * *********************************************************** */
 
 
    /**
     * Shows number of database queries and the time it takes for a page to load
     */
    public function showQueriesAndTime()
    {
        require_once(LIBS . 'Debug.php');
        $debug = new Debug();
        $debug->showQueriesAndTime($this);
    }
    
    
     
    
 /* *************************************************************
 *
 *  RSS FEED FUNCTIONS
 *
 * *********************************************************** */
 
 
    /**
     * Includes the SimplePie RSS file and sets the cache
     *
     * @param string $feed
     * @param bool $cache
     * @param int $cache_duration
     *
     * @return object|false $sp
     */
    public function newSimplePie($feed='', $cache=RSS_CACHE_ON, $cache_duration=RSS_CACHE_DURATION)
    {
        require_once(LIBS . 'Feeds.php');
        $feeds = new Feeds();
        return $feeds->newSimplePie($feed, $cache, $cache_duration);
    }
    
    
     /**
     * Display Hotaru forums feed on Admin front page
     *
     * @param int $max_items
     * @param int $items_with_content
     * @param int $max_chars
     */
    public function adminNews($max_items = 10, $items_with_content = 3, $max_chars = 300)
    {
        require_once(LIBS . 'Feeds.php');
        $feeds = new Feeds();
        $feeds->adminNews($this->lang, $max_items, $items_with_content, $max_chars);
    }
    
    
 /* *************************************************************
 *
 *  ADMIN FUNCTIONS
 *
 * *********************************************************** */
 
 
     /**
     * Admin Pages
     */
    public function adminPages($page = 'admin_login')
    {
        require_once(LIBS . 'AdminPages.php');
        $admin = new AdminPages();
        $admin->pages($this, $page);
    }
    
    
     /**
     * Admin login/logout
     *
     * @param string $action
     */
    public function adminLoginLogout($action = 'logout')
    {
        require_once(LIBS . 'AdminAuth.php');
        $admin = new AdminAuth();
        return ($action == 'login') ? $admin->adminLogin($this) : $admin->adminLogout($this);
    }
    
    
     /**
     * Admin login form
     */
    public function adminLoginForm()
    {
        require_once(LIBS . 'AdminAuth.php');
        $admin = new AdminAuth();
        $admin->adminLoginForm($this);
    }
    
    
 /* *************************************************************
 *
 *  MAINTENANCE FUNCTIONS
 *
 * *********************************************************** */
 
 
    /**
     * Check if site is open or closed. Exit if closed
     *
     * @param object $hotaru
     */
    public function checkAccess()
    {
        if (SITE_OPEN == 'true') { return true; }   // site is open, go back and continue
        
        // site closed, but user has admin access so go back and continue as normal
        if ($this->currentUser->getPermission('can_access_admin') == 'yes') { return true; }
        
        require_once(LIBS . 'Maintenance.php');
        $maintenance = new Maintenance();
        return $maintenance->siteClosed($this->lang); // displays "Site Closed for Maintenance"
    }
    
    
    /**
     * Open or close the site for maintenance
     *
     * @param string $switch - 'open' or 'close'
     */
    public function openCloseSite($switch = 'open')
    {
        require_once(LIBS . 'Maintenance.php');
        $maintenance = new Maintenance();
        $maintenance->openCloseSite($this, $switch);
    }
    
    
    /**
     * Optimize all database tables
     */
    public function optimizeTables()
    {
        require_once(LIBS . 'Maintenance.php');
        $maintenance = new Maintenance();
        $maintenance->optimizeTables($this);
    }
    
    
    /**
     * Empty plugin database table
     *
     * @param string $table_name - table to empty
     * @param string $msg - show "emptied" message or not
     */
    public function emptyTable($table_name, $msg = true)
    {
        require_once(LIBS . 'Maintenance.php');
        $maintenance = new Maintenance();
        $maintenance->emptyTable($this, $table_name, $msg);
    }
    
    
    /**
     * Delete plugin database table
     *
     * @param string $table_name - table to drop
     * @param string $msg - show "dropped" message or not
     */
    public function dropTable($table_name, $msg = true)
    {
        require_once(LIBS . 'Maintenance.php');
        $maintenance = new Maintenance();
        $maintenance->dropTable($this, $table_name, $msg);
    }
    
    
    /**
     * Remove plugin settings
     *
     * @param string $plugin_name - settings to remove
     * @param string $msg - show "removed" message or not
     */
    public function removeSettings($plugin_name, $msg = true)
    {
        require_once(LIBS . 'Maintenance.php');
        $maintenance = new Maintenance();
        $maintenance->removeSettings($this, $plugin_name, $msg);
    }
    
    
    /**
     * Delete all files in the specified directory except placeholder.txt
     *
     * @param string $dir - path to the cache folder
     * @return bool
     */    
    public function deleteFiles($dir)
    {
        require_once(LIBS . 'Maintenance.php');
        $maintenance = new Maintenance();
        $maintenance->deleteFiles($dir);
    }
    
    
    /**
     * Calls the delete_files function, then displays a message.
     *
     * @param string $folder - path to the cache folder
     * @param string $msg - show "cleared" message or not
     */
    public function clearCache($folder, $msg = true)
    {
        require_once(LIBS . 'Maintenance.php');
        $maintenance = new Maintenance();
        $maintenance->clearCache($this, $folder, $msg);
    }
    
    
 /* *************************************************************
 *
 *  CACHING FUNCTIONS (Note: "clearCache" is in Maintenance above)
 *
 * *********************************************************** */


    /**
     * Hotaru CMS Smart Caching
     *
     * This function does one query on the database to get the last updated time for a 
     * specified table. If that time is more recent than the $timeout length (e.g. 10 minutes),
     * the database will be used. If there hasn't been an update, any cached results from the 
     * last 10 minutes will be used.
     *
     * @param string $switch either "on", "off" or "html"
     * @param string $table DB table name
     * @param int $timeout time before DB cache expires
     * @param string $html output as HTML
     * @param string $label optional label to append to filename
     * @return bool
     */
    public function smartCache($switch = 'off', $table = '', $timeout = 0, $html = '', $label = '')
    {
        require_once(LIBS . 'Caching.php');
        $caching = new Caching();
        $caching->smartCache($hotaru, $switch, $table, $timeout, $html, $label);
    }
    
    
 /* *************************************************************
 *
 *  BLOCKED FUNCTIONS (i.e. Admin's Blocked list)
 *
 * *********************************************************** */
 
     /**
     * Check if a value is blocked from registration and post submission)
     *
     * @param string $type - i.e. ip, url, email, user
     * @param string $value
     * @param bool $like - used for LIKE sql if true
     * @return bool
     */
    public function isBlocked($type = '', $value = '', $operator = '=')
    {
        require_once(LIBS . 'Blocked.php');
        $blocked = new Blocked();
        return $blocked->isBlocked($this->db, $type, $value, $operator);
    }
}
?>