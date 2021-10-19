<?php
class AdminWebModel extends ModelBase{

## News
public function getSiteNews(){
return $this->provider->fetchScalar('SELECT news_text FROM g_summary');
}

public function setSiteNews($news){
$this->provider->executeQuery('UPDATE g_summary SET news_text="%s"', array($news));
}

public function getGlobalSiteNews(){
return $this->provider->fetchScalar('SELECT gnews_text FROM g_summary');
}
public function getGlobalSitevoting(){
return $this->provider->fetchScalar('SELECT new_voting FROM g_summary');
}
public function setGlobalPlayervot($new_voting){
$this->provider->executeQuery('UPDATE g_summary SET new_voting="%s"', array($new_voting));

$this->provider->executeQuery('UPDATE p_players SET new_voting=1');
}

public function setGlobalPlayerNews($news){
$this->provider->executeQuery('UPDATE g_summary SET gnews_text="%s"', array($news));
$flag = trim($news) != "" ? 1 : 0;
$this->provider->executeQuery('UPDATE p_players SET new_gnews=%s', array($flag));
}

## ControlMember
public function GetMeber( $pageIndexMeber, $pageSizeMeber )
{
return $this->provider->fetchResultSet( "SELECT * FROM `p_players` WHERE gold_num  > 235 LIMIT %s,%s;;", array( $pageIndexMeber * $pageSizeMeber, $pageSizeMeber ) );
}

public function getMeberCount( )
{
return $this->provider->fetchScalar( "SELECT COUNT(*) FROM `p_players` ;" );
}
public function blocked( $blocked,$id )
{
$this->provider->executeQuery( "UPDATE p_players g SET g.is_blocked='%s' WHERE id= '%s'", array($blocked,$id) );
}

public function getPlayerId( $name )
{
return $this->provider->fetchScalar( "SELECT p.id FROM p_players p WHERE p.name='%s'", array( $name ) );
}
public function getfilename($id){
return $this->provider->fetchResultSet( "SELECT * FROM filename WHERE idp='%s' order by id desc", array( $id ) );
}  

public function AddGoldPlayer($gold){
$this->provider->executeQuery('UPDATE p_players SET gold_num=gold_num+%s', array( $gold ) );
}

}
?>