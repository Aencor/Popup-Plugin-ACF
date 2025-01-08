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
        // Create the Pop Up Settings page if it doesn't exist already
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
                    'key' => 'field_popups_repeater',
                    'label' => 'Pop-ups',
                    'name' => 'popups',
                    'type' => 'repeater',
                    'instructions' => 'Add and configure the pop-ups.',
                    'required' => 0,
                    'sub_fields' => [
                        [
                            'key' => 'field_popup_id',
                            'label' => 'Pop-up ID',
                            'name' => 'popup_id',
                            'type' => 'text',
                            'instructions' => 'Enter a unique ID for this pop-up.',
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_popup_style',
                            'label' => 'Style',
                            'name' => 'popup_style',
                            'type' => 'select',
                            'instructions' => 'Select the style for this pop-up.',
                            'required' => 1,
                            'choices' => [
                                'style_1' => 'Style 1',
                                'style_2' => 'Style 2',
                            ],
                            'default_value' => 'style_1',
                        ],
                        [
                            'key' => 'field_popup_all_pages',
                            'label' => 'All Pages',
                            'name' => 'popup_all_pages',
                            'type' => 'true_false',
                            'instructions' => 'Show this Pop-up on all pages.',
                            'default_value' => 0,
                            'ui' => 1,
                        ],
                        [
                            'key' => 'field_popup_pages',
                            'label' => 'Select Pop Up Location',
                            'name' => 'popup_pages',
                            'type' => 'relationship',
                            'instructions' => 'Select the pages where this pop-up will be shown.',
                            'post_type' => ['page', 'post'],
                            'filters' => ['search', 'post_type'],
                            'return_format' => 'id',
                            'conditional_logic' => [
                                [
                                    [
                                        'field' => 'field_popup_all_pages',
                                        'operator' => '!=',
                                        'value' => '1',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'field_popup_title',
                            'label' => 'Pop-up Title',
                            'name' => 'popup_title',
                            'type' => 'text',
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_popup_content',
                            'label' => 'Pop-up Content',
                            'name' => 'popup_content',
                            'type' => 'wysiwyg',
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_popup_button_link',
                            'label' => 'Pop-up Button',
                            'name' => 'popup_button_link',
                            'type' => 'link',
                            'instructions' => 'Add a button to the Pop-up (Optional).',
                            'return_format' => 'array',
                        ],
                        [
                            'key' => 'field_popup_cookie_expiration',
                            'label' => 'Cookie Duration (days)',
                            'name' => 'popup_cookie_expiration',
                            'type' => 'number',
                            'instructions' => 'Set how many days the cookie will last.',
                            'default_value' => 7,
                            'min' => 1,
                        ],
                    ],
                    'min' => 0,
                    'layout' => 'block',
                    'button_label' => 'Add Pop-up',
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
    $popups = get_field('popups', 'option');
  
    if ($popups) {
        $popup_data = [];
        foreach ($popups as $popup) {
            // Obtener el enlace del botón, si está presente
            $popup_button_link = $popup['popup_button_link'] ?? [];
            
            // Obtener los slugs de las páginas seleccionadas
            $page_slugs = [];
            if (!empty($popup['popup_pages'])) {
                $page_ids = $popup['popup_pages']; // IDs de las páginas seleccionadas
                // Obtener las páginas completas por ID
                $pages = get_posts([
                    'post_type' => array('page', 'post'),
                    'post__in' => $page_ids,  // Filtrar por los IDs
                    'posts_per_page' => -1,    // Obtener todas las páginas
                    'fields' => 'ids'          // Solo obtener los IDs
                ]);
                
                // Obtener el slug de cada página
                foreach ($pages as $page_id) {
                    $page = get_post($page_id);
                    if ($page) {
                        $page_slugs[] = $page->post_name;  // Guardar el slug
                    }
                }
            }

            // Agregar la información del pop-up al array
            $popup_data[] = [
                'id' => $popup['popup_id'],
                'title' => $popup['popup_title'],
                'style' => $popup['popup_style'],
                'content' => $popup['popup_content'],
                'buttonLabel' => $popup_button_link['title'] ?? '',
                'buttonUrl' => $popup_button_link['url'] ?? '',
                'buttonTarget' => $popup_button_link['target'] ?? '',
                'cookieExpiration' => $popup['popup_cookie_expiration'] ? intval($popup['popup_cookie_expiration']) : 7,
                'allPages' => !empty($popup['popup_all_pages']), // Devuelve `true` si está activado
                'pages' => $page_slugs, // Aquí pasamos los slugs de las páginas seleccionadas
            ];
        }

        // Cargar el script
        wp_register_script(
            'acf-popup-script',
            plugin_dir_url(__FILE__) . 'assets/script.js', // Ruta válida para el script
            ['jquery'], // Dependencias
            '1.0',
            true // Cargar en el footer
        );
        // Añadir el ID del post actual para usarlo en JS
        $post_id = get_the_ID(); // Obtén el ID del post o página actual
        wp_localize_script('acf-popup-script', 'popupData', [
            'post_id' => $post_id, // Aquí pasas el post ID
            'popup_data' => $popup_data // Y los datos del pop-up como antes
        ]);
        wp_enqueue_script('acf-popup-script');
        // Cargar el estilo
        wp_register_style(
            'acf-popup-style',
            plugin_dir_url(__FILE__) . 'assets/style.css', // Ruta válida para el archivo CSS
            [], // Dependencias (si las hay)
            '1.0' // Versión del archivo CSS
        );

        wp_enqueue_style('acf-popup-style');
    }
});
