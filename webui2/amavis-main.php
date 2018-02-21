<?php
# Main amavisd display
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

//test commit
//altro test commit
//last comm

$db = connect_db();



printHeader(array(
));

#####################blocco funzioni


# Process an option
function process_post_option($option) {
	$results = array();

	# Inherit
	if ($option == 0) {
		array_push($results,NULL);
		array_push($results,0);
	# Explicit yes
	} elseif ($option == 1) {
		array_push($results,1);
		array_push($results,2);
	# Explicit no
	} elseif ($option == 2) {
		array_push($results,0);
		array_push($results,2);
	}

	return $results;
};


# Process a value
function process_post_value($option,$value) {
	$results = array();

	# Inherit
	if ($option == 0) {
		array_push($results,NULL);
		array_push($results,0);
	# Override
	} elseif ($option == 2) {
		array_push($results,$value);
		array_push($results,2);
	}

	return $results;
};


# Process a list of items
function process_post_list($option,$value) {
	$results = array();

	# Inherit
	if ($option == 0) {
		array_push($results,NULL);
		array_push($results,0);
	# Merge
	} elseif ($option == 1) {
		array_push($results,$value);
		array_push($results,1);
	# Override
	} elseif ($option == 2) {
		array_push($results,$value);
		array_push($results,2);
	}

	return $results;
};

#####################fine blocco funzioni


