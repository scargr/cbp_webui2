<?php
# Module: Greylisting
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
	if (empty($_POST['greylisting_policyid'])) {
		echo "Policy ID cannot be empty";

	# Check name
	} elseif (empty($_POST['greylisting_name'])) {
		echo "Name cannot be empty";

	} else {
		# Sort out using of blacklist
		switch ($_POST['greylisting_usegreylisting']) {
			case "0":
				$useGreylisting = null;
				break;
			case "1":
				$useGreylisting = 1;
				break;
			case "2":
				$useGreylisting = 0;
				break;
		}
		# Check period
		if (empty($_POST['greylisting_period'])) {
			$greylistPeriod = null;
		} else {
			$greylistPeriod = $_POST['greylisting_period'];
		}

		# Check validity
		if (empty($_POST['greylisting_authvalidity'])) {
			$greylistAuthValidity = null;
		} else {
			$greylistAuthValidity = $_POST['greylisting_authvalidity'];
		}
		if (empty($_POST['greylisting_unauthvalidity'])) {
			$greylistUnAuthValidity = null;
		} else {
			$greylistUnAuthValidity = $_POST['greylisting_unauthvalidity'];
		}

		# Sort out using of AWL
		switch ($_POST['greylisting_useawl']) {
			case "0":
				$useAWL = null;
				break;
			case "1":
				$useAWL = 1;
				break;
			case "2":
				$useAWL = 0;
				break;
		}
		# AWL period
		if (empty($_POST['greylisting_awlperiod'])) {
			$AWLPeriod = null;
		} else {
			$AWLPeriod = $_POST['greylisting_awlperiod'];
		}
		# AWL count 
		if (empty($_POST['greylisting_awlcount'])) {
			$AWLCount = null;
		} else {
			$AWLCount = $_POST['greylisting_awlcount'];
		}
		# AWL percentage 
		if (!isset($_POST['greylisting_awlpercentage']) || $_POST['greylisting_awlpercentage'] == "") {
			$AWLPercentage = null;
		} else {
			$AWLPercentage = $_POST['greylisting_awlpercentage'];
		}

		# Sort out using of ABL
		switch ($_POST['greylisting_useabl']) {
			case "0":
				$useABL = null;
				break;
			case "1":
				$useABL = 1;
				break;
			case "2":
				$useABL = 0;
				break;
		}
		# ABL period
		if (empty($_POST['greylisting_ablperiod'])) {
			$ABLPeriod = null;
		} else {
			$ABLPeriod = $_POST['greylisting_ablperiod'];
		}
		# ABL count 
		if (empty($_POST['greylisting_ablcount'])) {
			$ABLCount = null;
		} else {
			$ABLCount = $_POST['greylisting_ablcount'];
		}
		# ABL percentage 
		if (!isset($_POST['greylisting_ablpercentage']) || $_POST['greylisting_ablpercentage'] == "") {
			$ABLPercentage = null;
		} else {
			$ABLPercentage = $_POST['greylisting_ablpercentage'];
		}

		$stmt = $db->prepare("
			INSERT INTO ${DB_TABLE_PREFIX}greylisting
					(
						PolicyID,Name,
						UseGreylisting,GreylistPeriod,
						Track,
						GreylistAuthValidity, GreylistUnAuthValidity,

						UseAutoWhitelist,AutoWhitelistPeriod,AutoWhitelistCount,AutoWhitelistPercentage,
						UseAutoBlacklist,AutoBlacklistPeriod,AutoBlacklistCount,AutoBlacklistPercentage,

						Comment,Disabled
					)					
				VALUES 
					(
						?,?,
						?,?,
						?,
						?,?,
						?,?,?,?,
						?,?,?,?,
						?,1
					)
		");
		
		$res = $stmt->execute(array(
			$_POST['greylisting_policyid'],
			$_POST['greylisting_name'],

			$useGreylisting,$greylistPeriod,
			$_POST['greylisting_track'] . ":" . $_POST['greylisting_trackextra'],
			$greylistAuthValidity,$greylistUnAuthValidity,

			$useAWL,$AWLPeriod,$AWLCount,$AWLPercentage,
			$useABL,$ABLPeriod,$ABLCount,$ABLPercentage,

			$_POST['greylisting_comment']
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
		$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}greylisting WHERE ID = ".$db->quote($_POST['id']));	//elimina la policy	
				
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
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}greylisting SET Disabled = 0 WHERE ID = ".$db->quote($_POST['id']));
		}
		ELSE {
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}greylisting SET Disabled = 1 WHERE ID = ".$db->quote($_POST['id']));
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
	<p class="pageheader">Greylisting Checks</p>

	<?php include_once("includes/sorttable.php");?>
	
	<table id="mainresults" class="results" style="width: 75%;">
		<tr class="resultstitle">
			<td id="noborder"></td>
			<td class="textcenter" onclick="sortTable(0)">Policy</td>
			<td class="textcenter" onclick="sortTable(1)">Name</td>
			<td class="textcenter" onclick="sortTable(2)">Status</td>
			<td class="textcenter">Details</td>
			<td class="textcenter">Delete</td>
		</tr>
<?php
		$sql = "
				SELECT 
					${DB_TABLE_PREFIX}greylisting.ID, ${DB_TABLE_PREFIX}greylisting.Name, ${DB_TABLE_PREFIX}greylisting.Disabled,
					${DB_TABLE_PREFIX}policies.Name AS PolicyName

				FROM 
					${DB_TABLE_PREFIX}greylisting, ${DB_TABLE_PREFIX}policies

				WHERE
					${DB_TABLE_PREFIX}policies.ID = ${DB_TABLE_PREFIX}greylisting.PolicyID

				ORDER BY 
					${DB_TABLE_PREFIX}policies.Name
		";
		$res = $db->query($sql);

		# Check if we got a result
		if ($res) {
			while ($row = $res->fetchObject()) {
?>
				<tr class="resultsitem">
					<td><input type="radio" name="greylisting_id" value="<?php echo $row->id ?>" /></td>
					<td><?php echo $row->policyname ?></td>
					<td><?php echo $row->name ?></td>
				<td class="textcenter">
					<form method="post" action="greylisting-main.php">
						<div>
							<input type="hidden" name="frmaction" value="change" />
							<input type="hidden" name="id" value="<?php echo $row->id ?>" />
							<input type="hidden" name="status" value="<?php echo $row->disabled ?>" />
						</div>
						<input type="submit" class="<?php echo $row->disabled ? 'buttondisabled' : 'buttonenabled' ?>" value="<?php echo $row->disabled ? 'Disabled' : 'Enabled' ?>" />
					</form>					
				</td>
				<td class="textcenter">
					<form method="post" action="greylisting-change.php">
						<div>
							<input type="hidden" name="frmaction" value="change" />
							<input type="hidden" name="greylisting_id" value="<?php echo $row->id ?>" />

						</div>
						<input type="submit" class="button" value="Edit" />
					</form>					
				</td>
				<td align="center">
					<form method="post" action="greylisting-main.php">
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
		# Or not
		} else {
?>
				<tr class="resultsitem">
					<td class="textcenter warning" colspan="4"><?php print_r($db->errorInfo()) ?></td>
				</tr>
<?php
		}
?>
	</table>

	

	</br></br><hr width="75%"></br>
	
	<!---INIZIO QUICK ADD-->
	<form method="post" action="greylisting-main.php">
		<div>
			<input type="hidden" name="frmaction" value="add" />
		</div>
		<table class="entry">
		<caption>Add new Greylisting rule</caption>
			<tr>
				<td class="entrytitle">Name</td>
				<td><input type="text" name="greylisting_name" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Link to policy</td>
				<td>
					<select name="greylisting_policyid">
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
				<td colspan="2" class="textcenter" style="border-bottom: 1px dashed black;">Greylisting</td>
			</tr>
			<tr>
				<td class="entrytitle">Use Greylisting</td>
				<td>
					<select name="greylisting_usegreylisting">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Yes</option>
						<option value="2">No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Greylist Period
					<?php tooltip('greylisting_period'); ?>
				</td>
				<td><input type="text" name="greylisting_period" /></td>
			</tr>
			<tr>
				<td class="entrytitle">
					Track
					<?php tooltip('greylisting_track'); ?>
				</td>
				<td>
					<select id="greylisting_track" name="greylisting_track"
							onchange="
								var myobj = document.getElementById('greylisting_track');
								var myobj2 = document.getElementById('greylisting_trackextra');

								if (myobj.selectedIndex == 0) {
									myobj2.disabled = false;
									myobj2.value = '/32';
								} else if (myobj.selectedIndex != 0) {
									myobj2.disabled = true;
									myobj2.value = 'n/a';
								}
					">
						<option value="SenderIP">Sender IP</option>
					</select>
					<input type="text" id="greylisting_trackextra" name="greylisting_trackextra" size="18" value="/32" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Greylist Auth Validity
					<?php tooltip('greylisting_auth_validity'); ?>
				</td>
				<td><input type="text" name="greylisting_authvalidity" /></td>
			</tr>
			<tr>
				<td class="entrytitle">
					Greylist UnAuth Validity
					<?php tooltip('greylisting_unauth_validity'); ?>
				</td>
				<td><input type="text" name="greylisting_unauthvalidity" /></td>
			</tr>
			<tr>
				<td colspan="2" class="textcenter" style="border-bottom: 1px dashed black;">Auto-Whitelisting</td>
			</tr>
			<tr>
				<td class="entrytitle">Use AWL</td>
				<td>
					<select name="greylisting_useawl">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Yes</option>
						<option value="2">No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					AWL For Period
					<?php tooltip('greylisting_awl_period'); ?>
				</td>
				<td><input type="text" name="greylisting_awlperiod" /></td>
			</tr>
			<tr>
				<td class="entrytitle">
					AWL After Count
					<?php tooltip('greylisting_awl_count'); ?>
				</td>
				<td><input type="text" name="greylisting_awlcount" /></td>
			</tr>
			<tr>
				<td class="entrytitle">
					AWL After Percentage
					<?php tooltip('greylisting_awl_percentage'); ?>
				</td>
				<td><input type="text" name="greylisting_awlpercentage" /> (blank = inherit, 0 = disable)</td>
			</tr>
			<tr>
				<td colspan="2" class="textcenter" style="border-bottom: 1px dashed black;">Auto-Blacklisting</td>
			</tr>
			<tr>
				<td class="entrytitle">Use ABL</td>
				<td>
					<select name="greylisting_useabl">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Yes</option>
						<option value="2">No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					ABL For Period
					<?php tooltip('greylisting_abl_period'); ?>
				</td>
				<td><input type="text" name="greylisting_ablperiod" /></td>
			</tr>
			<tr>
				<td class="entrytitle">
					ABL After Count
					<?php tooltip('greylisting_abl_count'); ?>
				</td>
				<td><input type="text" name="greylisting_ablcount" /></td>
			</tr>
			<tr>
				<td class="entrytitle">
					ABL After Percentage
					<?php tooltip('greylisting_abl_percentage'); ?>
				</td>
				<td><input type="text" name="greylisting_ablpercentage" /></td>
			</tr>
			<tr>
				<td colspan="2" class="textcenter" style="border-bottom: 1px dashed black;">&nbsp;</td>
			</tr>
			<tr>
				<td class="entrytitle">Comment</td>
				<td><textarea name="greylisting_comment" cols="30" rows="2"></textarea></td>
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
