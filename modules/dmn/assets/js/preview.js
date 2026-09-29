"use strict";
const csrfHash = jQuery('input#csrf_token').val();
document.getElementById('print-svg').addEventListener('click', function () {
  if(confirm('Please adjust the canvas within your screen size before printing using ctrl+scroll !')){
    var div = document.getElementById('canvas');
    var svg = div.querySelector('svg');
    var canvas = document.getElementById('c');
    var canvasName = canvas.getAttribute('data-name');
    var rect = $(svg).find('rect').first()[0];
    const svgRectWidth = 1049;
    const svgRectHeight = 900;
    canvas.setAttribute('width',svgRectWidth);
    canvas.setAttribute('height',svgRectHeight);
    var data = new XMLSerializer().serializeToString(svg);
    var win = window.URL || window.webkitURL || window;
    var img = new Image();
    var blob = new Blob([data], { type: 'image/svg+xml' });
    var url = win.createObjectURL(blob);
    canvas.style.display = "none";
    img.onload = function () {
      canvas.getContext('2d').drawImage(img, 0, 0);
      win.revokeObjectURL(url);
      var uri = canvas.toDataURL('image/png').replace('image/png', 'octet/stream');
      var a = document.createElement('a');
      document.body.appendChild(a);
      a.style = 'display: none';
      a.href = uri
      a.download = canvasName+'.png';
      a.click();
      window.URL.revokeObjectURL(uri);
      document.body.removeChild(a);
    };
    img.src = url;
  }else{
    return false;
  }
});
var adminurl = admin_url.substring(0, admin_url.lastIndexOf("/"));
var adminurl = adminurl.substring(0, adminurl.lastIndexOf("/"));
var editfile = jQuery('textarea#dmn_content').val();
if(editfile=='')
{
   var globalxml = adminurl+'/modules/dmn/dmndiagram/diagram.dmn';
}
else
{
  var globalxml = adminurl+'/modules/dmn/dmndiagram/'+'diagram'+editfile;
}
var diagramUrl = globalxml;                                 
var dmnModeler = new DmnJS({
  container: "#canvas",
  keyboard: {
    bindTo: window
  }
});
function exportDiagram() {
  dmnModeler.saveXML({ format: true }, function(err, xml) {
    if (err) {
      return console.error("could not save DMN 1.1 diagram", err);
    }
    $("#dmnxml").html(xml);
  });
}
function openDiagram(dmnXML) {
  dmnModeler.importXML(dmnXML, function(err) {
    if (err) {
      return console.error("could not import DMN 1.1 diagram", err);
    }
    var activeView = dmnModeler.getActiveView();
    if (activeView.type === "drd") {
      var activeEditor = dmnModeler.getActiveViewer();
      var canvas = activeEditor.get("canvas");
      canvas.zoom("fit-viewport");
    }
  });
}
$.get(diagramUrl, openDiagram, "text");
$("#save-button").on('click',exportDiagram);
function likeCanvas(thumbVal){
  const data = {"csrf_token_name": csrfHash};
  const url = admin_url+'/dmn/dmn/likeCanvas';
  data.thumb = thumbVal;
  data.dmn_id = window.location.pathname.split("/").pop();
  $.post(url,data,function(resp){
    var response = JSON.parse(resp)
    $(".like_canvas").find('span').html(response.like);
    $(".dislike_canvas").find('span').html(response.dislike);
    if(thumbVal == 'dislike'){
      $('.like_canvas').find('i').css('color','');
      $('.dislike_canvas').find('i').css('color','red');
    }else{
      $('.dislike_canvas').find('i').css('color','');
      $('.like_canvas').find('i').css('color','green');
    }
  });
}
$('#print').on('click',function(){
  window.print();
});