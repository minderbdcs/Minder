<html>
    <head>
<?php
 include "viewport.php";
?>
        <title>Delivery</title>
        <link rel="stylesheet" type="text/css" href="addrprodlabel.css" media="all" />
<style type="text/css">
input.disabled {
    color: #000000;
}
#
#product_r, #variety_r, #brand_r
{
    width: 390px;
}
#received_page, #printed_page, #qtylabels_page, #qtyinputs_page,
#receivelocation_page, #deliverycompl_page, #trdparty_page, #party_issn_page
{
/*    display: none; */
}
</style>
        <script type="text/javascript">
            var buttonClicked = '';

            function actionBack()
            {
                buttonClicked = 'back';
                pageCount--;
                return true;
            }
            function actionShow(block)
            {
                buttonClicked = block;
                var element = document.getElementById(block + 'lookup');
                element.style.display = 'block';
                var tophalf = document.getElementById('tophalf');
                tophalf.style.display = 'none';
                return false;
            }
            function actionCancel(block)
            {
                buttonClicked = block;
                var element = document.getElementById(block + 'lookup');
                element.style.display = 'none';
                var tophalf = document.getElementById('tophalf');
                tophalf.style.display = 'block';
                return false;
            }
            function actionSelected(block)
            {
                var listR = document.getElementById(block + '_r');
                var lookupId = document.getElementById(block + '_id');
                for (i = 0; i < lookupId.options.length; i++) {
                    if (lookupId.options[i].value == listR.options[listR.selectedIndex].value) {
                        lookupId.selectedIndex = i;
                    }
                }
                var element = document.getElementById(block + 'lookup');
                element.style.display = 'none';
                var tophalf = document.getElementById('tophalf');
                tophalf.style.display = 'block';
                if (block == 'product') {
                    refresh_VB();
                }
                updateUI();
                return false;
            }

            function arrangeQty(iname)
            {
                var qty1 = document.getElementById('qty1');
                var qty2 = document.getElementById('qty2');
                var qty3 = document.getElementById('qty3');
                var qty4 = document.getElementById('qty4');
                var recvd = document.getElementById('recvd');
                var prt_qty = document.getElementById('prt_qty');
                switch(iname)
                {
                    case 'qty1':
                        if (parseInt(qty1.value) > 0) {
                            qty2.value = Math.floor((recvd.value)/qty1.value);
                            qty3.value = 1;
                            qty4.value = (recvd.value - (qty1.value * qty2.value));
                        } else {
                          qty1.value = 0;
                          qty2.value = 0;
                        }
                    break
                    case 'qty2':
                        if (parseInt(qty2.value) > 0) {
                            /* qty1.value = Math.floor((recvd.value)/qty2.value); */
                            qty3.value = 1;
                            qty4.value = (recvd.value - (qty1.value * qty2.value));
                        } else {
                          qty1.value = 0;
                          qty2.value = 0;
                        }
                    break;
                    case 'qty3':
                        if (parseInt(qty3.value) > 0) {
                            qty4.value = Math.floor((recvd.value - (qty1.value * qty2.value))/qty3.value);
                        } else {
                          qty3.value = 0;
                          qty4.value = 0;
                        }
                    break;
                    case 'qty4':
    /*                  
                        if (parseInt(qty4.value) > 0) {
                            qty3.value = Math.floor((recvd.value - (qty1.value * qty2.value))/qty4.value);
                        } else {
                          qty3.value = 0;
                          qty4.value = 0;
                        }
    */
                        done_1st = "T";
                    break;
                };
                verifyQty(false);
                prt_qty.value = qty1.value * qty2.value + qty3.valye * qty4.value;
                updateUI();
            }

            function verifyQty(firstrun)
            {
                var flag = true;

                if (firstrun) {
                    document.getElementById('qty1').value = 0;
                    document.getElementById('qty2').value = 0;
                    document.getElementById('qty3').value = 0;
                    document.getElementById('qty4').value = 0;
                    document.getElementById('recvd').value = 0;
                } else {
                    document.getElementById('qty1').value = parseInt(document.getElementById('qty1').value);
                    document.getElementById('qty2').value = parseInt(document.getElementById('qty2').value);
                    document.getElementById('qty3').value = parseInt(document.getElementById('qty3').value);
                    document.getElementById('qty4').value = parseInt(document.getElementById('qty4').value);

                    var qty1 = parseInt(document.getElementById('qty1').value);
                    var qty2 = parseInt(document.getElementById('qty2').value);
                    var qty3 = parseInt(document.getElementById('qty3').value);
                    var qty4 = parseInt(document.getElementById('qty4').value);
                    var recvd = parseInt(document.getElementById('recvd').value);
                    var prt_qty = parseInt(document.getElementById('prt_qty').value);

                    if (qty1 < 0 || !qty1) {
                      document.getElementById('qty1').value = 0;
                      flag = false;
                    }
                    if (qty2 < 0 || !qty2) {
                      document.getElementById('qty2').value = 0;
                      flag = false;
                    }
                    if (qty3 < 0 || !qty3) {
                      document.getElementById('qty3').value = 0;
                      flag = false;
                    }
                    if (qty4 < 0 || !qty4) {
                      document.getElementById('qty4').value = 0;
                      flag = false;
                    }
                    if (qty1 == 0 || qty2 == 0) {
                      document.getElementById('qty1').value = 0;
                      document.getElementById('qty2').value = 0;
                      flag = false;
                    }
                    if (qty3 == 0 || qty4 == 0) {
                      document.getElementById('qty3').value = 0;
                      document.getElementById('qty4').value = 0;
                      flag = false;
                    }
                    if (qty1 > recvd) {
                      document.getElementById('qty1').value = recvd;
                      document.getElementById('qty2').value = 1;
                      document.getElementById('qty3').value = 0;
                      document.getElementById('qty4').value = 0;
                      flag = false;
                    }
                    if (qty2 > recvd) {
                      document.getElementById('qty1').value = 1;
                      document.getElementById('qty2').value = recvd;
                      document.getElementById('qty3').value = 0;
                      document.getElementById('qty4').value = 0;
                      flag = false;
                    }
                    if (qty3 > recvd) {
                      document.getElementById('qty3').value = recvd;
                      document.getElementById('qty4').value = 1;
                      flag = false;
                    }
                    if (qty4 > recvd) {
                      document.getElementById('qty3').value = 1;
                      document.getElementById('qty4').value = recvd;
                      flag = false;
                    }
                }
                document.forms[0].prt_qty.value =
                    document.forms[0].qty1.value * document.forms[0].qty2.value
                    + document.forms[0].qty3.value * document.forms[0].qty4.value;
                return flag;
            }

            function actionFind(block)
            {
                buttonClicked = block;

                var lookup = document.getElementById(block);
                var listQ = document.getElementById(block + '_q');
                if (listQ.value.length < 2) {
                    window.alert('Please enter at least 2 characters');
                    return false;
                }
            }


            function actioncontinue()
            {
                buttonClicked = 'continue';
                pageCount++;
                return true;
            }

            function pageLoaded()
            {
                document.forms[0].order_no.disabled = true;
                document.forms[0].prt_qty.disabled = true;
                document.forms[0].product_desc.disabled = true;
                refresh_VB();
                var recvd           = document.getElementById('recvd');
                var qty1            = document.getElementById('qty1');
                var qty2            = document.getElementById('qty2');
                var qty3            = document.getElementById('qty3');
                var qty4            = document.getElementById('qty4');
                var product_id      = document.getElementById('product_id');
                recvd.onkeyup       = updateUI;
                //qty1.onkeyup        = updateUI;
                //qty2.onkeyup        = updateUI;
                //qty3.onkeyup        = updateUI;
                //qty4.onkeyup        = updateUI;
                product_id.onchange = prod_change;
                verifyQty(true);
                updateUI();
                document.getElementById('received_page').style.display  = 'block';
                document.getElementById('printed_page').style.display   = 'block';
                document.getElementById('qtylabels_page').style.display = 'block';
                document.getElementById('qtyinputs_page').style.display = 'block';
                document.getElementById('deliverycompl_page').style.display   = 'block';
                var tophalf = document.getElementById('tophalf');
                tophalf.style.display = 'block';
            }
            function prod_change()
            {
                updateUI();
            }

            function refresh_VB(prev_sel)
            {
            }

            function updateUI()
            {
                /* verifyQty(false); */
                document.forms[0].prt_qty.value =
                    document.forms[0].qty1.value * document.forms[0].qty2.value
                    + document.forms[0].qty3.value * document.forms[0].qty4.value;
                var prtQty = document.getElementById('prt_qty')
                prtQty.disabled = false;
                if (document.forms[0].prt_qty.value != document.forms[0].recvd.value) {
                    prtQty.style.backgroundColor = '#ff0000';
                    prtQty.style.color = '#ffffff';
                } else {
                    prtQty.style.backgroundColor = '#00ff00';
                    prtQty.style.color = '#ffffff';
                }
                var i = document.forms[0].product_id.options.selectedIndex;
                var selected = document.forms[0].product_id.options[i].value;
                document.forms[0].product_desc.value = productInfo[selected][0];


            }

            function formSubmitted()
            {
                if (buttonClicked == 'back' ) {
                    return true;
                    if (pageCount == -1) {
                    } else {
                        if (pageCount == 0 ) {
                            document.getElementById('received_page').style.display  = 'none';
                            document.getElementById('printed_page').style.display   = 'none';
                            document.getElementById('qtylabels_page').style.display = 'none';
                            document.getElementById('qtyinputs_page').style.display = 'none';
                        } else if (pageCount == 1) {
                            document.getElementById('received_page').style.display  = 'block';
                            document.getElementById('printed_page').style.display   = 'block';
                            document.getElementById('qtylabels_page').style.display = 'block';
                            document.getElementById('qtyinputs_page').style.display = 'block';
                            document.getElementById('deliverycompl_page').style.display   = 'none';
                        } else {
                            /* alert('error while back'); */
                        }
                        return false;
                    }
                }

                verifyQty(false);

                if (buttonClicked == 'continue') {
                    return true;
                    if (pageCount == 1) {

                        document.getElementById('received_page').style.display  = 'block';
                        document.getElementById('printed_page').style.display   = 'block';
                        document.getElementById('qtylabels_page').style.display = 'block';
                        document.getElementById('qtyinputs_page').style.display = 'block';
                        document.getElementById('recvd').focus();
                        document.getElementById('recvd').select();
                        return false;
                    } else if (pageCount == 2) {
                        document.getElementById('received_page').style.display  = 'none';
                        document.getElementById('printed_page').style.display   = 'none';
                        document.getElementById('qtylabels_page').style.display = 'none';
                        document.getElementById('qtyinputs_page').style.display = 'none';

                        return true;
                    } else if (pageCount == 3) {
                        document.forms[0].order_no.disabled = false;
                    } else {
                        /* alert('error'); */
                        return false;
                    }
                    if (document.forms[0].recvd.value == '' && pageCount == 2) {
                        window.alert('Please enter a received value');
                        pageCount--;
                        return false;
                    }
                    if (parseInt(document.forms[0].prt_qty.value) < parseInt(document.forms[0].recvd.value)
                        && pageCount == 2) {
                        window.alert('Too few labels');
                        pageCount--;
                        return false;
                    }

                    if (parseInt(document.forms[0].prt_qty.value) > parseInt(document.forms[0].recvd.value)
                        && pageCount == 2) {
                        window.alert('Too many labels');
                        pageCount--;
                        return false;
                    }
                }
                if (buttonClicked == 'product') {
                    return false;
                }
                updateUI();

                return true;
            }

            var productInfo = <?php echo json_encode($productIdOpts); ?>;
            var pageCount   = 0; 
        </script>
    </head>
    <body onload="pageLoaded()">
