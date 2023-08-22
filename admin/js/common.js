jQuery(document).ready(function ($) {
  $(".upload_category_image_button").click(function (event) {
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

      $(".category-image").prop("src", attachment.attributes.url);
      $("#category_image").val(attachment.attributes.id);
    });

    frame.open();
  });

  $(".remove_category_image_button").click(function () {
    $(".category-image").attr("src", cmme.default_image);
    $("#category_image").val("");
    $(this)
      .parent()
      .siblings(".title")
      .children("img")
      .attr("src", cmme.default_image);

    return false;
  });
});
