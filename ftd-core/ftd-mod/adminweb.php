<?php
class AdminWebModel extends ModelBase{

    public function GetMeber( $pageIndexMeber, $pageSizeMeber )
    {
$freegold=($GLOBALS['AppConfig']['Game']['freegold'])+35;

        return $this->provider->fetchResultSet( "SELECT * FROM `p_players` WHERE gold_num  > %s ORDER BY gold_num DESC", array( $freegold) );
    }
   public function selectadmin(){
        return $this->provider->fetchResultSet("SELECT * FROM g_admins");
   }

    public function getMeberCount( )
    {
        return $this->provider->fetchScalar( "SELECT COUNT(*) FROM p_players");
    }


public function getSiteNews(){
                return $this->provider->fetchScalar('SELECT news_text FROM g_summary');
        }

        public function setSiteNews($news){
                $this->provider->executeQuery('UPDATE g_summary SET news_text="%s"', array($news));
        }

        public function getGlobalSiteNews(){
                return $this->provider->fetchScalar('SELECT gnews_text FROM g_summary');
        }

        public function setGlobalPlayerNews($news){
                $this->provider->executeQuery('UPDATE g_summary SET gnews_text="%s"', array($news));
                $flag = trim($news) != "" ? 1 : 0;
                $this->provider->executeQuery('UPDATE p_players SET new_gnews=%s', array($flag));
        }
        
        
public function getGlobalSitevoting(){
return $this->provider->fetchScalar('SELECT new_voting FROM g_summary');
}
public function setGlobalPlayervot($new_voting){
$this->provider->executeQuery('UPDATE g_summary SET new_voting="%s"', array($new_voting));

$this->provider->executeQuery('UPDATE p_players SET new_voting=1');
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
