<?php if ( isset( $tmdb_id ) ) : ?>
    <tr class="form-field term-image-wrap">
			<th scope="row"><label for="series_tmdb_id">ID TMDb</label></th>
			<td>
				<input type="text" name="meta_input[series_tmdb_id]" id="series_tmdb_id" value="<?php echo esc_attr( $tmdb_id ?? '' ); ?>" />
			</td>
		</tr>
<?php else : ?>
    <div class="form-field term-tmdb-id-wrap">
      <label for="series_tmdb_id">ID TMDb</label>
      <input type="text" name="meta_input[series_tmdb_id]" id="series_tmdb_id" value="" />
    </div>
<?php endif; ?>