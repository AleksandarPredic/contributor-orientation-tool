<?php

namespace WPCOTool\Frontend;

use WPCOTool\Plugin;

/**
 * Class Shortcode responsible for frontend output
 * Shortcode: [contributor-orientation-tool]
 * @package WPCOTool\Frontend
 */
class Shortcode {

	/**
	 * Plugin version.
	 *
	 * @since    0.0.1
	 * @access   public
	 * @var string
	 */
	private $version;

	/**
	 * Shortcode tag
	 * @var string
	 */
	private $shortcode_tag = 'contributor-orientation-tool';

	/**
	 * Prefix used for output to create ids, field names...
	 * @var string
	 */
	private $form_prefix = 'wpcot';

	/**
	 * Active section css class
	 * @var string
	 */
	private $active_section_class;

	/**
	 * Shortcode constructor.
	 *
	 * @param string $version Plugin version
	 */
	public function __construct( string $version ) {

		$this->version = sanitize_text_field( $version );
		$this->active_section_class = sprintf( ' %s__section--active', $this->form_prefix );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
		add_shortcode( $this->shortcode_tag, array( $this, 'output' ) );

	}

	/**
	 * Html output (shortcode).
	 *
	 * @param array $atts Shortcode attributes.
	 * @param string $content Shortcode content
	 * @return string
	 */
	public function output( $atts, $content = '' ) {

		$selected_teams = Plugin::get_form_config( 'teams.php' );

		/**
		 * TODO: Validate if at least one team selected when options are built
		 */

        /**
         * Multipart form sections
         */
		$sections = $this->get_questions_sections( $selected_teams );

		/**
		 * Add teams section as final results
		 */
		$sections[] = $this->get_teams_section( $selected_teams );

        /**
         * Output
         */
		return sprintf(
			'<div id="%1$s"><h2>%2$s</h2>%3$s<form method="post" action=""><div class="wpcot__questions">%4$s</div><button type="submit">%5$s</button></form></div>',
			esc_attr( $this->form_prefix ),
			esc_html__( 'Contributor orientation tool', 'contributor-orientation-tool' ),
			$this->get_form_description(),
			implode( '', $sections ),
			esc_html__( 'Submit', 'contributor-orientation-tool' )
		);

	}

	/**
	 * Return section with teams
	 * @param array $selected_teams Array of enabled teams
	 *
	 * @return string Return section html
	 */
	private function get_teams_section( $selected_teams ) {

		$fields = array();
		foreach ( $selected_teams as $id => $name ) {

			$team = new Team( $id, $name );
			$team_id = $team->get_id();

			$fields[] = $this->get_checkbox_field(
				$team->get_name(),
				sprintf( '%s__teams', $this->form_prefix ),
				$team_id,
				$team_id
			);

		}

		return $this->get_section(
			sprintf( '%s-section-teams', $this->form_prefix ),
			esc_html__( 'Based on your answers, we recommend that you join some of teams below!', 'contributor-orientation-tool' ),
			implode( '', $fields ),
			'',
			$this->get_button( esc_html__( 'Previous section', 'contributor-orientation-tool' ), true ),
			''
		);

	}

	/**
	 * Return sections with questions
	 * @param array $selected_teams Array of enabled teams
	 *
	 * @return array Return array of sections html
	 */
	private function get_questions_sections( $selected_teams ) {

	    $section_1_key = sprintf( '%s-section-1', $this->form_prefix );

        $form_sections = array(
            $section_1_key => Plugin::get_form_config( 'section-1.php' ),
            sprintf( '%s-section-2', $this->form_prefix ) => Plugin::get_form_config( 'section-2.php' ),
            sprintf( '%s-section-3', $this->form_prefix ) => Plugin::get_form_config( 'section-3.php' )
        );

        $sections = array();
        foreach ( $form_sections as $section_id => $section ) {

            $fields = array();
            foreach ( $section['questions'] as $key => $field ) {

                if ( ! isset( $field['label'] ) || ! $field['teams'] ) {
                    continue;
                }

                $question = QuestionFactory::create( $field['label'], $field['teams'] );
                $teams = $question->get_teams();

                /**
                 * Compare if question is referring to one of selected teams and get only enabled teams
                 */
                $enabled_teams = array_filter( $teams, function ( $team ) use ( $selected_teams ) {
                    return in_array( $team, array_keys( $selected_teams ) );
                } );

                if ( empty( $enabled_teams ) ) {
                    continue;
                }

                $fields[] = $this->get_checkbox_field(
	                $question->get_label(),
	                str_replace( '-', '_', $section_id ),
	                implode( ',', $enabled_teams ),
	                sprintf( '%s-%s', $section_id, $key )
                );

            }

            $sections[] = $this->get_section(
	            $section_id,
	            $section['headline'],
	            implode( '', $fields ),
	            $this->get_button( esc_html__( 'Next section', 'contributor-orientation-tool' ), false ),
	            $this->get_button( esc_html__( 'Previous section', 'contributor-orientation-tool' ), true ),
	            $section_id === $section_1_key ? $this->active_section_class : ''
            );

        }

        return $sections;

    }

