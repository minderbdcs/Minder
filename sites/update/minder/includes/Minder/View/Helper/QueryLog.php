<?php

class Minder_View_Helper_QueryLog extends Zend_View_Helper_Abstract {
    public function queryLog($serviceUrl) {

        $debugDescription = $this->view->SymbologyPrefixDescriptor('DEBUG');

        return "
            <script type=\"text/javascript\">
                $(function(){
                    $.minderQueryLog({'url' : '$serviceUrl'});
                    $('#barcode').bind('parse-success.debug', function(evt) {
                        if (evt.parseResult.paramDesc.param_name == 'DEBUG') {
                            $(this).val('');
                            $.minderQueryLog('execute', evt.parseResult.paramDesc.param_filtered_value);
                        }
                    }).minderBarcodeInput({'barcodeDescriptors' : [$debugDescription]});
                });

            </script>
        ";
    }
}