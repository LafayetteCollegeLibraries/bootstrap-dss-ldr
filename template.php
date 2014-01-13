<?php

/**
 * @file template.php
 * @author griffinj@lafayette.edu
 * This file contains the primary theme hooks found within any given Drupal 7.x theme
 * 
 * @todo Implement some Drupal theming hooks
 */

  // Includes functions to create Islandora Solr blocks.
require_once dirname(__FILE__) . '/includes/blocks.inc';
require_once dirname(__FILE__) . '/includes/forms.inc';
require_once dirname(__FILE__) . '/includes/menus.inc';

/**
 * Preprocess variables for page.tpl.php
 *
 * @see page.tpl.php
 */

function bootstrap_dss_ldr_preprocess_page(&$variables) {

  // Add information about the number of sidebars.
  if (!empty($variables['page']['sidebar_first']) && !empty($variables['page']['sidebar_second'])) {
    $variables['columns'] = 3;

  }
  elseif (!empty($variables['page']['sidebar_first'])) {
    $variables['columns'] = 2;
  }
  elseif (!empty($variables['page']['sidebar_second'])) {
    $variables['columns'] = 2;
  }
  else {
    $variables['columns'] = 1;
  }

  // Primary nav
  $variables['primary_nav'] = FALSE;
  if ($variables['main_menu']) {
    // Build links
    $variables['primary_nav'] = menu_tree(variable_get('menu_main_links_source', 'main-menu'));
    // Provide default theme wrapper function
    $variables['primary_nav']['#theme_wrappers'] = array('menu_tree__primary');
  }

  // Secondary nav
  $variables['secondary_nav'] = FALSE;
  if ($variables['secondary_menu']) {
    // Build links
    $variables['secondary_nav'] = menu_tree(variable_get('menu_secondary_links_source', 'user-menu'));
    // Provide default theme wrapper function
    $variables['secondary_nav']['#theme_wrappers'] = array('menu_tree__secondary');
  }

  // The "Contact Us" link
  $variables['contact_anchor'] = l(t('Contact Us'), '', array('attributes' => array('data-toggle' => 'lafayette-dss-modal',
										    'data-target' => '#contact',
										    'data-anchor-align' => 'false'),
							      'fragment' => ' ',
							      'external' => TRUE));

  $browser = browscap_get_browser();
  $is_smartphone_browser = $browser['ismobiledevice'] && preg_match('/iPhone|(?:Android.*?Mobile)|(?:Windows Phone)/', $browser['useragent']);

  // Different images must be passed based upon the browser type

  // Shouldn't be parsing the string itself; refactor
  if($is_smartphone_browser) {
    //if(TRUE) {

    $variables['dss_logo_image'] = theme_image(array('path' => drupal_get_path('theme', 'bootstrap_dss_ldr') . '/files/dss_logo_mobile.png',
						     'alt' => t('digital scholarship services logo'),
						     'attributes' => array()));
  } else {

    // Work-around for the logo image
    $variables['dss_logo_image'] = theme_image(array('path' => drupal_get_path('theme', 'bootstrap_dss_ldr') . '/files/dss_logo.png',
						     'alt' => t('digital scholarship services logo'),
						     'attributes' => array()));
  }  

  // The "Log In" link
  $variables['auth_anchor'] = '<a data-toggle="lafayette-dss-modal" data-target="#auth-modal" data-width-offset="0px" data-height-offset="30px"><div class="auth-icon navbar-icon"><img src="/sites/all/themes/bootstrap_dss_digital/files/UserIcon.png" /><span>Log In</span></div></a>';

  // The "Log Out" link
  $variables['logout_anchor'] = l(t('Log Out'), 'user/logout');

  // The "Share" link
  $variables['share_anchor'] = '<a data-toggle="lafayette-dss-modal" data-target="#share-modal" data-width-offset="10px" data-height-offset="28px"><div class="share-icon navbar-icon"><img src="/sites/all/themes/bootstrap_dss_digital/files/ShareIcon.png" /><span>Share</span></div></a>';

  // Render thumbnails for authenticated users
  $variables['user_picture'] = '<span class="button-auth-icon"></span>';

  if(user_is_logged_in()) {

    // For the user thumbnail
    global $user;
    $user_view = user_view($user);
    $variables['user_picture'] = drupal_render($user_view['user_picture']);
  }

  // A search button must be passed if this is being viewed with a mobile browser

  $search_icon = theme_image(array('path' => drupal_get_path('theme', 'bootstrap_dss_digital') . '/files/SearchIcon.png',
				   'alt' => t('search the site'),
				   'attributes' => array()));

  $simple_search_mobile = '<a data-toggle="lafayette-dss-modal" data-target="#advanced-search-modal" data-width-offset="-286px" data-height-offset="28px">
<div class="simple-search-icon">' . $search_icon . '<span>Search</span></div></a>' . render($variables['page']['simple_search']);
  unset($variables['page']['simple_search']);
  $variables['search_container'] = '<div class="modal-container container"><div id="simple-search-control-container" class="modal-control-container container">' . $simple_search_mobile . '</div></div>';

  // Refactor
  $auth_container = '
     <div class="auth-container modal-container container">
       <div id="auth-control-container" class="modal-control-container container">';

  if(!empty($variables['page']['auth'])) {

    $auth_container .= $variables['auth_anchor'];
  } else {
    
    $auth_container .= '
      <div class="auth-icon">' . $variables['user_picture'] . '</div>
      <div class="auth-link">' . $variables['logout_anchor'] . '</div>';
  }

  $auth_container .= '
       </div><!-- /#auth-control-container -->
     </div><!-- /.auth-container -->';

  $variables['auth_container'] = $auth_container;

  $share_container = '
     <div class="share-container modal-container container">
       <div id="share-control-container" class="modal-control-container container">

         ' . $variables['share_anchor'] . '
       </div><!-- /#share-control-container -->
     </div><!-- /.share-container -->';

  $variables['share_container'] = $share_container;

  $menu_toggle_image = theme_image(array('path' => drupal_get_path('theme', 'bootstrap_dss_digital') . '/files/MenuIcon.png',
					 'alt' => t('mobile menu'),
					 'attributes' => array()));

  $variables['menu_toggle_image'] = $menu_toggle_image;

  $menu_toggle_container = '

       <div id="menu-toggle-control-container" class="modal-control-container container">
<div class="navbar-collapse-toggle">
<!-- .btn-navbar is used as the toggle for collapsed navbar content -->
  <div data-toggle="collapse" data-target=".nav-collapse">
    <div id="menu-toggle-icon" class="navbar-icon btn-navbar">' . $menu_toggle_image . '<span id="btn-navbar-caption" class="">Menu</span></div>
  </div>
</div><!-- /.navbar-collapse-toggle -->
</div>';

  $variables['menu_toggle_container'] = $menu_toggle_container;

  // Ensure that "home" always exists for the breadcrumbs
  if(empty($variables['breadcrumb'])) {

    $variables['breadcrumb'] = '<ul class="breadcrumb"><li>' . l(t('Home'), $variables['front_page']) . '</li></ul>';
  }
}

