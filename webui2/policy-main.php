<?php
# Policy main screen
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

#IF POST CONTIENE ADD
if ($_POST['frmaction'] == "add") {
	# Check name
	if (empty($_POST['policy_name']) || empty($_POST['policy_priority'])) {
		echo "<div class=\"textcenter\"> No policy without name or priority allowed! </div>";

	} 	else {
		$stmt = $db->prepare("INSERT INTO ${DB_TABLE_PREFIX}policies (Name,Priority,Description,Disabled) VALUES (?,?,?,1)");

		$res = $stmt->execute(array(
			$_POST['policy_name'],
			$_POST['policy_priority'],
			$_POST['policy_description'],
		));
		if ($res) {
			header('Location: '.$_SERVER['REQUEST_URI']); 
		} 
	}
}


#IF POST CONTIENE DELETE	
elseif ($_POST['frmaction'] == "delete")  {
				
	 if (isset($_POST['policy_id'])) {

		$db->beginTransaction();

		//elimina i membri della policy
		$res2 = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}policy_members WHERE PolicyID = ".$db->quote($_POST['policy_id']));	
		//elimina accesscontrol
		$res_access_control = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}access_control WHERE PolicyID = ".$db->quote($_POST['policy_id']));
		
		//elimina helocheck
		$res_checkhelo = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}checkhelo WHERE PolicyID = ".$db->quote($_POST['policy_id']));
		//elimina spfcheck
		$res_checkspf = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}checkspf WHERE PolicyID = ".$db->quote($_POST['policy_id']));
		//elimina greylisting
		$res_greylisting = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}greylisting WHERE PolicyID = ".$db->quote($_POST['policy_id']));
		//elimina amavis rule
		$res_amavis = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}amavis_rules WHERE PolicyID = ".$db->quote($_POST['policy_id']));

		
		//elimina quotas e relativi limits e tracking
		# Grab quotas we need to delete
		$quotas_to_delete = array();
		foreach ($db->query("SELECT ID FROM ${DB_TABLE_PREFIX}quotas WHERE PolicyID = ".$db->quote($_POST['policy_id'])) as $row) {
			array_push($quotas_to_delete, $row['id']);
		}

		# Proceed if we actually have quotas
		if (count($quotas_to_delete) > 0) {
			$quotas_to_delete = implode(",",$quotas_to_delete);

			# Grab limits we need to delete
			$limits_to_delete = array();
			foreach ($db->query("SELECT ID FROM ${DB_TABLE_PREFIX}quotas_limits WHERE QuotasID IN (".$quotas_to_delete.")") as $row) {
				array_push($limits_to_delete, $row['id']);
			}

			# Proceed if we actually have limits
			if (count($limits_to_delete) > 0) {
				$limits_to_delete = implode(",",$limits_to_delete);

				# Do delete of quotas
				$res_q_tracking = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}quotas_tracking WHERE QuotasLimitsID IN (".$limits_to_delete.")");
				$res_limits = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}quotas_limits WHERE ID IN (".$limits_to_delete.")");
			}
			$res_quotas = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}quotas WHERE ID IN (".$quotas_to_delete.")");
		}
		
		//elimina accounting e relativi tracking
		# Grab accounting we need to delete
		$accounting_to_delete = array();
		foreach ($db->query("SELECT ID FROM ${DB_TABLE_PREFIX}accounting WHERE PolicyID = ".$db->quote($_POST['policy_id'])) as $row) {
			array_push($accounting_to_delete, $row['id']);
		}
		# Proceed if we actually have accounting
		if (count($accounting_to_delete) > 0) {
			$accounting_to_delete = implode(",",$accounting_to_delete);

			$res_a_tracking = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}accounting_tracking WHERE AccountingID IN (".$accounting_to_delete.")");
			$res_accounting = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}accounting WHERE ID IN (".$accounting_to_delete.")");
		}
		
		//solo alla fine elimina la policy	
		$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}policies WHERE ID = ".$db->quote($_POST['policy_id']));			
						
		if ($res !== FALSE) {
			$db->commit();
		}
		header('Location: '.$_SERVER['REQUEST_URI']);
	 } 
		
}

