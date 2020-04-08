"use strict";

function init() {
	try {
		window.aloc = localStorage.getItem("aloc");
		window.bloc = localStorage.getItem("bloc");
		document.getElementById("contrast").checked
			= localStorage.getItem("contrast") == "true";
	} catch (e) {}
	window.aloc = window.aloc == null ? "Hong Kong" : aloc;
	window.bloc = window.bloc == null ? "London" : bloc;
	document.getElementById("alocinput").value = aloc;
	document.getElementById("blocinput").value = bloc;
	window.apijson = null;
	window.fctime = 21;
	window.fc = { 'a': new Array(5), 'b': new Array(5) };
	window.fcdate = { 'a': new Array(5), 'b': new Array(5) };
	window.fchilow = { 'a': new Array(5), 'b': new Array(5) };
	
	[].forEach.call(document.getElementsByClassName("jshide"), function(element) {
		element.style.display = "block";
	});
}

function hide(id) { document.getElementById(id).style.display = "none"; }
function show(id) { document.getElementById(id).style.display = "block"; }

function main() {
	setTimeout(main, 1000);
	if (apijson && typeof fchilow['b'][4] == "string") {
		show("fc");
		show("ainfo");
		show("binfo");
		fctime = (fctime + 1) % 22;
		if (fctime % 11 == 0) {
			var loc = "ab"[fctime / 11];
			document.getElementById("fc").style.color = loc == 'a' ? "orange" : "aquamarine";
			for (var i = 0; i < 5; i++) {
				document.getElementById("fc" + i).setAttribute("class", "owf owf-" + fc[loc][i]);
				document.getElementById("fcdate" + i).textContent = fcdate[loc][i][0];
				document.getElementById("fchilow" + i).textContent = fchilow[loc][i];
			}
		}
		document.getElementById("fctime").style.width = fctime % 11 * 10 + '%';
	}
	
	var sec = document.getElementById("s").textContent;
	if (sec < 59 && sec != "") {
		sec = Number(sec) + 1;
		if (sec.toString().length < 2) sec = "0" + sec;
		document.getElementById("s").textContent = sec;
		if (document.getElementById("atime").textContent == "00:00" && sec == 30) location.reload(true);
		return;
	}
	
	if (apijson && typeof fchilow['b'][4] == "string") {
		var atoday = moment().add(apijson['a']["weather"]["timezone"], 's').utc();
		var btoday = moment().add(apijson['b']["weather"]["timezone"], 's').utc();
		var target = {
			"atime": atoday.format("HH:mm"),
			"btime": btoday.format("HH:mm"),
			"s": atoday.format("ss"),
			"adate": aloc.split(',')[0] + ":\x1d" + atoday.format("dddd,\xa0Do\xa0MMM"),
			"bdate": bloc.split(',')[0] + ":\x1d" + btoday.format("dddd,\xa0Do\xa0MMM")
		};

		if (atoday.minute() % 5 == 0) refreshweather();
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
