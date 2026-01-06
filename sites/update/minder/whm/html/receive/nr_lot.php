<html>
    <head>
        <title>Lot</title>
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
                var grnNo = document.getElementById('grn_no');
                grnNo.onchange = updateUI;

                updateUI();
            }

            function updateUI() {
                var grnNo = document.getElementById('grn_no');
                var orderNo = document.getElementById('order_no');
                if (grnNo.value == '') {
                    orderNo.disabled = false;
                } else {
                    orderNo.disabled = true;
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
                var lotNo = document.forms[0].lot_no.value;
                if (lotNo == '') {
                    window.alert('Please enter a lot number');
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
            <?php htmlInputHidden('page', '7'); ?>

            <?php htmlInputHidden('type', $type); ?>

            <div>Type: <span id="type"><?php echo htmlentities($typeName, ENT_QUOTES); ?></type></div>
            <div>GRN No: <?php htmlInputText('grn_no', ''); ?></div>
            <div>Order No: <?php htmlInputText('order_no', ''); ?></div>
            <div>Lot No: <?php htmlInputText('lot_no', ''); ?></div>
<?php $today = getdate(); ?>
            <div id="actions"><input type="image" name="action_back" src="/icons/whm/Back_50x100.gif" onclick="return actionBack()" /><input type="image" name="action_continue" src="/icons/whm/Continue_50x100.gif" "return actionContinue()"/></div>
        </form>
    </body>
</html>
