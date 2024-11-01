<?php
/*
Plugin Name:	Visibility for SiteOrigin
Description: 	Adjust the visibility of SiteOrigin widgets, rows and cells.
Version: 		1.0.2
Author: 		Echelon
License: 		GPL3
License URI: 	https://www.gnu.org/licenses/gpl-3.0.txt
*/

if (!class_exists('VisibilityForSiteOrigin')) {
	
	class VisibilityForSiteOrigin {
		
		public function __construct() {
			
			add_action( 'plugins_loaded', array($this, 'plugins_loaded'));
			
		}
		
		/*
		*
		*	Plugin Text Domain
		*
		*/
		
		public function plugin_text_domain() {
			return  'visibilityforsiteorigin';
		}
		
		/*
		*
		*	Plugins Loaded
		*
		*/
		
		public function plugins_loaded() {
			add_filter( 'siteorigin_panels_general_style_groups', array($this, 'general_style_groups') );
			add_filter( 'siteorigin_panels_general_style_fields', array($this, 'general_style_fields') );
			add_filter( 'siteorigin_panels_row_style_css', array($this, 'general_style_css'),10, 2 );
			add_filter( 'siteorigin_panels_cell_style_css', array( $this, 'general_style_css' ),10, 2 );
			add_filter( 'siteorigin_panels_widget_style_css', array($this, 'general_style_css'),10, 2 );
			add_filter( 'siteorigin_panels_data', array( $this, 'show_hide_conditions' ), 99, 2 );
		}
		
		/*
		*
		*	Add the Style Group
		*
		*/
		
		public function general_style_groups($groups) {
			
			$groups['echelonso_visibility_group'] = array(
				'name'     => __( 'Visibility', $this->plugin_text_domain() ),
				'priority' => 5020
			);
			
			return $groups;
		}
		
		/*
		*
		*	Add the Style Fields
		*
		*/
		
		public function general_style_fields($fields) {
			
			
			$fields['echelonso_opacity'] = array(
				'name'        => __('Opacity', $this->plugin_text_domain()),
				'type'        => 'text',
				'group'       => 'echelonso_visibility_group',
				'description' => __('A number between 0 and 1. E.g 0.75', $this->plugin_text_domain()),
				'priority'    => 10,
			);
			
			$fields['echelonso_display_none'] = array(
				'name'        => __('Display None', $this->plugin_text_domain()),
				'type'        => 'checkbox',
				'group'       => 'echelonso_visibility_group',
				'description' => __("Hide this item with CSS.", $this->plugin_text_domain()),
				'priority'    => 20,
			);
			
			$fields['echelonso_hide_for_logged_out'] = array(
				'name'        => __('Hide for Logged Out Users?', $this->plugin_text_domain()),
				'type'        => 'checkbox',
				'group'       => 'echelonso_visibility_group',
				'description' => __('Hide for users who are currently logged out.', $this->plugin_text_domain()),
				'priority'    => 30,
			);
			
			$fields['echelonso_hide_for_logged_in'] = array(
				'name'        => __('Hide for Logged In Users?', $this->plugin_text_domain()),
				'type'        => 'checkbox',
				'group'       => 'echelonso_visibility_group',
				'description' => __('Hide for users who are currently logged in.', $this->plugin_text_domain()),
				'priority'    => 40,
			);
			
			return $fields;
		}
		
		/*
		*
		*	Add the Style CSS
		*
		*/
		
		public function general_style_css( $css, $style ) {
			
			if ( isset($style['echelonso_opacity']) && is_numeric($style['echelonso_opacity']) && $style['echelonso_opacity'] > -1 ) {
				$css['opacity'] = (float)$style['echelonso_opacity'];
			}
			
			if ( !empty($style['echelonso_display_none']) ) {
				$css['display'] = 'none';
			}
			
			return $css;
		}
		
		/*
		*
		*	Show hide conditions
		*
		*/
		
		public function show_hide_conditions( $panels_data, $post_id ) {
			
			if ( !empty($panels_data) && !empty($post_id) ) {
				
				if ( !is_admin() && !is_customize_preview() ) {
					
					//
					// unset individual widgets
					//
					
					foreach ($panels_data['widgets'] as $k => $v) {
						// hide for logged out users
						if ( !empty($v['panels_info']['style']['echelonso_hide_for_logged_out']) ) {
							if (!is_user_logged_in()) {
								unset($panels_data['widgets'][$k]);
							}
						}
						// hide for logged in users
						if ( !empty($v['panels_info']['style']['echelonso_hide_for_logged_in']) ) {
							if (is_user_logged_in()) {
								unset($panels_data['widgets'][$k]);
							}
						}
					}
					
					//
					// unset a grid cell
					//
					
					foreach ($panels_data['grid_cells'] as $k => $v) {
						// hide for logged out users
						if ( !empty($v['style']['echelonso_hide_for_logged_out']) ) {
							if (!is_user_logged_in()) {
								foreach ($panels_data['widgets'] as $k2 => $v2) {
									if ( $v2['panels_info']['cell'] == $k ) {
										unset($panels_data['widgets'][$k2]);
									}
								}
							}
						}
						// hide for logged in users
						if ( !empty($v['style']['echelonso_hide_for_logged_in']) ) {
							if (is_user_logged_in()) {
								foreach ($panels_data['widgets'] as $k2 => $v2) {
									if ( $v2['panels_info']['cell'] == $k ) {
										unset($panels_data['widgets'][$k2]);
									}
								}
							}
						}
					}
					
					//
					// unset all widgets in a grid
					//
					
					foreach ($panels_data['grids'] as $k => $v) {
						// hide for logged out users
						if ( !empty($v['style']['echelonso_hide_for_logged_out']) ) {
							if (!is_user_logged_in()) {
								foreach ($panels_data['widgets'] as $k2 => $v2) {
									if ( $v2['panels_info']['grid'] == $k ) {
										unset($panels_data['widgets'][$k2]);
									}
								}
							}
						}
						// hide for logged in users
						if ( !empty($v['style']['echelonso_hide_for_logged_in']) ) {
							if (is_user_logged_in()) {
								foreach ($panels_data['widgets'] as $k2 => $v2) {
									if ( $v2['panels_info']['grid'] == $k ) {
										unset($panels_data['widgets'][$k2]);
									}
								}
							}
						}
					}
				}
			}
			
			return $panels_data;
		}
	}
	
	$class = new VisibilityForSiteOrigin();
}
