<?php
/**
 * @package Cipher
 * @copyright Copyright 2020 Luigi Cavalieri.
 * @license https://opensource.org/licenses/GPL-3.0 GPL v3.0
 *
 *
 *
 * Plugin's main class.
 *
 * @since 1.0
 */
final class Cipher extends cipher\Plugin {
	/**
	 * @since 1.2
	 */
	const PLUGIN_NAME = 'Cipher';

	/**
	 * @since 1.0
	 */
	const VERSION = '1.2.1';

	/**
	 * @since 1.0
	 *
	 * @param string $loader_path
	 */
	public static function launch( $loader_path ) {
		global $pagenow;

		if ( parent::launch( $loader_path ) && ( $pagenow == 'wp-comments-post.php' ) ) {
			add_filter( 'preprocess_comment', array( self::$plugin, 'wpWillProcessComment' ) );
		}
	}
	
	/**
	 * @since 1.2
	 *
	 * @param array $data Comment data
	 * @return array
	 */
	public function &wpWillProcessComment( $data ) {
		$this->load( 'core/comment-processor.class.php' );

		$commentProcessor = new CipherCommentProcessor( $data['comment_content'] );

		if ( $commentProcessor->parseCodeBlocks() ) {	
			$user = null;
			
			if ( isset( $data['user_ID'] ) ) {
				$user = get_userdata( (int) $data['user_ID'] );
			}
			elseif ( isset( $data['user_id'] ) ) {
				$user = get_userdata( (int) $data['user_id'] );
			}
			
			if ( !( $user && $user->has_cap( 'moderate_comments' ) ) ) {
				// Hack to enable <pre> tags in comments.
				global $allowedtags;
				$allowedtags['pre'] = array();

				$commentProcessor->stripPreTags();
			}
		}
		
		$data['comment_content'] = $commentProcessor->getComment();
		
		return $data;
	}
}
?>