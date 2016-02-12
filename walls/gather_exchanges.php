<?php
include('config.php');
include('logger.php');
include('exchanges.php');
include("functions.php");
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

#try to open lock file
$fp = fopen(".lock_walls", "r+");

if (flock($fp, LOCK_EX)) {
	#we have a lock - let's go. 
	echo "I have a lock";
	#get fiat prices
	$btc_usd=get_btc_usd();
	$nbt_cny=get_nbt_cny($btc_usd);
	$nbt_eur=get_nbt_eur();

	if (!$btc_usd)
	{
	writelog("all", "no_bitcoin_price", "unresolved"); 
	exit; #no bitcoin price available, sorry. will not gather data (offline?)
	}

	#do the query
	$timestamp=time();
	$query=query_exchanges($timestamp,$global_tolerance,$btc_usd,$nbt_cny,$nbt_eur);

	#write it
	file_put_contents($data_munched, $query, LOCK_EX);

	#create chart points
	include("../charts/createcsv_walls.php");
	sleep(60);
	#unlock if we locked
	flock($fp, LOCK_UN); // Gib Sperre frei
}
else {
	writelog("all", "file_locked", "unresolved"); 
	echo "I don't have a lock";
}
?>