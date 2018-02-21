<?php
# Module: CheckHelo
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
	if (empty($_POST['checkhelo_policyid'])) {
		echo "Policy ID cannot be empty";

	# Check name
	} elseif (empty($_POST['checkhelo_name'])) {
		echo "Name cannot be empty";

	} else {
		# Sort out using of blacklist
		switch ($_POST['checkhelo_useblacklist']) {
			case "0":
				$useBlacklist = null;
				break;
			case "1":
				$useBlacklist = 1;
				break;
			case "2":
				$useBlacklist = 0;
				break;
		}
		# Check period
		if (empty($_POST['checkhelo_blacklistperiod'])) {
			$blacklistPeriod = null;
		} else {
			$blacklistPeriod = $_POST['checkhelo_blacklistperiod'];
		}

		# Sort out using of HRP
		switch ($_POST['checkhelo_usehrp']) {
			case "0":
				$useHRP = null;
				break;
			case "1":
				$useHRP = 1;
				break;
			case "2":
				$useHRP = 0;
				break;
		}
		# Check period
		if (empty($_POST['checkhelo_hrpperiod'])) {
			$HRPPeriod = null;
		} else {
			$HRPPeriod = $_POST['checkhelo_hrpperiod'];
		}
		# Check limit
		if (empty($_POST['checkhelo_hrplimit'])) {
			$HRPLimit = null;
		} else {
			$HRPLimit = $_POST['checkhelo_hrplimit'];
		}

		# Sort out checking invalid HELO's
		switch ($_POST['checkhelo_rejectinvalid']) {
			case "0":
				$rejectInvalid = null;
				break;
			case "1":
				$rejectInvalid = 1;
				break;
			case "2":
				$rejectInvalid = 0;
				break;
		}
		# Sort out checking HELO's for IP's
		switch ($_POST['checkhelo_rejectip']) {
			case "0":
				$rejectIP = null;
				break;
			case "1":
				$rejectIP = 1;
				break;
			case "2":
				$rejectIP = 0;
				break;
		}
		# Sort out checking HELO's are resolvable
		switch ($_POST['checkhelo_rejectunresolvable']) {
			case "0":
				$rejectUnresolvable = null;
				break;
			case "1":
				$rejectUnresolvable = 1;
				break;
			case "2":
				$rejectUnresolvable = 0;
				break;
		}

		$stmt = $db->prepare("
			INSERT INTO ${DB_TABLE_PREFIX}checkhelo
					(
						PolicyID,Name,
						UseBlacklist,BlacklistPeriod,
						UseHRP,HRPPeriod,HRPLimit,
						RejectInvalid,RejectIP,RejectUnresolvable,
						Comment,Disabled
					)					
				VALUES 
					(
						?,?,
						?,?,
						?,?,?,
						?,?,?,
						?,1
					)
		");
		
		$res = $stmt->execute(array(
			$_POST['checkhelo_policyid'],
			$_POST['checkhelo_name'],
			$useBlacklist,$blacklistPeriod,
			$useHRP,$HRPPeriod,$HRPLimit,
			$rejectInvalid,$rejectIP,$rejectUnresolvable,
			$_POST['checkhelo_comment']
		));

		if ($res) {
header('Location: '.$_SERVER['REQUEST_URI']); 
		} 
	}
}


#IF POST CONTIENE DELETE	
elseif ($_POST['frmaction'] == "delete")  {
				
	 if (isset($_POST['checkhelo_id'])) {

		$db->beginTransaction();
		$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}checkhelo WHERE ID = ".$db->quote($_POST['checkhelo_id']));	//elimina la policy	
		if ($res !== FALSE) {
			$db->commit();
		}
header('Location: '.$_SERVER['REQUEST_URI']); 
	 } 
}

