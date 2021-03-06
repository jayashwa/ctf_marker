<?php
session_start();
	
include 'template/connection.php';
if((mysqli_num_rows(mysqli_query($connection, "SHOW TABLES LIKE 'users'"))==0) || isset($_SESSION['TYPE']) == "A" && isset($_SESSION['USERNAME'])) {
	if(!isset($_GET['option'])){
		header('location:admin.php?option=team');
	}
}else{
	header('location:index.php');
} 



?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimal-ui" />
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link href="https://fonts.googleapis.com/css?family=Iceland|Orbitron" rel="stylesheet"> 
		<link href="css/admin.css" type="text/css" rel="stylesheet" />
	</head>
<body style="background:url('images/bgadmin.png');">
	
	<div id="wrapper">	
		<h1 id="head">Admin Portal</h1>
		<div id="menu">
			<h1>Options</h1>
			<a href="admin.php?option=team"><span>TEAM</span></a>
			<a href="admin.php?option=token"><span>CREATE TOKENS</span></a>
			<a href="admin.php?option=team-members"><span>TEAM MEMBERS</span></a>
			<a href="admin.php?option=options"><span>OPTIONS</span></a>
			<a href="admin.php?option=announce"><span>ANNOUNCE</span></a>
			<a href="admin.php?option=import-secgen"><span>IMPORT SECGEN</span></a>
			<a href="admin.php?option=db-manage"><span>DATABASE MANAGEMENT</span></a>
			<a href="admin.php?option=lockpicking"><span>LOCK PICKING</span></a>
			<a href="admin.php?option=verify"><span>SYSTEM STATUS</span></a>
			<a href="template/logout.php"><span>LOGOUT</span></a>
		</div>
		<div id="content">
			<!-- <h1>Manage Flags and Options</h1> -->
			<?php
			require 'class/Validator.php';
			require 'class/createtables.php';
			if(isset($_GET['option'])){
				$command = $_GET['option'];
				include 'template/connection.php';
				switch($command){
					case "announce":
							?>
							<h1>Announce</h1>
							<form method="post" action="admin.php?option=announce">
								<textarea rows="10" placeholder="Enter your message for announcement" name="team_announce"></textarea>
								<input id="ann_submit" type="submit" value="Send" name="a_send" <?php if(!Validator::AdminEditPermission()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
							</form>
							<?php
							if(isset($_POST['a_send'])){
								if(!empty($_POST['team_announce'])){
									$ann_post = Validator::filterString($_POST['team_announce']);
									$announce_insert = mysqli_query($connection, "UPDATE options SET value='$ann_post' WHERE name='ANNOUNCE'");
									if($announce_insert){
										$ann_updater = mysqli_query($connection, "UPDATE updater SET ANNOUNCE='1'");
										if($ann_updater){
											echo "<p style='color:green;margin-left:10%;'>Announcement Successful</p>";
										}else{
											echo "<p style='color:maroon;margin-left:10%;'>Failed to set updater</p>";
										}
									}else{
										echo "<p style='color:maroon;margin-left:10%;'>Failed to Announce</p>";
									}
		
								}else{
									echo "<p style='color:maroon;margin-left:10%;'>Textarea is empty</p>";
								}
							}
						break;
						
					case "team":
							?>
							<h1>Team</h1>
							<table>
							  <tr class="table_heading">
							    <th>Team Code</th>
							    <th>Team Name</th> 
							    <th>Logo</th>
							  </tr>
							<?php
							$team_list = mysqli_query($connection, "SELECT * FROM team");
							if($team_list){
							while($team_list_row = mysqli_fetch_assoc($team_list)){
								?>
								  <tr>
								    <td><?php echo $team_list_row['TEAM'];?></td>
								    <td><?php echo $team_list_row['TEAMNAME'];?></td> 
								    <td><?php echo $team_list_row['LOGO'];?></td>
								  </tr>
								<?php
							}
							}
							echo "</table>";?>
							<div class="token-div-add">
							<form method="post" action="admin.php?option=team">
								<table style="width:100%;">
									<tr>
										    <th>
										    	<h1>Create Team</h1>
											</th>
										    <th>
												<input type="text" name="team-create" placeholder="Team Name"/>
											</th> 
										    <th id="team-submit-btn">
										    	<input type="submit" name="team-create-submit" value="Create" <?php if(!Validator::AdminEditPermission()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
										    </th> 
									</tr>
								</table>
							</form>
							</div>
							<?php
							if(isset($_POST['team-create-submit'])){
								if(!empty($_POST['team-create'])){
									$team_create = $_POST['team-create'];
									if(strlen($team_create) > 0  && strlen($team_create) <=15){
										$check = mysqli_query($connection, "SELECT TEAM FROM team");
										if($check){
											$team_create_count = mysqli_num_rows(mysqli_query($connection, "SELECT TEAM FROM team")) + 1;
											$team_create_res = mysqli_query($connection, "INSERT INTO team (TEAM, TEAMNAME) VALUES ('$team_create_count','$team_create')");
											if($team_create_res){
												$team_scoreboard_add = mysqli_query($connection, "INSERT INTO scoreboard (TEAM, TEAMNAME) VALUES ('$team_create_count','$team_create')");
												if($team_scoreboard_add){
													echo "<p style='color:green;margin-left:10%;'>Team Creation Successful</p>";
												}else{
													echo "<p style='color:red;margin-left:10%;'>Failed to create team 3</p>";
												}
											}else{
												echo "<p style='color:red;margin-left:10%;'>Failed to create team 2</p>";
											}											
										}else{
											echo "<p style='color:red;margin-left:10%;'>Failed to Update</p>";
										}
									}else{
										echo "<p style='color:red;margin-left:10%;'>Team name should be between 0 - 15 characters long</p>";
									}
								}else{
									echo "<p style='color:red;margin-left:10%;'>Team name is empty</p>";
								}
							}
						break;
						
					case "team-members":
							?>
							<h1>Team Members</h1>
							<div id="team1-div">
								<table>
									<tr class="table_heading">
									    <th>Team Code</th>
									    <th>Team Members</th> 
								    </tr>
								    <tr> 		    
								    <?php

										$team_members_res = mysqli_query($connection, "SELECT DISTINCT TEAM FROM users WHERE TEAM > 0 ORDER BY TEAM ASC");
										if($team_members_res){
											while($team_members_row = mysqli_fetch_assoc($team_members_res)){
												$team_mem_code = $team_members_row['TEAM'];
												$team_members_list = mysqli_query($connection, "SELECT USERNAME FROM users WHERE TEAM='$team_mem_code'");
												$count_members = mysqli_num_rows($team_members_list);	
												$team_name_getter = mysqli_query($connection, "SELECT TEAMNAME FROM team WHERE TEAM='$team_mem_code'");
												while($team_name_getter_row = mysqli_fetch_assoc($team_name_getter)){									
												echo "<td rowspan='$count_members'>".$team_name_getter_row['TEAMNAME']."</td>";
												}
												while($team_members_list_row = mysqli_fetch_assoc($team_members_list)){
													$username = $team_members_list_row['USERNAME'];
													if(empty($username)){
														echo "<td>Not Registered</td></tr>";
													}else{
														echo "<td>".$username."</td></tr>";
													}
													
												}
											}
										}else{
											echo "<td>No Data Found</td>";
											echo "<td>No Data Found</td>";											
										}
								    ?>
										
								</table>
							</div>
							<?php
						break;
						
					case "token":
						?>	
						<h1>Generate Token</h1>
						<div class="token-div-add">
							<form method="post" action="admin.php?option=token">
							<table style="width:100%;">
								<tr>
									    <th>
									    	<input type="hidden" value="token" name="option" />
											<select name="token_gen_team">
												<?php
												$token_team_list = mysqli_query($connection, "SELECT TEAM, TEAMNAME FROM team");
												while($token_team_list_row = mysqli_fetch_assoc($token_team_list)){
													$token_team = $token_team_list_row['TEAM'];
													$token_team_name = $token_team_list_row['TEAMNAME'];
													echo "<option value='$token_team'>$token_team_name</option>";
												}
												?>
											</select>
										</th>
									    <th>
											<input type="number" name="token_gen_num" placeholder="Number of Token" class="token-input-1" maxlength="2"/>
										</th> 
									    <th>
									    	<input type="submit" name="token_gen_submit" value="Generate" class="token-input-2" <?php if(!Validator::AdminEditPermission()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
									    </th> 
								</tr>
							</table>
							</form>
							<form method="post" action="data_pdf.php">
								<table style="width:100%;">
									<tr>
										    <th>
										    	<h1>Export Token PDF</h1>
											</th>
										    <th>
												<select name="token_gen_team">
													<?php
													$token_team_list = mysqli_query($connection, "SELECT TEAM, TEAMNAME FROM team");
													while($token_team_list_row = mysqli_fetch_assoc($token_team_list)){
														$token_team = $token_team_list_row['TEAM'];
														$token_team_name = $token_team_list_row['TEAMNAME'];
														echo "<option value='$token_team'>$token_team_name</option>";
													}
													?>
												</select>
											</th> 
										    <th>
										    	<input type="submit" name="token_pdf" value="Export Tokens" class="token-input-2"/>
										    </th> 
									</tr>
								</table>
							</form>
							<?php
							if(isset($_POST['token_gen_submit'])){
								if(isset($_POST['option']) && isset($_POST['token_gen_team']) && isset($_POST['token_gen_num'])){
									$token_counter = $_POST['token_gen_num'];
									$token_teamd = $_POST['token_gen_team'];
									if($token_counter > 0 && $token_counter < 10){
										for($int = 0; $int <$token_counter; $int++){
											$randomKey = randomToken();
											$h = md5($randomKey);
											$insertToken = mysqli_query($connection, "INSERT INTO users (TEAM, TYPE, T_TYPE, TOKEN, TOKEN_HASH, TOKEN_ACT) VALUES ('$token_teamd','N','T','$randomKey','$h',0)");
											if($insertToken){
												
											}else{
												echo "<p style='color:maroon;'>Failed to Insert</p>";
											}
										}
									}else{
										echo "<p style='color:maroon;'>Team should be between 1-10</p>";
									}
								}
								
							}	
						
							?>
						</div>
						<!---Generate Logins---->
						<h1>Generate Logins</h1>
						<div class="token-div-add">
							<form method="post" action="admin.php?option=token">
							<table style="width:100%;">
								<tr>
									    <th>
									    	<input type="hidden" value="login" name="option" />
											<select name="login_gen_team">
												<?php
												$token_team_list = mysqli_query($connection, "SELECT TEAM, TEAMNAME FROM team");
												while($token_team_list_row = mysqli_fetch_assoc($token_team_list)){
													$token_team = $token_team_list_row['TEAM'];
													$token_team_name = $token_team_list_row['TEAMNAME'];
													echo "<option value='$token_team'>$token_team_name</option>";
												}
												?>
											</select>
										</th>
									    <th>
											<input type="number" style="text-align:center;" name="login_gen_num" placeholder="Number of Logins" id="login-input-1" maxlength="2"/>
										</th> 
									    <th>
									    	<input type="submit" name="login_gen_submit" value="Generate" class="token-input-2" <?php if(!Validator::AdminEditPermission()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
									    </th> 
								</tr>
							</table>
							</form>
							<form method="post" action="data_pdf.php">
								<table style="width:100%;">
									<tr>
										    <th>
										    	<h1>Export Login PDF</h1>
											</th>
										    <th>
												<select name="login_gen_team">
													<?php
													$token_team_list = mysqli_query($connection, "SELECT TEAM, TEAMNAME FROM team");
													while($token_team_list_row = mysqli_fetch_assoc($token_team_list)){
														$token_team = $token_team_list_row['TEAM'];
														$token_team_name = $token_team_list_row['TEAMNAME'];
														echo "<option value='$token_team'>$token_team_name</option>";
													}
													?>
												</select>
											</th> 
										    <th>
										    	<input type="submit" name="login_pdf" value="Export Logins" class="token-input-2"/>
										    </th> 
									</tr>
								</table>
							</form>
							<?php
							if(isset($_POST['login_gen_submit'])){
								if(isset($_POST['option']) && isset($_POST['login_gen_team']) && isset($_POST['login_gen_num'])){
									$login_counter = $_POST['login_gen_num'];
									$login_teamd = $_POST['login_gen_team'];
									if($login_counter > 0 && $login_counter < 10){									
										$ucount = 0;			
										foreach(file('support/usernames.txt') as $un){
											$usrname = trim($un);
											if($ucount == $login_counter){
												break;
											}else{
												$chkusr = mysqli_num_rows(mysqli_query($connection, "SELECT USERNAME FROM users WHERE USERNAME='$usrname'"));
												if($chkusr == 0){
													$randomKey = randomToken();
													$keyHash = md5($randomKey);
													$hash = md5($randomKey . "CTF");
													$insertToken = mysqli_query($connection, "INSERT INTO users (USERNAME, PASSWORD, TEAM, TYPE, T_TYPE, TOKEN, TOKEN_HASH, TOKEN_ACT) VALUES ('$usrname','$hash','$login_teamd','N','L','$randomKey','$keyHash','1')");
													if($insertToken){
														$updater = mysqli_query($connection, "INSERT INTO updater (TEAM, USERNAME) VALUES ('$login_teamd','$usrname')");
														if($updater){
															
														}else{
															echo "<p style='color:maroon;'>Failed to Insert</p>";															
														}	
													}else{
														echo "<p style='color:maroon;'>Failed to Insert</p>";
													}	
													$ucount++;
												}	
											}
										}	
									}else{
										echo "<p style='color:maroon;'>Team should be between 1-10</p>";
									}
								}
								
							}	
						
							?>
						</div>
						<!--Generate Login End--->						
						<h1>Available & Registered Token</h1>
						<div id="token-div">
								<table>
									<tr class="table_heading">
									    <th>Team Code</th>
									    <th>Username</th> 
									    <th>Token</th> 
								    </tr>
								    <tr> 		    
								    <?php
								    $team_members_res = mysqli_query($connection, "SELECT DISTINCT TEAM FROM users WHERE TEAM > 0 ORDER BY TEAM ASC");
									if($team_members_res){
										while($team_members_row = mysqli_fetch_assoc($team_members_res)){
											$team_mem_code = $team_members_row['TEAM'];
											$team_members_list = mysqli_query($connection, "SELECT USERNAME,TOKEN,TOKEN_ACT FROM users WHERE TEAM='$team_mem_code' AND TYPE='N'");
											$count_members = mysqli_num_rows($team_members_list);
											$team_name_getter_new = mysqli_query($connection, "SELECT TEAMNAME FROM team WHERE TEAM='$team_mem_code'");
											while($team_name_getter_row = mysqli_fetch_assoc($team_name_getter_new)){									
												echo "<td rowspan='$count_members'>".$team_name_getter_row['TEAMNAME']."</td>";
											}
											while($team_members_list_row = mysqli_fetch_assoc($team_members_list)){
												$username = $team_members_list_row['USERNAME'];
												$token = $team_members_list_row['TOKEN'];
												$token_stat = $team_members_list_row['TOKEN_ACT'];
												
												if($token_stat == 1){
													echo "<td style='background:#5e842e;color:black;'>".$username."</td>";
													echo "<td style='background:#5e842e;color:black;'>".$token."</td></tr>";
												}else{
													echo "<td style='background:#ff9999;color:black;'>".$username."</td>";
													echo "<td style='background:#ff9999;color:black;'>".$token."</td></tr>";
												}
											}
										}
									}
								    ?>
										
								</table>
							</div>
							<?php
						break; 
						
				case "options":
				?>
						<h1>Event Options</h1>
						<div class="token-div-add">
						<table style="width:100%;">
							<tr>
								    <th style="width:25%;">
								    	<h1>Homepage Date</h1>
									</th>
								    <th style="width:50%;">
										<input type="datetime-local" name="homepage-date" id="home_date"/>
										<h3 id="home_status">
											<?php
											$q1 = mysqli_query($connection, "SELECT value FROM options WHERE name='HOME_TIME'");
											if($q1){
												foreach(mysqli_fetch_assoc($q1) as $val){
													echo 'Time Set : <b style="color:green;">'.$val;
												}
											}else{
												echo '<b style="color:red;">'."Table Not Initialised";
											}
											
											?>
											</b>
										</h3>
									</th> 
								    <th style="width:25%;">
								    	<button id="home_date_submit" onclick="Update.homedate();" <?php if(!Validator::AdminEditPermission()){echo "disabled "; echo Validator::DisabledCSS();}?>>Update</button>
								    </th> 
							</tr>
						</table>
						<table style="width:100%;">
							<tr class="equalTable">
								    <th style="width:25%;">
								    	<h1>CTF Game End Time</h1>
									</th>
								    <th style="width:50%;">
										<input type="datetime-local" name="ctf-date" id="ctf_date" style="width:100%;"/>
										<h3 id="ctf_status">
											<?php
											$q1 = mysqli_query($connection, "SELECT value FROM options WHERE name='END_TIME'");
											if($q1){												
												foreach(mysqli_fetch_assoc($q1) as $val){
													echo 'Time Set : <b style="color:green;">'.$val;
												}
											}else{
												echo '<b style="color:red;">'."Table Not Initialised";
											}												
											?>
											</b>
										</h3>
									</th> 
								    <th style="width:25%;">
								    	<button onclick="Update.ctf();" <?php if(!Validator::AdminEditPermission()){echo "disabled "; echo Validator::DisabledCSS();}?>>Update</button>
								    </th> 
							</tr>
						</table>
						<br>
						<h1>Other Options</h1>
						<br>	
						<table style="width:100%;">
							<tr class="equalTable">
								    <th>
								    	<h1>Allow Edits</h1>
									</th>
								    <th>
										<h3 id="allow_status">
											<?php
											$q1 = mysqli_query($connection, "SELECT value FROM options WHERE name='ADMINEDIT'");
											if($q1){												
												foreach(mysqli_fetch_assoc($q1) as $val){
													if($val == "ALLOW"){
														echo "<b style='color:green;' id='adminedit'>$val</b>";
													}else{
														echo "<b style='color:red;' id='adminedit'>$val</b>";
													}
												}
											}else{
												echo "<b style='color:red;'>Table Not Initialised</b>";
											}												
											?>
										</h3>
									</th> 
								    <th>
								    	<button onclick="Update.edits();">Update</button>
								    </th> 
							</tr>
						</table>												
						<table style="width:100%;">
							<tr class="equalTable">
								    <th>
								    	<h1>Allow Users Login</h1>
									</th>
								    <th>
										<h3 id="allow_status">
											<?php
											$q1 = mysqli_query($connection, "SELECT value FROM options WHERE name='LOGIN'");
											if($q1){												
												foreach(mysqli_fetch_assoc($q1) as $val){
													if($val == "ALLOW"){
														echo "<b style='color:green;' id='lstat'>$val</b>";
													}else{
														echo "<b style='color:red;' id='lstat'>$val</b>";
													}
												}
											}else{
												echo "<b style='color:red;'>Table Not Initialised</b>";
											}												
											?>
										</h3>
									</th> 
								    <th>
								    	<button onclick="Update.login();" <?php if(!Validator::AdminEditPermission()){echo "disabled "; echo Validator::DisabledCSS();}?>>Update</button>
								    </th> 
							</tr>
						</table>
						<table style="width:100%;">
							<tr class="equalTable">
								    <th>
								    	<h1>View Homepage Scoreboard</h1>
									</th>
								    <th>
										<h3 id="allow_status">
											<?php
											$q1 = mysqli_query($connection, "SELECT value FROM options WHERE name='SCOREBOARD'");
											if($q1){												
												foreach(mysqli_fetch_assoc($q1) as $val){
													if($val == "ALLOW"){
														echo "<b style='color:green;' id='hscore'>$val</b>";
													}else{
														echo "<b style='color:red;' id='hscore'>$val</b>";
													}
												}
											}else{
												echo "<b style='color:red;'>Table Not Initialised</b>";
											}												
											?>
										</h3>
									</th> 
								    <th>
								    	<button onclick="Update.homeScore();" <?php if(!Validator::AdminEditPermission()){echo "disabled "; echo Validator::DisabledCSS();}?>>Update</button>
								    </th> 
							</tr>
						</table>											
						<form method="post" action="admin.php?option=options">
							<table style="width:100%;">
								<tr>
									    <th>
									    	<h1>Add Admin User</h1>
										</th>
									    <th id="pass_chn">
									    	<input type="text" name="username" placeholder="Username"/>
											<input type="password" name="pass1" placeholder="Password"/>
											<input type="password" name="pass2" placeholder="Re-Enter Password"/>
										</th> 
									    <th>
									    	<input type="submit" name="admin-add-submit" value="Add" class="token-input-2" <?php if(!Validator::AdminEditPermission()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
									    </th> 
								</tr>
							</table>
						</form>							
						<form method="post" action="admin.php?option=options">
							<table style="width:100%;">
								<tr>
									    <th>
									    	<h1>Admin Password Change</h1>
										</th>
									    <th id="pass_chn">
									    	<select name="admin_username">
												<?php
												$token_team_list = mysqli_query($connection, "SELECT USERNAME FROM users WHERE TYPE='A'");
												while($token_team_list_row = mysqli_fetch_assoc($token_team_list)){
													$token_team = $token_team_list_row['USERNAME'];
													echo "<option value='$token_team'>$token_team</option>";
												}
												?>
											</select>
											<input type="password" name="pass1" placeholder="Password"/>
											<input type="password" name="pass2" placeholder="Re-Enter Password"/>
										</th> 
									    <th>
									    	<input type="submit" name="admin-pass-submit" value="Update" class="token-input-2" <?php if(!Validator::AdminEditPermission()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
									    </th> 
								</tr>
							</table>
						</form>									
						</div>
								
				<?php 											
						if(isset($_POST['admin-pass-submit'])){
							$username = htmlspecialchars(htmlentities(trim(filter_var($_POST['admin_username'],FILTER_SANITIZE_STRING))));
							$password = htmlspecialchars(htmlentities(trim(filter_var($_POST['pass1'],FILTER_SANITIZE_STRING))));
							$password1 = htmlspecialchars(htmlentities(trim(filter_var($_POST['pass2'],FILTER_SANITIZE_STRING))));
							if(strlen($username) >= 5 && strlen($username) <= 10){
								if($password == $password1){
									$sql = mysqli_num_rows(mysqli_query($connection, "SELECT * FROM users WHERE USERNAME='$username'"));
									if($sql == 1){
										$pass = md5($password1."CTF");
										$update = mysqli_query($connection, "UPDATE users SET PASSWORD='$pass' WHERE USERNAME='$username'");
										if($update){
											echo "<p style='color:green;margin-left:10%;'>Password Updated Successfully</p>";
										}else{
											echo "<p style='color:maroon;margin-left:10%;'>Password Update Failed</p>";
										}
									}else{
										echo "<p style='color:maroon;margin-left:10%;'>Username doesn't exist</p>";
									}
								}else{
									echo "<p style='color:maroon;margin-left:10%;'>Password doesn't match</p>";
								}
							}else{
								echo "<p style='color:maroon;margin-left:10%;'>Username should be between 5 to 10 characters</p>";
							}
							
						}
	
						if(isset($_POST['admin-add-submit'])){
							$username = htmlspecialchars(htmlentities(trim(filter_var($_POST['username'],FILTER_SANITIZE_STRING))));
							$password = htmlspecialchars(htmlentities(trim(filter_var($_POST['pass1'],FILTER_SANITIZE_STRING))));
							$password1 = htmlspecialchars(htmlentities(trim(filter_var($_POST['pass2'],FILTER_SANITIZE_STRING))));
							if(strlen($username) >= 5 && strlen($username) <= 10){
								if($password == $password1){
									$sql = mysqli_num_rows(mysqli_query($connection, "SELECT * FROM users WHERE USERNAME='$username'"));
									if($sql == 0){
										$pass = md5($password1."CTF");
										$update = mysqli_query($connection, "INSERT INTO users (USERNAME, PASSWORD, TEAM, TYPE, TOKEN, TOKEN_HASH, TOKEN_ACT) 
										VALUES ('$username','$pass','0','A','A1B2C3D4','9cb4a9b49df14f3ee3c177f0f74ad443','1')");
										if($update){
											echo "<p style='color:green;margin-left:10%;'>Admin User Added Successfully</p>";
										}else{
											echo "<p style='color:maroon;margin-left:10%;'>Admin User Failed</p>";
										}
									}else{
										echo "<p style='color:maroon;margin-left:10%;'>Username already exist</p>";
									}
								}else{
									echo "<p style='color:maroon;margin-left:10%;'>Password doesn't match</p>";
								}
							}else{
								echo "<p style='color:maroon;margin-left:10%;'>Username should be between 5 to 10 characters</p>";
							}
							
						}						
					break; 	
					
				case "import-secgen":
					?>
						<h1>SecGen Flag Import</h1>
						<div class="token-div-add">
							<form method="post" action="admin.php?option=import-secgen" enctype="multipart/form-data">
							<table style="width:100%;">
								<tr>
									    <th>
									    	<input type="hidden" value="token" name="option" />
											<select name="imp_team">
												<?php
												$token_team_list = mysqli_query($connection, "SELECT TEAM, TEAMNAME FROM team");
												while($token_team_list_row = mysqli_fetch_assoc($token_team_list)){
													$token_team = $token_team_list_row['TEAM'];
													$token_team_name = $token_team_list_row['TEAMNAME'];
													echo "<option value='$token_team'>$token_team_name</option>";
												}
												?>
											</select>
										</th>
									    <th id="browse">
											<input type="file" name="imp_file" placeholder="Full File Path" class="token-input-1"/>
										</th> 
									    <th>
									    	<input type="submit" name="imp_submit" value="Import" class="token-input-2" <?php if(!Validator::AdminEditPermission()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
									    </th> 
								</tr>
							</table>
							</form>
							<form method="post" action="admin.php?option=import-secgen" enctype="multipart/form-data">
							<table style="width:100%;">
								<tr>
									    <th>
											<h1>Same Flag For All Team</h1>
										</th>
									    <th id="browse">
											<input type="file" name="imp_file" placeholder="Full File Path" class="token-input-1"/>
										</th> 
									    <th>
									    	<input type="submit" name="vm_all_submit" value="Import" class="token-input-2" <?php if(!Validator::AdminEditPermission()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
									    </th> 
								</tr>
							</table>
							</form>	
					<?php
					if(isset($_POST['vm_all_submit'])){
						//no validation done yet
						include 'template/connection.php';
						echo "Importing..."."<br><br>";
						$teamQuery = mysqli_query($connection, "SELECT TEAM FROM team");
							while($row = mysqli_fetch_assoc($teamQuery)){
								$team = $row['TEAM'];
								echo "<h1 style='color:orange;'>Team : ".$team."</h1></br>";
								if (isset($_FILES['imp_file']) && ($_FILES['imp_file']['error'] == UPLOAD_ERR_OK)) {
										$xml = simplexml_load_file($_FILES['imp_file']['tmp_name']); 
								
										foreach($xml->system as $system){
											$count = count($system->challenge);
											$q = mysqli_query($connection, "SELECT C_ID FROM secgen WHERE C_NO='$count'");
											$chall = 0;
											foreach($system->challenge as $challenge){
												$chall++;
												$num = 0;		
												foreach(mysqli_fetch_assoc($q) as $cid){
													$secgenflag = mysqli_query($connection,"INSERT INTO secgenflag (TEAM, C_ID, STATUS, VM, IP, FLAG, FLAG_POINTS) VALUES('$team', '$cid', '0', '$system->system_name', 
													'$system->platform', '$challenge->flag','100')");
													if($secgenflag){
														echo "[$system->system_name] Challenge No : $chall [SUCCESS]"."<br>";
														foreach($challenge->hint as $hint){
															$num++;
															$randomKey = strtoupper(md5(bin2hex(openssl_random_pseudo_bytes(16)).time()));
															$hintText = addslashes($hint->hint_text);
															$hint_update = mysqli_query($connection, "INSERT INTO hint (RANDOM, TEAM, SYSTEM_NAME, C_ID, CHALLENGE, HINT_STATUS, HINT_ID, HINT_TYPE, HINT_TEXT) VALUES 
															('$randomKey','$team','$system->system_name','$cid','$chall','0','$hint->hint_id','$hint->hint_type','$hintText')");
															if($hint_update){
																echo "<i style='color:green;margin-left:15%;'>HINT Challenge No : ($chall) Hint No : ($num) [SUCCESS]"."</i><br>";
															}else{
																echo "<p style='color:maroon;margin-left:10%;'>".mysqli_error($connection)."</p>";
															}			
														}
													}else{
														echo "<p style='color:maroon;margin-left:10%;'>".mysqli_error($connection)."/p>";
													}		
								
												}
											}
										}
								}else{
									echo "Error";
								}
								echo "</br></br>";
							}
					  }						

					  if(isset($_POST['imp_submit'])){
					  	//no validation done yet
					  	include 'template/connection.php';
					  	$team = $_POST['imp_team'];
					  	echo "Importing..."."<br><br>";
						if (isset($_FILES['imp_file']) && ($_FILES['imp_file']['error'] == UPLOAD_ERR_OK)) {
  			 					$xml = simplexml_load_file($_FILES['imp_file']['tmp_name']); 

								foreach($xml->system as $system){
									$count = count($system->challenge);
									$q = mysqli_query($connection, "SELECT C_ID FROM secgen WHERE C_NO='$count'");
									$chall = 0;
									foreach($system->challenge as $challenge){
										$chall++;
										$num = 0;		
										foreach(mysqli_fetch_assoc($q) as $cid){
											$secgenflag = mysqli_query($connection,"INSERT INTO secgenflag (TEAM, C_ID, STATUS, VM, IP, FLAG, FLAG_POINTS) VALUES('$team', '$cid', '0', '$system->system_name', 
											'$system->platform', '$challenge->flag','100')");
											if($secgenflag){
												echo "[$system->system_name] Challenge No : $chall [SUCCESS]"."<br>";
												foreach($challenge->hint as $hint){
													$num++;
													$randomKey = strtoupper(md5(bin2hex(openssl_random_pseudo_bytes(16)).time()));
													$hintText = addslashes($hint->hint_text);
													$hint_update = mysqli_query($connection, "INSERT INTO hint (RANDOM, TEAM, SYSTEM_NAME, C_ID, CHALLENGE, HINT_STATUS, HINT_ID, HINT_TYPE, HINT_TEXT) VALUES 
													('$randomKey','$team','$system->system_name','$cid','$chall','0','$hint->hint_id','$hint->hint_type','$hintText')");
													if($hint_update){
														echo "<i style='color:green;margin-left:15%;'>HINT Challenge No : ($chall) Hint No : ($num) [SUCCESS]"."</i><br>";
													}else{
														echo "<p style='color:maroon;margin-left:10%;'>Error ADMIN[101]</p>";
													}			
												}
											}else{
												echo "<p style='color:maroon;margin-left:10%;'>Error ADMIN[101]</p>";
											}		
	
										}
									}
								}
						}else{
							echo "Error";
						}
					  }
					break;	
					
					case "db-manage":
						$table = new Tables();
						?>
						<h1>Database Managemnt</h1>
						<div class="token-div-add">
								<h1>Create Table</h1>
								<table style="width:100%;">							
								<tr>
								    <th>
								    	<h1>CREATE <br>SecGen Hint & Flag</h1>
									</th>
								    <th>
								    	<form method="post" action="admin.php?option=db-manage">
											<input type="submit" name="create_hint" value="CREATE" class="token-input-2" <?php if(!Validator::AdminEditPermission() && DB::checkOptionTablesExists()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
										</form>
									</th> 
								    <th>
								    	<?php
								    	 if(isset($_POST['create_hint'])){
								    	 	$flagSql = "CREATE TABLE `secgenflag` (
															  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
															  `TEAM` int(11) NOT NULL,
															  `C_ID` varchar(6) NOT NULL,
															  `STATUS` int(1) NOT NULL,
															  `VM` varchar(200) NOT NULL,
															  `IP` text NOT NULL,
															  `FLAG` text NOT NULL,
															  `FLAG_POINTS` int(100) NOT NULL
															) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
															
											$hintSql = "CREATE TABLE `hint` (
															  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
															  `RANDOM` text NOT NULL,
															  `TEAM` int(2) NOT NULL,
															  `SYSTEM_NAME` varchar(100) NOT NULL,
															  `C_ID` varchar(10) NOT NULL,
															  `CHALLENGE` int(5) NOT NULL,
															  `HINT_STATUS` int(1) NOT NULL,
															  `HINT_ID` varchar(100) NOT NULL,
															  `HINT_TYPE` varchar(100) NOT NULL,
															  `HINT_TEXT` text NOT NULL
															) ENGINE=InnoDB DEFAULT CHARSET=latin1;";	
																							
								    	 	$create_hint = mysqli_query($connection, $flagSql);
											$create_flag = mysqli_query($connection, $hintSql);
											if($create_hint){
												if($create_flag){
														echo "<h1 style='color:green;'>Success</h1>";													
												}else{
													echo "<h1 style='color:maroon;'>[F] Failed</h1>";
												}		
											}else{
												echo "<h1 style='color:maroon;'>[H] Failed</h1>";
											}
								    	 }else{
								    	 	echo "<h1>STATUS</h1>";
								    	 }
								    	?>
								    </th> 
							</tr>
							<tr>
							    <th>
							    	<h1>CREATE <br>Chat, Logger & Report</h1>
								</th>
							    <th>
							    	<form method="post" action="admin.php?option=db-manage">
										<input type="submit" name="create_chat" value="CREATE" class="token-input-2" <?php if(!Validator::AdminEditPermission() && DB::checkOptionTablesExists()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
									</form>
								</th> 
							    <th>
							    	<?php
							    	 if(isset($_POST['create_chat'])){
							    	 	$sql_create_chat = "CREATE TABLE `chat` (
													  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
													  `DATE` datetime NOT NULL,
													  `USERNAME` varchar(50) NOT NULL,
													  `TEAM` int(11) NOT NULL,
													  `CHAT` text NOT NULL
													) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
														
										$sql_create_report = "CREATE TABLE `report` (
													  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
													  `DATE` datetime NOT NULL,
													  `LOG` text NOT NULL
													) ENGINE=InnoDB DEFAULT CHARSET=latin1;";	
										
										$sql_create_logger = "CREATE TABLE `logger` (
													  `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
													  `DATE` datetime NOT NULL,
													  `TEAM` int(2) NOT NULL,
													  `LOG` text NOT NULL
													) ENGINE=InnoDB DEFAULT CHARSET=latin1;";							
							    	 	$create_chat = mysqli_query($connection, $sql_create_chat);
										$create_report = mysqli_query($connection, $sql_create_report);
										$create_logger = mysqli_query($connection, $sql_create_logger);
										if($create_chat){
											if($create_report){
												if($create_logger){
													echo "<h1 style='color:green;'>Success</h1>";
												}else{
													echo "<h1 style='color:maroon;'>[F] Failed</h1>";
												}
											}else{
												echo "<h1 style='color:maroon;'>[F] Failed</h1>";
											}		
										}else{
											echo "<h1 style='color:maroon;'>[H] Failed</h1>";
										}
							    	 }else{
							    	 	echo "<h1>STATUS</h1>";
							    	 }
							    	?>
							    </th> 
						</tr>	
						<tr>
							    <th>
							    	<h1>CREATE <br>Scoreboard & Updater & Options</h1>
								</th>
							    <th>
							    	<form method="post" action="admin.php?option=db-manage">
										<input type="submit" name="create_score" value="CREATE" class="token-input-2" <?php if(!Validator::AdminEditPermission() && DB::checkOptionTablesExists()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
									</form>
								</th> 
							    <th>
							    	<?php
							    	 if(isset($_POST['create_score'])){
							    	 	$sql_create_score = "CREATE TABLE `scoreboard` (
															  `ID` int(2) NOT NULL AUTO_INCREMENT PRIMARY KEY,
															  `TEAM` int(2) NOT NULL,
															  `TEAMNAME` varchar(15) NOT NULL,
															  `SCORE` double NOT NULL,
															  `PENALTY` double NOT NULL
															) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
															
										$sql_create_updater = "CREATE TABLE `updater` (
																  `ID` int(2) NOT NULL AUTO_INCREMENT PRIMARY KEY,
																  `TEAM` int(2) NOT NULL,
																  `USERNAME` varchar(10) NOT NULL,
																  `CHAT` int(1) NOT NULL,
																  `ACTIVITY` int(1) NOT NULL,
																  `SCORE` int(1) NOT NULL,
																  `HINT` int(1) NOT NULL,
																  `ANNOUNCE` int(1) NOT NULL,
																  `FLAG` int(1) NOT NULL,
																  `TIME` int(1) NOT NULL,
																  `HINT_UPDATE` varchar(30) NOT NULL
																) ENGINE=InnoDB DEFAULT CHARSET=latin1;";	
																
										$sql_create_options = "CREATE TABLE `options` (
																  `ID` int(1) NOT NULL AUTO_INCREMENT PRIMARY KEY,
																  `name` varchar(200) NOT NULL,
																  `value` varchar(200) NOT NULL
																) ENGINE=InnoDB DEFAULT CHARSET=latin1;";											
														
										$create_score = mysqli_query($connection, $sql_create_score);
										$create_updater = mysqli_query($connection, $sql_create_updater);
										$create_options = mysqli_query($connection, $sql_create_options);
										if($create_score){
											if($create_updater){
												if($create_options){
													$insert = mysqli_query($connection, "INSERT INTO `options` (`ID`, `name`, `value`) VALUES
																						(1, 'ANNOUNCE', '&ensp;'),
																						(2, 'END_TIME', '2017-04-05 10:00:00'),
																						(3, 'HOME_TIME', '2017-04-05 10:00:00'),
																						(4, 'LOGIN', 'DENY'),
																						(5, 'ADMINEDIT', 'ALLOW'),
																						(6, 'SCOREBOARD', 'DENY');");
													if($insert){
														echo "<h1 style='color:green;'>Success</h1>";
													}else{
														echo "<h1 style='color:maroon;'>[H] Failed</h1>";
													}														
												}else{
													echo "<h1 style='color:maroon;'>[H] Failed</h1>";
												}		
											}else{
												echo "<h1 style='color:maroon;'>[H] Failed</h1>";
											}		
										}else{
											echo "<h1 style='color:maroon;'>[H] Failed</h1>";
										}
							    	 }else{
							    	 	echo "<h1>STATUS</h1>";
							    	 }
							    	?>
							    </th> 
						</tr>
						<tr>
							    <th>
							    	<h1>CREATE <br>Users & Teams</h1>
								</th>
							    <th>
							    	<form method="post" action="admin.php?option=db-manage">
										<input type="submit" name="create_user" value="CREATE" class="token-input-2" <?php if(!Validator::AdminEditPermission() && DB::checkOptionTablesExists()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
									</form>
								</th> 
							    <th>
							    	<?php
							    	 if(isset($_POST['create_user'])){
							    	 	$sql_create_user = "CREATE TABLE `users` (
																  `ID` int(2) NOT NULL AUTO_INCREMENT PRIMARY KEY,
																  `USERNAME` varchar(50) NOT NULL,
																  `PASSWORD` varchar(32) NOT NULL,
																  `TEAM` int(2) NOT NULL,
																  `TYPE` varchar(1) NOT NULL,
																  `T_TYPE` varchar(1) NOT NULL, 
																  `TOKEN` varchar(8) NOT NULL,
																  `TOKEN_HASH` varchar(32) NOT NULL,
																  `TOKEN_ACT` int(1) NOT NULL
																) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
										
										$sql_create_team = "CREATE TABLE `team` (
																  `ID` int(2) NOT NULL AUTO_INCREMENT PRIMARY KEY,
																  `TEAM` int(2) NOT NULL,
																  `TEAMNAME` varchar(15) NOT NULL,
																  `LOGO` text NOT NULL
																) ENGINE=InnoDB DEFAULT CHARSET=latin1;";	
																
										$create_user = mysqli_query($connection, $sql_create_user);
										$create_team = mysqli_query($connection, $sql_create_team);
										$insertToken = mysqli_query($connection, "INSERT INTO users (USERNAME, PASSWORD, TEAM, TYPE, TOKEN, TOKEN_HASH, TOKEN_ACT) VALUES ('admin','2722e43f2d69d11363dd048e69319dcf','0','A','A1B2C3D4','9cb4a9b49df14f3ee3c177f0f74ad443',1)");
										if($create_user){
											if($create_team){
												if($insertToken){
													echo "<h1 style='color:green;'>Success</h1>";								
												}else{
													echo "<h1 style='color:maroon;'>[H] Failed</h1>";
												}
											}else{
												echo "<h1 style='color:maroon;'>[H] Failed</h1>";
											}				
										}else{
											echo "<h1 style='color:maroon;'>[H] Failed</h1>";
										}
							    	 }else{
							    	 	echo "<h1>STATUS</h1>";
							    	 }
							    	?>
							    </th> 
							</tr>	
							<tr>
							    <th>
							    	<h1>CREATE <br>SecGen Map</h1>
								</th>
							    <th>
							    	<form method="post" action="admin.php?option=db-manage">
										<input type="submit" name="create_map" value="CREATE" class="token-input-2" <?php if(!Validator::AdminEditPermission() && DB::checkOptionTablesExists()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
									</form>
								</th> 
							    <th>
							    	<?php
							    	 if(isset($_POST['create_map'])){
										$filename = 'sql/secgen.sql';
										$sqlSource = file_get_contents('sql/secgen.sql');
										$result = mysqli_multi_query($connection,$sqlSource);
										if($result){
										 echo "<h1 style='color:green;'>Success</h1>";											
										}else{
										 echo "<h1 style='color:red;'>Failed</h1>";											
										}
									 }else{
									 	echo "<h1>STATUS</h1>";
									 }
							    	?>
							    </th>
							</tr>	
							<tr>
							    <th>
							    	<h1>CREATE <br>Lockpick</h1>
								</th>
							    <th>
							    	<form method="post" action="admin.php?option=db-manage">
										<input type="submit" name="create_lock" value="CREATE" class="token-input-2" <?php if(!Validator::AdminEditPermission() && DB::checkOptionTablesExists()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
									</form>
								</th> 
							    <th>
							    	<?php
							    	 if(isset($_POST['create_lock'])){
										echo $table->createTable(Constants::LOCKPICK);
									 }else{
									 	echo "<h1>STATUS</h1>";
									 }
							    	?>
							    </th>
							</tr>																						
							</table>
							<h1>DROP Table</h1>
							<table style="width:100%;">
								<tr>
									    <th>
									    	<h1>DROP <br>SecGen Hint & Flag</h1>
										</th>
									    <th>
									    	<form method="post" action="admin.php?option=db-manage">
												<input type="submit" name="drop_hint" value="DROP" class="token-input-2" <?php if(!Validator::AdminEditPermission() && DB::checkOptionTablesExists()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
											</form>
										</th> 
									    <th>
									    	<?php
									    	 if(isset($_POST['drop_hint'])){
									    	 	$drop_hint = mysqli_query($connection, "DROP TABLE IF EXISTS secgenflag");
												$drop_flag = mysqli_query($connection, "DROP TABLE IF EXISTS hint");
												if($drop_hint){
													if($drop_flag){
														echo "<h1 style='color:green;'>Success</h1>";
													}else{
														echo "<h1 style='color:maroon;'>[F] Failed</h1>";
													}		
												}else{
													echo "<h1 style='color:maroon;'>[H] Failed</h1>";
												}
									    	 }else{
									    	 	echo "<h1>STATUS</h1>";
									    	 }
									    	?>
									    </th> 
								</tr>
								<tr>
								    <th>
								    	<h1>DROP <br>Chat, Logger & Report</h1>
									</th>
								    <th>
								    	<form method="post" action="admin.php?option=db-manage">
											<input type="submit" name="drop_chat" value="DROP" class="token-input-2" <?php if(!Validator::AdminEditPermission() && DB::checkOptionTablesExists()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
										</form>
									</th> 
								    <th>
								    	<?php
								    	 if(isset($_POST['drop_chat'])){
								    	 	$drop_chat = mysqli_query($connection, "DROP TABLE IF EXISTS chat");
											$drop_logger = mysqli_query($connection, "DROP TABLE IF EXISTS logger");
											$drop_report = mysqli_query($connection, "DROP TABLE IF EXISTS report");
											if($drop_chat){
												if($drop_logger){
													if($drop_report){
														echo "<h1 style='color:green;'>Success</h1>";
													}else{
														echo "<h1 style='color:maroon;'>[F] Failed</h1>";
													}		
												}else{
													echo "<h1 style='color:maroon;'>[F] Failed</h1>";
												}		
											}else{
												echo "<h1 style='color:maroon;'>[H] Failed</h1>";
											}
								    	 }else{
								    	 	echo "<h1>STATUS</h1>";
								    	 }
								    	?>
								    </th> 
								</tr>
								<tr>
								    <th>
								    	<h1>DROP <br>Scoreboard & Updater & Options</h1>
									</th>
								    <th>
								    	<form method="post" action="admin.php?option=db-manage">
											<input type="submit" name="drop_score" value="DROP" class="token-input-2" <?php if(!Validator::AdminEditPermission() && DB::checkOptionTablesExists()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
										</form>
									</th> 
								    <th>
								    	<?php
								    	 if(isset($_POST['drop_score'])){
								    	 	$drop_score = mysqli_query($connection, "DROP TABLE IF EXISTS scoreboard");
											$drop_updater = mysqli_query($connection, "DROP TABLE IF EXISTS updater");
											$drop_option = mysqli_query($connection, "DROP TABLE IF EXISTS options");
											if($drop_score){
												if($drop_updater){
													if($drop_option){
														echo "<h1 style='color:green;'>Success</h1>";															
													}else{
														echo "<h1 style='color:maroon;'>[H] Failed</h1>";
													}
												}else{
													echo "<h1 style='color:maroon;'>[H] Failed</h1>";
												}	
											}else{
												echo "<h1 style='color:maroon;'>[H] Failed</h1>";
											}
								    	 }else{
								    	 	echo "<h1>STATUS</h1>";
								    	 }
								    	?>
								    </th> 
								</tr>
								<tr>
								    <th>
								    	<h1>DROP <br>Users & Team Table</h1>
									</th>
								    <th>
								    	<form method="post" action="admin.php?option=db-manage">
											<input type="submit" name="drop_user" value="DROP" class="token-input-2" <?php if(!Validator::AdminEditPermission() && DB::checkOptionTablesExists()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
										</form>
									</th> 
								    <th>
								    	<?php
								    	 if(isset($_POST['drop_user'])){
								    	 	$drop_user = mysqli_query($connection, "DROP TABLE IF EXISTS users");
											$drop_team = mysqli_query($connection, "DROP TABLE IF EXISTS team");
											if($drop_user){
												if($drop_team){
													echo "<h1 style='color:green;'>Success</h1>";
												}else{
													echo "<h1 style='color:maroon;'>[H] Failed</h1>";
												}		
											}else{
												echo "<h1 style='color:maroon;'>[H] Failed</h1>";
											}
								    	 }else{
								    	 	echo "<h1>STATUS</h1>";
								    	 }
								    	?>
								    </th> 
								</tr>
								<tr>
								    <th>
								    	<h1>DROP <br>SecGen Map</h1>
									</th>
								    <th>
								    	<form method="post" action="admin.php?option=db-manage">
											<input type="submit" name="drop_map" value="DROP" class="token-input-2" <?php if(!Validator::AdminEditPermission() && DB::checkOptionTablesExists()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
										</form>
									</th> 
								    <th>
								    	<?php
								    	 if(isset($_POST['drop_map'])){
								    	 	$drop_map = mysqli_query($connection, "DROP TABLE IF EXISTS secgen");
											if($drop_map){
													echo "<h1 style='color:green;'>Success</h1>";	
											}else{
												echo "<h1 style='color:maroon;'>[H] Failed</h1>";
											}
								    	 }else{
								    	 	echo "<h1>STATUS</h1>";
								    	 }
								    	?>
								    </th> 
								</tr>
								<tr>
								    <th>
								    	<h1>DROP <br>Lockpick</h1>
									</th>
								    <th>
								    	<form method="post" action="admin.php?option=db-manage">
											<input type="submit" name="drop_lock" value="DROP" class="token-input-2" <?php if(!Validator::AdminEditPermission() && DB::checkOptionTablesExists()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
										</form>
									</th> 
								    <th>
								    	<?php
								    	 if(isset($_POST['drop_lock'])){
								    	 	echo $table->dropTable(Constants::LOCKPICK);
								    	 }else{
								    	 	echo "<h1>STATUS</h1>";
								    	 }
								    	?>
								    </th> 
								</tr>																										
								</table>
							</div>

						<?php
						break;	
					
					case "verify":
					?>
							<div id="team1-div">
								<h1>System Game Status</h1>
								<table>
									<tr class="table_heading">
									    <th>Name</th>
									    <th>Status</th> 
								    </tr>
								    <tr> 		
								    	<td>Any Token Conflict</td>
								    	
								    		<?php
								    		$qc1 = mysqli_query($connection, "SELECT * FROM users");
											if($qc1){
												$q1 = mysqli_query($connection, "SELECT TOKEN FROM users group by TOKEN having count(*) >= 2");
												$q1_result = mysqli_num_rows($q1);
												if($q1){
													if($q1_result == 0){
														echo "<td style='background:#c3e29c;color:black;text-align:center;'>No Conflict</td>";
													}else{
														while($row1 = mysqli_fetch_assoc($q1)){
															$q1_b = $row1['TOKEN'];
															echo "<td style='background:#f7b9b9;color:black;text-align:center;'>$q1_b</td>";
														}
													}
												}else{
													echo "<td style='background:#f7b9b9;color:black;text-align:center;'>Conflict</td>";	
												}												
											}else{
												echo "<td style='background:#f7b9b9;color:black;text-align:center;'>Table Not Initialised</td>";												
											}
								    		?>
								    	
								    </tr>
								    <tr> 
								    <td>Any Notification Conflict</td>
								    	
								    		<?php
								    		$q2 = mysqli_query($connection, "SELECT USERNAME FROM users WHERE TOKEN_ACT='1' AND TYPE='N'");
											$q22 = mysqli_query($connection, "SELECT USERNAME FROM updater");
											if($q2 || $q22){
												$q2_result = mysqli_num_rows($q2);
												$q22_result = mysqli_num_rows($q22);
												if($q2_result == $q22_result){
													echo "<td style='background:#c3e29c;color:black;text-align:center;'>No Conflict</td>";
												}else{												
														echo "<td style='background:#f7b9b9;color:black;text-align:center;'>Conflict</td>";
												}
											}else{
												echo "<td style='background:#f7b9b9;color:black;text-align:center;'>Table Not Initialised</td>";	
											}
								    		
								    		?>
								    	
								    </tr>
								    <tr> 
								    <td>Any Scoreboard & Team Conflict</td>
								    	
								    		<?php
								    		$q4 = mysqli_query($connection, "SELECT TEAM,TEAMNAME FROM scoreboard");
											$q44 = mysqli_query($connection, "SELECT TEAM,TEAMNAME FROM team");
											
											if($q4 || $q44){
												if(mysqli_num_rows($q4) == mysqli_num_rows($q44)){
													echo "<td style='background:#c3e29c;color:black;text-align:center;'>No Conflict</td>";
												}else{											
													echo "<td style='background:#f7b9b9;color:black;text-align:center;'>Conflict</td>";
												}
											}else{
												echo "<td style='background:#f7b9b9;color:black;text-align:center;'>Table Not Initialised</td>";												
											}							    		
								    		?>
								    	
								    </tr>									    								    
								    <tr> 		
								    <td>Pending Registration</td>
								    	
								    		<?php
								    		$q3 = mysqli_query($connection, "SELECT TOKEN_ACT FROM users WHERE TOKEN_ACT='0'");
											if($q3){
												$q3_result = mysqli_num_rows($q3);
												if($q3_result == 0){
													echo "<td style='background:#c3e29c;color:black;text-align:center;'>All Registered</td>";
												}else{												
														echo "<td style='background:#f7b9b9;color:black;text-align:center;'>$q3_result </td>";
												}												
											}else{
												echo "<td style='background:#f7b9b9;color:black;text-align:center;'>Table Not Initialised</td>";												
											}
								    		?>
								    	
								    </tr>										    		    										
								</table>
								<h1>Table Status</h1>
								<table style="width:80%;">
									<tr class="table_heading">
									    <th>Chat</th>
									    <th>Hint</th> 
									    <th>Logger</th>
									    <th>Options</th>
									    <th>Report</th>
									    <th>Scoreboard</th>
									    <th>SecGen</th>
									    <th>SecGenFlag</th>
									    <th>Team</th>
									    <th>Updater</th>
									    <th>Users</th>
									    <th>LockPick</th>
								    </tr>
								    <tr> 		
								    	<?php
								    	$tables = array("chat", "hint", "logger", "options","report","scoreboard","secgen","secgenflag","team","updater","users","lockpick"); 
								    	foreach($tables as $tab){
								    		if(mysqli_num_rows(mysqli_query($connection, "SHOW TABLES LIKE '$tab'"))==0){
								    			echo "<td style='background:#f7b9b9;color:black;text-align:center;'>Failed</td>";
								    		}else{
								    			echo "<td style='background:#c3e29c;color:black;text-align:center;'>Success</td>";
								    		}
								    	}
								    	?>									
								    </tr>										    		    										
								</table>
							<h1>Challenges</h1>
							<table>
							<?php
							$select = mysqli_query($connection, "SELECT * FROM team");
							if($select){
								?>
							  <tr class="table_heading">
							    <th>Team</th>
							    <th>VM</th> 
							    <th>Challenges</th>
							    <th>Hint</th>
							  </tr>
								<?php
								while($team_row = mysqli_fetch_assoc($select)){
									$team = $team_row['TEAM'];
									$teamName = $team_row['TEAMNAME'];//team
									$vm = mysqli_query($connection, "SELECT DISTINCT VM FROM secgenflag WHERE TEAM='$team'");
									if($vm){
										$vm_query = mysqli_num_rows($vm);//vm	
										$challenges = mysqli_query($connection, "SELECT C_ID FROM secgenflag WHERE TEAM='$team'");
										$challenges_count = mysqli_num_rows($challenges);//challenges	
										$hint_q = mysqli_query($connection, "SELECT HINT_ID FROM hint WHERE TEAM='$team'");
										$hint_count = mysqli_num_rows($hint_q);//hint
											?>
											  <tr>
											    <td style='text-align:center;'><?php echo $teamName;?></td>
											    <td style='text-align:center;'><?php echo $vm_query;?></td>
											    <td style='text-align:center;'><?php echo $challenges_count;?></td>
											    <td style='text-align:center;'><?php echo $hint_count;?></td>
											  </tr>		
											 <?php							
									}else{

									}					
								}
							}else{
								echo "<td style='background:#f7b9b9;color:black;text-align:center;'>No Challanges Found</td>";								
							}
							?>
							</table>
							<h1>Error Monitor</h1>
								<table>
								  <tr class="table_heading">
								    <th>S.No</th>
								    <th>Date</th> 
								    <th>Log</th>
								  </tr>
								<?php
									$log = mysqli_query($connection, "SELECT * FROM report");
									if($log){
											$count = mysqli_num_rows($log);
											if($count > 0){									
												while($repo = mysqli_fetch_assoc($log)){
													$id = $repo['ID'];
													$date = $repo['DATE'];
													$logtext = $repo['LOG'];	
													?>
													  <tr>
													    <td style='text-align:left;color:black;background:#f7b9b9;'><?php echo $id;?></td>
													    <td style='text-align:left;color:black;background:#f7b9b9;'><?php echo $date;?></td>
													    <td style='text-align:left;color:black;background:#f7b9b9;'><?php echo $logtext;?></td>
													  </tr>
														
													<?php						
												}
											}else{
												?>
													  <tr>
													    <td style='background:#c3e29c;color:black;text-align:center;'>STATUS_OK</td>
													    <td style='background:#c3e29c;color:black;text-align:center;'>STATUS_OK</td>
													    <td style='background:#c3e29c;color:black;text-align:center;'>STATUS_OK</td>
													  </tr>
												<?php
											}
									}else{
										?>
													  <tr>
													    <td style='text-align:left;color:black;background:#f7b9b9;'>Table Not Found</td>
													    <td style='text-align:left;color:black;background:#f7b9b9;'>Table Not Found</td>
													    <td style='text-align:left;color:black;background:#f7b9b9;'>Table Not Found</td>
													  </tr>
										<?php
									}
								?>
								</table>
							</div>
							<?php
						break;		
					
					case "lockpicking":
						$lock_name = "NULL";
						?>
						<div class="token-div-add">
							<h1>Create Lockpicking Sendouts</h1>
							<form method="post" action="admin.php?option=lockpicking">
							<table style="width:100%;">
								<tr>
									    <th>
											<input type="text" name="lock_name" placeholder="Name of the flag" class="token-input-1"/>
										</th>
									    <th id="browse">
											<input type="text" name="lock_flag" placeholder="Enter the Flag Here" class="token-input-1"/>
										</th> 
									    <th>
									    	<input type="submit" name="lock_submit" value="Create" class="token-input-2" <?php if(!Validator::AdminEditPermission()){echo "disabled "; echo Validator::DisabledCSS();}?>/>
									    </th> 
								</tr>
							</table>
							</form>	
							<h1>Send Unlocked Flags</h1>
							<form method="post" action="admin.php?option=lockpicking">
							<table style="width:100%;">
								<tr>
									    <th>
											<select name="lock_send_team">
												<?php
												$token_team_list = mysqli_query($connection, "SELECT TEAM, TEAMNAME FROM team");
												while($token_team_list_row = mysqli_fetch_assoc($token_team_list)){
													$token_team = $token_team_list_row['TEAM'];
													$token_team_name = $token_team_list_row['TEAMNAME'];
													echo "<option value='$token_team'>$token_team_name</option>";
												}
												?>
											</select>
										</th>
									    <th>
											<select name="lock_send_flag" onchange="document.getElementById('changeinput').value=this.options[this.selectedIndex].text">
												<?php
												$token_team_list = mysqli_query($connection, "SELECT NAME, FLAG FROM lockpick");
												while($token_team_list_row = mysqli_fetch_assoc($token_team_list)){
													$lock_name = $token_team_list_row['NAME'];
													$lock_flag = $token_team_list_row['FLAG'];
													echo "<option value='$lock_flag'>$lock_name</option>";
												}
												?>
											</select>
										</th> 
									    <th>
									    	<input type="hidden" name="lname" id="changeinput" value="" />
									    	<input type="submit" name="lock_send_submit" value="Send" class="token-input-2"/>
									    </th> 
								</tr>							
							</table>
							</form>	
							<?php
							if(isset($_POST['lock_submit'])){
								if(Validator::BooleanEmptyCheck($_POST['lock_name']) && Validator::BooleanEmptyCheck($_POST['lock_flag'])){
									echo Validator::printSuccess(DB::lockpickAdd($_POST['lock_name'], $_POST['lock_flag']));
								}else{
									echo Validator::printFailure("Failed to Insert");	
								}
							}else if(isset($_POST['lock_send_submit'])){
								$lock_name = $_POST['lname'];
								echo DB::sendFlagsToTeamActivity($_POST['lock_send_team'], $lock_name, $_POST['lock_send_flag']);
							}
							?>														
						</div>
						<?php
						break; 
							
					default:
						header('location:admin.php?option=team');
						break;	
				}			
			}
			
			?>
			<?php
				function randomToken() {
				    $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
				    $password = array(); 
				    $letterLength = strlen($letters) - 1; 
				    for ($i = 0; $i < 8; $i++) {
				        $n = rand(0, $letterLength);
				        $password[] = $letters[$n];
				    }
				    return implode($password); 
				}
			
			?>
		</div>
	</div>
	<script>	
	function update(){
		this.homedate = function() {
			var fg = $('#home_date').val();
			$.ajax({
				method: "POST",
				url: "template/adminform.php",
				data: {"home_date": fg },
				success: function(status){
					$('#home_status').html(status);										
				}	
			});
		}

		this.ctf = function() {
			var fg = $('#ctf_date').val();
			$.ajax({
				method: "POST",
				url: "template/adminform.php",
				data: {"ctf_date": fg },
				success: function(status){
					$('#ctf_status').html(status);										
				}	
			});
		}	
		
		this.login = function() {
			var fg = $('#lstat').text();
			$.ajax({
				method: "POST",
				url: "template/adminform.php",
				data: {"ctf_login": fg },
				success: function(status){
					$('#lstat').html(status);										
				}	
			});
		}
		
		this.edits = function() {
			var fg = $('#adminedit').text();
			$.ajax({
				method: "POST",
				url: "template/adminform.php",
				data: {"adminedit": fg },
				success: function(status){
					$('#adminedit').html(status);	
					location.reload();									
				}	
			});
		}
		
		this.homeScore = function() {
			var fg = $('#hscore').text();
			$.ajax({
				method: "POST",
				url: "template/adminform.php",
				data: {"hscore": fg },
				success: function(status){
					$('#hscore').html(status);	
					location.reload();									
				}	
			});
		}				
	}
	var Update = new update();
	</script>
</body>
</html>