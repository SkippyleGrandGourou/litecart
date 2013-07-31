<?php
  
  if (isset($_GET['language_code'])) {
    $language = new ctrl_language($_GET['language_code']);
    if (!$_POST) {
      foreach ($language->data as $key => $value) {
        $_POST[$key] = $value;
      }
    }
  } else {
    $language = new ctrl_language();
  }

  if (!empty($_POST['save'])) {
    
    if (empty($_POST['code'])) $system->notices->add('errors', $system->language->translate('error_must_enter_code', 'You must enter a code'));
    if (empty($_POST['name'])) $system->notices->add('errors', $system->language->translate('error_must_enter_name', 'You must enter a name'));
    
    if (empty($_POST['status']) && isset($language->data['code']) && $language->data['code'] == $system->settings->get('default_language_code')) {
      $system->notices->add('errors', $system->language->translate('error_cannot_disable_default_language', 'You must change the default language before disabling it.'));
    }
    
    if (empty($_POST['status']) && isset($language->data['code']) && $language->data['code'] == $system->settings->get('store_language_code')) {
      $system->notices->add('errors', $system->language->translate('error_cannot_disable_store_language', 'You must change the store language before disabling it.'));
    }
    
    if (empty($_POST['set_default']) && isset($language->data['code']) && $language->data['code'] == $system->settings->get('default_language_code') && $language->data['code'] != $_POST['code']) {
      $system->notices->add('errors', $system->language->translate('error_cannot_rename_default_language', 'You must change the default language before renaming it.'));
    }
    
    if (empty($_POST['set_store']) && isset($language->data['code']) && $language->data['code'] == $system->settings->get('store_language_code') && $language->data['code'] != $_POST['code']) {
      $system->notices->add('errors', $system->language->translate('error_cannot_rename_store_language', 'You must change the store language before renaming it.'));
    }
    
    if (!empty($_POST['set_default']) && empty($_POST['status']) && isset($language->data['code']) && $language->data['code'] == $system->settings->get('default_language_code')) {
      $system->notices->add('errors', $system->language->translate('error_cannot_set_disabled_default_language', 'You cannot set a disabled language as default language.'));
    }
    
    if (!empty($_POST['set_store']) && empty($_POST['status']) && isset($language->data['code']) && $language->data['code'] == $system->settings->get('store_language_code')) {
      $system->notices->add('errors', $system->language->translate('error_cannot_set_disabled_store_language', 'You cannot set a disabled language as store language.'));
    }
    
    if (empty($system->notices->data['errors'])) {
      
      $_POST['raw_datetime'] = $_POST['raw_date'] .' '. $_POST['raw_time'];
      $_POST['format_datetime'] = $_POST['format_date'] .' '. $_POST['format_time'];
      
      $fields = array(
        'status',
        'code',
        'name',
        'charset',
        'locale',
        'raw_date',
        'raw_time',
        'raw_datetime',
        'format_date',
        'format_time',
        'format_datetime',
        'decimal_point',
        'thousands_sep',
        'currency_code',
        'priority',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $language->data[$field] = $_POST[$field];
      }
      
      $language->save();
      
      if (!empty($_POST['set_default'])) {
        $system->database->query("update ". DB_TABLE_SETTINGS ." set `value` = '". $system->database->input($_POST['code']) ."' where `key` = 'default_language_code' limit 1;");
      }
      
      if (!empty($_POST['set_store'])) {
        $system->database->query("update ". DB_TABLE_SETTINGS ." set `value` = '". $system->database->input($_POST['code']) ."' where `key` = 'store_language_code' limit 1;");
      }
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes were successfully saved.'));
      header('Location: '. $system->document->link('', array('doc' => 'languages'), true, array('action', 'language_code')));
      exit;
    }
  }
  
  if (!empty($_POST['delete'])) {
    
    if ($language->data['code'] == 'en') {
      $system->notices->add('errors', $system->language->translate('error_cannot_delete_framework_language', 'You cannot delete the PHP framework language. But you can disable it.'));
    }

    if ($language->data['code'] == $system->settings->get('default_language_code')) {
      $system->notices->add('errors', $system->language->translate('error_cannot_delete_default_language', 'You must change the default language before it can be deleted.'));
    }
    
    if ($language->data['code'] == $system->settings->get('store_language_code')) {
      $system->notices->add('errors', $system->language->translate('error_cannot_delete_store_language', 'You must change the store language before it can be deleted.'));
    }
    
    if (empty($system->notices->data['errors'])) {
      
      $language->delete();
    
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes were successfully saved.'));
      header('Location: '. $system->document->link('', array('doc' => 'languages'), true, array('action', 'language_code')));
      exit;
    }
  }

