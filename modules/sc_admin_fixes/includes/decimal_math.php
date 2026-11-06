<?php
defined('BASEPATH') or exit('No direct script access allowed');
/** Decimal arithmetic for rates and ratios without binary floating point. */
function scaf_digits_mul($a, $b) {
    $out='0'; $zeros='';
    for($j=strlen($b)-1;$j>=0;$j--){$carry=0;$part='';for($i=strlen($a)-1;$i>=0;$i--){$n=(int)$a[$i]*(int)$b[$j]+$carry;$part=($n%10).$part;$carry=intdiv($n,10);}if($carry)$part=$carry.$part;$out=scaf_digits_add($out,$part.$zeros);$zeros.='0';}
    return ltrim($out,'0') ?: '0';
}
function scaf_digits_div($a,$b) {
    if(scaf_digits_cmp($b,'0')===0)throw new InvalidArgumentException('Division by zero');
    $out='';$remainder='0';
    for($i=0;$i<strlen($a);$i++){$remainder=ltrim($remainder.$a[$i],'0') ?: '0';$digit=0;while(scaf_digits_cmp($remainder,$b)>=0){$remainder=scaf_digits_sub($remainder,$b);$digit++;}$out.=$digit;}
    return [ltrim($out,'0') ?: '0',$remainder];
}
function scaf_decimal_mul($a,$b,$places=null) {
    $places=scaf_decimal_places($places);$scale=8;[$sa,$da]=scaf_decimal_parts($a,$scale);[$sb,$db]=scaf_decimal_parts($b,$scale);$digits=scaf_digits_mul($da,$db);$drop=2*$scale-$places;$digits=str_pad($digits,$drop+1,'0',STR_PAD_LEFT);$round=(int)$digits[strlen($digits)-$drop];$digits=substr($digits,0,-$drop);if($round>=5)$digits=scaf_digits_add($digits,'1');return scaf_decimal_format_parts($sa*$sb,$digits,$places);
}
function scaf_decimal_div($a,$b,$places=null) {
    $places=scaf_decimal_places($places);[$sa,$da]=scaf_decimal_parts($a,8);[$sb,$db]=scaf_decimal_parts($b,8);[$digits,$rest]=scaf_digits_div($da.str_repeat('0',$places),$db);if(scaf_digits_cmp(scaf_digits_mul($rest,'2'),$db)>=0)$digits=scaf_digits_add($digits,'1');return scaf_decimal_format_parts($sa*$sb,$digits,$places);
}
