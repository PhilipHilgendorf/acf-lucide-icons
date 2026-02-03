<?php
if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists('acf_field_lucide_icon') ) :
    
/**
 * Class acf_field_lucide_icon.
 */
class acf_field_lucide_icon extends acf_field {
    /**
     * Initialize icon picker field
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function initialize() {
        $this->name          = 'lucide_icon';
        $this->label         = __( 'Lucide Icon', 'secure-custom-fields' );
        $this->public        = true;
        $this->category      = 'advanced';
        $this->description   = __( 'An interactive UI for selecting an icon. Select from Dashicons, the media library, or a standalone URL input.', 'secure-custom-fields' );
        $this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-icon-picker.png';
        $this->doc_url       = 'https://www.advancedcustomfields.com/resources/icon-picker/';
        $this->defaults      = array(
            'library'       => 'all',
            'return_format' => 'string',
            'default_value' => '',
        );
    }

    /**
     * Renders icon picker field
     *
     * @since 1.0.0
     *
     * @param object $field The ACF Field
     * @return void
     */
    public function render_field( $field ) {

        $div = array(
            'id'    => $field['id'],
            'class' => $field['class'] . ' acf-icon-picker ',
        );

        echo '<div ' . acf_esc_attrs( $div ) . '>';

        $value = $field['value'] ?? '';
        if (is_array($value)) {
            $selected_icon = $value['value'] ?? '';
        } else {
            $selected_icon = $value;
        }

        echo '<div class="acf-lucideicons-search-wrap">';
        
        printf(
            '<input type="hidden" name="%s" value="%s" class="acf-lucideicons-hidden-input" />',
            esc_attr($field['name']),
            esc_attr($selected_icon)
        );

        acf_text_input(
            array(
                'class'       => 'acf-lucideicons-search-input',
                'placeholder' => esc_html__( 'Search icons...', 'secure-custom-fields' ),
                'type'        => 'search',
            )
        );
        echo '<div class="acf-lucideicons-list" data-batch-size="100">';

        $all_icons = [];
        $selected_html = '';
        foreach ( $this->get_lucideicons() as $icon => $data ) {
            $label = $data['label'];
            $tags = implode(' ', $data['tags']);
            $searchable = strtolower($icon . ' ' . $label . ' ' . $tags);

            $all_icons[] = [
                'name' => $icon,
                'label' => $label,
                'search' => $searchable
            ];

            if($selected_icon == $icon) {
                $selected_html = sprintf('<div class="acf-lucideicons-container selected" data-search="%s" data-icon="%s" title="%s"><div class="acf-lucideicons-inner"><i data-lucide="%s"></i></div></div>', esc_attr($searchable), esc_attr($icon), esc_attr($label), esc_attr($icon));
            }
        }

        if ($selected_html) {
            echo $selected_html;
        }

        $count = 0;
        $batch_size = 100;
        foreach ( $all_icons as $icon_data ) {
            if ($icon_data['name'] === $selected_icon) continue; // Skip selected, already rendered
            if ($count >= $batch_size) break;

            printf('<div class="acf-lucideicons-container" data-search="%s" data-icon="%s" title="%s"><div class="acf-lucideicons-inner"><i data-lucide="%s"></i></div></div>',
                esc_attr($icon_data['search']),
                esc_attr($icon_data['name']),
                esc_attr($icon_data['label']),
                esc_attr($icon_data['name'])
            );
            $count++;
        }

        echo '<script type="application/json" class="acf-lucideicons-data">' . wp_json_encode($all_icons) . '</script>';
        echo '</div>';
        ?>
        <div class="acf-lucideicons-list-empty">
            <img src="<?php echo esc_url( acf_get_url( 'assets/images/face-sad.svg' ) ); ?>" />
            <p class="acf-no-results-text">
                <?php
                printf(
                    /* translators: %s: The invalid search term */
                    esc_html__( "No search results for '%s'", 'secure-custom-fields' ),
                    '<span class="acf-invalid-lucideicons-search-term"></span>'
                );
                ?>
            </p>
        </div>

                <?php

        echo '</div>';
        echo '</div>';

    }