#IF POST CONTIENE CHANGE	
elseif ($_POST['frmaction'] == "change")  {
				
	if (isset($_POST['checkhelo_id'])) {
		$db->beginTransaction();
		
		if ($_POST['checkhelo_status'] == 1){
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}checkhelo SET Disabled = 0 WHERE ID = ".$db->quote($_POST['checkhelo_id']));
		}
		ELSE {
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}checkhelo SET Disabled = 1 WHERE ID = ".$db->quote($_POST['checkhelo_id']));
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
	<p class="pageheader">HELO/EHLO Checks</p>

	<form id="main_form" action="checkhelo-main.php" method="post">

		<div class="textcenter">
			<!--SELETTORE
			Action
			<select id="main_form_action" name="frmaction" 
					onchange="
						var myform = document.getElementById('main_form');
						var myobj = document.getElementById('main_form_action');

						if (myobj.selectedIndex == 2) {
							myform.action = 'checkhelo-add.php';
						} else if (myobj.selectedIndex == 4) {
							myform.action = 'checkhelo-change.php';
						} else if (myobj.selectedIndex == 5) {
							myform.action = 'checkhelo-delete.php';
						}

						myform.submit();
					">
			 
				<option selected="selected">select action</option>
				<option disabled="disabled"> - - - - - - - - - - - </option>
				<option value="add">Add</option>
				<option disabled="disabled"> - - - - - - - - - - - </option>
				<option value="change">Change</option>
				<option value="delete">Delete</option>
			</select> 
			-->
		</div>

		<p />

		<?php include_once("includes/sorttable.php");?>
	
		<table id="mainresults" class="results" style="width: 75%;">
			<tr class="resultstitle">
				<td class="textcenter" onclick="sortTable(0)">ID</td>
				<td class="textcenter" onclick="sortTable(1)">Policy</td>
				<td class="textcenter" onclick="sortTable(2)">Name</td>
				<td class="textcenter" onclick="sortTable(3)">Status</td>
				<td class="textcenter">Edit</td>
				<td class="textcenter">Delete</td>
			</tr>
<?php
			$sql = "
					SELECT 
						${DB_TABLE_PREFIX}checkhelo.ID, ${DB_TABLE_PREFIX}checkhelo.Name, ${DB_TABLE_PREFIX}checkhelo.Disabled,
						${DB_TABLE_PREFIX}policies.Name AS PolicyName

					FROM 
						${DB_TABLE_PREFIX}checkhelo, ${DB_TABLE_PREFIX}policies

					WHERE
						${DB_TABLE_PREFIX}policies.ID = ${DB_TABLE_PREFIX}checkhelo.PolicyID

					ORDER BY 
						${DB_TABLE_PREFIX}policies.Name
			";
			$res = $db->query($sql);

			while ($row = $res->fetchObject()) {
?>
				<tr class="resultsitem">
					<td align="center"><?php echo $row->id ?></td>
					<td class="textcenter"><?php echo $row->policyname ?></td>
					<td class="textcenter"><?php echo $row->name ?></td>
					<td class="textcenter">
						<form method="post" action="checkhelo-main.php">
							<div>
								<input type="hidden" name="frmaction" value="change" />
								<input type="hidden" name="checkhelo_id" value="<?php echo $row->id ?>" />
								<input type="hidden" name="checkhelo_status" value="<?php echo $row->disabled ?>" />
							</div>
							<input type="submit" class="<?php echo $row->disabled ? 'buttondisabled' : 'buttonenabled' ?>" value="<?php echo $row->disabled ? 'Disabled' : 'Enabled' ?>" />
						</form>					
					</td>
					<td align="center">
						<form method="post" action="checkhelo-change.php">
							<div>
								<input type="hidden" name="frmaction" value="change" />
								<input type="hidden" name="checkhelo_id" value="<?php echo $row->id ?>" />
							</div>
							<input type="submit" class="button" value="Edit" />
						</form>					
					</td>
					<td align="center">
						<form method="post" action="checkhelo-main.php">
							<div>
								<input type="hidden" name="frmaction" value="delete" />
								<input type="hidden" name="checkhelo_id" value="<?php echo $row->id ?>" />
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
<form method="post" action="checkhelo-main.php">
		<div>
			<input type="hidden" name="frmaction" value="add" />
		</div>
		<table class="entry">
		<caption>Add new Check HELO rule</caption>
			<tr>
				<td class="entrytitle">Name</td>
				<td><input type="text" name="checkhelo_name" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Link to policy</td>
				<td>
					<select name="checkhelo_policyid">
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
				<td colspan="2" class="textcenter" style="border-bottom: 1px dashed black;">Blacklisting</td>
			</tr>
			<tr>
				<td class="entrytitle">Use Blacklist</td>
				<td>
					<select name="checkhelo_useblacklist">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Yes</option>
						<option value="2">No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Blacklist Period
					<?php tooltip('checkhelo_blacklist_period'); ?>
				</td>
				<td><input type="text" name="checkhelo_blacklistperiod" /></td>
			</tr>
			<tr>
				<td colspan="2" class="textcenter" style="border-bottom: 1px dashed black;">Randomization Prevention</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Use HRP
				</td>
				<td>
					<select name="checkhelo_usehrp">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Yes</option>
						<option value="2">No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					HRP Period
					<?php tooltip('checkhelo_blacklist_hrpperiod'); ?>
				</td>
				<td><input type="text" name="checkhelo_hrpperiod" /></td>
			</tr>
			<tr>
				<td class="entrytitle">
					HRP Limit
					<?php tooltip('checkhelo_blacklist_hrplimit'); ?>
				</td>
				<td><input type="text" name="checkhelo_hrplimit" /></td>
			</tr>
			<tr>
				<td colspan="2" class="textcenter" style="border-bottom: 1px dashed black;">Reject (RFC non-compliance)</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Reject Invalid
					<?php tooltip('checkhelo_rejectinvalid'); ?>
				</td>
				<td>
					<select name="checkhelo_rejectinvalid">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Yes</option>
						<option value="2">No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Reject non-literal IP
					<?php tooltip('checkhelo_rejectip'); ?>
				</td>
				<td>
					<select name="checkhelo_rejectip">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Yes</option>
						<option value="2">No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Reject Unresolvable
					<?php tooltip('checkhelo_rejectunresolv'); ?>
				</td>
				<td>
					<select name="checkhelo_rejectunresolvable">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Yes</option>
						<option value="2">No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="textcenter" style="border-bottom: 1px dashed black;">&nbsp;</td>
			</tr>
			<tr>
				<td class="entrytitle">Comment</td>
				<td><textarea name="checkhelo_comment" cols="30" rows="2"></textarea></td>
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
