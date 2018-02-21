<?php
# Module: Quotas limits
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
			"Back to quotas" => "quotas-main.php"
		),
));


#IF POST CONTIENE ADD
if ($_POST['frmaction'] == "add")  {

	# Check we have a limit
	if (empty($_POST['limit_counterlimit'])) {
		echo "Counter limit is required";

	} else {
		$stmt = $db->prepare("INSERT INTO ${DB_TABLE_PREFIX}quotas_limits (QuotasID,Type,CounterLimit,Comment,Disabled) VALUES (?,?,?,?,1)");
		$res = $stmt->execute(array(
			$_POST['quota_id'],
			$_POST['limit_type'],
			$_POST['limit_counterlimit'],
			$_POST['limit_comment']
		));
	}
}

#IF POST CONTIENE CHSTATUS	
 elseif ($_POST['frmaction'] == "change")  {


	if (isset($_POST['limit_id'])) {
		$db->beginTransaction();
		
		if ($_POST['limit_status'] == 1){
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}quotas_limits SET Disabled = 0 WHERE ID = ".$db->quote($_POST['limit_id']));
		}
		ELSE {
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}quotas_limits SET Disabled = 1 WHERE ID = ".$db->quote($_POST['limit_id']));
		}
		
		if ($res !== FALSE) {
			$db->commit();
		}
	} 
}

#IF POST CONTIENE DELETE	
 elseif ($_POST['frmaction'] == "delete")  {
				
	if (isset($_POST['limit_id'])) {
		$db->beginTransaction();
		$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}quotas_limits WHERE ID = ".$db->quote($_POST['limit_id']));
		if ($res !== FALSE) {
			
			$db->commit();
		}
	} 		
}



# Check a policy was selected
if (isset($_REQUEST['quota_id'])) {

	$stmt = $db->prepare("SELECT Type, CounterLimit, Disabled FROM ${DB_TABLE_PREFIX}quota_limits WHERE QuotaID = ?");
	$quota_stmt = $db->prepare("SELECT Name FROM ${DB_TABLE_PREFIX}quotas WHERE ID = ?");
	$quota_stmt->execute(array($_REQUEST['quota_id']));
	$row = $quota_stmt->fetchObject();
	$quota_stmt->closeCursor();
?>
	<p class="pageheader">Quota Limits</p>

		<div>
			<input type="hidden" name="quota_id" value="<?php echo $_REQUEST['quota_id'] ?>" />
		</div>

	<?php include_once("includes/sorttable.php");?>
	
	<table id="mainresults" class="results" style="width: 75%;">
		<tr class="resultstitle">
			<td class="textcenter" onclick="sortTable(0)">ID</td>
			<td class="textcenter" onclick="sortTable(1)">Type</td>
			<td class="textcenter" onclick="sortTable(2)">Counter Limit</td>
			<td class="textcenter" onclick="sortTable(3)">Status</td>
			<td class="textcenter">Delete</td>
		</tr>
<?php

		$stmt = $db->prepare("SELECT ID, Type, CounterLimit, Disabled FROM ${DB_TABLE_PREFIX}quotas_limits WHERE QuotasID = ?");
		$res = $stmt->execute(array($_REQUEST['quota_id']));

		$i = 0;

		# Loop with rows
		while ($row = $stmt->fetchObject()) {
?>
			<tr class="resultsitem">
				<td align="center"><?php echo $row->id ?></td>
				<td class="textcenter"><?php echo $row->type ?></td>
				<td class="textcenter"><?php echo $row->counterlimit ?></td>
				<td align="center">		
					<form action="quotas-limits-main.php" method="post">
						<div>
							<input type="hidden" name="frmaction" value="change" />
							<input type="hidden" name="quota_id" value="<?php echo $_POST['quota_id']; ?>" />
							<input type="hidden" name="limit_id" value="<?php echo $row->id ?>" />
							<input type="hidden" name="limit_status" value="<?php echo $row->disabled ?>" />
							<input type="submit" class="<?php echo $row->disabled ? 'buttondisabled' : 'buttonenabled' ?>" value="<?php echo $row->disabled ? 'Disabled' : 'Enabled' ?>" />
						</div>
					</form>
				</td>
				<td align="center">		
					<form action="quotas-limits-main.php" method="post">
						<div>
							<input type="hidden" name="frmaction" value="delete" />
							<input type="hidden" name="quota_id" value="<?php echo $_POST['quota_id']; ?>" />
							<input type="hidden" name="limit_id" value="<?php echo $row->id ?>" />
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
		
	</form>
	
			<form method="post" action="quotas-limits-main.php">
			<div>
				<input type="hidden" name="frmaction" value="add" />
				<input type="hidden" name="quota_id" value="<?php echo $_POST['quota_id'] ?>" />
			</div>
			<table class="entry">
			<caption>Add new Limit</caption>
				<tr>
					<td class="entrytitle">Type</td>
					<td>
						<select name="limit_type">
							<option value="MessageCount">Message Count</option>
							<option value="MessageCumulativeSize">Message Cumulative Size</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="entrytitle">Counter Limit</td>
					<td><input type="text" name="limit_counterlimit" /></td>
				</tr>
				<tr>
					<td class="entrytitle">Comment</td>
					<td><textarea name="limit_comment"></textarea></td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="submit" />
					</td>
				</tr>
			</table>
		</form>
	
	
<?php
} else {
?>
	<div class="warning">Invalid invocation</div>
<?php
}


printFooter();


# vim: ts=4
?>
