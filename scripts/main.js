"use strict";

function init() {
	// indicate correct error messages to show
	window.hosted = true;
	try {
		window.aloc = localStorage.getItem("aloc");
		window.bloc = localStorage.getItem("bloc");
		document.getElementById("contrast").checked
			= localStorage.getItem("contrast") == "true";
	} catch (e) {}
	// fill with default values
	window.aloc = window.aloc == null ? "Hong Kong" : aloc;
	window.bloc = window.bloc == null ? "London" : bloc;
	document.getElementById("alocinput").value = aloc;
	document.getElementById("blocinput").value = bloc;
	// non-empty string to avoid location not found trigger, inform first time
	window.apijson = "init";
	// (21 + 1) % 22 == 0, this will start at 0 when main is executed
	window.fctime = 21;
	window.fc = { 'a': new Array(5), 'b': new Array(5) };
	window.fcdate = { 'a': new Array(5), 'b': new Array(5) };
	window.fchilow = { 'a': new Array(5), 'b': new Array(5) };
	window.timeoutpid = 0;
	
	[].forEach.call(document.getElementsByClassName("jshide"), function(element) {
		element.style.display = "block";
	});
}

function hide(id) { document.getElementById(id).style.display = "none"; }
function show(id) { document.getElementById(id).style.display = "block"; }

function main() {
	// use internal timeout to reduce sync performance load
	setTimeout(main, 1000);
	// all updates will halt when apijson is not a JSON object
	// if updating, timeoutpid will be a timeout PID (positive integer)
	if (typeof apijson == "object" && timeoutpid == 0) {
		show("fc");
		show("ainfo");
		show("binfo");
		// iterate from 0 to 21
		fctime = (fctime + 1) % 22;
		// change forecast location every 11 seconds
		if (fctime % 11 == 0) {
			var loc = "ab"[fctime / 11];
			document.getElementById("fc").style.color = loc == 'a' ? "orange" : "aquamarine";
			// assuming that there are 5 entries
			for (var i = 0; i < 5; i++) {
				document.getElementById("fc" + i).setAttribute("class", "owf owf-" + fc[loc][i]);
				document.getElementById("fcdate" + i).textContent = fcdate[loc][i][0];
				document.getElementById("fchilow" + i).textContent = fchilow[loc][i];
			}
		}
		// multiplies by 10 as the bar must reach the end before returning to the start
		document.getElementById("fctime").style.width = fctime % 11 * 10 + '%';
	}
	
	var sec = document.getElementById("s").textContent;
	// trusting timeout pacing and add 1 when it is not updating or cross-minute
	if (sec < 59 && sec != "") {
		sec = Number(sec) + 1;
		if (sec.toString().length < 2) sec = "0" + sec;
		document.getElementById("s").textContent = sec;
		// when server hosted, the browser must refresh everyday to ensure it is up-to-date
		if (document.getElementById("atime").textContent == "00:00"
			&& sec == 30 && hosted) {
			// refresh at 30th second to eliminate server side race condition
			location.reload(true);
		}
		// allow fallthrough every minute to sync with system clock
		return;
	}
	
	if (typeof apijson == "object" && timeoutpid == 0) {
		// add timezone seconds to epoch time and convert to time format
		var atoday = moment().add(apijson['a']["weather"]["timezone"], 's').utc();
		var btoday = moment().add(apijson['b']["weather"]["timezone"], 's').utc();
		// mass updating dictionary
		var target = {
			"atime": atoday.format("HH:mm"),
			"btime": btoday.format("HH:mm"),
			"s": atoday.format("ss"),
			// use \x1d (group separator) for placeholder, replacing it with padding later
			"adate": aloc.split(',')[0] + ":\x1d" + atoday.format("dddd,\xa0Do\xa0MMM"),
			"bdate": bloc.split(',')[0] + ":\x1d" + btoday.format("dddd,\xa0Do\xa0MMM")
		};

		// request for weather every 5 minutes
		if (atoday.minute() % 5 == 0) refreshweather(false);
		// three-way comparison to determine pad direction
		var lendiff = target["adate"].length - target["bdate"].length;
		target["adate"] = target["adate"].replace("\x1d", repeatStr("\xa0", lendiff < 0 ? -lendiff + 1 : 1));
		target["bdate"] = target["bdate"].replace("\x1d", repeatStr("\xa0", lendiff > 0 ? lendiff + 1 : 1));
		Object.keys(target).forEach(function(id) { document.getElementById(id).textContent = target[id]; });
	}
}

function repeatStr(str, times) {
	var ret = "";
	for (var i = 0; i < times; i++) ret += str;
	return ret;
}