    /**
     * Localizes text for Lucide Icon
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function input_admin_enqueue_scripts() {
        wp_enqueue_script('lucide-js', ACF_LUCIDEICONS_PLUGIN_URL . 'assets/js/lucide.min.js', [], '0.563.0', true);
        wp_enqueue_script('lucide-icons-selector-acf', ACF_LUCIDEICONS_PLUGIN_URL . 'assets/js/lucide-icon-selector.js', ['jquery','acf-input', 'lucide-js'], rand(0, 9999), true);
        wp_enqueue_style('lucide-icons-selector-acf', ACF_LUCIDEICONS_PLUGIN_URL . 'assets/css/lucide-icon-selector.css', [], rand(0, 9999));
    }

    /**
     * Formats the value for output in the frontend.
     * Ensures that a string is always returned.
     *
     * @param mixed $value The stored value
     * @param int $post_id The post ID
     * @param array $field The field array
     * @return string The icon name as a string
     */
    public function format_value( $value, $post_id, $field ) {

        if (is_array($value)) {
            return $value['value'] ?? '';
        }

        if (is_string($value) && strpos($value, '{') === 0) {
            $decoded = json_decode($value, true);
            if (is_array($decoded) && isset($decoded['value'])) {
                return $decoded['value'] ?? '';
            }
        }

        return $value ? (string) $value : '';
    }

    /**
     * get_lucideicons()
     *
     * Loads all Lucide icons from the tags.json file.
     *
     * @since 1.0.0
     *
     * @return array Icon name => Array with label and tags
     */
    public function get_lucideicons() {
        static $icons = null;

        if ($icons !== null) {
            return $icons;
        }

        $json_file = ACF_LUCIDEICONS_PLUGIN_DIR . 'assets/js/tags.json';

        if (!file_exists($json_file)) {
            return [];
        }

        $json_content = file_get_contents($json_file);
        $icons_data = json_decode($json_content, true);

        if (!is_array($icons_data)) {
            return [];
        }

        $icons = [];
        foreach ($icons_data as $icon_name => $tags) {
            $label = ucwords(str_replace('-', ' ', $icon_name));
            $icons[$icon_name] = [
                'label' => $label,
                'tags' => is_array($tags) ? $tags : []
            ];
        }

        return $icons;
    }

    /**
     * Returns the schema used by the REST API.
     *
     * @since 6.3
     *
     * @param array $field The main field array.
     * @return array
     */
    public function get_rest_schema( array $field ): array {
        return array(
            'type'       => array( 'object', 'null' ),
            'required'   => ! empty( $field['required'] ),
            'description' => esc_html__( 'The type of icon to save.', 'secure-custom-fields' ),
        );
    }

    /**
     * Validates a value sent via the REST API.
     *
     * @since 6.3
     *
     * @param boolean    $valid The current validity boolean.
     * @param array|null $value The value of the field.
     * @param array      $field The main field array.
     * @return boolean|WP_Error
     */
    public function validate_rest_value( $valid, $value, $field ) {
        if ( is_null( $value ) ) {
            if ( ! empty( $field['required'] ) ) {
                return new WP_Error(
                    'rest_property_required',
                    /* translators: %s - field name */
                    sprintf( __( '%s is a required property of acf.', 'secure-custom-fields' ), $field['name'] )
                );
            } else {
                return $valid;
            }
        }

        if ( ! empty( $value['type'] ) && 'media_library' === $value['type'] ) {
            $param = sprintf( '%s[%s][value]', $field['prefix'], $field['name'] );
            $data  = array(
                'param' => $param,
                'value' => (int) $value['value'],
            );

            if ( ! is_int( $value['value'] ) || 'attachment' !== get_post_type( $value['value'] ) ) {
                /* translators: %s - field/param name */
                $error = sprintf( __( '%s requires a valid attachment ID when type is set to media_library.', 'secure-custom-fields' ), $param );
                return new WP_Error( 'rest_invalid_param', $error, $data );
            }
        }

        return $valid;
    }
}

acf_register_field_type( 'acf_field_lucide_icon' );
endif;
