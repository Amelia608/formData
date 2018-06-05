<?php
//************************强制重置sessionid,并且删除旧id***********************************
if($_SESSION){
	session_regenerate_id(true);
}
