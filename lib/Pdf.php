<?php
require_once 'Stylesheet.php';

class Pdf {
    public static function render($template,$data,$containerViewFile = null) {
        require_once Pimple::instance()->getRessource('lib/mpdf50/mpdf.php');
        if ($containerViewFile)
            $container = new View($containerViewFile);
        $view = new View($template);
        if (!is_array($data)) {
            $data = ArrayUtil::fromObject($data);
        }
        $cData = $data;
        $cData['body'] = $view->render($data);
        $css = "";
        $pdf = new mPDF();
        if ($container) {
            $html = $container->render($cData);
            self::readCss($pdf,$container);
        } else {
            $html = $cData['body'];
        }
        self::readCss($pdf,$view);
        
        $pdf->WriteHTML($html);
        return $pdf->Output('','S');
    }
    private static function readCss($pdf,$view) {
        $css = "";
        $files = $view->getCssFiles();
        foreach($files as $file) {
            $css = Stylesheet::minify($file);
            $pdf->WriteHtml($css,1);
        }
        return $css;
    }
}