<?php

class Minder_SysScreen_Model_AustpostManifest extends Minder_SysScreen_Model {

    /**
     * @return array - generated PDF IDs
     */
    public function reRunManifestBuild() {
        $totalResords = count($this);

        if ($totalResords < 1)
            return array();

        $manifests = $this->selectArbitraryExpression(0, $totalResords, 'DISTINCT MANIFEST_ID');
        $manifestRoutines = new Minder_ManifestRoutines();

        $generatedPdfIds = array();

        foreach ($manifests as $manifestRow) {
            if (!is_null($result = $manifestRoutines->reRunManifestBuild($manifestRow['MANIFEST_ID'])))
                $generatedPdfIds[] = $result;
        }

        return $generatedPdfIds;
    }
}