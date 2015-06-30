<?php
namespace Exporter\Components;

use \Exporter\Exporter as Exporter;

/**
 * Represents a simple image.
 *
 * @since 0.2.0
 */
class Image extends Component {

	/**
	 * Set this component as alignable. @see \Exporter\Components\Component.
	 */
	public $is_alignable = true;

	public static function node_matches( $node ) {
		// Is this an image node?
		if ( 'img' == $node->nodeName ) {
			return $node;
		}

		// Is there a node with tag 'img' inside this one? If so return all image
		// nodes.
		//
		// FIXME: Because image can't just be parsed by markdown we have to
		// forcefully extract them from the container, so if the container has
		// something besides this component, it will be ignored. See comment in
		// Components/Body.
		if ( $images = self::node_find_all_by_tagname( $node, 'img' ) ) {
			return $images;
		}

		return null;
	}

	protected function build( $text ) {
		$matches = array();
		preg_match( '/src="([^"]*?)"/im', $text, $matches );
		$url = $matches[1];
		$filename = basename( $url );

		// Save image into bundle
		$this->bundle_source( $filename, $url );

		$this->json = array(
			'role' => 'photo',
			'URL'  => 'bundle://' . $filename,
		);

		$this->set_default_layout();
	}

	private function set_default_layout() {
		// Find out the starting column
		$col_start = Body::COLUMN_SPAN;
		switch ( $this->get_setting( 'body_orientation' ) ) {
		case 'right':
			$col_start = 0;
			break;
		case 'center':
			// TODO: What do? Show centered?
			$col_start = 0;
			break;
		}

		$this->json[ 'layout' ] = 'image-layout';
		$this->register_layout( 'image-layout', array(
			'columnStart' => $col_start,
			'columnSpan'  => Exporter::LAYOUT_COLUMNS - Body::COLUMN_SPAN + Component::ALIGNMENT_OFFSET,
		) );
	}

}

