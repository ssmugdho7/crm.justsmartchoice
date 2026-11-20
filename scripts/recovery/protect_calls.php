<?php
/** Token-based native-call migration. CLI only; no customer data is read. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
function sc_recovery_rewrite_source(string $source): array
{
    $functions = array_fill_keys(['unlink','rmdir','rename','copy','move_uploaded_file','file_put_contents','fopen','fclose'], true);
    $tokens = token_get_all($source);$result='';$changes=0;$previous=null;
    $significant=[];
    foreach ($tokens as $index=>$token) {
        $text=is_array($token)?$token[1]:$token;
        $type=is_array($token)?$token[0]:null;
        $name=strtolower(ltrim($text,'\\'));
        $isCall=($type===T_STRING || (defined('T_NAME_FULLY_QUALIFIED') && $type===T_NAME_FULLY_QUALIFIED)) && isset($functions[$name]);
        if ($isCall) {
            $next=null;for($j=$index+1;$j<count($tokens);$j++){if(is_array($tokens[$j])&&in_array($tokens[$j][0],[T_WHITESPACE,T_COMMENT,T_DOC_COMMENT],true))continue;$next=is_array($tokens[$j])?$tokens[$j][1]:$tokens[$j];break;}
            $prev=$significant[count($significant)-1]??null;
            $before=$significant[count($significant)-2]??null;
            if ($next==='(' && !in_array($prev,[T_OBJECT_OPERATOR,T_DOUBLE_COLON,T_NULLSAFE_OBJECT_OPERATOR,T_FUNCTION,T_NEW],true) && !($prev==='&'&&$before===T_FUNCTION)) {
                $text="(\\function_exists('sc_recovery_".$name."') ? '\\sc_recovery_".$name."' : '\\".$name."')";$changes++;
            }
        }
        // Native callbacks used by recursive cleanup must use the same protection.
        if ($type===T_CONSTANT_ENCAPSED_STRING && in_array(strtolower(substr($text,1,-1)),['unlink','rmdir'],true)) {
            $callback=strtolower(substr($text,1,-1));
            $recent=array_slice($significant,-3);
            if (in_array('array_map',$recent,true)||in_array('call_user_func',$recent,true)||in_array('register_shutdown_function',$recent,true)) {$text="(\\function_exists('sc_recovery_".$callback."') ? 'sc_recovery_".$callback."' : '".$callback."')";$changes++;}
        }
        $result.=$text;
        if (!in_array($type,[T_WHITESPACE,T_COMMENT,T_DOC_COMMENT],true)) $significant[]=is_array($token)?($type===T_STRING?strtolower($token[1]):$type):$token;
    }
    return [$result,$changes];
}
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== __FILE__) return;
$root=realpath($argv[1]??dirname(__DIR__,2));$apply=in_array('--apply',$argv,true);$changed=[];
foreach (['application','modules','system/helpers','assets/plugins/elFinder','assets/plugins/elfinder'] as $sub) {
    if (!is_dir($root.'/'.$sub)) continue;
    $iterator=new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(new RecursiveDirectoryIterator($root.'/'.$sub,FilesystemIterator::SKIP_DOTS),function($file){return !$file->isLink() && !in_array($file->getFilename(),['vendor','node_modules','.git','uploads','media','recovery','PHPExcel'],true);}));
    foreach ($iterator as $file) {
        $path=$file->getPathname();$relative=substr($path,strlen($root)+1);
        if (substr($path,-4)!=='.php'||in_array($relative,['application/helpers/recovery_helper.php','application/hooks/ScRecoveryHook.php'],true)||strpos($relative,'modules/application/')===0||strpos($relative,'modules/system/')===0||strpos($relative,'modules/assets/')===0) continue;
        $source=file_get_contents($path);
        if (!preg_match('/\b(unlink|rmdir|rename|copy|move_uploaded_file|file_put_contents|fopen|fclose)\b/i',$source))continue;
        [$new,$count]=sc_recovery_rewrite_source($source);
        if (!$count)continue;
        if ($apply && file_put_contents($path,$new)===false)throw new RuntimeException('Could not write '.$relative);
        $changed[$relative]=['calls'=>$count,'before'=>hash('sha256',$source),'after'=>hash('sha256',$new)];
    }
}
echo json_encode($changed,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),"\n";
