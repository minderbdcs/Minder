<html>
    <head>
<?php
 include "viewport.php";
?>
        <title>Delivery</title>
        <link rel="stylesheet" type="text/css" href="addrprodlabel.css" media="all" />
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
                var ssnId = document.getElementById('ssn_id');
                ssnId.onchange = updateUI;

                updateUI();
            }

            function updateUI() {
                var ssnId = document.getElementById('ssn_id');
                {
                    ssnId.disabled = false;
                }
            }

            function formSubmitted() {
                // If the user clicked on back then check no further
                if (buttonClicked == 'back') {
                    return true;
                }
                if (document.forms[0].ssn_id.value != '') {
                    return true;
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
            <?php htmlInputHidden('page', '2'); ?>

            <div>ISSN: <?php htmlInputText('ssn_id', ''); ?> </div>
<?php $today = getdate(); ?>
            <div id="actions"><input type="image" name="action_back" src="/icons/whm/Back_50x100.gif" onclick="return actionBack()" /><input type="image" name="action_continue" src="/icons/whm/Continue_50x100.gif" "return actionContinue()"/></div>
        </form>
    </body>
</html>
