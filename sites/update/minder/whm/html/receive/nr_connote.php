<html>
    <head>
        <title>Connote</title>
        <link rel="stylesheet" type="text/css" href="newreceive.css" media="all" />
        <script type="text/javascript">
            var buttonClicked = '';

            function actionBack() {
                buttonClicked = 'back';
                return true;
            }

            function actionContinue() {
                buttonClicked = 'continue';
                return true;
            }

            function actionAccept() {
                buttonClicked = 'accept';
                return true;
            }

            function pageLoaded() {
                var containerY = document.getElementById('container_y');
                var containerN = document.getElementById('container_n');
                if (document.forms[0].container_no.value == '') {
                    containerN.checked = true;
                } else {
                    containerY.checked = true;
                }
                containerY.onchange = updateUI;
                containerN.onchange = updateUI;

                updateUI();
            }

            function updateUI() {
                var containerN = document.getElementById('container_n');
                var containerNo = document.getElementById('container_no');
                var containerType = document.getElementById('container_type');
                if (containerN.checked) {
                    containerType.disabled = true;
                    containerNo.disabled = true;
                } else {
                    containerType.disabled = false;
                    containerNo.disabled = false;
                }
            }

            function formSubmitted() {
                // If the user clicked on back then check no further
                if (buttonClicked == 'back') {
                    return true;
                }
                var docketNo = document.forms[0].docket_no.value;
                if (document.forms[0].docket_no.value == '') {
                    window.alert('Please enter a Consignment / AWB / Del. Docket No.');
                    return false;
                }
                if (document.forms[0].container[1].checked) {
                    if (document.forms[0].container_no.value == '') {
                        window.alert('Please enter a Container No.');
                        return false;
                    }
                }
                return true;
            }
        </script>
    </head>
    <body onload="pageLoaded()">
<?php
if ($message != '') {
    echo '<p>' . htmlentities($message, ENT_QUOTES) . '</p>';
}
if (!empty($errors)) {
    echo '<p>The following errors were found:</p>';
    echo '<ul>';
    foreach ($errors as $error) {
         echo '<li>' . $error . '</li>';
    }
    echo '</ul>';
}
?>
        <form action="<?php echo htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES); ?>" method="post" onsubmit="return formSubmitted()">
            <?php htmlInputHidden('page', '2'); ?>

            <?php htmlInputHidden('type', $type); ?>

            <?php htmlInputHidden('grn_no', $grn_no); ?>

            <?php htmlInputHidden('printer_id', $printer_id); ?>

            <?php htmlInputHidden('order_no', $order_no); ?>

            <?php htmlInputHidden('lot_no', $lot_no); ?>

            <?php htmlInputHidden('carrier', $carrier); ?>

            <?php htmlInputHidden('veh_reg', $veh_reg); ?>

            <?php htmlInputHidden('sent_by', $sent_by); ?>

            <?php htmlInputHidden('shipped_date', $shipped_date); ?>

            <?php htmlInputHidden('shipped_day', $shipped_day); ?>

            <?php htmlInputHidden('shipped_month', $shipped_month); ?>

            <?php htmlInputHidden('shipped_year', $shipped_year); ?>

            <?php htmlInputHidden('owned_by', $owned_by); ?>

            <?php htmlInputHidden('hire_pallets', 'N'); ?>

            <?php htmlInputHidden('hire_qty', ''); ?>

            <?php htmlInputHidden('hire_packaging', ''); ?>

            <div>Type: <span id="type"><?php echo htmlentities($typeName, ENT_QUOTES); ?></type></div>
            <div>Shipping Container: <?php htmlInputRadio('container', 'n', array('id' => 'container_n')); ?> No <?php htmlInputRadio('container', 'y', array('id' => 'container_y')); ?> Yes</div>
            <div><?php htmlInputText('container_no', ''); ?> <?php htmlSelect('container_type', null, $containerTypeOpts); ?></div>
            <div>Consignment / AWB / Del. Docket No.</div>
            <div><?php htmlInputText('docket_no', ''); ?></div>
            <div>Received: <?php htmlInputText('pkgs', '1'); ?> Packages</div>
            <div>Damaged Flag: <?php htmlInputCheckbox('damaged', 'y'); ?></div>
            <!-- <div id="actions"><input type="image" name="action_back" src="/icons/whm/Back_50x100.gif" onclick="return actionBack()"/> <input type="image" name="action_accept" src="/icons/whm/accept.gif" onclick="return actionAccept()"/> <input type="image" name="action_continue" src="/icons/whm/Continue_50x100.gif" onsubmit="return actionContinue()"/></div> -->
            <div id="actions"><input type="image" name="action_back" src="/icons/whm/Back_50x100.gif" onclick="return actionBack()"/> <input type="image" name="action_accept" src="/icons/whm/accept.gif" onclick="return actionAccept()"/> <input type="image" name="action_continue" src="/icons/whm/hire.gif" onsubmit="return actionContinue()"/></div>
        </form>

    </body>
</html>
