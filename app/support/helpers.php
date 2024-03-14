<?php
/**
 * The file that defines the plugin helper functions.
 *
 * @link https://charliemerland.me/
 * @package Roxane
 */

namespace roxane\support;

/**
 * Retrieves the rating for a given post.
 *
 * @since 2.2.0
 *
 * @param int|WP_Post|null $post Optional. Post ID or WP_Post object. Default is global $post.
 * @return string Returns HTML for displaying the rating stars.
 */
function get_the_rating( $post = null ) {

  // Retrieve the post object
  $post = get_post( $post );
  
  // Check if post exists
  if ( ! $post ) {
      return '';
  }

  // Retrieve the rating terms associated with the post
  $rating = get_the_terms( $post, 'rating' );

  // If no rating terms found or it's a WP_Error, return empty string
  if ( ! $rating || ! count( $rating ) || is_wp_error( $rating ) ) {
      return '';
  }

  // Initialize variables for rating calculation
  $none = 0;
  $half = 0;
  $full = 0;

  // Extract the first rating term
  $rating = array_shift( $rating );

  // Determine the number of full, half, and empty stars based on the rating term
  switch ( $rating->name ) {
      case '1.0':
      case '2.0':
      case '3.0':
      case '4.0':
      case '5.0':
          $full = intval( $rating->name );
          $half = 0;
          $none = 5 - $full;
          break;
      case '0.5':
      case '1.5':
      case '2.5':
      case '3.5':
      case '4.5':
          $full = intval( $rating->name );
          $half = 1;
          $none = 4 - $full;
          break;
      default:
          $rating = ''; // If rating doesn't match, set it to empty
          break;
  }

  $stars = str_repeat( times: $full, string: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20"/><g><path d="M10 1l3 6 6 .75-4.12 4.62L16 19l-6-3-6 3 1.13-6.63L1 7.75 7 7z"/></g></svg>' )
      . str_repeat( times: $half, string: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20"/><g><path d="M10 1L7 7l-6 .75 4.13 4.62L4 19l6-3 6 3-1.12-6.63L19 7.75 13 7zm0 2.24l2.34 4.69 4.65.58-3.18 3.56.87 5.15L10 14.88V3.24z"/></g></svg>' )
      . str_repeat( times: $none, string: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20"/><g><path d="M10 1L7 7l-6 .75 4.13 4.62L4 19l6-3 6 3-1.12-6.63L19 7.75 13 7zm0 2.24l2.34 4.69 4.65.58-3.18 3.56.87 5.15L10 14.88l-4.68 2.34.87-5.15-3.18-3.56 4.65-.58z"/></g></svg>' );

  // Return the HTML with rating stars and title
  return '<span class="stars" title="' . $rating->name . ' − ' . $rating->description . '">' . $stars . '</span>';
}