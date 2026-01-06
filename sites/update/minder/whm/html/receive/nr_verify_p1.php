<html>
    <head>
        <title>Delivery</title>
        <link rel="stylesheet" type="text/css" href="newreceive.css" media="all" />
<style type="text/css">
input.disabled {
    color: #000000;
}
#brandlookup, #productlookup, #varietylookup
{
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
#product_q, #variety_q, #brand_q
{
    width: 200px;
}
#product_r, #variety_r, #brand_r
{
    width: 390px;
}
#received_page, #printed_page, #qtylabels_page, #qtyinputs_page,
#receivelocation_page, #deliverycompl_page, #trdparty_page, #party_issn_page
{
    display: none;
}
</style>
        <script type="text/javascript">
            var buttonClicked = '';

            function actionBack()
            {
                buttonClicked = 'back';
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
                buttonClicked = block + '_cancel';
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
                updateUI();
                return false;
            }

            function actionFind(block)
            {
                buttonClicked = block + '_find';
                var ajax = null;
                if (window.XMLHttpRequest) {
                    ajax = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    ajax = new ActiveXObject('Microsoft.XMLHTTP');
                }
                if (!ajax) {
                    window.alert('Search will not work. No AJAX');
                }

                var lookup = document.getElementById(block);
                var listQ = document.getElementById(block + '_q');
                if (listQ.value.length < 2) {
                    window.alert('Please enter at least 2 characters');
                    return false;
                }
                ajax.onreadystatechange = function () {
                    if (ajax.readyState == 4) {
                        window.alert(ajax.responseText);
                        var opts = eval('(' + ajax.responseText + ')');
                        var listR = document.getElementById(block + '_r');
                        listR.options.length = 0;

                        for (i = 0; i < opts.length; i++ ) {
                            listR.options[i] = new Option(opts[i].id + ' - ' + opts[i].desc, opts[i].id);
                        }
                    }
                };
                ajax.open("GET", "/whm/receive/lookup0709.php?r=" + block + "&q=" + encodeURI(listQ.value), true);
                ajax.send(null);
                return false;
            }


            function actioncontinue()
            {
                buttonClicked = 'continue';
                pageCount++;
                return true;
            }

            function pageLoaded()
            {
                document.forms[0].grn_no.disabled = true;
                document.forms[0].order_no.disabled = true;
                document.forms[0].prt_qty.disabled = true;
                document.forms[0].product_desc.disabled = true;
                document.forms[0].variety_desc.disabled = true;
                document.forms[0].brand_desc.disabled = true;
                document.forms[0].um_description.disabled = true;
                var brand_id        = document.getElementById('brand_id');
                var product_id      = document.getElementById('product_id');
                var variety_id      = document.getElementById('variety_id');
                var recvd           = document.getElementById('recvd');
                var qty1            = document.getElementById('qty1');
                var qty2            = document.getElementById('qty2');
                var qty3            = document.getElementById('qty3');
                var qty4            = document.getElementById('qty4');
                recvd.onkeyup       = updateUI;
                qty1.onkeyup        = updateUI;
                qty2.onkeyup        = updateUI;
                qty3.onkeyup        = updateUI;
                qty4.onkeyup        = updateUI;
                brand_id.onchange   = updateUI;
                product_id.onchange = updateUI;
                variety_id.onchange = updateUI;

                updateUI();
            }

            function updateUI()
            {
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
                document.forms[0].product_desc.value = productInfo[selected][0];
                document.forms[0].um_description.value = productInfo[selected][2];
                document.forms[0].um.value = productInfo[selected][1];

                var i = document.forms[0].variety_id.options.selectedIndex;
                var selected = document.forms[0].variety_id.options[i].value;
                document.forms[0].variety_desc.value = varietyList[selected];

                var i = document.forms[0].brand_id.options.selectedIndex;
                var selected = document.forms[0].brand_id.options[i].value;
                document.forms[0].brand_desc.value = brandList[selected];


            }

            function formSubmitted()
            {
                if (buttonClicked == 'back' ) {
                    if (pageCount == 0) {
                        if ( confirm('Cancel Delivery?')) {
                            document.forms[0].grn_no.disabled = false;
                            document.forms[0].order_no.disabled = false;
                            document.forms[0].product_desc.disabled = false;
                            document.forms[0].variety_desc.disabled = false;
                            document.forms[0].brand_desc.disabled = false;
                            return true;
                        }
                        else {
                            return false;
                        }
                    } else {
                        pageCount--;
                        if (pageCount == 0 ) {
                            var brand_id = document.getElementById('brand_page');
                            var product_id = document.getElementById('product_page');
                            var variety_id = document.getElementById('variety_page');
                            brand_id.style.display   = 'block';
                            product_id.style.display = 'block';
                            variety_id.style.display = 'block';
                            document.getElementById('received_page').style.display  = 'none';
                            document.getElementById('printed_page').style.display   = 'none';
                            document.getElementById('qtylabels_page').style.display = 'none';
                            document.getElementById('qtyinputs_page').style.display = 'none';
                        } else if (pageCount == 1) {
                            document.getElementById('received_page').style.display  = 'block';
                            document.getElementById('printed_page').style.display   = 'block';
                            document.getElementById('qtylabels_page').style.display = 'block';
                            document.getElementById('qtyinputs_page').style.display = 'block';

                            document.getElementById('receivelocation_page').style.display = 'none';
                            document.getElementById('deliverycompl_page').style.display   = 'none';
                            document.getElementById('trdparty_page').style.display        = 'none';
                        } else {
                            alert('error while back');
                        }
                        return false;
                    }
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

                var completeN = document.getElementById('complete_n');
                var completeY = document.getElementById('complete_y');
                if (!completeN.checked && !completeY.checked && pageCount == 3) {
                    window.alert('Please indicate if this order is complete');
                    return false;
                }

                if (buttonClicked == 'continue') {
                    if (pageCount == 1) {
                        document.getElementById('brand_page').style.display   = 'none';;
                        document.getElementById('product_page').style.display = 'none';;
                        document.getElementById('variety_page').style.display = 'none';;

                        document.getElementById('received_page').style.display  = 'block';
                        document.getElementById('printed_page').style.display   = 'block';
                        document.getElementById('qtylabels_page').style.display = 'block';
                        document.getElementById('qtyinputs_page').style.display = 'block';
                        return false;
                    } else if (pageCount == 2) {
                        document.getElementById('received_page').style.display  = 'none';
                        document.getElementById('printed_page').style.display   = 'none';
                        document.getElementById('qtylabels_page').style.display = 'none';
                        document.getElementById('qtyinputs_page').style.display = 'none';

                        document.getElementById('receivelocation_page').style.display = 'block';
                        document.getElementById('deliverycompl_page').style.display   = 'block';
                        document.getElementById('trdparty_page').style.display        = 'block';
                        return false;
                    } else if (pageCount == 3) {
                        document.forms[0].grn_no.disabled = false;
                        document.forms[0].order_no.disabled = false;
                        document.forms[0].product_desc.disabled = false;
                        document.forms[0].variety_desc.disabled = false;
                        document.forms[0].brand_desc.disabled = false;
                    } else {
                        alert('error');
                        return false;
                    }
                }
                if (buttonClicked == 'product') {
                    return false;
                }
                if (buttonClicked == 'brand') {
                    return false;
                }
                if (buttonClicked == 'variety') {
                    return false;
                }
                updateUI();

                return true;
            }

            var productInfo = <?php echo json_encode($productInfo); ?>;
            var varietyList = <?php echo json_encode($varietyList); ?>;
            var brandList   = <?php echo json_encode($brandList);   ?>;
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
            <?php htmlInputHidden('type', $type); ?>
            <input type="hidden"
                   name="real_grn_no"
                   value="<?php echo htmlentities($grnNo, ENT_QUOTES); ?>" />
            <input type="hidden"
                   name="real_order_no"
                   value="<?php echo htmlentities($orderNo, ENT_QUOTES); ?>" />
            <input type="hidden"
                   name="um"
                   value="" />
            <div>Type:
                <span id="type">
                    <?php echo htmlentities($typeName, ENT_QUOTES); ?>
                </type>
            </div>
            <div>GRN No:
                <input type="text"
                       name="grn_no"
                       id="grn_no"
                       class="disabled"
                       value="<?php echo htmlentities($grnNo, ENT_QUOTES); ?>"
                       autocomplete="off" />
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
            <!--<hr>-->
            <div id = "product_page">Product ID:
            <select name="product_id"
                    id="product_id">
                    <?php foreach ($productInfo as $productId => $info) {
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
            <div id = "variety_page">Variety:
            <select name="variety_id"
                    id="variety_id">
                    <?php foreach ($varietyList as $key => $val) {
                              echo '<option value="' .
                              htmlentities($key, ENT_QUOTES) . '">' .
                              htmlentities($key, ENT_QUOTES) . '</option>';
                          } ?>
            </select>
            </div>
            <div>
            <input type="text"
                   name="variety_desc"
                   id="variety_desc"
                   class="disabled"
                   value=""
                   autocomplete="off" />
            </div>
            <div id = "brand_page">Brand:
            <select name="brand_id"
                    id="brand_id">
                    <?php foreach ($brandList as $key => $val) {
                              echo '<option value="' .
                              htmlentities($key, ENT_QUOTES) . '">' .
                              htmlentities($key, ENT_QUOTES) . '</option>';
                          } ?>
            </select>
            </div>
            <div>
            <input type="text"
                   name="brand_desc"
                   id="brand_desc"
                   class="disabled"
                   value=""
                   autocomplete="off" />
            </div>
            <div id="received_page">Recvd
                <input type="text"
                       name="recvd"
                       id="recvd"
                       value=""
                       autocomplete="off" />
                UM
                <input type="text"
                       name="um_description"
                       id="um_description"
                       class="disabled" value="" autocomplete="off" />
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
                       value=""
                       autocomplete="off" />
                X
                <input type="text"
                       name="qty2"
                       id="qty2"
                       value=""
                       autocomplete="off" />
                +
                <input type="text"
                       name="qty3"
                       id="qty3"
                       value=""
                       autocomplete="off" />
                X
                <input type="text"
                       name="qty4"
                       id="qty4"
                       value=""
                       autocomplete="off" />
            </div>
            <div id="receivelocation_page">
                Receive Location:
                <?php htmlSelect('receive_location', null, $receiveLocationOpts); ?>
            </div>
            <div id="deliverycompl_page">
                Is Delivery Complete:
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
            <div id="trdparty_page">
                3rd Party:
                <input type="radio"
                       name="thirdparty"
                       value="y"
                       id="thirdparty_y" />
                Yes
                <input type="radio"
                       name="thirdparty"
                       value="n"
                       id="thirdparty_n" />
                No
            </div>
            <div id="party_issn_page">
                <select id = "trdparty_issn">
                </select>
            </div>
            <div id = "actions1">
                <input type="image"
                       name="action_back"
                       id="action_back"
                       src="/icons/whm/50x100/back.gif"
                       onclick="return actionBack()" />
                <input type="image"
                       name="action_continue"
                       src="/icons/whm/50x100/continue.gif"
                       onclick="return actioncontinue()"/>
            </div>
            <div id = "actions2">
                <input type="image"
                       name="action_brand"
                       src="/icons/whm/50x100/brand.gif"
                       onclick="return actionShow('brand')"/>
                <input type="image"
                       name="action_product"
                       src="/icons/whm/50x100/product.gif"
                       onclick="return actionShow('product')"/>
                <input type="image"
                       name="action_variety"
                       src="/icons/whm/50x100/variety.gif"
                       onclick="return actionShow('variety')"/>
            </div>
        </form>
</div>
        <div id="productlookup">
            <form onsubmit="javascript: return false">
                <div>Search for description:</div>
                <div>
                    <input type="text"
                           name="product_q"
                           value=""
                           id="product_q"
                           autocomplete="off" />
                    </div>
                <div>Double click to select:</div>
                <div>
                    <select name="product_r"
                            id="product_r"
                            size="5"
                            ondblclick="actionSelected('product')">
                    <?php
                        foreach ($productInfo as $productId => $info) {
                            echo '<option value="' .
                                 htmlentities($productId, ENT_QUOTES) . '">' .
                                 htmlentities($info[0], ENT_QUOTES) .
                                 '</option>';
                        }
                    ?>
                    </select>
                </div>
                <div>
                    <button onclick="return actionCancel('product')">
                            Back
                    </button>
                    <button onclick="return actionFind('product')">
                            Find
                    </button>
                </div>
            </form>
        </div>
        <div id="brandlookup">
            <form onsubmit="javascript: return false">
                <div>Search for description:</div>
                <div>
                    <input type="text"
                           name="brand_q"
                           value=""
                           id="brand_q"
                           autocomplete="off" />
                </div>
                <div>Double click to select:</div>
                <div>
                    <?php echo htmlSelect("brand_r",
                                          null,
                                          $brandList,
                                          array("size"       => 5,
                                                "ondblclick" => "actionSelected('brand')")); ?>
                 </div>
                <div>
                    <button onclick="return actionCancel('brand')">
                            Back
                    </button>
                    <button onclick="return actionFind('brand')">
                            Find
                    </button>
                </div>
            </form>
        </div>
        <div id="varietylookup">
            <form onsubmit="javascript: return false">
                <div>Search for description:</div>
                <div><input type="text" name="variety_q" value="" id="variety_q" autocomplete="off" /></div>
                <div>Double click to select:</div>
                <div>
                    <?php echo htmlSelect("variety_r",
                                          null,
                                          $varietyList,
                                          array("size"       => 5,
                                                "ondblclick" => "actionSelected('variety')")); ?>
                </div>
                <div>
                    <button onclick="return actionCancel('variety')">
                            Back
                    </button>
                    <button onclick="return actionFind('variety')">
                            Find
                    </button>
                </div>
            </form>
        </div>
    </body>
</html>
