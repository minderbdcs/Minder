<?php
require_once dirname(__FILE__) . '/HtmlToPdf/fpdf.php';

class HtmlToPdf extends FPDF
{
    public $B;

    public $I;

    public $U;

    public $HREF;

    public $pheader = '';
   
    public $theader = array();
   
    public $pfooter = '';

    public $cellwidth;
    
    public $currentRow = 0;

    public $currentRowInTable = 0; 

    protected $_logo;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
    {
        $format = strtolower($format);
        switch ($format) {
            case 'a3':
                $format = array(297, 420);
                break;

            case 'a5':
                $format = array(148, 210);
                break;

            case 'letter':
                $format = array(216, 279);
                break;

            case 'legal':
                $format = array(216, 356);
                break;

            default:
            case 'a4':
                $format = array(210, 297);
                break;
        }

        $orientation = strtolower($orientation);
        if ($orientation == 'l') {
            $format = array_reverse($format);
        }
        $this->B = 0;
        $this->I = 0;
        $this->U = 0;
        $this->HREF = '';

        $this->FPDF('p', 'mm', $format);
    }

    public function writeTable($header, $data, $w)
    {
        //Colors, line width and bold font
        $this->SetFillColor(177, 219, 135);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(.1);
        //$this->SetFont('', 'B');

        for($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
        }
        $this->Ln();

        //Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetLineWidth(.1);
        $this->SetFont('');
        //Data
        $fill = 0;
        foreach ($data as $row) {
            foreach ($row as $k => $v) {
                $this->Cell($w[$k], 6, $v, 1, 0, 'L', $fill);
            }
            $this->Ln();
            $fill = !$fill;
            $this->currentRow++;
            $this->currentRowInTable++;
        }
        $this->Cell(array_sum($w), 0, '', 'T');
        $this->Ln();
    }

    public function writeHTML($html)
    {
        $html = str_replace("\n", ' ', $html);
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($a as $i => $e) {
            if ($i % 2 == 0) {
                // Text.
                if ($this->HREF) {
                    $this->PutLink($this->HREF, $e);
                } else {
                    $this->Write(5, $e);
                }
            } else {
                //Tag
                if ($e{0} == '/') {
                    $this->_closeTag(strtoupper(substr($e, 1)));
                } else {
                    //Extract attributes
                    $a2 = explode(' ', $e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = array();
                    foreach ($a2 as $v) {
                        if (ereg('^([^=]*)=["\']?([^"\']*)["\']?$', $v, $a3)) {
                            $attr[strtoupper($a3[1])] = $a3[2];
                        }
                    }
                    $this->_openTag($tag, $attr);
                }
            }
        }
    }

    public function setLogo($filename)
    {
        $this->_logo = $filename; 
    }

    public function Header()
    {
        if ($this->_logo) {
            $this->Image($this->_logo, $this->lMargin, $this->tMargin, $this->_mm(84), $this->_mm(40));
            $this->setXY($this->lMargin, $this->tMargin + $this->_mm(40));
        }
        if ($this->pheader != '') {
            $this->WriteHTML($this->pheader);
            $this->WriteHTML('<br>');
        }
        //Colors, line width and bold font
        $this->SetFillColor(177, 219, 135);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(.1);
        //$this->SetFont('', 'B');
        //Header
        for($i = 0; $i < count($this->theader); $i++) {
            $this->Cell($this->cellwidth[$i], 7, $this->theader[$i], 1, 0, 'C', 1);
        }
        $this->Ln();
    }

    public function Footer()
    {
        //Colors, line width and bold font
        $this->SetFillColor(177, 219, 135);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(.1);
        //Header
        for($i = 0; $i < count($this->theader); $i++) {
            $this->Cell($this->cellwidth[$i],7,$this->theader[$i],1,0,'C',1);
        }
        $this->Ln();
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetLineWidth(.1);
        $this->SetFont('');
        
        $f = "From " . ($this->currentRowInTable - $this->currentRow + 1) .  " to " . $this->currentRowInTable;
        //$this->Cell(0, 0, $f, 0, 0, "L", false );
        $this->writeHTML($f);
        $this->Ln();
        if ($this->pfooter != '') {
            $this->writeHTML($this->pfooter);
            $this->Ln();
        }
        $this->currentRow = 0;
    }

    public function render()
    {  
        return $this->Output('', 'S');
    }

    protected function _setStyle($tag, $enable)
    {
        //Modify style and select corresponding font
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach (array('B','I','U') as $s) {
            if ($this->$s > 0) {
                $style .= $s;
            }
        }
        $this->SetFont('', $style);
    }  

    protected function _openTag($tag, $attr)
    {
        //Opening tag
        if ($tag == 'B' or $tag == 'I' or $tag == 'U') {
            $this->_setStyle($tag, true);
        }
        if ($tag == 'A') {
            $this->HREF = $attr['HREF'];
        }
        if ($tag == 'BR') {
            $this->Ln(5);
        }
    }

    protected function _closeTag($tag)
    {
        //Closing tag
        if ($tag == 'B' or $tag == 'I' or $tag == 'U') {
            $this->_setStyle($tag, false);
        }
        if ($tag == 'A') {
            $this->HREF = '';
        }
    }

    protected function _mm($px)
    {
        return $px * 25.4 / 72;
    }
}
