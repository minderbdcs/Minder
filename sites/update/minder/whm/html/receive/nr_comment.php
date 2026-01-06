<html>
    <head>
        <title>Comment</title>
        <link rel="stylesheet" type="text/css" href="newreceive.css" media="all" />
        <script type="text/javascript">
            var buttonClicked = '';

            function actionBack() {
                buttonClicked = 'back';
                return true;
            }

            function actionAccept() {
                buttonClicked = 'accept';
                return true;
            }
        </script>


    </head>
    <body>

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
        <form action="<?php echo htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES); ?>" method="post">
            <?php htmlInputHidden('page', '4'); ?>

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

            <?php htmlInputHidden('hire_pallets', $hire_pallets); ?>

            <?php htmlInputHidden('hire_qty', $hire_qty); ?>

            <?php htmlInputHidden('hire_packaging', $hire_packaging); ?>

            <?php htmlInputHidden('hire_packaging_type', $hire_packaging_type); ?>

            <?php htmlInputHidden('packaging_crate_qty', $packaging_crate_qty); ?>

            <div>Comments</div>
            <div><?php htmlTextArea('comment', ''); ?></div>
            <div id="actions"><input type="image" name="action_back" src="/icons/whm/Back_50x100.gif" onclick="return actionBack()" /> <input type="image" name="action_accept" src="/icons/whm/accept.gif" onclick="return actionAccept()" /></div>
        </form>

    </body>
</html>
