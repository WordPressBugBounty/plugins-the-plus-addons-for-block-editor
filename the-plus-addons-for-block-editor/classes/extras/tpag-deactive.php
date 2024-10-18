<?php
/**
 * TPGB Deactive Popup
 *
 * @since 3.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Tpag_Deactive' ) ) {

	class Tpag_Deactive {


        /**
		 * Member Variable
		 */
		private static $instance;

		/**
		 *  Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 *  Constructor
		 */
		public function __construct() {
            add_action( 'current_screen', function () {
                if ( ! in_array( get_current_screen()->id, [ 'plugins', 'plugins-network' ] ) ) {
                    return;
                }
    
                add_action( 'admin_footer', array( $this, 'tpgb_deactive_popup' ) );
            } );
			
            add_action( 'wp_ajax_tpgb_deactive_plugin', array( $this, 'tpgb_deactive_plugin' ) );
            add_action( 'wp_ajax_tpgb_skip_deactivate', array( $this, 'tpgb_skip_deactivate' ) );
		}

        public function tpgb_check_white_label(){
            if(defined('TPGBP_VERSION')){
                $label_options = get_option( 'tpgb_white_label' );	
                
                if( !empty($label_options) && is_array($label_options)){
                    foreach($label_options as $key => $val){
                        if(!empty($val) && $val!='hidden'){
                            return false;
                        }
                    }
                }
            }
            return true;
        }

        /**
		 *  Popup Html Css Js
         * 
		 */
        public function tpgb_deactive_popup() {
            global $pagenow;
            if ( !empty($pagenow) && $pagenow == 'plugins.php' && $this->tpgb_check_white_label() ) {
                $this->tpgb_deact_popup_html();

                $this->tpgb_deact_popup_css();
                $this->tpgb_deact_popup_js();
            }
        }

        /**
		 *  Popup Html Code
         * 
		 */
        public function tpgb_deact_popup_html() {  
            
			$security = wp_create_nonce( 'tpgb-deactivate-feedback' );
            ?>
            <div class="tpgb-modal" id="tpgb-deactive-modal">
                <div class="tpgb-modal-wrap">
                
                    <div class="tpgb-modal-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="none"><rect width="30" height="30" fill="#1717CC" rx="6"/><path fill="#fff" fill-rule="evenodd" d="M16.665 19.075V5.382a.323.323 0 0 0-.144-.288.52.52 0 0 0-.384-.144H13.35v14.894h2.306c.289 0 .529-.08.721-.24.224-.16.32-.337.288-.529Zm0 5.91.048-2.354a.788.788 0 0 0-.048-.24v-.048c-.032-.033-.048-.065-.048-.097v-.048a1.322 1.322 0 0 0-.384-.336c-.032-.032-.064-.048-.096-.048a1.066 1.066 0 0 0-.337-.048c0-.032-.016-.048-.048-.048H13.35v3.267h3.315Z" clip-rule="evenodd"/></svg>
                        <span class="tpgb-feed-head-title">
                            <?php echo esc_html__( 'Quick Feedback', 'tpgb' ); ?>
                        </span>
                    </div>

                    <div class="tpgb-modal-body">
                        <h3 class="tpgb-feed-caption"><?php echo esc_html__( "If you have a moment, please let us know why you're deactivating Nexter Blocks :", "tpgb" ); ?></h3>
                        <form class="tpgb-feedback-dialog-form" method="post">

                            <input type="hidden" name="nonce" value="<?php echo esc_attr( $security ); ?>" />

                            <div class="tpgb-modal-input">
                                <?php 
                                    $resonData = array(
                                        array(
                                            'reason'  	    => __( "This is a temporary deactivation.", "tpgb" )
                                        ),
                                        array(
                                            'reason'        	=> __( "Facing technical issues/bugs with the plugin.", "tpgb" ),
                                        ),
                                        array(
                                            'reason'        	=> __( "Performance Issues.", "tpgb" ),
                                        ),
                                        array(
                                            'reason'        	=> __( "Found an alternative Block Addon.", "tpgb" )
                                        ),
                                        array(
                                            'reason'        	=> __( "No more planning to use Gutenberg Editor.", "tpgb" )
                                        ),
                                        array(
                                            'reason'        	=> __( "Dont want to use any Gutenberg Addon, just Gutenberg.", "tpgb" ),
                                        ),
                                        array(
                                            'reason'        	=> __( "Its missing the feature i require.", "tpgb" ),
                                        ),
                                        array(
                                            'reason'        	=> __( "Other", "tpgb" ),
                                        ),
                                    );
                                    foreach ( $resonData as $key => $value) { ?>
                                        <div>
                                            <label class="tpgb-relist">
                                                <input type="radio" <?php echo $key == 0 ? 'checked="checked"' : ''; ?> id="<?php echo 'details-'.esc_attr($key); ?>" name="tpgb-reason" value="<?php echo esc_attr($value['reason']); ?>">
                                                <div class="tpgb-reason-text"><?php echo esc_html($value['reason']); ?></div>
                                            </label>
                                        </div>
                                <?php } ?>
                            </div>
                            <textarea name="tpgb-reason-txt" placeholder="<?php echo esc_html__('Please share the reason', 'tpgb') ?>" class="tpgb-reason-deails"></textarea>
                        </form>
                    </div>

                    <div class="tpgb-modal-footer">
                        <a class="tpgb-modal-submit tpgb-btn tpgb-btn-primary" href="#"><?php echo esc_html__( "Submit & Deactivate", "tpgb" ); ?></a>
                        <a class="tpgb-modal-deactive" href="#"><?php echo esc_html__( "Skip & Deactivate", "tpgb" ); ?></a>
                    </div>
                        
                    <div class="tpgb-help-link">
                        <span><?php echo esc_html__( 'If you require any help , ' , 'tpgb'); ?> <a href="<?php if(defined('TPGBP_VERSION')) { echo esc_url('https://store.posimyth.com/helpdesk/?utm_source=wpbackend&utm_medium=admin&utm_campaign=links'); } else { echo esc_url('https://wordpress.org/support/plugin/the-plus-addons-for-block-editor/'); }  ?>" target="_blank" rel="noopener noreferrer" > <?php echo esc_html__( 'please add a ticket ', 'tpgb') ?> </a>. <?php echo esc_html__ ( 'We reply within 24 working hours.', 'tpgb' ); ?></span>
                        <span> <?php echo esc_html__( 'Read') ?> <a href="<?php  echo esc_url('https://nexterwp.com/docs/?utm_source=wpbackend&utm_medium=admin&utm_campaign=pluginpage') ?>" target="_blank" rel="noopener noreferrer" >  <?php echo esc_html__( 'Documentation.' , 'tpgb') ?>   </a> </span> 
                    </div>
                </div>
            </div>
        <?php }

        /**
		 *  Popup Css  Code
         * 
		 */
        public function tpgb_deact_popup_css() { ?>
            <style type="text/css">
                .tpgb-modal {
                    position: fixed;
                    z-index: 99999;
                    top: 0;
                    right: 0;
                    bottom: 0;
                    left: 0;
                    backdrop-filter: blur(4px);
                    display: none;
                    box-sizing: border-box;
                    overflow: scroll;
                    opacity: 0;
                    visibility: hidden;
                    transition: opacity .3s,visibility .3s,backdrop-filter .3s
                }

                .tpgb-modal.modal-active {
                    display: block;
                    opacity: 1;
                    visibility: visible
                }

                .tpgb-modal-wrap {
                    width: 100%;
                    position: relative;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%,-50%);
                    background: #fff;
                    max-width: 554px;
                    border-radius: 5px;
                    overflow: hidden;
                    transition: transform .3s ease-in-out;
                    transform-origin: center
                }

                .tpgb-reason-deails {
                    display: block;
                    width: 100%;
                    margin-top: 20px
                }

                #tpgb-deactive-modal {
                    background: rgb(0 0 0/33%);
                    overflow: hidden
                }

                #tpgb-deactive-modal .tpgb-modal-header {
                    padding: 17px 30px;
                    display: flex;
                    align-items: center;
                    background: #fff;
                    box-shadow: 0 0 8px rgba(0,0,0,.1)
                }

                #tpgb-deactive-modal .tpgb-modal-header .tpgb-feed-head-title {
                    margin-left: 10px;
                    padding: 0;
                    flex: 1;
                    line-height: 1;
                    font-size: 15px;
                    font-weight: 700;
                    text-transform: uppercase;
                    color: #3c434a
                }

                #tpgb-deactive-modal .tpgb-feed-caption {
                    font-weight: 700;
                    font-size: 15px;
                    line-height: 1.4
                }

                #tpgb-deactive-modal .tpgb-modal-body,.tpgb-help-link {
                    padding: 25px 30px;
                    display: flex;
                    flex-direction: column
                }

                .tpgb-feedback-dialog-form {
                    padding-top: 25px
                }

                #tpgb-deactive-modal .tpgb-modal-body h3 {
                    padding: 0;
                    margin: 0;
                    line-height: 1.4;
                    font-size: 15px
                }

                #tpgb-deactive-modal .tpgb-modal-body ul {
                    margin: 25px 0 10px
                }

                #tpgb-deactive-modal .tpgb-modal-body ul li {
                    display: flex;
                    margin-bottom: 10px;
                    color: #807d7d
                }

                #tpgb-deactive-modal .tpgb-modal-body ul li:last-child {
                    margin-bottom: 0
                }

                #tpgb-deactive-modal .tpgb-modal-body ul li label {
                    align-items: center;
                    width: 100%
                }

                #tpgb-deactive-modal .tpgb-modal-body ul li label input {
                    padding: 0!important;
                    margin: 0;
                    display: inline-block
                }

                #tpgb-deactive-modal .tpgb-modal-body ul li label textarea {
                    margin-top: 8px;
                    width: 350px
                }

                #tpgb-deactive-modal .tpgb-modal-body ul li label .tpgb-reason-text {
                    margin-left: 8px;
                    display: inline-block
                }

                #tpgb-deactive-modal .tpgb-modal-footer {
                    padding: 0 30px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    flex-wrap: wrap
                }

                #tpgb-deactive-modal .tpgb-modal-footer .tpgb-modal-deactive,#tpgb-deactive-modal .tpgb-modal-footer .tpgb-modal-submit {
                    cursor: pointer;
                    font-size: 12px;
                    font-weight: 500;
                    padding: 10px 15px;
                    outline: 0;
                    border: 0;
                    border-radius: 3px;
                    transition: all .3s;
                    text-decoration: none
                }

                #tpgb-deactive-modal .tpgb-modal-footer .tpgb-modal-submit {
                    background-color: #1717CC;
                    color: #fff
                }

                #tpgb-deactive-modal .tpgb-modal-footer .tpgb-modal-deactive {
                    color: #515962
                }

                .tpgb-modal-input {
                    display: flex;
                    flex-direction: column;
                    align-items: flex-start;
                    justify-content: center;
                    row-gap: 10px
                }

                .tpgb-relist {
                    display: block
                }

                .tpgb-reason-text {
                    display: inline-block
                }

                #tpgb-deactive-modal input[type=radio]:focus,.tpgb-modal-deactive:focus,.tpgb-modal-submit:focus {
                    border-color: #1717CC!important;
                    box-shadow: none!important
                }

                #tpgb-deactive-modal input[type=radio]:checked::before {
                    background: #1717CC
                }

                .tpgb-help-link span {
                    font-size: 13px;
                    font-style: italic;
                    font-weight: 500
                }

                .tpgb-help-link span>a {
                    color: #1717CC;
                    text-decoration: none;
                    line-height: 1.8
                }
                @keyframes tp-rotation{
                    0%{
                        transform:rotate(0deg)
                    }
                    100%{
                        transform:rotate(359deg)
                    }
                }
                #tpgb-deactive-modal .tpgb-modal-submit.tpgb-loading:before{display:inline-block;content:"\f463";font:18px dashicons;animation:tp-rotation 2s infinite linear}
            </style>
        <?php }
        
        /**
		 *  Popup Js Code
         * 
		 */
         public function tpgb_deact_popup_js() { ?>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function() {
                    'use strict';

                    // Modal Cancel Click Action
                    document.addEventListener('click', function(e) {
                        var modal = document.getElementById('tpgb-deactive-modal');
                        if (e.target === modal) {
                            modal.classList.remove('modal-active');
                        }
                    });

                    document.addEventListener('keydown', function(e) {
                        var modal = document.getElementById('tpgb-deactive-modal');
                        if (e.keyCode === 27) {
                            modal.classList.remove('modal-active');
                        }
                    });

                    // Deactivate Button Click Action
                    document.getElementById('deactivate-the-plus-addons-for-block-editor').addEventListener('click', function(e) {
                        e.preventDefault();
                        var modal = document.getElementById('tpgb-deactive-modal');
                        modal.classList.add('modal-active');
                        var href = this.getAttribute('href');
                        document.querySelector('.tpgb-modal-deactive').setAttribute('href', href);
                        document.querySelector('.tpgb-modal-submit').setAttribute('href', href);
                    });

                    // Submit to Remote Server
                    document.addEventListener('click', function(e) {
                        if (e.target.classList.contains('tpgb-modal-submit')) {
                            e.preventDefault();
                            var submitButton = e.target;
                            var url = submitButton.getAttribute('href');
                            submitButton.textContent = '';
                            submitButton.classList.add('tpgb-loading');

                            var formObj = document.getElementById('tpgb-deactive-modal').querySelector('form.tpgb-feedback-dialog-form');
                            var formData = new FormData(formObj);

                            var ajaxData = 'action=tpgb_deactive_plugin' +
                                '&nonce=' + formData.get('nonce') +
                                '&deactreson=' + formData.get('tpgb-reason');

                            if (formData.get('tpgb-reason-txt') && formData.get('tpgb-reason-txt') !== '') {
                                ajaxData += '&tprestxt=' + formData.get('tpgb-reason-txt');
                            }

                            var request = new XMLHttpRequest();
                            request.open('POST', "<?php echo esc_url(admin_url('admin-ajax.php')); ?>", true);
                            request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;');
                            request.onload = function () {
                                if (request.status >= 200 && request.status < 400) {
                                    document.getElementById('tpgb-deactive-modal').classList.remove('modal-active');
                                    window.location.href = url;
                                }
                            };
                            request.send( ajaxData );
                        }
                    });

                    document.addEventListener('click', function(e) {
                        if (e.target.classList.contains('tpgb-modal-deactive')) {
                            e.preventDefault();
                            var url = e.target.getAttribute('href');
                            var formObj = document.getElementById('tpgb-deactive-modal').querySelector('form.tpgb-feedback-dialog-form');
                            var formData = new FormData(formObj);

                            var request = new XMLHttpRequest();
                            request.open('POST', "<?php echo esc_url(admin_url('admin-ajax.php')); ?>", true);
                            request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;');
                            request.onload = function () {
                                if (request.status >= 200 && request.status < 400) {
                                    window.location.href = url;
                                }
                            };
                            request.send( 'action=tpgb_skip_deactivate' + '&nonce=' + formData.get('nonce')  );
                        }
                    });
                });
		    </script>
        <?php }

         /**
		 *  Deactive Plugin API Call
         * 
		 */
        public function tpgb_deactive_plugin(){
           
            $nonce = ! empty( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

			if ( ! isset( $nonce ) || empty( $nonce ) || ! wp_verify_nonce( $nonce, 'tpgb-deactivate-feedback' ) ) {
				die( 'Security checked!' );
			}

            if ( ! current_user_can( 'activate_plugins' ) ) {
                wp_send_json_error( 'Permission denied' );
            }

            $deavtive_url = 'https://api.posimyth.com/wp-json/tpag/v2/tpgb_deactivate_user_data';

			$deactreson = ! empty( $_POST['deactreson'] ) ? sanitize_text_field( wp_unslash( $_POST['deactreson'] ) ) : '';
			$tprestxt =  isset( $_POST['tprestxt'] ) && !empty( $_POST['tprestxt'] ) ? sanitize_text_field( wp_unslash( $_POST['tprestxt'] ) ) : '';

			$api_params = array(
				'site_url'    => esc_url( home_url() ),
				'reason_key'  => $deactreson,
				'reason_text' => $tprestxt,
                'tpgb_version' => TPGB_VERSION,
			);

			$response = wp_remote_post( 
                $deavtive_url,
				array(
					'timeout'   => 30,
					'sslverify' => false,
					'body'      => $api_params,
				)
            );
            
            if (is_wp_error($response)) {
				echo wp_send_json([ 'deactivated' => false ]);
			} else {
				echo wp_send_json([ 'deactivated' => true ]);
			}

			wp_die();
        }

        /**
		 *  Deactive Plugin API Call
         * 
		 */
        public function tpgb_skip_deactivate(){
           
            check_ajax_referer( 'tpgb-deactivate-feedback', 'nonce' );

            if ( ! current_user_can( 'activate_plugins' ) ) {
                wp_send_json_error( 'Permission denied' );
            }

			$response = wp_remote_post(
				'https://api.posimyth.com/wp-json/tpag/v2/tpgb_deactivate_user_count',
				array(
                    'timeout' => 30,
					'body'    => array(),
					'headers' => array(
						'Content-Type' => 'application/x-www-form-urlencoded',
					),
				)
			);

			wp_die();
        }
    }

    Tpag_Deactive::get_instance();
}