	/**
	 * Return section html
	 * @param string $id Section id attribute
	 * @param string $headline Section headline
	 * @param string $content Section content
	 * @param string $button_next Button html
	 * @param string $button_prev Button html
	 * @param bool $active_class If section should have active class
	 *
	 * @return string
	 */
    private function get_section( $id, $headline, $content, $button_next = '', $button_prev = '', $active_class = false ) {

	    return sprintf(
		    '<section id="%1$s" class="%6$s%7$s"><h3>%2$s</h3>%3$s<div class="%9$s"></div><div class="%8$s">%5$s%4$s</div></section>',
		    esc_attr( $id ), // %1$s
		    esc_html( $headline ), // %2$s
		    $content, // %3$s
		    ! empty( $button_next ) ? wp_kses_post( $button_next ) : '', // %4$s
		    ! empty( $button_prev ) ? wp_kses_post( $button_prev ) : '', // %5$s
		    sprintf( '%s__section', $this->form_prefix ), // %6$s
		    $active_class, // %7$s
		    sprintf( '%s__buttons', $this->form_prefix ), // %8$s
		    sprintf( '%s__section-error', $this->form_prefix ) // %9$s
	    );

    }

	/**
	 * Return button html
	 * @param string $text Button text
	 * @param bool $prev If it is previous or next button
	 *
	 * @return string
	 */
    private function get_button( $text, $prev = false ) {

		return sprintf(
		'<button class="%1$s" type="button">%2$s</button>',
		$prev ? esc_attr( sprintf( '%s__button-prev', $this->form_prefix ) ) : esc_attr( sprintf( '%s__button-next', $this->form_prefix ) ),
			esc_html( $text )
		);

    }

	/**
	 * Return checkbox html
	 * @param string $label Label
	 * @param string $name Input name
	 * @param string $value Input value
	 * @param string $id Input id
	 *
	 * @return string
	 */
    private function get_checkbox_field( $label, $name, $value, $id = '' ) {

		return sprintf(
			'<div><input id="%1$s" type="checkbox" name="%3$s[]" value="%4$s" /><label for="%1$s">%2$s</label></div>',
			esc_attr( $id ), // %1$s
			esc_html( $label ), // %2$s
			sanitize_text_field( $name ), // %3$s
			esc_js( $value ) // %4$s
		);

    }

	/**
	 * Return form description html
	 * @return string
	 */
	private function get_form_description() {

		return sprintf(
			'<div class="%1$s"><p>%2$s</p><p>%3$s</p><p>%4$s</p><p>%5$s</p><p>%6$s</p></div>',
			esc_attr( sprintf( '%s__description', $this->form_prefix ) ),
			esc_html__( 'Hello,', 'contributor-orientation-tool' ),
			esc_html__( 'Thank you for your interest in contributing to WordPress project. ', 'contributor-orientation-tool' ),
			esc_html__( 'Even though this tool is created by WordCamp Europe organising team, it is meant to help you decide in less than 1 minute which team to join at any WordCamp Contributor Day in order to start contributing. As a matter of fact, you don’t even have to use it specifically for Contributor Day. ', 'contributor-orientation-tool' ),
			esc_html__( 'We are not collecting nor storing any data from this form. It is completely anonymous and purely informative nature. This means that you can use it any time and as many times you want. Only you will know your results and these results are, by no means, obligatory for you to join recommended teams.', 'contributor-orientation-tool' ),
			esc_html__( 'Please note that this survey will not register you for any Contributor Day. You still need to do that if you want to attend Contributor Day. For more info on that please visit the website for WordCamp you are planning to attend and/or contact its organizers.', 'contributor-orientation-tool' )
		);

	}

	/**
	 * Scripts and styles
	 *
	 * @access public
	 * @since 0.0.1
	 */
	public function scripts() {

		if ( ! is_singular( array( 'post', 'page' ) ) ) {
			return;
		}

		/**
		 * Global $post var
		 * @param WP_Post $post
		 */
		global $post;

		if ( ! has_shortcode( $post->post_content, $this->shortcode_tag ) ) {
			return;
		}

		$handle = sprintf( '%s-public', $this->shortcode_tag );

		wp_enqueue_style(
			$handle,
			Plugin::assets_url( 'css/shortcode.css' ),
			array(),
			$this->version
		);

		wp_enqueue_script(
			$handle,
			Plugin::assets_url( 'js/shortcode.js' ),
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_localize_script(
			$handle,
			sprintf( '%sData', $this->form_prefix ),
			array(
				'errorMessage' => esc_html__( 'Please select at least one answer!', 'contributor-orientation-tool' )
			)
		);
	}

}