//Define an angular module for our app
var app = angular.module('sidebar', []);

app.controller('sidebar', function($scope,$window, $http) {
	
 // Load all available items 
	displayOverview();
	var user = 'ross';
	setSideBar();
	// $("div#draftStatusWrapper").hide();
	// $("div#draftSliderWrapper").hide();

	function displayOverview(){
		var overviewText = '<h1>Bracketball Overview</h1><br>This is the early version of the bracketball site. The UI is very basic. To the left is the nav bar, the only current working links are bracket and the links for the league pages. On the leauge page it will show a bracket with highlighting based on team ownership. Also there is a table to view who owns which teams, click on the various users to see. And finally is a score table tabulating current results.';
	  	$("div#maincontent-wrapper").html(overviewText);
	  	$("div#draftStatusWrapper").hide();
	  	//createDraftedTeamsTable(2,10);
	};
	$window.updateDraftedTeamsTable = function (leagueid,ownerid) {
		createDraftedTeamsTable(leagueid,ownerid);
	};
	$scope.displayOverview = function () {
		displayOverview();
	};

	$('#viewSwitch').change(function(){
		var sPageURL = window.location.href;
		var sURLVariables = sPageURL.split('#');
		leagueidurl = sURLVariables[1];
		useridurl = sURLVariables[2];
		displayDraft(leagueidurl,useridurl);
	})


	$window.setSelected = function (name,id){
		var container = $("div#selectedTeam");
		container.html('<label id="' + id + '" class="selectedTeam"><h2>' + name + '</h2></label>');
	};

	function displayLeague(leagueid,userid,picknumber=-1){
		var id = setTimeout( function (){
			$.ajax({
				url: "ajax/getDraftStatus.php",
				data: {lid: leagueid}
			})
			.done(function(data){
				data = JSON.parse(data);
				var container = $("div#draftStatusWrapper");
				if(data['status'] == 'completed'){
					createLeagueBracket(leagueid,userid);
					container.hide();
				}
				else if(data['status'] == 'not_started'){
					var statusText = '<h1>League Status</h1><br>Your league has not started the draft, please contact the league administrator about the start time, an email will be sent 30 minutes prior to the start of the draft';
					$("div#maincontent-wrapper").html(statusText);
					container.hide();
				}
				else if(data['status'] == 'in_progress'){
					var sPageURL = window.location.href;
					var sURLVariables = sPageURL.split('#');
					if(picknumber != data['pick']){
						displayDraft(leagueid,userid);
						createDraftedTeamsTable(leagueid,userid,data['pick']);
					}
					leagueidurl = sURLVariables[1];
					if(leagueidurl == '')
						return;
					container.show();
					displayLeague(leagueidurl,userid,data['pick']);
				}
			});
		},1000);
	}

	function displayDraft(leagueid,userid){
		if($("#viewSwitch").is(':checked'))
			displayDraftTable(leagueid,userid);
		else
			createLeagueDraftBracket(leagueid);
	}

	function displayDraftTable(leagueid,userid){
		$http.post("ajax/getDraftData.php?lid="+leagueid).success(function(data){
			var bracketArray = data;
			var container = $("div#maincontent-wrapper");
			var row = '';
			container.html('<table id="draftTable" class="draftTable"><tbody><tr><td>1</td><td>2</td><td>3</td><td>4</td></tr></tbody></table>');
			var table = $('#draftTable').children();
			for(i = 1; i < 17; i++){
				row = '<tr>';
				for(j = 1; j < 5; j++){
					row = row + '<td class="' + data['bracket'][j][i]['style'] + ' draftCell" onclick="setSelected(\'' + data['bracket'][j][i]['formatted_name'] + '\','  + data['bracket'][j][i]['tid'] +')">' + data['bracket'][j][i]['formatted_name'] + '</td>';
				}
				row = row + '</tr>';
				table.append(row);
			}
		})
	}
	
	function createBracket(){
		var container = $("div#maincontent-wrapper");
		
		$http.post("ajax/getBracket.php").success(function(data){
			var bracketArray = data;

			// $("div#maincontent-wrapper").html('<div id=bracket><table id="bracketTable"><tbody><tr><td>First Round</td><td>Second Round</td><td>Sweet Sixteen</td><td>Elite Eight</td><td>Final Four</td><td>Nat\'l Champ</td></tr></tbody></table></div>');
			var table = $('#bracketTable').children();
			var regionOrder = [1,4,3,2];
			var games = [];
			for (i = 0; i < regionOrder.length; i++)
			{
				region = regionOrder[i];
				games.push([data[region][1]['formatted_name'],data[region][16]['formatted_name']]);
				games.push([data[region][8]['formatted_name'],data[region][9]['formatted_name']]);
				games.push([data[region][5]['formatted_name'],data[region][12]['formatted_name']]);
				games.push([data[region][4]['formatted_name'],data[region][13]['formatted_name']]);
				games.push([data[region][6]['formatted_name'],data[region][11]['formatted_name']]);
				games.push([data[region][3]['formatted_name'],data[region][14]['formatted_name']]);
				games.push([data[region][7]['formatted_name'],data[region][10]['formatted_name']]);
				games.push([data[region][2]['formatted_name'],data[region][15]['formatted_name']]);
			}
			var bracketData = {teams : games};
			container.bracket({
				init: bracketData,
				teamWidth: 125,
				//decorator: {render: render_fn}
			});
		 });
	};

	function setSideBar(){
		var sideBarContent = '';
	  	$http.post("ajax/getUserInfo.php").success(function(data){
	  		var userData = data;
	  		for(i= 0; i < userData.length; i++)
	  		{
	  			sideBarContent = '<li id=lid'+ userData[i]['lid'] + '><a href="#'+ userData[i]['lid'] + '">' + userData[i]['league_name'] + '</a></li>';
	  			//document.getElementById("league"+i).innerHTML = '<li><a href="#">' + userData[0]['league_name'] + '</a></li>';
	  			// $("div#league"+ (i+1)).append(sideBarContent);
	  		}
	  		//$("div#league1").append(sideBarContent);
		 });
	};

	$('#bracketlink').click(function(){
    	createBracket();
	});

	$('#showHide').click(function(){
		$('#sidebar-wrapper').toggle();
		$('#page-content-wrapper').toggle();
	});
	$('#mainlink').click(function(){
    	displayOverview();
	});

	$('#league1').click(function(){
		var params = $("#league1 > a").attr("id");
		var arrParams = params.split("lid");
    	displayLeague(arrParams[0],arrParams[1]);
	});

	$('#league2').click(function(){
    	var params = $("#league2 > a").attr("id");
		var arrParams = params.split("lid");
    	displayLeague(arrParams[0],arrParams[1]);
	});

	$('#league3').click(function(){
    	var params = $("#league3 > a").attr("id");
		var arrParams = params.split("lid");
    	displayLeague(arrParams[0],arrParams[1]);
	});

	$('#league4').click(function(){
    	var params = $("#league4 > a").attr("id");
		var arrParams = params.split("lid");
    	displayLeague(arrParams[0],arrParams[1]);
	});

	$('#league5').click(function(){
    	createBracket();
	});

	$('#logout').click(function(){
		var cookie = getCookie('authID');
    	$.post("ajax/logoutUser.php",{ hash: cookie}).done(function(data){
    		alert("Logged out.");
			window.location = "/bracketball";
		});
	});

	$('#draftButton').click(function(){
    	var teamid = $("#selectedTeam > label").attr("id");
    	if (teamid == null){
    		alert('Please select a team');
    	}
    	else{
	    	var sPageURL = window.location.href;
			var sURLVariables = sPageURL.split('#');
			leagueidurl = sURLVariables[1];
			useridurl = sURLVariables[2];
			$.get("ajax/draftTeam.php",{user: useridurl, league: leagueidurl, team: teamid}).done(function(data){
				if(data=='selected')
					alert("That team has already been selected please pick another");
				else
					alert("Selected");
			});
	    }
	});

	function getCookie(name) {
		var value = "; " + document.cookie;
		var parts = value.split("; " + name + "=");
		if (parts.length == 2) return parts.pop().split(";").shift();
	}

	function createLeagueDraftBracket(leagueid){
		$http.post("ajax/getLeagueBracket.php?lid="+leagueid).success(function(data){
			var container = $("div#maincontent-wrapper");
		  	var games = [];
		  	var scores = [];
		  	for(var i = 0; i<6; i++)
		  		scores[i]=[];
		  	var regionOrder = [1,4,3,2];
		    for (var i = 0; i < regionOrder.length; i++)
			{
				var region = regionOrder[i];
				games.push([data['bracket'][region][1]['style'] + '|' + data['bracket'][region][1]['formatted_name'] + '|' + data['bracket'][region][1]['tid'],data['bracket'][region][16]['style'] + '|' + data['bracket'][region][16]['formatted_name'] + '|' + data['bracket'][region][16]['tid']]);
				games.push([data['bracket'][region][8]['style'] + '|' + data['bracket'][region][8]['formatted_name'] + '|' + data['bracket'][region][8]['tid'],data['bracket'][region][9]['style'] + '|' + data['bracket'][region][9]['formatted_name'] + '|' + data['bracket'][region][9]['tid']]);
				games.push([data['bracket'][region][5]['style'] + '|' + data['bracket'][region][5]['formatted_name'] + '|' + data['bracket'][region][5]['tid'],data['bracket'][region][12]['style'] + '|' + data['bracket'][region][12]['formatted_name'] + '|' + data['bracket'][region][12]['tid']]);
				games.push([data['bracket'][region][4]['style'] + '|' + data['bracket'][region][4]['formatted_name'] + '|' + data['bracket'][region][4]['tid'],data['bracket'][region][13]['style'] + '|' + data['bracket'][region][13]['formatted_name'] + '|' + data['bracket'][region][13]['tid']]);
				games.push([data['bracket'][region][6]['style'] + '|' + data['bracket'][region][6]['formatted_name'] + '|' + data['bracket'][region][6]['tid'],data['bracket'][region][11]['style'] + '|' + data['bracket'][region][11]['formatted_name'] + '|' + data['bracket'][region][11]['tid']]);
				games.push([data['bracket'][region][3]['style'] + '|' + data['bracket'][region][3]['formatted_name'] + '|' + data['bracket'][region][3]['tid'],data['bracket'][region][14]['style'] + '|' + data['bracket'][region][14]['formatted_name'] + '|' + data['bracket'][region][14]['tid']]);
				games.push([data['bracket'][region][7]['style'] + '|' + data['bracket'][region][7]['formatted_name'] + '|' + data['bracket'][region][7]['tid'],data['bracket'][region][10]['style'] + '|' + data['bracket'][region][10]['formatted_name'] + '|' + data['bracket'][region][10]['tid']]);
				games.push([data['bracket'][region][2]['style'] + '|' + data['bracket'][region][2]['formatted_name'] + '|' + data['bracket'][region][2]['tid'],data['bracket'][region][15]['style'] + '|' + data['bracket'][region][15]['formatted_name'] + '|' + data['bracket'][region][15]['tid']]);
			}
			var bracketData = {teams : games};
			container.bracket({
				init: bracketData,
				teamWidth: 125,
				decorator: {edit: render_draft, render: render_draft}
			});
			
		});
	}

	function createLeagueBracket(leagueid,userid){
		$http.post("ajax/getLeagueBracket.php?lid="+leagueid).success(function(data){
			var container = $("div#maincontent-wrapper");
		  	var games = [];
		  	var scores = [];
		  	for(var i = 0; i<6; i++)
		  		scores[i]=[];
		  	var regionOrder = [1,4,3,2];
		    for (var i = 0; i < regionOrder.length; i++)
			{
				var region = regionOrder[i];
				games.push([data['bracket'][region][1]['style'] + '|' + data['bracket'][region][1]['formatted_name'],data['bracket'][region][16]['style'] + '|' + data['bracket'][region][16]['formatted_name']]);
				games.push([data['bracket'][region][8]['style'] + '|' + data['bracket'][region][8]['formatted_name'],data['bracket'][region][9]['style'] + '|' + data['bracket'][region][9]['formatted_name']]);
				games.push([data['bracket'][region][5]['style'] + '|' + data['bracket'][region][5]['formatted_name'],data['bracket'][region][12]['style'] + '|' + data['bracket'][region][12]['formatted_name']]);
				games.push([data['bracket'][region][4]['style'] + '|' + data['bracket'][region][4]['formatted_name'],data['bracket'][region][13]['style'] + '|' + data['bracket'][region][13]['formatted_name']]);
				games.push([data['bracket'][region][6]['style'] + '|' + data['bracket'][region][6]['formatted_name'],data['bracket'][region][11]['style'] + '|' + data['bracket'][region][11]['formatted_name']]);
				games.push([data['bracket'][region][3]['style'] + '|' + data['bracket'][region][3]['formatted_name'],data['bracket'][region][14]['style'] + '|' + data['bracket'][region][14]['formatted_name']]);
				games.push([data['bracket'][region][7]['style'] + '|' + data['bracket'][region][7]['formatted_name'],data['bracket'][region][10]['style'] + '|' + data['bracket'][region][10]['formatted_name']]);
				games.push([data['bracket'][region][2]['style'] + '|' + data['bracket'][region][2]['formatted_name'],data['bracket'][region][15]['style'] + '|' + data['bracket'][region][15]['formatted_name']]);
				for(var j = 1; j < 9; j++)
					if(data['games'][region][j][0])
						scores[0].push([Number(data['games'][region][j][0]),Number(data['games'][region][j][1])]);
					else
						scores[0].push([]);
				for(var j = 9; j < 13; j++)
					if(data['games'][region][j][0])
						scores[1].push([Number(data['games'][region][j][0]),Number(data['games'][region][j][1])]);
					else
						scores[1].push([]);
				for(var j = 13; j < 15; j++)
					if(data['games'][region][j][0])
						scores[2].push([Number(data['games'][region][j][0]),Number(data['games'][region][j][1])]);
					else
						scores[2].push([]);
				if(data['games'][region][15][0])
					scores[3].push([Number(data['games'][region][15][0]),Number(data['games'][region][15][1])]);
				else
					scores[3].push([]);
			}
			if(data['games']['finalfour'][1])
				scores[4].push([Number(data['games']['finalfour'][1][0]),Number(data['games']['finalfour'][1][1])]);
			else
				scores[4].push([]);
			if(data['games']['finalfour'][2])
				scores[4].push([Number(data['games']['finalfour'][2][0]),Number(data['games']['finalfour'][2][1])]);
			else
				scores[4].push([]);
			if(data['games']['finalfour'][3])
				scores[5].push([Number(data['games']['finalfour'][3][0]),Number(data['games']['finalfour'][3][1])]);
			else
				scores[5].push([]);
			var bracketData = {teams : games,
					results:scores};
			container.bracket({
				init: bracketData,
				teamWidth: 125,
				decorator: {edit: render_fn, render: render_fn}
			});
			container.append('<table id="standingsTable" class="standingsTable"><tbody><tr><td>Conference</td><td>Score</td></tr></tbody></table>');
			createDraftedTeamsTable(leagueid,userid);
			var table = $('#standingsTable').children();
			for (i = 0; i < 8; i++)
			{
				table.append('<tr><td class="' + data['standings'][i]['style'] + '"">' + data['standings'][i]['user_name'] + '</td><td>' + data['standings'][i]['score'] + '</td></tr>');
			}
		});
	}
	function render_fn(container, data, score, state) {
		switch(state) {
			case 'empty-bye':
				container.append('BYE')
				return;
			case 'empty-tbd':
				container.append('TBD')
				return;
			
			case 'entry-no-score':
			case 'entry-default-win':
			case 'entry-complete':
				var fields = data.split('|')
				if (fields.length != 2)
					container.append('<i>INVALID</i>')
				else
					container.append('<div class="'+fields[0]+' bracketStyle">' + fields[1] + '</div>')
				return;
	  	}
	}

	function render_draft(container, data, score, state) {
		switch(state) {
			case 'empty-bye':
				container.append('BYE')
				return;
			case 'empty-tbd':
				container.append('TBD')
				return;
			
			case 'entry-no-score':
			case 'entry-default-win':
			case 'entry-complete':
				var fields = data.split('|')
				if (fields.length != 3)
					container.append('<i>INVALID</i>')
				else
					container.append('<div class="'+fields[0]+' bracketStyle" onclick="setSelected(\'' + fields[1] + '\','  + fields[2] + ')"">' + fields[1] + '</div>')
				return;
	  	}
	}

	function createDraftedTeamsTable(leagueid,ownerid,picknumber){
		ownerid = typeof ownerid !== 'undefined' ? ownerid : '';
		picknumber = typeof picknumber !== 'undefined' ? picknumber : '';
		$http.post("ajax/getDraftOwnership.php?lid="+leagueid+'&uid='+ownerid).success(function(data){
			var container = $("div#draftSliderWrapper");
			if(picknumber != ''){
				container.html('<table id="draftStatus" class="draftStatus"><tbody><tr><td><h3>Draft Status</h3></td></tr></tbody></table><br \>');
				var statusTable = $('#draftStatus').children();
				var tableStringStatus = '<tr>';
				var sPageURL = window.location.href;
				var sURLVariables = sPageURL.split('#');
				leagueidurl = sURLVariables[1];
				useridurl = sURLVariables[2];
				if(data['order'][picknumber]['uid'] == useridurl)
					$('#draftButton').prop('disabled',false);
				else
					$('#draftButton').prop('disabled',true);
				for(i=picknumber; i < Number(picknumber)+8 && i < 65;i++){
					if(i==picknumber)
						tableStringStatus = tableStringStatus + '<td class="selected statusBar ' + data['order'][i]['style'] + '"><b>' + data['order'][i]['user_name'] + '</b></td>';
					else
						tableStringStatus = tableStringStatus + '<td class="statusBar ' + data['order'][i]['style'] + '">' + data['order'][i]['user_name'] + '</td>';
				}
				tableStringStatus = tableStringStatus + '</tr>';
				statusTable.append(tableStringStatus);
			}
			if(!$("#ownerTable").length)
				$("div#maincontent-wrapper").append('<br /><div id=bracket><table id="wrapperTable"><tbody><tr><td><table id="ownerTable" class="ownerTable"><tbody><tr><td>Conference</td></tr></tbody></table></td><td><table id="teamsTable" class="teamsTable"><tr><td>Teams</td></tr></tbody></table></td></td></tbody</table></div>');
			var table = $('#ownerTable').children();
			var tableString = '';
			for (i = 1; i < 9; i++)
			{
				tableString = tableString + '<tr><td class="' + data['users'][i]['style'] + '" onClick="updateDraftedTeamsTable(' +leagueid + ',' + data['users'][i]['uid'] + ')">' + data['users'][i]['user_name'] + '</td></tr>';
				if(data['users'][i]['uid'] == ownerid)
					var teamStyle = data['users'][i]['style'];
			}
			table.html(tableString);
			if(data['teams'][0] !== 'undefined')
			{
				tableString = '';
				table = $('#teamsTable').children();
				data['teams'].forEach(function(team) {
					tableString = tableString + '<tr><td class="' + teamStyle + '">' + team + '</td></tr>';
				});
				table.html(tableString);
			}
		});
	};
});