?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo (isset($language->data['id'])) ? $system->language->translate('title_edit_language', 'Edit Language') : $system->language->translate('title_add_new_language', 'Add New Language'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('', 'post'); ?>
  <table>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_status', 'Status'); ?></strong><br />
        <?php echo $system->functions->form_draw_radio_button('status', '1', isset($_POST['status']) ? $_POST['status'] : '1'); ?> <?php echo $system->language->translate('title_enabled', 'Enabled'); ?>
        <?php echo $system->functions->form_draw_radio_button('status', '0', isset($_POST['status']) ? $_POST['status'] : '1'); ?> <?php echo $system->language->translate('title_disabled', 'Disabled'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_code', 'Code'); ?> (ISO 639-1)</strong><br />
        <?php echo $system->functions->form_draw_text_field('code', true, 'data-size="tiny"'); ?> <a href="http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes" target="_blank"><?php echo $system->language->translate('title_reference', 'Reference'); ?></a>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_name', 'Name'); ?></strong><br />
        <?php echo $system->functions->form_draw_text_field('name', true); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_charset', 'Charset'); ?></strong><br />
        <?php echo $system->functions->form_draw_text_field('charset', !isset($_POST['charset']) ? 'UTF-8' : true, 'placeholder="UTF-8" data-size="small"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_system_locale', 'System Locale'); ?></strong><br />
        <?php echo $system->functions->form_draw_text_field('locale', true, 'placeholder="xx_XX.utf8" data-size="small"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_date_format', 'Date Format'); ?></strong> (<a href="http://php.net/manual/en/function.strftime.php" target="_blank">?</a>)<br />
        <?php echo $system->functions->form_draw_text_field('format_date', true, 'placeholder="%e %b %Y" data-size="small"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_time_format', 'Time Format'); ?></strong> (<a href="http://php.net/manual/en/function.strftime.php" target="_blank">?</a>)<br />
        <?php echo $system->functions->form_draw_text_field('format_time', true, 'placeholder="%I:%M %p" data-size="small"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_raw_date_format', 'Raw Date Format'); ?></strong> (<a href="http://php.net/manual/en/function.date.php" target="_blank">?</a>)<br />
        <?php echo $system->functions->form_draw_text_field('raw_date', true, 'placeholder="m/d/y" data-size="small"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_raw_time_format', 'Raw Time Format'); ?></strong> (<a href="http://php.net/manual/en/function.date.php" target="_blank">?</a>)<br />
        <?php echo $system->functions->form_draw_text_field('raw_time', true, 'placeholder="h:i:s a" data-size="small"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_decimal_point', 'Decimal Point'); ?></strong><br />
        <?php echo $system->functions->form_draw_text_field('decimal_point', true, 'placeholder="." data-size="tiny"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_thousands_sep', 'Thousands Separator'); ?></strong><br />
        <?php echo $system->functions->form_draw_text_field('thousands_sep', true, 'data-size="tiny"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_force_currency_code', 'Force Currency Code'); ?></strong><br />
        <?php echo $system->functions->form_draw_text_field('currency_code', true, 'data-size="tiny"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_priority', 'Priority'); ?></strong><br />
        <?php echo $system->functions->form_draw_number_field('priority', true); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap">
        <?php echo $system->functions->form_draw_checkbox('set_default', '1', (isset($language->data['code']) && $language->data['code'] && $language->data['code'] == $system->settings->get('default_language_code')) ? '1' : true); ?> <?php echo $system->language->translate('description_set_as_default_language', 'Set as default language'); ?><br />
        <?php echo $system->functions->form_draw_checkbox('set_store', '1', (isset($language->data['code']) && $language->data['code'] && $language->data['code'] == $system->settings->get('store_language_code')) ? '1' : true); ?> <?php echo $system->language->translate('description_set_as_store_language', 'Set as store language'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($language->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></td>
    </tr>
  </table>
  
<?php echo $system->functions->form_draw_form_end(); ?>