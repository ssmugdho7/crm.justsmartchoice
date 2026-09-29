"use strict";
$(document).ready(function(){
 var editfile = $('#dmn_content').val();
 var diagramUrl = '../dmndiagram/'+'diagram'+editfile;
 var dmnModeler = new DmnJS({
  container: "#canvas",
  keyboard: {
    bindTo: window
  }
});
async function exportDiagram() {
  try {
    var result = await dmnModeler.saveXML({ format: true });
    $('textarea#dmn_content').html(result.xml);
  }catch (err) {
    console.error('could not save dmn 2.0 diagram', err);
  }
}
async function openDiagram(dmnXML) {
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
$.get(diagramUrl, openDiagram, 'text');
});