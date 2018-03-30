<?php
/*
Plugin Name: Gravity Forms Popup
Description:  An Ajax Form Loader. It Load Gravity Forms in a popup modal window. Shortcode usage: [gravityforms action="popup" id="1" text="button text" width="900px"]
Version: 1.0.1
Author: Val Catalasan
*/

class GravityFormsPopup {

	private static $instance = null;

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance()
	{
		// If the single instance hasn't been set, set it now.
		if (null == self::$instance) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	function __construct() {
		// Hook up the AJAX actions
		add_action( 'wp_ajax_nopriv_gf_popup_get_form', array($this, 'popup_ajax_get_form') );
		add_action( 'wp_ajax_gf_popup_get_form', array($this, 'popup_ajax_get_form') );

		// Add the "popup" action to the gravityforms shortcode
		// e.g. [gravityforms action="popup" id="1" text="popup text"]
		add_filter( 'gform_shortcode_popup', array($this, 'popup_shortcode'), 10, 3 );

	}

	function popup_shortcode( $shortcode_string, $attributes, $content ) {
		$a = shortcode_atts( array(
			'id'           => 0,
			'text'         => 'Show me the form!',
			'button'       => 'btn btn-primary btn-lg',
            'width'        => '900px',
            'height'       => 'auto',
            'margin'       => '0 auto 0',
            'padding'      => 'auto'
		), $attributes );

		$form_id = absint( $a['id'] );

		if ( $form_id < 1 ) {
			return 'Missing the ID attribute.';
		}

		// Enqueue the scripts and styles
		gravity_form_enqueue_scripts( $form_id, true );

		$ajax_url = admin_url( 'admin-ajax.php' );

		ob_start();
		?>
		<button
			id="gf_popup_get_form_<?php echo $form_id ?>"
			class="<?php echo $a['button'] ?>"
		    data-toggle="modal"
			data-target="#gf_popup_form_container_<?php echo $form_id ?>"
		>
			<?php echo $a['text'] ?>
		</button>

		<div id="gf_popup_form_container_<?php echo $form_id ?>" class="gf_popup_form_container modal fade" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><?php echo $a['text'] ?></h4>
					</div>
					<div class="modal-body">
						<p>Where's the form?</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default close" data-dismiss="modal">Close</button>
					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->

		<script>
            (function (SHFormLoader, $) {
                $('#gf_popup_form_container_<?php echo $form_id ?>')
                    .css('width', '<?php echo $a['width'] ?>')
                    .css('height', '<?php echo $a['height'] ?>')
                    .css('margin', '<?php echo $a['margin'] ?>')
                    .css('padding', '<?php echo $a['padding'] ?>');
                $('#gf_popup_get_form_<?php echo $form_id ?>')
                    .click(function(){
                        var button = $(this);
                        $.get('<?php echo $ajax_url ?>?action=gf_popup_get_form&form_id=<?php echo $form_id ?>',function(response){
                            $('#gf_popup_form_container_<?php echo $form_id ?> .modal-body').html(response).fadeIn();
                            button.hide();

                            $(".disabled input").attr('disabled','disabled');
                            $(".readonly input").attr('readonly','readonly');

                            //if(window['gformInitDatepicker']) {gformInitDatepicker();}
                        });
                    });
                $('#gf_popup_form_container_<?php echo $form_id ?> .close')
                    .click(function(){
                        var button = $('#gf_popup_get_form_<?php echo $form_id ?>');
                        button.show();
                    });
			}(window.SHFormLoader = window.SHFormLoader || {}, jQuery));
		</script>
		<?php
		return ob_get_clean();
	}

	function popup_ajax_get_form() {
		$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		gravity_form( $form_id, true, false, false, false, true );
		die();
	}

}

GravityFormsPopup::get_instance();
