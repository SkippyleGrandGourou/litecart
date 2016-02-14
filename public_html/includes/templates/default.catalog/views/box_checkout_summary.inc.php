<div id="checkout-summary">
  <h2><?php echo language::translate('title_order_summary', 'Order Summary'); ?></h2>
  
  <div id="order_confirmation-wrapper">
  
    <table class="table table-striped table-bordered data-table">

      <tbody>
        <?php foreach ($order_total as $row) { ?>
        <tr>
          <td colspan="5" style="text-align: right;"><strong><?php echo $row['title']; ?>:</strong></td>
          <td style="text-align: right;"><?php echo $row['value']; ?></td>
        </tr>
        <?php } ?>
      
        <?php if ($tax_total) { ?>
        <tr>
          <td colspan="5" style="text-align: right; color: #999999;"><?php echo $incl_excl_tax; ?>:</td>
          <td style="text-align: right; color: #999999;"><?php echo $tax_total; ?></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <tr class="footer">
          <td colspan="5" style="text-align: right;"><strong><?php echo language::translate('title_payment_due', 'Payment Due'); ?>:</strong></td>
          <td style="text-align: right;"><strong><?php echo $payment_due; ?></strong></td>
        </tr>
      </tfoot>
    </table>
    
    <?php echo functions::form_draw_form_begin('order_form', 'post', document::ilink('order_process'));  ?>
      <div class="comments form-group">
        <label><?php echo language::translate('title_comments', 'Comments'); ?></label>
        <?php echo functions::form_draw_textarea('comments', true); ?>
      </div>
      
      <div class="confirm row">
        <div class="col-md-9">
          <?php if ($error) echo '<div class="alert alert-warning">'. $error .'</div>' . PHP_EOL; ?>
        </div>
        
        <div class="col-md-3">
          <?php //echo functions::form_draw_button('confirm_order', $confirm, 'submit', 'style="width: 100%;"' . (!empty($error) ? ' disabled="disabled"' : '')); ?>
          <button class="btn btn-block btn-lg btn-default btn-success" name="confirm_order" value="true"<?php echo !empty($error) ? ' disabled="disabled"' : ''; ?>><?php echo $confirm; ?></button>
        </div>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>