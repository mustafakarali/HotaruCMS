<?php 
/**
 * Theme name: default
 * Template name: navigation.php
 * Template author: Shibuya246
 *
 * This file looks a bit ugly because whitespace between LI tags 
 * renders as spaces, so I had to squash all the lines together
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
 * @author    Shibuya246 <admin@hotarucms.org>
 * @copyright Copyright (c) 2010, Hotaru CMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://hotarucms.org/
 */
?>

<!-- Navigation Bar -->
<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container<?php if ($h->vars['theme_settings']['fullWidth']) echo '-fluid'; ?>">
            <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="brand" href="<?php echo SITEURL; ?>"><?php echo SITE_NAME; ?></a>
            <span class="btn-navbar">
            <?php	if ($h->currentUser->loggedIn) {
				if($h->isActive('avatar')) {
					$h->setAvatar($h->currentUser->id, 24, 'g', 'img-circle');
					echo  $h->linkAvatar();
				}
			} ?>
            </span>
            <div class="nav-collapse collapse">
                <ul class="nav">
                    <?php if ($h->currentUser->loggedIn) {
                            if($h->isActive('avatar')) {
                                    $h->setAvatar($h->currentUser->id, 22, 'g', 'img-circle');
                                    echo '<li class="visible-desktop">' . $h->linkAvatar() . '</li>';
                            }
                    } ?>
                    
                    <?php if ($h->pageName == $h->home) { $status = "class='active'"; } else { $status = ""; } ?>
                    <li <?php echo $status; ?>><a href="<?php echo SITEURL; ?>"><?php echo $h->lang("main_theme_navigation_home"); ?></a></li><?php $h->pluginHook('navigation'); ?>
                    <?php // RSS Link and icon if a type "base" plugin is active
                        if ($h->isActive('base')) { ?>
                            <li>
                                <a href="<?php echo $h->url(array('page'=>'rss')); ?>">RSS 
                                        <img src="<?php echo SITEURL; ?>content/themes/<?php echo THEME; ?>images/rss_16.png" width="16" height="16" alt="RSS" />
                                </a>
                            </li>
                        <?php } ?>
                </ul>
                <ul class="nav nav-pills pull-right">
               
                    <?php
                    if (!$h->isActive('signin')) { 
		
			if ($h->currentUser->loggedIn == true) { 
				
                                if ($h->currentUser->getPermission('can_access_admin') == 'yes') {
                                    adminNav($h);
                                } else {
                                    echo "<li><a href='" . $h->url(array(), 'admin') . "'>" . $h->lang("main_theme_navigation_admin") . "</a></li>";
                                }
                                
				if ($h->pageName == 'logout') { $status = "class='active'"; } else { $status = ""; }
				echo "<li " . $status . "><a href='" . $h->url(array('page'=>'admin_logout'), 'admin') . "'>" . $h->lang("main_theme_navigation_logout") . "</a></li>";
			} else { 
				if ($h->pageName == 'login') { $status = "class='active'"; } else { $status = ""; }
				echo "<li class='hidden-desktop' " . $status . "><a href='" . $h->url(array(), 'admin') . "'>" . $h->lang("main_theme_navigation_admin") . "</a></li>";
                                
                                ?>
                                
                                    <li class="dropdown visible-desktop">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#"><?php echo $h->lang("main_theme_navigation_login"); ?> <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li>
									<form id="signin" action="<?php echo SITEURL; ?>admin_index.php" method="post">
										<div style="margin:5px 15px 0 15px;">
                                                                                        <label for="username"><?php echo $h->lang("main_theme_login_username"); ?></label>
											<input id="username" name="username" value="" title="username" tabindex="1" type="text">
										</div>
										<div style="margin:5px 15px 0 15px;">
											<label for="password"><?php echo $h->lang("main_theme_login_password"); ?></label>
											<input id="password" name="password" value="" title="password" tabindex="2" type="password">
										</div>
										<div style="margin:0px 0 0 15px;">
											<input id="remember" style="float:left;margin-right:5px;" name="persistent" value="1" tabindex="3" type="checkbox">
											<label for="remember" style="float:left;font-size:10px;">Remember</label>
											<div style="clear:both;"></div>
										</div>
										<div style="margin:0;text-align:center;margin:0 auto;width:100%;">
											<input type="hidden" name="processlogin" value="1">
											<input type="hidden" name="return" value="">
											<input id="signin_submit" class="btn btn-primary" style="margin:0;width:90%;" value="<?php echo $h->lang('main_theme_login_form_submit'); ?>" tabindex="4" type="submit">
											<a id="forgot_password_link" class="btn" style="margin:8px 0 0 12px;width:74%;" href="/admin_index.php"><?php echo $h->lang("main_theme_login_forgot_password"); ?></a>
										</div>
                                                                                <input type='hidden' name='login_attempted' value='true'>
                                                                                <input type='hidden' name='page' value='admin_login'>
                                                                                <?php $h->csrf('set', 'navigation'); ?>
                                                                                <input type='hidden' name='csrf' value='<?php echo $h->csrfToken; ?>' />
									</form>
								</li>
							</ul><!--/.dropdown-menu -->
						</li>
                    
                                
                                <?php
                                
			}
                    } else {
                            $h->pluginHook('navigation_users'); // ensures login/logout/register are last.
                    }
                ?>
                </ul>
               
            </div>
        </div>
    </div>
</div>

<?php

// TODO 
// make a function that can be called in Hotaru by any themes
// make the li list dynamic based on plugins available
function adminNav($h)
{
 ?>
        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $h->lang("main_theme_navigation_admin"); ?> <b class="caret"></b></a>
            <ul class="dropdown-menu">
              <li><a href="<?php echo $h->url(array(), 'admin'); ?>">Home</a></li>
              <li><a href="<?php echo $h->url(array('page' => 'plugin_management'), 'admin'); ?>">Plugins</a></li>
              <li><a href="<?php echo $h->url(array('page' => 'maintenance'), 'admin'); ?>">Maintenance</a></li>
              <li class="divider"></li>
              <li class="nav-header">Plugins</li>
              <?php if ($h->isActive('user_manager')) { ?>
                <li><a href="<?php echo $h->url(array('page' => 'plugin_settings', 'plugin' => 'user_manager'), 'admin'); ?>">User Manager</a></li>
              <?php  } ?>
                <?php if ($h->isActive('post_manager')) { ?>
                <li><a href="<?php echo $h->url(array('page' => 'plugin_settings', 'plugin' => 'post_manager'), 'admin'); ?>">Post Manager</a></li>
              <?php  } ?>
              <?php if ($h->isActive('category_manager')) { ?>
                <li><a href="<?php echo $h->url(array('page' => 'plugin_settings', 'plugin' => 'category_manager'), 'admin'); ?>">Category Manager</a></li>
              <?php  } ?>
            </ul>
          </li>
<?php
}
?>

