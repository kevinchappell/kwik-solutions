jQuery(document).ready(function($) {

  $('#sortable-table tbody').sortable({
    axis: 'y',
    handle: '.column-order img',
    placeholder: 'ui-state-highlight',
    forcePlaceholderSize: true,
    update: function(event, ui) {
      var theOrder = $(this).sortable('toArray');

      var data = {
        action: 'solutions_update_post_order',
        postType: $(this).attr('data-post-type'),
        order: theOrder
      };

      $.post(ajaxurl, data);
    }
  }).disableSelection();

  var $iconSelect = $(document.getElementById('kwik-solutions-settings-dash-icon-icon-select')),
		  $imgPrev = $iconSelect.parent().siblings('.icon_preview');

  $imgPrev.attr('class', 'icon_preview '+$iconSelect.val());

  $iconSelect.change(function(){
  	$imgPrev.attr('class', 'icon_preview '+$(this).val());
  });

  $('input[name="kwik_solutions_settings[name_plural]"]').keyup(function(){
  	$('.wp-menu-name', '#menu-posts-solutions').html($(this).val());
  });

  $('input[name="kwik_solutions_settings[name]"]').keyup(function(){
  	$('a[href="post-new.php?post_type=solutions"]', '#menu-posts-solutions').html($(this).val());
  });



});
