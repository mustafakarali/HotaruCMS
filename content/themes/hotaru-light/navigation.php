<?php 
/**
 * Theme name: hotaru-light
 * Template name: navigation.php
 * Template author: carlo75
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
?>

<!-- Navigation Bar -->
<ul id="navigation">
    <?php $hotaru->plugins->pluginHook('navigation_first'); ?>
    
    <?php if ($hotaru->title == 'top') { $status = "id='navigation_active'"; } else { $status = ""; } ?>
    <li><a <?php echo $status; ?> href="<?php echo BASEURL; ?>"><?php echo $hotaru->lang["main_theme_navigation_home"]; ?></a></li>
    <?php $hotaru->plugins->pluginHook('navigation'); ?>
    <?php 
        if (!$hotaru->plugins->isActive('users')) { 

            if ($hotaru->current_user->loggedIn == true) { 
            
                if ($hotaru->title == 'admin') { $status = "id='navigation_active'"; } else { $status = ""; }
                echo "<li><a " . $status . " href='" . $hotaru->url(array(), 'admin') . "'>" . $hotaru->lang["main_theme_navigation_admin"] . "</a></li>"; 
                
                if ($hotaru->title == 'logout') { $status = "id='navigation_active'"; } else { $status = ""; }
                echo "<li><a " . $status . " href='" . $hotaru->url(array('page'=>'admin_logout'), 'admin') . "'>" . $hotaru->lang["main_theme_navigation_logout"] . "</a></li>";
            } else { 
                if ($hotaru->title == 'login') { $status = "id='navigation_active'"; } else { $status = ""; }
                echo "<li><a " . $status . " href='" . $hotaru->url(array(), 'admin') . "'>" . $hotaru->lang["main_theme_navigation_login"] . "</a></li>"; 
            }
        } else {
            $hotaru->plugins->pluginHook('navigation_users', true, 'users'); // ensures login/logout/register are last.
        }
    ?>
    
    <?php     // RSS Link and icon if Submit plugin is active
        if ($hotaru->plugins->getPluginStatus('submit') == 'active') { ?>
        <li>
        <div id="rss"><a href="<?php echo $hotaru->url(array('page'=>'rss')); ?>">RSS 
            <img src="<?php echo BASEURL; ?>content/themes/<?php echo THEME; ?>images/rss_16.png">
        </a></div>
        </li>
    <?php } ?>
            
</ul>

<?php $hotaru->plugins->pluginHook('navigation_last'); ?>