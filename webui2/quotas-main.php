<?php
# Module: Quotas
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

	# Check policy id
	if (empty($_POST['quota_policyid'])) {
		echo "Policy ID cannot be empty";

	# Check name
	} elseif (empty($_POST['quota_name'])) {
		echo "Name cannot be empty";

	# Check verdict
	} elseif (empty($_POST['quota_verdict'])) {
		echo "Verdict cannot be empty";

	# Check last quota
	} elseif (!isset($_POST['quota_lastquota'])) {
		echo "Stop procesing here field cannot be empty";

	} else {
		if ($_POST['quota_track'] == "SenderIP") {
			$quotaTrack = sprintf('%s:%s',$_POST['quota_track'],$_POST['quota_trackextra']);
		} else {
			$quotaTrack = $_POST['quota_track'];
		}

		$stmt = $db->prepare("
			INSERT INTO ${DB_TABLE_PREFIX}quotas 
				(PolicyID,Name,Track,Period,Verdict,Data,LastQuota,Comment,Disabled)
			VALUES 
				(?,?,?,?,?,?,?,?,1)
		");
		
		$res = $stmt->execute(array(
			$_POST['quota_policyid'],
			$_POST['quota_name'],
			$quotaTrack,
			$_POST['quota_period'],
			$_POST['quota_verdict'],
			$_POST['quota_lastquota'],
			$_POST['quota_data'],
			$_POST['quota_comment']
		));
	
		if ($res) {
header('Location: '.$_SERVER['REQUEST_URI']); 
		} else {
			echo "Failed to create quota";
			echo " print_r($stmt->errorInfo()) ";

		}
	}
}

#IF POST CONTIENE DELETE	
elseif ($_POST['frmaction'] == "delete")  {
				
	 if (isset($_POST['quota_id'])) {

		$db->beginTransaction();
		$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}quotas WHERE ID = ".$db->quote($_POST['quota_id']));	//elimina l'action	
		$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}quotas_limits WHERE QuotasID = ".$db->quote($_POST['quota_id']));	//elimina anche tutti i limiti della quota
		if ($res !== FALSE) {
			$db->commit();
		}
header('Location: '.$_SERVER['REQUEST_URI']); 
	 } 
		
}

