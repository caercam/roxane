<div class="wrap">
<?php if ( $updated ) : ?>
  <div class="notice notice-success is-dismissible">
    <p>API Key updated successfully.</p>
  </div>
<?php endif; ?>
  <h1>TMDb Settings</h1>
  <form method="POST" action="">
    <table class="form-table" role="presentation">
      <tbody>
        <tr>
          <th scope="row"><label for="tmdb_api_key">TMDB API Key</label></th>
          <td><input name="tmdb_api_key" type="text" id="tmdb_api_key" value="<?php echo get_option( 'tmdb_api_key', '' ); ?>" class="regular-text"></td>
        </tr>
      </tbody>
    </table>
    <div class="edit-tag-actions">
      <input type="submit" class="button button-primary" value="Enregistrer les modifications" />
      <?php wp_nonce_field( 'update-tmdb-api-key' ); ?>
    </div>
  </form>
</div>