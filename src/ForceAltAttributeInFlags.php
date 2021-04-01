<?php

namespace WPML\Core;

class ForceAltAttributeInFlags {
	public function init_hooks() {
		add_filter( 'wpml_ls_html', [ $this, 'update_html' ], 10, 3 );
	}

	/**
	 * @param string                          $html
	 * @param <string, string|<string,mixed>> $model
	 * @param \WPML_LS_Slot                   $slot
	 *
	 * @return false|string
	 */
	public function update_html( $html, $model, $slot ) {
		$display_flags  = $slot->get( 'display_flags' );
		$display_native = $slot->get( 'display_names_in_native_lang' );
		$display_name   = $slot->get( 'display_names_in_current_lang' );
		$group          = $slot->get( 'slot_group' );

		if ( $display_flags && ( $display_native || $display_name ) ) {
			$dom = new \DOMDocument();
			$dom->loadHTML( '<div id="wpml-fix-alt">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
			$xpath = new \DOMXPath( $dom );

			$container = $dom->getElementById( 'wpml-fix-alt' );

			$nodes = $xpath->query( '//img[ @class="wpml-ls-flag" ]' );

			/** @var \DOMNode $node */
			foreach ( $nodes as $node ) {
				$altTexts = [];

				if ( $group === 'menus' ) {
					if ( $display_native ) {
						$altTexts[] = $this->getLanguageName( $xpath, 'wpml-ls-native' );
					}
					if ( $display_name ) {
						$altTexts[] = $this->getLanguageName( $xpath, 'wpml-ls-display' );
					}
				} else {

					if ( $display_native ) {
						$altTexts[] = $this->getLanguageNameFromSiblings( $xpath, $node, 'wpml-ls-native' );
					}
					if ( $display_name ) {
						$altTexts[] = $this->getLanguageNameFromSiblings( $xpath, $node, 'wpml-ls-display' );
					}
				}

				$alt_value = trim( join( '', array_filter( $altTexts ) ) );
				if ( $alt_value ) {
					$node->setAttribute( 'alt', $alt_value );
				}
			}

			$htmlElements = [];

			foreach ( $container->childNodes as $childNode ) {
				$htmlElements[] = $dom->saveHTML( $childNode );
			}

			$save_HTML = join( PHP_EOL, $htmlElements );

			return $save_HTML;
		}

		return $html;
	}

	/**
	 * @param \DOMXPath $xpath
	 * @param string    $class
	 *
	 * @return string
	 */
	private function getLanguageName( $xpath, $class ) {
		$element = $xpath->query( 'span[ @class="' . $class . '" ]' );
		if ( $element->count() > 0 && $element->item( 0 )->textContent ) {
			return $element->item( 0 )->textContent;
		}

		return '';
	}

	/**
	 * @param \DOMXPath $xpath
	 * @param \DOMNode  $node
	 * @param string    $class
	 *
	 * @return string
	 */
	private function getLanguageNameFromSiblings( $xpath, $node, $class ) {
		$element = 'span[ @class="' . $class . '" ]';

		$followingSiblings = $xpath->query( ( $node ? 'following-sibling::' : '' ) . $element, $node );
		if ( $followingSiblings->count() > 0 && $followingSiblings->item( 0 )->textContent ) {
			return $followingSiblings->item( 0 )->textContent;
		}

		$precedingSiblings = $xpath->query( ( $node ? 'preceding-sibling::' : '' ) . $element, $node );
		if ( $precedingSiblings->count() > 0 && $precedingSiblings->item( 0 )->textContent ) {
			return $precedingSiblings->item( 0 )->textContent;
		}

		return '';
	}
}
