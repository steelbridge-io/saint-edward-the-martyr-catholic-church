<?php
/**
 * Meta fields for Bible Study template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Add meta box for Bible Study card content
 */
function bible_study_add_meta_box() {
	// Get current post template
	$template = get_post_meta(get_the_ID(), '_wp_page_template', true);

	// Only add meta box if using Bible Study template
	if ('bible-study-template.php' === $template) {
		add_meta_box(
			'bible_study_card_meta',
			'Bible Study Sidebar Card',
			'bible_study_meta_box_html',
			['post', 'page'],
			'normal',
			'high'
		);
	}
}
add_action('add_meta_boxes', 'bible_study_add_meta_box');

/**
 * Meta box HTML
 */
function bible_study_meta_box_html($post) {
	// Add nonce for security
	wp_nonce_field('bible_study_save_meta', 'bible_study_meta_nonce');

	// Get saved values
	$card_title = get_post_meta($post->ID, '_bible_study_card_title', true);
	$card_text = get_post_meta($post->ID, '_bible_study_card_text', true);

	// Get WordPress Editor settings
	$editor_settings = array(
		'textarea_name' => 'bible_study_card_text',
		'textarea_rows' => 10,
		'media_buttons' => true,
		'teeny' => true,
		'quicktags' => true,
		'tinymce' => array(
			'toolbar1' => 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,wp_more,spellchecker,fullscreen,wp_adv',
			'toolbar2' => 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
			'toolbar3' => '',
		),
	);
	?>
	<div class="bible-study-meta-fields">
		<p>
			<label for="bible_study_card_title"><strong>Card Title:</strong></label>
			<input
				type="text"
				id="bible_study_card_title"
				name="bible_study_card_title"
				value="<?php echo esc_attr($card_title); ?>"
				style="width: 100%"
			>
		</p>
		<div>
			<label><strong>Card Text:</strong></label>
			<?php wp_editor($card_text, 'bible_study_card_text', $editor_settings); ?>
			<p class="description">
                You can use HTML tags, attributes, and shortcodes in this field. Format your content using the editor above. Available shortcodes will work in this content area.

            </p>
		</div>

		<?php if (!empty($card_text)) : ?>
            <div class="shortcode-preview">
                <h4>Content Preview (including shortcodes):</h4>
                <div class="preview-content" style="padding: 10px; border: 1px solid #ddd; margin-top: 10px;">
					<?php echo do_shortcode(wp_kses_post($card_text)); ?>
                </div>
            </div>
		<?php endif; ?>
    </div>
    <script>
        jQuery(document).ready(function($) {
            // Add live preview functionality
            var updatePreview = function() {
                var content = wp.editor.getContent('bible_study_card_text');
                if (content) {
                    // Ajax call to render shortcodes
                    $.post(ajaxurl, {
                        action: 'preview_bible_study_content',
                        content: content,
                        nonce: '<?php echo wp_create_nonce('bible_study_preview'); ?>'
                    }, function(response) {
                        if (response.success) {
                            $('.shortcode-preview').show().find('.preview-content').html(response.data);
                        }
                    });
                }
            };

            // Update preview when editor content changes
            if (typeof wp !== 'undefined' && wp.editor) {
                wp.editor.initialize('bible_study_card_text', {
                    tinymce: {
                        setup: function (editor) {
                            editor.on('change', _.debounce(updatePreview, 500));
                        }
                    }
                });
            }
        });
    </script>

	<?php
}

/**
 * Save meta box data
 */
function bible_study_save_meta($post_id) {
	// Check if nonce is set
	if (!isset($_POST['bible_study_meta_nonce'])) {
		return;
	}

	// Verify nonce
	if (!wp_verify_nonce($_POST['bible_study_meta_nonce'], 'bible_study_save_meta')) {
		return;
	}

	// If this is autosave, don't do anything
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	// Check user permissions
	if ('page' === $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return;
		}
	} else {
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
	}

	// Save card title
	if (isset($_POST['bible_study_card_title'])) {
		update_post_meta(
			$post_id,
			'_bible_study_card_title',
			sanitize_text_field($_POST['bible_study_card_title'])
		);
	}

	// Save card text - allowing HTML
	if (isset($_POST['bible_study_card_text'])) {
		update_post_meta(
			$post_id,
			'_bible_study_card_text',
			wp_kses_post($_POST['bible_study_card_text'])
		);
	}
}
add_action('save_post', 'bible_study_save_meta');

/**
 * Ajax handler for live preview
 */
function bible_study_preview_content() {
	// Verify nonce
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bible_study_preview')) {
		wp_send_json_error('Invalid nonce');
	}

	// Process content
	if (isset($_POST['content'])) {
		$content = wp_kses_post($_POST['content']);
		$processed_content = do_shortcode($content);
		wp_send_json_success($processed_content);
	}

	wp_send_json_error('No content provided');
}
add_action('wp_ajax_preview_bible_study_content', 'bible_study_preview_content');


/**
 * Add meta box dynamically when template is selected
 */
function bible_study_template_selected_script() {
	global $post;
	if (!$post) return;
	?>
	<script>
        jQuery(document).ready(function($) {
            // Function to check template and show/hide meta box
            function checkTemplate() {
                var template = wp.data.select('core/editor').getEditedPostAttribute('template');
                if (template === 'bible-study-template.php') {
                    $('#bible_study_card_meta').show();
                } else {
                    $('#bible_study_card_meta').hide();
                }
            }

            // Check on load and when template changes
            checkTemplate();
            wp.data.subscribe(function() {
                checkTemplate();
            });
        });
	</script>
	<?php
}
add_action('admin_footer', 'bible_study_template_selected_script');

/**
 * Define allowed HTML tags and attributes
 */
function bible_study_allowed_html() {
	$allowed_tags = wp_kses_allowed_html('post');

	// Add additional HTML elements and attributes as needed
	$allowed_tags['div'] = array(
		'class' => true,
		'id' => true,
		'style' => true,
	);

	$allowed_tags['span'] = array(
		'class' => true,
		'id' => true,
		'style' => true,
	);

	return $allowed_tags;
}