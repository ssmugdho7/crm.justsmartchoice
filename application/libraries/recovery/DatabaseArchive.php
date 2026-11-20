<?php
/** MySQL 5.7+ deletion archive. Existing business queries and cascades stay intact. */
final class ScDatabaseArchive
{
    private mysqli $db;
    private string $prefix;
    private array $columns = [];
    private array $relations = [];
    public function __construct(mysqli $db, string $prefix)
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/D', $prefix)) throw new InvalidArgumentException('Invalid database prefix.');
        $this->db=$db;$this->prefix=$prefix;
    }
    private function identifier(string $value): string { return '`'.str_replace('`','``',$value).'`'; }
    public function archiveTable(): string { return $this->prefix.'sc_recovery_rows'; }
    public function excluded(string $table): bool
    {
        if (strpos($table,$this->prefix)!==0) return true;
        $name=substr($table,strlen($this->prefix));
        // Expired authentication material and disposable caches must not be resurrected.
        return strpos($name,'sc_recovery_')===0 || in_array($name,['sessions','ci_sessions','user_auto_login','password_resets','password_reset_tokens','cache','query_cache','recaptcha','csrf_tokens'],true);
    }
    public function schema(?string $only = null): array
    {
        $filter=$only!==null?" AND c.TABLE_NAME='".$this->db->real_escape_string($only)."'":"";
        $rows=$this->db->query("SELECT c.TABLE_NAME,c.COLUMN_NAME,c.DATA_TYPE,c.COLUMN_TYPE,c.IS_NULLABLE,c.EXTRA,c.ORDINAL_POSITION,t.ENGINE FROM information_schema.COLUMNS c JOIN information_schema.TABLES t ON t.TABLE_SCHEMA=c.TABLE_SCHEMA AND t.TABLE_NAME=c.TABLE_NAME WHERE c.TABLE_SCHEMA=DATABASE() AND t.TABLE_TYPE='BASE TABLE' $filter ORDER BY c.TABLE_NAME,c.ORDINAL_POSITION");
        $tables=[];
        while($row=$rows->fetch_assoc()) { if(!$this->excluded($row['TABLE_NAME'])) $tables[$row['TABLE_NAME']][]=$row; }
        if($only===null)$this->columns=$tables;else $this->columns=array_merge($this->columns,$tables);
        return $tables;
    }
    public function triggerName(string $table, string $hash): string { return 'sc_recover_'.substr(hash('sha256',$table),0,16).'_'.substr($hash,0,16); }
    private function loadRelations(): void
    {
        $this->relations=[];
        $result=$this->db->query("SELECT r.CONSTRAINT_NAME,r.TABLE_NAME,r.REFERENCED_TABLE_NAME,r.DELETE_RULE,k.COLUMN_NAME,k.REFERENCED_COLUMN_NAME,k.ORDINAL_POSITION FROM information_schema.REFERENTIAL_CONSTRAINTS r JOIN information_schema.KEY_COLUMN_USAGE k ON k.CONSTRAINT_SCHEMA=r.CONSTRAINT_SCHEMA AND k.CONSTRAINT_NAME=r.CONSTRAINT_NAME AND k.TABLE_NAME=r.TABLE_NAME WHERE r.CONSTRAINT_SCHEMA=DATABASE() AND r.DELETE_RULE IN ('CASCADE','SET NULL') ORDER BY r.TABLE_NAME,r.CONSTRAINT_NAME,k.ORDINAL_POSITION");
        while($row=$result->fetch_assoc()){
            $parent=$row['REFERENCED_TABLE_NAME'];$key=$row['TABLE_NAME'].':'.$row['CONSTRAINT_NAME'];
            if(!isset($this->relations[$parent][$key]))$this->relations[$parent][$key]=['parent'=>$parent,'child'=>$row['TABLE_NAME'],'rule'=>$row['DELETE_RULE'],'keys'=>[]];
            $this->relations[$parent][$key]['keys'][]=[$row['COLUMN_NAME'],$row['REFERENCED_COLUMN_NAME']];
        }
    }
    private function paths(string $table, array $visited=[], array $path=[]): array
    {
        if(isset($visited[$table]))throw new RuntimeException('A circular foreign-key cascade needs a reviewed recovery plan: '.$table);
        $visited[$table]=true;$out=[];
        foreach($this->relations[$table]??[] as $relation){
            $next=array_merge($path,[$relation]);$out[]=$next;
            if($relation['rule']==='CASCADE')$out=array_merge($out,$this->paths($relation['child'],$visited,$next));
        }
        return $out;
    }
    private function definitionHash(string $table, array $columns): string
    {
        $dependencies=[];
        foreach($this->paths($table) as $path){$last=$path[count($path)-1];$dependencies[]=[$path,$this->columns[$last['child']]??[]];}
        return hash('sha256',json_encode([$columns,$dependencies]));
    }
    private function jsonExpression(array $columns, string $alias): string
    {
        $pairs=[];
        foreach($columns as $column){$key=$this->db->real_escape_string($column['COLUMN_NAME']);$field=$alias.'.'.$this->identifier($column['COLUMN_NAME']);$pairs[]="'$key',IF($field IS NULL,NULL,TO_BASE64(CAST($field AS BINARY)))";}
        return 'JSON_OBJECT('.implode(',',$pairs).')';
    }
    public function triggerSql(string $table,array $columns): string
    {
        $archive=$this->identifier($this->archiveTable());$name=$this->db->real_escape_string($table);
        $insert="INSERT INTO $archive (batch_id,table_name,row_data,deleted_at,actor,reason) ";
        $statements=["DECLARE recovery_batch VARCHAR(64); SET recovery_batch=COALESCE(@sc_recovery_batch,UUID());",$insert."VALUES (recovery_batch,'$name',".$this->jsonExpression($columns,'OLD').",UTC_TIMESTAMP(6),COALESCE(@sc_recovery_actor,'database'),'delete');"];
        foreach($this->paths($table) as $path){
            $last=$path[count($path)-1];$child=$last['child'];if($this->excluded($child))continue;
            if(!isset($this->columns[$child]))throw new RuntimeException('A cascade target is outside recovery coverage: '.$child);
            $from='';$where=[];
            foreach($path as $index=>$relation){
                $alias='r'.$index;$conditions=[];
                foreach($relation['keys'] as [$column,$referenced]){$left=$alias.'.'.$this->identifier($column);$right=($index===0?'OLD':'r'.($index-1)).'.'.$this->identifier($referenced);$conditions[]=$left.'='.$right;}
                if($index===0){$from=$this->identifier($relation['child']).' '.$alias;$where=$conditions;}
                else $from.=' INNER JOIN '.$this->identifier($relation['child']).' '.$alias.' ON '.implode(' AND ',$conditions);
            }
            $childName=$this->db->real_escape_string($child);$reason=$last['rule']==='SET NULL'?'reference_clear':'cascade_delete';
            $statements[]=$insert."SELECT recovery_batch,'$childName',".$this->jsonExpression($this->columns[$child],'r'.(count($path)-1)).",UTC_TIMESTAMP(6),COALESCE(@sc_recovery_actor,'database'),'$reason' FROM $from WHERE ".implode(' AND ',$where).';';
        }
        return 'CREATE TRIGGER '.$this->identifier($this->triggerName($table,$this->definitionHash($table,$columns))).' BEFORE DELETE ON '.$this->identifier($table).' FOR EACH ROW BEGIN '.implode(' ',$statements).' END';
    }
    public function install(?string $only = null): array
    {
        $archive=$this->identifier($this->archiveTable());
        $this->db->query("CREATE TABLE IF NOT EXISTS $archive (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,batch_id VARCHAR(64) NOT NULL,table_name VARCHAR(64) NOT NULL,row_data LONGTEXT NOT NULL,deleted_at DATETIME(6) NOT NULL,actor VARCHAR(100) NOT NULL,reason VARCHAR(32) NOT NULL DEFAULT 'delete',KEY recovery_batch(batch_id,id),KEY recovery_date(deleted_at),KEY recovery_table(table_name,id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $registry=$this->identifier($this->prefix.'sc_recovery_coverage');
        $this->db->query("CREATE TABLE IF NOT EXISTS $registry (table_name VARCHAR(64) NOT NULL PRIMARY KEY,schema_hash CHAR(64) NOT NULL,updated_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        foreach (['DELETE','UPDATE'] as $action) {
            $guard='sc_immutable_'.substr(hash('sha256',$this->archiveTable()),0,16).'_'.strtolower($action);
            $escaped=$this->db->real_escape_string($guard);
            $exists=(int)$this->db->query("SELECT COUNT(*) n FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE() AND TRIGGER_NAME='$escaped'")->fetch_assoc()['n'];
            if(!$exists)$this->db->query('CREATE TRIGGER '.$this->identifier($guard).' BEFORE '.$action.' ON '.$archive." FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Recovery archives are immutable; use a reviewed maintenance procedure'");
        }
        $all=$this->schema();$this->loadRelations();$tables=$all;
        if($only!==null){
            $affected=[$only=>true];$again=true;
            while($again){$again=false;foreach($this->relations as $parent=>$relations){foreach($relations as $relation){if(isset($affected[$relation['child']])&&!isset($affected[$parent])){$affected[$parent]=true;$again=true;}}}}
            $tables=array_intersect_key($all,$affected);
        }
        $installed=0;$unchanged=0;
        foreach($tables as $table=>$columns){
            if($columns[0]['ENGINE']!=='InnoDB')throw new RuntimeException('A non-transactional table requires a separate recovery plan: '.$table);
            $hash=$this->definitionHash($table,$columns);$name=$this->triggerName($table,$hash);
            $q=$this->db->prepare("SELECT schema_hash FROM $registry WHERE table_name=?");$q->bind_param('s',$table);$q->execute();$old=$q->get_result()->fetch_assoc();$q->close();
            $q=$this->db->prepare('SELECT COUNT(*) n FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE() AND TRIGGER_NAME=? AND EVENT_OBJECT_TABLE=?');$q->bind_param('ss',$name,$table);$q->execute();$exists=(int)$q->get_result()->fetch_assoc()['n'];$q->close();
            if($old&&$old['schema_hash']===$hash&&$exists===1){$unchanged++;continue;}
            // Never silently replace somebody else's trigger with the same name.
            if($exists && !$old)throw new RuntimeException('An unexpected recovery trigger exists: '.$name);
            if(!$exists)$this->db->query($this->triggerSql($table,$columns));
            $q=$this->db->prepare("INSERT INTO $registry(table_name,schema_hash,updated_at) VALUES(?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE schema_hash=VALUES(schema_hash),updated_at=VALUES(updated_at)");$q->bind_param('ss',$table,$hash);$q->execute();$q->close();
            // Install the new guard before retiring the old one: no unprotected gap.
            if($old && $old['schema_hash']!==$hash) $this->db->query('DROP TRIGGER IF EXISTS '.$this->identifier($this->triggerName($table,$old['schema_hash'])));
            $installed++;
        }
        return ['protected_tables'=>count($tables),'installed'=>$installed,'unchanged'=>$unchanged];
    }
    public function health(): array
    {
        $missing=[];$changed=[];$tables=$this->schema();$this->loadRelations();$registry=$this->identifier($this->prefix.'sc_recovery_coverage');
        $known=[];$r=$this->db->query("SELECT table_name,schema_hash FROM $registry");while($x=$r->fetch_assoc())$known[$x['table_name']]=$x['schema_hash'];
        $triggers=[];$r=$this->db->query('SELECT TRIGGER_NAME FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE()');while($x=$r->fetch_assoc())$triggers[$x['TRIGGER_NAME']]=true;
        foreach($tables as $table=>$columns){if(!isset($known[$table]))$missing[]=$table;elseif($known[$table]!==$this->definitionHash($table,$columns))$changed[]=$table;elseif(!isset($triggers[$this->triggerName($table,$known[$table])]))$missing[]=$table;}
        return ['protected_tables'=>count($tables)-count($missing)-count($changed),'missing_tables'=>$missing,'changed_tables'=>$changed];
    }
}
