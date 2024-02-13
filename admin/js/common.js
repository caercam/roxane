jQuery(document).ready(function ($) {
  $(".upload_term_image_button").click(function (event) {
    event.preventDefault();

    var upload_button = $(this),
      frame;
    if (frame) {
      frame.open();
      return;
    }

    frame = wp.media();

    frame.on("select", function () {
      // Grab the selected attachment.
      var attachment = frame.state().get("selection").first();

      frame.close();

      $(".term-image").prop("src", attachment.attributes.url);
      $("#term_image").val(attachment.attributes.id);
    });

    frame.open();
  });

  $(".remove_term_image_button").click(function () {
    $(".term-image").attr("src", cmme.default_image);
    $("#term_image").val("");
    $(this)
      .parent()
      .siblings(".title")
      .children("img")
      .attr("src", cmme.default_image);

    return false;
  });

  $('.edit-tags-php #submit').click(function() {
    $(".term-image").attr("src", cmme.default_image);
  });
});
