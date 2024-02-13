<?php
/**
 * The file that defines the plugin permalinks class.
 *
 * @link https://charliemerland.me/
 * @package Roxane
 */

namespace roxane\registrars;

use roxane\traits\Singleton;

/**
 * Define the permalinks class.
 *
 * @since 1.0.0
 * @author Charlie Merland <charlie@caercam.org>
 */
class Permalinks {

	use Singleton;

  public function series_rewrite_rules( $rules ) {

    $new_rules = [];

    foreach ( $rules as $rule => $rewrite ) {
      // Series, no season.
      if ( false === strpos( $rule, '([^/]+)' ) ) {
        $rule = str_replace( '%parent%', '([^/]+)', $rule );
        $rewrite = str_replace( '%parent%', 'series=', $rewrite );
      }
      // Series and season.
      elseif ( 0 === strpos( $rule, 'series/%parent%/' ) ) {
        $rule = str_replace( '%parent%', '([^/]+)', $rule );
        $rewrite = str_replace(
          [
            '%parent%$matches[1]&',
            'series=$matches[2]'
          ], [
            'series=$matches[1]-$matches[2]',
          ],
          $rewrite
        );
      }
      $new_rules[ $rule ] = $rewrite;
    }

    print_r( $new_rules );

    return $new_rules;
  }
}