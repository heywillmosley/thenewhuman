<?php

/**
 * Handles logic for page data post properties.
 *
 * @since 1.0
 */
final class FLPageDataPost {

	/**
	 * @since 1.0
	 * @param object $settings
	 * @return string
	 */
	static public function get_excerpt( $settings ) {
		add_filter( 'excerpt_length', __CLASS__ . '::excerpt_length_filter' );
		add_filter( 'excerpt_more', __CLASS__ . '::excerpt_more_filter' );

		$excerpt = apply_filters( 'the_excerpt', get_the_excerpt() );

		remove_filter( 'excerpt_length', __CLASS__ . '::excerpt_length_filter' );
		remove_filter( 'excerpt_more', __CLASS__ . '::excerpt_more_filter' );

		return $excerpt;
	}

	/**
	 * @since 1.0
	 * @param string $length
	 * @return string
	 */
	static public function excerpt_length_filter( $length ) {
		$settings = FLPageData::get_current_settings();

		return $settings && is_numeric( $settings->length ) ? $settings->length : 55;
	}

	/**
	 * @since 1.0
	 * @param string $more
	 * @return string
	 */
	static public function excerpt_more_filter( $more ) {
		$settings = FLPageData::get_current_settings();

		return $settings && ! empty( $settings->more ) ? $settings->more : '...';
	}

	/**
	 * @since 1.0
	 * @return string
	 */
	static public function get_content() {
		remove_filter( 'the_content', 'FLBuilder::render_content' );

		$content = apply_filters( 'the_content', get_the_content() );

		$content .= wp_link_pages( array(
			'before'      => '<div class="page-links">' . __( 'Pages:', 'fl-theme-builder' ),
			'after'       => '</div>',
			'link_before' => '<span class="page-number">',
			'link_after'  => '</span>',
			'echo'        => false,
		) );

		add_filter( 'the_content', 'FLBuilder::render_content' );

		return $content;
	}

	/**
	 * @since 1.0
	 * @param object $settings
	 * @return string
	 */
	static public function get_link( $settings ) {
		$href = get_permalink();

		if ( 'title' == $settings->text ) {
			$title = the_title_attribute( array(
				'echo' => false,
			) );
			$text  = get_the_title();
		} else {
			$title = esc_attr( $settings->custom_text );
			$text  = $settings->custom_text;
		}

		return "<a href='{$href}' title='{$title}'>{$text}</a>";
	}

	/**
	 * @since 1.0
	 * @param object $settings
	 * @return string
	 */
	static public function get_date( $settings ) {
		return get_the_date( $settings->format );
	}

	/**
	 * @since 1.0.3
	 * @param object $settings
	 * @return string
	 */
	static public function get_modified_date( $settings ) {

		if ( 'human' == $settings->format ) {
			return human_time_diff( get_the_time( 'U' ) ) . ' ago';
		}
		return get_the_modified_date( $settings->format );
	}

	/**
	 * @since 1.0
	 * @param object $settings
	 * @return string
	 */
	static public function get_featured_image( $settings ) {
		global $post;

		if ( 'tag' == $settings->display ) {

			$class = 'default' == $settings->align ? '' : 'align' . $settings->align;
			$image = get_the_post_thumbnail( $post, $settings->size, array(
				'itemprop' => 'image',
				'class' => $class,
			) );

			if ( $image && 'yes' == $settings->linked ) {

				$href  = get_the_permalink();
				$title = the_title_attribute( array(
					'echo' => false,
				) );

				return "<a href='{$href}' title='{$title}'>{$image}</a>";
			} else {
				return $image;
			}
		} elseif ( 'url' == $settings->display ) {
			return get_the_post_thumbnail_url( $post, $settings->size );
		} elseif ( 'alt' == $settings->display ) {
			return get_post_meta( get_post_thumbnail_id( $post->ID ), '_wp_attachment_image_alt', true );
		} else {

			$image = get_post( get_post_thumbnail_id( $post->ID ) );

			if ( 'title' == $settings->display ) {
				return $image->post_title;
			} elseif ( 'caption' == $settings->display ) {
				return $image->post_excerpt;
			} elseif ( 'description' == $settings->display ) {
				return $image->post_content;
			}
		}
	}

