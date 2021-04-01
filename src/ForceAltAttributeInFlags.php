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

		if ( $display_flags && ( $display_native || $display_name ) ) {
			$dom = new \DOMDocument();
			$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
			$xpath = new \DOMXPath( $dom );

			$nodes = $xpath->query( '//img[ @class="wpml-ls-flag" ]' );

			/** @var \DOMNode $node */
			foreach ( $nodes as $node ) {
				$altTexts = [];

				if ( $display_native ) {
					$altTexts[] = $this->getLanguageName( $xpath, $node, 'wpml-ls-native' );
				}
				if ( $display_name ) {
					$altTexts[] = $this->getLanguageName( $xpath, $node, 'wpml-ls-display' );
				}

				$node->setAttribute( 'alt', join( '', array_filter( $altTexts ) ) );
			}

			return $dom->saveHTML();
		}

		return $html;
	}

	/**
	 * @param \DOMXPath $xpath
	 * @param \DOMNode  $node
	 * @param string    $class
	 *
	 * @return string
	 */
	private function getLanguageName( $xpath, $node, $class ) {
		$element = 'span[ @class="' . $class . '"]';

		$followingSiblings = $xpath->query( 'following-sibling::' . $element, $node );
		if ( $followingSiblings->count() > 0 && $followingSiblings->item( 0 )->textContent ) {
			return $followingSiblings->item( 0 )->textContent;
		}

		$precedingSiblings = $xpath->query( 'following-sibling::' . $element, $node );
		if ( $precedingSiblings->count() > 0 && $precedingSiblings->item( 0 )->textContent ) {
			return $precedingSiblings->item( 0 )->textContent;
		}

		return '';
	}
}