#IF POST CONTIENE ADD3
if ($_POST['frmaction'] == "add") {

	# Check name
	if (empty($_POST['amavis_policyid'])) {
		echo "Policy ID cannot be empty";
	# Check name
	} elseif (empty($_POST['amavis_name'])) {
		echo "Name cannot be empty";

	} else {
		$dbinfo = array();

		# add stuff we need first...
		array_push($dbinfo,$_POST['amavis_policyid']);
		array_push($dbinfo,$_POST['amavis_name']);

		# Bypass options
		$res = process_post_option($_POST['amavis_bypass_virus_checks']);
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_option($_POST['amavis_bypass_banned_checks']);
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_option($_POST['amavis_bypass_spam_checks']);
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_option($_POST['amavis_bypass_header_checks']);
		$dbinfo = array_merge($dbinfo,$res);

		# Anti-spam options
		$res = process_post_value($_POST['amavis_spam_tag_level_mode'],isset($_POST['amavis_spam_tag_level']) ? $_POST['amavis_spam_tag_level'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_value($_POST['amavis_spam_tag2_level_mode'],isset($_POST['amavis_spam_tag2_level']) ? $_POST['amavis_spam_tag2_level'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_value($_POST['amavis_spam_tag3_level_mode'],isset($_POST['amavis_spam_tag3_level']) ? $_POST['amavis_spam_tag3_level'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_value($_POST['amavis_spam_kill_level_mode'],isset($_POST['amavis_spam_kill_level']) ? $_POST['amavis_spam_kill_level'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_value($_POST['amavis_spam_dsn_cutoff_level_mode'],
				isset($_POST['amavis_spam_dsn_cutoff_level']) ? $_POST['amavis_spam_dsn_cutoff_level'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_value($_POST['amavis_spam_quarantine_cutoff_level_mode'],
				isset($_POST['amavis_spam_quarantine_cutoff_level']) ? $_POST['amavis_spam_quarantine_cutoff_level'] : '');
		$dbinfo = array_merge($dbinfo,$res);

		$res = process_post_option($_POST['amavis_spam_modifies_subject']);
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_value($_POST['amavis_spam_tag_subject_mode'],isset($_POST['amavis_spam_tag_subject']) ? $_POST['amavis_spam_tag_subject'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_value($_POST['amavis_spam_tag2_subject_mode'],isset($_POST['amavis_spam_tag2_subject']) ? $_POST['amavis_spam_tag2_subject'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_value($_POST['amavis_spam_tag3_subject_mode'],isset($_POST['amavis_spam_tag3_subject']) ? $_POST['amavis_spam_tag3_subject'] : '');
		$dbinfo = array_merge($dbinfo,$res);

		# General
		$res = process_post_value($_POST['amavis_max_message_size_mode'],isset($_POST['amavis_max_message_size']) ? $_POST['amavis_max_message_size'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_list($_POST['amavis_banned_files_mode'],isset($_POST['amavis_banned_files']) ? $_POST['amavis_banned_files'] : '');
		$dbinfo = array_merge($dbinfo,$res);

		# Whitelist & blacklist	
		$res = process_post_list($_POST['amavis_sender_whitelist_mode'],isset($_POST['amavis_sender_whitelist']) ? $_POST['amavis_sender_whitelist'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_list($_POST['amavis_sender_blacklist_mode'],isset($_POST['amavis_sender_blacklist']) ? $_POST['amavis_sender_blacklist'] : '');
		$dbinfo = array_merge($dbinfo,$res);

		# Notifications	
		$res = process_post_value($_POST['amavis_notify_admin_newvirus_mode'],isset($_POST['amavis_notify_admin_newvirus']) ? 
				$_POST['amavis_notify_admin_newvirus'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_value($_POST['amavis_notify_admin_virus_mode'],isset($_POST['amavis_notify_admin_virus']) ? 
				$_POST['amavis_notify_admin_virus'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_value($_POST['amavis_notify_admin_spam_mode'],isset($_POST['amavis_notify_admin_spam']) ? 
				$_POST['amavis_notify_admin_spam'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_value($_POST['amavis_notify_admin_banned_file_mode'],isset($_POST['amavis_notify_admin_banned_file']) ? 
				$_POST['amavis_notify_admin_banned_file'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_value($_POST['amavis_notify_admin_bad_header_mode'],isset($_POST['amavis_notify_admin_bad_header']) ? 
				$_POST['amavis_notify_admin_bad_header'] : '');
		$dbinfo = array_merge($dbinfo,$res);

		# Quarantine	
		$res = process_post_value($_POST['amavis_quarantine_virus_mode'],isset($_POST['amavis_quarantine_virus']) ? 
				$_POST['amavis_quarantine_virus'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_value($_POST['amavis_quarantine_spam_mode'],isset($_POST['amavis_quarantine_spam']) ? 
				$_POST['amavis_quarantine_spam'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_value($_POST['amavis_quarantine_banned_file_mode'],isset($_POST['amavis_quarantine_banned_file']) ? 
				$_POST['amavis_quarantine_banned_file'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		$res = process_post_value($_POST['amavis_quarantine_bad_header_mode'],isset($_POST['amavis_quarantine_bad_header']) ? 
				$_POST['amavis_quarantine_bad_header'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		
		# Interception	
		$res = process_post_value($_POST['amavis_bcc_to_mode'],isset($_POST['amavis_bcc_to']) ? 
				$_POST['amavis_bcc_to'] : '');
		$dbinfo = array_merge($dbinfo,$res);
		
		# And stuff we need at end
		array_push($dbinfo,$_POST['amavis_comment']);

		$stmt = $db->prepare("
			INSERT INTO ${DB_TABLE_PREFIX}amavis_rules
				(
					PolicyID,Name,
					
					bypass_virus_checks, bypass_virus_checks_m,
					bypass_banned_checks, bypass_banned_checks_m,
					bypass_spam_checks, bypass_spam_checks_m,
					bypass_header_checks, bypass_header_checks_m,

					spam_tag_level, spam_tag_level_m,
					spam_tag2_level, spam_tag2_level_m,
					spam_tag3_level, spam_tag3_level_m,
					spam_kill_level, spam_kill_level_m,
					spam_dsn_cutoff_level, spam_dsn_cutoff_level_m,
					spam_quarantine_cutoff_level, spam_quarantine_cutoff_level_m,

					spam_modifies_subject, spam_modifies_subject_m,
					spam_tag_subject, spam_tag_subject_m,
					spam_tag2_subject, spam_tag2_subject_m,
					spam_tag3_subject, spam_tag3_subject_m,

					max_message_size, max_message_size_m,
					banned_files, banned_files_m,

					sender_whitelist, sender_whitelist_m,
					sender_blacklist, sender_blacklist_m,

					notify_admin_newvirus, notify_admin_newvirus_m,
					notify_admin_virus, notify_admin_virus_m,
					notify_admin_spam, notify_admin_spam_m,
					notify_admin_banned_file, notify_admin_banned_file_m,
					notify_admin_bad_header, notify_admin_bad_header_m,

					quarantine_virus, quarantine_virus_m,
					quarantine_spam, quarantine_spam_m,
					quarantine_banned_file, quarantine_banned_file_m,
					quarantine_bad_header, quarantine_bad_header_m,
					
					bcc_to, bcc_to_m,

					Comment,

					Disabled
				) 
				VALUES 
				(
					?,?,
					
					?,?,
					?,?,
					?,?,
					?,?,

					?,?,
					?,?,
					?,?,
					?,?,
					?,?,
					?,?,

					?,?,
					?,?,
					?,?,
					?,?,

					?,?,
					?,?,

					?,?,
					?,?,

					?,?,
					?,?,
					?,?,
					?,?,
					?,?,

					?,?,
					?,?,
					?,?,
					?,?,
					
					?,?,

					?,

					1
				)"
		);

		if (!$stmt) {
			print_r( $db->errorInfo() );
		}

		$res = $stmt->execute($dbinfo);
		if ($res) {
header('Location: '.$_SERVER['REQUEST_URI']); 
		} 
	}
}


#IF POST CONTIENE DELETE	
elseif ($_POST['frmaction'] == "delete")  {
				
	 if (isset($_POST['id'])) {

		$db->beginTransaction();
		$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}amavis_rules WHERE ID = ".$db->quote($_POST['id']));	//elimina la policy	
				
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
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}amavis_rules SET Disabled = 0 WHERE ID = ".$db->quote($_POST['id']));
		}
		ELSE {
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}amavis_rules SET Disabled = 1 WHERE ID = ".$db->quote($_POST['id']));
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
	<p class="pageheader">Amavis Rule List</p>

	<?php include_once("includes/sorttable.php");?>
	
	<table id="mainresults" class="results" style="width: 75%;">
		<tr class="resultstitle">
			<td class="textcenter" onclick="sortTable(0)">ID</td>
			<td class="textcenter" onclick="sortTable(1)">Policy</td>
			<td class="textcenter" onclick="sortTable(2)">Name</td>
			<td class="textcenter" onclick="sortTable(3)">Status</td>
			<td class="textcenter">Details</td>
			<td class="textcenter">Delete</td>
		</tr>
<?php
		$sql = "
				SELECT 
					${DB_TABLE_PREFIX}amavis_rules.ID, ${DB_TABLE_PREFIX}amavis_rules.Name, ${DB_TABLE_PREFIX}amavis_rules.Disabled,
					${DB_TABLE_PREFIX}policies.Name AS PolicyName

				FROM 
					${DB_TABLE_PREFIX}amavis_rules, ${DB_TABLE_PREFIX}policies

				WHERE
					${DB_TABLE_PREFIX}policies.ID = ${DB_TABLE_PREFIX}amavis_rules.PolicyID

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
					<form method="post" action="amavis-main.php">
						<div>
							<input type="hidden" name="frmaction" value="change" />
							<input type="hidden" name="id" value="<?php echo $row->id ?>" />
							<input type="hidden" name="status" value="<?php echo $row->disabled ?>" />
						</div>
						<input type="submit" class="<?php echo $row->disabled ? 'buttondisabled' : 'buttonenabled' ?>" value="<?php echo $row->disabled ? 'Disabled' : 'Enabled' ?>" />
					</form>					
				</td>
				<td class="textcenter">
					<form method="post" action="amavis-change.php">
						<div>
							<input type="hidden" name="frmaction" value="change" />
							<input type="hidden" name="amavis_id" value="<?php echo $row->id ?>" />
						</div>
						<input type="submit" class="button" value="Edit" />
					</form>					
				</td>
				<td align="center">
					<form method="post" action="amavis-main.php">
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
	<form method="post" action="amavis-main.php">
		<div>
			<input type="hidden" name="frmaction" value="add" />
		</div>
		<table class="entry">
		<caption>Quick Add</caption>
			<tr>
				<td class="entrytitle">Name</td>
				<td><input type="text" name="amavis_name" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Link to policy</td>
				<td>
					<select name="amavis_policyid">
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
				<td colspan="2" class="textcenter" style="border-bottom: 1px dashed black;">Bypass Checks</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Virus
					<?php tooltip('amavis_bypass_virus_checks'); ?>
				</td>
				<td>
					<select name="amavis_bypass_virus_checks">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Yes</option>
						<option value="2">No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Banned File
					<?php tooltip('amavis_bypass_banned_checks'); ?>
				</td>
				<td>
					<select name="amavis_bypass_banned_checks">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Yes</option>
						<option value="2">No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Spam
					<?php tooltip('amavis_bypass_spam_checks'); ?>
				</td>
				<td>
					<select name="amavis_bypass_spam_checks">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Yes</option>
						<option value="2">No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Header
					<?php tooltip('amavis_bypass_header_checks'); ?>
				</td>
				<td>
					<select name="amavis_bypass_header_checks">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Yes</option>
						<option value="2">No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="textcenter" style="border-bottom: 1px dashed black;">Anti-spam Settings</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Tag Level
					<?php tooltip('amavis_spam_tag_level'); ?>
				</td>
				<td>
					<select name="amavis_spam_tag_level_mode" id="amavis_spam_tag_level_mode"
						onchange="
							var myobjs = document.getElementById('amavis_spam_tag_level_mode');
							var myobji = document.getElementById('amavis_spam_tag_level');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '0.0';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_spam_tag_level" size="6" id="amavis_spam_tag_level" disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Tag2 Level
					<?php tooltip('amavis_spam_tag2_level'); ?>
				</td>
				<td>
					<select name="amavis_spam_tag2_level_mode" id="amavis_spam_tag2_level_mode"
						onchange="
							var myobjs = document.getElementById('amavis_spam_tag2_level_mode');
							var myobji = document.getElementById('amavis_spam_tag2_level');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '5.0';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_spam_tag2_level" size="6" id="amavis_spam_tag2_level" disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Tag3 Level
					<?php tooltip('amavis_spam_tag3_level'); ?>
				</td>
				<td>
					<select name="amavis_spam_tag3_level_mode" id="amavis_spam_tag3_level_mode"
						onchange="
							var myobjs = document.getElementById('amavis_spam_tag3_level_mode');
							var myobji = document.getElementById('amavis_spam_tag3_level');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '7.5';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_spam_tag3_level" size="6" id="amavis_spam_tag3_level" disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Kill Level
					<?php tooltip('amavis_spam_kill_level'); ?>
				</td>
				<td>
					<select name="amavis_spam_kill_level_mode" id="amavis_spam_kill_level_mode"
						onchange="
							var myobjs = document.getElementById('amavis_spam_kill_level_mode');
							var myobji = document.getElementById('amavis_spam_kill_level');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '7.5';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_spam_kill_level" size="6" id="amavis_spam_kill_level" disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					DSN Cutoff Level
					<?php tooltip('amavis_spam_dsn_cutoff_level'); ?>
				</td>
				<td>
					<select name="amavis_spam_dsn_cutoff_level_mode" id="amavis_spam_dsn_cutoff_level_mode"
						onchange="
							var myobjs = document.getElementById('amavis_spam_dsn_cutoff_level_mode');
							var myobji = document.getElementById('amavis_spam_dsn_cutoff_level');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '7.5';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_spam_dsn_cutoff_level" size="6" id="amavis_spam_dsn_cutoff_level" disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Quarantine Cutoff Level
					<?php tooltip('amavis_spam_quarantine_cutoff_level'); ?>
				</td>
				<td>
					<select name="amavis_spam_quarantine_cutoff_level_mode" id="amavis_spam_quarantine_cutoff_level_mode"
						onchange="
							var myobjs = document.getElementById('amavis_spam_quarantine_cutoff_level_mode');
							var myobji = document.getElementById('amavis_spam_quarantine_cutoff_level');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '15.0';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_spam_quarantine_cutoff_level" size="6" id="amavis_spam_quarantine_cutoff_level" 
							disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Modify Subject
					<?php tooltip('amavis_spam_modifies_subject'); ?>
				</td>
				<td>
					<select name="amavis_spam_modifies_subject">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Yes</option>
						<option value="2">No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Tag Subject
					<?php tooltip('amavis_spam_tag_subject'); ?>
				</td>
				<td>
					<select name="amavis_spam_tag_subject_mode" id="amavis_spam_tag_subject_mode"
						onchange="
							var myobjs = document.getElementById('amavis_spam_tag_subject_mode');
							var myobji = document.getElementById('amavis_spam_tag_subject');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_spam_tag_subject" id="amavis_spam_tag_subject" 
							disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Tag2 Subject
					<?php tooltip('amavis_spam_tag2_subject'); ?>
				</td>
				<td>
					<select name="amavis_spam_tag2_subject_mode" id="amavis_spam_tag2_subject_mode"
						onchange="
							var myobjs = document.getElementById('amavis_spam_tag2_subject_mode');
							var myobji = document.getElementById('amavis_spam_tag2_subject');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_spam_tag2_subject" id="amavis_spam_tag2_subject" 
							disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Tag3 Subject
					<?php tooltip('amavis_spam_tag3_subject'); ?>
				</td>
				<td>
					<select name="amavis_spam_tag3_subject_mode" id="amavis_spam_tag3_subject_mode"
						onchange="
							var myobjs = document.getElementById('amavis_spam_tag3_subject_mode');
							var myobji = document.getElementById('amavis_spam_tag3_subject');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_spam_tag3_subject" id="amavis_spam_tag3_subject" 
							disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td colspan="2" class="textcenter" style="border-bottom: 1px dashed black;">General Checks</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Max Message Size (in Kbyte)
					<?php tooltip('amavis_max_message_size'); ?>
				</td>
				<td>
					<select name="amavis_max_message_size_mode" id="amavis_max_message_size_mode"
						onchange="
							var myobjs = document.getElementById('amavis_max_message_size_mode');
							var myobji = document.getElementById('amavis_max_message_size');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_max_message_size" id="amavis_max_message_size" 
							disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle texttop">
					Banned Files
					<?php tooltip('amavis_banned_files'); ?>
				</td>
				<td class="texttop">
					<select name="amavis_banned_files_mode" id="amavis_banned_files_mode"
						onchange="
							var myobjs = document.getElementById('amavis_banned_files_mode');
							var myobji = document.getElementById('amavis_banned_files');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Merge</option>
						<option value="2">Override</option>
					</select>
					<br />
					<textarea name="amavis_banned_files" id="amavis_banned_files" disabled="disabled" cols="40" rows="5">n/a</textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="textcenter" style="border-bottom: 1px dashed black;">Whitelist &amp; Blacklist</td>
			</tr>
			<tr>
				<td class="entrytitle texttop">
					Sender Whitelist
					<?php tooltip('amavis_sender_whitelist'); ?>
				</td>
				<td class="texttop">
					<select name="amavis_sender_whitelist_mode" id="amavis_sender_whitelist_mode"
						onchange="
							var myobjs = document.getElementById('amavis_sender_whitelist_mode');
							var myobji = document.getElementById('amavis_sender_whitelist');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Merge</option>
						<option value="2">Override</option>
					</select>
					<br />
					<textarea name="amavis_sender_whitelist" id="amavis_sender_whitelist" disabled="disabled" cols="40" rows="5">n/a</textarea>
				</td>
			</tr>
			<tr>
				<td class="entrytitle texttop">
					Sender Blacklist
					<?php tooltip('amavis_sender_blacklist'); ?>
				</td>
				<td class="texttop">
					<select name="amavis_sender_blacklist_mode" id="amavis_sender_blacklist_mode"
						onchange="
							var myobjs = document.getElementById('amavis_sender_blacklist_mode');
							var myobji = document.getElementById('amavis_sender_blacklist');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="1">Merge</option>
						<option value="2">Override</option>
					</select>
					<br />
					<textarea name="amavis_sender_blacklist" id="amavis_sender_blacklist" disabled="disabled" cols="40" rows="5">n/a</textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="textcenter" style="border-bottom: 1px dashed black;">Admin Notifications</td>
			</tr>
			<tr>
				<td class="entrytitle">
					New Virus
					<?php tooltip('amavis_notify_admin_newvirus'); ?>
				</td>
				<td>
					<select name="amavis_notify_admin_newvirus_mode" id="amavis_notify_admin_newvirus_mode"
						onchange="
							var myobjs = document.getElementById('amavis_notify_admin_newvirus_mode');
							var myobji = document.getElementById('amavis_notify_admin_newvirus');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_notify_admin_newvirus" id="amavis_notify_admin_newvirus" 
							disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Virus
					<?php tooltip('amavis_notify_admin_virus'); ?>
				</td>
				<td>
					<select name="amavis_notify_admin_virus_mode" id="amavis_notify_admin_virus_mode"
						onchange="
							var myobjs = document.getElementById('amavis_notify_admin_virus_mode');
							var myobji = document.getElementById('amavis_notify_admin_virus');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_notify_admin_virus" id="amavis_notify_admin_virus" 
							disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Spam
					<?php tooltip('amavis_notify_admin_spam'); ?>
				</td>
				<td>
					<select name="amavis_notify_admin_spam_mode" id="amavis_notify_admin_spam_mode"
						onchange="
							var myobjs = document.getElementById('amavis_notify_admin_spam_mode');
							var myobji = document.getElementById('amavis_notify_admin_spam');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_notify_admin_spam" id="amavis_notify_admin_spam" 
							disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Banned File
					<?php tooltip('amavis_notify_admin_banned_file'); ?>
				</td>
				<td>
					<select name="amavis_notify_admin_banned_file_mode" id="amavis_notify_admin_banned_file_mode"
						onchange="
							var myobjs = document.getElementById('amavis_notify_admin_banned_file_mode');
							var myobji = document.getElementById('amavis_notify_admin_banned_file');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_notify_admin_banned_file" id="amavis_notify_admin_banned_file" 
							disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Bad Header
					<?php tooltip('amavis_notify_admin_bad_header'); ?>
				</td>
				<td>
					<select name="amavis_notify_admin_bad_header_mode" id="amavis_notify_admin_bad_header_mode"
						onchange="
							var myobjs = document.getElementById('amavis_notify_admin_bad_header_mode');
							var myobji = document.getElementById('amavis_notify_admin_bad_header');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_notify_admin_bad_header" id="amavis_notify_admin_bad_header" 
							disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td colspan="2" class="textcenter" style="border-bottom: 1px dashed black;">Quarantine</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Virus
					<?php tooltip('amavis_quarantine_virus'); ?>
				</td>
				<td>
					<select name="amavis_quarantine_virus_mode" id="amavis_quarantine_virus_mode"
						onchange="
							var myobjs = document.getElementById('amavis_quarantine_virus_mode');
							var myobji = document.getElementById('amavis_quarantine_virus');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_quarantine_virus" id="amavis_quarantine_virus" 
							disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Spam
					<?php tooltip('amavis_quarantine_spam'); ?>
				</td>
				<td>
					<select name="amavis_quarantine_spam_mode" id="amavis_quarantine_spam_mode"
						onchange="
							var myobjs = document.getElementById('amavis_quarantine_spam_mode');
							var myobji = document.getElementById('amavis_quarantine_spam');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_quarantine_spam" id="amavis_quarantine_spam" 
							disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Banned File
					<?php tooltip('amavis_quarantine_banned_file'); ?>
				</td>
				<td>
					<select name="amavis_quarantine_banned_file_mode" id="amavis_quarantine_banned_file_mode"
						onchange="
							var myobjs = document.getElementById('amavis_quarantine_banned_file_mode');
							var myobji = document.getElementById('amavis_quarantine_banned_file');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_quarantine_banned_file" id="amavis_quarantine_banned_file" 
							disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td class="entrytitle">
					Bad Header
					<?php tooltip('amavis_quarantine_bad_header'); ?>
				</td>
				<td>
					<select name="amavis_quarantine_bad_header_mode" id="amavis_quarantine_bad_header_mode"
						onchange="
							var myobjs = document.getElementById('amavis_quarantine_bad_header_mode');
							var myobji = document.getElementById('amavis_quarantine_bad_header');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_quarantine_bad_header" id="amavis_quarantine_bad_header" 
							disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td colspan="2" class="textcenter" style="border-bottom: 1px dashed black;">Interception</td>
			</tr>
			<tr>
				<td class="entrytitle">
					BCC To
					<?php tooltip('amavis_bcc_to'); ?>
				</td>
				<td>
					<select name="amavis_bcc_to_mode" id="amavis_bcc_to_mode"
						onchange="
							var myobjs = document.getElementById('amavis_bcc_to_mode');
							var myobji = document.getElementById('amavis_bcc_to');

							if (myobjs.selectedIndex == 0) {
								myobji.disabled = true;
								myobji.value = 'n/a';
							} else if (myobjs.selectedIndex != 0) {
								myobji.disabled = false;
								myobji.value = '';
							}
					">
						<option value="0" selected="selected">Inherit</option>
						<option value="2">Override</option>
					</select>
					<input type="text" name="amavis_bcc_to" id="amavis_bcc_to" 
							disabled="disabled" value="n/a" />
				</td>
			</tr>
			<tr>
				<td colspan="2" class="textcenter" style="border-bottom: 1px dashed black;">&nbsp;</td>
			</tr>
			<tr>
				<td class="entrytitle texttop">Comment</td>
				<td><textarea name="amavis_comment" cols="40" rows="5"></textarea></td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="submit" />
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
