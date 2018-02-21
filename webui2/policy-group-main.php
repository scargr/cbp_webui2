<?php
# Policy groups main screen
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



$db = connect_db();



printHeader(array(
));
#IF POST CONTIENE ADD
if ($_POST['frmaction'] == "add")  {
	if ($_POST['policy_group_name'] !== "") {	
		$stmt = $db->prepare("INSERT INTO ${DB_TABLE_PREFIX}policy_groups (Name,Comment,Disabled) VALUES (?,?,1)");
		$res = $stmt->execute(array(
			$_POST['policy_group_name'],
			$_POST['policy_group_comment'],
		));
		if ($res) {
header('Location: '.$_SERVER['REQUEST_URI']); 
		} 
	}	
}	
	
#IF POST CONTIENE CHSTATUS	
 elseif ($_POST['frmaction'] == "chstatus")  {

	if (isset($_POST['policy_group_id'])) {
		$db->beginTransaction();
		
		if ($_POST['policy_group_status'] == 1){
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}policy_groups SET Disabled = 0 WHERE ID = ".$db->quote($_POST['policy_group_id']));
		}
		ELSE {
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}policy_groups SET Disabled = 1 WHERE ID = ".$db->quote($_POST['policy_group_id']));
		}
		
		if ($res !== FALSE) {
			$db->commit();
header('Location: '.$_SERVER['REQUEST_URI']); 
		 }
	} 
}		




		

 
	
	
#IF POST CONTIENE DELETE	
 elseif ($_POST['frmaction'] == "delete")  {
				
	 if (isset($_POST['policy_group_id'])) {
		//$res2= FALSE;
		$db->beginTransaction();
		$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}policy_groups WHERE ID = ".$db->quote($_POST['policy_group_id']));	//elimina il gruppo 	
		$res2 = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}policy_group_members WHERE PolicyGroupID = ".$db->quote($_POST['policy_group_id']));	//elimina i membri del gruppo
		if ($res !== FALSE) {
			// && $res2 != FALSE
			$db->commit();
		}
header('Location: '.$_SERVER['REQUEST_URI']); 
	 } 
		






		

	
	
	
} else {
?>
	<p class="pageheader">Policy Groups</p>

	<?php include_once("includes/sorttable.php");?>
	
	<table id="mainresults" class="results" style="width: 75%;">
		<tr class="resultstitle">
			<!--<td id="noborder"></td>-->
			<td class="textcenter" onclick="sortTable(0)">ID</td>
			<td class="textcenter" onclick="sortTable(1)">Name</td>
			<td class="textcenter" onclick="sortTable(2)">Status</td>
			<td class="textcenter">Details</td>
			<td class="textcenter">Members</td>
			<td class="textcenter">Delete</td>
		</tr>
<?php
		$sql = "SELECT ID, Name, Disabled FROM ${DB_TABLE_PREFIX}policy_groups ORDER BY Name";
		$res = $db->query($sql);

		while ($row = $res->fetchObject()) {
?>
			<tr class="resultsitem">
				<td class="textcenter"><?php echo $row->id ?></td>
				<td><?php echo $row->name ?></td>
				<td align="center">		
					<form method="post" action="policy-group-main.php">
						<div>
							<input type="hidden" name="frmaction" value="chstatus" />
							<input type="hidden" name="policy_group_id" value="<?php echo $row->id ?>" />
							<input type="hidden" name="policy_group_status" value="<?php echo $row->disabled ?>" />
						</div>
						<input type="submit" class="<?php echo $row->disabled ? 'buttondisabled' : 'buttonenabled' ?>" value="<?php echo $row->disabled ? 'Disabled' : 'Enabled' ?>"/>
					</form>
				</td>
				<td align="center">		
					<form method="post" action="policy-group-change.php">
						<div>
							<input type="hidden" name="frmaction" value="change" />
							<input type="hidden" name="policy_group_id" value="<?php echo $row->id ?>" />
						</div>
						<input type="submit" class="button" value="Edit"/>
					</form>
				</td>
				<td align="center">	
					<form method="post" action="policy-group-member-main.php">
						<div>
							<input type="hidden" name="frmaction" value="chmembers" />
							<input type="hidden" name="policy_group_id" value="<?php echo $row->id ?>" />
						</div>
						<input type="submit" class="button" value="Change members"/>
					</form>
				</td>
				<td align="center">		
					<form method="post" action="policy-group-main.php">
						<div>
							<input type="hidden" name="frmaction" value="delete" />
							<input type="hidden" name="policy_group_id" value="<?php echo $row->id ?>" />
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
	
	<form method="post" action="policy-group-main.php">
			<div>
				<input type="hidden" name="frmaction" value="add" />
				<input type="hidden" name="policy_group_id" value="<?php echo $_POST['policy_group_id'] ?>" />
			</div>
			<table class="entry">
			<caption>Add new Group</caption>
				<tr>
					<td class="entrytitle">Name</td>
					<td><input type="text" name="policy_group_name" /></td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="submit" class="button" value="Submit" />
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
