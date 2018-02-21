<?php
# Module: CheckSPF
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
	if (empty($_POST['checkspf_policyid'])) {
		echo "Policy ID cannot be empty";

	# Check name
	} elseif (empty($_POST['checkspf_name'])) {
		echo "Name cannot be empty";
	} else {
		# Sort out if we going to use SPF or not
		switch ($_POST['checkspf_usespf']) {
			case "0":
				$useSPF = null;
				break;
			case "1":
				$useSPF = 1;
				break;
			case "2":
				$useSPF = 2;
				break;
		}

		# And if we reject on failed
		switch ($_POST['checkspf_rejectfailed']) {
			case "0":
				$rejectFailed = null;
				break;
			case "1":
				$rejectFailed = 1;
				break;
			case "2":
				$rejectFailed = 2;
				break;
		}

		# And if we add the spf header
		switch ($_POST['checkspf_addheader']) {
			case "0":
				$addHeader = null;
				break;
			case "1":
				$addHeader = 1;
				break;
			case "2":
				$addHeader = 2;
				break;
		}

		$stmt = $db->prepare("
			INSERT INTO ${DB_TABLE_PREFIX}checkspf 
				(PolicyID,Name,UseSPF,RejectFailedSPF,AddSPFHeader,Comment,Disabled) 
			VALUES 
				(?,?,?,?,?,1,1)
		");
		
		$res = $stmt->execute(array(
			$_POST['checkspf_policyid'],
			$_POST['checkspf_name'],
			$useSPF,
			$rejectFailed,
			$addHeader,
			//$_POST['checkspf_comment']
		));
		
		if ($res) {
header('Location: '.$_SERVER['REQUEST_URI']); 
		} else {
			echo "Failed to create SPF check";
			echo " print_r($stmt->errorInfo()) ";
		}
	}
}


#IF POST CONTIENE DELETE	
elseif ($_POST['frmaction'] == "delete")  {
				
	 if (isset($_POST['id'])) {

		$db->beginTransaction();
		$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}checkspf WHERE ID = ".$db->quote($_POST['id']));	//elimina la policy	
				
		if ($res !== FALSE) {
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
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}checkspf SET Disabled = 0 WHERE ID = ".$db->quote($_POST['id']));
		}
		ELSE {
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}checkspf SET Disabled = 1 WHERE ID = ".$db->quote($_POST['id']));
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
	<p class="pageheader">SPF Checks</p>

	<?php include_once("includes/sorttable.php");?>
	
	<table id="mainresults" class="results" style="width: 75%;">
		<tr class="resultstitle">
			<td class="textcenter" onclick="sortTable(0)">ID</td>
			<td class="textcenter" onclick="sortTable(1)">Policy</td>
			<td class="textcenter" onclick="sortTable(2)">Name</td>
			<td class="textcenter" onclick="sortTable(3)">Status</td>
			<td class="textcenter">Delete</td>
		</tr>
<?php
		$sql = "
				SELECT 
					${DB_TABLE_PREFIX}checkspf.ID, ${DB_TABLE_PREFIX}checkspf.Name, ${DB_TABLE_PREFIX}checkspf.Disabled,
					${DB_TABLE_PREFIX}policies.Name AS PolicyName

				FROM 
					${DB_TABLE_PREFIX}checkspf, ${DB_TABLE_PREFIX}policies

				WHERE
					${DB_TABLE_PREFIX}policies.ID = ${DB_TABLE_PREFIX}checkspf.PolicyID

				ORDER BY 
					${DB_TABLE_PREFIX}policies.Name
		";
		$res = $db->query($sql);

		while ($row = $res->fetchObject()) {
?>
			<tr class="resultsitem">
				<td align="center"><?php echo $row->id ?></td>
				<td><?php echo $row->policyname ?></td>
				<td><?php echo $row->name ?></td>
				<td class="textcenter">
					<form method="post" action="checkspf-main.php">
						<div>
							<input type="hidden" name="frmaction" value="change" />
							<input type="hidden" name="id" value="<?php echo $row->id ?>" />
							<input type="hidden" name="status" value="<?php echo $row->disabled ?>" />
						</div>
						<input type="submit" class="<?php echo $row->disabled ? 'buttondisabled' : 'buttonenabled' ?>" value="<?php echo $row->disabled ? 'Disabled' : 'Enabled' ?>" />
					</form>					
				</td>
				<td align="center">
					<form method="post" action="checkspf-main.php">
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
	<form method="post" action="checkspf-main.php">
		<div>
			<input type="hidden" name="frmaction" value="add" />
		</div>
		<table class="entry">
		<caption>Add new SPF Check rule</caption>
			<tr>
				<td class="entrytitle">Name</td>
				<td><input type="text" name="checkspf_name" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Link to policy</td>
				<td>
					<select name="checkspf_policyid">
<?php
						$res = $db->query("SELECT ID, Name FROM ${DB_TABLE_PREFIX}policies ORDER BY Name");
						while ($row = $res->fetchObject()) {
?>
							<option value="<?php echo $row->id ?>"><?php echo $row->name ?></option>
<?php
						}
?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">Use SPF</td>
				<td>
					<select name="checkspf_usespf">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Yes</option>
						<option value="2">No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Reject Failed SPF
					<?php tooltip('checkspf_rejectfailed'); ?>
				</td>
				<td>
					<select name="checkspf_rejectfailed">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Yes</option>
						<option value="2">No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Add SPF Header
					<?php tooltip('checkspf_addheader'); ?>
				</td>
				<td>
					<select name="checkspf_addheader">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Yes</option>
						<option value="2">No</option>
					</select>
				</td>
			</tr>
			<!--
			<tr>
				<td class="entrytitle">Comment</td>
				<td><textarea name="checkspf_comment" cols="30" rows="2"></textarea></td>
			</tr>
			-->
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
