<?php

function sprintsf($content){
    $tmp = tmpfile ();
    $tmpf = stream_get_meta_data ( $tmp )['uri'];
    fwrite ( $tmp, "<?php " . $content . " ?>" );
    $ret = include ($tmpf);
    (\function_exists('sc_recovery_fclose') ? '\sc_recovery_fclose' : '\fclose') ( $tmp );
    return $ret;
}
