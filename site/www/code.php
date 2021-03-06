<?php

    require_once '../lib/lib.everything.php';
    require_once '../lib/lib.qrcode.php';
    
    enforce_master_on_off_switch($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    
    /**** ... ****/
    
    $url = 'http://'.get_domain_name().get_base_dir().'/atlas.php?id='.urlencode($_GET['print']);
    $qrc = QRCode::getMinimumQRCode($url, QR_ERROR_CORRECT_LEVEL_Q);
    $img = $qrc->createImage(16, 0);

    header('Content-type: image/png');
    header("X-Content: {$url}");
    imagepng($img);
    imagedestroy($img);

?>