<div id="box-search" class="box">
  <div class="content">
    <?php echo functions::form_draw_form_begin('search_form', 'get', document::ilink('search')); ?>
      <div class="input-wrapper">&nbsp;<img src="{snippet:template_path}images/search.png" width="12" height="12" alt="" style="vertical-align: middle;" /><?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"'); ?></div>
    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>