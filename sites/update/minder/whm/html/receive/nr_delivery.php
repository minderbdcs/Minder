<html>
    <head>
        <title>Delivery</title>
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

            function pageLoaded() {
                var shippedDate = document.getElementById('shipped_date');
                shippedDate.onclick = updateUI;
                var grnNo = document.getElementById('grn_no');
                grnNo.onchange = updateUI;

                updateUI();
            }

            function updateUI() {
                var shippedDate = document.getElementById('shipped_date');
                var shippedDay = document.getElementById('shipped_day');
                var shippedMonth = document.getElementById('shipped_month');
                var shippedYear = document.getElementById('shipped_year');
                var grnNo = document.getElementById('grn_no');
                var orderNo = document.getElementById('order_no');
                var sentBy = document.getElementById('sent_by');
                var carrier = document.getElementById('carrier');
                var vehReg = document.getElementById('veh_reg');
                var ownedBy = document.getElementById('owned_by');
                if (grnNo.value == '') {
                    if (!shippedDate.checked) {
                        shippedDay.disabled = true;
                        shippedMonth.disabled = true;
                        shippedYear.disabled = true;
                    } else {
                        shippedDay.disabled = false;
                        shippedMonth.disabled = false;
                        shippedYear.disabled = false;
                    }
                    shippedDate.disabled = false;
                    orderNo.disabled = false;
                    sentBy.disabled = false;
                    carrier.disabled = false;
                    vehReg.disabled = false;
                    ownedBy.disabled = false;
                } else {
                    shippedDate.disabled = true;
                    shippedDay.disabled = true;
                    shippedMonth.disabled = true;
                    shippedYear.disabled = true;
                    orderNo.disabled = true;
                    sentBy.disabled = true;
                    carrier.disabled = true;
                    vehReg.disabled = true;
                    ownedBy.disabled = true;
                }
            }

            function formSubmitted() {
                // If the user clicked on back then check no further
                if (buttonClicked == 'back') {
                    return true;
                }
                // We'll use the GRN to lookup details
                if (document.forms[0].grn_no.value != '') {
                    return true;
                }
                var vehReg = document.forms[0].veh_reg.value;
                if (vehReg == '') {
                    window.alert('Please enter a vehical registration number');
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
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return formSubmitted()">
            <?php htmlInputHidden('page', '1'); ?>

            <?php htmlInputHidden('type', $type); ?>

            <?php htmlInputHidden('lot_no', $lot_no); ?>

            <div>Type: <span id="type"><?php echo htmlentities($typeName, ENT_QUOTES); ?></type></div>
            <div>GRN No: <?php htmlInputText('grn_no', ''); ?> <?php htmlSelect('printer_id', null, $printerOpts); ?></div>
            <div>Order No: <?php htmlInputText('order_no', ''); ?></div>
            <!-- <div>Sent By: <?php htmlSelect('sent_by', null, $sentByOpts); ?></div> -->
            <div>Sent By: <?php htmlSelect('sent_by', $sentById, $sentByOpts); ?></div>
            <div>Carrier: <?php htmlSelect('carrier', null, $carrierOpts); ?></div>
            <div>Veh. Reg: <?php htmlInputText('veh_reg', ''); ?></div>
<?php $today = getdate(); ?>
            <div>Shipped Date: <?php htmlInputCheckbox('shipped_date', 'y'); ?> <?php htmlSelect('shipped_day', $today['mday'], $dayOpts); ?> <?php htmlSelect('shipped_month', $today['mon'], $monthOpts); ?> <?php htmlSelect('shipped_year', $today['year'], $yearOpts); ?></div>
            <div>Owned By:</div>
            <div><?php htmlSelect('owned_by', $ownedById, $ownedByOpts); ?></div>
            <div id="actions"><input type="image" name="action_back" src="/icons/whm/Back_50x100.gif" onclick="return actionBack()" /><input type="image" name="action_continue" src="/icons/whm/Continue_50x100.gif" "return actionContinue()"/></div>
        </form>
    </body>
</html>
