<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$bg_attr = '';
$thumbnail_id = get_post_thumbnail_id(get_the_ID());
$featured_image = wp_get_attachment_url($thumbnail_id);

if (!empty($featured_image) && !empty($layout) && $layout == 'metro') {
    $bg_attr = 'style="background: url(' . esc_url($featured_image) . '); background-size: cover; background-repeat: no-repeat; background-position: center;"';
}
?>
<div class="dynamic-list-content tpgb-dynamic-tran">

    <div class="tpgb-post-featured-img tpgb-dynamic-tran <?php echo esc_attr($imageHoverStyle); ?>">
        <a href="<?php echo esc_url(get_the_permalink()); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">

            <?php if (!empty($layout) && $layout == 'metro') { ?>
                <div class="tpgb-blog-image-metro" <?php echo $bg_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped inside $bg_attr. ?>></div>
            <?php } else { ?>
                <?php if (!empty($featured_image)) { ?>
                    <img class="tpgb-blog-image" src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy" />
                <?php } ?>
            <?php } ?>

        </a>
    </div>

    <!-- Category Section -->
    <?php if ($showPostCategory == 'yes') { ?>
        <?php include TPGB_INCLUDES_URL . 'blog/' . sanitize_file_name('category-' . $postCategoryStyle . '.php'); ?>
    <?php } ?>

    <!-- Content Bottom -->
    <div class="tpgb-content-bottom">
        <?php if (!empty($showPostMeta) && $showPostMeta == 'yes') { ?>
            <?php include TPGB_INCLUDES_URL . 'blog/' . sanitize_file_name('post-meta-' . $postMetaStyle . '.php'); ?>
        <?php } ?>

        <?php if (!empty($ShowTitle) && $ShowTitle == 'yes') {
            include TPGB_INCLUDES_URL . 'blog/post-title.php';
        } ?>

        <div class="tpgb-post-hover-content">
            <?php if (!empty($showExcerpt) && $showExcerpt == 'yes' && get_the_excerpt()) {
                include TPGB_INCLUDES_URL . 'blog/get-excerpt.php';
            } ?>
        </div>

        <?php if(!empty($ShowButton) && $ShowButton == 'yes') { ?>
            <div class="tpgb-adv-button button-<?php echo esc_attr($postBtnsty); ?>"> 
                <a class="button-link-wrap" href="<?php echo esc_url(get_the_permalink()); ?>" > 
                    <?php 
                        if($postBtnsty == 'style-8'){
                            if($btnIconPosi == 'before'){
                    ?>
                            <span class="btn-icon  button-<?php echo esc_attr($btnIconPosi); ?>"> 
                                <i class="<?php echo esc_attr($pobtnIconName); ?>" > </i>
                            </span>
                            <?php echo esc_html($postbtntext); ?>
                    <?php
                        }else{
                    ?>
                            <?php echo esc_html($postbtntext); ?>
                            <span class="btn-icon  button-<?php echo esc_attr($btnIconPosi); ?>"> 
                                <i class="<?php echo esc_attr($pobtnIconName); ?>"> </i>
                            </span>
                    <?php 			
                            }
                        }else if( $postBtnsty == 'style-7' || $postBtnsty == 'style-9' ){
                            echo esc_html($postbtntext);
                    ?>
                        <span class='button-arrow'> 
                            <?php if($postBtnsty == 'style-7') { ?> 
                                <span class='btn-right-arrow'><i class="fas fa-chevron-right"></i></span>  
                            <?php }  if($postBtnsty == 'style-9') { ?>
                                <i class="btn-show fas fa-chevron-right"></i>
                                <i class="btn-hide fas fa-chevron-right"></i>
                            <?php } ?>
                        </span>
                    <?php
                        }
                    ?>
                </a>
            </div>
        <?php } ?>
    </div>
</div>
