<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Updater {
	
	public static function check_error($mysqli) {
		if ($mysqli->errno) {
			echo "ERROR " . $mysqli->errno . ": " . $mysqli->error;
			die();
		}
	}
	
	public static function log($s, $updated) {
		if (!$updated) echo "<xmp>\n*** ACTUALIZACION DE BASE DATOS ***\n\n";
		echo '- ' . $s . "\n";
	}
	
	public static function runUpdate() {
		
		//return;
		
		set_time_limit(600);
		
		//error_reporting(E_ERROR);
		
		$updated = false;	//flag to determine when to die because 
		
		$mysqli = new mysqli("127.0.0.1", DB_USER, DB_PASS, DB_NAME);
		if ($mysqli->connect_errno == 1049) {
			//DB do not exists
			$mysqli = new mysqli("127.0.0.1", DB_USER, DB_PASS);
			$mysqli->query("CREATE DATABASE `".DB_NAME."` CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'");
			$mysqli = null;
			
			//re connect
			$mysqli = new mysqli("127.0.0.1", DB_USER, DB_PASS, DB_NAME);
			
			Updater::check_error($mysqli);
		}


		$rs = $mysqli->query("select * from pedidos");
		if ($mysqli->errno == 1146) {
			//table do not exists, run init script
			$sql = file_get_contents("_db_updates/init.sql");

			//run SQL
			$mysqli->multi_query($sql);
			Updater::check_error($mysqli);
			while ($mysqli->next_result()) $mysqli->use_result();

			Updater::log('SCRIPT DE INSTALACION DE BASE DE DATOS EJECUTADO');
		}
		
		$rs = $mysqli->query("select * from db_config");
		if ($mysqli->errno == 1146) {
			//table do not exists
			$mysqli->multi_query("create table db_config(id varchar(100) not null, valor varchar(255) not null, primary key (id))");
			
			Updater::check_error($mysqli);
			while ($mysqli->next_result()) $mysqli->use_result();
			
			$mysqli->query("insert into db_config(id, valor) values('VER', '1')");
			Updater::log('Tabla DB_CONFIG creada', $updated);
			$updated = true;
		}
		
		$res = $mysqli->query("select valor from db_config where id = 'VER'");

		$row = $res->fetch_array();
		$version = ((int)$row['valor']) + 1;

		$keep_update = true;
		while ($keep_update) {
			if (file_exists("_db_updates/{$version}.sql")) {
				$sql = file_get_contents("_db_updates/{$version}.sql");

				//run SQL
				$mysqli->multi_query($sql);
				Updater::check_error($mysqli);
				while ($mysqli->next_result()) $mysqli->use_result();
				
				Updater::log('EXEC: ' . $sql, $updated);
				$updated = true;

				//update version
				$mysqli->query("update db_config set valor = '{$version}' where id = 'VER'");
				Updater::check_error($mysqli);
				while ($mysqli->next_result()) $mysqli->use_result();
				$version++;
			}
			else 
				$keep_update = false;
		}
		
		$mysqli = null;
		
		if ($updated) {
			echo "\n\n*** Base de datos actualizada ***</xmp>";
			die();
		}
	}
	
}