	/**
	 * @since 1.0
	 * @param object $settings
	 * @return array
	 */
	static public function get_featured_image_url( $settings ) {
		global $post;

		$id  = '';
		$url = '';

		if ( has_post_thumbnail( $post ) ) {
			$id  = get_post_thumbnail_id( $post->ID );
			$url = get_the_post_thumbnail_url( $post, $settings->size );
		} elseif ( isset( $settings->default_img_src ) ) {
			$id  = $settings->default_img;
			$url = $settings->default_img_src;
		}

		return array(
			'id'  => $id,
			'url' => $url,
		);
	}

	/**
	 * @since 1.0
	 * @return array
	 */
	static public function get_attached_images() {
		global $post;

		return array_keys( get_attached_media( 'image', $post->ID ) );
	}

	/**
	 * @since 1.0
	 * @param object $settings
	 * @return string
	 */
	static public function get_terms_list( $settings ) {
		global $post;

		$terms_list = get_the_term_list( $post->ID, $settings->taxonomy, '', $settings->separator, '' );

		if ( is_string( $terms_list ) ) {
			if ( 'no' === $settings->linked ) {
				$terms_list = strip_tags( $terms_list );
			}
		} else {
			$terms_list = '';
		}

		return $terms_list;
	}

	/**
	 * @since 1.0
	 * @return array
	 */
	static public function get_taxonomy_options() {
		$taxonomies = get_taxonomies( array(
			'public' => true,
			'show_ui' => true,
		), 'objects' );
		$result     = array();

		foreach ( $taxonomies as $slug => $data ) {

			if ( stristr( $slug, 'fl-builder' ) ) {
				continue;
			}

			$result[ $slug ] = $data->label;
		}

		return $result;
	}

	/**
	 * @since 1.0
	 * @param object $settings
	 * @return string
	 */
	static public function get_comments_number( $settings ) {
		$zero = isset( $settings->none_text ) ? $settings->none_text : null;
		$one = isset( $settings->one_text ) ? $settings->one_text : null;
		$more = isset( $settings->more_text ) ? $settings->more_text : null;

		ob_start();

		if ( '1' == $settings->link || 'yes' == $settings->link ) {
			comments_popup_link( $zero, $one, $more );
		} else {
			comments_number( $zero, $one, $more );
		}

		return ob_get_clean();
	}

	/**
	 * @since 1.0
	 * @return string
	 */
	static public function get_comments_url() {
		global $post;

		return get_comments_link( $post->ID );
	}

	/**
	 * @since 1.0
	 * @param object $settings
	 * @return string
	 */
	static public function get_author_name( $settings ) {

		$user = get_userdata( get_the_author_meta( 'ID' ) );
		$name = '';

		if ( ! $user ) {
			return '';
		}

		switch ( $settings->type ) {

			case 'display':
				$name = $user->display_name;
			break;

			case 'first':
				$name = get_user_meta( $user->ID, 'first_name', true );
			break;

			case 'last':
				$name = get_user_meta( $user->ID, 'last_name', true );
			break;

			case 'firstlast':
				$first = get_user_meta( $user->ID, 'first_name', true );
				$last  = get_user_meta( $user->ID, 'last_name', true );
				$name  = $first . ' ' . $last;
			break;

			case 'lastfirst':
				$first = get_user_meta( $user->ID, 'first_name', true );
				$last  = get_user_meta( $user->ID, 'last_name', true );
				$name  = $last . ', ' . $first;
			break;

			case 'nickname':
				$name = $user->user_nicename;
			break;

			case 'username':
				$name = $user->user_login;
			break;
		}

		if ( $name && 'yes' == $settings->link ) {
			$settings->type = $settings->link_type;
			$name = '<a href="' . self::get_author_url( $settings ) . '">' . $name . '</a>';
		}

		return $name;
	}

