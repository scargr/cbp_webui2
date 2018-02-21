<?php
# Policy group member screen
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
			"Back to groups" => "policy-group-main.php"
		),
));




#IF POST CONTIENE ADD (rimossa, solo add multipla)
// if ($_POST['frmaction'] == "add")  {

	// if ($_POST['policy_group_member_member'] !== "") {	
		// $stmt = $db->prepare("INSERT INTO ${DB_TABLE_PREFIX}policy_group_members (PolicyGroupID,Member,Comment,Disabled) VALUES (?,?,?,1)");
		// $res = $stmt->execute(array(
			// $_POST['policy_group_id'],
			// $_POST['policy_group_member_member'],
			// $_POST['policy_group_member_comment']
		// ));
	// } 
// }	


#IF POST CONTIENE add_list
if ($_POST['frmaction'] == "add_list")  {

	if ($_POST['policy_group_member_list'] !== "") {	
		
		//echo $_POST['policy_group_member_list'];
		$array = preg_split('/\r\n|[\r\n]/', $_POST['policy_group_member_list']);
		foreach($array as $line) 
		{ 
			$stmt = $db->prepare("INSERT INTO ${DB_TABLE_PREFIX}policy_group_members (PolicyGroupID,Member,Comment,Disabled) VALUES (?,?,1,1)");
			$res = $stmt->execute(array(
				$_POST['policy_group_id'],
				$line
				));
		} 
	} 
}	



#IF POST CONTIENE CHSTATUS	
 elseif ($_POST['frmaction'] == "changemember")  {


	if (isset($_POST['policy_group_member_id'])) {
		$db->beginTransaction();
		
		if ($_POST['policy_group_member_status'] == 1){
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}policy_group_members SET Disabled = 0 WHERE ID = ".$db->quote($_POST['policy_group_member_id']));
		}
		ELSE {
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}policy_group_members SET Disabled = 1 WHERE ID = ".$db->quote($_POST['policy_group_member_id']));
		}
		
		if ($res !== FALSE) {
			$db->commit();
		}
	} 
}


	
	

#IF POST CONTIENE DELETE	
 elseif ($_POST['frmaction'] == "deletemember")  {
				
	if (isset($_POST['policy_group_id'])) {
		$db->beginTransaction();
		//$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}policy_groups WHERE ID = ".$db->quote($_POST['policy_group_id']));	
		$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}policy_group_members WHERE ID = ".$db->quote($_POST['policy_group_member_id']));
		if ($res !== FALSE) {
			
			$db->commit();
		}
	} 
}

# Check a policy group was selected
if (isset($_REQUEST['policy_group_id'])) {

?>
	<p class="pageheader">Policy Group Members</p>
	
<?php		

	$policy_group_stmt = $db->prepare("SELECT Name FROM ${DB_TABLE_PREFIX}policy_groups WHERE ID = ?");
	$policy_group_stmt->execute(array($_REQUEST['policy_group_id']));
	$row = $policy_group_stmt->fetchObject();
	$policy_group_stmt->closeCursor();
?>
	<div>
		<input type="hidden" name="policy_group_id" value="<?php echo $_REQUEST['policy_group_id'] ?>" />
	</div>
	
		<div class="notice" align="center">Group: <?php echo $row->name ?></div></br>

	<?php include_once("includes/sorttable.php");?>
	
	<table id="mainresults" class="results" style="width: 75%;">
		<tr class="resultstitle">
			<td class="textcenter" onclick="sortTable(0)">ID</td>
			<td class="textcenter" onclick="sortTable(1)">Member</td>
			<td class="textcenter" onclick="sortTable(2)">Status</td>
			<td class="textcenter">Delete</td>
		</tr>
<?php

		$stmt = $db->prepare("SELECT ID, Member, Disabled FROM ${DB_TABLE_PREFIX}policy_group_members WHERE PolicyGroupID = ?");
		$res = $stmt->execute(array($_REQUEST['policy_group_id']));

		$p_id = $_POST['policy_group_id'];
		
		$i = 0;
					
		# Loop with rows
		while ($row = $stmt->fetchObject()) {
?>
			<tr class="resultsitem">
				<!--<td><input type="radio" name="policy_group_member_id" value="<?php echo $row->id ?>" /></td>-->
				<td class="textcenter"><?php echo $row->id ?></td>
				<td class="textcenter"><?php echo $row->member ?></td>
				
				<td align="center">		
					<form action="policy-group-member-main.php" method="post">
						<div>
							<input type="hidden" name="frmaction" value="changemember" />
							<input type="hidden" name="policy_group_id" value="<?php echo $p_id; ?>" />
							<input type="hidden" name="policy_group_member_id" value="<?php echo $row->id ?>" />
							<input type="hidden" name="policy_group_member_status" value="<?php echo $row->disabled ?>" />
							<input type="submit" class="<?php echo $row->disabled ? 'buttondisabled' : 'buttonenabled' ?>" value="<?php echo $row->disabled ? 'Disabled' : 'Enabled' ?>" />
						</div>
					</form>
				</td>					
				
				<td align="center">		
					<form action="policy-group-member-main.php" method="post">
						<div>
							<input type="hidden" name="frmaction" value="deletemember" />
							<input type="hidden" name="policy_group_id" value="<?php echo $_POST['policy_group_id']; ?>" />
							<input type="hidden" name="policy_group_member_id" value="<?php echo $row->id ?>" />
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
	
	<!---INIZIO QUICK ADD

		<form method="post" action="policy-group-member-main.php">
			<div>
				<input type="hidden" name="frmaction" value="add" />
				<input type="hidden" name="policy_group_id" value="<?php echo $_POST['policy_group_id'] ?>" />
			</div>
			<table class="entry">
			<caption>Add new Group member</caption>
				<tr>
					<td class="entrytitle">
						Member
						<?php tooltip('policy_group_member'); ?>
					</td>
					<td><input type="text" name="policy_group_member_member" /></td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="submit" class="button" value="Submit" />
					</td>
				</tr>
				
			</table>
		</form>

	FINE QUICK ADD	
	</br></br><hr width="75%"></br></br>-->
	
	
	
	<!---INIZIO MULTI ADD-->

		<form method="post" action="policy-group-member-main.php">
			<div>
				<input type="hidden" name="frmaction" value="add_list" />
				<input type="hidden" name="policy_group_id" value="<?php echo $_POST['policy_group_id'] ?>" />
			</div>
			<table class="entry">
			<caption>Add members</caption>
				<tr>
					<td class="entrytitle">
						Member
						<?php tooltip('policy_group_member'); ?>
					</td>
					<td>
						<textarea name="policy_group_member_list" cols="30" rows="4" /></textarea>
					</td>
					
				</tr>
				<tr>
					<td colspan="2">
						<input type="submit" class="button" value="Submit" />
					</td>
				</tr>
				
			</table>
		</form>

	<!---FINE MULTI ADD-->	
	
	
	
<?php
} else {
?>
	<div class="warning">Invalid invocation</div>
<?php
}

// //chiudi elseif gigante
printFooter();


# vim: ts=4
?>
