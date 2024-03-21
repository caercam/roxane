
    <div <?php echo get_block_wrapper_attributes( [ 'id' => $attributes['anchor'] ?? $attributes['id'] ?? '' ] ); ?>>
<?php if ( ! empty( $attributes['movie']['poster_path'] ) ) : ?> 
        <img class="critic-background" src="<?php echo esc_url( 'https://image.tmdb.org/t/p/w220_and_h330_face' . $attributes['movie']['poster_path'] ); ?>" alt="" />   
<?php elseif ( has_post_thumbnail() ) : ?>
        <?php the_post_thumbnail( 'large', [ 'class' => 'critic-background' ] ); ?>
<?php endif; ?>
        <div class="critic">
            <div class="critic-header">
                <div class="movie-title"><?php echo $attributes['movie']['title'] ?? ''; ?></div>
                <div class="movie-rating"><?php echo \roxane\support\get_the_rating(); ?></div>
                <div class="movie-details">
                    <div class="director"><?php echo $attributes['movie']['director'] ?? ''; ?></div>
                    <div class="year"><?php echo date( 'Y', strtotime( $attributes['movie']['release_date'] ?? '' ) ); ?></div>
                </div>
            </div>
            <div class="critic-content">
<?php foreach ( $block->inner_blocks as $inner_block ) : ?>
                <?php echo $inner_block->render(); ?>
<?php endforeach; ?>
            </div>
        </div>
    </div>