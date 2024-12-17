<?php
/**
 * Plugin Name: Accelity Pop Up
 * Description: Add custom pop up to the site.
 * Version: 1.1
 * Author: Enrique Contreras
 * License: GPL2
 */

 add_action('acf/init', function() {
  if (function_exists('acf_add_options_page')) {
      // Create the Pop Up Settings page if it doesn't exists already
      acf_add_options_page([
          'page_title' => 'Pop-up Settings',
          'menu_title' => 'Pop-up Settings',
          'menu_slug'  => 'popup-settings',
          'capability' => 'edit_posts',
          'redirect'   => false,
      ]);
  }

  if (function_exists('acf_add_local_field_group')) {
      // Create the Fields
      acf_add_local_field_group([
          'key' => 'group_popup_settings',
          'title' => 'Pop-up Settings',
          'fields' => [
                [
                    'key' => 'field_popup_all_pages',
                    'label' => 'All Pages',
                    'name' => 'popup_all_pages',
                    'type' => 'true_false',
                    'instructions' => 'Check this box to show the Pop-up on all pages.',
                    'required' => 0,
                    'default_value' => 0,
                    'ui' => 1,
                ],
              [
                  'key' => 'field_popup_pages',
                  'label' => 'Select Pop Up Location',
                  'name' => 'popup_pages',
                  'type' => 'relationship',
                  'instructions' => 'Select the pages where the pop up will be shown.',
                  'required' => 1,
                  'post_type' => ['page'],
                  'filters' => ['search', 'post_type'],
                  'return_format' => 'id',
                  'conditional_logic' => [
                      [
                          [
                              'field' => 'field_popup_all_pages', // Clave del campo "All Pages"
                              'operator' => '!=',
                              'value' => '1', // Se muestra si "All Pages" NO está marcado
                          ],
                      ],
                  ],
              ],
              [
                  'key' => 'field_popup_title',
                  'label' => 'Pop-up Title',
                  'name' => 'popup_title',
                  'type' => 'text',
                  'instructions' => 'Write the title of the Pop-up that will be shown.',
                  'required' => 1,
              ],
              [
                  'key' => 'field_popup_content',
                  'label' => 'Pop-up Content',
                  'name' => 'popup_content',
                  'type' => 'wysiwyg',
                  'instructions' => 'Insert here the Pop-up content.',
                  'required' => 1,
              ],
              [
                  'key' => 'field_popup_button_link',
                  'label' => 'Pop-up Button',
                  'name' => 'popup_button_link',
                  'type' => 'link',
                  'instructions' => 'Add a button to the Pop-up (Optional).',
                  'required' => 0,
                  'return_format' => 'array',
              ],
              [
                  'key' => 'field_popup_cookie_expiration',
                  'label' => 'Cookie Duration (days)',
                  'name' => 'popup_cookie_expiration',
                  'type' => 'number',
                  'instructions' => 'Set how many days do the cookie will last.',
                  'default_value' => 7,
                  'required' => 0,
                  'min' => 1,
              ],
          ],
          'location' => [
              [
                  [
                      'param' => 'options_page',
                      'operator' => '==',
                      'value' => 'popup-settings',
                  ],
              ],
          ],
      ]);
  }
});

add_action('wp_enqueue_scripts', function() {
  // Get the Pop up data
  $popup_all_pages = get_field('popup_all_pages', 'option');
  $popup_pages = get_field('popup_pages', 'option');
  $popup_title = get_field('popup_title', 'option');
  $popup_content = get_field('popup_content', 'option');
  $popup_button_link = get_field('popup_button_link', 'option');
  $cookie_expiration = get_field('popup_cookie_expiration', 'option');
  
  $show_popup = $popup_all_pages || ($popup_pages && is_page($popup_pages));

  if ($show_popup && $popup_title && $popup_content) {
      wp_enqueue_style('acf-popup-style', plugin_dir_url(__FILE__) . 'assets/style.css');
      wp_enqueue_script('acf-popup-script', plugin_dir_url(__FILE__) . 'assets/script.js', ['jquery'], '1.0', true);

      // Prepare button and cookie options
      $button_label = $popup_button_link['title'] ?? '';
      $button_url = $popup_button_link['url'] ?? '';
      $button_target = $popup_button_link['target'] ?? '';

      wp_localize_script('acf-popup-script', 'popupData', [
          'title' => $popup_title,
          'content' => $popup_content,
          'buttonLabel' => $button_label,
          'buttonUrl' => $button_url,
          'buttonTarget' => $button_target,
          'cookieExpiration' => $cookie_expiration ? intval($cookie_expiration) : 7, // Default 7 días
      ]);
  }
});



