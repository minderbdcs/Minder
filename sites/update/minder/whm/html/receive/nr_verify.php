<html>
    <head>
        <title>Delivery</title>
        <link rel="stylesheet" type="text/css" href="newreceive.css" media="all" />
<style type="text/css">
input.disabled {
    color: #000000;
}
#lookup {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    padding: 2px;
    display: none;
    z-index: 10;
    background-color: #ffffff;
}
#q {
    width: 200px;
}
#r {
    width: 390px;
}
</style>
        <script type="text/javascript">
            var buttonClicked = '';

            function actionBack() {
                buttonClicked = 'back';
                return confirm('Cancel Delivery?');
            }

            function actionProductShow() {
                buttonClicked = 'product';
                var lookup = document.getElementById('lookup');
                lookup.style.display = 'block';
                var tophalf = document.getElementById('tophalf');
                tophalf.style.display = 'none';
                return false;
            }

            function actionProductCancel() {
                buttonClicked = 'product_cancel';
                var lookup = document.getElementById('lookup');
                lookup.style.display = 'none';
                var tophalf = document.getElementById('tophalf');
                tophalf.style.display = 'block';
                return false;
            }

            function actionProductSelected() {
                var r = document.getElementById('r');
                var productId = document.getElementById('product_id');
                for (i = 0; i < productId.options.length; i++) {
                    if (productId.options[i].value == r.options[r.selectedIndex].value) {
                        productId.selectedIndex = i;
                    }
                }
                var lookup = document.getElementById('lookup');
                lookup.style.display = 'none';
                var tophalf = document.getElementById('tophalf');
                tophalf.style.display = 'block';
                updateUI();
                return false;
            }

            function actionProductFind() {
                var ajax = null;
                if (window.XMLHttpRequest) {
                    ajax = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    ajax = new ActiveXObject('Microsoft.XMLHTTP');
                }
                if (!ajax) {
                    window.alert('Search will not work. No AJAX');
                }

                buttonClicked = 'product_find';
                var lookup = document.getElementById('lookup');
                var q = document.getElementById('q');
                if (q.value.length < 3) {
                    window.alert('Please enter at least 2 characters');
                    return false;
                }
                ajax.onreadystatechange = function () {
                    if (ajax.readyState == 4) {
                        var opts = eval('(' + ajax.responseText + ')');
                        var r = document.getElementById('r');
                        r.options.length = 0;
                        for (i = 0; i < opts.length; i++ ) {
                            r.options[i] = new Option(opts[i].id + ' - ' + opts[i].desc, opts[i].id);
                        }
                    }
                };
                ajax.open("GET", "/whm/receive/lookup.php?q=" + encodeURI(q.value), true);
                ajax.send(null);
                return false;
            }

            function actionAccept() {
                buttonClicked = 'accept';
                return true;
            }

            function pageLoaded() {
                document.forms[0].grn_no.disabled = true;
                document.forms[0].order_no.disabled = true;
                document.forms[0].prt_qty.disabled = true;
                document.forms[0].description.disabled = true;
                document.forms[0].um_description.disabled = true;
                var recvd = document.getElementById('recvd');
                var qty1 = document.getElementById('qty1');
                var qty2 = document.getElementById('qty2');
                var qty3 = document.getElementById('qty3');
                var qty4 = document.getElementById('qty4');
                recvd.onkeyup = updateUI;
                qty1.onkeyup = updateUI;
                qty2.onkeyup = updateUI;
                qty3.onkeyup = updateUI;
                qty4.onkeyup = updateUI;
                var product_id = document.getElementById('product_id');
                product_id.onchange = updateUI;

                updateUI();
            }

            function updateUI() {
                document.forms[0].prt_qty.value =
                    document.forms[0].qty1.value * document.forms[0].qty2.value
                    + document.forms[0].qty3.value * document.forms[0].qty4.value;

                var prtQty = document.getElementById('prt_qty')
                prtQty.disbaled = false;
                if (document.forms[0].prt_qty.value != document.forms[0].recvd.value) {
                    prtQty.style.backgroundColor = '#ff0000';
                    prtQty.style.color = '#ffffff';
                } else {
                    prtQty.style.backgroundColor = '#00ff00';
                    prtQty.style.color = '#ffffff';
                }

                var i = document.forms[0].product_id.options.selectedIndex;
                var selected = document.forms[0].product_id.options[i].value;
                document.forms[0].description.value = productInfo[selected][0];
                document.forms[0].um_description.value = productInfo[selected][2];
                document.forms[0].um.value = productInfo[selected][1];
            }

            function formSubmitted() {
                if (buttonClicked == 'back') {
                    return true;
                }
                if (buttonClicked == 'product') {
                    return false;
                }
                updateUI();

                if (document.forms[0].recvd.value == '') {
                    window.alert('Please enter a received value');
                    return false;
                }

                if (parseInt(document.forms[0].prt_qty.value) < parseInt(document.forms[0].recvd.value)) {
                    window.alert('Too few labels');
                    return false;
                }

                if (parseInt(document.forms[0].prt_qty.value) > parseInt(document.forms[0].recvd.value)) {
                    window.alert('Too many labels');
                    return false;
                }

                var completeN = document.getElementById('complete_n');
                var completeY = document.getElementById('complete_y');
                if (!completeN.checked && !completeY.checked) {
                    window.alert('Please indicate if this order is complete');
                    return false;
                }

                var completeN = document.getElementById('complete_n');
                var completeY = document.getElementById('complete_y');
                if (!completeN.checked && !completeY.checked) {
                    window.alert('Please indicate if this order is complete');
                    return false;
                }

                document.forms[0].grn_no.disabled = false;
                document.forms[0].order_no.disabled = false;
                document.forms[0].prt_qty.disabled = false;
                document.forms[0].description.disabled = false;

                return true;
            }

            var productInfo = <?php echo json_encode($productInfo); ?>
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
<div id="tophalf">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return formSubmitted()">
            <?php htmlInputHidden('page', '5'); ?>

            <?php htmlInputHidden('type', $type); ?>
            <input type="hidden" name="real_grn_no" value="<?php echo htmlentities($grnNo, ENT_QUOTES); ?>" />
            <input type="hidden" name="real_order_no" value="<?php echo htmlentities($orderNo, ENT_QUOTES); ?>" />
            <input type="hidden" name="um" value="" />

            <div>Type: <span id="type"><?php echo htmlentities($typeName, ENT_QUOTES); ?></type></div>
            <div>GRN No: <input type="text" name="grn_no" id="grn_no" class="disabled" value="<?php echo htmlentities($grnNo, ENT_QUOTES); ?>" autocomplete="off" /> <?php htmlSelect('printer_id', null, $printerOpts); ?></div>
            <div>Order No: <input type="text" name="order_no" id="order_no" class="disabled" value="<?php echo htmlentities($orderNo, ENT_QUOTES); ?>" autocomplete="off" /></div>
            <div>Product ID: <select name="product_id" id="product_id"><?php foreach ($productInfo as $productId => $info) { echo '<option value="' . htmlentities($productId, ENT_QUOTES) . '">' . htmlentities($productId, ENT_QUOTES) . '</option>'; } ?></select></div>
            <div><input type="text" name="description" id="description" class="disabled" valuie="" autocomplete="off" /></div>
            <div>Recvd <input type="text" name="recvd" id="recvd" value="" autocomplete="off" /> UM <input type="text" name="um_description" id="um_description" class="disabled" value="" autocomplete="off" /></div>
            <div>Printed Quantity <input type="text" name="prt_qty" id="prt_qty" value="" autocomplete="off" /></div>
            <div>Qty Labels X Qty/SSN Label</div>
            <div><input type="text" name="qty1" id="qty1" value="" autocomplete="off" /> X <input type="text" name="qty2" id="qty2" value="" autocomplete="off" /> + <input type="text" name="qty3" id="qty3" value="" autocomplete="off" /> X <input type="text" name="qty4" id="qty4" value="" autocomplete="off" /></div>
            <div>Receive Location: <?php htmlSelect('receive_location', null, $receiveLocationOpts); ?></div>
            <div>Is Delivery Complete: <input type="radio" name="complete" value="y" id="complete_y" /> Yes <input type="radio" name="complete" value="n" id="complete_n" /> No</div>
            <div>3rd Party: <input type="radio" name="thirdparty" value="y" id="thirdparty_y" /> Yes <input type="radio" name="thirdparty" value="n" id="thirdparty_n" /> No</div>
            <div id="actions"><input type="image" name="action_back" id="action_back" src="/icons/whm/Back_50x100.gif" onclick="return actionBack()" /> <input type="image" name="action_product" src="/icons/whm/product.gif" onclick="return actionProductShow()"/> <input type="image" name="action_accept" src="/icons/whm/accept.gif" onclick="return actionAccept()"/></div>
        </form>
</div>
        <div id="lookup">
            <form onsubmit="javascript: return false">
                <div>Search for description:</div>
                <div><input type="text" name="q" value="" id="q" autocomplete="off" /></div>
                <div>Double click to select:</div>
                <div><select name="r" id="r" size="5" ondblclick="actionProductSelected()"></select></div>
                <div><button onclick="return actionProductCancel()">Back</button> <button onclick="return actionProductFind()">Find</button></div>
            </form>
        </div>
    </body>
</html>
