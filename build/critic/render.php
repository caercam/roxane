
    <div <?php echo get_block_wrapper_attributes( [ 'id' => $attributes['anchor'] ?? $attributes['id'] ?? '' ] ); ?>>
<?php if ( ! empty( $attributes['poster'] ) ) : ?> 
        <img class="critic-background" src="<?php echo esc_url( $attributes['poster'] ); ?>" alt="" />   
<?php elseif ( has_post_thumbnail() ) : ?>
        <?php the_post_thumbnail( 'large', [ 'class' => 'critic-background' ] ); ?>
<?php endif; ?>
        <div class="critic">
            <div class="critic-header">
                <div class="movie-title"><?php echo $attributes['title'] ?? ''; ?></div>
                <div class="movie-rating"><?php echo \roxane\support\get_the_rating(); ?></div>
                <div class="movie-details">
                    <div class="director"><?php echo $attributes['director'] ?? ''; ?></div>
                    <div class="year"><?php echo $attributes['year'] ?? ''; ?></div>
                </div>
            </div>
            <div class="critic-content">
<?php foreach ( $block->inner_blocks as $inner_block ) : ?>
                <?php echo $inner_block->render(); ?>
<?php endforeach; ?>
            </div>
        </div>
    </div>