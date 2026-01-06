<html>
    <head>
        <link rel="stylesheet" type="text/css" href="newreceive.css" media="all" />
<style type="text/css">
.disabled {
    color: #000000;
}
</style>
<script type="text/javascript">
    function actionAccept()
    {
        if (document.getElementById('third_no').value.length < 1)
        {
            window.alert('Please enter at least 1 characters');
            return false;
        }
        return true;
    }
    function formSubmitted()
    {
        return true;
    }
    //function actionBack()
    //{
    //    return true;
    //}
</script>
<?php
if (isset($_POST)) {
    if (isset($_POST['cnt'])) {
        $cnt = $_POST['cnt']+1;
    } else {
        $cnt = 1;
    }

    $type = getTypeInfo();
    $typeName = getTypeName($type);
    $printerOpts = getPrinterOpts($Link);
    if ($_POST['thirdparty'] == 'y') {
        if (false == isset($_POST['total_labels'])) {
            $totalLabels = $_POST['qty1']+$_POST['qty3'];
        } else {
            $totalLabels = $_POST['total_labels'];
        }
?>
<div id="tophalf">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return formSubmitted()">
            <!--<?php echo htmlInputText('cIssn', $cIssn); ?>-->
            <?php htmlInputHidden('page', '6'); ?>
            <?php htmlInputHidden('type', $type); ?>
            <?php htmlInputHidden('cnt', $cnt); ?>
            <?php htmlInputHidden('cIssn1', $cIssn1); ?>
            <?php htmlInputHidden('cIssn2', $cIssn2); ?>
            <?php htmlInputHidden('owned_by', htmlentities($_POST['owned_by'])); ?>
            <?php htmlInputHidden('thirdparty', $_POST['thirdparty']);?>
            <?php htmlInputHidden('complete', $_POST['complete']);?>
            <?php htmlInputHidden('product_desc', htmlentities($_POST['product_desc']));?>
            <?php htmlInputHidden('variety_desc', htmlentities($_POST['variety_desc']));?>
            <?php htmlInputHidden('brand_desc', htmlentities($_POST['brand_desc']));?>
            <?php htmlInputHidden('um', htmlentities($_POST['um'])); ?>
            <?php htmlInputHidden('printer_id', htmlentities($_POST['printer_id'])); ?>
            <?php htmlInputHidden('product_id', htmlentities($_POST['product_id'])); ?>
            <?php htmlInputHidden('variety_id', htmlentities($_POST['variety_id'])); ?>
            <?php htmlInputHidden('brand_id', htmlentities($_POST['brand_id'])); ?>
            <?php htmlInputHidden('recvd', htmlentities($_POST['recvd'])); ?>
            <?php htmlInputHidden('receive_location', htmlentities($_POST['receive_location'])); ?>
            <input type="hidden"
                   name="real_grn_no"
                   value="<?php echo htmlentities($_POST['real_grn_no'], ENT_QUOTES); ?>" />
            <input type="hidden"
                   name="real_order_no"
                   value="<?php echo htmlentities($_POST['real_order_no'], ENT_QUOTES); ?>" />
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
                       disabled
                       value="<?php echo htmlentities($_POST['real_grn_no'], ENT_QUOTES); ?>"
                       autocomplete="off" />
                <?php htmlSelect('printer_id', null, $printerOpts, array(
                     'class' => 'disabled')); ?>
            </div>
            <div>Order No:
                <input type="text"
                       name="order_no"
                       id="order_no"
                       class="disabled"
                       disabled
                       value="<?php echo htmlentities($_POST['real_order_no'], ENT_QUOTES); ?>"
                       autocomplete="off" />
            </div>
            <hr>
            <div>
            <?php echo htmlInputHidden('product_id',$_POST['product_id']);?>
            <input type  = "text"
                   name  = "product_d"
                   id    = "product_d"
                   class = "disabled"
                   disabled
                   value="<?php echo htmlentities($_POST['product_desc'], ENT_QUOTES); ?>"
                   />
            </div>
            <div>
            <input type  = "text"
                   name  = "variety_d"
                   id    = "variety_d"
                   class = "disabled"
                   disabled
                   value="<?php echo htmlentities($_POST['variety_desc'], ENT_QUOTES); ?>"
                   />
            </div>
            <div>
            <input type  = "text"
                   name  = "brand_d"
                   id    = "brand_d"
                   class = "disabled"
                   disabled
                   value="<?php echo htmlentities($_POST['brand_desc'], ENT_QUOTES); ?>"
                   />
            </div>
            <hr>
            <div>
                Record 3rd party Number:
            </div>
            <div>
                Total Labels:
                <?php echo $totalLabels; ?>
                <?php echo htmlInputHidden('total_labels', $totalLabels); ?>
                Recorded:
                <?php echo ($rQty1+$rQty2); ?>
                <?php echo htmlInputHidden('r_qty1', $rQty1); ?>
                <?php echo htmlInputHidden('r_qty2', $rQty2); ?>
            </div>
            <?php if (($rQty1+$rQty2)<$totalLabels) {?>
            <div>
                3rd Party No:
                <input type="text" autocomplete="off" value="" name="third_no" id="third_no"/>
                <?php //echo htmlInputText("third_no",'');?>
            </div>
            <?php }?>
            <div id = "actions1">
                <!--<input type="image"
                       name="action_back"
                       id="action_back"
                       src="/icons/whm/Back_50x100.gif"
                       onclick="return actionBack()" />-->
                <input type="image"
                       name="action_accept"
                       src="/icons/whm/accept.gif"
                       onclick="return actionAccept()"/>
            </div>
<?php
    }
}

foreach ($result as $key => $val) {
?>
    <div>
        <?php echo htmlInputHidden('result[]',$val);?>
    </div>
<?php
}
