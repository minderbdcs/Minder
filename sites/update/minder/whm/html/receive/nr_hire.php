<html>
    <head>
        <title>Hire</title>
        <link rel="stylesheet" type="text/css" href="newreceive.css" media="all" />
        <script type="text/javascript">
            var buttonClicked = '';

            function actionBack() {
                buttonClicked = 'back';
                return true;
            }

            function actionComment() {
                buttonClicked = 'comment';
                return true;
            }

            function actionAccept() {
                buttonClicked = 'accept';
                return true;
            }

            function pageLoaded() {
                var hirePallets = document.getElementById('hire_pallets');
                hirePallets.onchange = updateUI;

                updateUI();
            }

            function updateUI() {
                var hirePallets = document.getElementById('hire_pallets');
                var hireQty = document.getElementById('hire_qty');
                var i = document.forms[0].hire_pallets.options.selectedIndex;
                var selected = document.forms[0].hire_pallets.options[i].value;
                if (selected == 'N') {
                    hireQty.disabled = true;
                } else {
                    hireQty.disabled = false;
                }
            }

            function formSubmitted() {
                // If the user clicked on back then check no further
                if (document.forms[0].action_back.x != '') {
                    return true;
                }
                var i = document.forms[0].hire_pallets.options.selectedIndex;
                var selected = document.forms[0].hire_pallets.options[i].value;
                if (selected != 'N' && document.forms[0].hire_qty.value == '') {
                    window.alert('Please enter the number of hired pallets');
                    return false;
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
            <?php htmlInputHidden('page', '3'); ?>

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

            <?php htmlInputHidden('container', $container); ?>

            <?php htmlInputHidden('container_no', $container_no); ?>

            <?php htmlInputHidden('container_type', $container_type); ?>

            <?php htmlInputHidden('docket_no', $docket_no); ?>

            <?php htmlInputHidden('pkgs', $pkgs); ?>

            <?php htmlInputHidden('damaged', $damaged); ?>


            <div>Type: <span id="type"><?php echo htmlentities($typeName, ENT_QUOTES); ?></type></div>
            <div>Hire Pallets: <?php htmlSelect('hire_pallets', null, $hirePalletOpts); ?> <?php htmlInputText('hire_qty', '1'); ?> </div>
            <div>Hire Packaging: <?php htmlSelect('hire_packaging', null, $hirePackagingOpts); ?></div>
            <div>Hire Packaging Type: <?php htmlSelect('hire_packaging_type', null, $hirePackagingTypeOpts); ?></div>
            <div>Packaging Crate Qty: <?php htmlInputText('packaging_crate_qty', ''); ?></div>
            <div id="actions"><input type="image" name="action_back" src="/icons/whm/Back_50x100.gif" onclick="return actionBack()"/> <input type="image" name="action_comment" src="/icons/whm/comment.gif" onclick="return actionComment()" /> <input type="image" name="action_accept" src="/icons/whm/accept.gif" onclick="return actionAccept()" /></div>
        </form>

    </body>
</html>
