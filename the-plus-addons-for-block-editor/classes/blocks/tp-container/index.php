<?php
/* Block : Container(Section)
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_container_render_callback( $attributes, $content) {
	$output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
    $height = (!empty($attributes['height'])) ? $attributes['height'] : '';
    $customClass = (!empty($attributes['customClass'])) ? $attributes['customClass'] : '';
	
	$customId = (!empty($attributes['customId'])) ? 'id="'.esc_attr($attributes['customId']).'"' : ( isset($attributes['anchor']) && !empty($attributes['anchor']) ? 'id="'.esc_attr($attributes['anchor']).'"'  : '' ) ;

	$wrapLink = (!empty($attributes['wrapLink'])) ? $attributes['wrapLink'] : false;

	$showchild = (!empty($attributes['showchild'])) ? $attributes['showchild'] : false;
	$contentWidth = (!empty($attributes['contentWidth'])) ? $attributes['contentWidth'] : 'wide';
	$colDir = (!empty($attributes['colDir'])) ? $attributes['colDir'] : '';
	$tagName = (!empty($attributes['tagName'])) ? $attributes['tagName'] : '';
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$sectionClass = '';
	if( !empty( $height ) ){
		$sectionClass .= ' tpgb-section-height-'.esc_attr($height);
	}
	
	// Toogle Class For wrapper Link

	$linkdata = '';
	if(!empty($wrapLink)){
		$rowUrl = (!empty($attributes['rowUrl'])) ? $attributes['rowUrl'] : '';
		$sectionClass .= ' tpgb-row-link';
		
		if( !empty($rowUrl) && isset($rowUrl['url']) && !empty($rowUrl['url']) ){
			$linkdata .= 'data-tpgb-row-link="'.esc_url($rowUrl['url']).'" ';
		}
		if(!empty($rowUrl) && isset($rowUrl['target']) && !empty($rowUrl['target'])){
			$linkdata .= 'data-target="_blank" ';
		}else{
			$linkdata .= 'data-target="_self" ';
		}
		$linkdata .= Tp_Blocks_Helper::add_link_attributes($attributes['rowUrl']);
	}

	$output .= '<'.Tp_Blocks_Helper::validate_html_tag($tagName).' '.$customId.' class="tpgb-container-row tpgb-block-'.esc_attr($block_id).' '.esc_attr($sectionClass).' '.esc_attr($customClass).' '.esc_attr($blockClass).' '.($colDir == 'c100' || $colDir == 'r100' ? ' tpgb-container-inline' : '').'  tpgb-container-'.esc_attr($contentWidth).' " data-id="'.esc_attr($block_id).'" '.$linkdata.' >';
	if($contentWidth=='wide'){
		$output .= '<div class="tpgb-cont-in">';
	}
		$output .= $content;
	if($contentWidth=='wide'){
		$output .= '</div>';
	}
	$output .= "</".Tp_Blocks_Helper::validate_html_tag($tagName).">";
	
	
	if ( class_exists( 'Tpgb_Blocks_Global_Options' ) ) {
		$global_block = Tpgb_Blocks_Global_Options::get_instance();
		if ( !empty($global_block) && is_callable( array( $global_block, 'block_row_conditional_render' ) ) ) {
			$output = Tpgb_Blocks_Global_Options::block_row_conditional_render($attributes, $output);
		}
	}
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_container_row() {
	
	$displayRules = [];
	if ( class_exists( 'Tpgb_Display_Conditions_Rules' ) ) {
		$display_Conditions = Tpgb_Display_Conditions_Rules::get_instance();
		if ( !empty($display_Conditions) && is_callable( array( $display_Conditions, 'tpgb_display_option' ) ) ) {
			$displayRules = Tpgb_Display_Conditions_Rules::tpgb_display_option();
		}
	}
	
	$attributesOptions = [
			'block_id' => [
                'type' => 'string',
				'default' => '',
			],
			'anchor' => array(
				'type' => 'string',
			),
			'className' => [
				'type' => 'string',
				'default' => '',
			],
			'columns' => [
                'type' => 'number',
				'default' => '',
			],
			'contentWidth' => [
				'type' => 'string',
				'default' => 'wide',
			],
			'align' => [
				'type' => 'string',
				'default' => 'wide',
			],
			'containerWide' => [
				'type' => 'object',
				'default' => [ 
					'md' => '',
					"unit" => '%',
				],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in{ --content-width : {{containerWide}};}',
					],
				],
			],
			'contwidFull' => [
				'type' => 'string',
				'default' => '',
			],
			'containerFull' => [
				'type' => 'object',
				'default' => [ 
					'md' => 100,
					"unit" => 'vw',
				],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_CLIENT_ID}}:not(.tpgb-container-row-editor){ max-width : {{containerFull}}  !important;}',
						'backend' => true,
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => 'body {{PLUS_WRAP}}.alignfull.tpgb-container-full.tpgb-container-row{ max-width : {{containerFull}} !important;}',
						'backend' => false,
					],
				],
			],
			'colDir' => [
				'type' => 'string',
				'default' => '',
			],
			'sectionWidth' => [
				'type' => 'string',
				'default' => 'boxed',	
			],
			'height' => [
				'type' => 'string',
				'default' => '',	
			],
			'minHeight' => [
				'type' => 'object',
				'default' => [ 
					'md' => 300,
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'height', 'relation' => '==', 'value' => 'min-height'],(object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in { min-height: {{minHeight}};}',
					],
					(object) [
						'condition' => [ (object) ['key' => 'height', 'relation' => '==', 'value' => 'min-height'],(object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}{ min-height: {{minHeight}};}',
					],
					(object) [
						'condition' => [ (object) ['key' => 'height', 'relation' => '==', 'value' => 'min-height'] , (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => ' {{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ min-height: {{minHeight}};}',
						'backend' => true,
					],
					(object) [
						'condition' => [ (object) ['key' => 'height', 'relation' => '==', 'value' => 'min-height'] , (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}} > .block-editor-block-list__layout { min-height: {{minHeight}}; }',
						'backend' => false,
					],
				],
			],
			'gutterSpace' => [
				'type' => 'object',
				'default' => [ 
					'md' => 15,	
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout > [data-type="tpgb/tp-container-inner"]> .components-resizable-box__container > .tpgb-container-col-editor{ padding: {{gutterSpace}}; }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .tpgb-container-col{ padding: {{gutterSpace}}; }',
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}} > .block-editor-block-list__layout > [data-type="tpgb/tp-container-inner"]> .components-resizable-box__container > .tpgb-container-col-editor{ padding: {{gutterSpace}}; }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-container-col{ padding: {{gutterSpace}}; }',
					],
				],
			],
			'tagName' => [
                'type' => 'string',
				'default' => 'div',
			],
			'overflow' => [
                'type' => 'string',
				'default' => '',
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}{ overflow: {{overflow}}; }',
					],
				],
			],
			'customClass' => [
				'type' => 'string',
				'default' => '',	
			],
			'customId' => [
				'type' => 'string',
				'default' => '',	
			],
			'customCss' => [
				'type' => 'string',
				'default' => '',
				'style' => [
					(object) [
						'selector' => '',
					],
				],
			],
			
			'shapeTop' => [
                'type' => 'string',
				'default' => '',
				'scopy' => true,
			],
			
			'shapeBottom' => [
                'type' => 'string',
				'default' => '',
				'scopy' => true,
			],
			
			'NormalBg' => [
				'type' => 'object',
				'default' => (object) [
					'openBg'=> 0,
					'bgType' => '',
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}',
					],
				],
				'scopy' => true,
			],
			'HoverBg' => [
				'type' => 'object',
				'default' => (object) [
					'openBg'=> 0,
					'bgType' => '',
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}:hover',
					],
				],
				'scopy' => true,
			],
			'NormalBorder' => [
				'type' => 'object',
				'default' => (object) [
					'openBorder' => 0,
					'type' => '',
					'color' => '',
					'width' => (object) [
						'md' => [
							"top" => '',
							'bottom' => '',
							'left' => '',
							'right' => '',
						],
						"unit" => "",
					],
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}',
					],
				],
				'scopy' => true,
			],
			'HoverBorder' => [
				'type' => 'object',
				'default' => (object) [
					'openBorder' => 0,
					'type' => '',
					'color' => '',
					'width' => (object) [
						'md' => [
							"top" => '',
							'bottom' => '',
							'left' => '',
							'right' => '',
						],
						"unit" => "",
					],
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}:hover',
					],
				],
				'scopy' => true,
			],
			'NormalBradius' => [
				'type' => 'object',
				'default' => (object) [ 
					'md' => [
						"top" => '',
						'bottom' => '',
						'left' => '',
						'right' => '',
					],
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}},{{PLUS_WRAP}} .tpgb-row-background{ border-radius: {{NormalBradius}}; }',
					],
				],
				'scopy' => true,
			],
			'HoverBradius' => [
				'type' => 'object',
				'default' => (object) [ 
					'md' => [
						"top" => '',
						'bottom' => '',
						'left' => '',
						'right' => '',
					],
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}:hover,{{PLUS_WRAP}}:hover .tpgb-row-background{ border-radius: {{HoverBradius}}; }',
					],
				],
				'scopy' => true,
			],
			'NormalBShadow' => [
				'type' => 'object',
				'default' => (object) [
					'openShadow' => 0,
					'inset' => 0,
					'horizontal' => 0,
					'vertical' => 4,
					'blur' => 8,
					'spread' => 0,
					'color' => "rgba(0,0,0,0.40)",
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}',
					],
				],
				'scopy' => true,
			],
			'HoverBShadow' => [
				'type' => 'object',
				'default' => (object) [
					'openShadow' => 0,
					'inset' => 0,
					'horizontal' => 0,
					'vertical' => 4,
					'blur' => 8,
					'spread' => 0,
					'color' => "rgba(0,0,0,0.40)",
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}:hover',
					],
				],
				'scopy' => true,
			],
			'Margin' => [
				'type' => 'object',
				'default' => (object) [ 
					'md' => [
						"top" => '',
						'bottom' => '',
						'left' => '',
						'right' => '',
					],
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}{margin: {{Margin}} !important; }',
					],
				],
				'scopy' => true,
			],
			'Padding' => [
				'type' => 'object',
				'default' => (object) [ 
					'md' => [
						"top" => '',
						'bottom' => '',
						'left' => '',
						'right' => '',
					],
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}}{ padding-left: {{LEFT}}{{Padding}} } {{PLUS_WRAP}}{ padding-right: {{RIGHT}}{{Padding}} } {{PLUS_WRAP}} > .tpgb-cont-in{ padding-top: {{TOP}}{{Padding}} } {{PLUS_WRAP}} > .tpgb-cont-in{ padding-bottom: {{BOTTOM}}{{Padding}} }',
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}{ padding : {{Padding}} }',
					],
				],
				'scopy' => true,
			],
			'ZIndex' => [
				'type' => 'string',
				'default' => '',
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}{z-index: {{ZIndex}};}',
					],
				],
				'scopy' => true,
			],
			
			'HideDesktop' => [
				'type' => 'boolean',
				'default' => false,
				'style' => [
					(object) [
						'selector' => '@media (min-width: 1201px){ .edit-post-visual-editor {{PLUS_WRAP}},.editor-styles-wrapper {{PLUS_WRAP}}{display: block;opacity: .5;}}',
						'backend' => true
					],
					(object) [
						'selector' => '@media (min-width: 1201px){ {{PLUS_WRAP}}{ display:none } }',
					],
				],
				'scopy' => true,
			],
			'HideTablet' => [
				'type' => 'boolean',
				'default' => false,
				'style' => [
					(object) [
						'selector' => '@media (min-width: 768px) and (max-width: 1200px){ .edit-post-visual-editor {{PLUS_WRAP}},.editor-styles-wrapper {{PLUS_WRAP}}{display: block;opacity: .5;} }',
						'backend' => true
					],
					(object) [
						'selector' => '@media (min-width: 768px) and (max-width: 1200px){  {{PLUS_WRAP}}{ display:none } }',
					],
				],
				'scopy' => true,
			],
			'HideMobile' => [
				'type' => 'boolean',
				'default' => false,
				'style' => [
					(object) [
						'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width: 767px){ .edit-post-visual-editor {{PLUS_WRAP}},.editor-styles-wrapper {{PLUS_WRAP}}{display: block !important;opacity: .5;} }',
						'backend' => true
					],
					(object) [
						'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width: 767px){ {{PLUS_WRAP}}{ display:none !important; } }',
					],
				],
				'scopy' => true,
			],
		
			'wrapLink' => [
				'type' => 'boolean',
				'default' => false,
			],
			'rowUrl' => [
				'type'=> 'object',
				'default'=> [
					'url' => '',
					'target' => '',
					'nofollow' => ''
				],
			],
			
			// Flex Css
			'flexreverse' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'flexRespreverse' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'flexTabreverse' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'flexMobreverse' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'flexDirection' => [
				'type' => 'object',
				'default' => [ 'md' => '', 'sm' =>  '', 'xs' =>  'column' ],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'flexreverse', 'relation' => '==', 'value' => false]],
						'selector' => '{{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in { flex-direction: {{flexDirection}} }',
						'media' => 'md',
					],
					(object) [
						'condition' => [ (object) ['key' => 'flexreverse', 'relation' => '==', 'value' => false]],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout, {{PLUS_WRAP}} > .block-editor-block-list__layout{ flex-direction: {{flexDirection}} }',
						'media' => 'md',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'flexreverse', 'relation' => '==', 'value' => true]],
						'selector' => '{{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in { flex-direction: {{flexDirection}}-reverse }',
						'media' => 'md',
					],
					(object) [
						'condition' => [ (object) ['key' => 'flexreverse', 'relation' => '==', 'value' => true]],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout,{{PLUS_WRAP}} > .block-editor-block-list__layout{ flex-direction: {{flexDirection}}-reverse }',
						'media' => 'md',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => false ]],
						'selector' => '@media (max-width: 1024px) { {{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in { flex-direction: {{flexDirection}} } }' ,
						'media' => 'sm',
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => false ]],
						'selector' => '@media (max-width: 1024px) { {{PLUS_WRAP}} > .block-editor-block-list__layout,{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ flex-direction: {{flexDirection}} } }' ,
						'media' => 'sm',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => true ],
							(object) ['key' => 'flexTabreverse', 'relation' => '==', 'value' => false] 
						],
						'selector' => '@media (max-width: 1024px) and (min-width:768px) { {{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in { flex-direction: {{flexDirection}} } }' ,
						'media' => 'sm',
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => true ],
							(object) ['key' => 'flexTabreverse', 'relation' => '==', 'value' => false] 
						],
						'selector' => '@media (max-width: 1024px) and (min-width:768px) { {{PLUS_WRAP}} > .block-editor-block-list__layout,{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ flex-direction: {{flexDirection}} } }' ,
						'media' => 'sm',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => true ],
							(object) ['key' => 'flexTabreverse', 'relation' => '==', 'value' => true] 
						],
						'selector' => '@media (max-width: 1024px) and (min-width:768px) { {{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in { flex-direction: {{flexDirection}}-reverse  } }',
						'media' => 'sm',
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => true ],
							(object) ['key' => 'flexTabreverse', 'relation' => '==', 'value' => true] 
						],
						'selector' => '@media (max-width: 1024px) and (min-width:768px) { {{PLUS_WRAP}} > .block-editor-block-list__layout,{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ flex-direction: {{flexDirection}}-reverse  } }',
						'media' => 'sm',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => false ]],
						'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width: 767px) { {{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in{ flex-direction: {{flexDirection}} } }',
						'media' => 'xs',
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => false ]],
						'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width: 767px) { {{PLUS_WRAP}} > .block-editor-block-list__layout,{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ flex-direction: {{flexDirection}} } }',
						'media' => 'xs',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => true ],
							(object) ['key' => 'flexMobreverse', 'relation' => '==', 'value' => false] 
						],
						'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width: 767px) { {{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in{ flex-direction: {{flexDirection}} } }',
						'media' => 'xs',
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => true ],
							(object) ['key' => 'flexMobreverse', 'relation' => '==', 'value' => false] 
						],
						'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width: 767px) { {{PLUS_WRAP}} > .block-editor-block-list__layout,{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ flex-direction: {{flexDirection}} } }',
						'media' => 'xs',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => true ], 
							(object) ['key' => 'flexMobreverse', 'relation' => '==', 'value' => true] 
						],
						'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width: 767px){ {{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in { flex-direction: {{flexDirection}}-reverse  } }',
						'media' => 'xs',
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => true ], 
							(object) ['key' => 'flexMobreverse', 'relation' => '==', 'value' => true] 
						],
						'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width: 767px){ {{PLUS_WRAP}} > .block-editor-block-list__layout,{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ flex-direction: {{flexDirection}}-reverse  } }',
						'media' => 'xs',
						'backend' => true
					],
				],
				'scopy' => true,
			],
			'flexAlign' => [
				'type' => 'object',
				'default' => [ 'md' => '', 'sm' =>  '', 'xs' =>  '' ],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}} > .block-editor-block-list__layout,{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ align-items : {{flexAlign}} }',
						'backend' => true
					],
					(object) [
						'selector' => '{{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in{ align-items : {{flexAlign}} }',
					],
				],
				'scopy' => true,
			],
			'flexJustify' => [
				'type' => 'object',
				'default' => [ 'md' => '', 'sm' =>  '', 'xs' =>  '' ],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}} > .block-editor-block-list__layout,{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ justify-content : {{flexJustify}} }',
						'backend' => true
					],
					(object) [
						'selector' => '{{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in{ justify-content : {{flexJustify}} }',
					],
				],
				'scopy' => true,
			],
			'flexGap' => [
				'type' => 'object',
				'default' => [ 
					'md' => '',
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ gap : {{flexGap}} }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in{ gap : {{flexGap}} }',
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}} > .block-editor-block-list__layout{ gap : {{flexGap}} }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}{ gap : {{flexGap}} }',
					],
				],
				'scopy' => true,
			],
			'flexwrap' => [
				'type' => 'object',
				'default' => [ 'md' => '', 'sm' =>  '', 'xs' =>  'wrap' ],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'reverseWrap', 'relation' => '==', 'value' => false],  ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ flex-wrap : {{flexwrap}} }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'reverseWrap', 'relation' => '==', 'value' => false],  ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in{ flex-wrap : {{flexwrap}} }',
					],
					(object) [
						'condition' => [ (object) ['key' => 'reverseWrap', 'relation' => '==', 'value' => false], ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row-editor > .block-editor-block-list__layout{ flex-wrap : {{flexwrap}} }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'reverseWrap', 'relation' => '==', 'value' => false], ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}{ flex-wrap : {{flexwrap}} }',
					],
					(object) [
						'condition' => [ (object) ['key' => 'reverseWrap', 'relation' => '==', 'value' => true], ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ flex-wrap : {{flexwrap}}-reverse }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'reverseWrap', 'relation' => '==', 'value' => true], ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in{ flex-wrap : {{flexwrap}}-reverse }',
					],
					(object) [
						'condition' => [ (object) ['key' => 'reverseWrap', 'relation' => '==', 'value' => true], ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}} > .block-editor-block-list__layout{ flex-wrap : {{flexwrap}}-reverse }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'reverseWrap', 'relation' => '==', 'value' => true], ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}{ flex-wrap : {{flexwrap}}-reverse }',
					],
				],
				'scopy' => true,
			],
			'alignWrap' => [
				'type' => 'object',
				'default' => [ 'md' => 'flex-end', 'sm' =>  '', 'xs' =>  '' ],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ align-content : {{alignWrap}} }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in{ align-content : {{alignWrap}} }',
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}} > .block-editor-block-list__layout{ align-content : {{alignWrap}} }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}{ align-content : {{alignWrap}} }',
					],
				],
				'scopy' => true,
			],
			'reverseWrap' => [
				'type' => 'boolean',
				'default' => false,
			],
			// child Css
			'flexChild' => [
				'type'=> 'array',
				'repeaterField' => [
					(object) [
						'flexShrink' => [
							'type' => 'object',
							'default' => [ 
								'md' => '',
							],
							'style' => [
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > .tpgb-cont-in > *:nth-child({{TP_INDEX}}){ flex-shrink : {{flexShrink}} }',
								],
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > *:nth-child({{TP_INDEX}}){ flex-shrink : {{flexShrink}} }',
								],
							],
							'scopy' => true,
						],
						'flexGrow' => [
							'type' => 'object',
							'default' => [ 
								'md' => '',
							],
							'style' => [
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > .tpgb-cont-in > *:nth-child({{TP_INDEX}}){ flex-grow : {{flexGrow}} }',
								],
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > *:nth-child({{TP_INDEX}}){ flex-grow : {{flexGrow}} }',
								],
							],
							'scopy' => true,
						],
						'flexBasis' => [
							'type' => 'object',
							'default' => [ 
								'md' => '',
								"unit" => '%',
							],
							'style' => [
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > .tpgb-cont-in > *:nth-child({{TP_INDEX}}){ flex-basis : {{flexBasis}} }{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout > *:nth-child({{TP_INDEX}}) > *{width:100% !important}',
								],
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > *:nth-child({{TP_INDEX}}){ flex-basis : {{flexBasis}} }{{PLUS_WRAP}} > .block-editor-block-list__layout > *:nth-child({{TP_INDEX}}) > *{width:100% !important}',
								],
							],
							'scopy' => true,
						],
						'flexselfAlign' => [
							'type' => 'object',
							'default' => [ 'md' => 'auto', 'sm' =>  '', 'xs' =>  '' ],
							'style' => [
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > .tpgb-cont-in > *:nth-child({{TP_INDEX}}){ align-self : {{flexselfAlign}} }',
								],
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > *:nth-child({{TP_INDEX}}){ align-self : {{flexselfAlign}} }',
								],
							],
							'scopy' => true,
						],
						'flexOrder' => [
							'type' => 'object',
							'default' => [ 'md' => '', 'sm' =>  '', 'xs' =>  '' ],
							'style' => [
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > .tpgb-cont-in > *:nth-child({{TP_INDEX}}){ order : {{flexOrder}} }',
								],
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > *:nth-child({{TP_INDEX}}){ order : {{flexOrder}} }',
								],
							],
							'scopy' => true,
						],
					],
				],
				'default' => [
					[ '_key'=> 'cvi9', 'flexShrink' => [ 'md' => '' ] , 'flexGrow' => [ 'md' => '' ], 'flexBasis' => [ 'md' => '' ] ,'flexselfAlign' => [ 'md' => '' ] ,'flexOrder' => [ 'md' => '' ] ],
				],
			],
			'showchild' => [
				'type' => 'boolean',
				'default' => false,
			],
			'conPosi' => [
				'type' => 'object',
				'default' => [ 'md' => '','sm' => '','xs' => '' ],
				'style' => [
					(object) [
						'selector' => '{{PLUS_CLIENT_ID}}:not(.tpgb-container-row-editor){ position : {{conPosi}}; }',
						'backend' => true,
					],
					(object) [
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row { position : {{conPosi}}; }',
						'backend' => false,
					],
				],
				'scopy' => true,
			],
			'conhorizoOri' => [
				'type' => 'object',
				'default' => [ 'md' => 'left', 'sm' =>  '', 'xs' =>  '' ]
			],
			'conhoriOffset' => [
				'type' => 'object',
				'default' =>[ 
					'md' => '0',
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'condition' => [
							(object) [ 'key' => 'conPosi', 'relation' => '==', 'value' => ['absolute' , 'fixed'] ],
							(object) [ 'key' => 'conhorizoOri', 'relation' => '==', 'value' => 'left' ]
						],
						'selector' => '{{PLUS_CLIENT_ID}}:not(.tpgb-container-row-editor){ left : {{conhoriOffset}};right : auto; }',
						'backend' => true,
					],
					(object) [
						'condition' => [
							(object) [ 'key' => 'conPosi', 'relation' => '==', 'value' => ['absolute' , 'fixed'] ],
							(object) [ 'key' => 'conhorizoOri', 'relation' => '==', 'value' => 'left' ],
						],
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row { left : {{conhoriOffset}};right : auto; }',
						'backend' => false,
					],
					(object) [
						'condition' => [
							(object) [ 'key' => 'conPosi', 'relation' => '==', 'value' => ['absolute' , 'fixed'] ],
							(object) [ 'key' => 'conhorizoOri', 'relation' => '==', 'value' => 'right' ]
						],
						'selector' => '{{PLUS_CLIENT_ID}}:not(.tpgb-container-row-editor){ right : {{conhoriOffset}};left : auto; }',
						'backend' => true,
					],
					(object) [
						'condition' => [
							(object) [ 'key' => 'conPosi', 'relation' => '==', 'value' => ['absolute' , 'fixed'] ],
							(object) [ 'key' => 'conhorizoOri', 'relation' => '==', 'value' => 'right' ]
						],
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row { right : {{conhoriOffset}};left : auto; }',
						'backend' => false,
					],
				],
			],
			'conabverticalOri' => [
				'type' => 'object',
				'default' => [ 'md' => 'top', 'sm' =>  '', 'xs' =>  '' ]
			],
			'converticalOffset' => [
				'type' => 'object',
				'default' => [ 
					'md' => '0',
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'condition' => [
							(object) [ 'key' => 'conPosi', 'relation' => '==', 'value' => ['absolute' , 'fixed'] ],
							(object) [ 'key' => 'conabverticalOri', 'relation' => '==', 'value' => 'top' ]
						],
						'selector' => '{{PLUS_CLIENT_ID}}:not(.tpgb-container-row-editor){ top : {{converticalOffset}}; bottom : auto; }',
						'backend' => true,
					],
					(object) [
						'condition' => [
							(object) [ 'key' => 'conPosi', 'relation' => '==', 'value' => ['absolute' , 'fixed'] ],
							(object) [ 'key' => 'conabverticalOri', 'relation' => '==', 'value' => 'top' ]
						],
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row { top : {{converticalOffset}}; bottom : auto; }',
						'backend' => false,
					],
					(object) [
						'condition' => [
							(object) [ 'key' => 'conPosi', 'relation' => '==', 'value' => ['absolute' , 'fixed'] ],
							(object) [ 'key' => 'conabverticalOri', 'relation' => '==', 'value' => 'bottom' ]
						],
						'selector' => '{{PLUS_CLIENT_ID}}:not(.tpgb-container-row-editor){ bottom : {{converticalOffset}}; top : auto; }',
						'backend' => true,
					],
					(object) [
						'condition' => [
							(object) [ 'key' => 'conPosi', 'relation' => '==', 'value' => ['absolute' , 'fixed'] ],
							(object) [ 'key' => 'conabverticalOri', 'relation' => '==', 'value' => 'bottom' ]
						],
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row { bottom : {{converticalOffset}}; top : auto; }',
						'backend' => false,
					],
				],
			],
 		];
		
	$attributesOptions = array_merge( $attributesOptions, $displayRules );
	
	register_block_type( 'tpgb/tp-container', array(
		'attributes' => $attributesOptions,
		'editor_script' => 'tpgb-block-editor-js',
		'editor_style'  => 'tpgb-block-editor-css',
        'render_callback' => 'tpgb_tp_container_render_callback'
    ) );
}
add_action( 'init', 'tpgb_tp_container_row' );