<?php
# Module: CheckHelo (blacklisting)
# Copyright (C) 2009-2011, AllWorldIT
# Copyright (C) 2008, LinuxRulz
# 
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.


include_once("includes/header.php");
include_once("includes/footer.php");
include_once("includes/db.php");
include_once("includes/tooltips.php");



$db = connect_db();



printHeader(array(
));

#IF POST CONTIENE ADD3
if ($_POST['frmaction'] == "add") {
	# Check name
	if (empty($_POST['blacklist_helo'])) {
		echo "Helo cannot be empty";

	} else {
		$stmt = $db->prepare("INSERT INTO ${DB_TABLE_PREFIX}checkhelo_blacklist (Helo,Comment,Disabled) VALUES (?,1,1)");
		
		$res = $stmt->execute(array(
			$_POST['blacklist_helo'],
			//$_POST['blacklist_comment']
		));
		
		if ($res) {
header('Location: '.$_SERVER['REQUEST_URI']); 
		} 
	}
}

#IF POST CONTIENE DELETE	
elseif ($_POST['frmaction'] == "delete")  {
				
	 if (isset($_POST['id'])) {

		$db->beginTransaction();
		$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}checkhelo_blacklist WHERE ID = ".$db->quote($_POST['id']));	//elimina la policy	
				
		if ($res !== FALSE) {
			// && $res2 != FALSE
			$db->commit();
		}
header('Location: '.$_SERVER['REQUEST_URI']); 
	 } 
		
}

#IF POST CONTIENE CHANGE	
elseif ($_POST['frmaction'] == "change")  {
				
	if (isset($_POST['id'])) {
		$db->beginTransaction();
		
		if ($_POST['status'] == 1){
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}checkhelo_blacklist SET Disabled = 0 WHERE ID = ".$db->quote($_POST['id']));
		}
		ELSE {
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}checkhelo_blacklist SET Disabled = 1 WHERE ID = ".$db->quote($_POST['id']));
		}
		
		if ($res !== FALSE) {
			$db->commit();
header('Location: '.$_SERVER['REQUEST_URI']); 
		 }
	} 	
}

# If we have no action, display list
if (!isset($_POST['frmaction']))
{
?>
	<p class="pageheader">HELO/EHLO Blacklistings</p>

	<?php include_once("includes/sorttable.php");?>
	
	<table id="mainresults" class="results" style="width: 75%;">
		<tr class="resultstitle">
			<td class="textcenter" onclick="sortTable(0)">ID</td>
			<td class="textcenter" onclick="sortTable(1)">HELO/EHLO</td>
			<td class="textcenter" onclick="sortTable(2)">Status</td>
			<td class="textcenter">Delete</td>
		</tr>                      
<?php
		$sql = "
				SELECT 
					ID, Helo, Disabled

				FROM 
					${DB_TABLE_PREFIX}checkhelo_blacklist

				ORDER BY 
					Helo
		";
		$res = $db->query($sql);
		while ($row = $res->fetchObject()) {
?>
			<tr class="resultsitem">
				<td class="textcenter"><?php echo $row->id ?></td>
				<td><?php echo $row->helo ?></td>
				<td class="textcenter">
					<form method="post" action="checkhelo-blacklist-main.php">
						<div>
							<input type="hidden" name="frmaction" value="change" />
							<input type="hidden" name="id" value="<?php echo $row->id ?>" />
							<input type="hidden" name="status" value="<?php echo $row->disabled ?>" />
						</div>
						<input type="submit" class="<?php echo $row->disabled ? 'buttondisabled' : 'buttonenabled' ?>" value="<?php echo $row->disabled ? 'Disabled' : 'Enabled' ?>" />
					</form>					
				</td>
				<td align="center">
					<form method="post" action="checkhelo-blacklist-main.php">
						<div>
							<input type="hidden" name="frmaction" value="delete" />
							<input type="hidden" name="id" value="<?php echo $row->id ?>" />
						</div>
						<input type="submit" class="button" value="Delete" />
					</form>					
				</td>
			</tr>
<?php
		}
		$res->closeCursor();
?>
	</table>



	</br></br><hr width="75%"></br>
	
	<!---INIZIO QUICK ADD-->
	<form method="post" action="checkhelo-blacklist-main.php">
		<div>
			<input type="hidden" name="frmaction" value="add" />
		</div>
		<table class="entry">
		<caption>Add new Blacklist rule</caption>
			<tr>
				<td class="entrytitle">
					Blacklist Helo
					<?php tooltip('checkhelo_blacklist_helo'); ?>
				</td>
				<td><input type="text" name="blacklist_helo" /></td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="submit" class="button"/>
				</td>
			</tr>
		</table>
	</form>
	<!---FINE QUICK ADD-->	
<?php



}


printFooter();


# vim: ts=4
?>
