jQuery(document).ready(function ($) {
  $(".upload-term-image-button").click(function (event) {
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

  $(".remove-term-image-button").click(function () {
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

  if ('edit-series' === pagenow && 'edit-tags-php' === adminpage ) {

    var apiKey = roxaneOptions?.tmdb_api_key ?? null;

    $('#the-list tr.level-0').each(function() {

      var $action = $('<span class="new-season"> | </span>'),
          $button = $('<button class="button-link">Nouvelle saison</button>');

      $button.prependTo($action);
      $(this).find('.row-actions .delete').before($action);

      $button.on('click', function(event) {

        event.preventDefault();

        var $tr = $(this).closest('tr'),
             id = $tr.attr('id').replace('tag-', ''),
          title = $tr.find('.row-title').text(),
           slug = $tr.find('.slug.column-slug').text(),
        tmdb_id = $tr.find('.tmdb_id.column-tmdb_id').text();

        var $editor = $(`<tr id="edit-${id}" class="inline-edit-row inline-editor"><td colspan="${$(this).closest('table').find('thead th').length + 1}" class="colspanchange"><div class="inline-edit-wrapper"><fieldset><legend class="inline-edit-legend">Ajouter une nouvelle saison</legend><div class="inline-edit-col"><label><span class="title">Numéro</span><span class="input-text-wrap"></span></label></fieldset><div class="inline-edit-save submit"></div></div></td></tr>`);

        var $input = $('<input type="number" min="1" max="999" step="1" class="ptitle" value="1">'),
             $save = $('<button type="button" class="save button button-primary">Créer la saison</button>'),
           $cancel = $('<button type="button" class="cancel button">Annuler</button>');

        $editor.find('.input-text-wrap').append($input);
        $editor.find('.inline-edit-save').append($save).append($cancel);

        $tr.hide();
        $tr.after($editor);

        $save.on('click', function() {
          var season = $input.val();
          $('#tag-name').val(`${title} − Saison ${season}`);
          $('#tag-slug').val(`${slug}-saison-${season}`);
          $('#parent').val(id);
          $('#tag-description').val(`Saison ${season} de ${title}`);
          $tr.show();
          $editor.remove();

          if (apiKey && tmdb_id) {
            fetch(`https://api.themoviedb.org/3/tv/${tmdb_id}?api_key=${apiKey}&language=fr-FR`)
              .then(response => response.json())
              .then(data => {
                $('#term_image').val(data.backdrop_path);
                $('.term-image').attr('src', `https://image.tmdb.org/t/p/w780${data.backdrop_path}`);
              });

            var $backdropPicker = $('.backdrop-picker');
            if (!$backdropPicker.length) {
              var $backdropPicker = $(`<div style="display:none">
                <div class="media-modal">
                  <button type="button" class="media-modal-close" style="z-index:9999999;"><span class="media-modal-icon"></span></button>
                  <div class="media-modal-content" style="z-index:9999998;">
                    <div class="media-frame mode-select wp-core-ui hide-menu">
                      <div class="media-frame-title" id="media-frame-title"><h1>Choisir une image</h1></div>
                      <div class="media-frame-content">
                        <div class="backdrop-picker"></div>
                      </div>
                    </div>
                  </div>
                  <div class="media-modal-backdrop"></div>
                </div>`),
                    $pickerButton = $('<button type="button" class="button" style="margin-left:10px">Choisir une autre image</button>');

              $('.upload-term-image-button').after($backdropPicker);
              $('.upload-term-image-button').after($pickerButton);

              $backdropPicker.find('.media-modal-close').on('click', function(event) {
                event.preventDefault();
                $backdropPicker.hide();
              });

              $pickerButton.on('click', function(event) {
                event.preventDefault();

                if (!$backdropPicker.find('.backdrop-picker>*').length) {
                  fetch(`https://api.themoviedb.org/3/tv/${tmdb_id}/images?api_key=${apiKey}`)
                    .then(response => response.json())
                    .then(data => {
                      data.backdrops?.forEach(backdrop => {
                        var $backdrop = $(`<button type="button" class="button-link"><img src="https://image.tmdb.org/t/p/w300${backdrop.file_path}"/></button>`);
                        $backdropPicker.find('.backdrop-picker').append($backdrop);
                        $backdrop.on('click', function(event) {
                          event.preventDefault();
                          $('#term_image').val(backdrop.file_path);
                          $('.term-image').attr('src', `https://image.tmdb.org/t/p/w780${backdrop.file_path}`);
                          $backdropPicker.hide();
                        });
                      });
                    });
                }

                $backdropPicker.show();
              });
            }
          }
        });

        $cancel.on('click', function() {
          $editor.remove();
          $tr.show();
        });
      });
    });

    if (apiKey) {
      $('.term-name-wrap').after('<div class="tmdb-search-results"></div>');
      $('#tag-name').on('change', function() {
        fetch(`https://api.themoviedb.org/3/search/tv?api_key=${apiKey}&query=${$(this).val()}&language=fr-FR`)
          .then(response => response.json())
          .then(data => {
            $('.tmdb-search-results').empty();
            data.results?.forEach(result => {
              var $result = $('<a></a>');
              $result.prop('href', `https://www.themoviedb.org/tv/${result.id}`);
              $result.addClass('tmdb-search-result');
              $result.append(`<img src="https://image.tmdb.org/t/p/w154${result.poster_path}" alt="${result.name}">`);
              $result.append(`<div class="details"><span class="title">${result.name}</span><span class="year">${ new Date( result.first_air_date ).getFullYear() }</span></div>`);
              $result.appendTo('.tmdb-search-results');
              $result.on('click', function(event) {
                event.preventDefault();
                $('#tag-name').val( result.name );
                $('#tag-description').val( result.overview );
                $('#series_tmdb_id').val( result.id );
                $('#term_image').val(result.backdrop_path);
                $('.term-image').attr('src', `https://image.tmdb.org/t/p/w780${result.backdrop_path}`);
                $('.tmdb-search-results').empty();
              });
            });
          });
      });
    }
  }
});