<?php
if (true === isset($message)) {
    if ($message != '') {
        echo '<p>' . htmlentities($message, ENT_QUOTES) . '</p>';
    }
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
<?php
              ?>
            <input type="hidden"
                   name="real_order_no"
                   value="<?php echo htmlentities($orderNo, ENT_QUOTES); ?>" />
            <div>Printer:
                <?php htmlSelect('printer_id', null, $printerOpts); ?>
            </div>
            <div>Order No:
                <input type="text"
                       name="order_no"
                       id="order_no"
                       class="disabled"
                       value="<?php echo htmlentities($orderNo, ENT_QUOTES); ?>"
                       autocomplete="off" />
            </div>
            <div id = "product_page">Product ID:
            <select name="product_id"
                    id="product_id">
                    <?php foreach ($productIdOpts as $productId => $info) {
                              echo '<option value="' .
                              htmlentities($productId, ENT_QUOTES) . '">' .
                              htmlentities($productId, ENT_QUOTES) . '</option>';
                          } ?>
            </select>
            </div>
            <div>
            <input type="text"
                   name="product_desc"
                   id="product_desc"
                   class="disabled"
                   value=""
                   autocomplete="off" />
            </div>
            <div id="received_page">Despatch
                <input type="text"
                       name="recvd"
                       id="recvd"
                       value=""
                       autocomplete="off" />
            </div>
            <div id="printed_page">
                Printed Quantity
                <input type="text"
                       name="prt_qty"
                       id="prt_qty"
                       value=""
                       autocomplete="off" />
            </div>
            <div id="qtylabels_page">
               Qty Labels X Qty/SSN Label
            </div>
            <div id="qtyinputs_page">
                <input type="text"
                       name="qty1"
                       id="qty1"
                       value="0"
                       onchange="arrangeQty(this.name)"
                       autocomplete="off" />
                X
                <input type="text"
                       name="qty2"
                       id="qty2"
                       value="0"
                       onchange="arrangeQty(this.name)"
                       autocomplete="off" />
                +
                <input type="text"
                       name="qty3"
                       id="qty3"
                       value="0"
                       onchange="arrangeQty(this.name)"
                       autocomplete="off" />
                X
                <input type="text"
                       name="qty4"
                       id="qty4"
                       value="0"
                       onchange="arrangeQty(this.name)"
                       autocomplete="off" />
            </div>
            <div id="deliverycompl_page">
                Is Despatch Complete:
                <input type="radio"
                       name="complete"
                       value="y"
                       id="complete_y" />
                Yes
                <input type="radio"
                name="complete"
                value="n"
                id="complete_n" />
                No
            </div>
            <div id = "actions1">
                <input type="image"
                       name="action_back"
                       id="action_back"
                       src="/icons/whm/Back_50x100.gif"
                       onclick="return actionBack()" />
                <input type="image"
                       name="action_continue"
                       id="action_continue"
                       src="/icons/whm/Continue_50x100.gif"
                       onclick="return actioncontinue()"/>
            </div>
        </form>
</div>
    </body>
</html>