#IF POST CONTIENE CHANGE	
elseif ($_POST['frmaction'] == "change")  {
				
	if (isset($_POST['quota_id'])) {
		$db->beginTransaction();
		
		if ($_POST['quota_status'] == 1){
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}quotas SET Disabled = 0 WHERE ID = ".$db->quote($_POST['quota_id']));
		}
		ELSE {
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}quotas SET Disabled = 1 WHERE ID = ".$db->quote($_POST['quota_id']));
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
	<p class="pageheader">Quota List</p>

	<form id="main_form" action="quotas-main.php" method="post">


		<?php include_once("includes/sorttable.php");?>
	
		<table id="mainresults" class="results" style="width: 75%;">
				<tr class="resultstitle">
				<td class="textcenter" onclick="sortTable(0)">ID</td>
				<td class="textcenter" onclick="sortTable(1)">Policy</td>
				<td class="textcenter" onclick="sortTable(2)">Name</td>
				<td class="textcenter" onclick="sortTable(3)">Track</td>
				<td class="textcenter" onclick="sortTable(4)">Period</td>
				<td class="textcenter" onclick="sortTable(5)">Verdict</td>
				<td class="textcenter" onclick="sortTable(6)">Data</td>
				<td class="textcenter" onclick="sortTable(7)">Status</td>
				<td class="textcenter">Details</td>
				<td class="textcenter">Limits</td>
				<td class="textcenter">Delete</td>
			</tr>
<?php
			$sql = "
					SELECT 
						${DB_TABLE_PREFIX}quotas.ID, ${DB_TABLE_PREFIX}quotas.Name, ${DB_TABLE_PREFIX}quotas.Track, 
						${DB_TABLE_PREFIX}quotas.Period, 
						${DB_TABLE_PREFIX}quotas.Verdict, ${DB_TABLE_PREFIX}quotas.Data, 
						${DB_TABLE_PREFIX}quotas.Disabled, ${DB_TABLE_PREFIX}quotas.Comment,
						${DB_TABLE_PREFIX}policies.Name AS PolicyName

					FROM 
						${DB_TABLE_PREFIX}quotas, ${DB_TABLE_PREFIX}policies

					WHERE
						${DB_TABLE_PREFIX}policies.ID = ${DB_TABLE_PREFIX}quotas.PolicyID

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
					<td><?php echo $row->track ?></td>
					<td><?php echo $row->period ?></td>
					<td><?php echo $row->verdict ?></td>
					<td><?php echo $row->data ?></td>
					<td class="textcenter">
						<form method="post" action="quotas-main.php">
							<div>
								<input type="hidden" name="frmaction" value="change" />
								<input type="hidden" name="quota_id" value="<?php echo $row->id ?>" />
								<input type="hidden" name="quota_status" value="<?php echo $row->disabled ?>" />
							</div>
							<input type="submit" class="<?php echo $row->disabled ? 'buttondisabled' : 'buttonenabled' ?>" value="<?php echo $row->disabled ? 'Disabled' : 'Enabled' ?>" />
						</form>	
					</td>				
					<td align="center">
						<form method="post" action="quotas-change.php">
							<div>
								<input type="hidden" name="frmaction" value="change" />
								<input type="hidden" name="quota_id" value="<?php echo $row->id ?>" />
							</div>
							<input type="submit" class="button" value="Edit" />
						</form>
						</td>
					<td align="center">
						<form method="post" action="quotas-limits-main.php">
							<div>
								<input type="hidden" name="frmaction" value="change" />
								<input type="hidden" name="quota_id" value="<?php echo $row->id ?>" />
							</div>
							<input type="submit" class="button" value="Limits" />
						</form>
						</td>
					<td align="center">
						<form method="post" action="quotas-main.php">
							<div>
								<input type="hidden" name="frmaction" value="delete" />
								<input type="hidden" name="quota_id" value="<?php echo $row->id ?>" />
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
	</form>
	

	</br></br><hr width="75%"></br>
	
	<!---INIZIO QUICK ADD-->	
	<form method="post" action="quotas-main.php">
		<div>
			<input type="hidden" name="frmaction" value="add" />
		</div>
		<table class="entry">
		<caption>Add new Quota rule</caption>
			<tr>
				<td class="entrytitle">Name</td>
				<td><input type="text" name="quota_name" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Track</td>
				<td>
					<select id="quota_track" name="quota_track"
							onchange="
								var myobj = document.getElementById('quota_track');
								var myobj2 = document.getElementById('quota_trackextra');

								if (myobj.selectedIndex == 0) {
									myobj2.disabled = false;
									myobj2.value = '/32';
								} else if (myobj.selectedIndex != 0) {
									myobj2.disabled = true;
									myobj2.value = 'n/a';
								}
					">
						<option value="SenderIP">Sender IP</option>
						<option value="Sender:user@domain" selected="selected">Sender:user@domain</option>
						<option value="Sender:@domain">Sender:@domain</option>
						<option value="Sender:user@">Sender:user@</option>
						<option value="Recipient:user@domain">Recipient:user@domain</option>
						<option value="Recipient:@domain">Recipient:@domain</option>
						<option value="Recipient:user@">Recipient:user@</option>
						<option value="SASLUsername">SASLUsername:username</option>
						<option value="Policy">Policy</option>
					</select>
					<input type="text" id="quota_trackextra" name="quota_trackextra" size="18" value="n/a" disabled="disabled" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle">Period</td>
				<td><input type="text" name="quota_period" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Link to policy</td>
				<td>
					<select name="quota_policyid">
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
				<td class="entrytitle">Verdict</td>
				<td>
					<select name="quota_verdict">
						<option value="HOLD">Hold</option>
						<option value="REJECT" selected="selected">Reject</option>
						<option value="DEFER">Defer (delay)</option>
						<option value="DISCARD">Discard (drop)</option>
						<option value="FILTER">Filter</option>
						<option value="REDIRECT">Redirect</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">Data</td>
				<td><input type="text" name="quota_data" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Stop processing here</td>
				<td>
					<select name="quota_lastquota">
						<option value="0">No</option>
						<option value="1">Yes</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">Comment</td>
				<td><textarea name="quota_comment" cols="30" rows="2"></textarea></td>
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
