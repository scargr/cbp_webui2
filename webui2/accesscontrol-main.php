<?php
# Module: AccessControl
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
	if (empty($_POST['accesscontrol_policyid'])) {
		echo "Policy ID cannot be empty";

	# Check name
	} elseif (empty($_POST['accesscontrol_name'])) {
		echo "Name cannot be empty";

	# Check verdict
	} elseif (empty($_POST['accesscontrol_verdict'])) {
		echo "Verdict cannot be empty";

	} else {
		$stmt = $db->prepare("INSERT INTO ${DB_TABLE_PREFIX}access_control (PolicyID,Name,Verdict,Data,Comment,Disabled) VALUES (?,?,?,?,?,1)");
		$res = $stmt->execute(array(
			$_POST['accesscontrol_policyid'],
			$_POST['accesscontrol_name'],
			$_POST['accesscontrol_verdict'],
			$_POST['accesscontrol_data'],
			$_POST['accesscontrol_comment']
		));
		if ($res) {
			header('Location: '.$_SERVER['REQUEST_URI']); 
		} else {
			echo "Failed to create access control";
			echo "<?php print_r($stmt->errorInfo()) ?>";
		}
	}
}

#IF POST CONTIENE DELETE	
elseif ($_POST['frmaction'] == "delete")  {
				
	 if (isset($_POST['accesscontrol_id'])) {

		$db->beginTransaction();
		$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}access_control WHERE ID = ".$db->quote($_POST['accesscontrol_id']));	//elimina l'action	
		if ($res !== FALSE) {
			$db->commit();
		}
			header('Location: '.$_SERVER['REQUEST_URI']); 
	 } 
		
}


#IF POST CONTIENE CHANGE	
elseif ($_POST['frmaction'] == "change")  {
				
	if (isset($_POST['accesscontrol_id'])) {
		$db->beginTransaction();
		
		if ($_POST['accesscontrol_status'] == 1){
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}access_control SET Disabled = 0 WHERE ID = ".$db->quote($_POST['accesscontrol_id']));
		}
		ELSE {
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}access_control SET Disabled = 1 WHERE ID = ".$db->quote($_POST['accesscontrol_id']));
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
	<p class="pageheader">Access Control List</p>


	<?php include_once("includes/sorttable.php");?>
	
	<table id="mainresults" class="results" style="width: 75%;">
		<tr class="resultstitle">
			<td class="textcenter" onclick="sortTable(0)">ID</td>
			<td class="textcenter" onclick="sortTable(1)">Policy</td>
			<td class="textcenter" onclick="sortTable(2)">Name</td>
			<td class="textcenter" onclick="sortTable(3)">Verdict</td>
			<td class="textcenter" onclick="sortTable(4)">Data</td>
			<td class="textcenter" onclick="sortTable(5)">Status</td>
			<td class="textcenter">Details</td>
			<td class="textcenter">Delete</td>
		</tr>
<?php
		$sql = "
				SELECT 
					${DB_TABLE_PREFIX}access_control.ID, ${DB_TABLE_PREFIX}access_control.Name, 
					${DB_TABLE_PREFIX}access_control.Verdict, ${DB_TABLE_PREFIX}access_control.Data, 
					${DB_TABLE_PREFIX}access_control.Disabled,
					${DB_TABLE_PREFIX}policies.Name AS PolicyName

				FROM 
					${DB_TABLE_PREFIX}access_control, ${DB_TABLE_PREFIX}policies

				WHERE
					${DB_TABLE_PREFIX}policies.ID = ${DB_TABLE_PREFIX}access_control.PolicyID

				ORDER BY 
					${DB_TABLE_PREFIX}policies.Name
		";
		$res = $db->query($sql);

		while ($row = $res->fetchObject()) {
?>
			<tr class="resultsitem">
				<td class="textcenter"><?php echo $row->id ?></td>
				<td><?php echo $row->policyname ?></td>
				<td><?php echo $row->name ?></td>
				<td><?php echo $row->verdict ?></td>
				<td><?php echo $row->data ?></td>
				<!--<td class="textcenter"><?php echo $row->disabled ? 'yes' : 'no' ?></td>-->
				<td class="textcenter">
					<form method="post" action="accesscontrol-main.php">
						<div>
							<input type="hidden" name="frmaction" value="change" />
							<input type="hidden" name="accesscontrol_id" value="<?php echo $row->id ?>" />
							<input type="hidden" name="accesscontrol_status" value="<?php echo $row->disabled ?>" />
						</div>
						<input type="submit" class="<?php echo $row->disabled ? 'buttondisabled' : 'buttonenabled' ?>" value="<?php echo $row->disabled ? 'Disabled' : 'Enabled' ?>" />
					</form>					
				</td>
				<td align="center">
					<form method="post" action="accesscontrol-change.php">
						<div>
							<input type="hidden" name="frmaction" value="change" />
							<input type="hidden" name="accesscontrol_id" value="<?php echo $row->id ?>" />
						</div>
						<input type="submit" class="button" value="Edit" />
					</form>
				</td>
				<td align="center">
					<form method="post" action="accesscontrol-main.php">
						<div>
							<input type="hidden" name="frmaction" value="delete" />
							<input type="hidden" name="accesscontrol_id" value="<?php echo $row->id ?>" />
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

	
		</br></br><hr width="75%"></br></br>
	
	<!---INIZIO QUICK ADD-->
	<form method="post" action="accesscontrol-main.php">
		<div>
			<input type="hidden" name="frmaction" value="add" />
		</div>
		<table class="entry">
		<caption>Add new Access Control rule</caption>
			<tr>
				<td class="entrytitle">Name</td>
				<td><input type="text" name="accesscontrol_name" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Link to policy</td>
				<td>
					<select name="accesscontrol_policyid">
<?php
						$res = $db->query("SELECT ID, Name FROM ${DB_TABLE_PREFIX}policies ORDER BY Name");
						while ($row = $res->fetchObject()) {
?>
							<option value="<?php echo $row->id ?>"><?php echo $row->name ?></option>
<?php
						}
						$res->closeCursor();
?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Verdict
					<?php tooltip('accesscontrol_verdict'); ?>
				</td>
				<td>
					<select name="accesscontrol_verdict">
						<option value="HOLD">Hold</option>
						<option value="REJECT" selected="selected">Reject</option>
						<option value="DISCARD">Discard (drop)</option>
						<option value="FILTER">Filter</option>
						<option value="REDIRECT">Redirect</option>
						<option value="OK">Ok</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Data
					<?php tooltip('accesscontrol_data'); ?>
				</td>
				<td><input type="text" name="accesscontrol_data" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Comment</td>
				<td><textarea name="accesscontrol_comment" cols="30" rows="2"></textarea></td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="submit" class="button" />
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
