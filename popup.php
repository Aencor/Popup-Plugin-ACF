<?php
/**
 * Plugin Name: Accelity Pop Up
 * Description: Add custom pop up to the site.
 * Version: 1.0
 * Author: Enrique Contreras
 * License: GPL2
 */

 add_action('acf/init', function() {
  if (function_exists('acf_add_options_page')) {
      // Crear la página de opciones si no existe
      acf_add_options_page([
          'page_title' => 'Pop-up Settings',
          'menu_title' => 'Pop-up Settings',
          'menu_slug'  => 'popup-settings',
          'capability' => 'edit_posts',
          'redirect'   => false,
      ]);
  }

  if (function_exists('acf_add_local_field_group')) {
      // Crear los campos
      acf_add_local_field_group([
          'key' => 'group_popup_settings',
          'title' => 'Configuración de Pop-ups',
          'fields' => [
              [
                  'key' => 'field_popup_pages',
                  'label' => 'Seleccionar páginas',
                  'name' => 'popup_pages',
                  'type' => 'relationship',
                  'instructions' => 'Selecciona las páginas donde se mostrará el Pop-up.',
                  'required' => 1,
                  'post_type' => ['page'],
                  'filters' => ['search', 'post_type'],
                  'return_format' => 'id',
              ],
              [
                  'key' => 'field_popup_title',
                  'label' => 'Título del Pop-up',
                  'name' => 'popup_title',
                  'type' => 'text',
                  'instructions' => 'Escribe el título que aparecerá en el Pop-up.',
                  'required' => 1,
              ],
              [
                  'key' => 'field_popup_content',
                  'label' => 'Contenido del Pop-up',
                  'name' => 'popup_content',
                  'type' => 'wysiwyg',
                  'instructions' => 'Escribe el contenido del Pop-up.',
                  'required' => 1,
              ],
              [
                  'key' => 'field_popup_button_link',
                  'label' => 'Botón del Pop-up',
                  'name' => 'popup_button_link',
                  'type' => 'link',
                  'instructions' => 'Agrega un enlace y texto del botón (opcional).',
                  'required' => 0,
                  'return_format' => 'array',
              ],
              [
                  'key' => 'field_popup_cookie_expiration',
                  'label' => 'Duración de la Cookie (días)',
                  'name' => 'popup_cookie_expiration',
                  'type' => 'number',
                  'instructions' => 'Escribe la cantidad de días para la expiración de la cookie.',
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
  // Obtener los campos del pop-up
  $popup_pages = get_field('popup_pages', 'option');
  $popup_title = get_field('popup_title', 'option');
  $popup_content = get_field('popup_content', 'option');
  $popup_button_link = get_field('popup_button_link', 'option');
  $cookie_expiration = get_field('popup_cookie_expiration', 'option');

  if ($popup_pages && $popup_title && $popup_content && is_page($popup_pages)) {
      wp_enqueue_style('acf-popup-style', plugin_dir_url(__FILE__) . 'assets/style.css');
      wp_enqueue_script('acf-popup-script', plugin_dir_url(__FILE__) . 'assets/script.js', ['jquery'], '1.0', true);

      // Preparar datos del botón y la cookie
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



