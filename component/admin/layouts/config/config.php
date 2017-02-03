<?php
/**
 * @package     RedSHOP.Backend
 * @subpackage  Template
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * =========================
 *
 * @var  array   $displayData List of data
 * @var  string  $title       Title of this config field
 * @var  string  $desc        Description of this config field.
 * @var  string  $field       HTML content of field.
 * @var  boolean $line        True for show line.
 * @var  string  $showOn      JS condition for display this field.
 * @var  string  $id          DOM ID of this field.
 */
extract($displayData);

$line = (isset($line)) ? $line : true;
$id   = (isset($id)) ? $id : '';
?>
<?php if (!empty($showOn)): ?>
	<?php
	$showOn     = explode(':', $showOn);
	$fieldName  = $showOn[0];
	$fieldValue = $showOn[1];
	?>
    <script type="text/javascript">
        (function ($) {
            $(document).ready(function () {
                if ($('input[name="<?php echo $fieldName ?>"]').length) {
                    var inputType = $('input[name="<?php echo $fieldName ?>"]').attr('type');
                    var inputVal  = '';

                    if (inputType == "radio") {
                        inputVal = $('input[name="<?php echo $fieldName ?>"]:checked').val();
                    }
                    else {
                        inputVal = $('input[name="<?php echo $fieldName ?>"]').val();
                    }

                    if (inputVal == "<?php echo $fieldValue ?>") {
                        $("#<?php echo $id ?>-wrapper").show().next("hr").show();
                    }
                    else {
                        $("#<?php echo $id ?>-wrapper").hide().next("hr").hide();
                    }

                    $('input[name="<?php echo $fieldName ?>"]').on('change', function (event) {
                        if ($(this).val() == "<?php echo $fieldValue ?>") {
                            $("#<?php echo $id ?>-wrapper").show().next("hr").show();
                        }
                        else {
                            $("#<?php echo $id ?>-wrapper").hide().next("hr").hide();
                        }
                    });
                }
            });
        })(jQuery);
    </script>
<?php endif; ?>
<div class="row" id="<?php echo !empty($id) ? $id . '-wrapper' : '' ?>">
    <div class="form-group">
        <label class="col-md-4 hasPopover" data-content="<?php echo $desc ?>"><?php echo $title ?></label>
        <div class="col-md-8"><?php echo $field ?></div>
    </div>
</div>
<?php if ($line): ?>
    <hr/>
<?php endif; ?>