#IF POST CONTIENE CHANGE	
elseif ($_POST['frmaction'] == "change")  {
				
	if (isset($_POST['policy_id'])) {
		$db->beginTransaction();
		
		if ($_POST['policy_status'] == 1){
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}policies SET Disabled = 0 WHERE ID = ".$db->quote($_POST['policy_id']));
		}
		ELSE {
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}policies SET Disabled = 1 WHERE ID = ".$db->quote($_POST['policy_id']));
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
	<p class="pageheader">Policy List</p>
	
	<?php include_once("includes/sorttable.php");?>
	
	<table id="mainresults" class="results" style="width: 75%;">
		<tr class="resultstitle">
			<td class="textcenter" onclick="sortTable(0)">ID</td>
			<td class="textcenter" onclick="sortTable(1)">Name</td>
			<td class="textcenter" onclick="sortTable(2)">Priority</td>
			<td class="textcenter" onclick="sortTable(3)">Description</td>
			<td class="textcenter" onclick="sortTable(4)">Status</td>
			<td class="textcenter">Details</td>
			<td class="textcenter">Members</td>
			<td class="textcenter">Delete</td>
		</tr>
<?php
		$sql = "SELECT ID, Name, Priority, Description, Disabled FROM ${DB_TABLE_PREFIX}policies ORDER BY Priority ASC";
		$res = $db->query($sql);

		while ($row = $res->fetchObject()) {
?>
			<tr class="resultsitem">
				<td class="textcenter"><?php echo $row->id ?></td>
				<td><?php echo $row->name ?></td>
				<td class="textcenter"><?php echo $row->priority ?></td>
				<td><?php echo $row->description ?></td>
				<td class="textcenter">
					<form method="post" action="policy-main.php">
						<div>
							<input type="hidden" name="frmaction" value="change" />
							<input type="hidden" name="policy_id" value="<?php echo $row->id ?>" />
							<input type="hidden" name="policy_status" value="<?php echo $row->disabled ?>" />
						</div>
						<input type="submit" class="<?php echo $row->disabled ? 'buttondisabled' : 'buttonenabled' ?>" value="<?php echo $row->disabled ? 'Disabled' : 'Enabled' ?>" />
					</form>					
				</td>
				<td align="center">
					<form method="post" action="policy-change.php">
						<div>
							<input type="hidden" name="frmaction" value="change" />
							<input type="hidden" name="policy_id" value="<?php echo $row->id ?>" />
						</div>
						<input type="submit" class="button" value="Edit" />
					</form>
				</td>
				<td align="center">
					<form method="post" action="policy-member-main.php">
						<div>
							<input type="hidden" name="frmaction" value="chmembers" />
							<input type="hidden" name="policy_id" value="<?php echo $row->id ?>" />
						</div>
						<input type="submit" class="button" value="Change members" />
					</form>
				</td>
				<td align="center">
					<form method="post" action="policy-main.php">
						<div>
							<input type="hidden" name="frmaction" value="delete" />
							<input type="hidden" name="policy_id" value="<?php echo $row->id ?>" />
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
	<form method="post" action="policy-main.php">
		<div>
			<input type="hidden" name="frmaction" value="add" />
		</div>
		<table class="entry">
		<caption>Add new Policy</caption>
			<tr>
				<td class="entrytitle">Name</td>
				<td><input type="text" name="policy_name" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Priority</td>
				<td>
					<input type="text" size="4" name="policy_priority" value="50" />
					<?php tooltip('policy_priority'); ?>
				</td>
			</tr>
			<tr>
				<td class="entrytitle texttop">Description</td>
				<td><textarea name="policy_description" cols="30" rows="2" /></textarea></td>
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