/**
 * Implements template_preprocess_hybridauth_widget
 * @griffinj
 *
 */
function bootstrap_dss_ldr_preprocess_hybridauth_widget(&$vars) {

  // Refactor
  $i = 0;
  foreach (hybridauth_get_enabled_providers() as $provider_id => $provider_name) {

    //$vars['providers'][$i] .= preg_replace('/(<\/span>)/', "</span><span>&nbsp;$provider_name</span>", $vars['providers'][$i]);
    //if(preg_match('/<span class="sharethis-anchor-wrapper"><\/span>/', $vars['providers'][$i])) {

    //$vars['providers'][$i] = preg_replace('/(<\/span>)/', "</span><span>&nbsp;$provider_name</span>", $vars['providers'][$i]);
    $i++;
      //}
  }
}

/**
 * Implements template_preprocess_html
 *
 */
function bootstrap_dss_ldr_preprocess_html(&$variables) {

  drupal_add_library('system', 'effects.drop');
  drupal_add_library('system', 'effects.slide');
}

/**
 * Template preprocess function for hybridauth_widget.
 */
/*
function template_preprocess_hybridauth_widget(&$vars, $hook) {

}
*/

function bootstrap_dss_ldr_theme_registry_alter(&$registry) {

  $registry['hybridauth_widget']['file'] = 'template';
}

/**
 * Implements hook_theme().
 */
/*
function hybridauth_theme($existing, $type, $theme, $path) {
  return array(
    'hybridauth_admin_settings_providers_table' => array(
      'render element' => 'form',
      'file' => 'hybridauth.admin.inc',
    ),
    'hybridauth_widget' => array(
      'render element' => 'element',
      'template' => 'templates/hybridauth_widget',
      'file' => 'hybridauth.theme.inc',
    ),
}
*/
