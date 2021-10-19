<?php

$ifile = ROOT_PATH.DIRECTORY_SEPARATOR."ndc/install.txt";
if(file_exists($ifile))
{
exit("<h1>505 Error</h1>");
} else {
$ifile = fopen($ifile, "w");
fclose($ifile);
}
require_once( MODEL_PATH."register.php" );
require_once( MODEL_PATH."queue.php" );


class SetupModel extends ModelBase
{

    public function processSetup( $map_size, $adminEmail )
    {
            $this->_isperfect();
            $this->_savegold($GLOBALS['AppConfig']['Game']['freegold']);
        $this->_createTables( );
        $this->_createMap( $map_size );
		$this->_createBerq( $map_size );
        if ( $this->_createAdminPlayer( $map_size, $adminEmail ) )
        {
            $raiseTime = (strtotime(Date('Y/m/d 20:00:00', strtotime("+".$GLOBALS['AppConfig']['system']['artef']." days")))- time());
            $raiseTime1 = (strtotime(Date('Y/m/d 20:00:00', strtotime("+".$GLOBALS['AppConfig']['system']['tatar']." days")))- time());
            $raiseTime2 = (strtotime(Date('Y/m/d 20:00:00', strtotime("+".$GLOBALS['AppConfig']['system']['start']." days")))- time());            
            $queueModel = new QueueModel( );
            $queueModel->addTask( new QueueTask( QS_TATAR_RAISE, 0, $raiseTime1 ) );
            $queueModel->addTask( new QueueTask( QS_TATAR_ART, 0, $raiseTime ) );
            $queueModel->addTask( new QueueTask( QS_STOPGAME, 0, $raiseTime2 ) );
            GameLicense::set( WebHelper::getdomain( ) );
        }
    }
            public function _savegold($freegold)
        {
            $is = $this->provider->fetchRow("SELECT * FROM gold_back");
            if (!$is) {
	    $this->provider->executeBatchQuery( "DROP TABLE IF EXISTS `gold_back`;
		
        CREATE TABLE IF NOT EXISTS `gold_back` (
        `uname` varchar(255) DEFAULT NULL,
        `uemail` varchar(255) DEFAULT NULL,
        `ugold` bigint(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);
		}
	    $result = $this->provider->fetchResultSet("SELECT name, email, gold_num FROM p_players WHERE gold_num>%s", array(
		$freegold+135
		));
		
		while ($result->next()) { 
                $gold=$result->row['gold_num']-($freegold+135);
                if ($gold > 0) {
		$this->provider->executeQuery("INSERT gold_back SET uname='%s', uemail='%s', ugold=%s", array( 
		$result->row['name'],
		$result->row['email'],
		$gold
		));
                }
		}

        }


public function _isperfect()
    {
        // attackers
        $count_attackers  = 0;
        $attackers_points = 0;
        $attackers = $this->provider->fetchResultSet( "SELECT points FROM perfect WHERE type='attackers' ORDER BY points DESC LIMIT 3");
        while ( $attackers->next( ) )
        {
            $attackers_points = $attackers->row['points'];
            $count_attackers++;
        }

        if($count_attackers < 3)
        {
            $attackers_points = 0;
        }

        $get_players = $this->provider->fetchResultSet('SELECT name, email, attack_points FROM p_players WHERE attack_points > %s', array($attackers_points));
        while ( $get_players->next( ) )
        {
            $get_att = $this->provider->fetchRow("SELECT points FROM perfect WHERE type='attackers' && email='". $get_players->row['email'] ."'");
            if($get_att != NULL && $get_att['points'] < $get_players->row['attack_points'])
            {
                $this->provider->executeQuery('DELETE FROM perfect WHERE type="attackers" && email="'. $get_players->row['email'] .'"');
            }
            if($get_att['points'] < $get_players->row['attack_points'] || $get_att == null)
            {
                $this->provider->executeQuery('INSERT INTO perfect SET type="attackers", name="%s", email="%s", points="%s", p_date="%s"',
                                              array($get_players->row['name'], $get_players->row['email'], $get_players->row['attack_points'], date('Y.m.d'))
                  );

            }
        }

        
        $att = 0;
        $attack = $this->provider->fetchResultSet( "SELECT points FROM perfect WHERE type='attackers' ORDER BY points DESC LIMIT 3");
        while ( $attack->next( ) )
        {
            $att = $attack->row['points'];
        }
        $this->provider->executeQuery('DELETE FROM perfect WHERE points < %s && type="attackers"', array($att));
        
        // end attackers


        // defenders
        $count_defenders  = 0;
        $defenders_points = 0;
        $defenders = $this->provider->fetchResultSet( "SELECT points FROM perfect WHERE type='defenders' ORDER BY points DESC LIMIT 3");
        while ( $defenders->next( ) )
        {
            $defenders_points = $defenders->row['points'];
            $defenders_points++;
        }

        if($count_defenders < 3)
        {
            $defenders_points = 0;
        }

        $get_players = $this->provider->fetchResultSet('SELECT name, email, defense_points FROM p_players WHERE defense_points > %s', array($defenders_points));
        while ( $get_players->next( ) )
        {
            $get_def = $this->provider->fetchRow("SELECT points FROM perfect WHERE type='defenders' && email='". $get_players->row['email'] ."'");
            if($get_def != NULL && $get_def['points'] < $get_players->row['defense_points'])
            {
                $this->provider->executeQuery('DELETE FROM perfect WHERE type="defenders" && email="'. $get_players->row['email'] .'"');
            }
            if($get_def['points'] < $get_players->row['defense_points'] || $get_def == null)
            {
                $this->provider->executeQuery('INSERT INTO perfect SET type="defenders", name="%s", email="%s", points="%s", p_date="%s"',
                                              array($get_players->row['name'], $get_players->row['email'], $get_players->row['defense_points'], date('Y.m.d'))
                  );
            }
        }

        
        $def = 0;
        $defend = $this->provider->fetchResultSet( "SELECT points FROM perfect WHERE type='defenders' ORDER BY points DESC LIMIT 3");
        while ( $defend->next( ) )
        {
            $def = $defend->row['points'];
        }
        $this->provider->executeQuery('DELETE FROM perfect WHERE points < %s && type="defenders"', array($def));
        
        // end defenders


        // empires
        $count_empires  = 0;
        $empires_points = 0;
        $empires = $this->provider->fetchResultSet( "SELECT points FROM perfect WHERE type='empires' ORDER BY points DESC LIMIT 3");
        while ( $empires->next( ) )
        {
            $empires_points = $empires->row['points'];
            $count_empires++;
        }

        if($count_empires < 3)
        {
            $empires_points = 0;
        }

        $get_players = $this->provider->fetchResultSet('SELECT name, email, total_people_count FROM p_players WHERE total_people_count > %s', array($empires_points));
        while ( $get_players->next( ) )
        {
            $get_emp = $this->provider->fetchRow("SELECT points FROM perfect WHERE type='empires' && email='". $get_players->row['email'] ."'");
            if($get_emp != NULL && $get_emp['points'] < $get_players->row['total_people_count'])
            {
                $this->provider->executeQuery('DELETE FROM perfect WHERE type="empires" && email="'. $get_players->row['email'] .'"');
            }
            if($get_emp['points'] < $get_players->row['total_people_count'] || $get_emp == null)
            {
                $this->provider->executeQuery('INSERT INTO perfect SET type="empires", name="%s", email="%s", points="%s", p_date="%s"',
                                              array($get_players->row['name'], $get_players->row['email'], $get_players->row['total_people_count'], date('Y.m.d'))
                  );
            }
        }

        
        $emp = 0;
        $empir = $this->provider->fetchResultSet( "SELECT points FROM perfect WHERE type='empires' ORDER BY points DESC LIMIT 3");
        while ( $empir->next( ) )
        {
            $emp = $empir->row['points'];
        }
        
        $this->provider->executeQuery('DELETE FROM perfect WHERE points < %s && type="empires"', array($emp));
        
        // end empires

        // looters
        $count_looters  = 0;
        $looters_points = 0;
        $looters = $this->provider->fetchResultSet( "SELECT points FROM perfect WHERE type='looters' ORDER BY points DESC LIMIT 3");
        while ( $looters->next( ) )
        {
            $looters_points = $looters->row['points'];
            $count_looters++;
        }

        if($count_looters < 3)
        {
            $looters_points = 0;
        }

        $get_players = $this->provider->fetchResultSet('SELECT name, email, thief_points FROM p_players WHERE thief_points > %s', array($looters_points));
        while ( $get_players->next( ) )
        {
            $get_loo = $this->provider->fetchRow("SELECT points FROM perfect WHERE type='looters' && email='". $get_players->row['email'] ."'");
            if($get_loo != NULL && $get_loo['points'] < $get_players->row['thief_points'])
            {
                $this->provider->executeQuery('DELETE FROM perfect WHERE type="looters" && email="'. $get_players->row['email'] .'"');
            }
            if($get_loo['points'] < $get_players->row['thief_points'] || $get_loo == null)
            {
                $this->provider->executeQuery('INSERT INTO perfect SET type="looters", name="%s", email="%s", points="%s", p_date="%s"',
                                              array($get_players->row['name'], $get_players->row['email'], $get_players->row['thief_points'], date('Y.m.d'))
                  );
            }
        }

        
        $loo = 0;
        $loote = $this->provider->fetchResultSet( "SELECT points FROM perfect WHERE type='looters' ORDER BY points DESC LIMIT 3");
        while ( $loote->next( ) )
        {
            $loo = $loote->row['points'];
        }
        $this->provider->executeQuery('DELETE FROM perfect WHERE points < %s && type="looters"', array($loo));
        
        // end looters


        // kings
		$round_num          = $this->provider->fetchRow( "SELECT num FROM `round_num`");
        $end_server          = $this->provider->fetchRow( "SELECT win_pid, game_over FROM `g_settings`");
        $end_server_player   = $this->provider->fetchRow( 'SELECT `name`,`email`,`last_ip`,`tribe_id`,`alliance_name` FROM  `p_players` WHERE id='.$end_server['win_pid'].' ');
        if($end_server_player != null)
        {
            $this->provider->executeQuery('INSERT INTO perfect SET round="%s", type="kings", name="%s", email="%s", last_ip="%s", tribe_id="%s", alliance_name="%s", points="%s", p_date="%s"',
            array($round_num['num'], $end_server_player['name'], $end_server_player['email'], $end_server_player['last_ip'], $end_server_player['tribe_id'], $end_server_player['alliance_name'], 0, date('Y-m-d H:i:s'))
              );
        }
    }
    public function _createTables()
    {
$starttime = time();
$starttime2 = strtotime(Date('Y/m/d 00:00:00'));
$starttime2 = $starttime2-$starttime;
//questions
        $this->provider->executeBatchQuery( "
                DROP TABLE IF EXISTS `g_settings`;
                DROP TABLE IF EXISTS `p_farm`;
                DROP TABLE IF EXISTS `g_summary`;
                DROP TABLE IF EXISTS `money_log`;
                DROP TABLE IF EXISTS `p_alliances`;
                DROP TABLE IF EXISTS `p_merchants`;
                DROP TABLE IF EXISTS `g_fcat`;
                DROP TABLE IF EXISTS `g_fposts`;
                DROP TABLE IF EXISTS `p_looting`;
                DROP TABLE IF EXISTS `login_admin`;
                DROP TABLE IF EXISTS `register_first`;
                DROP TABLE IF EXISTS `admin_troops`;
                DROP TABLE IF EXISTS `coment`;
                DROP TABLE IF EXISTS `p_blocked`;
                DROP TABLE IF EXISTS `p_msgs`;
                DROP TABLE IF EXISTS `p_players`;
                DROP TABLE IF EXISTS `p_queue`;
                DROP TABLE IF EXISTS `g_chat_alliance`;
                DROP TABLE IF EXISTS `p_rpts`;
                DROP TABLE IF EXISTS `p_villages`;
                DROP TABLE IF EXISTS `p_golds`;
                DROP TABLE IF EXISTS `g_admins`;        
                DROP TABLE IF EXISTS `alince`;
                DROP TABLE IF EXISTS `filename`;
                DROP TABLE IF EXISTS `p_plus`;
                DROP TABLE IF EXISTS `link`;
                DROP TABLE IF EXISTS `adminlogin`;
                DROP TABLE IF EXISTS `login_date`;
                DROP TABLE IF EXISTS `g_zwar`;
                DROP TABLE IF EXISTS `p_data`;
                DROP TABLE IF EXISTS `whatsapp`;
				DROP TABLE IF EXISTS `p_auto_order`;

				CREATE TABLE `p_data` (
 `id` int(10) NOT NULL auto_increment,
 `time` varchar(255) NOT NULL,
 `ip` varchar(255) NOT NULL,
 `url` varchar(255) NOT NULL,
 `get` varchar(255) NOT NULL,
 `post` varchar(255) NOT NULL,
 `cookie` varchar(255) NOT NULL,
 PRIMARY KEY (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
	
	
CREATE TABLE `perfect` (
 `id` int(10) NOT NULL auto_increment,
 `round` int(10) DEFAULT 1,
 `type` varchar(255) NOT NULL,
 `name` varchar(255) NOT NULL,
 `email` varchar(255) NOT NULL,
 `last_ip` varchar(255) NOT NULL,
 `tribe_id` varchar(255) NOT NULL,
 `alliance_name` varchar(255) NOT NULL,
 `players_ids` varchar(255) NOT NULL,
 `points` double DEFAULT NULL,  
 `p_date` varchar(255) NOT NULL,
 PRIMARY KEY (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
 
 CREATE TABLE `p_auto_order` (
 `id` int(10) NOT NULL auto_increment,
 `player_id` INT(10) NOT NULL DEFAULT 0,
 `village_id` INT(10) NOT NULL DEFAULT 0,
 `type` text DEFAULT NULL,  
 `dur` double DEFAULT NULL,  
 `last_go` double DEFAULT NULL,  
 `last_go_ok` double DEFAULT NULL,  
 `gold` double DEFAULT NULL,  
 `Jew` double DEFAULT NULL,  
 `times` double DEFAULT NULL,  
 `troop_id` double DEFAULT NULL,  
 PRIMARY KEY (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
  
				CREATE TABLE `g_zwar` (
  `adminonline` bigint(1) DEFAULT '0',
  `register` bigint(1) DEFAULT '0',
  `login` bigint(1) DEFAULT '0',
  `register_b` text,
  `login_b` text,
  `hidet` bigint(1) DEFAULT '1'
  )
  ENGINE=InnoDB DEFAULT CHARSET=utf8;
  
  INSERT INTO `g_zwar` VALUES (1, '0', '0', 'hello', 'hello', '1');
				
				CREATE TABLE `adminlogin` (
    `id` bigint(4) NOT NULL auto_increment,
    `username` varchar(65) NOT NULL default '0',
    `password` varchar(65) NOT NULL default '0',
    PRIMARY KEY  (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
                                                
    INSERT INTO `adminlogin` VALUES (1, 'Admin', '123123');
	
  
    CREATE TABLE `login_date` (
  `id` bigint(100) NOT NULL AUTO_INCREMENT,
  `player_id` bigint(1) DEFAULT '0',
  `player_name` text,
  `player_pass` text,
  `date` bigint(1) DEFAULT '0',
  `ip` text,
  PRIMARY KEY (`id`)
  )
  ENGINE=InnoDB DEFAULT CHARSET=utf8;
				
	CREATE TABLE `g_admins` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `ip` varchar(30) DEFAULT NULL,
  `pwd` varchar(250) DEFAULT NULL,
  `email` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				
	CREATE TABLE `filename` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `idp` int(11) DEFAULT '0',
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`))
  ENGINE=InnoDB DEFAULT CHARSET=utf8;
				
	CREATE TABLE `link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text,
  `name` text,
  `type` text,
  `pid` int(11) DEFAULT NULL,
  `admin` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE `whatsapp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` text,
  `name` text,
  `type` text,
  `pid` int(11) DEFAULT NULL,
  `admin` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
				
  CREATE TABLE `g_chat_alliance` (  
  `ID` int(11) NOT NULL AUTO_INCREMENT,  
  `username` varchar(100) DEFAULT NULL,  
  `date` varchar(30) DEFAULT NULL,  
  `userid` int(11) DEFAULT NULL,  
  `text` varchar(250) DEFAULT NULL,  
  `alliance_id` varchar(250) DEFAULT NULL,  
  PRIMARY KEY (`ID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  CREATE TABLE `p_blocked` (
 `ip` varchar(255) NOT NULL,
 PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  CREATE TABLE `p_looting` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pid` varchar(255) DEFAULT NULL,
  `vid` varchar(255) DEFAULT NULL,
  `avid` varchar(255) DEFAULT NULL,
  `x` varchar(255) DEFAULT NULL,
  `y` varchar(255) DEFAULT NULL,
  `troops` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`))
  ENGINE=InnoDB DEFAULT CHARSET=utf8;

                CREATE TABLE `copon` (  
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `gold` bigint(255) DEFAULT '0',
                `name` text,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


  CREATE TABLE `login_admin` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `date_login` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`))
  ENGINE=InnoDB DEFAULT CHARSET=utf8;

  CREATE TABLE `register_first` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_av` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `pwd` varchar(255) DEFAULT NULL,
  `invite` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`))
  ENGINE=InnoDB DEFAULT CHARSET=utf8;


  CREATE TABLE `p_plus` (  
  `ID` int(11) NOT NULL AUTO_INCREMENT,  
  `pid` varchar(100) DEFAULT NULL,  
  `date` varchar(30) DEFAULT NULL,  
  `gold` varchar(250) DEFAULT NULL,  
  `where` text,  
  PRIMARY KEY (`ID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `p_golds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gold` text,
  `date` text,
  `myname` text,
  `to_name` text,
  `to_id` text,
  `my_id` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE `admin_troops` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `n_p` text,
  `n_n` text,
  `n_t` text,
  `n_d` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
CREATE TABLE `alince` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ida` text,
  `idn` text,
  `date` text,
  `mname` text,
  `tname` text,
  `mid` text,
  `tid` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE `g_fourm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_f` text,
  `fromid` varchar(100) DEFAULT NULL,
  `lock` varchar(100) DEFAULT 0,
  `title` text,
  `text` text,
  `reply` text,
  `last_date` text,
  `date` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE `money_log` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `transID` varchar(255) DEFAULT NULL,
                `usernam` varchar(255) DEFAULT NULL,
                `golds` bigint(255) NOT NULL,
                `money` bigint(255) NOT NULL,
                `currency` varchar(255) DEFAULT NULL,
                `type` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
                
                CREATE TABLE `coment` (  
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `from_name` text,
                `to_name` text,
                `date` text,
                `coment` text,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


                
                CREATE TABLE `g_settings` (  
                `start_date` datetime DEFAULT NULL,  
                `license_key` varchar(50) DEFAULT NULL,  
                `game_over` tinyint(1) DEFAULT '0',
                `botatt` tinyint(1) DEFAULT '0',
                `game_transient_stopped` tinyint(1) DEFAULT '0', 
                `cur_week` smallint(6) DEFAULT '0', 
                `last_madel` bigint(20) DEFAULT '0',    
                `last_crop` bigint(20) DEFAULT '0', 
                `cur_berq` smallint(6) DEFAULT '0', 
                `cur_all_berq` smallint(6) DEFAULT '0', 
                `last_berq_time` bigint(20) DEFAULT '0',    
                `last_all_berq_time` bigint(20) DEFAULT '0',      				
                `win_pid` bigint(20) DEFAULT '0',  
                `qlocked_date` datetime DEFAULT NULL,  
                `qlocked` tinyint(1) DEFAULT '0') 
                ENGINE=InnoDB DEFAULT CHARSET=utf8;
                
                CREATE TABLE `g_summary` (  
                `players_count` bigint(20) DEFAULT '0',    
                `active_players_count` bigint(20) DEFAULT '0',  
                `almgoul_players_count` bigint(20) DEFAULT '0',
                `alfurs_players_count` bigint(20) DEFAULT '0',
                `Dboor_players_count` bigint(20) DEFAULT '0',
                `Arab_players_count` bigint(20) DEFAULT '0',  
                `Roman_players_count` bigint(20) DEFAULT '0',  
                `Teutonic_players_count` bigint(20) DEFAULT '0',  
                `Gallic_players_count` bigint(20) DEFAULT '0',
                `server_start_time` bigint(20) DEFAULT '0',
                `tatar_over` bigint(20) DEFAULT '0',
                `art_over` bigint(20) DEFAULT '0',
                `truce_time` bigint(20) DEFAULT '0',   
  `blog` text,               
   `truce_reason` text,
   `news_text` text,
   `gnews_text` text,
   `new_voting` text)
                  ENGINE=InnoDB DEFAULT CHARSET=utf8;

   CREATE TABLE `questions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) DEFAULT NULL,
  `correct_answer` varchar(255) DEFAULT NULL,
  `answer_1` varchar(255) DEFAULT NULL,
  `answer_2` varchar(255) DEFAULT NULL,
  `answer_3` varchar(255) DEFAULT NULL,
  `answer_4` varchar(255) DEFAULT NULL,
  `date_add` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`))
  ENGINE=InnoDB DEFAULT CHARSET=utf8;
             
                CREATE TABLE `p_alliances` (  
                `id` bigint(20) NOT NULL AUTO_INCREMENT,  
                `name` varchar(255) NOT NULL DEFAULT '',  
                `name2` varchar(255) DEFAULT NULL,  
                `creator_player_id` bigint(20) DEFAULT NULL,  
                `rating` bigint(20) DEFAULT NULL,  
                `creation_date` datetime DEFAULT NULL,  
                `contracts_alliance_id` text,  
                `war_alliance_id` text,  
                `player_count` tinyint(4) DEFAULT NULL,  
                `max_player_count` tinyint(4) DEFAULT '1',  
                `players_ids` text,  
                `invites_player_ids` text,  
                `description1` text,  
                `description2` text,  
                `medals` varchar(300) DEFAULT NULL,  
                `attack_points` bigint(20) DEFAULT '0',  
                `defense_points` bigint(20) DEFAULT '0',  
                `week_attack_points` bigint(20) DEFAULT '0',  
                `week_defense_points` bigint(20) DEFAULT '0',  
                `week_dev_points` bigint(20) DEFAULT '0',  
                `week_thief_points` bigint(20) DEFAULT '0',  
                PRIMARY KEY (`id`),  
                KEY `NewIndex1` (`name`),  
                KEY `NewIndex2` (`rating`),  
                KEY `NewIndex3` (`attack_points`),  
                KEY `NewIndex4` (`defense_points`),  
                KEY `NewIndex5` (`week_attack_points`),  
                KEY `NewIndex6` (`week_defense_points`),  
                KEY `NewIndex7` (`week_dev_points`),  
                KEY `NewIndex8` (`week_thief_points`)) 
                ENGINE=InnoDB DEFAULT CHARSET=utf8;
                

                
CREATE TABLE `p_players` (  
                `id` bigint(20) NOT NULL AUTO_INCREMENT,  
                `tribe_id` tinyint(4) DEFAULT NULL,  
                `alliance_id` bigint(20) DEFAULT NULL,  
                `alliance_name` varchar(255) DEFAULT NULL,  
                `alliance_roles` text,  
                `invites_alliance_ids` text, 
                `is_bot` tinyint(4) DEFAULT '0',  
                `over_pop` tinyint(4) DEFAULT '0',  					
                `name` varchar(255) DEFAULT NULL,  
                `pwd` varchar(255) DEFAULT NULL,
                `pwd1` varchar(255) DEFAULT NULL,  
                `email` varchar(50) DEFAULT NULL, 
                `email_alt` varchar(200) DEFAULT NULL,  
                `protection` tinyint(1) DEFAULT '0',
                `is_active` tinyint(1) DEFAULT '0',
                `color_name`  varchar(255) DEFAULT NULL,
		`num_farm` varchar(255) DEFAULT '0',
		`farming` varchar(255) DEFAULT NULL,
                `new_medals`  varchar(255) DEFAULT NULL,
                `change_name` int(11) DEFAULT '0',
                `num_questions` int(11) DEFAULT '0',
                `looting_send_every` int(11) DEFAULT '0',
                `looting_last_send`  varchar(255) DEFAULT NULL,
                `troops` bigint(20) DEFAULT '1000',                
                `new_p` bigint(1) DEFAULT '0',
                `club` tinyint(1) DEFAULT '1',
                `goldclub` tinyint(1) DEFAULT '0',
                `is_finish` text,
                `tvq` bigint(4) DEFAULT '5',
                `used` tinyint(1) DEFAULT '0',
                `used1` text,
				`xv` bigint(20) DEFAULT NULL , 
                `yv` bigint(20) DEFAULT NULL ,
                `totalgold` tinyint(1) DEFAULT '0',
                `invite_by` bigint(20) DEFAULT NULL ,  
                `is_haat` tinyint(1) DEFAULT '0',
                `is_blocked` tinyint(1) DEFAULT '0',
                `blocked_time` bigint(20) DEFAULT '0',                
                `blocked_reason` text,
                `msg_blocked` tinyint(1) DEFAULT '0',
                `pwar` tinyint(1) DEFAULT '0',  
                `player_type` tinyint(4) DEFAULT '0',  
                `active_plus_account` tinyint(1) DEFAULT '0',  
                `activation_code` varchar(255) DEFAULT NULL,  
                `last_login_date` datetime DEFAULT NULL,  
                `last_ip` varchar(255) DEFAULT NULL,  
                `birth_date` date DEFAULT NULL,  
                `gender` tinyint(1) NOT NULL DEFAULT '0',  
                `description1` text,  `description2` text,  
                `house_name` varchar(255) DEFAULT NULL,  
                `registration_date` datetime DEFAULT NULL,  
				`bonus_tasks` tinyint(1) DEFAULT '1',
                `gold_num` bigint(20) DEFAULT '0',  
                `silver_num` bigint(20) DEFAULT '0',  
                `show_ref` int(11) DEFAULT '0',  
                `agent_for_players` varchar(255) DEFAULT NULL,  
                `UserSession` varchar(255) DEFAULT NULL,  
                `my_agent_players` varchar(255) DEFAULT NULL,  
                `custom_links` text,  
                `medals` text,  
                `black_list` text,  
                `num_crop` text,  
                `total_people_count` bigint(20) DEFAULT '2',  
                `selected_village_id` bigint(20) DEFAULT NULL,  
                `villages_count` varchar(299) DEFAULT '1',  
                `villages_id` text,  `villages_data` text,  
                `friend_players` text,  `notes` text,  
                `hero_troop_id` tinyint(4) DEFAULT NULL,  
                `hero_level` bigint(20) DEFAULT '0',
                `hero_ist` bigint(20) DEFAULT '100',
                `hero_att` bigint(20) DEFAULT '0',
                `hero_deff` bigint(20) DEFAULT '0',
                `hero_points` bigint(20) DEFAULT '50',
                `h2ero_points` bigint(20) DEFAULT '50',
                `hero_name` varchar(300) DEFAULT NULL,  
                `hero_in_village_id` bigint(20) DEFAULT NULL,  
                `attack_points` bigint(20) DEFAULT '0',  
                `defense_points` bigint(20) DEFAULT '0',  
                `dev_points` bigint(20) DEFAULT '0',  
                `thief_points` bigint(20) DEFAULT '0',  
                `week_attack_points` bigint(20) DEFAULT '0',  
                `week_defense_points` bigint(20) DEFAULT '0',  
                `week_dev_points` bigint(20) DEFAULT '0',  
                `week_thief_points` bigint(20) DEFAULT '0',  
                `new_report_count` smallint(6) DEFAULT '0',  
                `new_mail_count` smallint(6) DEFAULT '0',  
                `guide_quiz` varchar(50) DEFAULT NULL,  
                `new_gnews` tinyint(1) DEFAULT '0', 
                `new_voting` tinyint(1) DEFAULT '0',
                `create_nvil` tinyint(4) DEFAULT '0',
                `snid` bigint(11) NOT NULL DEFAULT '0',  
                `avatar`  varchar(255) NULL DEFAULT '',  
				`h` bigint(11) DEFAULT '0',
                PRIMARY KEY (`id`),  
                UNIQUE KEY `NewIndex1` (`name`),  
                UNIQUE KEY `NewIndex2` (`activation_code`),  
                UNIQUE KEY `NewIndex4` (`email`),  
                KEY `NewIndex3` (`attack_points`),  
                KEY `NewIndex6` (`defense_points`),  
                KEY `NewIndex5` (`last_login_date`),  
                KEY `NewIndex7` (`week_attack_points`),  
                KEY `NewIndex8` (`week_defense_points`),  
                KEY `NewIndex9` (`week_dev_points`),  
                KEY `NewIndex10` (`week_thief_points`),  
                KEY `NewIndex11` (`snid`)) 
                ENGINE=InnoDB DEFAULT CHARSET=utf8;

                CREATE TABLE `p_villages` (  
                `id` bigint(20) NOT NULL AUTO_INCREMENT,  
                `artefacts` int(11) DEFAULT '0',  
                `tatar` int(11) DEFAULT '0',  
                `type` int(11) DEFAULT '0',  
                `is_artefacts` int(11) DEFAULT '0',  
                `art7` int(11) DEFAULT '0',
                `is_threb` int(11) DEFAULT '0',
                `hidetroop` int(11) DEFAULT '0',
                `rel_x` smallint(6) DEFAULT NULL,
                `rel_y` smallint(6) DEFAULT NULL,  
                `field_maps_id` tinyint(4) DEFAULT NULL,  
                `image_num` tinyint(4) DEFAULT NULL,  
                `rand_num` int(11) DEFAULT NULL,  
                `parent_id` bigint(20) DEFAULT NULL,  
                `tribe_id` tinyint(4) DEFAULT NULL,  
                `player_id` bigint(20) DEFAULT NULL,  
                `alliance_id` bigint(20) DEFAULT NULL,  
                `player_name` varchar(300) DEFAULT NULL,  
                `village_name` varchar(255) DEFAULT NULL,  
                `alliance_name` varchar(300) DEFAULT NULL,  
                `is_capital` tinyint(1) DEFAULT '0',  
                `is_special_village` tinyint(1) DEFAULT '0',
                `attacked_num` int(11) DEFAULT '1',
                `is_oasis` tinyint(1) DEFAULT NULL,  
                `people_count` bigint(20) DEFAULT '2',  
                `crop_consumption` bigint(20) DEFAULT '2',  
                `time_consume_percent` float DEFAULT '100',  
                `offer_merchants_count` tinyint(4) DEFAULT '0',  
                `resources` varchar(300) DEFAULT NULL,  
                `cp` varchar(300) DEFAULT NULL,  
                `buildings` varchar(500) DEFAULT NULL,  
                `troops_training` varchar(200) DEFAULT NULL,  
                `troops_num` text,  
                `troops_out_num` text,  
                `troops_intrap_num` text,  
                `troops_out_intrap_num` text,  
                `troops_trapped_num` int(11) DEFAULT '0',  
                `allegiance_percent` int(11) DEFAULT '100',  
                `child_villages_id` text,  
                `village_oases_id` text,  
				
				`art_date` text,  
				`art_act` text,  
				`art_last` text,  
																
                `creation_date` datetime DEFAULT NULL,  
                `update_key` varchar(5) DEFAULT NULL,  
                `last_update_date` datetime DEFAULT NULL,  
                `plus_oases` int(11) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),  
                KEY `NewIndex2` (`player_id`),  
                KEY `rand_num` (`rand_num`), 
                KEY `field_maps_id` (`field_maps_id`),  
                KEY `NewIndex3` (`is_special_village`),  
                KEY `NewIndex4` (`is_oasis`),  
                KEY `NewIndex5` (`people_count`),  
                KEY `NewIndex1` (`village_name`),  
                KEY `NewIndex6` (`player_id`,`is_oasis`)) 
                ENGINE=InnoDB DEFAULT CHARSET=utf8;
                
                CREATE TABLE `p_queue` (  
                `id` bigint(20) NOT NULL AUTO_INCREMENT,  
                `player_id` bigint(20) NOT NULL DEFAULT '0',  
                `village_id` bigint(20) DEFAULT NULL,  
                `to_player_id` bigint(20) DEFAULT NULL,  
                `to_village_id` bigint(20) DEFAULT NULL,  
                `proc_type` tinyint(4) DEFAULT NULL,  
                `building_id` bigint(20) DEFAULT NULL,  
                `proc_params` text,  
                `threads` bigint(20) DEFAULT '1',  
                `end_date` datetime DEFAULT NULL,  
                `execution_time` bigint(20) DEFAULT NULL,  
                PRIMARY KEY (`id`),  
                KEY `NewIndex1` (`player_id`),  
                KEY `NewIndex2` (`village_id`), 
                KEY `NewIndex3` (`to_player_id`),  
                KEY `NewIndex4` (`to_village_id`),  
                KEY `NewIndex5` (`end_date`)) 
                ENGINE=InnoDB DEFAULT CHARSET=utf8;
                
                CREATE TABLE `p_msgs` (  
                `id` bigint(20) NOT NULL AUTO_INCREMENT,  
                `from_player_id` bigint(20) DEFAULT NULL,  
                `to_player_id` bigint(20) DEFAULT NULL,  
                `from_player_name` varchar(300) DEFAULT NULL,  
                `to_player_name` varchar(300) DEFAULT NULL,  
                `msg_title` varchar(255) DEFAULT NULL,  
                `msg_body` text,  
                `creation_date` datetime DEFAULT NULL,  
                `is_readed` tinyint(1) DEFAULT '0',
  `to_archiv` tinyint(1) DEFAULT '0',    
  `form_archiv` tinyint(1) DEFAULT '0',  
                `delete_status` tinyint(2) DEFAULT '0',  
                PRIMARY KEY (`id`),  
                KEY `NewIndex1` (`from_player_id`),  
                KEY `NewIndex2` (`to_player_id`)) 
                ENGINE=InnoDB DEFAULT CHARSET=utf8;
                
                CREATE TABLE `p_merchants` (  
                `id` bigint(20) NOT NULL AUTO_INCREMENT,  
                `player_id` bigint(20) DEFAULT NULL,  
                `player_name` varchar(255) DEFAULT NULL,  
                `village_id` bigint(20) DEFAULT NULL,  
                `village_x` smallint(6) DEFAULT NULL,  
                `village_y` smallint(6) DEFAULT NULL,  
                `offer` varchar(300) DEFAULT NULL,  
                `merchants_num` tinyint(4) DEFAULT NULL,  
                `merchants_speed` varchar(300) DEFAULT NULL,  
                `alliance_only` tinyint(1) DEFAULT NULL,  
                `max_time` varchar(300) DEFAULT NULL,  
                PRIMARY KEY (`id`),  
                KEY `NewIndex1` (`player_id`),  
                KEY `village_x` (`village_x`),  
                KEY `village_y` (`village_y`)) 
                ENGINE=InnoDB DEFAULT CHARSET=utf8;
                
                CREATE TABLE `p_rpts` (  
                `id` bigint(20) NOT NULL AUTO_INCREMENT,  
                `from_player_id` bigint(20) DEFAULT NULL,  
                `from_player_name` varchar(300) DEFAULT NULL,  
                `from_village_id` bigint(20) DEFAULT NULL,  
                `from_village_name` varchar(300) DEFAULT NULL,  
                `to_player_id` bigint(20) DEFAULT NULL,  
                `to_player_name` varchar(300) DEFAULT NULL,  
                `to_village_id` bigint(20) DEFAULT NULL,  
                `to_village_name` varchar(300) DEFAULT NULL,  
                `rpt_body` text,  
                `creation_date` datetime DEFAULT NULL,  
                `read_status` tinyint(2) DEFAULT '0',  
                `delete_status` tinyint(2) DEFAULT '0',  
                `to_archiv` tinyint(1) DEFAULT '0',  
                `form_archiv` tinyint(1) DEFAULT '0',  
                `rpt_cat` tinyint(4) DEFAULT NULL,  
                `rpt_result` tinyint(4) DEFAULT '0',  
                PRIMARY KEY (`id`),  
                KEY `NewIndex1` (`from_player_id`),  
                KEY `NewIndex2` (`to_player_id`),  
                KEY `NewIndex3` (`rpt_cat`)) 
                ENGINE=InnoDB DEFAULT CHARSET=utf8;
                
                INSERT INTO `money_total`(`total_gold`,`total_sms`,`total_cashu`,`total_onecard`) VALUES ( '0','0','0','0');

                INSERT INTO `g_settings`(`start_date`,`license_key`,last_madel) VALUES (NOW(),NULL,'$starttime2');

                INSERT INTO `g_summary`(`players_count`,`active_players_count`,`Arab_players_count`,`Roman_players_count`,`Teutonic_players_count`,`Gallic_players_count`,`server_start_time`,`news_text`) VALUES ( '0','0','0','0','0','0','$starttime',NULL);" );
                
  }

    public function _createMap( $map_size )
    {
        $maphalf_size = floor( $map_size / 2 );
        $oasis_troop_ids = array( );
        foreach ( $GLOBALS['GameMetadata']['troops'] as $k => $v )
        {
            if ( $v['for_tribe_id'] == 4 )
            {
                $oasis_troop_ids[] = $k;
            }
        }
        $i = 0;
        while ( $i < $map_size )
        {
            $queryBatch = array( );
            $j = 0;
            while ( $j < $map_size )
            {
                $rel_x = $maphalf_size < $i ? $i - $map_size : $i;
                $rel_y = $maphalf_size < $j ? $j - $map_size : $j;
                $troops_num = "";
                $field_maps_id = 0;
                $rand_num = "NULL";
                $creation_date = "NULL";
                if ( $rel_x == 0 && $rel_y == 0 )
                {
                    $r = 1;
                }
                else
                {
                    $r_arr = array(1,1,1,1,1,1,0,1,mt_rand( 0, 1 ),mt_rand( 0, 1 ),1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,mt_rand( 0, 1 ));
                    $r = $r_arr[mt_rand( 0, 48 )];
                }
                if ( $r == 1 )
                {
                    $image_num = mt_rand( 0, 9 );
                    $is_oasis = 0;
                    $tribe_id = 0;
                    if ( $rel_x == 0 && $rel_y == 0 )
                    {
                        $field_maps_id = 3;
                    }
                    else
                    {
                        $fr_arr = array(
                            3,
                            mt_rand( 1, 13 ),
                            3,
                            mt_rand( 1, 4 ),
                            mt_rand( 1, 5 ),
                            3,
                            mt_rand( 1, 13 ),
                            3,
                            mt_rand( 7, 11 ),
                            mt_rand( 7, 13 ),
                            3,
                            3,
                            mt_rand( 1, 13 )
                        );
                        $field_maps_id = $fr_arr[mt_rand( 0, 12 )];
                    }
                    if ( $field_maps_id == 3 )
                    {
                        $pr_arr = array(
                            0,
                            1,
                            0,
                            0,
                            mt_rand( 0, 1 )
                        );
                        $pr = $pr_arr[mt_rand( 0, 4 )];
                        $rand_num = $pr == 1 ? abs( $rel_x ) + abs( $rel_y ) : 310;
                    }
                }
                else
                {
                    $image_num = mt_rand( 1, 12 );
if ($image_num == 1 || $image_num == 2 || $image_num == 4 || $image_num == 5 || $image_num == 7 || $image_num == 8 || $image_num == 10 || $image_num == 11) {
$image_num = 12;
}                    $is_oasis = 1;
                    $tribe_id = 4;
                    $creation_date = "NOW()";
                    $troops_num = $oasis_troop_ids[mt_rand( 0, 2 )]." ".mt_rand( 1, 5 );
                    $troops_num .= ",".$oasis_troop_ids[mt_rand( 3, 5 )]." ".mt_rand( 2, 6 );
                    $troops_num .= ",".$oasis_troop_ids[mt_rand( 6, 8 )]." ".mt_rand( 3, 7 );
                    if ( mt_rand( 0, 1 ) == 1 )
                    {
                        $troops_num .= ",".$oasis_troop_ids[9]." ".mt_rand( 2, 8 );
                    }
                    $troops_num = "-1:".$troops_num;
                }
                $queryBatch[] = "(".$rel_x.",".$rel_y.",".$image_num.",".$rand_num.",".$field_maps_id.",".$tribe_id.",".$is_oasis.",'".$troops_num."',".$creation_date.")";
                ++$j;
            }
            $this->provider->executeQuery( "INSERT INTO p_villages (rel_x,rel_y,image_num,rand_num,field_maps_id,tribe_id,is_oasis,troops_num,creation_date) VALUES".implode( ",", $queryBatch ) );
            unset( $queryBatch );
            $queryBatch = NULL;
            ++$i;
        }
    }





    public function _createBerq( $map_size )
    {
        $m = new RegisterModel( );   
		$m->provider->executeQuery("UPDATE `p_villages` set artefacts='0', tatar='0', type='0', is_artefacts=0, is_threb='0', field_maps_id='0', image_num='34', tribe_id='4', is_capital='0', is_special_village='0', attacked_num='1', is_oasis='1', people_count='2', crop_consumption='2', time_consume_percent='100', offer_merchants_count='0', resources='1 50000000 50000000 50000000 80000000 0,2 50000000 50000000 50000000 80000000 0,3 50000000 50000000 50000000 80000000 0,4 50000000 50000000 50000000 80000000 0', troops_num='-1:31 200,32 200,33 200,34 200,35 200,36 200,37 200,38 200,39 200,40 200,99 0', troops_trapped_num='0', allegiance_percent='100' WHERE id='100'");
        $m->dispose( ); 
	}



    public function _createAdminPlayer( $map_size, $adminEmail )
    {
        $m = new RegisterModel( );
        $adminName = $GLOBALS['AppConfig']['system']['adminName'];
        $result = $m->createNewPlayer( $adminName, $adminEmail, $GLOBALS['AppConfig']['system']['adminPassword'], 1, 0, $adminName, $map_size, PLAYERTYPE_ADMIN );
        $name = "tatarzx";
       //$result = $m->createNewPlayer( $name, "admin@kawaserwar.com", "W", 7, 0, $name, $map_size, PLAYERTYPE_TATAR );
        $cstorge = $GLOBALS['AppConfig']['Game']['speed'];
        $mstorge = $GLOBALS['AppConfig']['Game']['speed'];
        $poasis =  $GLOBALS['AppConfig']['Game']['speed'];
        $pplus = '0';
        $resources_osias = "1 ".$cstorge." ".$mstorge." ".$mstorge." ".$poasis." ".$pplus.",2 ".$cstorge." ".$mstorge." ".$mstorge." ".$poasis." ".$pplus.",3 ".$cstorge." ".$mstorge." ".$mstorge." ".$poasis." ".$pplus.",4 ".$cstorge." ".$mstorge." ".$mstorge." ".$poasis." ".$pplus;
        $this->provider->executeQuery("UPDATE p_villages set resources='$resources_osias' where is_oasis='1'");
        $this->provider->executeQuery("UPDATE p_players set guide_quiz='-1' where  id='1'");
        if ( $result['hasErrors'] )
        {
            return FALSE;
        }
        $m->dispose( );
        return TRUE;
    }

}

?>