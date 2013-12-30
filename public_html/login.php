<?php
  require_once('includes/app_header.inc.php');
  
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  if (!empty(customer::$data['id'])) notices::add('notice', language::translate('text_already_logged_in', 'You are already logged in'));
  
  if (!empty($_POST['login'])) customer::login($_POST['email'], $_POST['password'], $_POST['redirect_url']);

  if (!empty($_POST['lost_password'])) customer::password_reset($_POST['email']);
  
?>
 
<div class="box">
  <div class="heading"><h1><?php echo language::translate('title_login', 'Login'); ?></h1></div>
  <div class="content">
    <?php echo functions::form_draw_form_begin('login_form', 'post'); ?>
    <?php echo functions::form_draw_hidden_field('redirect_url', !empty($_GET['redirect_url']) ? $_GET['redirect_url'] : document::link(WS_DIR_HTTP_HOME)); ?>
      <table>
        <tr>
          <td><?php echo language::translate('title_email_address', 'E-mail Address'); ?> <span class="required">*</span><br />
            <?php echo functions::form_draw_text_field('email', true); ?></td>
        </tr>
        <tr>
          <td><?php echo language::translate('title_password', 'Password'); ?> <span class="required">*</span><br />
          <?php echo functions::form_draw_password_field('password', ''); ?></td>
        </tr>
        <tr>
          <td><?php echo functions::form_draw_button('login', language::translate('title_login', 'Login')); ?> &nbsp; <?php echo functions::form_draw_button('lost_password', language::translate('title_lost_password', 'Lost Password')); ?> </td>
        </tr>
        <tr>
          <td><a href="<?php echo document::href_link(WS_DIR_HTTP_HOME . 'create_account.php'); ?>"><?php echo language::translate('text_new_customers_click_here', 'New customers click here'); ?></a></td>
        </tr>
    </table>
    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<?php
  if (!in_array(__FILE__, array_slice(get_included_files(), 1))) {
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
  }
?>