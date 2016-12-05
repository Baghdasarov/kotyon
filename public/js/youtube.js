$(document).ready(function(){
//   $('#start_search').click(function(){
//       startSearch();
//       
//           console.log(alldata);
//       
//   }) 
});
var alldata = [];
var inputNum = 49;
var curId = 2;
var tm = 300;

var token = "";
var loc = "";
var workData = {};
var progress = 0;
var count = 0;
var index = 0;
var running = false;
var coords = {"usa" : "40.683948,-73.888618", "uk" : "51.451053,-0.161530", "denmark" : "55.659008,12.545010", "japan" : "35.683422,139.742779"};
window.onload = init;

function init(){
    
//  addFields(inputNum);
//  document.getElementById("start_scan").onclick = startSearch;
//  
//  document.getElementById("download").onclick = download;

  if(document.getElementById("lang-select")){
    document.getElementById("lang-select").onchange = setCoordinates;
  }
  loc = coords['usa'];
}

//function addFields(num){
//  for (var i=0; i<num; i++){
//  	var fieldItem = '<div class="input-line"><div class="border" style="z-index: 1;"><div class="input-wrapper"><span class="input-title">Search term</span><input type="text" name="term_'+curId+'" class="term1" placeholder="Search" /></div></div><div class="border"><div class="input-wrapper second"><span class="input-title">Secondary term</span><input type="text" name="sterm_'+curId+'" class="term2" placeholder="Search" /></div></div></div>';
//  	var container = document.getElementById("main_form");
//  	container.innerHTML += fieldItem;
//  	curId++;
//  }
//}

function download(){
  var dl = document.getElementById("link");
  if (dl.href === "" || document.getElementById('pg-text').innerHTML !== '100%') return;
  dl.click();
}

function setCoordinates(e){
  var val = e.target.value;
  loc = coords[val];
  document.getElementById("location").value = loc;
}

function startSearch(){
  if (running) return;
  running = true;
  isError = false;
  var fields = document.getElementsByClassName("term1");
  if (document.getElementById("location").value) loc = document.getElementById("location").value;
  var max_res = 100;
  var data = {};
  if (loc) data['location'] = loc;
  for (var i=0; i<fields.length; i++){
  	var f = fields[i];
  	if (!f.value) continue;
  	data[f.name] = f.value;
//  	var sf = document.getElementsByName("s"+f.name)[0];
//    var ers = sf.parentNode.getElementsByClassName("error-msg");
//    for (var j=0; j<ers.length; j++){
//      ers[j].remove();
//    }
//    if (!sf.value) {
//      sf.parentNode.innerHTML = sf.parentNode.innerHTML + "<span class='error-msg'>Secondary term needs to be filled out.</span>";
//      isError = true;
//    }
       var sf = ""
  }
  if (document.getElementsByName("max_res_0")[0] && document.getElementsByName("max_res_0")[0].value)
      max_res = document.getElementsByName("max_res_0")[0].value;
    data["max_res_0"] = max_res;
  if (Object.keys(data).length === 0) {
  	running = false;
  	return;
  }
  if (isError) {
    running = false;
    return;
  }
  token = new Date().getTime();
  data['token'] = token;
  //if (document.getElementById("lang-select")) data['lang'] = document.getElementById("lang-select").value;
  running = true;
//  document.getElementById("progress").style.width = "0px";
//  document.getElementById("pg-text").innerHTML = "0%";
//  progress = 0;
//  var start = document.getElementById("start");
//  start.style.cursor = "default";
//  start.style.opacity = 0.7;
//  var download = document.getElementById("download");
//  download.style.cursor = "default";
//  download.style.opacity = 0.7;
  document.getElementById("link").href = "";
  $.ajaxSetup(
  {
  headers:
    {
        'X-CSRF-Token': $('input[name="_token"]').val()
    }
  });
  $.ajax({
    method: "POST",
    url: "startSearch",
    
    data: data,
    success: function(msg){
      console.log(msg);
      workData = JSON.parse(msg);
      console.log(workData);
      checkProcess();
    }
  });
}

function beginUpdating(){
  
}

function checkTerm(vids, t1){

}

function updateProgress(){
  var width = document.getElementById('bar').offsetWidth * (progress / 100);
  document.getElementById("progress").style.width = width + "px";
  var prec = Math.round(progress * 100) / 100;
  if (!isFinite(progress)) prec = 100;
  document.getElementById("pg-text").innerHTML = prec + "%";
  if (Math.round(progress) === 100) {
  	var dl = document.getElementById("link");
  	dl.href = "./tmp/tmp_"+token+".csv";
  	token = "";
    workData = {};
    progress = 0;
    count = 0;
    index = 0;
    running = false;
//    var start = document.getElementById("start");
//    var download = document.getElementById("download");
//    start.style.cursor = "pointer";
//    start.style.opacity = 1.0;
//    download.style.cursor = "pointer";
//    download.style.opacity = 1.0;
  }
}

function checkProcess(vid, t1, indx){
  if (progress === 100 || !running) {
  	progress = 0;
  	running = false;
  	return;
  }
  for (var i=0; i<workData.length; i++){
    var obj = workData[i];
    var vids = obj['vids'];
    count += vids.length;
  }
  var timeout = 0;
  var tdif = 1000;//10000;
  for (var i=0; i<workData.length; i++){
    var obj = workData[i];
    var t1 = obj['term1'];
    var vids = obj['vids'];
    setTimeout(checkTerm, timeout, vids, t1);
    timeout += tdif;
  }
    for (var j=0; j<vids.length; j++){
    var vid = vids[j];
    var index = j + 1;
    checkProcess(vid, t1, index);
  }
  var data = {"token" : token, "vid" : vid, "term1" : t1, "rank" : indx};
  $.ajaxSetup(
    {
        headers:
        {
            'X-CSRF-Token': $('input[name="_token"]').val()
        }
    });
  $.ajax({
    method: "POST",
    url: "continueSearch",
    data: data,
    success: function(msg){
      if (!msg) return;
      index++;
      progress = index * 100 / count;
      updateProgress();
      console.log('last ajax');
      console.log(msg);
      alldata.push(msg);
      console.log('alldata ajax');
      console.log(alldata);
    },
    error: function(msg){
      //console.log("Error: ", msg);
      if (msg.status !== 503) setTimeout(checkProcess, 3000, vid, t1, indx);
    },
    statusCode: {
    503: function() {
      //console.log("HTTP 503");
      setTimeout(checkProcess, 3000, vid, t1, indx);
    }
  }
  });
  
}