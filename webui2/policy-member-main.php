<?php
# Policy ACL main screen
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
		"Tabs" => array(
			"Back to policies" => "policy-main.php"
		),
));


#IF POST CONTIENE ADD
if ($_POST['frmaction'] == "add")  {
	#Check params
	if (empty($_POST['member_source']) || empty($_POST['member_destination'])) {
		echo "<div class=\"textcenter\"> No void source or destination allowed! </div>";

	}	else {
		$stmt = $db->prepare("INSERT INTO ${DB_TABLE_PREFIX}policy_members (PolicyID,Source,Destination,Comment,Disabled) VALUES (?,?,?,?,1)");
		
		$res = $stmt->execute(array(
			$_POST['policy_id'],
			$_POST['member_source'],
			$_POST['member_destination'],
			$_POST['member_comment']
		));
	}
}


#IF POST CONTIENE CHSTATUS	
 elseif ($_POST['frmaction'] == "change")  {


	if (isset($_POST['policy_member_id'])) {
		$db->beginTransaction();
		
		if ($_POST['policy_member_status'] == 1){
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}policy_members SET Disabled = 0 WHERE ID = ".$db->quote($_POST['policy_member_id']));
		}
		ELSE {
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}policy_members SET Disabled = 1 WHERE ID = ".$db->quote($_POST['policy_member_id']));
		}
		
		if ($res !== FALSE) {
			$db->commit();
		}
	} 
}

#IF POST CONTIENE DELETE	
 elseif ($_POST['frmaction'] == "delete")  {
				
	if (isset($_POST['policy_member_id'])) {
		$db->beginTransaction();
		//$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}policy_groups WHERE ID = ".$db->quote($_POST['policy_group_id']));	
		$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}policy_members WHERE ID = ".$db->quote($_POST['policy_member_id']));
		if ($res !== FALSE) {
			
			$db->commit();
			//header('Location: policy-member-main.php?policy_id='.$_POST['policy_id']); 
		  }
	  } 
			
}






# Check a policy was selected
if (isset($_REQUEST['policy_id'])) {

?>
	<p class="pageheader">Policy Members</p>
	
<?php		

	$policy_stmt = $db->prepare("SELECT Name FROM ${DB_TABLE_PREFIX}policies WHERE ID = ?");
	$policy_stmt->execute(array($_REQUEST['policy_id']));
	$row = $policy_stmt->fetchObject();
	$policy_stmt->closeCursor();
?>

	<div>
		<input type="hidden" name="policy_id" value="<?php echo $_REQUEST['policy_id'] ?>" />
	</div>
	
	<div class="notice" align="center">Policy: <?php echo $row->name ?></div></br>
	



	<?php include_once("includes/sorttable.php");?>
	
	<table id="mainresults" class="results" style="width: 75%;">
		<tr class="resultstitle">
			<td class="textcenter" onclick="sortTable(0)">ID</td>
			<td class="textcenter" onclick="sortTable(1)">Source</td>
			<td class="textcenter" onclick="sortTable(2)">Destination</td>
			<td class="textcenter" onclick="sortTable(3)">Status</td>
			<td class="textcenter">Details</td>
			<td class="textcenter">Delete</td>
		</tr>
<?php

		$stmt = $db->prepare("SELECT ID, Source, Destination, Disabled FROM ${DB_TABLE_PREFIX}policy_members WHERE PolicyID = ?");
		$res = $stmt->execute(array($_REQUEST['policy_id']));

		$i = 0;

		# Loop with rows
		while ($row = $stmt->fetchObject()) {
?>
			<tr class="resultsitem">
				<td class="textcenter"><?php echo ($row->id)?></td>
				<td class="textcenter"><?php echo is_null($row->source) ? 'any' : $row->source ?></td>
				<td class="textcenter"><?php echo is_null($row->destination) ? 'any' : $row->destination ?></td>
				<td align="center">		
					<form action="policy-member-main.php" method="post">
						<div>
							<input type="hidden" name="frmaction" value="change" />
							<input type="hidden" name="policy_id" value="<?php echo $_POST['policy_id']; ?>" />
							<input type="hidden" name="policy_member_id" value="<?php echo $row->id ?>" />
							<input type="hidden" name="policy_member_status" value="<?php echo $row->disabled ?>" />
							<input type="submit" class="<?php echo $row->disabled ? 'buttondisabled' : 'buttonenabled' ?>" value="<?php echo $row->disabled ? 'Disabled' : 'Enabled' ?>" />
						</div>
					</form>
				</td>
				<td align="center">		
					<form action="policy-member-change.php" method="post">
						<div>
							<input type="hidden" name="frmaction" value="change" />
							<input type="hidden" name="policy_id" value="<?php echo $_POST['policy_id']; ?>" />
							<input type="hidden" name="policy_member_id" value="<?php echo $row->id ?>" />
							<input type="hidden" name="policy_member_status" value="<?php echo $row->disabled ?>" />
							<input type="submit" class="button" value="Edit" />
						</div>
					</form>
				</td>
				<td align="center">		
					<form action="policy-member-main.php" method="post">
						<div>
							<input type="hidden" name="frmaction" value="delete" />
							<input type="hidden" name="policy_id" value="<?php echo $_POST['policy_id']; ?>" />
							<input type="hidden" name="policy_member_id" value="<?php echo $row->id ?>" />
							<input type="submit" class="button" value="Delete" />
						</div>
					</form>
				</td>
			</tr>
<?php
			}
			$stmt->closeCursor();
?>
	</table>

		
	</br></br><hr width="75%"></br>
	
	<!---INIZIO QUICK ADD-->

	<form method="post" action="policy-member-main.php">
		<div>
			<input type="hidden" name="frmaction" value="add" />
			<input type="hidden" name="policy_id" value="<?php echo $_POST['policy_id'] ?>" />
		</div>
		<table class="entry">
		<caption>Add new Policy member</caption>
			<tr>
				<td class="entrytitle texttop">
					Source
					<?php tooltip('policy_member_source'); ?>
				</td>
				<td><textarea name="member_source" cols="30" rows="2"/></textarea></td>
			</tr>
			<tr>
				<td class="entrytitle texttop">
					Destination
					<?php tooltip('policy_member_destination'); ?>
				</td>
				<td><textarea name="member_destination" cols="30" rows="2"/></textarea></td>
			</tr>
			<tr>
				<td class="entrytitle">Comment</td>
				<td><textarea name="member_comment" cols="30" rows="2"></textarea></td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="submit" class="button" value="Submit"/>
				</td>
			</tr>
		</table>
	</form>
	<!---FINE QUICK ADD-->

	
<?php



} else {
?>
	<div class="warning">Invalid invocation</div>
<?php
}


printFooter();


# vim: ts=4
?>