	/**
	 * @since 1.0
	 * @return string
	 */
	static public function get_author_bio() {
		return get_the_author_meta( 'description' );
	}

	/**
	 * @since 1.0
	 * @param object $settings
	 * @return string
	 */
	static public function get_author_url( $settings ) {

		$id  = get_the_author_meta( 'ID' );
		$url = '';

		if ( 'archive' == $settings->type ) {
			$url = get_author_posts_url( $id );
		} elseif ( 'website' == $settings->type ) {
			$user = get_userdata( $id );
			$url  = $user->user_url;
		}

		return $url;
	}

	/**
	 * @since 1.0
	 * @param object $settings
	 * @return string
	 */
	static public function get_author_profile_picture( $settings ) {
		$size   = ! is_numeric( $settings->size ) ? 512 : $settings->size;
		$avatar = get_avatar( get_the_author_meta( 'ID' ), $size );
		if ( '1' == $settings->link || 'yes' == $settings->link ) {
			$settings->type = $settings->link_type;
			$avatar = '<a href="' . self::get_author_url( $settings ) . '">' . $avatar . '</a>';
		}

		return $avatar;
	}

	/**
	 * @since 1.0
	 * @param object $settings
	 * @return string
	 */
	static public function get_author_profile_picture_url( $settings ) {

		$author = get_the_author_meta( 'ID' );

		// if not in loop use global $post to find author ID
		if ( ! $author ) {
			global $post;
			$author = $post->post_author;
		}
		// We get the url like this because not all custom avatar plugins filter get_avatar_url.
		$size = ! is_numeric( $settings->size ) ? 512 : $settings->size;
		$avatar = get_avatar( $author, $size );

		preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $avatar, $matches, PREG_SET_ORDER );
		$url = ! empty( $matches ) && isset( $matches[0][1] ) ? $matches[0][1] : '';

		if ( ! $url && isset( $settings->default_img_src ) ) {
			$url = $settings->default_img_src;
		}

		return $url;
	}

	/**
	 * @since 1.0
	 * @param object $settings
	 * @return string
	 */
	static public function get_author_meta( $settings ) {

		if ( empty( $settings->key ) ) {
			return '';
		}

		return get_user_meta( get_the_author_meta( 'ID' ), $settings->key, true );
	}

	/**
	 * @since 1.0
	 * @param object $settings
	 * @return string
	 */
	static public function get_custom_field( $settings ) {
		global $post;

		if ( empty( $settings->key ) ) {
			return '';
		}
		$meta = get_post_meta( $post->ID, $settings->key, true );

		// expression support
		if ( isset( $settings->value ) && '' !== $settings->value && isset( $settings->exp ) && '' !== $settings->exp ) {
			switch ( $settings->exp ) {
				case 'less':
					return ( intval( $meta ) < intval( $settings->value ) ) ? $meta : '';
					break;

				case 'lessequals':
					return ( intval( $meta ) <= intval( $settings->value ) ) ? $meta : '';
					break;

				case 'greater':
					return ( intval( $meta ) > intval( $settings->value ) ) ? $meta : '';
					break;

				case 'greaterequals':
					return ( intval( $meta ) >= intval( $settings->value ) ) ? $meta : '';
					break;

				case 'equals':
					return ( $meta === $settings->value ) ? $meta : '';
					break;

				case 'notequals':
					return ( $meta !== $settings->value ) ? $meta : '';
					break;

				default:
				break;
			}
		}

		if ( isset( $settings->value ) && '' !== $settings->value ) {
			return ( $settings->value == $meta ) ? $meta : '';
		}
		return $meta;
	}
}
